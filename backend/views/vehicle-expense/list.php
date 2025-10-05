<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\ActiveForm;

$this->title = 'รายการค่าใช้จ่ายรถยนต์';
$this->params['breadcrumbs'][] = $this->title;
?>
<!-- Flash Messages -->
<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i> สำเร็จ!</h4>
        <?= Yii::$app->session->getFlash('success') ?>
    </div>
<?php endif; ?>

<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-ban"></i> ข้อผิดพลาด!</h4>
        <?= Yii::$app->session->getFlash('error') ?>
    </div>
<?php endif; ?>

<?php if (Yii::$app->session->hasFlash('warning')): ?>
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-warning"></i> คำเตือน!</h4>
        <?= Yii::$app->session->getFlash('warning') ?>
    </div>
<?php endif; ?>
<div class="vehicle-expense-list">
    <div class="box box-primary">
        <div class="box-header with-border">
            <div class="box-tools">
                <?= Html::a('<i class="fa fa-upload"></i> นำเข้าข้อมูล', ['import'], [
                    'class' => 'btn btn-success',
                ]) ?>
                <?= Html::a('<i class="fa fa-download"></i> ดาวน์โหลด Template', ['download-template'], [
                    'class' => 'btn btn-info',
                ]) ?>
            </div>
        </div>

        <!-- ฟอร์มค้นหา -->
        <div class="box-body">
            <?php $form = ActiveForm::begin([
                'action' => ['list'],
                'method' => 'get',
                'options' => ['class' => 'form-inline'],
            ]); ?>

            <div class="form-group">
                <?= Html::textInput('job_no', Yii::$app->request->get('job_no'), [
                    'class' => 'form-control',
                    'placeholder' => 'เลขที่ใบงาน',
                    'style' => 'width: 180px;',
                ]) ?>
            </div>

            <div class="form-group">
                <?= Html::textInput('vehicle_no', Yii::$app->request->get('vehicle_no'), [
                    'class' => 'form-control',
                    'placeholder' => 'ทะเบียนรถ',
                    'style' => 'width: 120px;',
                ]) ?>
            </div>

            <div class="form-group">
                <?= Html::input('date', 'date_from', Yii::$app->request->get('date_from'), [
                    'class' => 'form-control',
                    'placeholder' => 'วันที่เริ่มต้น',
                    'style' => 'width: 150px;',
                ]) ?>
            </div>

            <div class="form-group">
                <?= Html::input('date', 'date_to', Yii::$app->request->get('date_to'), [
                    'class' => 'form-control',
                    'placeholder' => 'วันที่สิ้นสุด',
                    'style' => 'width: 150px;',
                ]) ?>
            </div>

            <?= Html::submitButton('<i class="fa fa-search"></i> ค้นหา', [
                'class' => 'btn btn-primary',
            ]) ?>

            <?= Html::a('<i class="fa fa-refresh"></i> รีเซ็ต', ['list'], [
                'class' => 'btn btn-default',
            ]) ?>

            <?php ActiveForm::end(); ?>
        </div>

        <!-- ตารางข้อมูล -->
        <div class="box-body table-responsive no-padding">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover table-striped'],
                'summary' => '<div class="text-muted" style="padding: 10px;">แสดง {begin}-{end} จาก {totalCount} รายการ</div>',
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],

                    [
                        'attribute' => 'expense_date',
                        'label' => 'วันที่ใช้งานรถ',
                        'format' => 'raw',
                        'value' => function($model) {
                            return '<span class="text-nowrap">' .
                                Yii::$app->formatter->asDate($model->expense_date, 'php:d/m/Y') .
                                '</span>';
                        },
                        'headerOptions' => ['style' => 'width: 110px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],

                    [
                        'attribute' => 'job_no',
                        'label' => 'Job No.',
                        'format' => 'raw',
                        'value' => function($model) {
                            if ($model->job_no) {
                                return Html::tag('span', $model->job_no, [
                                    'class' => 'label label-primary',
                                    'style' => 'font-size: 11px; padding: 4px 8px;',
                                ]);
                            }
                            return '<span class="text-muted">-</span>';
                        },
                        'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],

                    [
                        'attribute' => 'vehicle_no',
                        'label' => 'ทะเบียนรถ',
                        'format' => 'raw',
                        'value' => function($model) {
                            return $model->vehicle_no ?
                                '<strong>' . Html::encode($model->vehicle_no) . '</strong>' :
                                '<span class="text-muted">-</span>';
                        },
                        'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],

                    [
                        'attribute' => 'total_distance',
                        'label' => 'ระยะทางรวม(กม.)',
                        'format' => 'raw',
                        'value' => function($model) {
                            if ($model->total_distance > 0) {
                                return '<span class="badge bg-blue">' .
                                    number_format($model->total_distance, 2) .
                                    '</span>';
                            }
                            return '<span class="text-muted">0</span>';
                        },
                        'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],

                    [
                        'attribute' => 'vehicle_cost',
                        'label' => 'ค่าใช้จ่ายรถ',
                        'format' => 'raw',
                        'value' => function($model) {
                            if ($model->vehicle_cost > 0) {
                                return '<span class="text-success"><strong>' .
                                    number_format($model->vehicle_cost, 2) .
                                    '</strong></span>';
                            }
                            return '<span class="text-muted">0.00</span>';
                        },
                        'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: right; padding-right: 15px;'],
                    ],

                    [
                        'attribute' => 'passenger_count',
                        'label' => 'จำนวน<br>ผู้ใช้รถ',
                        'format' => 'raw',
                        'value' => function($model) {
                            if ($model->passenger_count > 0) {
                                return '<span class="badge bg-purple">' .
                                    $model->passenger_count .
                                    '</span>';
                            }
                            return '<span class="text-muted">0</span>';
                        },
                        'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],

                    [
                        'attribute' => 'total_wage',
                        'format' => 'raw',
                        'label' => 'ค่าจ้างรวม',
                        'value' => function($model) {
                            if ($model->total_wage > 0) {
                                return '<span class="text-danger"><strong>' .
                                    number_format($model->total_wage, 2) .
                                    '</strong></span>';
                            }
                            return '<span class="text-muted">0.00</span>';
                        },
                        'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: right; padding-right: 15px;'],
                    ],

                    [
                        'label' => 'รวมทั้งหมด(บาท)',
                        'format' => 'raw',
                        'value' => function($model) {
                            $total = $model->vehicle_cost + $model->total_wage;
                            if ($total > 0) {
                                return '<span class="text-primary"><strong>' .
                                    number_format($total, 2) .
                                    '</strong></span>';
                            }
                            return '<span class="text-muted">0.00</span>';
                        },
                        'headerOptions' => ['style' => 'width: 120px; text-align: center; background-color: #f9f9f9;'],
                        'contentOptions' => ['style' => 'text-align: right; padding-right: 15px; background-color: #f9f9f9; font-weight: bold;'],
                    ],

                    [
                        'attribute' => 'import_batch',
                        'label' => 'Batch',
                        'format' => 'raw',
                        'value' => function($model) {
                            if ($model->import_batch) {
                                return '<small class="text-muted" title="' .
                                    Html::encode($model->import_batch) . '">' .
                                    substr($model->import_batch, 0, 14) .
                                    '</small>';
                            }
                            return '<span class="text-muted">-</span>';
                        },
                        'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'จัดการ',
                        'template' => '{delete-batch}',
                        'buttons' => [
                            'delete-batch' => function ($url, $model) {
                                return Html::a('<i class="fa fa-trash"></i>',
                                    ['delete-batch', 'batch' => $model->import_batch], [
                                        'class' => 'btn btn-danger btn-xs',
                                        'title' => 'ลบ Batch นี้',
                                        'data' => [
                                            'confirm' => 'ต้องการลบข้อมูลทั้ง Batch: ' . $model->import_batch . ' ?',
                                            'method' => 'post',
                                        ],
                                    ]);
                            },
                        ],
                        'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],
                ],
            ]); ?>
        </div>
    </div>

    <!-- สรุปยอดรวม -->
    <?php
    $query = clone $dataProvider->query;
    $totalVehicleCost = $query->sum('vehicle_cost') ?? 0;
    $totalWage = $query->sum('total_wage') ?? 0;
    $totalDistance = $query->sum('total_distance') ?? 0;
    $totalRecords = $dataProvider->totalCount;
    $grandTotal = $totalVehicleCost + $totalWage;
    ?>

    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-files-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">จำนวนรายการ</span>
                    <span class="info-box-number"><?= number_format($totalRecords) ?></span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fa fa-road"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">ระยะทางรวม</span>
                    <span class="info-box-number"><?= number_format($totalDistance, 2) ?> <small>กม.</small></span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-car"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">ค่าใช้จ่ายรถ</span>
                    <span class="info-box-number"><?= number_format($totalVehicleCost, 2) ?> <small>บาท</small></span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">ค่าจ้างรวม</span>
                    <span class="info-box-number"><?= number_format($totalWage, 2) ?> <small>บาท</small></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ยอดรวมทั้งหมด -->
    <div class="box box-solid bg-light-blue">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h3 style="margin: 10px 0;">
                        <i class="fa fa-calculator"></i>
                        ยอดรวมทั้งหมด:
                        <strong style="font-size: 32px;"><?= number_format($grandTotal, 2) ?></strong>
                        <span style="font-size: 20px;">บาท</span>
                    </h3>
                    <p class="text-muted">
                        (ค่าใช้จ่ายรถ + ค่าจ้างรวม)
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- สรุปตามใบงาน -->
    <?php if (!empty(Yii::$app->request->get('job_no'))): ?>
        <?php
        $jobNo = Yii::$app->request->get('job_no');
        $jobRecords = \backend\models\VehicleExpense::find()
            ->where(['like', 'job_no', $jobNo])
            ->all();

        $jobTotalDistance = 0;
        $jobTotalVehicleCost = 0;
        $jobTotalWage = 0;

        foreach ($jobRecords as $record) {
            $jobTotalDistance += $record->total_distance;
            $jobTotalVehicleCost += $record->vehicle_cost;
            $jobTotalWage += $record->total_wage;
        }

        $jobGrandTotal = $jobTotalVehicleCost + $jobTotalWage;
        ?>

        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-pie-chart"></i>
                    สรุปค่าใช้จ่ายของ Job No: <strong><?= Html::encode($jobNo) ?></strong>
                </h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="description-block">
                            <h5 class="description-header"><?= count($jobRecords) ?> รายการ</h5>
                            <span class="description-text">จำนวนเที่ยว</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="description-block">
                            <h5 class="description-header"><?= number_format($jobTotalDistance, 2) ?> กม.</h5>
                            <span class="description-text">ระยะทางรวม</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="description-block">
                            <h5 class="description-header text-success"><?= number_format($jobTotalVehicleCost, 2) ?> บาท</h5>
                            <span class="description-text">ค่าใช้จ่ายรถ</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="description-block">
                            <h5 class="description-header text-danger"><?= number_format($jobTotalWage, 2) ?> บาท</h5>
                            <span class="description-text">ค่าจ้างรวม</span>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <h4>
                        ยอดรวมทั้งหมดของ Job นี้:
                        <strong class="text-primary" style="font-size: 24px;">
                            <?= number_format($jobGrandTotal, 2) ?>
                        </strong>
                        บาท
                    </h4>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .table thead th {
        vertical-align: middle;
        background-color: #f4f4f4;
        font-weight: 600;
    }
    .info-box {
        min-height: 90px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }
    .info-box-number {
        font-weight: bold;
    }
    .info-box-icon {
        font-size: 45px;
    }
    .description-block {
        padding: 10px 0;
    }
    .description-header {
        font-size: 24px;
        font-weight: bold;
        margin: 10px 0;
    }
    .description-text {
        text-transform: uppercase;
        font-size: 12px;
        color: #999;
    }
    .form-inline .form-group {
        margin-right: 10px;
        margin-bottom: 10px;
    }
    .box-body {
        padding: 15px;
    }
    .bg-light-blue {
        background-color: #3c8dbc !important;
        color: white;
    }
    .bg-light-blue .text-muted {
        color: rgba(255,255,255,0.7) !important;
    }
</style>