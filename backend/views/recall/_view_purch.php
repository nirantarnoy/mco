<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model backend\models\Purch */
?>
<div class="recall-view-purch">
    <!-- Recall Reason Alert -->
    <div class="alert alert-warning border border-warning">
        <h5 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> คำร้องขอดึงใบสั่งซื้อกลับมาแก้ไข (Recall)</h5>
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

    <!-- PO Overview Info -->
    <div class="row">
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr>
                    <th class="bg-light" style="width: 40%;">เลขที่ใบสั่งซื้อ</th>
                    <td><strong><?= Html::encode($model->purch_no) ?></strong></td>
                </tr>
                <tr>
                    <th class="bg-light">วันที่</th>
                    <td><?= $model->purch_date ? date('d/m/Y', strtotime($model->purch_date)) : '-' ?></td>
                </tr>
                <tr>
                    <th class="bg-light">ผู้ขาย</th>
                    <td><?= Html::encode($model->vendor_name ?: ($model->vendor ? $model->vendor->name : '-')) ?></td>
                </tr>
                <tr>
                    <th class="bg-light">สถานะปัจจุบัน</th>
                    <td><?= $model->getApproveStatusBadge() ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr>
                    <th class="bg-light" style="width: 40%;">ยอดรวม</th>
                    <td class="text-right"><?= number_format($model->total_amount, 2) ?> บาท</td>
                </tr>
                <tr>
                    <th class="bg-light">ส่วนลด</th>
                    <td class="text-right"><?= number_format($model->discount_amount, 2) ?> บาท</td>
                </tr>
                <tr>
                    <th class="bg-light">ภาษีมูลค่าเพิ่ม (VAT)</th>
                    <td class="text-right"><?= number_format($model->vat_amount, 2) ?> บาท</td>
                </tr>
                <tr class="table-warning font-weight-bold">
                    <th>ยอดสุทธิ</th>
                    <td class="text-right" style="font-size: 16px;"><?= number_format($model->net_amount, 2) ?> บาท</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Lines table -->
    <h6 class="font-weight-bold mt-3"><i class="fas fa-boxes mr-1"></i> รายการสินค้าในใบสั่งซื้อ</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-striped" style="font-size: 13px;">
            <thead class="thead-light">
                <tr>
                    <th style="width: 40px; text-align: center;">#</th>
                    <th>รายการสินค้า / คำอธิบาย</th>
                    <th style="width: 90px; text-align: right;">จำนวน</th>
                    <th style="width: 80px; text-align: center;">หน่วย</th>
                    <th style="width: 100px; text-align: right;">ราคา/หน่วย</th>
                    <th style="width: 110px; text-align: right;">รวมเงิน</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($model->purchLines)): ?>
                    <?php foreach ($model->purchLines as $i => $line): ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td>
                                <div><?= Html::encode($line->product ? $line->product->name : $line->description) ?></div>
                                <?php if ($line->product_description): ?>
                                    <small class="text-muted"><?= Html::encode($line->product_description) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?= number_format($line->qty, 2) ?></td>
                            <td class="text-center"><?= $line->unit ? Html::encode($line->unit->name) : '-' ?></td>
                            <td class="text-right"><?= number_format($line->price, 2) ?></td>
                            <td class="text-right"><?= number_format($line->line_total, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">ไม่มีรายการ</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Action Buttons inside Modal -->
    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
        <div>
            <?= Html::a('<i class="fas fa-external-link-alt mr-1"></i> เปิดดูเอกสารเต็มหน้าต่างใหม่', ['purch/view', 'id' => $model->id], [
                'class' => 'btn btn-outline-secondary btn-sm',
                'target' => '_blank',
            ]) ?>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-check mr-1"></i> อนุมัติ Recall (ปลดล็อคให้แก้ไข)', ['approve', 'type' => 'purch', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'ยืนยันการอนุมัติ Recall ใบสั่งซื้อ ' . $model->purch_no . '? สถานะจะถูกเปลี่ยนเป็นรออนุมัติเพื่อให้สามารถแก้ไขได้',
                    'method' => 'post',
                ],
            ]) ?>
            <button type="button" class="btn btn-danger btn-reject-modal ml-2" data-type="purch" data-id="<?= $model->id ?>" data-doc="<?= Html::encode($model->purch_no) ?>" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i> ปฏิเสธ Recall
            </button>
        </div>
    </div>
</div>
