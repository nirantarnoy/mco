<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\PurchaseMaster $model */

$this->title = $model->docnum;
$this->params['breadcrumbs'][] = ['label' => 'บันทึกการซื้อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="purchase-master-view">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ใบซื้อเลขที่: <?= Html::encode($this->title) ?> <?= $model->rev > 0 ? '(ครั้งที่แก้ไข: ' . $model->rev . ')' : '' ?></h3>
            <div class="card-tools">
                <?php if ($model->status != \backend\models\PurchaseMaster::STATUS_CANCELLED): ?>
                    <?php if ($model->approve_status == \backend\models\PurchaseMaster::APPROVE_STATUS_PENDING || $model->approve_status == \backend\models\PurchaseMaster::APPROVE_STATUS_RETURNED): ?>
                        <?php if (\Yii::$app->user->can('purchasemaster/approve') || \backend\models\User::isUserAdmin()): ?>
                            <?= Html::a('<i class="fas fa-check"></i> อนุมัติ', ['approve', 'id' => $model->id], [
                                'class' => 'btn btn-success btn-sm',
                                'data' => [
                                    'confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบซื้อนี้?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('purchasemaster/reject') || \Yii::$app->user->can('purchasemaster/approve') || \backend\models\User::isUserAdmin()): ?>
                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#return-modal" data-bs-toggle="modal" data-bs-target="#return-modal">
                                <i class="fas fa-undo-alt"></i> ส่งกลับแก้ไข
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#reject-modal" data-bs-toggle="modal" data-bs-target="#reject-modal">
                                <i class="fas fa-times-circle"></i> ไม่อนุมัติ
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($model->approve_status == \backend\models\PurchaseMaster::APPROVE_STATUS_APPROVED): ?>
                        <?php if ($model->is_recall != 1): ?>
                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#recall-modal" data-bs-toggle="modal" data-bs-target="#recall-modal">
                                <i class="fas fa-undo-alt"></i> ร้องขอแก้ไข (Recall)
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($model->is_recall == 1): ?>
                        <?php if (\Yii::$app->user->can('purchasemaster/approve') || \backend\models\User::isUserAdmin()): ?>
                            <?= Html::a('<i class="fas fa-check"></i> อนุมัติ Recall', ['approve-recall', 'id' => $model->id], [
                                'class' => 'btn btn-success btn-sm',
                                'data' => [
                                    'confirm' => 'ยืนยันการอนุมัติ Recall? เอกสารนี้จะถูกปลดสถานะอนุมัติและสามารถแก้ไขได้',
                                    'method' => 'post',
                                ],
                            ]) ?>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#reject-recall-modal" data-bs-toggle="modal" data-bs-target="#reject-recall-modal">
                                <i class="fas fa-times"></i> ปฏิเสธ Recall
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?= Html::a('<i class="fas fa-box"></i> รับสินค้าเข้าคลัง', ['receive', 'id' => $model->id], ['class' => 'btn btn-success btn-sm']) ?>
                    <?= Html::a('<i class="fas fa-history"></i> ประวัติรับสินค้า', ['receive-history', 'id' => $model->id], ['class' => 'btn btn-outline-info btn-sm']) ?>
                    <?php if ($model->approve_status != \backend\models\PurchaseMaster::APPROVE_STATUS_REJECTED || \backend\models\User::isUserAdmin()): ?>
                        <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm']) ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?= Html::a('<i class="fas fa-print"></i> พิมพ์เอกสาร (Print)', ['print', 'id' => $model->id], ['class' => 'btn btn-info btn-sm', 'target' => '_blank']) ?>
                <?= Html::a('<i class="fas fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('<i class="fas fa-list"></i> กลับไปรายการ', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">
            <?php if ($model->is_recall == 1): ?>
                <div class="alert alert-warning border border-warning shadow-sm mb-3">
                    <h5><i class="fas fa-exclamation-triangle"></i> เอกสารนี้มีคำร้องขอ Recall เพื่อแก้ไข</h5>
                    <p class="mb-1"><strong>ผู้ร้องขอ:</strong> <?= \backend\models\User::findEmployeeNameByUserId($model->recall_by) ?> (วันที่: <?= $model->recall_at ? date('d/m/Y H:i:s', strtotime($model->recall_at)) : '-' ?>)</p>
                    <p class="mb-0"><strong>เหตุผล:</strong> <?= Html::encode($model->recall_reason) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($model->approve_status == \backend\models\PurchaseMaster::APPROVE_STATUS_RETURNED): ?>
                <div class="alert alert-warning border border-warning shadow-sm mb-3">
                    <h5><i class="fas fa-undo-alt"></i> บันทึกซื้อนี้ถูกส่งกลับเพื่อแก้ไข</h5>
                    <?php if (!empty($model->rejected_by)): ?>
                        <p class="mb-1"><strong>ผู้ส่งกลับแก้ไข:</strong> <?= \backend\models\User::findEmployeeNameByUserId($model->rejected_by) ?> (วันที่: <?= $model->rejected_at ? date('d/m/Y H:i:s', strtotime($model->rejected_at)) : '-' ?>)</p>
                    <?php endif; ?>
                    <?php if (!empty($model->reject_reason)): ?>
                        <p class="mb-0"><strong>รายละเอียดที่ต้องปรับปรุง/แก้ไข:</strong> <?= nl2br(Html::encode($model->reject_reason)) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($model->approve_status == \backend\models\PurchaseMaster::APPROVE_STATUS_REJECTED): ?>
                <div class="alert alert-danger border border-danger shadow-sm mb-3">
                    <h5><i class="fas fa-times-circle"></i> บันทึกซื้อนี้ไม่อนุมัติ (ปิดจบรายการ)</h5>
                    <?php if (!empty($model->rejected_by)): ?>
                        <p class="mb-1"><strong>ผู้ไม่อนุมัติ:</strong> <?= \backend\models\User::findEmployeeNameByUserId($model->rejected_by) ?> (วันที่: <?= $model->rejected_at ? date('d/m/Y H:i:s', strtotime($model->rejected_at)) : '-' ?>)</p>
                    <?php endif; ?>
                    <?php if (!empty($model->reject_reason)): ?>
                        <p class="mb-0"><strong>เหตุผลที่ไม่อนุมัติ:</strong> <?= nl2br(Html::encode($model->reject_reason)) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">ข้อมูลหลัก</h5>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'docnum',
                            [
                                'attribute' => 'rev',
                                'label' => 'ครั้งที่แก้ไข',
                                'value' => function ($model) {
                                    return $model->rev;
                                }
                            ],
                            [
                                'attribute' => 'docdat',
                                'value' => function ($model) {
                                    return date('m/d/Y', strtotime($model->docdat));
                                }
                            ],
                            [
                                'attribute' => 'supcod',
                                'value' => function ($model) {
                                    return \backend\models\Vendor::findCode($model->supcod);
                                }
                            ],
                            [
                                'attribute' => 'supnam',
                                'value' => function ($model) {
                                    return \backend\models\Vendor::findName($model->supcod);
                                }
                            ],
                            [
                                'attribute' => 'job_no',
                                'value' => function ($model) {
                                    return \backend\models\Job::findJobNo($model->job_no);
                                }
                            ],
                            [
                                'attribute' => 'paytrm',
                                'value' => function ($model) {
                                    return \backend\models\Paymentterm::findName($model->paytrm);
                                }
                            ],
                            'duedat:date:วันครบกำหนด',
                            'taxid',
                            'discod',
                        ],
                    ]) ?>
                </div>

                <div class="col-md-6">
                    <h5 class="mb-3">ที่อยู่และข้อมูลอื่นๆ</h5>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'addr01',
                            'addr02',
                            'addr03',
                            'zipcod',
                            'telnum',
                            'orgnum',
                            'refnum',
                            'vatdat:date:วันที่ภาษี',
                        ],
                    ]) ?>
                </div>
            </div>

            <?php
            $model_deposit = \backend\models\PurchNonePrDeposit::find()->where(['purchase_master_id' => $model->id])->one();
            if ($model_deposit):
                $model_deposit_line = \backend\models\PurchNonePrDepositLine::find()->where(['purch_none_pr_deposit_id' => $model_deposit->id])->one();
            ?>
                <h5 class="mt-4 mb-3">ข้อมูลมัดจำ</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th width="25%">วันที่เอกสารมัดจำ</th>
                                <th width="25%">วันที่จ่ายมัดจำ</th>
                                <th class="text-right" width="25%">จำนวนเงินมัดจำ</th>
                                <th class="text-center" width="25%">เอกสารแนบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($model_deposit->trans_date)) ?></td>
                                <td><?= ($model_deposit_line && $model_deposit_line->deposit_date) ? date('d/m/Y', strtotime($model_deposit_line->deposit_date)) : '-' ?></td>
                                <td class="text-right"><?= $model_deposit_line ? Yii::$app->formatter->asDecimal($model_deposit_line->deposit_amount, 2) : '0.00' ?> บาท</td>
                                <td class="text-center">
                                    <?php if ($model_deposit_line && $model_deposit_line->deposit_doc): ?>
                                        <?= Html::a('<i class="fas fa-file-download"></i> ดูเอกสาร', Yii::$app->request->baseUrl . '/uploads/purch_doc/' . $model_deposit_line->deposit_doc, ['target' => '_blank', 'class' => 'btn btn-outline-info btn-sm', 'data-pjax' => '0']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">ไม่มีเอกสารแนบ</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ข้อมูลการรับมัดจำคืน -->
                <h6 class="mt-3 mb-2 font-weight-bold">ข้อมูลการรับมัดจำคืน</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th width="33%">วันที่รับมัดจำคืน</th>
                                <th class="text-right" width="33%">จำนวนเงินมัดจำที่รับคืน</th>
                                <th class="text-center" width="34%">เอกสารแนบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= ($model_deposit_line && $model_deposit_line->receive_date) ? date('d/m/Y', strtotime($model_deposit_line->receive_date)) : '-' ?></td>
                                <td class="text-right"><?= ($model_deposit_line && $model_deposit_line->receive_doc != null && $model_deposit_line->deposit_amount) ? Yii::$app->formatter->asDecimal($model_deposit_line->deposit_amount, 2) : '0.00' ?> บาท</td>
                                <td class="text-center">
                                    <?php if ($model_deposit_line && $model_deposit_line->receive_doc): ?>
                                        <?= Html::a('<i class="fas fa-file-download"></i> ดูเอกสาร', Yii::$app->request->baseUrl . '/uploads/purch_doc/' . $model_deposit_line->receive_doc, ['target' => '_blank', 'class' => 'btn btn-outline-info btn-sm', 'data-pjax' => '0']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">ไม่มีเอกสารแนบ</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <h5 class="mt-4 mb-3">รายละเอียดสินค้า</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">ลำดับ</th>
                        <th width="120">รหัสสินค้า</th>
                        <th>รายละเอียด</th>
                        <th class="text-center" width="90">สั่งซื้อ</th>
                        <th class="text-center" width="90">รับแล้ว</th>
                        <th class="text-center" width="90">ค้างรับ</th>
                        <th class="text-right" width="110">ราคา/หน่วย</th>
                        <th class="text-center" width="80">ส่วนลด</th>
                        <th class="text-right" width="110">จำนวนเงิน</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($model->purchaseDetails)): ?>
                        <?php 
                        $receivedMap = $model->getReceivedQtyPerDetail();
                        foreach ($model->purchaseDetails as $index => $detail): 
                            $productId = \backend\models\PurchaseMaster::findProductIdByStkcod($detail->stkcod);
                            $ordered = (float)$detail->uqnty;
                            $rec = $receivedMap[$detail->id] ?? ($productId ? ($receivedMap['prod_' . $productId] ?? 0) : 0);
                            $rem = max(0, $ordered - $rec);
                        ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= Html::encode($detail->stkcod) ?></td>
                                <td><?= Html::encode($detail->stkdes) ?></td>
                                <td class="text-center"><?= Yii::$app->formatter->asDecimal($ordered, 2) ?></td>
                                <td class="text-center text-success font-weight-bold"><?= Yii::$app->formatter->asDecimal($rec, 2) ?></td>
                                <td class="text-center text-danger font-weight-bold"><?= Yii::$app->formatter->asDecimal($rem, 2) ?></td>
                                <td class="text-right"><?= Yii::$app->formatter->asDecimal($detail->unitpr, 2) ?></td>
                                <td class="text-center"><?= Html::encode($detail->disc) ?></td>
                                <td class="text-right"><?= Yii::$app->formatter->asDecimal($detail->amount, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">ไม่มีรายละเอียดสินค้า</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <h5>หมายเหตุ</h5>
                    <p><?= nl2br(Html::encode($model->remark)) ?></p>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td class="text-right" width="60%"><strong>มูลค่าสินค้า:</strong></td>
                                    <td class="text-right"
                                        width="40%"><?= Yii::$app->formatter->asDecimal($model->vatpr0, 2) ?> บาท
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-right"><strong>VAT (<?= $model->vat_percent ?>%):</strong></td>
                                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($model->vat_amount, 2) ?>
                                        บาท
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-right"><strong>TAX (<?= $model->tax_percent ?>%):</strong></td>
                                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($model->tax_amount, 2) ?>
                                        บาท
                                    </td>
                                </tr>
                                <tr class="table-active">
                                    <td class="text-right"><h5><strong>รวมทั้งสิ้น:</strong></h5></td>
                                    <td class="text-right"><h5>
                                            <strong><?= Yii::$app->formatter->asDecimal($model->total_amount, 2) ?>
                                                บาท</strong></h5></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $preAdvanceRefs = \backend\models\PreAdvanceRef::find()
                ->where(['ref_type' => \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR, 'ref_id' => $model->id])
                ->all();
            ?>
            <h5 class="mt-4 mb-3"><i class="fas fa-file-invoice-dollar text-primary me-2"></i> ข้อมูล Pre-Advance / Pay Advance ที่นำไปใช้งาน</h5>
            <?php if (empty($preAdvanceRefs)): ?>
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i> ยังไม่มีการนำรายการนี้ไปทำ Pre-Advance / Pay Advance
                </div>
            <?php else: ?>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">#</th>
                                <th width="20%">เลขที่ Pre-Advance</th>
                                <th width="15%">วันที่</th>
                                <th width="20%">ผู้รับเงิน</th>
                                <th width="15%" class="text-right">จำนวนเงิน</th>
                                <th width="25%" class="text-center">เอกสารแนบจาก Pre-Advance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($preAdvanceRefs as $idx => $paRef): 
                                $pa = $paRef->preAdvance;
                                if (!$pa) continue;
                            ?>
                                <tr>
                                    <td class="text-center"><?= $idx + 1 ?></td>
                                    <td>
                                        <?= Html::a('<i class="fas fa-external-link-alt"></i> ' . Html::encode($pa->pre_advance_no), ['/pre-advance/view', 'id' => $pa->id], ['target' => '_blank', 'class' => 'fw-bold text-primary']) ?>
                                    </td>
                                    <td><?= $pa->trans_date ? date('d/m/Y', strtotime($pa->trans_date)) : '-' ?></td>
                                    <td><?= Html::encode($pa->recipient_name) ?></td>
                                    <td class="text-right"><?= number_format($pa->amount, 2) ?> บาท</td>
                                    <td>
                                        <?php if (!empty($pa->preAdvanceDocs)): ?>
                                            <div class="d-flex flex-column gap-1">
                                                <?php foreach ($pa->preAdvanceDocs as $doc): ?>
                                                    <div>
                                                        <a href="<?= Yii::getAlias('@web/uploads/pre_advance/') . $doc->file_path ?>" target="_blank" class="btn btn-outline-info btn-xs btn-sm me-1 mb-1">
                                                            <i class="fas fa-paperclip"></i> <?= Html::encode($doc->file_name) ?>
                                                        </a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">ไม่มีเอกสารแนบ</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="mt-3">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function($model) {
                                return $model->getStatusLabel();
                            },
                        ],
                        [
                            'attribute' => 'approve_status',
                            'format' => 'raw',
                            'value' => function($model) {
                                return $model->getApproveStatusBadge();
                            },
                        ],
                        'created_at:datetime:สร้างเมื่อ',
                        'updated_at:datetime:แก้ไขเมื่อ',
                    ],
                ]) ?>
            </div>

        </div>
    </div>

    <!-- Modal สำหรับ ร้องขอ Recall -->
    <?php
    \yii\bootstrap4\Modal::begin([
        'id' => 'recall-modal',
        'title' => '<h4><i class="fas fa-undo-alt text-warning"></i> ร้องขอแก้ไขบันทึกการซื้อ (Recall)</h4>',
        'size' => \yii\bootstrap4\Modal::SIZE_DEFAULT,
        'options' => ['tabindex' => false],
        'closeButton' => [
            'class' => 'btn-close',
            'data-bs-dismiss' => 'modal',
            'aria-label' => 'Close',
        ],
    ]);
    ?>
    <?php $form = \yii\widgets\ActiveForm::begin([
        'action' => ['request-recall', 'id' => $model->id],
        'method' => 'post',
    ]); ?>
    <div class="modal-body">
        <p class="text-muted">เมื่อส่งคำร้องขอ ระบบจะแจ้งเตือนผู้อนุมัติเพื่อพิจารณาปลดล็อคเอกสารกลับมาให้แก้ไข</p>
        <div class="form-group mb-3">
            <label for="pm-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ต้องการดึงเอกสารกลับมาแก้ไข <span class="text-danger">*</span></label>
            <textarea name="recall_reason" id="pm-recall-reason" class="form-control" rows="4" required placeholder="ตัวอย่าง: แก้ไขยอดเงิน, แก้ไขข้อมูลผู้จำหน่าย, แก้ไขใบกำกับภาษี"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane"></i> ยืนยันส่งคำร้องขอ Recall</button>
    </div>
    <?php \yii\widgets\ActiveForm::end(); ?>
    <?php \yii\bootstrap4\Modal::end(); ?>

    <!-- Modal สำหรับ ปฏิเสธ Recall -->
    <?php
    \yii\bootstrap4\Modal::begin([
        'id' => 'reject-recall-modal',
        'title' => '<h4><i class="fas fa-times text-danger"></i> ปฏิเสธคำร้องขอ Recall</h4>',
        'size' => \yii\bootstrap4\Modal::SIZE_DEFAULT,
        'options' => ['tabindex' => false],
        'closeButton' => [
            'class' => 'btn-close',
            'data-bs-dismiss' => 'modal',
            'aria-label' => 'Close',
        ],
    ]);
    ?>
    <?php $form = \yii\widgets\ActiveForm::begin([
        'action' => ['reject-recall', 'id' => $model->id],
        'method' => 'post',
    ]); ?>
    <div class="modal-body">
        <div class="form-group mb-3">
            <label for="pm-reject-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ไม่อนุมัติคำร้องขอ Recall</label>
            <textarea name="reject_reason" id="pm-reject-recall-reason" class="form-control" rows="3" placeholder="ระบุเหตุผลที่ไม่อนุมัติ"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-danger"><i class="fas fa-check"></i> ยืนยันปฏิเสธ Recall</button>
    </div>
    <?php \yii\widgets\ActiveForm::end(); ?>
    <?php \yii\bootstrap4\Modal::end(); ?>

    <!-- Modal สำหรับ ส่งกลับแก้ไข -->
    <?php
    \yii\bootstrap4\Modal::begin([
        'id' => 'return-modal',
        'title' => '<h4><i class="fas fa-undo text-warning"></i> ส่งกลับเพื่อแก้ไข บันทึกซื้อ (NPR)</h4>',
        'size' => \yii\bootstrap4\Modal::SIZE_DEFAULT,
        'options' => ['tabindex' => false],
        'closeButton' => [
            'class' => 'btn-close',
            'data-bs-dismiss' => 'modal',
            'aria-label' => 'Close',
        ],
    ]);
    ?>
    <?php $form = \yii\widgets\ActiveForm::begin([
        'action' => ['return', 'id' => $model->id],
        'method' => 'post',
    ]); ?>
    <div class="modal-body">
        <div class="form-group mb-3">
            <label for="pm-return-reason" class="form-label fw-bold">ระบุรายละเอียดที่ต้องการให้ปรับหรือแก้ไข <span class="text-danger">*</span></label>
            <textarea name="reject_reason" id="pm-return-reason" class="form-control" rows="4" required placeholder="ระบุสิ่งที่ต้องการให้ผู้จัดทำปรับปรุงหรือแก้ไข เพื่อแจ้งให้ทราบ"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane"></i> ยืนยันส่งกลับแก้ไข</button>
    </div>
    <?php \yii\widgets\ActiveForm::end(); ?>
    <?php \yii\bootstrap4\Modal::end(); ?>

    <!-- Modal สำหรับ ไม่อนุมัติ -->
    <?php
    \yii\bootstrap4\Modal::begin([
        'id' => 'reject-modal',
        'title' => '<h4><i class="fas fa-times-circle text-danger"></i> ไม่อนุมัติ บันทึกซื้อ (NPR)</h4>',
        'size' => \yii\bootstrap4\Modal::SIZE_DEFAULT,
        'options' => ['tabindex' => false],
        'closeButton' => [
            'class' => 'btn-close',
            'data-bs-dismiss' => 'modal',
            'aria-label' => 'Close',
        ],
    ]);
    ?>
    <?php $form = \yii\widgets\ActiveForm::begin([
        'action' => ['reject', 'id' => $model->id],
        'method' => 'post',
    ]); ?>
    <div class="modal-body">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> เมื่อเลือก <strong>ไม่อนุมัติ</strong> เอกสารนี้จะถูกยกเลิกถาวรและไม่สามารถส่งแก้ไขได้อีก
        </div>
        <div class="form-group mb-3">
            <label for="pm-reject-reason" class="form-label fw-bold">ระบุเหตุผลที่ไม่อนุมัติ <span class="text-danger">*</span></label>
            <textarea name="reject_reason" id="pm-reject-reason" class="form-control" rows="4" required placeholder="ระบุเหตุผลที่ไม่อนุมัติเอกสารนี้"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-danger"><i class="fas fa-ban"></i> ยืนยันไม่อนุมัติเอกสาร</button>
    </div>
    <?php \yii\widgets\ActiveForm::end(); ?>
    <?php \yii\bootstrap4\Modal::end(); ?>

    <!-- Modal สำหรับแสดง Diff -->
    <?php
    \yii\bootstrap4\Modal::begin([
        'id' => 'diff-modal',
        'title' => '<h4><i class="fas fa-exchange-alt"></i> รายละเอียดการเปลี่ยนแปลง</h4>',
        'size' => \yii\bootstrap4\Modal::SIZE_LARGE,
        'options' => ['tabindex' => false],
        'closeButton' => [
            'class' => 'btn-close',
            'data-bs-dismiss' => 'modal',
            'aria-label' => 'Close',
        ],
    ]);
    echo '<div id="diff-modal-content"></div>';
    \yii\bootstrap4\Modal::end();
    ?>

    <!-- ประวัติการทำรายการ Action Log -->
    <?php 
    $userId = \Yii::$app->user->id;
    $isAdmin = \backend\models\User::isUserAdmin();
    $isMcoAdmin = \backend\models\User::checkhasrole($userId, 'mcoadmin');
    $isExactAdmin = \backend\models\User::checkhasrole($userId, 'admin');
    if ($isAdmin || $isMcoAdmin || $isExactAdmin): 
    ?>
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="card-title mb-0"><i class="fas fa-history"></i> ประวัติการทำรายการบันทึกซื้อ (None PR)</h5>
            <div class="card-tools d-flex align-items-center gap-2">
                <a href="<?= \yii\helpers\Url::to(['action-log/index', 'ActionLogSearchModel[model_class]' => get_class($model), 'ActionLogSearchModel[model_id]' => $model->id]) ?>" class="btn btn-xs btn-outline-light btn-sm text-white" target="_blank" title="ดูบันทึกเหตุการณ์ทั้งหมดของระบบ">
                    <i class="fas fa-external-link-alt"></i> เปิดหน้า Action Log กลาง
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php
            $rawLogs = \backend\models\ActionLogModel::find()
                ->where(['model_class' => get_class($model), 'model_id' => $model->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();

            $filteredLogs = array_filter($rawLogs, function($log) {
                if ($log->action === 'CONTROLLER_ACTION') {
                    $actionId = $log->action_method ?? '';
                    if (empty($actionId) && !empty($log->data)) {
                        $data = $log->getFormattedData();
                        $actionId = $data['action_id'] ?? '';
                    }
                    if (in_array($actionId, ['view', 'print', 'update'])) {
                        return false;
                    }
                }

                if ($log->action === 'MODEL_UPDATE') {
                    $data = $log->getFormattedData();
                    $changes = $data['changed_attributes'] ?? [];
                    $meaningfulChanges = array_filter($changes, function($val, $key) {
                        return !in_array($key, ['updated_at', 'updated_by']);
                    }, ARRAY_FILTER_USE_BOTH);

                    if (empty($meaningfulChanges)) {
                        return false;
                    }
                }

                return true;
            });
            ?>

            <ul class="nav nav-pills mb-3" id="pmLogTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active btn-sm" id="pm-filtered-tab" data-bs-toggle="pill" data-bs-target="#pm-filtered-log-pane" data-toggle="pill" href="#pm-filtered-log-pane" role="tab" type="button">
                        <i class="fas fa-filter"></i> ประวัติสำคัญ/แก้ไขเนื้อหา (<?= count($filteredLogs) ?>)
                    </button>
                </li>
                <li class="nav-item ms-1">
                    <button class="nav-link btn-sm" id="pm-all-tab" data-bs-toggle="pill" data-bs-target="#pm-all-log-pane" data-toggle="pill" href="#pm-all-log-pane" role="tab" type="button">
                        <i class="fas fa-list-ul"></i> ประวัติทั้งหมดสำหรับ Admin (<?= count($rawLogs) ?>)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="pmLogTabContent">
                <div class="tab-pane fade show active" id="pm-filtered-log-pane" role="tabpanel">
                    <?php if (!empty($filteredLogs)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" style="font-size: 13px; white-space: nowrap;">
                                <thead>
                                    <tr>
                                        <th>เวลา</th>
                                        <th>ผู้ดำเนินการ</th>
                                        <th>การกระทำ</th>
                                        <th>รายละเอียดเพิ่มเติม</th>
                                        <th>Diff (เปรียบเทียบ)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filteredLogs as $log): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?></td>
                                            <td>
                                                <?php 
                                                $emp = $log->getEmployeeName();
                                                if ($emp): ?>
                                                    <strong><?= Html::encode($emp) ?></strong><br>
                                                    <small class="text-muted"><?= Html::encode($log->username) ?></small>
                                                <?php else: ?>
                                                    <?= Html::encode($log->username ?: 'Guest') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if ($log->action == 'MODEL_CREATE') echo '<span class="badge bg-success">สร้าง</span>';
                                                    elseif ($log->action == 'MODEL_UPDATE') echo '<span class="badge bg-warning">แก้ไข</span>';
                                                    elseif ($log->action == 'MODEL_DELETE') echo '<span class="badge bg-danger">ลบ</span>';
                                                    elseif ($log->action == 'REQUEST_RECALL') echo '<span class="badge bg-warning text-dark"><i class="fas fa-undo-alt"></i> ร้องขอ Recall</span>';
                                                    elseif ($log->action == 'APPROVE_RECALL') echo '<span class="badge bg-success"><i class="fas fa-check"></i> อนุมัติ Recall</span>';
                                                    elseif ($log->action == 'REJECT_RECALL') echo '<span class="badge bg-danger"><i class="fas fa-times"></i> ปฏิเสธ Recall</span>';
                                                    elseif ($log->action == 'REJECT') echo '<span class="badge bg-danger"><i class="fas fa-ban"></i> ไม่อนุมัติ</span>';
                                                    elseif ($log->action == 'RETURN_FOR_EDIT') echo '<span class="badge bg-warning text-dark"><i class="fas fa-undo"></i> ส่งกลับแก้ไข</span>';
                                                    elseif ($log->action == 'APPROVE') echo '<span class="badge bg-success"><i class="fas fa-check"></i> อนุมัติ</span>';
                                                    else echo '<span class="badge bg-info">'.Html::encode($log->action).'</span>';
                                                ?>
                                            </td>
                                            <td><?= Html::encode($log->message) ?></td>
                                            <td>
                                                <?php if ($log->action == 'MODEL_UPDATE'): ?>
                                                    <button type="button" class="btn btn-sm btn-info btn-view-diff" data-id="<?= $log->id ?>">
                                                        <i class="fas fa-exchange-alt"></i> ดูส่วนที่ถูกแก้
                                                    </button>
                                                <?php endif; ?>
                                                <a href="<?= \yii\helpers\Url::to(['action-log/view', 'id' => $log->id]) ?>" class="btn btn-sm btn-secondary" target="_blank">
                                                    <i class="fas fa-eye"></i> รายละเอียดแบบเต็ม
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">ยังไม่มีประวัติการแก้ไขเนื้อหา</div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="pm-all-log-pane" role="tabpanel">
                    <?php if (!empty($rawLogs)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" style="font-size: 13px; white-space: nowrap;">
                                <thead>
                                    <tr>
                                        <th>เวลา</th>
                                        <th>ผู้ดำเนินการ</th>
                                        <th>Action Type</th>
                                        <th>รายละเอียด / ข้อความ</th>
                                        <th>IP Address</th>
                                        <th>Diff / รายละเอียด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rawLogs as $log): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?></td>
                                            <td>
                                                <?php 
                                                $emp = $log->getEmployeeName();
                                                if ($emp): ?>
                                                    <strong><?= Html::encode($emp) ?></strong><br>
                                                    <small class="text-muted"><?= Html::encode($log->username) ?></small>
                                                <?php else: ?>
                                                    <?= Html::encode($log->username ?: 'Guest') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if ($log->action == 'MODEL_CREATE') echo '<span class="badge bg-success">สร้าง (Model)</span>';
                                                    elseif ($log->action == 'MODEL_UPDATE') echo '<span class="badge bg-warning">แก้ไข (Model)</span>';
                                                    elseif ($log->action == 'MODEL_DELETE') echo '<span class="badge bg-danger">ลบ (Model)</span>';
                                                    elseif ($log->action == 'CONTROLLER_ACTION') echo '<span class="badge bg-info">Action: ' . Html::encode($log->action_method) . '</span>';
                                                    elseif ($log->action == 'REQUEST_RECALL') echo '<span class="badge bg-warning text-dark"><i class="fas fa-undo-alt"></i> ร้องขอ Recall</span>';
                                                    elseif ($log->action == 'APPROVE_RECALL') echo '<span class="badge bg-success"><i class="fas fa-check"></i> อนุมัติ Recall</span>';
                                                    elseif ($log->action == 'REJECT_RECALL') echo '<span class="badge bg-danger"><i class="fas fa-times"></i> ปฏิเสธ Recall</span>';
                                                    elseif ($log->action == 'REJECT') echo '<span class="badge bg-danger"><i class="fas fa-ban"></i> ไม่อนุมัติ</span>';
                                                    elseif ($log->action == 'RETURN_FOR_EDIT') echo '<span class="badge bg-warning text-dark"><i class="fas fa-undo"></i> ส่งกลับแก้ไข</span>';
                                                    elseif ($log->action == 'APPROVE') echo '<span class="badge bg-success"><i class="fas fa-check"></i> อนุมัติ</span>';
                                                    else echo '<span class="badge bg-secondary">'.Html::encode($log->action).'</span>';
                                                ?>
                                            </td>
                                            <td><?= Html::encode($log->message) ?></td>
                                            <td><?= Html::encode($log->ip_address) ?></td>
                                            <td>
                                                <?php if ($log->action == 'MODEL_UPDATE'): ?>
                                                    <button type="button" class="btn btn-sm btn-info btn-view-diff" data-id="<?= $log->id ?>">
                                                        <i class="fas fa-exchange-alt"></i> ดู Diff
                                                    </button>
                                                <?php endif; ?>
                                                <a href="<?= \yii\helpers\Url::to(['action-log/view', 'id' => $log->id]) ?>" class="btn btn-sm btn-secondary" target="_blank">
                                                    <i class="fas fa-eye"></i> รายละเอียดแบบเต็ม
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">ไม่มีประวัติในระบบ</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
$(document).on('click', '.btn-view-diff', function() {
    var id = $(this).data('id');
    $('#diff-modal-content').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> กำลังโหลดข้อมูล...</div>');
    $('#diff-modal').modal('show');
    
    $.get('<?= \yii\helpers\Url::to(['view-diff']) ?>', {id: id}, function(data) {
        $('#diff-modal-content').html(data);
    }).fail(function() {
        $('#diff-modal-content').html('<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>');
    });
});
</script>