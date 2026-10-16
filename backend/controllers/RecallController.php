<?php

namespace backend\controllers;

use Yii;
use backend\models\Purch;
use backend\models\PurchReq;
use backend\models\PurchaseMaster;
use backend\models\Quotation;
use backend\models\PettyCashVoucher;
use backend\models\PettyCashAdvance;
use backend\models\PreAdvance;
use yii\data\ArrayDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\behaviors\ActionLogBehavior;

/**
 * RecallController manages recall requests across all approval modules.
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
        PurchReq::ensureRecallColumns();
        PurchaseMaster::ensureRecallColumns();
        Quotation::ensureRecallColumns();
        PettyCashVoucher::ensureRecallColumns();
        PettyCashAdvance::ensureRecallColumns();
        PreAdvance::ensureRecallColumns();

        // RBAC check: allow user if admin or has any approval privilege
        $canApprove = \backend\models\User::isUserAdmin()
            || Yii::$app->user->can('purch/approve')
            || Yii::$app->user->can('purchreq/approve')
            || Yii::$app->user->can('purchasemaster/approve')
            || Yii::$app->user->can('quotation/approve')
            || Yii::$app->user->can('pettycashvoucher/approve')
            || Yii::$app->user->can('approve-advance')
            || Yii::$app->user->can('pre_advance_approver')
            || Yii::$app->user->can('approver')
            || Yii::$app->user->can('recall/index');

        if (!$canApprove) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าถึงหน้ารายการคำร้องขอ Recall');
        }

        return parent::beforeAction($action);
    }

    /**
     * Display list of all pending recall requests
     * @return string
     */
    public function actionIndex()
    {
        $type = Yii::$app->request->get('type', 'all');
        $items = [];
        $companyId = Yii::$app->session->get('company_id');

        // 1. PO (Purch)
        if ($type === 'all' || $type === 'purch') {
            $q = Purch::find()->where(['is_recall' => 1]);
            if (!empty($companyId) && $companyId != 100) $q->andWhere(['company_id' => $companyId]);
            foreach ($q->orderBy(['recall_at' => SORT_DESC])->all() as $po) {
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

        // 2. PR (PurchReq)
        if ($type === 'all' || $type === 'purchreq') {
            $q = PurchReq::find()->where(['is_recall' => 1]);
            if (!empty($companyId) && $companyId != 100) $q->andWhere(['company_id' => $companyId]);
            foreach ($q->orderBy(['recall_at' => SORT_DESC])->all() as $pr) {
                $items[] = [
                    'id' => $pr->id,
                    'type' => 'purchreq',
                    'type_label' => 'ใบขอซื้อ (PR)',
                    'type_badge' => 'badge-info',
                    'doc_no' => $pr->purch_req_no,
                    'doc_date' => $pr->purch_req_date,
                    'party_name' => $pr->vendor_name ?: ($pr->vendor ? $pr->vendor->name : '-'),
                    'amount' => $pr->net_amount,
                    'recall_reason' => $pr->recall_reason,
                    'recall_by' => $pr->recall_by,
                    'recall_by_name' => \backend\models\User::findEmployeeNameByUserId($pr->recall_by),
                    'recall_at' => $pr->recall_at,
                    'view_url' => ['purchreq/view', 'id' => $pr->id],
                    'raw_model' => $pr,
                ];
            }
        }

        // 3. PurchaseMaster (None PR)
        if ($type === 'all' || $type === 'purchasemaster') {
            $q = PurchaseMaster::find()->where(['is_recall' => 1]);
            if (!empty($companyId) && $companyId != 100) $q->andWhere(['company_id' => $companyId]);
            foreach ($q->orderBy(['recall_at' => SORT_DESC])->all() as $pm) {
                $items[] = [
                    'id' => $pm->id,
                    'type' => 'purchasemaster',
                    'type_label' => 'บันทึกซื้อ None PR',
                    'type_badge' => 'badge-secondary',
                    'doc_no' => $pm->docnum,
                    'doc_date' => $pm->docdat,
                    'party_name' => $pm->supnam ?: '-',
                    'amount' => $pm->total_amount,
                    'recall_reason' => $pm->recall_reason,
                    'recall_by' => $pm->recall_by,
                    'recall_by_name' => \backend\models\User::findEmployeeNameByUserId($pm->recall_by),
                    'recall_at' => $pm->recall_at,
                    'view_url' => ['purchasemaster/view', 'id' => $pm->id],
                    'raw_model' => $pm,
                ];
            }
        }

        // 4. Quotation
        if ($type === 'all' || $type === 'quotation') {
            $q = Quotation::find()->where(['is_recall' => 1]);
            foreach ($q->orderBy(['recall_at' => SORT_DESC])->all() as $qt) {
                $items[] = [
                    'id' => $qt->id,
                    'type' => 'quotation',
                    'type_label' => 'ใบเสนอราคา',
                    'type_badge' => 'badge-success',
                    'doc_no' => $qt->quotation_no,
                    'doc_date' => $qt->quotation_date,
                    'party_name' => $qt->customer_name ?: ($qt->customer ? $qt->customer->name : '-'),
                    'amount' => $qt->total_amount,
                    'recall_reason' => $qt->recall_reason,
                    'recall_by' => $qt->recall_by,
                    'recall_by_name' => \backend\models\User::findEmployeeNameByUserId($qt->recall_by),
                    'recall_at' => $qt->recall_at,
                    'view_url' => ['quotation/view', 'id' => $qt->id],
                    'raw_model' => $qt,
                ];
            }
        }

        // 5. PreAdvance
        if ($type === 'all' || $type === 'preadvance') {
            $q = PreAdvance::find()->where(['is_recall' => 1]);
            if (!empty($companyId) && $companyId != 100) $q->andWhere(['company_id' => $companyId]);
            foreach ($q->orderBy(['recall_at' => SORT_DESC])->all() as $pa) {
                $items[] = [
                    'id' => $pa->id,
                    'type' => 'preadvance',
                    'type_label' => 'เงินทดรองจ่าย (Payment Advance)',
                    'type_badge' => 'badge-warning',
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

        // 6. Petty Cash Voucher (PCV)
        if ($type === 'all' || $type === 'pettycashvoucher') {
            $q = PettyCashVoucher::find()->where(['is_recall' => 1]);
            foreach ($q->orderBy(['recall_at' => SORT_DESC])->all() as $pcv) {
                $items[] = [
                    'id' => $pcv->id,
                    'type' => 'pettycashvoucher',
                    'type_label' => 'ใบสำคัญจ่ายเงินสดย่อย (PCV)',
                    'type_badge' => 'badge-dark',
                    'doc_no' => $pcv->pcv_no,
                    'doc_date' => $pcv->date,
                    'party_name' => $pcv->name ?: '-',
                    'amount' => $pcv->amount,
                    'recall_reason' => $pcv->recall_reason,
                    'recall_by' => $pcv->recall_by,
                    'recall_by_name' => \backend\models\User::findEmployeeNameByUserId($pcv->recall_by),
                    'recall_at' => $pcv->recall_at,
                    'view_url' => ['petty-cash-voucher/view', 'id' => $pcv->id],
                    'raw_model' => $pcv,
                ];
            }
        }

        // 7. Petty Cash Advance
        if ($type === 'all' || $type === 'pettycashadvance') {
            $q = PettyCashAdvance::find()->where(['is_recall' => 1]);
            if (!empty($companyId) && $companyId != 100) $q->andWhere(['company_id' => $companyId]);
            foreach ($q->orderBy(['recall_at' => SORT_DESC])->all() as $pca) {
                $items[] = [
                    'id' => $pca->id,
                    'type' => 'pettycashadvance',
                    'type_label' => 'ใบเบิกเงินทดแทนสดย่อย',
                    'type_badge' => 'badge-info',
                    'doc_no' => $pca->advance_no,
                    'doc_date' => $pca->request_date,
                    'party_name' => $pca->employee ? ($pca->employee->fname . ' ' . $pca->employee->lname) : '-',
                    'amount' => $pca->amount,
                    'recall_reason' => $pca->recall_reason,
                    'recall_by' => $pca->recall_by,
                    'recall_by_name' => \backend\models\User::findEmployeeNameByUserId($pca->recall_by),
                    'recall_at' => $pca->recall_at,
                    'view_url' => ['petty-cash-advance/view', 'id' => $pca->id],
                    'raw_model' => $pca,
                ];
            }
        }

        // Sort items by recall_at desc
        usort($items, function ($a, $b) {
            return strtotime($b['recall_at']) - strtotime($a['recall_at']);
        });

        // Counts
        $countPurch = (int)Purch::find()->where(['is_recall' => 1])->count();
        $countPurchReq = (int)PurchReq::find()->where(['is_recall' => 1])->count();
        $countPurchaseMaster = (int)PurchaseMaster::find()->where(['is_recall' => 1])->count();
        $countQuotation = (int)Quotation::find()->where(['is_recall' => 1])->count();
        $countPreAdvance = (int)PreAdvance::find()->where(['is_recall' => 1])->count();
        $countPettyCashVoucher = (int)PettyCashVoucher::find()->where(['is_recall' => 1])->count();
        $countPettyCashAdvance = (int)PettyCashAdvance::find()->where(['is_recall' => 1])->count();
        $countTotal = $countPurch + $countPurchReq + $countPurchaseMaster + $countQuotation + $countPreAdvance + $countPettyCashVoucher + $countPettyCashAdvance;

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
            'countPurchReq' => $countPurchReq,
            'countPurchaseMaster' => $countPurchaseMaster,
            'countQuotation' => $countQuotation,
            'countPreAdvance' => $countPreAdvance,
            'countPettyCashVoucher' => $countPettyCashVoucher,
            'countPettyCashAdvance' => $countPettyCashAdvance,
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
        switch ($type) {
            case 'purch':
                $model = Purch::findOne($id);
                if (!$model) throw new NotFoundHttpException('ไม่พบเอกสารใบสั่งซื้อ');
                return $this->renderAjax('_view_purch', ['model' => $model]);

            case 'purchreq':
                $model = PurchReq::findOne($id);
                if (!$model) throw new NotFoundHttpException('ไม่พบเอกสารใบขอซื้อ');
                return $this->renderAjax('_view_purchreq', ['model' => $model]);

            case 'purchasemaster':
                $model = PurchaseMaster::findOne($id);
                if (!$model) throw new NotFoundHttpException('ไม่พบเอกสารบันทึกการซื้อ');
                return $this->renderAjax('_view_purchasemaster', ['model' => $model]);

            case 'quotation':
                $model = Quotation::findOne($id);
                if (!$model) throw new NotFoundHttpException('ไม่พบเอกสารใบเสนอราคา');
                return $this->renderAjax('_view_quotation', ['model' => $model]);

            case 'preadvance':
                $model = PreAdvance::findOne($id);
                if (!$model) throw new NotFoundHttpException('ไม่พบเอกสาร Pre-Advance');
                return $this->renderAjax('_view_preadvance', ['model' => $model]);

            case 'pettycashvoucher':
                $model = PettyCashVoucher::findOne($id);
                if (!$model) throw new NotFoundHttpException('ไม่พบเอกสารใบสำคัญจ่ายเงินสดย่อย');
                return $this->renderAjax('_view_pettycashvoucher', ['model' => $model]);

            case 'pettycashadvance':
                $model = PettyCashAdvance::findOne($id);
                if (!$model) throw new NotFoundHttpException('ไม่พบเอกสารใบเบิกเงินทดแทน');
                return $this->renderAjax('_view_pettycashadvance', ['model' => $model]);
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
        switch ($type) {
            case 'purch':
                if (!Yii::$app->user->can('purch/approve') && !\backend\models\User::isUserAdmin()) {
                    throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการอนุมัติ Recall ใบสั่งซื้อ');
                }
                $model = Purch::findOne($id);
                if ($model && $model->is_recall == 1) {
                    $oldApproveBy = $model->approve_by;
                    $model->approve_status = Purch::APPROVE_STATUS_PENDING;
                    $model->approve_by = null;
                    $model->approve_date = null;
                    $model->is_recall = 0;
                    if ($model->save(false)) {
                        \backend\models\ActionLogModel::logModelAction('APPROVE_RECALL', $model, ['previous_approve_by' => $oldApproveBy, 'approved_recall_by' => Yii::$app->user->id], \backend\models\ActionLogModel::STATUS_SUCCESS, 'อนุมัติการ Recall เอกสารใบสั่งซื้อ ' . $model->purch_no);
                        Yii::$app->session->setFlash('success', 'อนุมัติ Recall ใบสั่งซื้อ ' . $model->purch_no . ' เรียบร้อยแล้ว');
                    }
                }
                break;

            case 'purchreq':
                if (!Yii::$app->user->can('purchreq/approve') && !\backend\models\User::isUserAdmin()) {
                    throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการอนุมัติ Recall ใบขอซื้อ');
                }
                $model = PurchReq::findOne($id);
                if ($model && $model->is_recall == 1) {
                    $oldApproveBy = $model->approve_by;
                    $model->approve_status = PurchReq::APPROVE_STATUS_PENDING;
                    $model->approve_by = null;
                    $model->approve_date = null;
                    $model->is_recall = 0;
                    if ($model->save(false)) {
                        \backend\models\ActionLogModel::logModelAction('APPROVE_RECALL', $model, ['previous_approve_by' => $oldApproveBy, 'approved_recall_by' => Yii::$app->user->id], \backend\models\ActionLogModel::STATUS_SUCCESS, 'อนุมัติการ Recall เอกสารใบขอซื้อ ' . $model->purch_req_no);
                        Yii::$app->session->setFlash('success', 'อนุมัติ Recall ใบขอซื้อ ' . $model->purch_req_no . ' เรียบร้อยแล้ว');
                    }
                }
                break;

            case 'purchasemaster':
                if (!Yii::$app->user->can('purchasemaster/approve') && !\backend\models\User::isUserAdmin()) {
                    throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการอนุมัติ Recall บันทึกการซื้อ');
                }
                $model = PurchaseMaster::findOne($id);
                if ($model && $model->is_recall == 1) {
                    $model->approve_status = PurchaseMaster::APPROVE_STATUS_PENDING;
                    $model->is_recall = 0;
                    if ($model->save(false)) {
                        \backend\models\ActionLogModel::logModelAction('APPROVE_RECALL', $model, ['approved_recall_by' => Yii::$app->user->id], \backend\models\ActionLogModel::STATUS_SUCCESS, 'อนุมัติการ Recall บันทึกการซื้อ ' . $model->docnum);
                        Yii::$app->session->setFlash('success', 'อนุมัติ Recall บันทึกการซื้อ ' . $model->docnum . ' เรียบร้อยแล้ว');
                    }
                }
                break;

            case 'quotation':
                if (!Yii::$app->user->can('quotation/approve') && !\backend\models\User::isUserAdmin()) {
                    throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการอนุมัติ Recall ใบเสนอราคา');
                }
                $model = Quotation::findOne($id);
                if ($model && $model->is_recall == 1) {
                    $oldApproveBy = $model->approve_by;
                    $model->approve_status = Quotation::APPROVE_STATUS_PENDING;
                    $model->approve_by = null;
                    $model->is_recall = 0;
                    if ($model->save(false)) {
                        \backend\models\ActionLogModel::logModelAction('APPROVE_RECALL', $model, ['previous_approve_by' => $oldApproveBy, 'approved_recall_by' => Yii::$app->user->id], \backend\models\ActionLogModel::STATUS_SUCCESS, 'อนุมัติการ Recall ใบเสนอราคา ' . $model->quotation_no);
                        Yii::$app->session->setFlash('success', 'อนุมัติ Recall ใบเสนอราคา ' . $model->quotation_no . ' เรียบร้อยแล้ว');
                    }
                }
                break;

            case 'preadvance':
                $isApprover = \backend\models\User::isUserAdmin() || Yii::$app->user->can('pre_advance_approver') || Yii::$app->user->can('approver');
                if (!$isApprover) {
                    throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการอนุมัติ Recall เอกสาร Pre-Advance');
                }
                $model = PreAdvance::findOne($id);
                if ($model && $model->is_recall == 1) {
                    $oldApproveBy = $model->approved_by;
                    $model->approved_by = null;
                    $model->approved_at = null;
                    $model->is_recall = 0;
                    if ($model->save(false)) {
                        \backend\models\ActionLogModel::logModelAction('APPROVE_RECALL', $model, ['previous_approved_by' => $oldApproveBy, 'approved_recall_by' => Yii::$app->user->id], \backend\models\ActionLogModel::STATUS_SUCCESS, 'อนุมัติการ Recall เอกสาร Pre-Advance ' . $model->pre_advance_no);
                        Yii::$app->session->setFlash('success', 'อนุมัติ Recall Pre-Advance ' . $model->pre_advance_no . ' เรียบร้อยแล้ว');
                    }
                }
                break;

            case 'pettycashvoucher':
                if (!Yii::$app->user->can('pettycashvoucher/approve') && !\backend\models\User::isUserAdmin()) {
                    throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการอนุมัติ Recall ใบสำคัญจ่ายเงินสดย่อย');
                }
                $model = PettyCashVoucher::findOne($id);
                if ($model && $model->is_recall == 1) {
                    $oldApproveBy = $model->approved_by;
                    $model->approve_status = 0;
                    $model->approved_by = null;
                    $model->approved_date = null;
                    $model->is_recall = 0;
                    if ($model->save(false)) {
                        \backend\models\ActionLogModel::logModelAction('APPROVE_RECALL', $model, ['previous_approved_by' => $oldApproveBy, 'approved_recall_by' => Yii::$app->user->id], \backend\models\ActionLogModel::STATUS_SUCCESS, 'อนุมัติการ Recall ใบสำคัญจ่ายเงินสดย่อย ' . $model->pcv_no);
                        Yii::$app->session->setFlash('success', 'อนุมัติ Recall ใบสำคัญจ่ายเงินสดย่อย ' . $model->pcv_no . ' เรียบร้อยแล้ว');
                    }
                }
                break;

            case 'pettycashadvance':
                if (!Yii::$app->user->can('approve-advance') && !\backend\models\User::isUserAdmin()) {
                    throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการอนุมัติ Recall ใบเบิกเงินทดแทน');
                }
                $model = PettyCashAdvance::findOne($id);
                if ($model && $model->is_recall == 1) {
                    $oldApproveBy = $model->approved_by;
                    $model->status = PettyCashAdvance::STATUS_PENDING;
                    $model->approved_by = null;
                    $model->is_recall = 0;
                    if ($model->save(false)) {
                        \backend\models\ActionLogModel::logModelAction('APPROVE_RECALL', $model, ['previous_approved_by' => $oldApproveBy, 'approved_recall_by' => Yii::$app->user->id], \backend\models\ActionLogModel::STATUS_SUCCESS, 'อนุมัติการ Recall ใบเบิกเงินทดแทน ' . $model->advance_no);
                        Yii::$app->session->setFlash('success', 'อนุมัติ Recall ใบเบิกเงินทดแทน ' . $model->advance_no . ' เรียบร้อยแล้ว');
                    }
                }
                break;
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

        $modelClass = null;
        $docNoAttr = '';
        $typeLabel = '';

        switch ($type) {
            case 'purch':
                $modelClass = Purch::class;
                $docNoAttr = 'purch_no';
                $typeLabel = 'ใบสั่งซื้อ';
                break;
            case 'purchreq':
                $modelClass = PurchReq::class;
                $docNoAttr = 'purch_req_no';
                $typeLabel = 'ใบขอซื้อ';
                break;
            case 'purchasemaster':
                $modelClass = PurchaseMaster::class;
                $docNoAttr = 'docnum';
                $typeLabel = 'บันทึกการซื้อ None PR';
                break;
            case 'quotation':
                $modelClass = Quotation::class;
                $docNoAttr = 'quotation_no';
                $typeLabel = 'ใบเสนอราคา';
                break;
            case 'preadvance':
                $modelClass = PreAdvance::class;
                $docNoAttr = 'pre_advance_no';
                $typeLabel = 'Pre-Advance';
                break;
            case 'pettycashvoucher':
                $modelClass = PettyCashVoucher::class;
                $docNoAttr = 'pcv_no';
                $typeLabel = 'ใบสำคัญจ่ายเงินสดย่อย';
                break;
            case 'pettycashadvance':
                $modelClass = PettyCashAdvance::class;
                $docNoAttr = 'advance_no';
                $typeLabel = 'ใบเบิกเงินทดแทน';
                break;
        }

        if ($modelClass) {
            $model = $modelClass::findOne($id);
            if ($model && $model->is_recall == 1) {
                $docNo = $model->$docNoAttr ?? $id;
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
                        'ปฏิเสธคำร้องขอ Recall ' . $typeLabel . ' ' . $docNo . ': ' . $rejectReason
                    );
                    Yii::$app->session->setFlash('info', 'ปฏิเสธคำร้องขอ Recall ' . $typeLabel . ' ' . $docNo . ' เรียบร้อยแล้ว');
                }
            }
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * Static helper to get total pending recall count across all models
     * @return int
     */
    public static function getPendingRecallCount()
    {
        try {
            $countPurch = Purch::find()->where(['is_recall' => 1])->count();
            $countPurchReq = PurchReq::find()->where(['is_recall' => 1])->count();
            $countPurchaseMaster = PurchaseMaster::find()->where(['is_recall' => 1])->count();
            $countQuotation = Quotation::find()->where(['is_recall' => 1])->count();
            $countPreAdvance = PreAdvance::find()->where(['is_recall' => 1])->count();
            $countPettyCashVoucher = PettyCashVoucher::find()->where(['is_recall' => 1])->count();
            $countPettyCashAdvance = PettyCashAdvance::find()->where(['is_recall' => 1])->count();

            return (int)($countPurch + $countPurchReq + $countPurchaseMaster + $countQuotation + $countPreAdvance + $countPettyCashVoucher + $countPettyCashAdvance);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
