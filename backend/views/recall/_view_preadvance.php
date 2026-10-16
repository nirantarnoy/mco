<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model backend\models\PreAdvance */
?>
<div class="recall-view-preadvance">
    <!-- Recall Reason Alert -->
    <div class="alert alert-warning border border-warning">
        <h5 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> คำร้องขอดึงใบเบิกเงินทดรองจ่ายกลับมาแก้ไข (Recall)</h5>
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

    <!-- Info Overview -->
    <div class="row">
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr>
                    <th class="bg-light" style="width: 40%;">เลขที่ใบเบิก</th>
                    <td><strong><?= Html::encode($model->pre_advance_no) ?></strong></td>
                </tr>
                <tr>
                    <th class="bg-light">วันที่เอกสาร</th>
                    <td><?= $model->trans_date ? date('d/m/Y', strtotime($model->trans_date)) : '-' ?></td>
                </tr>
                <tr>
                    <th class="bg-light">ชื่อผู้รับเงิน / Vendor</th>
                    <td><?= Html::encode($model->recipient_name ?: ($model->vendor ? $model->vendor->name : '-')) ?></td>
                </tr>
                <tr>
                    <th class="bg-light">หมายเหตุ</th>
                    <td><?= Html::encode($model->remark) ?: '-' ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr>
                    <th class="bg-light" style="width: 40%;">ผู้ตรวจสอบ</th>
                    <td><?= \backend\models\User::findEmployeeNameByUserId($model->checked_by) ?: '-' ?></td>
                </tr>
                <tr>
                    <th class="bg-light">เวลาตรวจสอบ</th>
                    <td><?= $model->checked_at ? date('d/m/Y H:i', strtotime($model->checked_at)) : '-' ?></td>
                </tr>
                <tr>
                    <th class="bg-light">ผู้อนุมัติเดิม</th>
                    <td><?= \backend\models\User::findEmployeeNameByUserId($model->approved_by) ?: '-' ?></td>
                </tr>
                <tr class="table-info font-weight-bold">
                    <th>ยอดขอเบิกรวม</th>
                    <td class="text-right" style="font-size: 16px;"><?= number_format($model->amount, 2) ?> บาท</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Lines Table -->
    <h6 class="font-weight-bold mt-3"><i class="fas fa-list-alt mr-1"></i> รายการบันทึกขออนุมัติค่าใช้จ่าย/ตั้งเบิก</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-striped" style="font-size: 13px;">
            <thead class="thead-light">
                <tr>
                    <th style="width: 40px; text-align: center;">#</th>
                    <th style="width: 100px; text-align: center;">วันที่</th>
                    <th>รายละเอียดการขออนุมัติค่าใช้จ่าย</th>
                    <th style="width: 120px; text-align: right;">ยอดเบิก (บาท)</th>
                    <th>หมายเหตุ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($model->preAdvanceLines)): ?>
                    <?php foreach ($model->preAdvanceLines as $i => $line): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td class="text-center"><?= $line->trans_date ? date('d/m/Y', strtotime($line->trans_date)) : '-' ?></td>
                            <td><?= Html::encode($line->description) ?></td>
                            <td class="text-right font-weight-bold"><?= number_format($line->amount, 2) ?></td>
                            <td><?= Html::encode($line->remark) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">ไม่มีรายการย่อย</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Action Buttons inside Modal -->
    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
        <div>
            <?= Html::a('<i class="fas fa-external-link-alt mr-1"></i> เปิดดูเอกสารเต็มหน้าต่างใหม่', ['pre-advance/view', 'id' => $model->id], [
                'class' => 'btn btn-outline-secondary btn-sm',
                'target' => '_blank',
            ]) ?>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-check mr-1"></i> อนุมัติ Recall (ปลดล็อคให้แก้ไข)', ['approve', 'type' => 'preadvance', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'ยืนยันการอนุมัติ Recall รายการ ' . $model->pre_advance_no . '? สถานะอนุมัติจะถูกปลดเพื่อให้สามารถแก้ไขได้',
                    'method' => 'post',
                ],
            ]) ?>
            <button type="button" class="btn btn-danger btn-reject-modal ml-2" data-type="preadvance" data-id="<?= $model->id ?>" data-doc="<?= Html::encode($model->pre_advance_no) ?>" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i> ปฏิเสธ Recall
            </button>
        </div>
    </div>
</div>
