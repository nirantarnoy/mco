<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\PettyCashVoucher */

$this->title = $model->pcv_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบสำคัญจ่ายเงินสดย่อย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$model_doc_slip = \common\models\PettyCashVoucherDocSlip::find()->where(['petty_cash_voucher_id' => $model->id])->all();
$model_doc_bill = \common\models\PettyCashVoucherDocBill::find()->where(['petty_cash_voucher_id' => $model->id])->all();
?>
<div class="petty-cash-voucher-view">
    <!-- Flash Messages -->
    <?php if (\Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= \Yii::$app->session->getFlash('warning') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('info') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#upload-pcv-doc-modal" data-bs-toggle="modal" data-bs-target="#upload-pcv-doc-modal">
                <i class="fas fa-paperclip"></i> แนบไฟล์เพิ่มเติม
            </button>
            <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'target' => '_blank'
            ]) ?>
            <?php $isFullyApproved = (!empty($model->approved_by) && $model->approve_status == 1); ?>
            <?php if (!$isFullyApproved): ?>
                <?php if (\Yii::$app->user->can('pettycashvoucher/approve')): ?>
                    <?= Html::a('อนุมัติ', ['approve', 'id' => $model->id], [
                        'class' => 'btn btn-success',
                        'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบจ่ายเงินสดย่อย ?',
                        'data-method' => 'post',
                    ]) ?>

                <?php endif; ?>
            <?php endif; ?>
            <?php if ($isFullyApproved): ?>
                <?php if ($model->is_recall != 1): ?>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#recall-modal" data-bs-toggle="modal" data-bs-target="#recall-modal">
                        <i class="fas fa-undo-alt"></i> ร้องขอแก้ไข (Recall)
                    </button>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($model->is_recall == 1): ?>
                <?php if (\Yii::$app->user->can('pettycashvoucher/approve') || \backend\models\User::isUserAdmin()): ?>
                    <?= Html::a('<i class="fas fa-check"></i> อนุมัติ Recall (ดึงกลับมาแก้ไข)', ['approve-recall', 'id' => $model->id], [
                        'class' => 'btn btn-success',
                        'data' => [
                            'confirm' => 'ยืนยันการอนุมัติ Recall? เอกสารนี้จะถูกปลดสถานะอนุมัติและสามารถแก้ไขได้',
                            'method' => 'post',
                        ],
                    ]) ?>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#reject-recall-modal" data-bs-toggle="modal" data-bs-target="#reject-recall-modal">
                        <i class="fas fa-times"></i> ปฏิเสธ Recall
                    </button>
                <?php else: ?>
                    <span class="badge bg-warning text-dark p-2 align-self-center"><i class="fas fa-hourglass-half"></i> อยู่ระหว่างรออนุมัติ Recall จากผู้มีอำนาจ</span>
                <?php endif; ?>
            <?php endif; ?>
            <?= Html::a('<i class="fas fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                'data-method' => 'post',
            ]) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> กลับไปรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?php if ($model->is_recall == 1): ?>
        <div class="alert alert-warning border border-warning shadow-sm mb-3">
            <h5><i class="fas fa-exclamation-triangle"></i> เอกสารนี้มีคำร้องขอ Recall เพื่อแก้ไข</h5>
            <p class="mb-1"><strong>ผู้ร้องขอ:</strong> <?= \backend\models\User::findEmployeeNameByUserId($model->recall_by) ?> (วันที่: <?= $model->recall_at ? date('d/m/Y H:i:s', strtotime($model->recall_at)) : '-' ?>)</p>
            <p class="mb-0"><strong>เหตุผล:</strong> <?= Html::encode($model->recall_reason) ?></p>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> ข้อมูลทั่วไป</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-bordered detail-view'],
                        'attributes' => [
                            'pcv_no',
                            [
                                'attribute' => 'date',
                                'value' => Yii::$app->formatter->asDate($model->date, 'dd/MM/yyyy'),
                            ],
                            [
                                'attribute' => 'name',
                                'label' => 'ชื่อผู้รับเงิน (Vendor)',
                                'value' => function($model) {
                                    $vendor = \backend\models\Vendor::findOne($model->vendor_id);
                                    return $vendor ? $vendor->name : $model->name;
                                }
                            ],
                            [
                                'attribute' => 'amount',
                                'value' => number_format($model->amount - $model->getTotalVat() - $model->getTotalWht() - $model->getTotalVatProhibit(), 2) . ' บาท',
                            ],
                            [
                                'attribute' => 'paid_for',
                                'value' => $model->paid_for ?: '-',
                                'format' => 'ntext',
                            ],
                            [
                                'label' => 'VAT',
                                'value' => number_format($model->getTotalVat(), 2) . ' บาท',
                            ],
                            [
                                'label' => 'VAT ต้องห้าม',
                                'value' => number_format($model->getTotalVatProhibit(), 2) . ' บาท',
                            ],
                            [
                                'label' => 'W/H',
                                'value' => number_format($model->getTotalWht(), 2) . ' บาท',
                            ],
                            [
                                'label' => 'อื่นๆ',
                                'value' => number_format($model->getTotalOther(), 2) . ' บาท',
                            ],
                            [
                                'label' => 'TOTAL',
                                'value' => number_format($model->calculateTotalAmount(), 2) . ' บาท',
                                'contentOptions' => ['class' => 'font-weight-bold'],
                                'captionOptions' => ['class' => 'font-weight-bold'],
                            ],
                            // [
                            //     'attribute' => 'quotation_id',
                            //     'value' => function ($model) {
                            //         return \backend\models\Quotation::findNo($model->quotation_id);
                            //     },
                            // ],
                            // [
                            //     'attribute' => 'job_id',
                            //     'value' => function ($model) {
                            //         return \backend\models\Job::findJobNo($model->job_id);
                            //     },
                            // ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-users"></i> ผู้ดำเนินการ</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-bordered detail-view'],
                        'attributes' => [
                            [
                                'attribute' => 'issued_by',
                                'value' => $model->issued_by ?: '-',
                            ],
                            [
                                'attribute' => 'issued_date',
                                'value' => $model->issued_date ? Yii::$app->formatter->asDate($model->issued_date, 'dd/MM/yyyy') : '-',
                            ],
                            [
                                'attribute' => 'approved_status',
                                'value' => function ($data) {
                                    if($data->approve_status == 1){
                                        return 'อนุมัติ';
                                    }else {
                                        return 'ไม่อนุมัติ';
                                    }
                                }
                            ],
                            [
                                'attribute' => 'approved_by',
                                'value' => $model->approved_by ?: '-',
                            ],
                            [
                                'attribute' => 'approved_date',
                                'value' => $model->approved_date ? Yii::$app->formatter->asDate($model->approved_date, 'dd/MM/yyyy') : '-',
                            ],
                            [
                                'label' => 'สถานะเอกสารแนบ',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data->getDocStatusHtml();
                                }
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-list"></i> รายละเอียดการจ่าย</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($model->details)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">สำหรับเลขที่บิล</th>
                            <th width="10%">DATE</th>
                            <th width="30%">DETAIL</th>
                            <th width="10%">JOB</th>
                            <th width="12%">AMOUNT</th>
                            <th width="8%">VAT</th>
                            <th width="10%">VAT ต้องห้าม</th>
                            <th width="8%">W/H</th>
                            <th width="8%">อื่นๆ</th>
                            <th width="12%">TOTAL</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $totalAmount = 0; ?>
                        <?php foreach ($model->details as $index => $detail): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= Html::encode($detail->ac_code) ?></td>
                                <td class="text-center">
                                    <?= $detail->detail_date ? Yii::$app->formatter->asDate($detail->detail_date, 'dd/MM/yyyy') : '-' ?>
                                </td>
                                <td><?= Html::encode($detail->detail) ?></td>
                                <td><?= Html::encode(\backend\models\Job::findJobNo($detail->job_ref_id)) ?></td>
                                <td class="text-right"><?= number_format($detail->amount, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->vat, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->vat_prohibit, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->wht, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->other, 2) ?></td>
                                <td class="text-right font-weight-bold"><?= number_format($detail->total, 2) ?></td>
                            </tr>
                            <?php $totalAmount += $detail->total; ?>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                        <tr>
                            <th colspan="10" class="text-right">รวมทั้งหมด:</th>
                            <th class="text-right"><?= number_format($totalAmount, 2) ?> บาท</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted p-4">
                    <i class="fas fa-inbox fa-2x"></i><br>
                    ไม่มีรายละเอียดการจ่าย
                </div>
            <?php endif; ?>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="mb-0">เอกสารแนบสลิป</h4>
                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#upload-pcv-doc-modal" data-bs-toggle="modal" data-bs-target="#upload-pcv-doc-modal" onclick="$('#pcv-doc-type').val('1');">
                    <i class="fas fa-paperclip"></i> แนบสลิป
                </button>
            </div>
            <table class="table table-bordered table-striped" style="width: 100%">
                <thead>
                <tr>
                    <th style="width: 5%;text-align: center">#</th>
                    <th style="width: 60%;text-align: center">ชื่อไฟล์</th>
                    <th style="width: 20%;text-align: center">ดูเอกสาร</th>
                    <th style="width: 15%;text-align: center">ลบ</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($model_doc_slip != null): ?>

                    <?php foreach ($model_doc_slip as $key => $value): ?>
                        <tr>
                            <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                            <td><?= $value->doc ?></td>
                            <td style="text-align: center">
                                <a href="<?= \yii\helpers\Url::to(['site/view-file', 'folder' => 'pettycash_doc_slip', 'file' => $value->doc]) ?>"
                                   target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-search"></i> ดูเอกสาร
                                </a>
                            </td>
                            <td style="text-align: center">
                                <button type="button" class="btn btn-outline-danger btn-sm" data-value="<?= $model->id ?>"
                                     data-type="1"
                                     data-var="<?= trim($value->doc) ?>"
                                     onclick="delete_doc_view($(this))">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">ไม่มีเอกสารแนบสลิป</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="mb-0">เอกสารแนบใบเสร็จ</h4>
                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#upload-pcv-doc-modal" data-bs-toggle="modal" data-bs-target="#upload-pcv-doc-modal" onclick="$('#pcv-doc-type').val('2');">
                    <i class="fas fa-paperclip"></i> แนบใบเสร็จ
                </button>
            </div>
            <table class="table table-bordered table-striped" style="width: 100%">
                <thead>
                <tr>
                    <th style="width: 5%;text-align: center">#</th>
                    <th style="width: 60%;text-align: center">ชื่อไฟล์</th>
                    <th style="width: 20%;text-align: center">ดูเอกสาร</th>
                    <th style="width: 15%;text-align: center">ลบ</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($model_doc_bill != null): ?>

                    <?php foreach ($model_doc_bill as $key => $value): ?>
                        <tr>
                            <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                            <td><?= $value->doc ?></td>
                            <td style="text-align: center">
                                <a href="<?= \yii\helpers\Url::to(['site/view-file', 'folder' => 'pettycash_doc_bill', 'file' => $value->doc]) ?>"
                                   target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-search"></i> ดูเอกสาร
                                </a>
                            </td>
                            <td style="text-align: center">
                                <button type="button" class="btn btn-outline-danger btn-sm" data-value="<?= $model->id ?>"
                                     data-type="2"
                                     data-var="<?= trim($value->doc) ?>"
                                     onclick="delete_doc_view($(this))">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">ไม่มีเอกสารแนบใบเสร็จ</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <br/>

    <!-- Modal สำหรับ แนบไฟล์เพิ่มเติม PCV -->
    <?php
    \yii\bootstrap4\Modal::begin([
        'id' => 'upload-pcv-doc-modal',
        'title' => '<h4><i class="fas fa-paperclip text-primary"></i> แนบเอกสารเพิ่มเติม</h4>',
        'size' => \yii\bootstrap4\Modal::SIZE_DEFAULT,
        'options' => ['tabindex' => false],
        'closeButton' => [
            'class' => 'btn-close',
            'data-bs-dismiss' => 'modal',
            'aria-label' => 'Close',
        ],
    ]);
    ?>
    <form id="form-upload-pcv-doc" action="<?= \yii\helpers\Url::to(['petty-cash-voucher/add-doc-file'], true) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
        <input type="hidden" name="id" value="<?= $model->id ?>">
        <input type="hidden" name="redirect_to" value="view">
        <div class="modal-body">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">ประเภทเอกสารแนบ <span class="text-danger">*</span></label>
                <select id="pcv-doc-type" class="form-control" onchange="updatePcvUploadAction(this.value)">
                    <option value="1">เอกสารแนบสลิป</option>
                    <option value="2">เอกสารแนบใบเสร็จค่าสินค้า</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label class="form-label fw-bold">เลือกไฟล์เอกสาร <span class="text-danger">*</span></label>
                <input type="file" name="file_doc[]" class="form-control" multiple required>
                <small class="text-muted">สามารถเลือกได้หลายไฟล์พร้อมกัน (PDF, รูปภาพ, เอกสารอื่นๆ)</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> อัพโหลดไฟล์</button>
        </div>
    </form>
    <?php \yii\bootstrap4\Modal::end(); ?>

    <form id="form-delete-doc-view" action="<?= \yii\helpers\Url::to(['petty-cash-voucher/delete-doc-file'], true) ?>" method="post">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
        <input type="hidden" name="id" value="<?= $model->id ?>">
        <input type="hidden" name="redirect_to" value="view">
        <input type="hidden" class="delete-doc-type" name="doc_delete_type" value="1">
        <input type="hidden" class="delete-doc-list" name="doc_delete_list" value="">
    </form>

    <!-- Modal สำหรับ ร้องขอ Recall -->
    <?php
    \yii\bootstrap4\Modal::begin([
        'id' => 'recall-modal',
        'title' => '<h4><i class="fas fa-undo-alt text-warning"></i> ร้องขอแก้ไขใบสำคัญจ่ายเงินสดย่อย (Recall)</h4>',
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
            <label for="pcv-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ต้องการดึงเอกสารกลับมาแก้ไข <span class="text-danger">*</span></label>
            <textarea name="recall_reason" id="pcv-recall-reason" class="form-control" rows="4" required placeholder="ตัวอย่าง: แก้ไขยอดเงิน, แก้ไขรายการค่าใช้จ่าย, เปลี่ยนแปลงเอกสารแนบ"></textarea>
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
            <label for="pcv-reject-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ไม่อนุมัติคำร้องขอ Recall</label>
            <textarea name="reject_reason" id="pcv-reject-recall-reason" class="form-control" rows="3" placeholder="ระบุเหตุผลที่ไม่อนุมัติ"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-danger"><i class="fas fa-check"></i> ยืนยันปฏิเสธ Recall</button>
    </div>
    <?php \yii\widgets\ActiveForm::end(); ?>
    <?php \yii\bootstrap4\Modal::end(); ?>

</div>

<script>
function updatePcvUploadAction(val) {
    var form = $('#form-upload-pcv-doc');
    if (val == '2') {
        form.attr('action', '<?= \yii\helpers\Url::to(['petty-cash-voucher/add-doc-file-bill'], true) ?>');
    } else {
        form.attr('action', '<?= \yii\helpers\Url::to(['petty-cash-voucher/add-doc-file'], true) ?>');
    }
}

function delete_doc_view(e) {
    var id = e.attr('data-value');
    var docType = e.attr('data-type');
    var fileName = e.attr('data-var');
    if (id > 0 && fileName && confirm('ต้องการลบไฟล์แนบ \"' + fileName + '\" ใช่หรือไม่?')) {
        $('.delete-doc-type').val(docType);
        $('.delete-doc-list').val(fileName);
        $('#form-delete-doc-view').submit();
    }
}
</script>