<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $job_id int */
/* @var $product_id int */
/* @var $from_date string */
/* @var $to_date string */

$this->title = 'รายงานสรุปยอดการเบิก และ คืนสินค้า';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="borrow-return-report">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <div class="report-filter mb-4">
                <?php $form = ActiveForm::begin([
                    'action' => ['borrow-return-report'],
                    'method' => 'get',
                ]); ?>

                <div class="row">
                    <div class="col-md-3">
                        <label>เลขที่ใบงาน</label>
                        <?= Select2::widget([
                            'name' => 'job_id',
                            'value' => $job_id,
                            'data' => \yii\helpers\ArrayHelper::map(\backend\models\Job::find()->all(), 'id', 'job_no'),
                            'options' => ['placeholder' => 'เลือกใบงาน...'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-3">
                        <label>สินค้า</label>
                        <?= Select2::widget([
                            'name' => 'product_id',
                            'value' => $product_id,
                            'data' => \yii\helpers\ArrayHelper::map(\backend\models\Product::find()->all(), 'id', function($model){
                                return $model->code . ' ' . $model->name;
                            }),
                            'options' => ['placeholder' => 'เลือกสินค้า...'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-2">
                        <label>จากวันที่</label>
                        <?= DatePicker::widget([
                            'name' => 'from_date',
                            'value' => $from_date,
                            'pluginOptions' => [
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true
                            ]
                        ]); ?>
                    </div>
                    <div class="col-md-2">
                        <label>ถึงวันที่</label>
                        <?= DatePicker::widget([
                            'name' => 'to_date',
                            'value' => $to_date,
                            'pluginOptions' => [
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true
                            ]
                        ]); ?>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <?= Html::submitButton('ค้นหา', ['class' => 'btn btn-primary mr-1']) ?>
                            <?= Html::a('ล้าง', ['borrow-return-report'], ['class' => 'btn btn-outline-secondary mr-1']) ?>
                            <button type="button" class="btn btn-info" onclick="window.print()"><i class="fa fa-print"></i> พิมพ์</button>
                        </div>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>

            <div class="table-responsive print-area">
                <div class="print-header d-none d-print-block text-center mb-4">
                    <h4>รายงานสรุปยอดการเบิก และ คืนสินค้า</h4>
                    <?php if ($job_id): ?>
                        <p>ใบงาน: <?= \backend\models\Job::findJobNo($job_id) ?></p>
                    <?php endif; ?>
                    <?php if ($product_id): ?>
                        <?php $p = \backend\models\Product::findOne($product_id); ?>
                        <p>สินค้า: <?= $p ? $p->code . ' ' . $p->name : '' ?></p>
                    <?php endif; ?>
                    <?php if ($from_date || $to_date): ?>
                        <p>วันที่: <?= $from_date ?> ถึง <?= $to_date ?></p>
                    <?php endif; ?>
                </div>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'responsive' => true,
                    'hover' => true,
                    'striped' => true,
                    'bordered' => true,
                    'pjax' => true,
                    'summary' => "แสดง {begin} - {end} จากทั้งหมด {totalCount} รายการ",
                    'columns' => [
                        [
                            'label' => 'Job No',
                            'value' => function ($model) {
                                $job = \backend\models\Job::findOne($model['job_id']);
                                return $job ? $job->job_no : '-';
                            },
                            'headerOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'label' => 'รหัสเครื่องมือ',
                            'value' => function ($model) {
                                $product = \backend\models\Product::findOne($model['product_id']);
                                return $product ? $product->code : '-';
                            },
                        ],
                        [
                            'label' => 'รายการเครื่องมือ',
                            'value' => function ($model) {
                                $product = \backend\models\Product::findOne($model['product_id']);
                                return $product ? $product->name : '-';
                            },
                        ],
                        [
                            'label' => 'เบิก',
                            'value' => function ($model) {
                                return number_format($model['total_withdraw'], 0);
                            },
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'label' => 'คืนเบิก',
                            'value' => function ($model) {
                                return number_format($model['total_return_withdraw'], 0);
                            },
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'label' => 'ยืม',
                            'value' => function ($model) {
                                return number_format($model['total_borrow'], 0);
                            },
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'label' => 'คืนยืม',
                            'value' => function ($model) {
                                return number_format($model['total_return_borrow'], 0);
                            },
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'label' => 'คงค้าง',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $outstanding = $model['total_borrow'] - $model['total_return_borrow'];
                                if ($outstanding < 0) $outstanding = 0;
                                return $outstanding > 0 ? '<span style="color: orange; font-weight: bold;">' . number_format($outstanding, 0) . '</span>' : number_format($outstanding, 0);
                            },
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'label' => 'เสียหาย',
                            'value' => function ($model) {
                                return number_format($model['total_damaged'], 0);
                            },
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'label' => 'สูญหาย',
                            'value' => function ($model) {
                                return number_format($model['total_missing'], 0);
                            },
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'label' => 'ยอดคงเหลือ',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $balance = $model['current_stock_qty'] ?: 0;
                                return $balance > 0 ? '<span style="color: blue; font-weight: bold;">' . number_format($balance, 0) . '</span>' : number_format($balance, 0);
                            },
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'label' => 'หมายเหตุ',
                            'attribute' => 'remarks',
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .main-sidebar, .main-header, .card-header, .report-filter, .footer, .breadcrumb {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
        }
        .card {
            border: none !important;
        }
        .table-responsive {
            overflow: visible !important;
        }
        .print-area {
            width: 100%;
        }
    }
</style>
