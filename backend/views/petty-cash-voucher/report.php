<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PettyCashReportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'รายงานเงินสดย่อย (Petty Cash Report)';
$this->params['breadcrumbs'][] = ['label' => 'ใบสำคัญจ่ายเงินสดย่อย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Get total summary
$totalSummary = $searchModel->getTotalSummary();
?>

    <div class="petty-cash-report">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <?php
                // Build print URL with current parameters
                $printParams = Yii::$app->request->queryParams;
                $printUrl = \yii\helpers\Url::to(array_merge(['print-report'], $printParams));
                ?>
                <?= Html::a('<i class="fas fa-print"></i> พิมพ์รายงาน', $printUrl, [
                    'class' => 'btn btn-info',
                    'target' => '_blank'
                ]) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> กลับไปรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-filter"></i> เงื่อนไขการค้นหา</h5>
            </div>
            <div class="card-body">
                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                    'options' => ['class' => 'form-inline'],
                    'fieldConfig' => [
                        'template' => '<div class="form-group mr-3 mb-3">{label}<div class="ml-2">{input}</div></div>',
                        'labelOptions' => ['class' => 'mb-0'],
                    ],
                ]); ?>

                <div class="row w-100">
                    <div class="col-md-3">
                        <?= $form->field($searchModel, 'date_from')->widget(DatePicker::class, [
                            'options' => ['placeholder' => 'วันที่เริ่มต้น', 'class' => 'form-control'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ]
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($searchModel, 'date_to')->widget(DatePicker::class, [
                            'options' => ['placeholder' => 'วันที่สิ้นสุด', 'class' => 'form-control'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ]
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($searchModel, 'ac_code')->textInput([
                            'placeholder' => 'รหัสบัญชี',
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($searchModel, 'vat_type')->dropDownList([
                            'all' => 'ทั้งหมด',
                            'vat' => 'มี VAT',
                            'no_vat' => 'ไม่มี VAT',
                        ], ['class' => 'form-control']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($searchModel, 'report_type')->dropDownList([
                            'detail' => 'รายละเอียด',
                            'summary' => 'สรุป',
                        ], ['class' => 'form-control']) ?>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <?= Html::submitButton('<i class="fas fa-search"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="fas fa-refresh"></i> ล้างค่า', ['report'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-table"></i>
                    <?= $searchModel->report_type === 'summary' ? 'รายงานสรุป' : 'รายงานรายละเอียด' ?>
                </h5>
                <small class="text-muted">
                    ระหว่างวันที่ <?= Yii::$app->formatter->asDate($searchModel->date_from, 'dd/MM/yyyy') ?>
        <!-- Summary Cards -->
        <div class="row mt-3">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">รายจ่ายรวม</h5>
                        <h3><?= number_format($totalSummary['total_amount'], 2) ?></h3>
                        <small>บาท</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">VAT รวม</h5>
                        <h3><?= number_format($totalSummary['total_vat_amount'], 2) ?></h3>
                        <small>บาท</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">หัก ณ ที่จ่าย</h5>
                        <h3><?= number_format($totalSummary['total_wht'], 2) ?></h3>
                        <small>บาท</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">ยอดรวมสุทธิ</h5>
                        <h3><?= number_format($totalSummary['grand_total'], 2) ?></h3>
                        <small>บาท</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
// Remove the problematic JavaScript since we're handling it in PHP now
?>