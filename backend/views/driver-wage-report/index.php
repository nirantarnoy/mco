<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $reportModels backend\models\DriverWageReport[] */
/* @var $driversList array */
/* @var $month int */
/* @var $year int */

$this->title = 'รายงานค่าเที่ยว/ค่าแรงพนักงานขับรถ';
$this->params['breadcrumbs'][] = $this->title;

$thaiMonths = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

$this->registerJsFile(Yii::$app->request->baseUrl . '/js/jquery.table2excel.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

<div class="driver-wage-report-index">

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave"></i>
                        <?= Html::encode($this->title) ?> - ประจำเดือน <?= $thaiMonths[intval($month)] ?> พ.ศ. <?= intval($year) + 543 ?>
                    </h3>
                </div>
                <div class="card-body">

                    <!-- Filter Form -->
                    <div class="search-form bg-light p-3 rounded mb-4">
                        <?php $form = ActiveForm::begin([
                            'action' => ['index'],
                            'method' => 'get',
                            'options' => ['class' => 'form-inline'],
                        ]); ?>
                        
                        <div class="row w-100 align-items-center">
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="mr-2 font-weight-bold" for="filter-month">เลือกเดือน:</label>
                                <?= Html::dropDownList('month', $month, $thaiMonths, [
                                    'id' => 'filter-month',
                                    'class' => 'form-control w-100',
                                ]) ?>
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="mr-2 font-weight-bold" for="filter-year">เลือกปี:</label>
                                <?php
                                $years = [];
                                $currentYear = intval(date('Y'));
                                for ($y = $currentYear - 3; $y <= $currentYear + 3; $y++) {
                                    $years[$y] = $y + 543; // Display in Buddhist Era
                                }
                                echo Html::dropDownList('year', $year, $years, [
                                    'id' => 'filter-year',
                                    'class' => 'form-control w-100',
                                ]);
                                ?>
                            </div>
                            <div class="col-md-6 text-md-right mt-3 mt-md-0">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search"></i> ค้นหา
                                </button>
                                <button type="button" id="btn-export-excel" class="btn btn-success mr-2">
                                    <i class="fas fa-file-excel"></i> ส่งออก Excel
                                </button>
                                <button type="button" id="btn-print" class="btn btn-info">
                                    <i class="fas fa-print"></i> พิมพ์รายงาน
                                </button>
                            </div>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>

                    <!-- Main Form to Save Data -->
                    <?php $saveForm = ActiveForm::begin([
                        'action' => ['save'],
                        'method' => 'post',
                        'options' => ['id' => 'form-driver-wage'],
                    ]); ?>

                    <?= Html::hiddenInput('month', $month) ?>
                    <?= Html::hiddenInput('year', $year) ?>

                    <div class="table-responsive">
                        <table id="table-driver-wage" class="table table-bordered table-striped table-hover text-center font-size-13" style="width: 100%; min-width: 1500px;">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 40px; vertical-align: middle;">ลำดับ</th>
                                    <th style="width: 100px; vertical-align: middle;">ทะเบียนรถ</th>
                                    <th style="width: 200px; vertical-align: middle;">ชื่อพนักงานขับรถ</th>
                                    <th style="width: 100px; vertical-align: middle;">ค่าครองชีพ</th>
                                    <th style="width: 100px; vertical-align: middle;">ค่าเที่ยว</th>
                                    <th style="width: 100px; vertical-align: middle;">ยอดรวม</th>
                                    <th style="width: 100px; vertical-align: middle;">หักประกันสังคม</th>
                                    <th style="width: 100px; vertical-align: middle;">โอที</th>
                                    <th style="width: 100px; vertical-align: middle;">เงินเบี้ยเลี้ยง</th>
                                    <th style="width: 100px; vertical-align: middle;">หักภาษี ภงด.</th>
                                    <th style="width: 100px; vertical-align: middle;">หักเงินยืมทดรอง</th>
                                    <th style="width: 100px; vertical-align: middle;">หักค่าปรับจราจร</th>
                                    <th style="width: 100px; vertical-align: middle;">ประกันของเสีย</th>
                                    <th style="width: 100px; vertical-align: middle;">สินค้าเสียหาย</th>
                                    <th style="width: 100px; vertical-align: middle;">หักอื่นๆ</th>
                                    <th style="width: 120px; vertical-align: middle;">คงเหลือสุทธิ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reportModels)): ?>
                                    <tr>
                                        <td colspan="16" class="text-center text-muted py-4">ไม่พบข้อมูลการใช้งานรถในเดือนนี้</td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $index = 1;
                                    foreach ($reportModels as $vNo => $model): 
                                    ?>
                                        <tr class="row-wage-report" data-vehicle="<?= Html::encode($vNo) ?>">
                                            <td class="align-middle"><?= $index++ ?></td>
                                            <td class="align-middle font-weight-bold"><?= Html::encode($vNo) ?></td>
                                            <td class="align-middle">
                                                <?= Html::dropDownList(
                                                    "report[{$vNo}][driver_name]", 
                                                    $model->driver_name, 
                                                    ['' => '-- เลือกพนักงาน --'] + $driversList, 
                                                    ['class' => 'form-control input-driver-name select2-dropdown', 'style' => 'width: 100%;']
                                                ) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= Html::textInput("report[{$vNo}][cost_of_living]", number_format($model->cost_of_living, 2, '.', ''), [
                                                    'class' => 'form-control text-right numeric-input col-cost-of-living',
                                                    'style' => 'width: 90px; display: inline-block;'
                                                ]) ?>
                                            </td>
                                            <td class="align-middle bg-light text-right pr-3 font-weight-bold col-trip-allowance-val" data-val="<?= $model->trip_allowance ?>">
                                                <?= number_format($model->trip_allowance, 2) ?>
                                                <?= Html::hiddenInput("report[{$vNo}][trip_allowance]", $model->trip_allowance, ['class' => 'col-trip-allowance']) ?>
                                            </td>
                                            <td class="align-middle font-weight-bold text-right pr-3 col-total-income font-size-14">
                                                <?= number_format($model->cost_of_living + $model->trip_allowance, 2) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= Html::textInput("report[{$vNo}][social_security]", number_format($model->social_security, 2, '.', ''), [
                                                    'class' => 'form-control text-right numeric-input col-social-security',
                                                    'style' => 'width: 90px; display: inline-block;'
                                                ]) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= Html::textInput("report[{$vNo}][ot]", number_format($model->ot, 2, '.', ''), [
                                                    'class' => 'form-control text-right numeric-input col-ot',
                                                    'style' => 'width: 90px; display: inline-block;'
                                                ]) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= Html::textInput("report[{$vNo}][food_allowance]", number_format($model->food_allowance, 2, '.', ''), [
                                                    'class' => 'form-control text-right numeric-input col-food-allowance',
                                                    'style' => 'width: 90px; display: inline-block;'
                                                ]) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= Html::textInput("report[{$vNo}][tax_withholding]", number_format($model->tax_withholding, 2, '.', ''), [
                                                    'class' => 'form-control text-right numeric-input col-tax-withholding',
                                                    'style' => 'width: 90px; display: inline-block;'
                                                ]) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= Html::textInput("report[{$vNo}][cash_advance]", number_format($model->cash_advance, 2, '.', ''), [
                                                    'class' => 'form-control text-right numeric-input col-cash-advance',
                                                    'style' => 'width: 90px; display: inline-block;'
                                                ]) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= Html::textInput("report[{$vNo}][traffic_fine]", number_format($model->traffic_fine, 2, '.', ''), [
                                                    'class' => 'form-control text-right numeric-input col-traffic-fine',
                                                    'style' => 'width: 90px; display: inline-block;'
                                                ]) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= Html::textInput("report[{$vNo}][damage_insurance]", number_format($model->damage_insurance, 2, '.', ''), [
                                                    'class' => 'form-control text-right numeric-input col-damage-insurance',
                                                    'style' => 'width: 90px; display: inline-block;'
                                                ]) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= Html::textInput("report[{$vNo}][product_damage]", number_format($model->product_damage, 2, '.', ''), [
                                                    'class' => 'form-control text-right numeric-input col-product-damage',
                                                    'style' => 'width: 90px; display: inline-block;'
                                                ]) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= Html::textInput("report[{$vNo}][other_deduction]", number_format($model->other_deduction, 2, '.', ''), [
                                                    'class' => 'form-control text-right numeric-input col-other-deduction',
                                                    'style' => 'width: 90px; display: inline-block;'
                                                ]) ?>
                                            </td>
                                            <td class="align-middle font-weight-bold text-right pr-3 col-net-total font-size-15 text-success">
                                                <?= number_format($model->net_total, 2) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="bg-secondary text-white font-weight-bold">
                                <tr>
                                    <td colspan="3" class="text-right align-middle pr-3">รวมทั้งหมด</td>
                                    <td class="text-right pr-3 align-middle" id="sum-cost-of-living">0.00</td>
                                    <td class="text-right pr-3 align-middle" id="sum-trip-allowance">0.00</td>
                                    <td class="text-right pr-3 align-middle" id="sum-total-income">0.00</td>
                                    <td class="text-right pr-3 align-middle" id="sum-social-security">0.00</td>
                                    <td class="text-right pr-3 align-middle" id="sum-ot">0.00</td>
                                    <td class="text-right pr-3 align-middle" id="sum-food-allowance">0.00</td>
                                    <td class="text-right pr-3 align-middle" id="sum-tax-withholding">0.00</td>
                                    <td class="text-right pr-3 align-middle" id="sum-cash-advance">0.00</td>
                                    <td class="text-right pr-3 align-middle" id="sum-traffic-fine">0.00</td>
                                    <td class="text-right pr-3 align-middle" id="sum-damage-insurance">0.00</td>
                                    <td class="text-right pr-3 align-middle" id="sum-product-damage">0.00</td>
                                    <td class="text-right pr-3 align-middle" id="sum-other-deduction">0.00</td>
                                    <td class="text-right pr-3 align-middle text-warning font-size-16" id="sum-net-total">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if (!empty($reportModels)): ?>
                        <div class="row mt-4">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-lg btn-success shadow px-5">
                                    <i class="fas fa-save mr-2"></i> บันทึกข้อมูล
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>
    </div>

</div>

<!-- Styles for screen display and printing -->
<style>
    .font-size-13 {
        font-size: 13px;
    }
    .font-size-14 {
        font-size: 14px;
    }
    .font-size-15 {
        font-size: 15px;
    }
    .font-size-16 {
        font-size: 16px;
    }
    .numeric-input {
        border-radius: 4px;
        border: 1px solid #ced4da;
        padding: 4px 8px;
    }
    .numeric-input:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    @media print {
        body * {
            visibility: hidden;
        }
        .driver-wage-report-index, .driver-wage-report-index * {
            visibility: visible;
        }
        .driver-wage-report-index {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .search-form, button[type="submit"], #btn-export-excel, #btn-print, .input-driver-name {
            display: none !important;
        }
        table {
            border-collapse: collapse !important;
            width: 100% !important;
            font-size: 11px !important;
        }
        th, td {
            border: 1px solid #000 !important;
            padding: 4px !important;
        }
        input.numeric-input {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            text-align: right !important;
            padding: 0 !important;
            width: 100% !important;
        }
        /* Show select value instead of dropdown on print */
        .select2-dropdown {
            border: none !important;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background: transparent !important;
        }
    }
</style>

<!-- Recalculation Scripts -->
<?php
$script = <<<JS
$(document).ready(function() {
    // Dynamic calculation function
    function recalculate() {
        var grandCostOfLiving = 0;
        var grandTripAllowance = 0;
        var grandTotalIncome = 0;
        var grandSocialSecurity = 0;
        var grandOT = 0;
        var grandFoodAllowance = 0;
        var grandTaxWithholding = 0;
        var grandCashAdvance = 0;
        var grandTrafficFine = 0;
        var grandDamageInsurance = 0;
        var grandProductDamage = 0;
        var grandOtherDeduction = 0;
        var grandNetTotal = 0;

        $('.row-wage-report').each(function() {
            var row = $(this);
            
            // Get inputs
            var costOfLiving = parseFloat(row.find('.col-cost-of-living').val()) || 0;
            var tripAllowance = parseFloat(row.find('.col-trip-allowance').val()) || 0;
            var socialSecurity = parseFloat(row.find('.col-social-security').val()) || 0;
            var ot = parseFloat(row.find('.col-ot').val()) || 0;
            var foodAllowance = parseFloat(row.find('.col-food-allowance').val()) || 0;
            var taxWithholding = parseFloat(row.find('.col-tax-withholding').val()) || 0;
            var cashAdvance = parseFloat(row.find('.col-cash-advance').val()) || 0;
            var trafficFine = parseFloat(row.find('.col-traffic-fine').val()) || 0;
            var damageInsurance = parseFloat(row.find('.col-damage-insurance').val()) || 0;
            var productDamage = parseFloat(row.find('.col-product-damage').val()) || 0;
            var otherDeduction = parseFloat(row.find('.col-other-deduction').val()) || 0;

            // Calculations
            var totalIncome = costOfLiving + tripAllowance;
            var deductions = socialSecurity + taxWithholding + cashAdvance + trafficFine + damageInsurance + productDamage + otherDeduction;
            var netTotal = totalIncome + ot + foodAllowance - deductions;

            // Update row fields
            row.find('.col-total-income').text(totalIncome.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            row.find('.col-net-total').text(netTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

            // Update grand totals
            grandCostOfLiving += costOfLiving;
            grandTripAllowance += tripAllowance;
            grandTotalIncome += totalIncome;
            grandSocialSecurity += socialSecurity;
            grandOT += ot;
            grandFoodAllowance += foodAllowance;
            grandTaxWithholding += taxWithholding;
            grandCashAdvance += cashAdvance;
            grandTrafficFine += trafficFine;
            grandDamageInsurance += damageInsurance;
            grandProductDamage += productDamage;
            grandOtherDeduction += otherDeduction;
            grandNetTotal += netTotal;
        });

        // Update footer cells
        $('#sum-cost-of-living').text(grandCostOfLiving.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-trip-allowance').text(grandTripAllowance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-total-income').text(grandTotalIncome.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-social-security').text(grandSocialSecurity.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-ot').text(grandOT.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-food-allowance').text(grandFoodAllowance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-tax-withholding').text(grandTaxWithholding.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-cash-advance').text(grandCashAdvance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-traffic-fine').text(grandTrafficFine.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-damage-insurance').text(grandDamageInsurance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-product-damage').text(grandProductDamage.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-other-deduction').text(grandOtherDeduction.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#sum-net-total').text(grandNetTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    // Bind event listeners to input changes
    $('.numeric-input').on('input change', function() {
        recalculate();
    });

    // Run once at start
    recalculate();

    // Export to Excel handler
    $('#btn-export-excel').click(function() {
        // Clone table to avoid changing the page UI
        var cloneTable = $('#table-driver-wage').clone();
        
        // Replace inputs and select elements in clone with text values
        cloneTable.find('tbody tr').each(function() {
            var row = $(this);
            
            // Driver name select replacement
            var driverSelect = row.find('.input-driver-name');
            var selectedDriver = driverSelect.val() || '';
            driverSelect.replaceWith('<span>' + selectedDriver + '</span>');
            
            // Replace text inputs with spans of their current value
            row.find('.numeric-input').each(function() {
                var input = $(this);
                var val = parseFloat(input.val()) || 0;
                input.replaceWith('<span>' + val.toFixed(2) + '</span>');
            });
        });

        // Hide it in DOM, perform export, then remove
        cloneTable.attr('id', 'temp-export-table').css('display', 'none').appendTo('body');
        
        $('#temp-export-table').table2excel({
            exclude: ".noExl",
            name: "รายงานค่าเที่ยวพนักงานขับรถ",
            filename: "รายงานค่าเที่ยวพนักงานขับรถ_" + $('#filter-month').val() + "_" + $('#filter-year').val() + ".xls",
            preserveColors: true
        });

        $('#temp-export-table').remove();
    });

    // Print handler
    $('#btn-print').click(function() {
        window.print();
    });
});
JS;
$this->registerJs($script, \yii\web\View::POS_END);
?>
