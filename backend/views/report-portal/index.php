<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use backend\models\Job;
use backend\models\Company;

/* @var $this yii\web\View */

//$this->title = 'Report Portal - ศูนย์รวมรายงานและวิเคราะห์ผลประกอบการ';
$this->params['breadcrumbs'][] = 'Report Portal - ศูนย์รวมรายงานและวิเคราะห์ผลประกอบการ' ;

$monthNames = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตารางพฤศจิกายน', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];
?>

<div class="report-portal-index">
    <!-- Page Title & Portal Header -->
    <div class="portal-header card border-0 shadow-sm bg-white mb-4 rounded-lg">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <div class="avatar-icon-wrapper mr-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; font-size: 24px;">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div>
                        <h3 class="font-weight-bold mb-1 text-dark"><?= Html::encode($this->title) ?>Report Portal - ศูนย์รวมรายงานและวิเคราะห์ผลประกอบการ</h3>
                        <p class="text-muted mb-0"><i class="fas fa-info-circle text-info"></i> สรุปผลประกอบการ รายรับ, รายจ่าย, เงินเดือน และค่าใช้จ่ายรถ (คำนวณยอดไม่รวม VAT)</p>
                    </div>
                </div>
                <div class="portal-actions btn-group">
                    <?= Html::a('<i class="fas fa-file-excel mr-1"></i> Excel รายบริษัท', ['export-company-excel', 'company_id' => $companyId, 'start_date_from' => $startDateFrom, 'start_date_to' => $startDateTo, 'month' => $month, 'year' => $year], ['class' => 'btn btn-success btn-sm font-weight-bold shadow-sm']) ?>
                    <?= Html::a('<i class="fas fa-file-excel mr-1"></i> Excel รายใบงาน', ['export-job-excel', 'company_id' => $companyId, 'start_date_from' => $startDateFrom, 'start_date_to' => $startDateTo, 'job_no' => $jobNo], ['class' => 'btn btn-outline-success btn-sm font-weight-bold shadow-sm ml-2']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card shadow-sm border-0 mb-4 rounded-lg">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="card-title mb-0 font-weight-bold text-secondary">
                <i class="fas fa-filter text-primary mr-1"></i> ตัวกรองข้อมูล (Filter Criteria)
            </h5>
        </div>
        <div class="card-body">
            <form method="get" action="<?= Url::to(['report-portal/index']) ?>" id="filter-form">
                <input type="hidden" name="r" value="report-portal/index">
                <div class="row">
                    <!-- บริษัท -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label font-weight-bold">บริษัท</label>
                        <?= Select2::widget([
                            'name' => 'company_id',
                            'value' => $companyId,
                            'data' => ['0' => 'ทุกบริษัท (All Companies)'] + \yii\helpers\ArrayHelper::map(Company::find()->all(), 'id', 'name'),
                            'options' => ['placeholder' => 'เลือกบริษัท...', 'class' => 'form-control'],
                            'pluginOptions' => ['allowClear' => true, 'width' => '100%']
                        ]) ?>
                    </div>

                    <!-- วันที่เริ่ม -->
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="form-label font-weight-bold">ตั้งแต่วันที่</label>
                        <?= DatePicker::widget([
                            'name' => 'start_date_from',
                            'value' => $startDateFrom,
                            'options' => ['placeholder' => 'วันที่เริ่ม', 'class' => 'form-control'],
                            'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayHighlight' => true],
                            'removeButton' => false
                        ]) ?>
                    </div>

                    <!-- ถึงวันที่ -->
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label class="form-label font-weight-bold">ถึงวันที่</label>
                        <?= DatePicker::widget([
                            'name' => 'start_date_to',
                            'value' => $startDateTo,
                            'options' => ['placeholder' => 'ถึงวันที่', 'class' => 'form-control'],
                            'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayHighlight' => true],
                            'removeButton' => false
                        ]) ?>
                    </div>

                    <!-- รอบเงินเดือน (เดือน/ปี) -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="form-label font-weight-bold">รอบเงินเดือน (เดือน/ปี)</label>
                        <div class="input-group">
                            <?= Html::dropDownList('month', $month, $monthNames, ['class' => 'form-control']) ?>
                            <?= Html::dropDownList('year', $year, array_combine(range(date('Y') - 2, date('Y') + 1), range(date('Y') - 2, date('Y') + 1)), ['class' => 'form-control']) ?>
                        </div>
                    </div>

                    <!-- เลขใบงาน -->
                    <div class="col-lg-2 col-md-12 mb-3">
                        <label class="form-label font-weight-bold">เลขใบงาน</label>
                        <?= Html::textInput('job_no', $jobNo, ['class' => 'form-control', 'placeholder' => 'เลขใบงาน...']) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 text-right">
                        <?= Html::submitButton('<i class="fas fa-search"></i> ค้นหา', ['class' => 'btn btn-primary font-weight-bold px-4']) ?>
                        <?= Html::a('<i class="fas fa-redo"></i> ล้างตัวกรอง', ['index'], ['class' => 'btn btn-secondary font-weight-bold px-4 ml-2']) ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Executive KPI Summary Cards -->
    <div class="row mb-4">
        <!-- Revenue Card -->
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card border-left-primary shadow-sm h-100 py-2 border-0 rounded-lg bg-white">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">รายรับรวม (Excl. VAT)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($grandCompanyRevenue, 2) ?></div>
                    <small class="text-muted">บาท</small>
                </div>
            </div>
        </div>

        <!-- Withdraw Card -->
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card border-left-warning shadow-sm h-100 py-2 border-0 rounded-lg bg-white">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">เบิกของ/คชจ.งาน</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($grandCompanyWithdraw, 2) ?></div>
                    <small class="text-muted">บาท (ไม่รวม VAT)</small>
                </div>
            </div>
        </div>

        <!-- Salary Card -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="card border-left-purple shadow-sm h-100 py-2 border-0 rounded-lg bg-white" style="border-left: 4px solid #6f42c1 !important;">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-purple text-uppercase mb-1" style="color: #6f42c1;">เงินเดือนรวมบริษัท</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($grandCompanySalary, 2) ?></div>
                    <small class="text-muted">บาท (ประจำเดือน <?= $month ?>/<?= $year ?>)</small>
                </div>
            </div>
        </div>

        <!-- Vehicle Expense Card -->
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card border-left-info shadow-sm h-100 py-2 border-0 rounded-lg bg-white">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">ค่าเดินทาง & ค่าแรงรถ</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($grandCompanyVehicleEx, 2) ?></div>
                    <small class="text-muted">บาท</small>
                </div>
            </div>
        </div>

        <!-- Net Profit Card -->
        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="card border-left-<?= $grandCompanyNetProfit >= 0 ? 'success' : 'danger' ?> shadow-sm h-100 py-2 border-0 rounded-lg bg-white">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-<?= $grandCompanyNetProfit >= 0 ? 'success' : 'danger' ?> text-uppercase mb-1">กำไร/ขาดทุนสุทธิรวม</div>
                    <div class="h5 mb-0 font-weight-bold text-<?= $grandCompanyNetProfit >= 0 ? 'success' : 'danger' ?>"><?= number_format($grandCompanyNetProfit, 2) ?></div>
                    <small class="font-weight-bold">
                        (<?= $grandCompanyRevenue > 0 ? number_format(($grandCompanyNetProfit / $grandCompanyRevenue) * 100, 2) : '0.00' ?>%)
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card shadow-sm border-0 rounded-lg">
        <div class="card-header bg-white border-0 pt-3 pb-0">
            <ul class="nav nav-pills card-header-pills font-weight-bold" id="reportTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active px-4 py-2 mr-2" id="company-tab" data-toggle="tab" href="#company-report" role="tab" aria-controls="company-report" aria-selected="true">
                        <i class="fas fa-building mr-1"></i> 1. สรุปผลประกอบการรายบริษัท
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-4 py-2" id="job-tab" data-toggle="tab" href="#job-report" role="tab" aria-controls="job-report" aria-selected="false">
                        <i class="fas fa-tasks mr-1"></i> 2. สรุปผลประกอบการรายใบงาน (Job)
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="reportTabsContent">
                
                <!-- TAB 1: Company Summary Report -->
                <div class="tab-pane fade show active" id="company-report" role="tabpanel" aria-labelledby="company-tab">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="font-weight-bold text-dark mb-0">
                            <i class="fas fa-calculator text-primary mr-1"></i> รายงานสรุปผลประกอบการแต่ละบริษัท (ยอดไม่รวม VAT)
                        </h5>
                        <small class="text-muted">* สูตร: กำไรสุทธิ = รายรับสุทธิ - รายจ่ายเบิกของ - เงินเดือน - ค่าใช้จ่ายรถ & ค่าแรง</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover shadow-sm rounded">
                            <thead class="bg-primary text-white text-center">
                                <tr>
                                    <th width="4%">#</th>
                                    <th width="18%">บริษัท</th>
                                    <th width="8%">ใบงาน</th>
                                    <th width="12%">รายรับ (Excl. VAT)</th>
                                    <th width="13%">เบิกของ/คชจ.งาน (Excl. VAT)</th>
                                    <th width="13%">เงินเดือนพนักงาน</th>
                                    <th width="13%">ค่าเดินทาง & ค่าแรงรถ</th>
                                    <th width="13%">กำไร/ขาดทุนสุทธิ</th>
                                    <th width="6%">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($companySummaryList)): ?>
                                    <?php $no = 1; foreach ($companySummaryList as $cData): ?>
                                        <tr>
                                            <td class="text-center align-middle"><?= $no++ ?></td>
                                            <td class="font-weight-bold align-middle">
                                                <i class="fas fa-building text-secondary mr-1"></i>
                                                <?= Html::encode($cData['company_name']) ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-info px-2 py-1"><?= number_format($cData['job_count']) ?> ใบ</span>
                                            </td>
                                            <td class="text-right font-weight-bold text-primary align-middle">
                                                <?= number_format($cData['revenue_no_vat'], 2) ?>
                                            </td>
                                            <td class="text-right text-warning font-weight-bold align-middle">
                                                <?= number_format($cData['withdraw_amount'], 2) ?>
                                            </td>
                                            <td class="text-right align-middle" style="background-color: #f8f0fc;">
                                                <span class="font-weight-bold text-purple" style="color: #6f42c1;">
                                                    <?= number_format($cData['salary_amount'], 2) ?>
                                                </span>
                                                <button type="button" class="btn btn-sm btn-outline-purple ml-1 py-0 px-1 btn-edit-salary" 
                                                        data-company-id="<?= $cData['company_id'] ?>" 
                                                        data-company-name="<?= Html::encode($cData['company_name']) ?>"
                                                        data-salary="<?= $cData['salary_amount'] ?>"
                                                        title="แก้ไขเงินเดือน">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                            <td class="text-right font-weight-bold text-info align-middle">
                                                <?= number_format($cData['vehicle_total'], 2) ?>
                                                <br>
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    (คชจ.: <?= number_format($cData['vehicle_cost'], 0) ?> | ค่าแรง: <?= number_format($cData['vehicle_wage'], 0) ?>)
                                                </small>
                                            </td>
                                            <td class="text-right font-weight-bold align-middle <?= $cData['net_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($cData['net_profit'], 2) ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-<?= $cData['profit_percentage'] >= 0 ? 'success' : 'danger' ?> px-2 py-1">
                                                    <?= number_format($cData['profit_percentage'], 1) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">ไม่พบข้อมูลบริษัท</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr class="text-right">
                                    <td colspan="3" class="text-center font-weight-bold">รวมทั้งหมด</td>
                                    <td class="text-primary font-weight-bold"><?= number_format($grandCompanyRevenue, 2) ?></td>
                                    <td class="text-warning font-weight-bold"><?= number_format($grandCompanyWithdraw, 2) ?></td>
                                    <td style="color: #6f42c1;" class="font-weight-bold"><?= number_format($grandCompanySalary, 2) ?></td>
                                    <td class="text-info font-weight-bold"><?= number_format($grandCompanyVehicleEx, 2) ?></td>
                                    <td class="<?= $grandCompanyNetProfit >= 0 ? 'text-success' : 'text-danger' ?> font-weight-bold h6 mb-0">
                                        <?= number_format($grandCompanyNetProfit, 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-<?= $grandCompanyNetProfit >= 0 ? 'success' : 'danger' ?> px-2 py-1">
                                            <?= $grandCompanyRevenue > 0 ? number_format(($grandCompanyNetProfit / $grandCompanyRevenue) * 100, 1) : '0.0' ?>%
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: Job Summary Report -->
                <div class="tab-pane fade" id="job-report" role="tabpanel" aria-labelledby="job-tab">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="font-weight-bold text-dark mb-0">
                            <i class="fas fa-list-alt text-primary mr-1"></i> รายงานสรุปผลประกอบการรายใบงาน (ยอดไม่รวม VAT)
                        </h5>
                        <small class="text-muted">* หักค่าเดินทางรถและค่าแรงพนักงานขับรถจากระบบการใช้รถ</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover shadow-sm rounded">
                            <thead class="bg-dark text-white text-center">
                                <tr>
                                    <th width="3%">#</th>
                                    <th width="12%">เลขใบงาน</th>
                                    <th width="14%">บริษัท</th>
                                    <th width="9%">วันที่เริ่ม</th>
                                    <th width="8%">สถานะ</th>
                                    <th width="11%">มูลค่างาน (Excl. VAT)</th>
                                    <th width="12%">เบิกของ/คชจ. (Excl. VAT)</th>
                                    <th width="13%">ค่าเดินทาง & ค่าแรงรถ</th>
                                    <th width="12%">กำไร/ขาดทุนสุทธิ</th>
                                    <th width="6%">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($jobDataList)): ?>
                                    <?php $no = 1; foreach ($jobDataList as $jData): ?>
                                        <tr>
                                            <td class="text-center align-middle"><?= $no++ ?></td>
                                            <td class="font-weight-bold align-middle">
                                                <?= Html::a(Html::encode($jData['job_no']), ['/job/view', 'id' => $jData['model']->id], ['target' => '_blank', 'class' => 'text-primary']) ?>
                                            </td>
                                            <td class="align-middle"><?= Html::encode($jData['company_name']) ?></td>
                                            <td class="text-center align-middle"><?= date('d/m/Y', strtotime($jData['start_date'])) ?></td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-secondary"><?= Html::encode($jData['status_text']) ?></span>
                                            </td>
                                            <td class="text-right font-weight-bold text-primary align-middle">
                                                <?= number_format($jData['revenue_no_vat'], 2) ?>
                                            </td>
                                            <td class="text-right text-warning font-weight-bold align-middle">
                                                <?= number_format($jData['withdraw_amount'], 2) ?>
                                            </td>
                                            <td class="text-right font-weight-bold text-info align-middle">
                                                <?= number_format($jData['vehicle_total'], 2) ?>
                                                <br>
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    (คชจ.: <?= number_format($jData['vehicle_cost'], 0) ?> | ค่าแรง: <?= number_format($jData['vehicle_wage'], 0) ?>)
                                                </small>
                                            </td>
                                            <td class="text-right font-weight-bold align-middle <?= $jData['net_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($jData['net_profit'], 2) ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-<?= $jData['profit_percentage'] >= 0 ? 'success' : 'danger' ?> px-2 py-1">
                                                    <?= number_format($jData['profit_percentage'], 1) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">ไม่พบข้อมูลใบงาน</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr class="text-right">
                                    <td colspan="5" class="text-center font-weight-bold">รวมทั้งสิ้น (<?= count($jobDataList) ?> ใบงาน)</td>
                                    <td class="text-primary font-weight-bold"><?= number_format($totalJobRevenue, 2) ?></td>
                                    <td class="text-warning font-weight-bold"><?= number_format($totalJobWithdraw, 2) ?></td>
                                    <td class="text-info font-weight-bold"><?= number_format($totalJobVehicleTotal, 2) ?></td>
                                    <td class="<?= $totalJobNetProfit >= 0 ? 'text-success' : 'text-danger' ?> font-weight-bold h6 mb-0">
                                        <?= number_format($totalJobNetProfit, 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-<?= $totalJobNetProfit >= 0 ? 'success' : 'danger' ?> px-2 py-1">
                                            <?= $totalJobRevenue > 0 ? number_format(($totalJobNetProfit / $totalJobRevenue) * 100, 1) : '0.0' ?>%
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Company Salary -->
<div class="modal fade" id="modal-salary" tabindex="-1" role="dialog" aria-labelledby="modalSalaryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-lg">
            <div class="modal-header bg-purple text-white" style="background-color: #6f42c1;">
                <h5 class="modal-title font-weight-bold" id="modalSalaryLabel">
                    <i class="fas fa-money-check-alt mr-1"></i> บันทึกเงินเดือนพนักงาน
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="salary-form">
                    <input type="hidden" id="salary-company-id" name="company_id">
                    <input type="hidden" id="salary-month" name="month" value="<?= $month ?>">
                    <input type="hidden" id="salary-year" name="year" value="<?= $year ?>">
                    
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">บริษัท</label>
                        <input type="text" id="salary-company-name" class="form-control bg-light" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">ยอดเงินเดือนประจำเดือน (<?= $month ?>/<?= $year ?>)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" id="salary-amount" class="form-control form-control-lg font-weight-bold text-purple" style="color: #6f42c1;" required>
                            <div class="input-group-append">
                                <span class="input-group-text font-weight-bold">บาท</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">หมายเหตุ</label>
                        <input type="text" id="salary-note" class="form-control" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)...">
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-purple font-weight-bold px-4 text-white" id="btn-save-salary" style="background-color: #6f42c1;">
                    <i class="fas fa-save mr-1"></i> บันทึกข้อมูล
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .report-portal-index .card {
        border-radius: 12px;
    }
    .nav-pills .nav-link {
        border-radius: 20px;
        color: #495057;
        background-color: #e9ecef;
        transition: all 0.2s ease-in-out;
    }
    .nav-pills .nav-link.active {
        background-color: #007bff;
        color: #fff;
        box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
    }
    .border-left-primary { border-left: 4px solid #007bff !important; }
    .border-left-warning { border-left: 4px solid #ffc107 !important; }
    .border-left-info { border-left: 4px solid #17a2b8 !important; }
    .border-left-success { border-left: 4px solid #28a745 !important; }
    .border-left-danger { border-left: 4px solid #dc3545 !important; }
</style>

<?php
$saveSalaryUrl = Url::to(['report-portal/save-company-salary']);
$js = <<<JS
$('.btn-edit-salary').on('click', function() {
    var companyId = $(this).data('company-id');
    var companyName = $(this).data('company-name');
    var salary = $(this).data('salary');

    $('#salary-company-id').val(companyId);
    $('#salary-company-name').val(companyName);
    $('#salary-amount').val(salary);
    $('#modal-salary').modal('show');
});

$('#btn-save-salary').on('click', function() {
    var data = {
        company_id: $('#salary-company-id').val(),
        month: $('#salary-month').val(),
        year: $('#salary-year').val(),
        amount: $('#salary-amount').val(),
        note: $('#salary-note').val(),
        _csrf: yii.getCsrfToken()
    };

    $.post('{$saveSalaryUrl}', data, function(res) {
        if (res.success) {
            $('#modal-salary').modal('hide');
            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() {
                    location.reload();
                });
            } else {
                alert(res.message);
                location.reload();
            }
        } else {
            alert('เกิดข้อผิดพลาด: ' + res.message);
        }
    });
});
JS;
$this->registerJs($js);
?>
