<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model backend\models\PettyCashVoucher */
?>
<div class="recall-view-pettycashvoucher">
    <!-- Recall Reason Alert -->
    <div class="alert alert-warning border border-warning">
        <h5 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> คำร้องขอดึงใบสำคัญจ่ายเงินสดย่อยกลับมาแก้ไข (Recall)</h5>
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
                    <th class="bg-light" style="width: 40%;">เลขที่ PCV</th>
                    <td><strong><?= Html::encode($model->pcv_no) ?></strong></td>
                </tr>
                <tr>
                    <th class="bg-light">วันที่</th>
                    <td><?= $model->date ? date('d/m/Y', strtotime($model->date)) : '-' ?></td>
                </tr>
                <tr>
                    <th class="bg-light">ชื่อผู้รับเงิน</th>
                    <td><?= Html::encode($model->name ?: '-') ?></td>
                </tr>
                <tr>
                    <th class="bg-light">จ่ายเพื่อ</th>
                    <td><?= Html::encode($model->paid_for ?: '-') ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr>
                    <th class="bg-light" style="width: 40%;">ผู้อนุมัติเดิม</th>
                    <td><?= Html::encode($model->approved_by ?: '-') ?></td>
                </tr>
                <tr>
                    <th class="bg-light">วันที่อนุมัติเดิม</th>
                    <td><?= $model->approved_date ? date('d/m/Y H:i', strtotime($model->approved_date)) : '-' ?></td>
                </tr>
                <tr class="table-warning font-weight-bold">
                    <th>ยอดรวมเงิน</th>
                    <td class="text-right" style="font-size: 16px;"><?= number_format($model->amount, 2) ?> บาท</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Lines table -->
    <h6 class="font-weight-bold mt-3"><i class="fas fa-list-alt mr-1"></i> รายการจ่ายเงินสดย่อย</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-striped" style="font-size: 13px;">
            <thead class="thead-light">
                <tr>
                    <th style="width: 40px; text-align: center;">#</th>
                    <th>รายละเอียด</th>
                    <th style="width: 120px; text-align: right;">จำนวนเงิน (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($model->details)): ?>
                    <?php foreach ($model->details as $i => $det): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td><?= Html::encode($det->detail) ?></td>
                            <td class="text-right font-weight-bold"><?= number_format($det->amount, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center text-muted py-3">ไม่มีรายการย่อย</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Action Buttons inside Modal -->
    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
        <div>
            <?= Html::a('<i class="fas fa-external-link-alt mr-1"></i> เปิดดูเอกสารเต็มหน้าต่างใหม่', ['petty-cash-voucher/view', 'id' => $model->id], [
                'class' => 'btn btn-outline-secondary btn-sm',
                'target' => '_blank',
            ]) ?>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-check mr-1"></i> อนุมัติ Recall (ปลดล็อคให้แก้ไข)', ['approve', 'type' => 'pettycashvoucher', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'ยืนยันการอนุมัติ Recall ใบสำคัญจ่ายเงินสดย่อย ' . $model->pcv_no . '? ลายเซ็นต์อนุมัติจะถูกปลดออกและสามารถแก้ไขได้',
                    'method' => 'post',
                ],
            ]) ?>
            <button type="button" class="btn btn-danger btn-reject-modal ml-2" data-type="pettycashvoucher" data-id="<?= $model->id ?>" data-doc="<?= Html::encode($model->pcv_no) ?>" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i> ปฏิเสธ Recall
            </button>
        </div>
    </div>
</div>
