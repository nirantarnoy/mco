<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $from_date string */
/* @var $to_date string */

$this->title = 'รายงานสรุปการจ่ายเงิน';
$this->params['breadcrumbs'][] = ['label' => 'รายการบันทึกการจ่ายเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purch-payment-report">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => ['report'],
                'options' => ['class' => 'form-inline mb-3'],
            ]); ?>

            <div class="row w-100">
                <div class="col-md-4">
                    <label class="mr-2">จากวันที่:</label>
                    <?= DatePicker::widget([
                        'name' => 'from_date',
                        'value' => $from_date,
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ],
                        'options' => ['class' => 'form-control', 'autocomplete' => 'off']
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <label class="mr-2">ถึงวันที่:</label>
                    <?= DatePicker::widget([
                        'name' => 'to_date',
                        'value' => $to_date,
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ],
                        'options' => ['class' => 'form-control', 'autocomplete' => 'off']
                    ]); ?>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <?= Html::submitButton('<i class="fas fa-search"></i> ค้นหา', ['class' => 'btn btn-primary mr-2']) ?>
                    
                    <?php if ($dataProvider && $dataProvider->totalCount > 0): ?>
                        <?= Html::a('<i class="fas fa-file-pdf"></i> Print PDF', 
                            ['report', 'from_date' => $from_date, 'to_date' => $to_date, 'export' => 'pdf'], 
                            ['class' => 'btn btn-danger mr-2', 'target' => '_blank']) ?>
                            
                        <?= Html::a('<i class="fas fa-file-excel"></i> Export Excel', 
                            ['report', 'from_date' => $from_date, 'to_date' => $to_date, 'export' => 'excel'], 
                            ['class' => 'btn btn-success', 'target' => '_blank']) ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

            <?php if ($dataProvider): ?>
                <div class="mt-4">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            ['class' => 'kartik\grid\SerialColumn'],
                            [
                                'attribute' => 'trans_date',
                                'label' => 'วันที่โอน',
                                'format' => ['date', 'php:d/m/Y'],
                                'value' => function($model) {
                                    return $model->trans_date;
                                }
                            ],
                            [
                                'label' => 'เลขที่ใบสั่งซื้อ',
                                'value' => function($model) {
                                    return $model->purchPayment->purch->purch_no ?? '-';
                                }
                            ],
                            [
                                'label' => 'ผู้ขาย',
                                'value' => function($model) {
                                    return $model->purchPayment->purch->vendor_name ?? '-';
                                }
                            ],
                            [
                                'attribute' => 'bank_name',
                                'label' => 'ธนาคาร',
                            ],
                            [
                                'attribute' => 'pay_amount',
                                'label' => 'จำนวนเงิน',
                                'format' => ['decimal', 2],
                                'contentOptions' => ['class' => 'text-right'],
                                'pageSummary' => true,
                            ],
                            [
                                'attribute' => 'nodet',
                                'label' => 'หมายเหตุ',
                            ],
                        ],
                        'showPageSummary' => true,
                        'panel' => [
                            'type' => GridView::TYPE_DEFAULT,
                            'heading' => 'รายการจ่ายเงิน',
                        ],
                    ]); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
