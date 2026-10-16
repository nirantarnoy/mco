<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model backend\models\PurchReq */
?>
<div class="recall-view-purchreq">
    <!-- Recall Reason Alert -->
    <div class="alert alert-warning border border-warning">
        <h5 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> คำร้องขอดึงใบขอซื้อกลับมาแก้ไข (Recall)</h5>
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

    <!-- PR Overview Info -->
    <div class="row">
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr>
                    <th class="bg-light" style="width: 40%;">เลขที่ใบขอซื้อ (PR)</th>
                    <td><strong><?= Html::encode($model->purch_req_no) ?></strong></td>
                </tr>
                <tr>
                    <th class="bg-light">วันที่</th>
                    <td><?= $model->purch_req_date ? date('d/m/Y', strtotime($model->purch_req_date)) : '-' ?></td>
                </tr>
                <tr>
                    <th class="bg-light">ผู้ขาย</th>
                    <td><?= Html::encode($model->vendor_name ?: ($model->vendor ? $model->vendor->name : '-')) ?></td>
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
    <h6 class="font-weight-bold mt-3"><i class="fas fa-boxes mr-1"></i> รายการสินค้าในใบขอซื้อ</h6>
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
                <?php
                $lines = $model->getPurchReqLines()->all();
                if (!empty($lines)):
                    foreach ($lines as $i => $line):
                        $prodCode = method_exists($line, 'getProductCode') ? $line->getProductCode() : '';
                        $prodName = $line->product_name ?: ($line->product ? $line->product->name : '-');
                        $unitName = !empty($line->unit_id) ? \backend\models\Unit::findName($line->unit_id) : ($line->unit ?: '-');
                ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td>
                                <div>
                                    <?php if ($prodCode): ?>
                                        <span class="badge badge-light border mr-1">[<?= Html::encode($prodCode) ?>]</span>
                                    <?php endif; ?>
                                    <strong><?= Html::encode($prodName) ?></strong>
                                </div>
                                <?php if (!empty($line->product_description)): ?>
                                    <div class="text-muted small mt-1"><?= nl2br(Html::encode($line->product_description)) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?= number_format($line->qty, 2) ?></td>
                            <td class="text-center"><?= Html::encode($unitName) ?></td>
                            <td class="text-right"><?= number_format($line->line_price, 2) ?></td>
                            <td class="text-right font-weight-bold"><?= number_format($line->line_total, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">ไม่มีรายการสินค้า</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Action Buttons inside Modal -->
    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
        <div>
            <?= Html::a('<i class="fas fa-external-link-alt mr-1"></i> เปิดดูเอกสารเต็มหน้าต่างใหม่', ['purchreq/view', 'id' => $model->id], [
                'class' => 'btn btn-outline-secondary btn-sm',
                'target' => '_blank',
            ]) ?>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-check mr-1"></i> อนุมัติ Recall (ปลดล็อคให้แก้ไข)', ['approve', 'type' => 'purchreq', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'ยืนยันการอนุมัติ Recall ใบขอซื้อ ' . $model->purch_req_no . '? สถานะจะถูกเปลี่ยนเป็นรออนุมัติเพื่อให้สามารถแก้ไขได้',
                    'method' => 'post',
                ],
            ]) ?>
            <button type="button" class="btn btn-danger btn-reject-modal ml-2" data-type="purchreq" data-id="<?= $model->id ?>" data-doc="<?= Html::encode($model->purch_req_no) ?>" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i> ปฏิเสธ Recall
            </button>
        </div>
    </div>
</div>
