<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $job_id int */
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
                    <div class="col-md-4">
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
                    <div class="col-md-3">
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
                        <div>
                            <?= Html::submitButton('ค้นหา', ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('ล้าง', ['borrow-return-report'], ['class' => 'btn btn-outline-secondary']) ?>
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
                    <?php if ($from_date || $to_date): ?>
                        <p>วันที่: <?= $from_date ?> ถึง <?= $to_date ?></p>
                    <?php endif; ?>
                </div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr style="background-color: #f4f6f9;">
                            <th class="text-center">Job No</th>
                            <th class="text-center">รหัสเครื่องมือ</th>
                            <th class="text-center">รายการเครื่องมือ</th>
                            <th class="text-center">เบิกทั้งหมด (ครั้ง)</th>
                            <th class="text-center">คืนแล้ว (ครั้ง)</th>
                            <th class="text-center">คงค้าง (ครั้ง)</th>
                            <th class="text-center">รายการเสียหาย</th>
                            <th class="text-center">หมายเหตุ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($dataProvider->getCount() > 0): ?>
                            <?php foreach ($dataProvider->getModels() as $model): ?>
                                <?php 
                                    $product = \backend\models\Product::findOne($model->product_id);
                                    $job = \backend\models\Job::findOne($model->job_id);
                                    $pending = $model->total_issued - $model->total_returned;
                                ?>
                                <tr>
                                    <td><?= $job ? $job->job_no : '-' ?></td>
                                    <td><?= $product ? $product->code : '-' ?></td>
                                    <td><?= $product ? $product->name : '-' ?></td>
                                    <td class="text-center"><?= number_format($model->total_issued, 0) ?></td>
                                    <td class="text-center"><?= number_format($model->total_returned, 0) ?></td>
                                    <td class="text-center" style="<?= $pending > 0 ? 'color: red; font-weight: bold;' : '' ?>">
                                        <?= number_format($pending, 0) ?>
                                    </td>
                                    <td class="text-center"><?= number_format($model->total_damaged, 0) ?></td>
                                    <td><?= Html::encode($model->remarks) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">ไม่พบข้อมูล</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
