<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model backend\models\PurchaseMaster */
?>
<div class="recall-view-purchasemaster">
    <!-- Recall Reason Alert -->
    <div class="alert alert-warning border border-warning">
        <h5 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> คำร้องขอดึงบันทึกการซื้อ (None PR) กลับมาแก้ไข (Recall)</h5>
        <div class="row mt-2">
            <div class="col-md-6">
                <strong>ผู้ร้องขอ:</strong> <?= \backend\models\User::findEmployeeNameByUserId($model->recall_by) ?: 'ไม่ระบุ' ?>
            </div>
            <div class="col-md-6">
                <strong>เวลาที่ขอ:</strong> <?= $model->recall_at ? date('d/m/Y H:i:s', strtotime($model->recall_at)) : '-' ?>
            </div>
            <div class="col-12 mt-2">
                <strong>เหตุผลที่ระบุ:</strong>
                <div class="p-2 bg-white rounded border mt-1 text-dark">
                    <?= nl2br(Html::encode($model->recall_reason)) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Info -->
    <div class="row">
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr>
                    <th class="bg-light" style="width: 40%;">เลขที่เอกสาร</th>
                    <td><strong><?= Html::encode($model->docnum) ?></strong></td>
                </tr>
                <tr>
                    <th class="bg-light">วันที่</th>
                    <td><?= $model->docdat ? date('d/m/Y', strtotime($model->docdat)) : '-' ?></td>
                </tr>
                <tr>
                    <th class="bg-light">ผู้จำหน่าย</th>
                    <td><?= Html::encode($model->supnam ?: '-') ?></td>
                </tr>
                <tr>
                    <th class="bg-light">สถานะอนุมัติ</th>
                    <td><?= $model->getApproveStatusBadge() ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr>
                    <th class="bg-light" style="width: 40%;">มูลค่าก่อนภาษี</th>
                    <td class="text-right"><?= number_format($model->vatpr0, 2) ?> บาท</td>
                </tr>
                <tr>
                    <th class="bg-light">VAT</th>
                    <td class="text-right"><?= number_format($model->vat_amount, 2) ?> บาท</td>
                </tr>
                <tr>
                    <th class="bg-light">TAX (หัก ณ ที่จ่าย)</th>
                    <td class="text-right"><?= number_format($model->tax_amount, 2) ?> บาท</td>
                </tr>
                <tr class="table-warning font-weight-bold">
                    <th>ยอดสุทธิ</th>
                    <td class="text-right" style="font-size: 16px;"><?= number_format($model->total_amount, 2) ?> บาท</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Action Buttons inside Modal -->
    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
        <div>
            <?= Html::a('<i class="fas fa-external-link-alt mr-1"></i> เปิดดูเอกสารเต็มหน้าต่างใหม่', ['purchasemaster/view', 'id' => $model->id], [
                'class' => 'btn btn-outline-secondary btn-sm',
                'target' => '_blank',
            ]) ?>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-check mr-1"></i> อนุมัติ Recall (ปลดล็อคให้แก้ไข)', ['approve', 'type' => 'purchasemaster', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'ยืนยันการอนุมัติ Recall รายการ ' . $model->docnum . '? สถานะจะถูกเปลี่ยนเป็นรออนุมัติเพื่อให้สามารถแก้ไขได้',
                    'method' => 'post',
                ],
            ]) ?>
            <button type="button" class="btn btn-danger btn-reject-modal ml-2" data-type="purchasemaster" data-id="<?= $model->id ?>" data-doc="<?= Html::encode($model->docnum) ?>" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i> ปฏิเสธ Recall
            </button>
        </div>
    </div>
</div>
