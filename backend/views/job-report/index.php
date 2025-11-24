<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Url;
use backend\models\Job;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $searchModel JobReportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'รายงานใบงาน';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="job-report-index">

    <div class="row">
        <div class="col-lg-12">
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
                            'options' => ['class' => 'search-form-wrapper'],
                        ]); ?>

                        <div class="row">
                            <!-- บริษัท -->
                            <div class="col-lg-2 col-md-6 col-sm-6 mb-3">
                                <?= $form->field($searchModel, 'company_id')->widget(Select2::class, [
                                    'data' => ['0' => 'ทุกบริษัท'] + \yii\helpers\ArrayHelper::map(\backend\models\Company::find()->all(), 'id', 'name'),
                                    'options' => [
                                        'placeholder' => 'เลือกบริษัท',
                                        'class' => 'form-control'
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'multiple' => true,
                                        'width' => '100%'
                                    ],
                                ])->label(false) ?>
                            </div>

                            <!-- เลขใบงาน -->
                            <div class="col-lg-2 col-md-6 col-sm-6 mb-3">
                                <?= $form->field($searchModel, 'job_no')->textInput([
                                    'placeholder' => 'เลขใบงาน',
                                    'class' => 'form-control w-100'
                                ])->label(false) ?>
                            </div>

                            <!-- สถานะ -->
                            <div class="col-lg-2 col-md-6 col-sm-6 mb-3">
                                <?= $form->field($searchModel, 'status')->widget(Select2::class, [
                                    'data' => ['' => 'ทุกสถานะ'] + Job::getStatusOptions(),
                                    'options' => [
                                        'placeholder' => 'เลือกสถานะ',
                                        'class' => 'form-control'
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'width' => '100%'
                                    ],
                                ])->label(false) ?>
                            </div>

                            <!-- วันที่เริ่ม -->
                            <div class="col-lg-2 col-md-6 col-sm-6 mb-3">
                                <?= $form->field($searchModel, 'start_date_from')->widget(DatePicker::class, [
                                    'options' => [
                                        'placeholder' => 'วันที่เริ่ม',
                                        'class' => 'form-control'
                                    ],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                        'todayHighlight' => true
                                    ],
                                    'removeButton' => false
                                ])->label(false) ?>
                            </div>

                            <!-- ถึงวันที่ -->
                            <div class="col-lg-2 col-md-6 col-sm-6 mb-3">
                                <?= $form->field($searchModel, 'start_date_to')->widget(DatePicker::class, [
                                    'options' => [
                                        'placeholder' => 'ถึงวันที่',
                                        'class' => 'form-control'
                                    ],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                        'todayHighlight' => true
                                    ],
                                    'removeButton' => false
                                ])->label(false) ?>
                            </div>

                            <!-- ปุ่มค้นหาและล้าง -->
                            <div class="col-lg-2 col-md-12 col-sm-12 mb-3">
                                <div class="btn-group-actions">
                                    <?= Html::submitButton('<i class="fas fa-search"></i> ค้นหา', [
                                        'class' => 'btn btn-primary flex-fill'
                                    ]) ?>
                                    <?= Html::a('<i class="fas fa-redo"></i> ล้าง', ['index'], [
                                        'class' => 'btn btn-secondary flex-fill'
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
                                'label' => 'สถานะกิจกรรม',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    // ตรวจสอบสถานะของแต่ละกิจกรรม
                                    $activities = [
                                        'ขอซื้อ' => $model->hasPurchaseRequest,
                                        'สั่งซื้อ' => $model->hasPurchaseOrder,
                                        'รับสินค้า' => $model->hasReceiveTransaction($model->id),
                                        'เบิกสินค้า' => $model->hasWithdrawTransaction($model->id),
                                        'แจ้งหนี้' => $model->hasDebtNotification($model->id),
                                        'กำกับภาษี' => $model->hasTaxInvoice($model->id),
                                        'ใบเสร็จ' => $model->hasReceipt($model->id),
                                        'วางบิล' => $model->hasBilling($model->id),
                                        'ชำระเงิน' => $model->hasPayment($model->id),
                                    ];

                                    $output = '<div class="activity-status-container">';
                                    foreach ($activities as $activityName => $hasActivity) {
                                        $statusClass = $hasActivity ? 'activity-completed' : 'activity-pending';
                                        $output .= '<span class="activity-badge ' . $statusClass . '" title="' . $activityName . '">' .
                                            $activityName . '</span>';
                                    }
                                    $output .= '</div>';

                                    return $output;
                                },
                                'contentOptions' => ['style' => 'width: 320px; text-align: center;'],
                            ],
                            [
                                'label' => 'ความคืบหน้า',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    // ตรวจสอบสถานะของแต่ละกิจกรรม
                                    $activities = [
                                        'ขอซื้อ' => $model->hasPurchaseRequest,
                                        'สั่งซื้อ' => $model->hasPurchaseOrder,
                                        'รับสินค้า' => $model->hasReceiveTransaction($model->id),
                                        'เบิกสินค้า' => $model->hasWithdrawTransaction($model->id),
                                        'เงินสดย่อย' => $model->hasPettyCash($model->id), // เพิ่มเงินสดย่อย
                                        'แจ้งหนี้' => $model->hasDebtNotification($model->id),
                                        'กำกับภาษี' => $model->hasTaxInvoice($model->id),
                                        'ใบเสร็จ' => $model->hasReceipt($model->id),
                                        'วางบิล' => $model->hasBilling($model->id),
                                        'ชำระเงิน' => $model->hasPayment($model->id),
                                    ];

                                    // คำนวณเปอร์เซ็นต์ความคืบหน้า
                                    $totalActivities = count($activities);
                                    $completedActivities = count(array_filter($activities));
                                    $progressPercentage = ($completedActivities / $totalActivities) * 100;

                                    // กำหนดสีของ progress bar
                                    $progressColor = $progressPercentage == 100 ? 'success' : 'warning';

                                    // สร้าง tooltip แสดงรายละเอียด
                                    $tooltipContent = [];
                                    foreach ($activities as $activityName => $hasActivity) {
                                        $icon = $hasActivity ? '✓' : '✗';
                                        $tooltipContent[] = $icon . ' ' . $activityName;
                                    }
                                    $tooltipText = implode("\n", $tooltipContent);

                                    $output = '<div class="progress-container" data-toggle="tooltip" data-placement="top" data-html="true" title="' . Html::encode($tooltipText) . '">';
                                    $output .= '<div class="progress" style="height: 25px; position: relative;">';
                                    $output .= '<div class="progress-bar bg-' . $progressColor . ' progress-bar-striped" role="progressbar" style="width: ' . $progressPercentage . '%" aria-valuenow="' . $progressPercentage . '" aria-valuemin="0" aria-valuemax="100">';
                                    $output .= '<span style="position: absolute; width: 100%; left: 0; top: 50%; transform: translateY(-50%); color: ' . ($progressPercentage > 50 ? 'white' : 'black') . '; font-weight: bold;">';
                                    $output .= number_format($progressPercentage, 0) . '% (' . $completedActivities . '/' . $totalActivities . ')';
                                    $output .= '</span>';
                                    $output .= '</div>';
                                    $output .= '</div>';

                                    // แสดงรายการกิจกรรมขนาดเล็กด้านล่าง
                                    $output .= '<div class="activity-mini-list mt-1" style="font-size: 0.7em; line-height: 1.2;">';

                                    $output .= '</div>';

                                    $output .= '</div>';

                                    return $output;
                                },
                                'contentOptions' => ['style' => 'width: 150px; text-align: center;'],
                                'headerOptions' => ['style' => 'text-align: center;'],
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
                                'label' => 'เบิก/ค่าใช้จ่าย/รถ',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $totalWithdraw = $model->getTotalWithdrawAmount();
                                    $totalJobExpense = $model->getJobExpenseAll();
                                    $totalVehicleEx = $model->getVehicleExpenseAll();
                                    return '<div style="text-align: right;">' .
                                        number_format(($totalWithdraw + $totalJobExpense + $totalVehicleEx), 2) .
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
//                                            'target' => '_blank'
                                        ]);
                                    },
                                ],
                                'contentOptions' => ['style' => 'width: 120px; text-align: center;'],
                            ],
                        ],
                    ]); ?>

                    <!-- สรุปข้อมูล -->
                    <?php
                   // $models = $dataProvider->getModels();
                    // ดึง query หลักจาก dataProvider
                    $query = clone $dataProvider->query;

                    // เอา pagination ออก เพื่อดึงข้อมูลทั้งหมด
                    $allModels = $query->all();

                    $totalJobAmount = 0;
                    $totalWithdrawAmount = 0;
                    $totalJobExpense = 0;
                    $totalProfitLoss = 0;
                    $totalVehicleExpense = 0;

                    foreach ($allModels as $model) {
                        $totalJobAmount += $model->job_amount;
                        $totalWithdrawAmount += $model->getTotalWithdrawAmount();
                        $totalJobExpense += $model->getJobExpenseAll();
                        $totalVehicleExpense += $model->getVehicleExpenseAll();
                    }
                    $totalProfitLoss = $totalJobAmount - $totalWithdrawAmount - $totalJobExpense - $totalVehicleExpense;
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
                                            <h4 class="text-primary"><?= number_format(count($allModels)) ?> ใบ</h4>
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
                                            <h6 class="text-muted">รายจ่ายรวม</h6>
                                            <h4 class="text-warning"><?= number_format($totalWithdrawAmount + $totalJobExpense + $totalVehicleExpense , 2) ?> บาท</h4>
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

                    <?php
                    // สรุปข้อมูลแยกตามบริษัท
                    $companySummary = [];
                    foreach ($allModels as $model) {
                        $companyId = $model->company_id;
                        $companyName = $model->company ? $model->company->name : 'ทั้งหมด';

                        if (!isset($companySummary[$companyId])) {
                            $companySummary[$companyId] = [
                                'name' => $companyName,
                                'job_count' => 0,
                                'total_job_amount' => 0,
                                'total_withdraw_amount' => 0,
                                'total_profit_loss' => 0
                            ];
                        }

                        $companySummary[$companyId]['job_count']++;
                        $companySummary[$companyId]['total_job_amount'] += $model->job_amount;
                        $companySummary[$companyId]['total_withdraw_amount'] += ($model->getTotalWithdrawAmount() + $model->getJobExpenseAll() + $model->getVehicleExpenseAll());
                    }

                    // คำนวณกำไรขาดทุนและเปอร์เซ็นต์สำหรับแต่ละบริษัท
                    foreach ($companySummary as $companyId => &$summary) {
                        $summary['total_profit_loss'] = $summary['total_job_amount'] - $summary['total_withdraw_amount'];
                        $summary['percentage'] = $summary['total_job_amount'] > 0
                            ? ($summary['total_profit_loss'] / $summary['total_job_amount']) * 100
                            : 0;
                    }

                    // เรียงลำดับตามชื่อบริษัท
                    uasort($companySummary, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    ?>

                    <!-- สรุปข้อมูลแยกตามบริษัท -->
                    <div class="company-summary-section mt-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-building"></i> สรุปข้อมูลแยกตามบริษัท
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($companySummary)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="thead-light">
                                            <tr>
                                                <th class="text-center" width="5%">#</th>
                                                <th>บริษัท</th>
                                                <th class="text-center" width="12%">จำนวนใบงาน</th>
                                                <th class="text-right" width="15%">มูลค่างานรวม</th>
                                                <th class="text-right" width="15%">มูลค่ารายจ่าย</th>
                                                <th class="text-right" width="15%">กำไร/ขาดทุน</th>
                                                <th class="text-center" width="10%">เปอร์เซ็นต์</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            $rowNumber = 1;
                                            $grandTotalJobs = 0;
                                            $grandTotalJobAmount = 0;
                                            $grandTotalWithdraw = 0;
                                            $grandTotalProfitLoss = 0;

                                            foreach ($companySummary as $companyId => $summary):
                                                $grandTotalJobs += $summary['job_count'];
                                                $grandTotalJobAmount += $summary['total_job_amount'];
                                                $grandTotalWithdraw += $summary['total_withdraw_amount'];
                                                $grandTotalProfitLoss += $summary['total_profit_loss'];
                                                ?>
                                                <tr>
                                                    <td class="text-center"><?= $rowNumber++ ?></td>
                                                    <td>
                                                        <strong><?= Html::encode($summary['name']) ?></strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info"><?= number_format($summary['job_count']) ?> ใบ</span>
                                                    </td>
                                                    <td class="text-right">
                                                        <span class="text-primary"><?= number_format($summary['total_job_amount'], 2) ?></span>
                                                    </td>
                                                    <td class="text-right">
                                                        <span class="text-warning"><?= number_format($summary['total_withdraw_amount'], 2) ?></span>
                                                    </td>
                                                    <td class="text-right">
                                        <span class="<?= $summary['total_profit_loss'] >= 0 ? 'text-success' : 'text-danger' ?> font-weight-bold">
                                            <?= number_format($summary['total_profit_loss'], 2) ?>
                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                        <span class="badge badge-<?= $summary['percentage'] >= 0 ? 'success' : 'danger' ?>">
                                            <?= number_format($summary['percentage'], 2) ?>%
                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                            <tr class="table-secondary font-weight-bold">
                                                <td colspan="2" class="text-right">รวมทั้งหมด</td>
                                                <td class="text-center">
                                                    <span class="badge badge-dark"><?= number_format($grandTotalJobs) ?> ใบ</span>
                                                </td>
                                                <td class="text-right text-primary"><?= number_format($grandTotalJobAmount, 2) ?></td>
                                                <td class="text-right text-warning"><?= number_format($grandTotalWithdraw, 2) ?></td>
                                                <td class="text-right <?= $grandTotalProfitLoss >= 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= number_format($grandTotalProfitLoss, 2) ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $grandPercentage = $grandTotalJobAmount > 0
                                                        ? ($grandTotalProfitLoss / $grandTotalJobAmount) * 100
                                                        : 0;
                                                    ?>
                                                    <span class="badge badge-<?= $grandPercentage >= 0 ? 'success' : 'danger' ?>">
                                        <?= number_format($grandPercentage, 2) ?>%
                                    </span>
                                                </td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- กราฟแสดงสัดส่วน (Optional) -->
<!--                                    <div class="row mt-4">-->
<!--                                        <div class="col-md-6">-->
<!--                                            <div class="chart-container">-->
<!--                                                <h6 class="text-center text-muted">สัดส่วนมูลค่างานแต่ละบริษัท</h6>-->
<!--                                                <canvas id="companyJobValueChart"></canvas>-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                        <div class="col-md-6">-->
<!--                                            <div class="chart-container">-->
<!--                                                <h6 class="text-center text-muted">เปรียบเทียบกำไร/ขาดทุน</h6>-->
<!--                                                <canvas id="companyProfitChart"></canvas>-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div id="companyJobValueChart" style="height:400px;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div id="companyProfitChart" style="height:400px;"></div>
                                        </div>
                                    </div>


                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> ไม่พบข้อมูลสำหรับแสดงผล
                                    </div>
                                <?php endif; ?>
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
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    /* ปรับความกว้างของ form-group และ input */
    .search-form .form-group {
        width: 100%;
        margin-bottom: 0;
    }

    .search-form .form-control,
    .search-form .select2-container {
        width: 100% !important;
    }

    /* จัดการ DatePicker ให้แสดงผลถูกต้อง */
    .search-form .krajee-datepicker {
        width: 100% !important;
    }

    .search-form .input-group {
        width: 100%;
        display: flex;
        flex-wrap: nowrap; /* ป้องกันการขึ้นบรรทัดใหม่ */
    }

    .search-form .input-group .form-control {
        flex: 1;
        min-width: 0; /* ให้ input ยืดหยุ่นได้ */
    }

    .search-form .input-group-append,
    .search-form .input-group-btn {
        flex: 0 0 auto;
    }

    /* ปรับขนาด icon ใน DatePicker */
    .search-form .input-group-addon,
    .search-form .input-group-text {
        padding: 0.375rem 0.75rem;
        white-space: nowrap;
    }

    /* ปุ่มค้นหาและล้าง */
    .search-form .btn-group-actions {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        height: 100%;
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
        gap: 3px;
        padding: 5px;
    }

    .activity-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 12px;
        font-size: 0.75em;
        font-weight: 500;
        text-align: center;
        min-width: 35px;
        white-space: nowrap;
        transition: all 0.2s ease;
        cursor: default;
        line-height: 1.2;
    }

    .activity-completed {
        background-color: #fa952e;
        color: white;
        box-shadow: 0 1px 3px rgba(40, 167, 69, 0.3);
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

    /* สไตล์สำหรับ progress bar */
    .progress-container {
        width: 100%;
    }

    .progress {
        background-color: #e9ecef;
        border-radius: 0.25rem;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
    }

    .progress-bar {
        transition: width 0.6s ease;
        position: relative;
        animation: progress-bar-stripes 1s linear infinite;
    }

    @keyframes progress-bar-stripes {
        from {
            background-position: 1rem 0;
        }
        to {
            background-position: 0 0;
        }
    }

    .progress-bar-striped {
        background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
        background-size: 1rem 1rem;
    }

    .activity-mini-list {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 2px;
    }

    .activity-mini-list .badge {
        display: inline-block;
        min-width: 25px;
        text-align: center;
    }

    /* Tooltip styling */
    .tooltip-inner {
        text-align: left;
        max-width: 300px;
        white-space: pre-line;
    }


    @media (max-width: 1400px) {
        .activity-status-container {
            gap: 2px;
        }

        .activity-badge {
            font-size: 0.6em;
            padding: 1px 4px;
            min-width: 30px;
        }
    }

    @media (max-width: 1200px) {
        .activity-status-container {
            flex-direction: column;
            gap: 1px;
        }

        .activity-badge {
            font-size: 0.55em;
            padding: 1px 3px;
            min-width: 25px;
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

<!--<script>-->
<?php

$highchartsScripts = [
    'https://code.highcharts.com/highcharts.js',
    'https://code.highcharts.com/modules/exporting.js',
    'https://code.highcharts.com/modules/accessibility.js',
];

foreach ($highchartsScripts as $script) {
    $this->registerJsFile($script, [
        'depends' => [\yii\web\JqueryAsset::class],
        'position' => View::POS_HEAD,
    ]);
}


$companySummaryJson = json_encode($companySummary, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

$js = <<<JS
$(document).ready(function() {

    // เปิดใช้งาน tooltip
    $('[data-toggle="tooltip"]').tooltip({
        html: true,
        boundary: 'window'
    });
    $('[title]').tooltip();

    // Auto submit form เมื่อเปลี่ยนสถานะ
    $('#jobreportsearch-status').on('change', function() {
        $(this).closest('form').submit();
    });

    // ================================
    //  แปลงข้อมูลจาก PHP → JS
    // ================================
    var companySummary = $companySummaryJson;

    if (companySummary && companySummary.length > 0) {

        var companyNames = companySummary.map(item => item.name);
        var jobAmounts   = companySummary.map(item => item.total_job_amount);
        var profitLoss   = companySummary.map(item => item.total_profit_loss);
        
        console.log(jobAmounts[0]);
        
        // ================================
        //  กราฟ 1: สัดส่วนมูลค่างาน (Pie)
        // ================================
        Highcharts.chart('companyJobValueChart', {
            chart: {
                type: 'pie',
                backgroundColor: 'transparent'
            },
            title: {
                text: 'สัดส่วนมูลค่างานตามบริษัท'
            },
            tooltip: {
                pointFormat: '<b>{point.y:,.2f} บาท</b> ({point.percentage:.2f}%)'
            },
            accessibility: {
                point: { valueSuffix: '%' }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '{point.name}: {point.percentage:.1f} %'
                    }
                }
            },
            series: [{
                name: 'มูลค่างาน',
                colorByPoint: true,
                data: companyNames.map((name, i) => ({
                    name: name,
                    y: jobAmounts[i]
                }))
            }]
        });

        // ================================
        //  กราฟ 2: กำไร/ขาดทุน (Column)
        // ================================
        Highcharts.chart('companyProfitChart', {
            chart: {
                type: 'column',
                backgroundColor: 'transparent'
            },
            title: {
                text: 'เปรียบเทียบกำไร/ขาดทุน'
            },
            xAxis: {
                categories: companyNames,
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: { text: 'จำนวนเงิน (บาท)' },
                labels: {
                    formatter: function() {
                        return '฿' + Highcharts.numberFormat(this.value, 0);
                    }
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat:
                    '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>฿{point.y:,.2f}</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    borderWidth: 0,
                    colors: profitLoss.map(v => v >= 0 ? '#28a745' : '#dc3545'),
                    colorByPoint: true
                }
            },
            series: [{
                name: 'กำไร/ขาดทุน',
                data: profitLoss
            }]
        });

    } else {
        console.log('ไม่มีข้อมูล companySummary');
    }

});
JS;

//echo '<pre>';
//print_r($companySummary);
//echo '</pre>';

$this->registerJs($js, static::POS_END);
?>
<!--</script>-->