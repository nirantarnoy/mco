<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use backend\models\Company;

$this->title = 'Executive Dashboard (แดชบอร์ดผู้บริหาร 8.8)';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="executive-dashboard-index">

    <!-- Top Header & Filter Bar -->
    <div class="card shadow-sm mb-4 border-0 bg-white">
        <div class="card-body py-3">
            <form method="get" action="<?= Url::to(['executive-dashboard/index']) ?>" class="row g-3 align-items-center">
                <input type="hidden" name="r" value="executive-dashboard/index">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-secondary small"><i class="fas fa-building me-1"></i> บริษัทในเครือ</label>
                    <select name="company_id" class="form-select form-select-sm">
                        <option value="0">-- ทุกบริษัทในเครือ --</option>
                        <?php foreach (Company::find()->all() as $comp): ?>
                            <option value="<?= $comp->id ?>" <?= $companyId == $comp->id ? 'selected' : '' ?>>
                                <?= Html::encode($comp->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-secondary small"><i class="fas fa-calendar-alt me-1"></i> ตั้งแต่วันที่</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="<?= Html::encode($fromDate) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-secondary small"><i class="fas fa-calendar-alt me-1"></i> ถึงวันที่</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="<?= Html::encode($toDate) ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100 me-2">
                        <i class="fas fa-search me-1"></i> ประมวลผลข้อมูล
                    </button>
                    <a href="<?= Url::to(['executive-dashboard/index']) ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 8.8.2 Cashflow Warning Alert Banner -->
    <?php if ($isCashflowWarning): ?>
        <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center mb-4 p-3" role="alert">
            <i class="fas fa-exclamation-triangle fa-2x me-3 text-danger"></i>
            <div>
                <h5 class="alert-heading mb-1 fw-bold"><i class="fas fa-bell me-1"></i> เตือนกระแสเงินสด: สภาพคล่องในระบบไม่เพียงพอสำหรับยอดจ่าย PO!</h5>
                <p class="mb-0 small">
                    ยอดเงินรวมปัจจุบันคงเหลือ (<?= number_format($currentAvailableCash, 2) ?> บาท) + ยอดรอรับ (<?= number_format($pendingReceivables, 2) ?> บาท) 
                    <strong class="text-decoration-underline">น้อยกว่า</strong> ยอดผูกพันรอจ่าย PO (<?= number_format($pendingPoPayables, 2) ?> บาท)
                    กรุณาตรวจสอบการโอนสดย่อยหรือโอนเติมเข้าบัญชีหลัก
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-success shadow-sm border-0 d-flex align-items-center mb-4 p-3" role="alert">
            <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
            <div>
                <h5 class="alert-heading mb-1 fw-bold"><i class="fas fa-shield-alt me-1"></i> สถานะกระแสเงินสด: ปกติสมบูรณ์</h5>
                <p class="mb-0 small">
                    ยอดเงินในระบบเพียงพอครอบคลุมภาระผูกพันชำระ PO และค่าใช้จ่ายประจำช่วงเวลานี้
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- 8.8.1 Group Companies Financial Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Expenses -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-gradient-danger text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase small fw-bold">8.8.1 ค่าใช้จ่ายรวม</span>
                        <div class="icon-shape bg-white bg-opacity-25 rounded-circle p-2">
                            <i class="fas fa-receipt text-white"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold"><?= number_format($totalExpenses, 2) ?> <small class="fs-6">บาท</small></h3>
                    <div class="small opacity-75">รวม PO, None PR, รถยนต์ & ค่าแรง</div>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-gradient-success text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase small fw-bold">8.8.1 รายรับรวม</span>
                        <div class="icon-shape bg-white bg-opacity-25 rounded-circle p-2">
                            <i class="fas fa-hand-holding-usd text-white"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold"><?= number_format($totalRevenue, 2) ?> <small class="fs-6">บาท</small></h3>
                    <div class="small opacity-75">ยอดตามใบแจ้งหนี้ / Invoice</div>
                </div>
            </div>
        </div>

        <!-- Pending Receivables -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-gradient-warning text-dark">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase small fw-bold">8.8.1 ยอดค้างรับ</span>
                        <div class="icon-shape bg-dark bg-opacity-10 rounded-circle p-2">
                            <i class="fas fa-hourglass-half text-dark"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold"><?= number_format($pendingReceivables, 2) ?> <small class="fs-6">บาท</small></h3>
                    <div class="small opacity-75">ลูกหนี้การค้ายังไม่ได้รับชำระ</div>
                </div>
            </div>
        </div>

        <!-- Car Mileage -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-gradient-info text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase small fw-bold">8.8.1 ใช้รถยนต์ (กม.ละ 5 บาท)</span>
                        <div class="icon-shape bg-white bg-opacity-25 rounded-circle p-2">
                            <i class="fas fa-car text-white"></i>
                        </div>
                    </div>
                    <h3 class="mb-1 fw-bold"><?= number_format($vehicleCostByKm, 2) ?> <small class="fs-6">บาท</small></h3>
                    <div class="small opacity-75"><?= number_format($totalKm, 1) ?> กม. x 5.00 บาท/กม.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Net Profit / Loss Highlight Banner -->
    <div class="card border-0 shadow-sm mb-4 <?= $netProfitLoss >= 0 ? 'bg-success text-white' : 'bg-danger text-white' ?>">
        <div class="card-body py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2"></i> สรุปผลกำไร/ขาดทุนสุทธิภาพรวม (Net Profit / Loss)</h5>
                <small class="opacity-75">คำนวณจาก [ รายรับรวม - (ค่าใช้จ่ายรวม + ค่าใช้รถยนต์ตามระยะทาง) ]</small>
            </div>
            <div class="text-end">
                <h2 class="mb-0 fw-bold"><?= number_format($netProfitLoss, 2) ?> บาท</h2>
                <span class="badge bg-white <?= $netProfitLoss >= 0 ? 'text-success' : 'text-danger' ?> fw-bold">
                    <?= $netProfitLoss >= 0 ? 'กำไรสุทธิ (PROFIT)' : 'ขาดทุนสุทธิ (LOSS)' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- 8.8.2 Accounting Balance Comparison & Monthly Closing Section -->
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-university text-primary me-2"></i> 8.8.2 สรุปเปรียบเทียบบัญชีจริง กับ ใน MCOAutomation</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#monthlyClosingModal">
                        <i class="fas fa-file-invoice-dollar me-1"></i> ปิดยอด/จัดเก็บไฟล์ประจำเดือน
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ประเภทบัญชี</th>
                                    <th class="text-end">ยอดเงินคงเหลือปัจจุบัน</th>
                                    <th class="text-end">ยอดจะจ่าย (PO/Payables)</th>
                                    <th class="text-end">ยอดจะรับ (Receivables)</th>
                                    <th class="text-center">สถานะสภาพคล่อง</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>1. บัญชีสดย่อย (Petty Cash Account)</strong></td>
                                    <td class="text-end fw-bold text-primary"><?= number_format($totalPettyCashBalance, 2) ?></td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-end text-muted">-</td>
                                    <td class="text-center"><span class="badge bg-info">ใช้งานประจำวัน</span></td>
                                </tr>
                                <tr>
                                    <td><strong>2. บัญชีหลักที่ต้องโอนเมื่อเปิด PO (Main Bank Accounts)</strong></td>
                                    <td class="text-end fw-bold text-success"><?= number_format($totalMainBankBalance, 2) ?></td>
                                    <td class="text-end text-danger fw-bold"><?= number_format($pendingPoPayables, 2) ?></td>
                                    <td class="text-end text-warning fw-bold"><?= number_format($pendingReceivables, 2) ?></td>
                                    <td class="text-center">
                                        <?php if ($totalMainBankBalance >= $pendingPoPayables): ?>
                                            <span class="badge bg-success">เพียงพอชำระ PO</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">ต้องโอนเงินเติม</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>ยอดเงินรวมในระบบชำระเงิน</td>
                                    <td class="text-end text-primary fs-6"><?= number_format($currentAvailableCash, 2) ?></td>
                                    <td class="text-end text-danger fs-6"><?= number_format($pendingPoPayables, 2) ?></td>
                                    <td class="text-end text-warning fs-6"><?= number_format($pendingReceivables, 2) ?></td>
                                    <td class="text-center">
                                        <?= $isCashflowWarning ? '<span class="badge bg-danger">เงินไม่พอจ่าย</span>' : '<span class="badge bg-success">ปกติ</span>' ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-folder-open text-info me-2"></i> ประวัติปิดยอด/จัดเก็บไฟล์ประจำเดือน</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($monthlyClosings)): ?>
                        <div class="p-3 text-center text-muted">ยังไม่มีประวัติจัดเก็บไฟล์ปิดยอดประจำเดือน</div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($monthlyClosings as $mc): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>เดือน <?= Html::encode($mc->year_month) ?></strong>
                                        <div class="small text-muted">สดย่อย: <?= number_format($mc->petty_cash_balance, 2) ?> | บัญชีหลัก: <?= number_format($mc->main_account_balance, 2) ?></div>
                                    </div>
                                    <?php if ($mc->statement_file): ?>
                                        <a href="<?= Yii::getAlias('@web/uploads/statements/') . $mc->statement_file ?>" target="_blank" class="btn btn-outline-info btn-xs btn-sm">
                                            <i class="fas fa-download"></i> Statement
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 8.8.4 Advanced Search & Drill-Down Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-search me-2"></i> 8.8.4 ระบบค้นหาข้อมูลเชิงลึก (Advanced Search to Job Pipeline)</h6>
            <span class="badge bg-primary">8.8.4.1 ค้นหาค้นคืนเพื่อเข้าหน้า 8.8.3</span>
        </div>
        <div class="card-body">
            <form method="get" action="<?= Url::to(['executive-dashboard/index']) ?>" class="row g-2 mb-3">
                <input type="hidden" name="r" value="executive-dashboard/index">
                <div class="col-md-3">
                    <input type="text" name="search_job_no" class="form-control form-control-sm" placeholder="ค้นหา Job Number..." value="<?= Html::encode($searchJobNo) ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="search_customer" class="form-control form-control-sm" placeholder="ชื่อลูกค้า / รหัสลูกค้า..." value="<?= Html::encode($searchCustomer) ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="search_vendor" class="form-control form-control-sm" placeholder="ชื่อ Vendor / ผู้ขาย..." value="<?= Html::encode($searchVendor) ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-dark btn-sm w-100">
                        <i class="fas fa-filter me-1"></i> ค้นหา Job Number
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-secondary text-uppercase small">
                        <tr>
                            <th class="text-center" style="width: 5%">#</th>
                            <th style="width: 15%">Job Number</th>
                            <th style="width: 25%">ลูกค้า</th>
                            <th style="width: 15%">บริษัท</th>
                            <th class="text-right" style="width: 15%">มูลค่างาน (บาท)</th>
                            <th class="text-center" style="width: 15%">สถานะ 15 ขั้นตอน</th>
                            <th class="text-center" style="width: 10%">Action (8.8.3)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($searchJobsList)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">ไม่พบข้อมูล Job Number ตามเงื่อนไขค้นหา</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($searchJobsList as $idx => $jobItem): ?>
                                <tr>
                                    <td class="text-center"><?= $idx + 1 ?></td>
                                    <td>
                                        <a href="<?= Url::to(['executive-dashboard/job-pipeline', 'id' => $jobItem->id]) ?>" class="fw-bold text-primary">
                                            <i class="fas fa-folder me-1"></i> <?= Html::encode($jobItem->job_no) ?>
                                        </a>
                                    </td>
                                    <td><?= Html::encode($jobItem->customer_name ?: ($jobItem->customer ? $jobItem->customer->name : '-')) ?></td>
                                    <td><?= $jobItem->company ? Html::encode($jobItem->company->name) : '-' ?></td>
                                    <td class="text-right fw-bold">
                                        <?= $jobItem->quotation ? number_format($jobItem->quotation->grand_total, 2) : '0.00' ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= Url::to(['executive-dashboard/job-pipeline', 'id' => $jobItem->id]) ?>" class="btn btn-xs btn-outline-primary rounded-pill">
                                            <i class="fas fa-tasks me-1"></i> ดูรายละเอียด 15 ขั้นตอน
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= Url::to(['executive-dashboard/job-pipeline', 'id' => $jobItem->id]) ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-arrow-right"></i> เปิดดู
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal บันทึกปิดยอดประจำเดือน -->
<div class="modal fade" id="monthlyClosingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php $form = ActiveForm::begin([
                'action' => ['executive-dashboard/monthly-closing'],
                'options' => ['enctype' => 'multipart/form-data']
            ]); ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-invoice-dollar me-2"></i> ปิดยอด/จัดเก็บไฟล์ประจำเดือน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">บริษัทในเครือ</label>
                    <select name="company_id" class="form-select" required>
                        <?php foreach (Company::find()->all() as $comp): ?>
                            <option value="<?= $comp->id ?>"><?= Html::encode($comp->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ประจำเดือน (YYYY-MM)</label>
                    <input type="month" name="year_month" class="form-control" value="<?= date('Y-m') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ยอดคงเหลือเงินสดย่อย (บาท)</label>
                    <input type="number" step="0.01" name="petty_cash_balance" class="form-control" value="<?= $totalPettyCashBalance ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ยอดคงเหลือบัญชีหลัก (บาท)</label>
                    <input type="number" step="0.01" name="main_account_balance" class="form-control" value="<?= $totalMainBankBalance ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">แนบไฟล์ Statement ประจำเดือน</label>
                    <input type="file" name="statement_file" class="form-control" accept="application/pdf,image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">หมายเหตุ</label>
                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> บันทึกปิดยอด</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<style>
.bg-gradient-danger { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); }
.bg-gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.bg-gradient-warning { background: linear-gradient(135deg, #ffe000 0%, #799f0c 100%); }
.bg-gradient-info { background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%); }
</style>
