<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Url;
use backend\models\Job;

/* @var $this yii\web\View */
/* @var $searchModel JobReportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'รายงานใบงาน';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="job-report-index">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        <?= Html::encode($this->title) ?>
                    </h3>
                </div>
                <div class="card-body">

                    <!-- ฟอร์มค้นหา -->
                    <div class="search-form">
                        <?php $form = ActiveForm::begin([
                            'method' => 'get',
                            'options' => ['class' => 'form-inline mb-3'],
                        ]); ?>

                        <div class="row">
                            <div class="col-md-3">
                                <?= $form->field($searchModel, 'job_no')->textInput([
                                    'placeholder' => 'เลขใบงาน',
                                    'class' => 'form-control'
                                ])->label(false) ?>
                            </div>

                            <div class="col-md-2">
                                <?= $form->field($searchModel, 'status')->widget(Select2::class, [
                                    'data' => ['' => 'ทุกสถานะ'] + Job::getStatusOptions(),
                                    'options' => ['placeholder' => 'เลือกสถานะ'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ])->label(false) ?>
                            </div>

                            <div class="col-md-2">
                                <?= $form->field($searchModel, 'start_date_from')->widget(DatePicker::class, [
                                    'options' => ['placeholder' => 'วันที่เริ่ม'],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd'
                                    ]
                                ])->label(false) ?>
                            </div>

                            <div class="col-md-2">
                                <?= $form->field($searchModel, 'start_date_to')->widget(DatePicker::class, [
                                    'options' => ['placeholder' => 'ถึงวันที่'],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd'
                                    ]
                                ])->label(false) ?>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= Html::submitButton('<i class="fas fa-search"></i> ค้นหา', [
                                        'class' => 'btn btn-primary'
                                    ]) ?>
                                    <?= Html::a('<i class="fas fa-redo"></i> ล้าง', ['index'], [
                                        'class' => 'btn btn-secondary'
                                    ]) ?>
                                </div>
                            </div>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>

                    <!-- ปุ่มการดำเนินการ -->
                    <div class="action-buttons mb-3">
                        <div class="btn-group" role="group">
                            <?= Html::a('<i class="fas fa-print"></i> พิมพ์',
                                ['print'] + Yii::$app->request->queryParams, [
                                    'class' => 'btn btn-info',
                                    'target' => '_blank'
                                ]) ?>

                            <?= Html::a('<i class="fas fa-file-pdf"></i> PDF',
                                ['print-pdf'] + Yii::$app->request->queryParams, [
                                    'class' => 'btn btn-danger',
                                    'target' => '_blank'
                                ]) ?>

                            <?= Html::a('<i class="fas fa-file-excel"></i> Excel',
                                ['export-excel'] + Yii::$app->request->queryParams, [
                                    'class' => 'btn btn-success'
                                ]) ?>
                        </div>
                    </div>

                    <!-- ตารางข้อมูล -->
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => ['class' => 'table table-striped table-bordered'],
                        'layout' => "{summary}\n{items}\n{pager}",
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn','headerOptions' => ['style' => 'text-align: center'], 'contentOptions' => ['style' => 'text-align: center']],

                            [
                                'attribute' => 'job_no',
                                'label' => 'เลขใบงาน',
                                'format' => 'text',
                            ],

                            [
                                'attribute' => 'start_date',
                                'label' => 'วันที่เริ่ม',
                                //  'format' => 'date',
                                'contentOptions' => ['style' => 'width: 120px;'],
                                'value' => function ($model) {
                                    return date('m/d/Y', strtotime($model->start_date));
                                }
                            ],

                            [
                                'attribute' => 'status',
                                'label' => 'สถานะ',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::tag('span', $model->getStatusText(), [
                                        'class' => 'badge badge-' . $model->getStatusColor()
                                    ]);
                                },
                                'contentOptions' => ['style' => 'width: 120px; text-align: center;'],
                            ],

                            [
                                'label' => 'มูลค่างาน',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return '<div style="text-align: right;">' .
                                        number_format($model->job_amount, 2) .
                                        '</div>';
                                },
                                'contentOptions' => ['style' => 'width: 120px;'],
                            ],

                            [
                                'label' => 'มูลค่าเบิกของ',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $totalWithdraw = $model->getTotalWithdrawAmount();
                                    return '<div style="text-align: right;">' .
                                        number_format($totalWithdraw, 2) .
                                        '</div>';
                                },
                                'contentOptions' => ['style' => 'width: 120px;'],
                            ],

                            [
                                'label' => 'กำไร/ขาดทุน',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $profitLoss = $model->getProfitLoss();
                                    $color = $model->getProfitLossColor();

                                    return '<div style="text-align: right;">' .
                                        Html::tag('span', number_format($profitLoss, 2), [
                                            'class' => 'text-' . $color . ' font-weight-bold'
                                        ]) .
                                        '</div>';
                                },
                                'contentOptions' => ['style' => 'width: 120px;'],
                            ],

                            [
                                'label' => 'เปอร์เซ็นต์',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $percentage = $model->getProfitLossPercentage();
                                    $color = $model->getProfitLossColor();

                                    return '<div style="text-align: right;">' .
                                        Html::tag('span', number_format($percentage, 2) . '%', [
                                            'class' => 'text-' . $color . ' font-weight-bold'
                                        ]) .
                                        '</div>';
                                },
                                'contentOptions' => ['style' => 'width: 100px;'],
                            ],

                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'จัดการ',
                                'template' => '{view} {timeline}',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-eye"></i>', ['/job/view', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'title' => 'ดูรายละเอียด',
                                            'data-pjax' => '0'
                                        ]);
                                    },
                                    'timeline' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-project-diagram"></i>', ['/job/timeline', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-outline-success',
                                            'title' => 'Timeline รายละเอียด',
                                            'data-pjax' => '0',
                                            'target' => '_blank'
                                        ]);
                                    },
                                ],
                                'contentOptions' => ['style' => 'width: 120px; text-align: center;'],
                            ],
                        ],
                    ]); ?>

                    <!-- สรุปข้อมูล -->
                    <?php
                    $models = $dataProvider->getModels();
                    $totalJobAmount = 0;
                    $totalWithdrawAmount = 0;
                    $totalProfitLoss = 0;

                    foreach ($models as $model) {
                        $totalJobAmount += $model->job_amount;
                        $totalWithdrawAmount += $model->getTotalWithdrawAmount();
                    }
                    $totalProfitLoss = $totalJobAmount - $totalWithdrawAmount;
                    $totalPercentage = $totalJobAmount > 0 ? ($totalProfitLoss / $totalJobAmount) * 100 : 0;
                    ?>

                    <div class="summary-section mt-4">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-calculator"></i> สรุปข้อมูลรวม</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h6 class="text-muted">จำนวนใบงาน</h6>
                                            <h4 class="text-primary"><?= number_format(count($models)) ?> ใบ</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h6 class="text-muted">มูลค่างานรวม</h6>
                                            <h4 class="text-info"><?= number_format($totalJobAmount, 2) ?> บาท</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h6 class="text-muted">มูลค่าเบิกของรวม</h6>
                                            <h4 class="text-warning"><?= number_format($totalWithdrawAmount, 2) ?> บาท</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h6 class="text-muted">กำไร/ขาดทุนรวม</h6>
                                            <h4 class="<?= $totalProfitLoss >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($totalProfitLoss, 2) ?> บาท
                                                <small>(<?= number_format($totalPercentage, 2) ?>%)</small>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .search-form {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .action-buttons {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 15px;
    }

    .summary-section {
        border-top: 1px solid #dee2e6;
        padding-top: 20px;
    }

    .table th {
        background-color: #343a40;
        color: white;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-group .btn {
        margin-right: 5px;
    }

    .badge {
        font-size: 0.8em;
    }

    /* สไตล์สำหรับสถานะกิจกรรม */
    .activity-status-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 4px;
        padding: 5px;
    }

    .activity-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 15px;
        font-size: 0.7em;
        font-weight: 500;
        text-align: center;
        min-width: 50px;
        white-space: nowrap;
        transition: all 0.2s ease;
        cursor: default;
    }

    .activity-completed {
        background-color: #28a745;
        color: white;
        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    }

    .activity-pending {
        background-color: #e9ecef;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }

    .activity-badge:hover {
        transform: scale(1.05);
        opacity: 0.9;
    }

    @media (max-width: 1200px) {
        .activity-status-container {
            flex-direction: column;
            gap: 2px;
        }

        .activity-badge {
            font-size: 0.65em;
            padding: 2px 6px;
            min-width: 40px;
        }
    }

    @media print {
        .search-form,
        .action-buttons,
        .summary-section {
            display: none;
        }
    }
</style>

<script>
    $(document).ready(function() {
        // เพิ่ม tooltip สำหรับปุ่มต่างๆ
        $('[title]').tooltip();

        // Auto submit form เมื่อเปลี่ยนสถานะ
        $('#jobreportsearch-status').on('change', function() {
            $(this).closest('form').submit();
        });
    });
</script>