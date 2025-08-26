<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */
/* @var $purchModel backend\models\Purch */
/* @var $receiveHistory backend\models\JournalTrans[] */

$this->title = 'ประวัติการรับสินค้า: ' . $purchModel->purch_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบสั่งซื้อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $purchModel->purch_no, 'url' => ['view', 'id' => $purchModel->id]];
$this->params['breadcrumbs'][] = 'ประวัติการรับสินค้า';
?>

<div class="purch-receive-history">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <?= Html::a('รับสินค้าเข้าคลัง', ['receive', 'id' => $purchModel->id], [
                'class' => 'btn btn-success'
            ]) ?>
            <?= Html::a('กลับ', ['view', 'id' => $purchModel->id], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <!-- PO Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">ข้อมูลใบสั่งซื้อ</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>เลขที่ใบสั่งซื้อ:</strong><br>
                    <?= Html::encode($purchModel->purch_no) ?>
                </div>
                <div class="col-md-3">
                    <strong>วันที่:</strong><br>
                    <?= date('m/d/Y', strtotime($purchModel->purch_date)) ?>
                </div>
                <div class="col-md-3">
                    <strong>ผู้ขาย:</strong><br>
                    <?= Html::encode($purchModel->vendor_name) ?>
                </div>
                <div class="col-md-3">
                    <strong>สถานะ:</strong><br>
                    <?= $purchModel->getApproveStatusBadge() ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($receiveHistory)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> ยังไม่มีประวัติการรับสินค้าสำหรับใบสั่งซื้อนี้
        </div>
    <?php else: ?>
        <?php foreach ($receiveHistory as $receive): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <i class="fas fa-download text-success"></i>
                            เลขที่เอกสาร: <strong><?= Html::encode($receive->journal_no) ?></strong>
                        </h6>
                        <small class="text-muted">
                            วันที่รับสินค้า: <?= date('m/d/Y H:i', strtotime($receive->trans_date)) ?>
                        </small>
                    </div>
                    <div>
                        <?php if ($receive->status == 0): ?>
                            <span class="badge bg-success">ใช้งาน</span>
                            <?= Html::a('<i class="fas fa-times"></i> ยกเลิก',
                                ['cancel-receive', 'id' => $receive->id], [
                                    'class' => 'btn btn-sm btn-outline-danger ms-2',
                                    'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะยกเลิกการรับสินค้านี้? จำนวนสต๊อกจะถูกปรับลด',
                                    'data-method' => 'post',
                                ]) ?>
                        <?php elseif ($receive->status == \backend\models\JournalTrans::STATUS_CANCELLED): ?>
                            <span class="badge bg-danger">ยกเลิกแล้ว</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($receive->remark)): ?>
                        <p class="mb-3"><strong>หมายเหตุ:</strong> <?= Html::encode($receive->remark) ?></p>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>ชื่อสินค้า</th>
                                <th>คลังสินค้า</th>
                                <th class="text-center">จำนวนที่รับ</th>
                                <th>หมายเหตุ</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $lineNum = 1; ?>
                            <?php $journal_trans_line = \backend\models\JournalTransLine::find()->where(['journal_trans_id' => $receive->id])->all(); ?>
                            <?php foreach ($journal_trans_line as $line): ?>
                                <tr class="<?= $receive->status == \backend\models\JournalTrans::STATUS_CANCELLED ? 'text-muted' : '' ?>">
                                    <td><?= $lineNum++ ?></td>
                                    <td>
                                        <?= Html::encode(\backend\models\Product::findName($line->product_id) ?? 'N/A') ?>
                                    </td>
                                    <td>
                                        <?= Html::encode($line->warehouse->name ?? 'N/A') ?>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($line->qty, 2) ?>
                                    </td>
                                    <td>
                                        <?= Html::encode($line->remark) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-secondary">
                            <tr>
                                <td colspan="3" class="text-end"><strong>รวมจำนวนที่รับ:</strong></td>
                                <td class="text-center">
                                    <strong><?= number_format($receive->qty, 2) ?></strong>
                                </td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .badge {
        font-size: 12px;
        padding: 0.4em 0.8em;
    }
</style>