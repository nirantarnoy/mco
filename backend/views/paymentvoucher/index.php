<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PaymentVoucherSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Payment Voucher (ใบสำคัญจ่าย)';
$this->params['breadcrumbs'][] = $this->title;

$totalAdvanceCount = (count($advancePos ?? [])) + (count($advanceNprs ?? []));
$dueAdvanceCount = 0;
foreach (($advancePos ?? []) as $ap) { if ($ap['is_due']) $dueAdvanceCount++; }
foreach (($advanceNprs ?? []) as $an) { if ($an['is_due']) $dueAdvanceCount++; }
?>
<div class="payment-voucher-index">

    <?php if ($totalAdvanceCount > 0): ?>
    <!-- แจ้งเตือนและตารางงานรอโอนเงินล่วงหน้า (Advance Alert Center) -->
    <div class="card card-outline card-warning shadow-sm mb-4 border-warning">
        <div class="card-header bg-warning py-2 text-dark d-flex justify-content-between align-items-center">
            <h5 class="card-title font-weight-bold mb-0">
                <i class="fas fa-bell text-danger mr-1"></i> รายการสั่งซื้อรอโอนเงินล่วงหน้า/มัดจำ (Pending Advance Payment)
                <?php if ($dueAdvanceCount > 0): ?>
                    <span class="badge badge-danger ml-2 animate__animated animate__flash">ถึงกำหนดจ่ายวันนี้/เกินกำหนด <?= $dueAdvanceCount ?> รายการ</span>
                <?php endif; ?>
            </h5>
            <span class="badge badge-dark"><?= $totalAdvanceCount ?> รายการรอดำเนินการ</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 120px;">ประเภท</th>
                            <th>เลขที่เอกสาร</th>
                            <th>ผู้ขาย / คู่ค้า</th>
                            <th>เลขที่ใบแจ้งหนี้/วางบิล</th>
                            <th>วันที่นัดโอน</th>
                            <th class="text-right">ยอดเงินมัดจำคงเหลือ</th>
                            <th class="text-center" style="width: 150px;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($advancePos ?? []) as $item): ?>
                            <?php $po = $item['model']; ?>
                            <tr class="<?= $item['is_due'] ? 'table-danger font-weight-bold' : '' ?>">
                                <td><span class="badge badge-primary">PO (สั่งซื้อ)</span></td>
                                <td><?= Html::encode($po->purch_no) ?></td>
                                <td><?= Html::encode($po->vendor_name) ?></td>
                                <td><?= Html::encode($po->invoice_no ?: '-') ?></td>
                                <td>
                                    <?= !empty($po->payment_due_date) ? date('d/m/Y', strtotime($po->payment_due_date)) : '-' ?>
                                    <?php if ($item['is_due']): ?>
                                        <span class="badge badge-danger">ถึงกำหนด</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right text-success font-weight-bold"><?= number_format($item['remaining'], 2) ?></td>
                                <td class="text-center">
                                    <?= Html::a('<i class="fas fa-plus-circle"></i> ออก PV ทันที', ['create', 'ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PO, 'ref_id' => $po->id], [
                                        'class' => 'btn btn-xs btn-success font-weight-bold shadow-sm',
                                        'title' => 'ดึงไปทำใบสำคัญจ่าย PV พร้อมออกใบหัก ณ ที่จ่าย'
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php foreach (($advanceNprs ?? []) as $item): ?>
                            <?php $npr = $item['model']; ?>
                            <tr class="<?= $item['is_due'] ? 'table-danger font-weight-bold' : '' ?>">
                                <td><span class="badge badge-info">None PR</span></td>
                                <td><?= Html::encode($npr->docnum) ?></td>
                                <td><?= Html::encode($npr->supnam) ?></td>
                                <td><?= Html::encode($npr->invoice_no ?: '-') ?></td>
                                <td>
                                    <?= !empty($npr->payment_due_date) ? date('d/m/Y', strtotime($npr->payment_due_date)) : '-' ?>
                                    <?php if ($item['is_due']): ?>
                                        <span class="badge badge-danger">ถึงกำหนด</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right text-success font-weight-bold"><?= number_format($item['remaining'], 2) ?></td>
                                <td class="text-center">
                                    <?= Html::a('<i class="fas fa-plus-circle"></i> ออก PV ทันที', ['create', 'ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_NONE_PR, 'ref_id' => $npr->id], [
                                        'class' => 'btn btn-xs btn-success font-weight-bold shadow-sm',
                                        'title' => 'ดึงไปทำใบสำคัญจ่าย PV พร้อมออกใบหัก ณ ที่จ่าย'
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h3 class="card-title text-primary"><i class="fas fa-file-invoice-dollar me-2"></i><?= Html::encode($this->title) ?></h3>
            <div class="card-tools float-end">
                <?= Html::a('<i class="fas fa-plus"></i> สร้างรายการใหม่', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => true,
                'bordered' => false,
                'striped' => true,
                'hover' => true,
                'tableOptions' => ['class' => 'table table-bordered table-striped mb-0'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'voucher_no',
                        'headerOptions' => ['style' => 'width: 15%'],
                        'contentOptions' => ['class' => 'fw-bold text-primary'],
                    ],
                    [
                        'attribute' => 'trans_date',
                        'format' => 'date',
                        'headerOptions' => ['style' => 'width: 12%'],
                    ],
                    'recipient_name',
                    [
                        'attribute' => 'payment_method',
                        'value' => function($model) {
                            $options = \backend\models\PaymentVoucher::getPaymentMethodOptions();
                            return $options[$model->payment_method] ?? '-';
                        },
                        'filter' => \backend\models\PaymentVoucher::getPaymentMethodOptions(),
                        'headerOptions' => ['style' => 'width: 15%'],
                    ],
                    [
                        'attribute' => 'amount',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end fw-bold'],
                        'headerOptions' => ['class' => 'text-center', 'style' => 'width: 12%'],
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'template' => '{print} {view} {update} {delete}',
                        'buttons' => [
                            'print' => function($url, $model) {
                                $btn1 = Html::a('<i class="fas fa-list-alt"></i> 1', ['print', 'id' => $model->id, 'step' => 1], [
                                    'title' => 'พิมพ์ใบรวมบิล (สเต็ป 1)',
                                    'class' => 'btn btn-xs btn-outline-secondary me-1',
                                    'target' => '_blank',
                                    'data-pjax' => '0'
                                ]);
                                $btn2 = Html::a('<i class="fas fa-file-invoice-dollar"></i> 2', ['print', 'id' => $model->id, 'step' => 2], [
                                    'title' => 'พิมพ์ใบจ่ายเงิน & บัญชี (สเต็ป 2)',
                                    'class' => 'btn btn-xs btn-outline-success me-1',
                                    'target' => '_blank',
                                    'data-pjax' => '0'
                                ]);
                                return $btn1 . $btn2;
                            },
                        ],
                        'headerOptions' => ['style' => 'width: 15%'],
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>

<style>
.card { border-radius: 12px; border: none; }
.card-header { border-bottom: 1px solid #eee; }
.grid-view .table { margin-bottom: 0; }
.grid-view .table thead th { background: #f8f9fc; color: #4e73df; font-weight: 600; padding: 12px 15px; }
.grid-view .table tbody td { padding: 12px 15px; vertical-align: middle; }
</style>
