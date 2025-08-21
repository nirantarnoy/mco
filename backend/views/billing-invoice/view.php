<?php
// backend/views/billing-invoice/view.php
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'ใบวางบิล: ' . $model->billing_number;
$this->params['breadcrumbs'][] = ['label' => 'ใบวางบิล', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="billing-invoice-view">
    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('พิมพ์', ['print', 'id' => $model->id], ['class' => 'btn btn-info', 'target' => '_blank']) ?>
        <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <div class="row">
        <div class="col-md-6">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'billing_number',
                    [
                        'attribute' => 'billing_date',
                        'value' => function ($model) {
                            return date('m-d-Y', strtotime($model->billing_date));
                        }
                    ],
                    [
                        'attribute' => 'customer_id',
                        'value' => $model->customer->code . ' - ' . $model->customer->name,
                        'label' => 'ลูกค้า',
                    ],
                    [
                        'attribute' => 'subtotal',
                        'value' => function ($model) {
                            return number_format($model->subtotal, 2);
                        }
                    ]
                    ,
                    'discount_percent',
                    [
                        'attribute' => 'discount_amount',
                        'value' => function ($model) {
                            return number_format($model->discount_amount, 2);
                        }
                    ],
                    'vat_percent',
                    [
                        'attribute' => 'vat_amount',
                        'value' => function ($model) {
                            return number_format($model->vat_amount, 2);
                        }
                    ],
                    [
                        'attribute' => 'total_amount',
                        'value' => function ($model) {
                            return number_format($model->total_amount, 2);
                        }
                    ],
                    'credit_terms',
                    [
                        'attribute' => 'payment_due_date',
                        'value' => function ($model) {
                            return date('m-d-Y', strtotime($model->payment_due_date));
                        }
                    ]
                    ,
                ],
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'status',
                    'notes:ntext',
                    [
                            'attribute' => 'created_at',
                            'value' => function ($model) {
                                return date('m-d-Y H:i:s', strtotime($model->created_at));
                            }
                    ],
                    [
                            'attribute' => 'updated_at',
                            'value' => function ($model) {
                                return date('m-d-Y H:i:s', strtotime($model->updated_at));
                            }
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <h3>รายการใบแจ้งหนี้ที่รวมในใบวางบิล</h3>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th style="width: 5%">ลำดับ</th>
            <th>เลขที่ใบแจ้งหนี้</th>
            <th>วันที่</th>
            <th>ประเภท</th>
            <th class="text-right">จำนวนเงิน</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($model->billingInvoiceItems as $item): ?>
            <tr>
                <td><?= $item->item_seq ?></td>
                <td><?= Html::a($item->invoice->invoice_number, ['/invoice/view', 'id' => $item->invoice_id]) ?></td>
                <td><?=date('m-d-Y', strtotime($item->invoice->invoice_date)) ?></td>
                <td><?= $item->invoice->invoice_type == 'tax_invoice' ? 'ใบกำกับภาษี' : 'ใบแจ้งหนี้' ?></td>
                <td class="text-right"><?= number_format($item->amount, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <th colspan="4" class="text-right">รวมทั้งสิ้น:</th>
            <th class="text-right"><?= number_format($model->total_amount, 2) ?></th>
        </tr>
        </tfoot>
    </table>

</div>