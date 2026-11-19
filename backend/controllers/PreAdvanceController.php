<?php

namespace backend\controllers;

use Yii;
use backend\models\PreAdvance;
use backend\models\PreAdvanceLine;
use backend\models\PreAdvanceDoc;
use backend\models\PreAdvanceRef;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use app\behaviors\ActionLogBehavior;

/**
 * PreAdvanceController implements the CRUD actions for PreAdvance model.
 */
class PreAdvanceController extends BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'reject' => ['POST'],
                    'return' => ['POST'],
                ],
            ],
            'actionLog' => [
                'class' => ActionLogBehavior::class,
                'actions' => ['create', 'update', 'delete', 'check', 'uncheck', 'approve', 'unapprove', 'reject', 'return', 'view', 'print', 'request-recall', 'approve-recall', 'reject-recall'],
            ],
        ];
    }

    public function beforeAction($action)
    {
        PreAdvance::ensureRecallColumns();
        PreAdvance::ensureRejectColumns();
        return parent::beforeAction($action);
    }

    public function actionViewDiff($id)
    {
        $log = \backend\models\ActionLogModel::findOne($id);
        if ($log === null) {
            throw new \yii\web\NotFoundHttpException('ไม่พบข้อมูล Log');
        }
        return $this->renderAjax('_view_diff', [
            'model' => $log,
        ]);
    }

    public function actionAddDbColumn()
    {
        $db = Yii::$app->db;
        $table = $db->getTableSchema('purchase_master');
        if ($table && !isset($table->columns['approve_by'])) {
            $db->createCommand()->addColumn('purchase_master', 'approve_by', $db->getSchema()->createColumnSchemaBuilder('int')->defaultValue(null))->execute();
            return "Added approve_by to purchase_master";
        }
        return "approve_by already exists in purchase_master or table not found";
    }

    public function actionIndex()
    {
        $globalSearch = Yii::$app->request->get('globalSearch');
        $query = PreAdvance::find()->orderBy(['id' => SORT_DESC]);
        
        if ($globalSearch) {
            $query->joinWith('vendor');
            $query->andFilterWhere([
                'or',
                ['like', 'pre_advance.pre_advance_no', $globalSearch],
                ['like', 'vendor.name', $globalSearch],
            ]);
        }
        
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'globalSearch' => $globalSearch,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        // ตรวจสอบและอัปเดตเลขอ้างอิง QT ใน description ให้เป็นเลขที่ใบเสนอราคา/Job ที่แท้จริง (กรณีเดิมบันทึกเป็น id ตัวเลข)
        if (!empty($model->preAdvanceLines)) {
            foreach ($model->preAdvanceLines as $line) {
                $resolved = self::resolveLineDescription($line->description);
                if (!empty($resolved) && $resolved !== $line->description) {
                    $line->description = $resolved;
                    $line->save(false);
                }
            }
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new PreAdvance();
        $model->trans_date = date('Y-m-d');
        $model->status = PreAdvance::STATUS_ACTIVE;

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->status == PreAdvance::STATUS_REJECTED) {
                    $model->status = PreAdvance::STATUS_ACTIVE;
                }
                if ($model->save()) {
                    $this->savePreAdvanceLines($model);
                    $this->savePreAdvanceRefs($model);
                    $this->uploadAttachments($model);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'บันทึก Pre-Advance สำเร็จ');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if (!empty($model->approved_by)) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถแก้ไขเอกสารที่อนุมัติแล้วได้');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->status == PreAdvance::STATUS_REJECTED && !\backend\models\User::isUserAdmin()) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถแก้ไขเอกสารที่ไม่อนุมัติแล้วได้');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->status == PreAdvance::STATUS_RETURNED || $model->status == PreAdvance::STATUS_REJECTED) {
                    $model->status = PreAdvance::STATUS_ACTIVE;
                }
                if ($model->save()) {
                    $this->savePreAdvanceLines($model);
                    $this->savePreAdvanceRefs($model);
                    $this->uploadAttachments($model);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'อัปเดต Pre-Advance สำเร็จ');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        // Auto-sync lines with actual PO/NPR values before rendering
        $autoUpdatedAmount = 0;
        $changedMessages = [];
        foreach ($model->preAdvanceLines as $line) {
            $oldAmount = $line->amount;
            if (preg_match('/เลขที่:\s*(NPR\d+)/', $line->description, $m)) {
                $npr = \backend\models\PurchaseMaster::findOne(['docnum' => $m[1]]);
                if ($npr) {
                    $line->amount = $npr->total_amount;
                    $jobRef = self::resolveNonePrJobRef($npr);
                    $line->description = 'เลขที่: ' . $npr->docnum . (!empty($jobRef) ? ' (อ้างอิง QT: ' . $jobRef . ')' : '');
                    
                    if (abs($oldAmount - $line->amount) > 0.001) {
                        $changedMessages[] = $npr->docnum . ' (จาก ' . number_format($oldAmount, 2) . ' เป็น ' . number_format($line->amount, 2) . ')';
                    }
                }
            } elseif (preg_match('/เลขที่:\s*(PO-[^\s\(]+)/', $line->description, $m)) {
                $po = \backend\models\Purch::findOne(['purch_no' => $m[1]]);
                if ($po) {
                    $line->amount = $po->net_amount;
                    $jobRef = self::resolvePoJobRef($po);
                    $line->description = 'เลขที่: ' . $po->purch_no . (!empty($jobRef) ? ' (อ้างอิง QT: ' . $jobRef . ')' : '');
                    
                    if (abs($oldAmount - $line->amount) > 0.001) {
                        $changedMessages[] = $po->purch_no . ' (จาก ' . number_format($oldAmount, 2) . ' เป็น ' . number_format($line->amount, 2) . ')';
                    }
                }
            }
            $autoUpdatedAmount += $line->amount;
        }
        $model->amount = $autoUpdatedAmount;

        if (!empty($changedMessages)) {
            $alertMsg = 'ระบบดึงยอดเงินล่าสุดจากบิลต้นฉบับ: ' . implode(', ', $changedMessages) . ' **กรุณากดปุ่มอัพเดทรายการด้านล่างเพื่อบันทึก**';
            Yii::$app->session->setFlash('update-sync-msg', $alertMsg);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (!empty($model->approved_by)) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบเอกสารที่อนุมัติแล้วได้');
            return $this->redirect(['index']);
        }
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบเอกสารสำเร็จ');
        return $this->redirect(['index']);
    }

    public function actionCheck($id)
    {
        $model = $this->findModel($id);
        $model->checked_by = Yii::$app->user->id;
        $model->checked_at = date('Y-m-d H:i:s');
        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'ตรวจสอบรายการแล้ว');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionUncheck($id)
    {
        $model = $this->findModel($id);
        $model->checked_by = null;
        $model->checked_at = null;
        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'ยกเลิกการตรวจสอบแล้ว');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        $userId = Yii::$app->user->id;

        if (!self::canApprovePreAdvance($userId, null, $model)) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์ในการตรวจสอบและอนุมัติเอกสาร Payment Advance (อิงตาม Permission Matrix)');
        }

        $now = date('Y-m-d H:i:s');

        // บันทึกทั้งตรวจสอบและอนุมัติพร้อมกันในคลิกเดียว
        if (empty($model->checked_by)) {
            $model->checked_by = $userId;
            $model->checked_at = $now;
        }
        $model->approved_by = $userId;
        $model->approved_at = $now;

        if ($model->save(false)) {
            \backend\models\ActionLogModel::logModelAction(
                'APPROVE',
                $model,
                [
                    'checked_by' => $model->checked_by,
                    'checked_at' => $model->checked_at,
                    'approved_by' => $model->approved_by,
                    'approved_at' => $model->approved_at,
                ],
                \backend\models\ActionLogModel::STATUS_SUCCESS,
                'ตรวจสอบและอนุมัติรายการ Payment Advance'
            );
            Yii::$app->session->setFlash('success', 'บันทึกตรวจสอบและอนุมัติรายการเรียบร้อยแล้ว');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionReject($id)
    {
        $model = $this->findModel($id);
        $userId = Yii::$app->user->id;

        if (!self::canApprovePreAdvance($userId, null, $model)) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์ในการไม่อนุมัติเอกสาร Payment Advance (อิงตาม Permission Matrix)');
        }

        PreAdvance::ensureRejectColumns();

        $rejectReason = trim(Yii::$app->request->post('reject_reason', ''));
        if (empty($rejectReason)) {
            Yii::$app->session->setFlash('error', 'กรุณาระบุเหตุผลที่ไม่อนุมัติ');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->status = PreAdvance::STATUS_REJECTED;
        $model->reject_reason = $rejectReason;
        $model->rejected_by = $userId;
        $model->rejected_at = date('Y-m-d H:i:s');
        $model->checked_by = null;
        $model->checked_at = null;
        $model->approved_by = null;
        $model->approved_at = null;

        if ($model->save(false)) {
            \backend\models\ActionLogModel::logModelAction(
                'REJECT',
                $model,
                [
                    'reject_reason' => $rejectReason,
                    'rejected_by' => $userId,
                    'rejected_at' => $model->rejected_at,
                ],
                \backend\models\ActionLogModel::STATUS_SUCCESS,
                'ไม่อนุมัติ Payment Advance: ' . $rejectReason
            );
            Yii::$app->session->setFlash('success', 'บันทึกไม่อนุมัติ Payment Advance เรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถดำเนินการไม่อนุมัติได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionReturn($id)
    {
        $model = $this->findModel($id);
        $userId = Yii::$app->user->id;

        if (!self::canApprovePreAdvance($userId, null, $model)) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์ในการส่งกลับแก้ไขเอกสาร Payment Advance (อิงตาม Permission Matrix)');
        }

        PreAdvance::ensureRejectColumns();

        $rejectReason = trim(Yii::$app->request->post('reject_reason', ''));
        if (empty($rejectReason)) {
            Yii::$app->session->setFlash('error', 'กรุณาระบุรายละเอียดที่ต้องการให้ปรับหรือแก้ไข');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->status = PreAdvance::STATUS_RETURNED;
        $model->reject_reason = $rejectReason;
        $model->rejected_by = $userId;
        $model->rejected_at = date('Y-m-d H:i:s');
        $model->checked_by = null;
        $model->checked_at = null;
        $model->approved_by = null;
        $model->approved_at = null;

        if ($model->save(false)) {
            \backend\models\ActionLogModel::logModelAction(
                'RETURN_FOR_EDIT',
                $model,
                [
                    'reject_reason' => $rejectReason,
                    'rejected_by' => $userId,
                    'rejected_at' => $model->rejected_at,
                ],
                \backend\models\ActionLogModel::STATUS_SUCCESS,
                'ส่งกลับแก้ไข Payment Advance: ' . $rejectReason
            );
            Yii::$app->session->setFlash('success', 'ส่งกลับเพื่อแก้ไข Payment Advance เรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถส่งกลับเพื่อแก้ไขได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionUnapprove($id)
    {
        $model = $this->findModel($id);
        $userId = Yii::$app->user->id;

        if (!self::canApprovePreAdvance($userId, null, $model)) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์ในการยกเลิกอนุมัติเอกสาร Payment Advance (อิงตาม Permission Matrix)');
        }

        $model->approved_by = null;
        $model->approved_at = null;
        $model->checked_by = null;
        $model->checked_at = null;
        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'ยกเลิกการตรวจสอบและอนุมัติแล้ว');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Request recall for pre-advance
     * @param int $id
     * @return Response
     */
    public function actionRequestRecall($id)
    {
        $model = $this->findModel($id);

        if (empty($model->approved_by)) {
            Yii::$app->session->setFlash('error', 'สามารถขอ Recall ได้เฉพาะรายการที่ได้รับการอนุมัติแล้วเท่านั้น');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $reason = Yii::$app->request->post('recall_reason', '');
        if (empty(trim($reason))) {
            Yii::$app->session->setFlash('error', 'กรุณาระบุเหตุผลในการร้องขอ Recall เอกสาร');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->is_recall = 1;
        $model->recall_reason = trim($reason);
        $model->recall_by = Yii::$app->user->id;
        $model->recall_at = date('Y-m-d H:i:s');

        if ($model->save(false)) {
            \backend\models\ActionLogModel::logModelAction(
                'REQUEST_RECALL',
                $model,
                [
                    'recall_reason' => $model->recall_reason,
                    'recall_by' => $model->recall_by,
                    'recall_at' => $model->recall_at,
                ],
                \backend\models\ActionLogModel::STATUS_SUCCESS,
                'ส่งคำร้องขอ Recall เอกสาร Pre-Advance: ' . $model->recall_reason
            );
            Yii::$app->session->setFlash('success', 'ส่งคำร้องขอ Recall เอกสารเรียบร้อยแล้ว รอผู้อนุมัติตรวจสอบ');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถส่งคำร้องขอ Recall ได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Approve recall for pre-advance
     * @param int $id
     * @return Response
     */
    public function actionApproveRecall($id)
    {
        $model = $this->findModel($id);

        if (!self::canApprovePreAdvance(Yii::$app->user->id, null, $model)) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์ในการอนุมัติ Recall เอกสาร Pre-Advance (อิงตาม Permission Matrix)');
        }

        if ($model->is_recall != 1) {
            Yii::$app->session->setFlash('warning', 'รายการนี้ไม่ได้อยู่ในสถานะรออนุมัติ Recall');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $oldApproveBy = $model->approved_by;
        $oldApproveAt = $model->approved_at;
        $recallReason = $model->recall_reason;

        $model->approved_by = null;
        $model->approved_at = null;
        $model->is_recall = 0; // ปลดล็อคกลับมาให้สามารถแก้ไข/อัปเดตได้

        if ($model->save(false)) {
            \backend\models\ActionLogModel::logModelAction(
                'APPROVE_RECALL',
                $model,
                [
                    'previous_approved_by' => $oldApproveBy,
                    'previous_approved_at' => $oldApproveAt,
                    'recall_reason' => $recallReason,
                    'approved_recall_by' => Yii::$app->user->id,
                ],
                \backend\models\ActionLogModel::STATUS_SUCCESS,
                'อนุมัติการ Recall เอกสาร Pre-Advance ปลดสถานะอนุมัติเพื่อให้แก้ไขได้'
            );
            Yii::$app->session->setFlash('success', 'อนุมัติการ Recall เอกสารเรียบร้อยแล้ว เอกสารถูกดึงกลับมาให้แก้ไขได้');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถอนุมัติการ Recall ได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Reject recall for pre-advance
     * @param int $id
     * @return Response
     */
    public function actionRejectRecall($id)
    {
        $model = $this->findModel($id);

        if (!self::canApprovePreAdvance(Yii::$app->user->id, null, $model)) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์ในการปฏิเสธ Recall เอกสาร Pre-Advance (อิงตาม Permission Matrix)');
        }

        if ($model->is_recall != 1) {
            Yii::$app->session->setFlash('warning', 'รายการนี้ไม่ได้อยู่ในสถานะรออนุมัติ Recall');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $rejectReason = Yii::$app->request->post('reject_reason', 'ไม่อนุมัติคำร้องขอ Recall');
        $model->is_recall = 0;

        if ($model->save(false)) {
            \backend\models\ActionLogModel::logModelAction(
                'REJECT_RECALL',
                $model,
                [
                    'reject_reason' => $rejectReason,
                    'rejected_recall_by' => Yii::$app->user->id,
                ],
                \backend\models\ActionLogModel::STATUS_SUCCESS,
                'ปฏิเสธคำร้องขอ Recall เอกสาร Pre-Advance: ' . $rejectReason
            );
            Yii::$app->session->setFlash('info', 'ปฏิเสธคำร้องขอ Recall เอกสารเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการปฏิเสธ Recall');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionGetNonePrByVendor($vendor_id = null, $q = null, $pre_advance_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $result = [];

        // Resolve vendor filter (can be vendor ID or vendor code)
        $vendorCode = null;
        $vendorIdInt = null;
        if ($vendor_id && $vendor_id !== 'null' && $vendor_id !== '') {
            if (is_numeric($vendor_id)) {
                $vModel = \backend\models\Vendor::findOne($vendor_id);
                if ($vModel) {
                    $vendorIdInt = (int)$vModel->id;
                    $vendorCode = $vModel->code;
                } else {
                    $vendorIdInt = (int)$vendor_id;
                    $vendorCode = (string)$vendor_id;
                }
            } else {
                $vModel = \backend\models\Vendor::find()->where(['code' => $vendor_id])->one();
                if ($vModel) {
                    $vendorIdInt = (int)$vModel->id;
                    $vendorCode = $vModel->code;
                } else {
                    $vendorCode = (string)$vendor_id;
                }
            }
        }

        // Fetch used references once in a single query to eliminate N+1 queries
        $usedRefsQuery = \backend\models\PreAdvanceRef::find()
            ->alias('r')
            ->select(['r.ref_type', 'r.ref_id'])
            ->innerJoin('pre_advance pa', 'r.pre_advance_id = pa.id')
            ->where(['!=', 'pa.status', \backend\models\PreAdvance::STATUS_CANCELLED]);

        if ($pre_advance_id) {
            $usedRefsQuery->andWhere(['!=', 'r.pre_advance_id', $pre_advance_id]);
        }

        $usedRefs = $usedRefsQuery->asArray()->all();
        $usedNonePrIds = [];
        $usedPoIds = [];
        foreach ($usedRefs as $ref) {
            if ((int)$ref['ref_type'] === \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR) {
                $usedNonePrIds[] = (int)$ref['ref_id'];
            } elseif ((int)$ref['ref_type'] === \backend\models\PreAdvanceRef::REF_TYPE_PO) {
                $usedPoIds[] = (int)$ref['ref_id'];
            }
        }

        // 1. None PR (PurchaseMaster)
        $queryNonePr = \backend\models\PurchaseMaster::find()
            ->where(['approve_status' => \backend\models\PurchaseMaster::APPROVE_STATUS_APPROVED])
            ->andWhere(['status' => \backend\models\PurchaseMaster::STATUS_ACTIVE])
            ->andWhere(['>', 'total_amount', 0]);
            
        if (!empty($usedNonePrIds)) {
            $queryNonePr->andWhere(['not in', 'id', $usedNonePrIds]);
        }

        if ($vendorCode || $vendorIdInt) {
            $conds = ['or'];
            if ($vendorCode) $conds[] = ['supcod' => $vendorCode];
            if ($vendorIdInt) $conds[] = ['supcod' => $vendorIdInt];
            $queryNonePr->andWhere($conds);
        }
        
        if ($q !== null && trim($q) !== '') {
            $searchTerm = trim($q);
            $queryNonePr->andWhere([
                'or',
                ['like', 'docnum', $searchTerm],
                ['like', 'refnum', $searchTerm],
                ['like', 'job_no', $searchTerm],
                ['like', 'supnam', $searchTerm],
            ]);
        }
        
        $none_prs = $queryNonePr->orderBy(['id' => SORT_DESC])->limit(50)->all();
        
        foreach ($none_prs as $none_pr) {
            $result[] = [
                'id' => 'none_pr_' . $none_pr->id,
                'text' => '[None PR] ' . $none_pr->docnum . ' (ยอดรวม: ' . number_format($none_pr->total_amount, 2) . ( !empty($none_pr->supnam) ? ' - ' . $none_pr->supnam : '' ) . ')',
            ];
        }

        // 2. PO (Purch)
        $queryPo = \backend\models\Purch::find()
            ->where(['approve_status' => 1])
            ->andWhere(['>', 'net_amount', 0]);

        if (!empty($usedPoIds)) {
            $queryPo->andWhere(['not in', 'id', $usedPoIds]);
        }
            
        if ($vendorCode || $vendorIdInt) {
            $conds = ['or'];
            if ($vendorIdInt) $conds[] = ['vendor_id' => $vendorIdInt];
            if ($vendorCode) $conds[] = ['vendor_id' => $vendorCode];
            $queryPo->andWhere($conds);
        }
        
        if ($q !== null && trim($q) !== '') {
            $searchTerm = trim($q);
            $queryPo->andWhere([
                'or',
                ['like', 'purch_no', $searchTerm],
                ['like', 'ref_no', $searchTerm],
                ['like', 'vendor_name', $searchTerm],
            ]);
        }
        
        $pos = $queryPo->orderBy(['id' => SORT_DESC])->limit(50)->all();
        
        foreach ($pos as $po) {
            $result[] = [
                'id' => 'po_' . $po->id,
                'text' => '[PO] ' . $po->purch_no . ' (ยอดรวม: ' . number_format($po->net_amount, 2) . ( !empty($po->vendor_name) ? ' - ' . $po->vendor_name : '' ) . ')',
            ];
        }
        
        return ['results' => $result];
    }

    public function actionPullMultiple()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $none_pr_ids = Yii::$app->request->post('none_pr_ids', []);
        
        $lines = [];
        $total_amount = 0;
        $vendor_id = null;
        $vendor_name = null;
        
        foreach ($none_pr_ids as $item) {
            if (empty($item)) continue;
            
            if (is_string($item) && strpos($item, 'po_') === 0) {
                $po_id = (int)str_replace('po_', '', $item);
                $po = \backend\models\Purch::findOne($po_id);
                if ($po) {
                    $total_amount += $po->net_amount;
                    $vName = !empty($po->vendor_name) ? $po->vendor_name : ($po->vendor ? $po->vendor->name : '');
                    if (!$vendor_id) {
                        $vendor_id = $po->vendor_id;
                        $vendor_name = $vName;
                    }
                    
                    $desc = 'เลขที่: ' . $po->purch_no;
                    $jobRef = self::resolvePoJobRef($po);
                    if (!empty($jobRef)) {
                        $desc .= ' (อ้างอิง QT: ' . $jobRef . ')';
                    }
                    $lines[] = [
                        'line_date' => date('Y-m-d'),
                        'description' => $desc,
                        'amount' => $po->net_amount,
                        'remark' => $vName ?: '',
                    ];
                }
            } else {
                $none_pr_id = (int)str_replace('none_pr_', '', (string)$item);
                $none_pr = \backend\models\PurchaseMaster::findOne($none_pr_id);
                if ($none_pr) {
                    $total_amount += $none_pr->total_amount;
                    $vName = !empty($none_pr->supnam) ? $none_pr->supnam : ($none_pr->vendor ? $none_pr->vendor->name : '');
                    if (!$vendor_id) {
                        $vendor_id = $none_pr->supcod;
                        $vendor_name = $vName;
                    }
                    
                    $desc = 'เลขที่: ' . $none_pr->docnum;
                    $jobRef = self::resolveNonePrJobRef($none_pr);
                    if (!empty($jobRef)) {
                        $desc .= ' (อ้างอิง QT: ' . $jobRef . ')';
                    }
                    $lines[] = [
                        'line_date' => date('Y-m-d'),
                        'description' => $desc,
                        'amount' => $none_pr->total_amount,
                        'remark' => $vName ?: '',
                    ];
                }
            }
        }
        
        return [
            'success' => true,
            'amount' => $total_amount,
            'none_pr_ids' => $none_pr_ids,
            'vendor_id' => $vendor_id ?? null,
            'vendor_name' => $vendor_name ?? null,
            'lines' => $lines,
        ];
    }

    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $this->layout = false;
        return $this->render('print', [
            'model' => $model,
        ]);
    }

    private function savePreAdvanceLines($model)
    {
        PreAdvanceLine::deleteAll(['pre_advance_id' => $model->id]);
        
        $dates = Yii::$app->request->post('line_date', []);
        $descriptions = Yii::$app->request->post('line_description', []);
        $amounts = Yii::$app->request->post('line_amount', []);
        $remarks = Yii::$app->request->post('line_remark', []);
        
        foreach ($descriptions as $i => $desc) {
            if (empty($desc) && empty($amounts[$i])) continue;
            
            $rem = trim($remarks[$i] ?? '');
            if (strtolower($rem) === 'null') {
                $rem = '';
            }

            $line = new PreAdvanceLine();
            $line->pre_advance_id = $model->id;
            $line->line_date = $dates[$i] ?? null;
            $line->description = $desc;
            $line->amount = $amounts[$i] ?? 0;
            $line->remark = $rem;
            $line->save(false);
        }
    }

    private function savePreAdvanceRefs($model)
    {
        $none_pr_ids_raw = Yii::$app->request->post('none_pr_ids', []);
        
        if (is_string($none_pr_ids_raw)) {
            $none_pr_ids = json_decode($none_pr_ids_raw, true) ?: [];
        } else {
            $none_pr_ids = $none_pr_ids_raw;
        }
        
        PreAdvanceRef::deleteAll(['pre_advance_id' => $model->id]);
        
        foreach ($none_pr_ids as $item) {
            if (empty($item)) continue;
            
            if (is_string($item) && strpos($item, 'po_') === 0) {
                $po_id = (int)str_replace('po_', '', $item);
                $po = \backend\models\Purch::findOne($po_id);
                if ($po) {
                    $isUsed = \backend\models\PreAdvanceRef::find()
                        ->where(['ref_type' => \backend\models\PreAdvanceRef::REF_TYPE_PO, 'ref_id' => $po->id])
                        ->andWhere(['!=', 'pre_advance_id', $model->id])
                        ->exists();
                    
                    if (!$isUsed) {
                        $ref = new PreAdvanceRef();
                        $ref->pre_advance_id = $model->id;
                        $ref->ref_type = PreAdvanceRef::REF_TYPE_PO;
                        $ref->ref_id = $po->id;
                        $ref->save(false);
                    }
                }
            } else {
                $none_pr_id = (int)str_replace('none_pr_', '', (string)$item);
                $none_pr = \backend\models\PurchaseMaster::findOne($none_pr_id);
                if ($none_pr) {
                    $isUsed = \backend\models\PreAdvanceRef::find()
                        ->where(['ref_type' => \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR, 'ref_id' => $none_pr->id])
                        ->andWhere(['!=', 'pre_advance_id', $model->id])
                        ->exists();
                    
                    if (!$isUsed) {
                        $ref = new PreAdvanceRef();
                        $ref->pre_advance_id = $model->id;
                        $ref->ref_type = PreAdvanceRef::REF_TYPE_NONE_PR;
                        $ref->ref_id = $none_pr->id;
                        $ref->save(false);
                    }
                }
            }
        }

        $this->syncAttachmentsToSources($model);
    }

    protected function findModel($id)
    {
        if (($model = PreAdvance::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    private function uploadAttachments($model)
    {
        $uploadPath = Yii::getAlias('@backend/web/uploads/pre_advance/');
        if (!file_exists($uploadPath)) {
            FileHelper::createDirectory($uploadPath, 0777);
        }

        $files = UploadedFile::getInstancesByName('upload_files');
        if ($files) {
            foreach ($files as $file) {
                $newName = time() . '_' . Yii::$app->security->generateRandomString(10) . '.' . $file->extension;
                if ($file->saveAs($uploadPath . $newName)) {
                    $doc = new PreAdvanceDoc();
                    $doc->pre_advance_id = $model->id;
                    $doc->file_name = $file->baseName . '.' . $file->extension;
                    $doc->file_path = $newName;
                    $doc->file_size = $file->size;
                    $doc->uploaded_at = time();
                    $doc->uploaded_by = Yii::$app->user->id;
                    $doc->save(false);
                }
            }
        }

        // $this->syncAttachmentsToSources($model);
    }

    private function syncAttachmentsToSources($model)
    {
        $uploadPathPreAdvance = Yii::getAlias('@backend/web/uploads/pre_advance/');
        $uploadPathPurch = Yii::getAlias('@backend/web/uploads/purch_doc/');
        if (!file_exists($uploadPathPurch)) {
            FileHelper::createDirectory($uploadPathPurch, 0777);
        }

        $docs = PreAdvanceDoc::find()->where(['pre_advance_id' => $model->id])->all();

        foreach ($docs as $doc) {
            if (file_exists($uploadPathPreAdvance . $doc->file_path) && !file_exists($uploadPathPurch . $doc->file_path)) {
                @copy($uploadPathPreAdvance . $doc->file_path, $uploadPathPurch . $doc->file_path);
            }

            if ($doc->ref_type == PreAdvanceRef::REF_TYPE_PO && $doc->ref_id) {
                $exists = \common\models\PurchDoc::find()
                    ->where(['purch_id' => $doc->ref_id, 'doc_name' => $doc->file_path])
                    ->exists();
                if (!$exists) {
                    $purchDoc = new \common\models\PurchDoc();
                    $purchDoc->purch_id = $doc->ref_id;
                    $purchDoc->doc_name = $doc->file_path;
                    $purchDoc->doc_type_id = 3;
                    $purchDoc->save(false);
                }
            } elseif ($doc->ref_type == PreAdvanceRef::REF_TYPE_NONE_PR && $doc->ref_id) {
                $exists = \common\models\PurchNonePrDoc::find()
                    ->where(['purchase_master_id' => $doc->ref_id, 'doc_name' => $doc->file_path])
                    ->exists();
                if (!$exists) {
                    $nonePrDoc = new \common\models\PurchNonePrDoc();
                    $nonePrDoc->purchase_master_id = $doc->ref_id;
                    $nonePrDoc->doc_name = $doc->file_path;
                    $nonePrDoc->doc_type_id = 3;
                    $nonePrDoc->save(false);
                }
            } else {
                $refs = PreAdvanceRef::find()->where(['pre_advance_id' => $model->id])->all();
                foreach ($refs as $ref) {
                    if ($ref->ref_type == PreAdvanceRef::REF_TYPE_PO) {
                        $exists = \common\models\PurchDoc::find()
                            ->where(['purch_id' => $ref->ref_id, 'doc_name' => $doc->file_path])
                            ->exists();
                        if (!$exists) {
                            $purchDoc = new \common\models\PurchDoc();
                            $purchDoc->purch_id = $ref->ref_id;
                            $purchDoc->doc_name = $doc->file_path;
                            $purchDoc->doc_type_id = 3;
                            $purchDoc->save(false);
                        }
                    } elseif ($ref->ref_type == PreAdvanceRef::REF_TYPE_NONE_PR) {
                        $exists = \common\models\PurchNonePrDoc::find()
                            ->where(['purchase_master_id' => $ref->ref_id, 'doc_name' => $doc->file_path])
                            ->exists();
                        if (!$exists) {
                            $nonePrDoc = new \common\models\PurchNonePrDoc();
                            $nonePrDoc->purchase_master_id = $ref->ref_id;
                            $nonePrDoc->doc_name = $doc->file_path;
                            $nonePrDoc->doc_type_id = 3;
                            $nonePrDoc->save(false);
                        }
                    }
                }
            }
        }
    }

    public function actionRemoveAttachment($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $doc = PreAdvanceDoc::findOne($id);
        if ($doc) {
            $filePath = $doc->file_path;
            if ($doc->delete()) {
                \common\models\PurchDoc::deleteAll(['doc_name' => $filePath]);
                \common\models\PurchNonePrDoc::deleteAll(['doc_name' => $filePath]);
                return ['success' => true];
            }
        }
        return ['success' => false];
    }

    /**
     * ดึงเลขที่ใบเสนอราคาหรือ Job Reference ของ None PR (PurchaseMaster)
     */
    public static function resolveNonePrJobRef($none_pr)
    {
        if (!$none_pr) return '';

        // 1. ตรวจสอบจาก job_no
        if (!empty($none_pr->job_no)) {
            if (is_numeric($none_pr->job_no)) {
                $job = \backend\models\Job::findOne($none_pr->job_no);
                if ($job) {
                    if ($job->quotation && !empty($job->quotation->quotation_no)) {
                        return $job->quotation->quotation_no;
                    }
                    if (!empty($job->job_no)) {
                        return $job->job_no;
                    }
                }
                $quotation = \backend\models\Quotation::findOne($none_pr->job_no);
                if ($quotation && !empty($quotation->quotation_no)) {
                    return $quotation->quotation_no;
                }
            } else {
                return $none_pr->job_no;
            }
        }

        // 2. ตรวจสอบจาก refnum
        if (!empty($none_pr->refnum)) {
            if (is_numeric($none_pr->refnum)) {
                $job = \backend\models\Job::findOne($none_pr->refnum);
                if ($job) {
                    if ($job->quotation && !empty($job->quotation->quotation_no)) {
                        return $job->quotation->quotation_no;
                    }
                    if (!empty($job->job_no)) {
                        return $job->job_no;
                    }
                }
                $quotation = \backend\models\Quotation::findOne($none_pr->refnum);
                if ($quotation && !empty($quotation->quotation_no)) {
                    return $quotation->quotation_no;
                }
            } else {
                return $none_pr->refnum;
            }
        }

        return '';
    }

    /**
     * ดึงเลขที่ใบเสนอราคาหรือ Job Reference ของ PO (Purch)
     */
    public static function resolvePoJobRef($po)
    {
        if (!$po) return '';

        // 1. ตรวจสอบจาก ref_no
        if (!empty($po->ref_no)) {
            if (is_numeric($po->ref_no)) {
                $job = \backend\models\Job::findOne($po->ref_no);
                if ($job) {
                    if ($job->quotation && !empty($job->quotation->quotation_no)) {
                        return $job->quotation->quotation_no;
                    }
                    if (!empty($job->job_no)) {
                        return $job->job_no;
                    }
                }
                $quotation = \backend\models\Quotation::findOne($po->ref_no);
                if ($quotation && !empty($quotation->quotation_no)) {
                    return $quotation->quotation_no;
                }
            } else {
                return $po->ref_no;
            }
        }

        // 2. ตรวจสอบจาก job_id
        if (!empty($po->job_id)) {
            if (is_numeric($po->job_id)) {
                $job = \backend\models\Job::findOne($po->job_id);
                if ($job) {
                    if ($job->quotation && !empty($job->quotation->quotation_no)) {
                        return $job->quotation->quotation_no;
                    }
                    if (!empty($job->job_no)) {
                        return $job->job_no;
                    }
                }
                $quotation = \backend\models\Quotation::findOne($po->job_id);
                if ($quotation && !empty($quotation->quotation_no)) {
                    return $quotation->quotation_no;
                }
            } else {
                return $po->job_id;
            }
        }

        // 3. ตรวจสอบจาก purch_no เช่น PO-00293-QT26-00084.108
        if (!empty($po->purch_no) && preg_match('/-(QT[A-Za-z0-9-]+)(?:\.|$)/i', $po->purch_no, $match)) {
            return $match[1];
        }

        return '';
    }

    /**
     * แปลงคำอธิบายรายการ (description) ให้แสดงเลขอ้างอิง QT ที่ถูกต้อง
     */
    public static function resolveLineDescription($desc)
    {
        if (empty($desc)) return $desc;

        if (preg_match('/เลขที่:\s*(NPR\d+)/', $desc, $m)) {
            $npr = \backend\models\PurchaseMaster::findOne(['docnum' => $m[1]]);
            if ($npr) {
                $jobRef = self::resolveNonePrJobRef($npr);
                if (!empty($jobRef)) {
                    if (preg_match('/(อ้างอิง\s*QT:\s*)([^\)]+)/u', $desc, $qm)) {
                        if ($qm[2] !== $jobRef) {
                            $desc = preg_replace('/(อ้างอิง\s*QT:\s*)([^\)]+)/u', '${1}' . $jobRef, $desc);
                        }
                    } else {
                        $desc .= ' (อ้างอิง QT: ' . $jobRef . ')';
                    }
                }
            }
        } elseif (preg_match('/เลขที่:\s*(PO-[^\s\(]+)/', $desc, $m)) {
            $po = \backend\models\Purch::findOne(['purch_no' => $m[1]]);
            if ($po) {
                $jobRef = self::resolvePoJobRef($po);
                if (!empty($jobRef)) {
                    if (preg_match('/(อ้างอิง\s*QT:\s*)([^\)]+)/u', $desc, $qm)) {
                        if ($qm[2] !== $jobRef) {
                            $desc = preg_replace('/(อ้างอิง\s*QT:\s*)([^\)]+)/u', '${1}' . $jobRef, $desc);
                        }
                    } else {
                        $desc .= ' (อ้างอิง QT: ' . $jobRef . ')';
                    }
                }
            }
        }

        return $desc;
    }

    /**
     * ตรวจสอบสิทธิ์ว่าผู้ใช้มีสิทธิ์อนุมัติ Payment Advance หรือไม่
     * โดยอิงจาก Permission Matrix (user_module_permission -> appr_pre_advance = 1) เท่านั้น
     */
    public static function canApprovePreAdvance($userId = null, $companyId = null, $model = null)
    {
        $userId = $userId ?: (Yii::$app->user ? Yii::$app->user->id : null);
        if (empty($userId)) {
            return false;
        }

        // อนุญาต mcoadmin
        $username = strtolower(\backend\models\User::findName($userId));
        if ($username === 'mcoadmin') {
            return true;
        }

        // กำหนด company_id
        if ($companyId === null) {
            if ($model && !empty($model->company_id)) {
                $companyId = (int)$model->company_id;
            } else {
                $companyId = Yii::$app->session ? Yii::$app->session->get('company_id') : null;
            }
        }

        // ตรวจสอบใน Permission Matrix (ตาราง user_module_permission)
        $query = \common\models\UserModulePermission::find()
            ->where([
                'user_id' => $userId,
                'module_key' => 'appr_pre_advance',
                'permission_type' => \common\models\UserModulePermission::PERM_MANAGE, // '1'
            ]);

        if (!empty($companyId) && $companyId != 100) {
            $query->andWhere(['company_id' => $companyId]);
        }

        return $query->exists();
    }
}
