<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PaymentVoucherSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Payment Voucher';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-voucher-index">

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
                                return Html::a('<i class="fas fa-print"></i>', ['print', 'id' => $model->id], [
                                    'title' => 'พิมพ์',
                                    'class' => 'btn btn-sm btn-outline-secondary me-1',
                                    'target' => '_blank',
                                    'data-pjax' => '0'
                                ]);
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
