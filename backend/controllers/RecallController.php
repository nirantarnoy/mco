<?php

namespace backend\controllers;

use Yii;
use backend\models\Purch;
use backend\models\PreAdvance;
use yii\data\ArrayDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\behaviors\ActionLogBehavior;

/**
 * RecallController manages recall requests for Purchase Orders and Pre-Advances.
 */
class RecallController extends BaseController
{
    public function behaviors()
    {
        return [
            'actionLog' => [
                'class' => ActionLogBehavior::class,
                'actions' => ['approve', 'reject'],
            ],
        ];
    }

    public function beforeAction($action)
    {
        // Check database schema
        Purch::ensureRecallColumns();
        PreAdvance::ensureRecallColumns();

        // RBAC check: allow user if they can approve PO or PreAdvance, or admin
        $isPoApprover = Yii::$app->user->can('purch/approve');
        $isPreAdvanceApprover = \backend\models\User::isUserAdmin() || Yii::$app->user->can('pre_advance_approver') || Yii::$app->user->can('approver');
        $isAdmin = \backend\models\User::isUserAdmin();

        if (!$isPoApprover && !$isPreAdvanceApprover && !$isAdmin) {
            // Also check if user has recall/index permission
            if (!Yii::$app->user->can('recall/index')) {
                throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าถึงหน้ารายการคำร้องขอ Recall');
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * Display list of all pending recall requests
     * @return string
     */
    public function actionIndex()
    {
        $type = Yii::$app->request->get('type', 'all'); // 'all', 'purch', 'preadvance'
        $items = [];

        // 1. Fetch Purchase Orders with is_recall = 1
        if ($type === 'all' || $type === 'purch') {
            $purchQuery = Purch::find()->where(['is_recall' => 1]);
            // If company filter exists
            $companyId = Yii::$app->session->get('company_id');
            if (!empty($companyId) && $companyId != 100) {
                $purchQuery->andWhere(['company_id' => $companyId]);
            }
            $purchases = $purchQuery->orderBy(['recall_at' => SORT_DESC])->all();

            foreach ($purchases as $po) {
                $items[] = [
                    'id' => $po->id,
                    'type' => 'purch',
                    'type_label' => 'ใบสั่งซื้อ (PO)',
                    'type_badge' => 'badge-primary',
                    'doc_no' => $po->purch_no,
                    'doc_date' => $po->purch_date,
                    'party_name' => $po->vendor_name ?: ($po->vendor ? $po->vendor->name : '-'),
                    'amount' => $po->net_amount,
                    'recall_reason' => $po->recall_reason,
                    'recall_by' => $po->recall_by,
                    'recall_by_name' => \backend\models\User::findEmployeeNameByUserId($po->recall_by),
                    'recall_at' => $po->recall_at,
                    'view_url' => ['purch/view', 'id' => $po->id],
                    'raw_model' => $po,
                ];
            }
        }

        // 2. Fetch Pre-Advances with is_recall = 1
        if ($type === 'all' || $type === 'preadvance') {
            $preAdvQuery = PreAdvance::find()->where(['is_recall' => 1]);
            $companyId = Yii::$app->session->get('company_id');
            if (!empty($companyId) && $companyId != 100) {
                $preAdvQuery->andWhere(['company_id' => $companyId]);
            }
            $preAdvances = $preAdvQuery->orderBy(['recall_at' => SORT_DESC])->all();

            foreach ($preAdvances as $pa) {
                $items[] = [
                    'id' => $pa->id,
                    'type' => 'preadvance',
                    'type_label' => 'เงินทดรองจ่าย (Payment Advance)',
                    'type_badge' => 'badge-info',
                    'doc_no' => $pa->pre_advance_no,
                    'doc_date' => $pa->trans_date,
                    'party_name' => $pa->recipient_name ?: ($pa->vendor ? $pa->vendor->name : '-'),
                    'amount' => $pa->amount,
                    'recall_reason' => $pa->recall_reason,
                    'recall_by' => $pa->recall_by,
                    'recall_by_name' => \backend\models\User::findEmployeeNameByUserId($pa->recall_by),
                    'recall_at' => $pa->recall_at,
                    'view_url' => ['pre-advance/view', 'id' => $pa->id],
                    'raw_model' => $pa,
                ];
            }
        }

        // Sort items by recall_at desc
        usort($items, function ($a, $b) {
            return strtotime($b['recall_at']) - strtotime($a['recall_at']);
        });

        // Count for badges
        $countPurch = Purch::find()->where(['is_recall' => 1])->count();
        $countPreAdvance = PreAdvance::find()->where(['is_recall' => 1])->count();
        $countTotal = $countPurch + $countPreAdvance;

        $dataProvider = new ArrayDataProvider([
            'allModels' => $items,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'type' => $type,
            'countTotal' => $countTotal,
            'countPurch' => $countPurch,
            'countPreAdvance' => $countPreAdvance,
        ]);
    }

    /**
     * AJAX View details modal
     * @param string $type
     * @param int $id
     * @return string
     */
    public function actionViewAjax($type, $id)
    {
        if ($type === 'purch') {
            $model = Purch::findOne($id);
            if (!$model) throw new NotFoundHttpException('ไม่พบเอกสารใบสั่งซื้อ');
            return $this->renderAjax('_view_purch', [
                'model' => $model,
            ]);
        } elseif ($type === 'preadvance') {
            $model = PreAdvance::findOne($id);
            if (!$model) throw new NotFoundHttpException('ไม่พบเอกสาร Pre-Advance');
            return $this->renderAjax('_view_preadvance', [
                'model' => $model,
            ]);
        }

        throw new NotFoundHttpException('ประเภทเอกสารไม่ถูกต้อง');
    }

    /**
     * Quick Approve Recall
     * @param string $type
     * @param int $id
     * @return Response
     */
    public function actionApprove($type, $id)
    {
        if ($type === 'purch') {
            if (!Yii::$app->user->can('purch/approve') && !\backend\models\User::isUserAdmin()) {
                throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการอนุมัติ Recall เอกสารใบสั่งซื้อ');
            }
            $model = Purch::findOne($id);
            if ($model && $model->is_recall == 1) {
                $oldApproveBy = $model->approve_by;
                $oldApproveDate = $model->approve_date;
                $recallReason = $model->recall_reason;

                $model->approve_status = Purch::APPROVE_STATUS_PENDING;
                $model->approve_by = null;
                $model->approve_date = null;
                $model->is_recall = 0;

                if ($model->save(false)) {
                    \backend\models\ActionLogModel::logModelAction(
                        'APPROVE_RECALL',
                        $model,
                        [
                            'previous_approve_by' => $oldApproveBy,
                            'previous_approve_date' => $oldApproveDate,
                            'recall_reason' => $recallReason,
                            'approved_recall_by' => Yii::$app->user->id,
                        ],
                        \backend\models\ActionLogModel::STATUS_SUCCESS,
                        'อนุมัติการ Recall เอกสารใบสั่งซื้อ ' . $model->purch_no . ' ให้กลับมาแก้ไขได้'
                    );
                    Yii::$app->session->setFlash('success', 'อนุมัติการ Recall ใบสั่งซื้อ ' . $model->purch_no . ' เรียบร้อยแล้ว');
                }
            }
        } elseif ($type === 'preadvance') {
            $isApprover = \backend\models\User::isUserAdmin() || Yii::$app->user->can('pre_advance_approver') || Yii::$app->user->can('approver');
            if (!$isApprover) {
                throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการอนุมัติ Recall เอกสาร Pre-Advance');
            }
            $model = PreAdvance::findOne($id);
            if ($model && $model->is_recall == 1) {
                $oldApproveBy = $model->approved_by;
                $oldApproveAt = $model->approved_at;
                $recallReason = $model->recall_reason;

                $model->approved_by = null;
                $model->approved_at = null;
                $model->is_recall = 0;

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
                        'อนุมัติการ Recall เอกสาร Pre-Advance ' . $model->pre_advance_no . ' ให้กลับมาแก้ไขได้'
                    );
                    Yii::$app->session->setFlash('success', 'อนุมัติการ Recall Pre-Advance ' . $model->pre_advance_no . ' เรียบร้อยแล้ว');
                }
            }
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * Quick Reject Recall
     * @param string $type
     * @param int $id
     * @return Response
     */
    public function actionReject($type, $id)
    {
        $rejectReason = Yii::$app->request->post('reject_reason', 'ไม่อนุมัติคำร้องขอ Recall');

        if ($type === 'purch') {
            if (!Yii::$app->user->can('purch/approve') && !\backend\models\User::isUserAdmin()) {
                throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการปฏิเสธ Recall เอกสารใบสั่งซื้อ');
            }
            $model = Purch::findOne($id);
            if ($model && $model->is_recall == 1) {
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
                        'ปฏิเสธคำร้องขอ Recall เอกสารใบสั่งซื้อ ' . $model->purch_no . ': ' . $rejectReason
                    );
                    Yii::$app->session->setFlash('info', 'ปฏิเสธคำร้องขอ Recall ใบสั่งซื้อ ' . $model->purch_no . ' แล้ว');
                }
            }
        } elseif ($type === 'preadvance') {
            $isApprover = \backend\models\User::isUserAdmin() || Yii::$app->user->can('pre_advance_approver') || Yii::$app->user->can('approver');
            if (!$isApprover) {
                throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการปฏิเสธ Recall เอกสาร Pre-Advance');
            }
            $model = PreAdvance::findOne($id);
            if ($model && $model->is_recall == 1) {
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
                        'ปฏิเสธคำร้องขอ Recall เอกสาร Pre-Advance ' . $model->pre_advance_no . ': ' . $rejectReason
                    );
                    Yii::$app->session->setFlash('info', 'ปฏิเสธคำร้องขอ Recall Pre-Advance ' . $model->pre_advance_no . ' แล้ว');
                }
            }
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * Static helper to get total pending recall count
     * @return int
     */
    public static function getPendingRecallCount()
    {
        try {
            $countPurch = Purch::find()->where(['is_recall' => 1])->count();
            $countPreAdvance = PreAdvance::find()->where(['is_recall' => 1])->count();
            return (int)($countPurch + $countPreAdvance);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
