<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $purchModel backend\models\PurchaseMaster */
/* @var $receiveHistory array */

$this->title = 'ประวัติการรับสินค้าเข้าคลัง (None PR): ' . $purchModel->docnum;
$this->params['breadcrumbs'][] = ['label' => 'บันทึกการซื้อ (None PR)', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $purchModel->docnum, 'url' => ['view', 'id' => $purchModel->id]];
$this->params['breadcrumbs'][] = 'ประวัติการรับสินค้า';
?>

<div class="purchasemaster-receive-history">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <?= Html::a('<i class="fas fa-plus mr-1"></i> รับสินค้าเข้าคลัง', ['receive', 'id' => $purchModel->id], [
                'class' => 'btn btn-success'
            ]) ?>
            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> กลับหน้าดูรายละเอียด', ['view', 'id' => $purchModel->id], [
                'class' => 'btn btn-secondary'
            ]) ?>
        </div>
    </div>

    <!-- Master Info -->
    <div class="card card-outline card-info mb-4">
        <div class="card-header">
            <h3 class="card-title">ข้อมูลใบซื้อ None PR</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>เลขที่เอกสาร:</strong> <?= Html::encode($purchModel->docnum) ?>
                </div>
                <div class="col-md-3">
                    <strong>วันที่เอกสาร:</strong> <?= date('m/d/Y', strtotime($purchModel->docdat)) ?>
                </div>
                <div class="col-md-3">
                    <strong>ผู้จำหน่าย:</strong> <?= Html::encode($purchModel->supnam) ?>
                </div>
                <div class="col-md-3">
                    <strong>สถานะรับเข้า:</strong> <?= $purchModel->getReceiveStatusBadge() ?>
                </div>
            </div>
        </div>
    </div>

    <!-- History List -->
    <?php if (empty($receiveHistory)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i> ยังไม่มีประวัติการรับสินค้าเข้าคลังสำหรับเอกสารนี้
        </div>
    <?php else: ?>
        <?php foreach ($receiveHistory as $transIndex => $trans): ?>
            <div class="card card-outline <?= $trans->status == \backend\models\JournalTrans::STATUS_CANCELLED ? 'card-danger' : 'card-success' ?> mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">
                            <strong>เลขที่เอกสารรับ:</strong> <?= Html::encode($trans->journal_no) ?>
                            <?php if ($trans->status == \backend\models\JournalTrans::STATUS_CANCELLED): ?>
                                <span class="badge badge-danger ml-2">ยกเลิกแล้ว</span>
                            <?php else: ?>
                                <span class="badge badge-success ml-2">รับเข้าเรียบร้อย</span>
                            <?php endif; ?>
                        </h4>
                        <small class="text-muted ml-3">
                            บันทึกเมื่อ: <?= date('d/m/Y H:i', strtotime($trans->trans_date)) ?> 
                            โดย: <?= Html::encode($trans->updated_by ?? $trans->created_by ?? 'system') ?>
                        </small>
                    </div>
                    <div>
                        <?php if ($trans->status != \backend\models\JournalTrans::STATUS_CANCELLED): ?>
                            <?= Html::a('<i class="fas fa-times-circle mr-1"></i> ยกเลิกการรับนี้', ['cancel-receive', 'id' => $trans->id], [
                                'class' => 'btn btn-danger btn-sm',
                                'data' => [
                                    'confirm' => 'คุณแน่ใจหรือว่าต้องการยกเลิกการรับสินค้าในรอบนี้? ระบบจะปรับปรุงสต็อกกลับคืน',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($trans->remark)): ?>
                        <div class="mb-3">
                            <strong>หมายเหตุ:</strong> <?= Html::encode($trans->remark) ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>รหัส / ชื่อสินค้า</th>
                                    <th class="text-center" style="width: 140px;">Lot No.</th>
                                    <th style="width: 180px;">คลังสินค้า</th>
                                    <th class="text-right" style="width: 120px;">จำนวนรับ</th>
                                    <th class="text-right" style="width: 130px;">ราคาต่อหน่วย</th>
                                    <th class="text-right" style="width: 140px;">ราคารวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalTransQty = 0;
                                $totalTransAmount = 0;
                                foreach ($trans->journalTransLines as $lineIdx => $line): 
                                    $lineTotal = (float)$line->qty * (float)$line->sale_price;
                                    $totalTransQty += (float)$line->qty;
                                    $totalTransAmount += $lineTotal;
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $lineIdx + 1 ?></td>
                                        <td>
                                            <strong><?= Html::encode($line->product ? $line->product->code : '-') ?></strong> 
                                            - <?= Html::encode($line->product ? $line->product->name : $line->remark) ?>
                                        </td>
                                        <td class="text-center"><span class="badge badge-secondary"><?= Html::encode($line->lot_no) ?></span></td>
                                        <td><?= Html::encode($line->warehouse ? $line->warehouse->name : '-') ?></td>
                                        <td class="text-right font-weight-bold text-success"><?= number_format($line->qty, 2) ?></td>
                                        <td class="text-right"><?= number_format($line->sale_price, 2) ?></td>
                                        <td class="text-right"><?= number_format($lineTotal, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">รวมรับรอบนี้:</th>
                                    <th class="text-right text-success"><?= number_format($totalTransQty, 2) ?></th>
                                    <th></th>
                                    <th class="text-right"><?= number_format($totalTransAmount, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
