<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $models array */
/* @var $month string */
/* @var $year string */

$this->title = 'รายงานภาษีซื้อ';
$this->params['breadcrumbs'][] = $this->title;

$months = [
    '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
    '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
    '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
];
$years = [];
$currentYear = date('Y');
for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
    $years[$i] = $i + 543;
}
?>
<div class="purchase-tax-report-index">

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> ค้นหา</h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'action' => ['index'],
                        'method' => 'get',
                        'options' => ['class' => 'form-inline']
                    ]); ?>

                    <div class="form-group mx-sm-3 mb-2">
                        <label for="month" class="sr-only">เดือน</label>
                        <?= Html::dropDownList('month', $month, $months, ['class' => 'form-control', 'prompt' => '-- เลือกเดือน --']) ?>
                    </div>
                    
                    <div class="form-group mx-sm-3 mb-2">
                        <label for="year" class="sr-only">ปี</label>
                        <?= Html::dropDownList('year', $year, $years, ['class' => 'form-control']) ?>
                    </div>

                    <?= Html::submitButton('<i class="fas fa-search"></i> ค้นหา', ['class' => 'btn btn-primary mb-2 mr-2']) ?>
                    
                    <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'month' => $month, 'year' => $year], ['class' => 'btn btn-info mb-2 mr-2', 'target' => '_blank']) ?>
                    
                    <?= Html::a('<i class="fas fa-file-excel"></i> Export Excel', ['export-excel', 'month' => $month, 'year' => $year], ['class' => 'btn btn-success mb-2 mr-2']) ?>
                    
                    <?= Html::a('<i class="fas fa-file-pdf"></i> Export PDF', ['export-pdf', 'month' => $month, 'year' => $year], ['class' => 'btn btn-danger mb-2', 'target' => '_blank']) ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive">
                    <?= $this->render('_report', [
                        'models' => $models,
                        'month' => $month,
                        'year' => $year,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

</div>
