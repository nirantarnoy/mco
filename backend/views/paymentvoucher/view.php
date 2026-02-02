<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\PaymentVoucher */

$this->title = $model->voucher_no;
$this->params['breadcrumbs'][] = ['label' => 'Payment Voucher', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="payment-voucher-view">

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-primary"><i class="fas fa-file-invoice-dollar me-2"></i>รายละเอียด Payment Voucher: <?= Html::encode($this->title) ?></h5>
            <div>
                <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], ['class' => 'btn btn-secondary', 'target' => '_blank']) ?>
                <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fas fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?',
                        'method' => 'post',
                    ],
                ]) ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'voucher_no',
                            'trans_date:date',
                            'recipient_name',
                            [
                                'attribute' => 'payment_method',
                                'value' => function($model) {
                                    $options = \backend\models\PaymentVoucher::getPaymentMethodOptions();
                                    return $options[$model->payment_method] ?? '-';
                                },
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'cheque_no',
                            'cheque_date:date',
                            'amount:decimal',
                            'paid_for',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">รายละเอียดรายการ</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 5%">#</th>
                        <th style="width: 12%">Code Acc.</th>
                        <th style="width: 12%">Code Bill</th>
                        <th style="width: 23%">Description</th>
                        <th style="width: 23%"></th>
                        <th class="text-end" style="width: 12%">Debit</th>
                        <th class="text-end" style="width: 12%">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_debit = 0;
                    $total_credit = 0;
                    foreach ($model->paymentVoucherLines as $index => $line): 
                        $total_debit += $line->debit;
                        $total_credit += $line->credit;
                        
                        // แยก description ออกเป็น 2 ช่อง
                        $descriptions = explode('|||', $line->description);
                        $desc1 = $descriptions[0] ?? '';
                        $desc2 = $descriptions[1] ?? '';
                    ?>
                        <tr>
                            <td class="text-center"><?= $index + 1 ?></td>
                            <td><?= Html::encode($line->account_code) ?></td>
                            <td><?= Html::encode($line->bill_code) ?></td>
                            <td><?= Html::encode($desc1) ?></td>
                            <td><?= Html::encode($desc2) ?></td>
                            <td class="text-end"><?= number_format($line->debit, 2) ?></td>
                            <td class="text-end"><?= number_format($line->credit, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="5" class="text-end">รวมทั้งสิ้น</td>
                        <td class="text-end"><?= number_format($total_debit, 2) ?></td>
                        <td class="text-end"><?= number_format($total_credit, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>

<style>
.card { border-radius: 12px; border: none; overflow: hidden; }
.detail-view th { width: 30%; background-color: #f8f9fc; }
</style>
