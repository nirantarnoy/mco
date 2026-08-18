<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use backend\models\Company;

$this->title = 'Executive Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Google Font Inter & Prompt -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap">

<div class="executive-dashboard-container py-3">

    <!-- Top Header Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-slate-800 mb-1" style="color: #0f172a; font-family: 'Prompt', sans-serif;">
                <i class="fas fa-chart-line text-indigo-600 me-2" style="color: #4f46e5;"></i> Executive Dashboard
            </h3>
            <p class="text-slate-500 mb-0 small" style="color: #64748b;">
                ภาพรวมสรุปการเงินบริษัทในเครือ, แจ้งเตือนกระแสเงินสด PO และติดตามสถานะกิจกรรม 15 ขั้นตอน
            </p>
        </div>
        <div>
            <span class="badge bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-2 rounded-pill fw-medium" style="background-color: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                <i class="fas fa-building me-1"></i> <?= $companyId ? Company::findName($companyId) : 'ทุกบริษัทในเครือ' ?>
            </span>
        </div>
    </div>

    <!-- Filter Card (Tailwind Style Light) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: #ffffff; border-radius: 16px;">
        <div class="card-body p-4">
            <form method="get" action="<?= Url::to(['executive-dashboard/index']) ?>" class="row g-3 align-items-end">
                <input type="hidden" name="r" value="executive-dashboard/index">
                <div class="col-md-3">
                    <label class="form-label text-slate-700 small fw-semibold" style="color: #334155;">
                        <i class="fas fa-building text-slate-400 me-1"></i> บริษัทในเครือ
                    </label>
                    <select name="company_id" class="form-select form-select-modern">
                        <option value="0">-- ทุกบริษัทในเครือ --</option>
                        <?php foreach (Company::find()->all() as $comp): ?>
                            <option value="<?= $comp->id ?>" <?= $companyId == $comp->id ? 'selected' : '' ?>>
                                <?= Html::encode($comp->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-slate-700 small fw-semibold" style="color: #334155;">
                        <i class="fas fa-calendar-alt text-slate-400 me-1"></i> ตั้งแต่วันที่
                    </label>
                    <input type="date" name="from_date" class="form-control form-select-modern" value="<?= Html::encode($fromDate) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-slate-700 small fw-semibold" style="color: #334155;">
                        <i class="fas fa-calendar-alt text-slate-400 me-1"></i> ถึงวันที่
                    </label>
                    <input type="date" name="to_date" class="form-control form-select-modern" value="<?= Html::encode($toDate) ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-indigo-modern flex-grow-1">
                        <i class="fas fa-search me-1"></i> ประมวลผล
                    </button>
                    <a href="<?= Url::to(['executive-dashboard/index']) ?>" class="btn btn-light-modern" title="รีเซ็ต">
                        <i class="fas fa-sync-alt text-slate-500"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 8.8.2 Cashflow Warning Alert Banner -->
    <?php if ($isCashflowWarning): ?>
        <div class="alert border-0 shadow-sm rounded-4 p-4 mb-4 d-flex align-items-center" style="background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%); border: 1px solid #fecdd3 !important; border-radius: 16px;">
            <div class="me-3 p-3 rounded-circle" style="background-color: #fecdd3; color: #e11d48;">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-1" style="color: #9f1239;"><i class="fas fa-bell me-1"></i> แจ้งเตือนสภาพคล่องกระแสเงินสด!</h5>
                <p class="mb-0 small" style="color: #be123c;">
                    ยอดเงินรวมปัจจุบันคงเหลือ (<strong><?= number_format($currentAvailableCash, 2) ?></strong> บาท) + ยอดรอรับ (<strong><?= number_format($pendingReceivables, 2) ?></strong> บาท) 
                    <span class="badge bg-rose-200 text-rose-800">น้อยกว่า</span> ยอดรอชำระ PO (<strong><?= number_format($pendingPoPayables, 2) ?></strong> บาท)
                    กรุณาตรวจสอบโอนเติมเงินเข้าบัญชีหลักก่อนการอนุมัติโอนชำระ PO
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="alert border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0 !important; border-radius: 16px;">
            <div class="me-3 p-2 rounded-circle" style="background-color: #bbf7d0; color: #15803d;">
                <i class="fas fa-check-circle fa-lg"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0" style="color: #166534;"><i class="fas fa-shield-alt me-1"></i> สภาพคล่องกระแสเงินสด: สมบูรณ์เพียงพอ</h6>
                <p class="mb-0 small" style="color: #15803d;">
                    ยอดเงินสดย่อยและบัญชีหลักในระบบเพียงพอครอบคลุมภาระผูกพันชำระ PO
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- 8.8.1 Group Companies Financial Summary (Modern Light Tailwind Cards) -->
    <div class="row g-4 mb-4">
        <!-- Expenses Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-hover" style="background-color: #ffffff; border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-slate-500 small fw-bold uppercase-tracking" style="color: #64748b;">ค่าใช้จ่ายรวม</span>
                        <div class="p-3 rounded-3" style="background-color: #ffe4e6; color: #e11d48;">
                            <i class="fas fa-receipt fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1" style="color: #be123c; font-family: 'Inter', sans-serif;">
                        <?= number_format($totalExpenses, 2) ?>
                    </h2>
                    <div class="small text-slate-500" style="color: #94a3b8;">
                        <i class="fas fa-info-circle me-1"></i> รวม PO, None PR, ค่ารถ & ค่าแรง
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-hover" style="background-color: #ffffff; border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-slate-500 small fw-bold uppercase-tracking" style="color: #64748b;">รายรับรวม</span>
                        <div class="p-3 rounded-3" style="background-color: #d1fae5; color: #059669;">
                            <i class="fas fa-hand-holding-usd fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1" style="color: #047857; font-family: 'Inter', sans-serif;">
                        <?= number_format($totalRevenue, 2) ?>
                    </h2>
                    <div class="small text-slate-500" style="color: #94a3b8;">
                        <i class="fas fa-file-invoice me-1"></i> ตามใบแจ้งหนี้ Invoice
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Receivables Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-hover" style="background-color: #ffffff; border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-slate-500 small fw-bold uppercase-tracking" style="color: #64748b;">ยอดค้างรับ</span>
                        <div class="p-3 rounded-3" style="background-color: #fef3c7; color: #d97706;">
                            <i class="fas fa-hourglass-half fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1" style="color: #b45309; font-family: 'Inter', sans-serif;">
                        <?= number_format($pendingReceivables, 2) ?>
                    </h2>
                    <div class="small text-slate-500" style="color: #94a3b8;">
                        <i class="fas fa-user-clock me-1"></i> ลูกหนี้การค้ายังไม่ได้รับชำระ
                    </div>
                </div>
            </div>
        </div>

        <!-- Car Mileage Cost Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-hover" style="background-color: #ffffff; border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-slate-500 small fw-bold uppercase-tracking" style="color: #64748b;">ใช้รถยนต์ (กม.ละ 5 บาท)</span>
                        <div class="p-3 rounded-3" style="background-color: #e0f2fe; color: #0284c7;">
                            <i class="fas fa-car fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-1" style="color: #0369a1; font-family: 'Inter', sans-serif;">
                        <?= number_format($vehicleCostByKm, 2) ?>
                    </h2>
                    <div class="small text-slate-500" style="color: #94a3b8;">
                        <i class="fas fa-route me-1"></i> <?= number_format($totalKm, 1) ?> กม. x 5.00 บาท
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Net Profit / Loss Light Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: <?= $netProfitLoss >= 0 ? 'linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%)' : 'linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%)' ?>; border: 1px solid <?= $netProfitLoss >= 0 ? '#a7f3d0' : '#fecdd3' ?> !important; border-radius: 16px;">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1" style="color: <?= $netProfitLoss >= 0 ? '#065f46' : '#9f1239' ?>; font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-chart-pie me-2"></i> สรุปผลกำไร / ขาดทุนสุทธิภาพรวม (Net Profit / Loss)
                </h5>
                <span class="small" style="color: <?= $netProfitLoss >= 0 ? '#047857' : '#be123c' ?>;">
                    คำนวณจาก [ รายรับรวม - (ค่าใช้จ่ายรวม + ค่าใช้รถยนต์ตามระยะทาง) ]
                </span>
            </div>
            <div class="text-end">
                <h1 class="fw-bold mb-0" style="color: <?= $netProfitLoss >= 0 ? '#047857' : '#be123c' ?>; font-family: 'Inter', sans-serif;">
                    <?= number_format($netProfitLoss, 2) ?> <small class="fs-6">บาท</small>
                </h1>
                <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: <?= $netProfitLoss >= 0 ? '#d1fae5' : '#ffe4e6' ?>; color: <?= $netProfitLoss >= 0 ? '#047857' : '#be123c' ?>;">
                    <?= $netProfitLoss >= 0 ? '🟢 กำไรสุทธิ (PROFIT)' : '🔴 ขาดทุนสุทธิ (LOSS)' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- 8.8.2 Account Balance Comparison & Monthly Closing Section -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background-color: #ffffff; border-radius: 16px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-slate-800" style="color: #1e293b; font-family: 'Prompt', sans-serif;">
                        <i class="fas fa-university text-indigo-600 me-2" style="color: #4f46e5;"></i> สรุปเปรียบเทียบบัญชีจริง กับ ใน MCOAutomation
                    </h6>
                    <button type="button" class="btn btn-indigo-modern btn-sm" data-bs-toggle="modal" data-bs-target="#monthlyClosingModal">
                        <i class="fas fa-file-invoice-dollar me-1"></i> ปิดยอด/จัดเก็บไฟล์ประจำเดือน
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ประเภทบัญชี</th>
                                    <th class="text-end">ยอดเงินคงเหลือปัจจุบัน</th>
                                    <th class="text-end">ยอดจะจ่าย (PO/Payables)</th>
                                    <th class="text-end">ยอดจะรับ (Receivables)</th>
                                    <th class="text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-slate-800">1. บัญชีสดย่อย (Petty Cash Account)</div>
                                        <div class="small text-slate-400">เงินสดย่อยหมุนเวียนประจำวัน</div>
                                    </td>
                                    <td class="text-end fw-bold text-indigo-600 fs-6" style="color: #4f46e5;"><?= number_format($totalPettyCashBalance, 2) ?></td>
                                    <td class="text-end text-slate-400">-</td>
                                    <td class="text-end text-slate-400">-</td>
                                    <td class="text-center"><span class="badge-soft badge-soft-info">ใช้งานประจำวัน</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-slate-800">2. บัญชีหลักที่ต้องโอนเมื่อเปิด PO (Main Accounts)</div>
                                        <div class="small text-slate-400">บัญชีธนาคารหลักสำหรับเปิด PO</div>
                                    </td>
                                    <td class="text-end fw-bold text-emerald-600 fs-6" style="color: #059669;"><?= number_format($totalMainBankBalance, 2) ?></td>
                                    <td class="text-end text-rose-600 fw-bold fs-6" style="color: #e11d48;"><?= number_format($pendingPoPayables, 2) ?></td>
                                    <td class="text-end text-amber-600 fw-bold fs-6" style="color: #d97706;"><?= number_format($pendingReceivables, 2) ?></td>
                                    <td class="text-center">
                                        <?php if ($totalMainBankBalance >= $pendingPoPayables): ?>
                                            <span class="badge-soft badge-soft-success">เพียงพอชำระ PO</span>
                                        <?php else: ?>
                                            <span class="badge-soft badge-soft-danger">ต้องโอนเงินเติม</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="fw-bold text-slate-800">ยอดเงินรวมในระบบชำระเงิน</td>
                                    <td class="text-end fw-bold text-indigo-600 fs-6" style="color: #4f46e5;"><?= number_format($currentAvailableCash, 2) ?></td>
                                    <td class="text-end fw-bold text-rose-600 fs-6" style="color: #e11d48;"><?= number_format($pendingPoPayables, 2) ?></td>
                                    <td class="text-end fw-bold text-amber-600 fs-6" style="color: #d97706;"><?= number_format($pendingReceivables, 2) ?></td>
                                    <td class="text-center">
                                        <?= $isCashflowWarning ? '<span class="badge-soft badge-soft-danger">เงินไม่พอจ่าย</span>' : '<span class="badge-soft badge-soft-success">ปกติ</span>' ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background-color: #ffffff; border-radius: 16px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0 text-slate-800" style="color: #1e293b; font-family: 'Prompt', sans-serif;">
                        <i class="fas fa-folder-open text-sky-500 me-2" style="color: #0284c7;"></i> ประวัติปิดยอด/จัดเก็บไฟล์ประจำเดือน
                    </h6>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($monthlyClosings)): ?>
                        <div class="p-4 text-center text-slate-400 bg-slate-50 rounded-3" style="background-color: #f8fafc; color: #94a3b8;">
                            <i class="fas fa-folder-minus fa-2x mb-2 opacity-50"></i>
                            <div class="small">ยังไม่มีประวัติจัดเก็บไฟล์ปิดยอดประจำเดือน</div>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush border-0">
                            <?php foreach ($monthlyClosings as $mc): ?>
                                <div class="list-group-item border-0 bg-slate-50 rounded-3 mb-2 p-3 d-flex justify-content-between align-items-center" style="background-color: #f8fafc;">
                                    <div>
                                        <div class="fw-bold text-slate-800">เดือน <?= Html::encode($mc->year_month) ?></div>
                                        <div class="small text-slate-500" style="font-size: 0.8rem; color: #64748b;">
                                            สดย่อย: <?= number_format($mc->petty_cash_balance, 2) ?> | หลัก: <?= number_format($mc->main_account_balance, 2) ?>
                                        </div>
                                    </div>
                                    <?php if ($mc->statement_file): ?>
                                        <a href="<?= Yii::getAlias('@web/uploads/statements/') . $mc->statement_file ?>" target="_blank" class="btn btn-light-modern btn-xs">
                                            <i class="fas fa-download me-1 text-sky-600"></i> Statement
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 8.8.4 Advanced Search & Drill-Down Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: #ffffff; border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-1 text-slate-800" style="color: #1e293b; font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-search-location text-indigo-600 me-2" style="color: #4f46e5;"></i> ระบบค้นหาข้อมูลเชิงลึก (Advanced Search to Job Pipeline)
                </h6>
                <div class="small text-slate-500" style="color: #64748b;">ค้นหา Job Number เพื่อเจาะลึกเข้าไปดูรายละเอียด 15 ขั้นตอน</div>
            </div>
            <span class="badge bg-indigo-50 text-indigo-700 px-3 py-2 rounded-pill fw-medium" style="background-color: #eef2ff; color: #4338ca;">
                Search & Drill-Down
            </span>
        </div>
        <div class="card-body p-4">
            <form method="get" action="<?= Url::to(['executive-dashboard/index']) ?>" class="row g-3 mb-4">
                <input type="hidden" name="r" value="executive-dashboard/index">
                <div class="col-md-3">
                    <input type="text" name="search_job_no" class="form-control form-select-modern" placeholder="ค้นหา Job Number..." value="<?= Html::encode($searchJobNo) ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="search_customer" class="form-control form-select-modern" placeholder="ชื่อลูกค้า / รหัสลูกค้า..." value="<?= Html::encode($searchCustomer) ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="search_vendor" class="form-control form-select-modern" placeholder="ชื่อ Vendor / ผู้ขาย..." value="<?= Html::encode($searchVendor) ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-indigo-modern w-100">
                        <i class="fas fa-filter me-1"></i> ค้นหา Job Number
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%">#</th>
                            <th style="width: 15%">Job Number</th>
                            <th style="width: 25%">ลูกค้า</th>
                            <th style="width: 15%">บริษัท</th>
                            <th class="text-end" style="width: 15%">มูลค่างาน (บาท)</th>
                            <th class="text-center" style="width: 15%">สถานะ 15 ขั้นตอน</th>
                            <th class="text-center" style="width: 10%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($searchJobsList)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-slate-400 py-4" style="color: #94a3b8;">
                                    <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                                    <div>ไม่พบข้อมูล Job Number ตามเงื่อนไขค้นหา</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($searchJobsList as $idx => $jobItem): ?>
                                <tr>
                                    <td class="text-center text-slate-400" style="color: #94a3b8;"><?= $idx + 1 ?></td>
                                    <td>
                                        <a href="<?= Url::to(['executive-dashboard/job-pipeline', 'id' => $jobItem->id]) ?>" class="fw-bold text-indigo-600" style="color: #4f46e5;">
                                            <i class="fas fa-folder me-1"></i> <?= Html::encode($jobItem->job_no) ?>
                                        </a>
                                    </td>
                                    <td><span class="fw-medium text-slate-800" style="color: #334155;"><?= Html::encode($jobItem->customerName) ?></span></td>
                                    <td><span class="small text-slate-600" style="color: #475569;"><?= $jobItem->company ? Html::encode($jobItem->company->name) : '-' ?></span></td>
                                    <td class="text-end fw-bold text-slate-800" style="color: #1e293b;">
                                        <?= number_format($jobItem->job_amount ?: ($jobItem->quotation ? $jobItem->quotation->total_amount : 0), 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= Url::to(['executive-dashboard/job-pipeline', 'id' => $jobItem->id]) ?>" class="badge-soft badge-soft-indigo text-decoration-none">
                                            <i class="fas fa-tasks me-1"></i> รายละเอียด 15 ขั้นตอน
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= Url::to(['executive-dashboard/job-pipeline', 'id' => $jobItem->id]) ?>" class="btn btn-indigo-modern btn-xs">
                                            <i class="fas fa-external-link-alt me-1"></i> เปิดดู
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

<!-- Modal บันทึกปิดยอดประจำเดือน (Tailwind Light Modal) -->
<div class="modal fade" id="monthlyClosingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4" style="border-radius: 16px;">
            <?php $form = ActiveForm::begin([
                'action' => ['executive-dashboard/monthly-closing'],
                'options' => ['enctype' => 'multipart/form-data']
            ]); ?>
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-slate-800" style="color: #1e293b; font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-file-invoice-dollar text-indigo-600 me-2" style="color: #4f46e5;"></i> ปิดยอด/จัดเก็บไฟล์ประจำเดือน
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label text-slate-700 small fw-semibold">บริษัทในเครือ</label>
                    <select name="company_id" class="form-select form-select-modern" required>
                        <?php foreach (Company::find()->all() as $comp): ?>
                            <option value="<?= $comp->id ?>"><?= Html::encode($comp->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-slate-700 small fw-semibold">ประจำเดือน (YYYY-MM)</label>
                    <input type="month" name="year_month" class="form-control form-select-modern" value="<?= date('Y-m') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-slate-700 small fw-semibold">ยอดคงเหลือเงินสดย่อย (บาท)</label>
                    <input type="number" step="0.01" name="petty_cash_balance" class="form-control form-select-modern" value="<?= $totalPettyCashBalance ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-slate-700 small fw-semibold">ยอดคงเหลือบัญชีหลัก (บาท)</label>
                    <input type="number" step="0.01" name="main_account_balance" class="form-control form-select-modern" value="<?= $totalMainBankBalance ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-slate-700 small fw-semibold">แนบไฟล์ Statement ประจำเดือน</label>
                    <input type="file" name="statement_file" class="form-control form-select-modern" accept="application/pdf,image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label text-slate-700 small fw-semibold">หมายเหตุ</label>
                    <textarea name="remarks" class="form-control form-select-modern" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light-modern" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-indigo-modern"><i class="fas fa-save me-1"></i> บันทึกปิดยอด</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<!-- Tailwind CSS Light Style Custom CSS -->
<style>
body {
    background-color: #f8fafc !important;
    font-family: 'Inter', 'Prompt', sans-serif;
}
.form-select-modern {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.5rem 0.85rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}
.form-select-modern:focus {
    background-color: #ffffff;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}
.btn-indigo-modern {
    background-color: #4f46e5;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s ease;
}
.btn-indigo-modern:hover {
    background-color: #4338ca;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}
.btn-light-modern {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
}
.btn-light-modern:hover {
    background-color: #e2e8f0;
    color: #1e293b;
}
.transition-hover {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.transition-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01) !important;
}
.uppercase-tracking {
    letter-spacing: 0.05em;
    font-size: 0.75rem;
}
.table-custom {
    border-collapse: separate;
    border-spacing: 0;
}
.table-custom th {
    background-color: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #e2e8f0;
}
.table-custom td {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
}
.table-custom tr:last-child td {
    border-bottom: none;
}
.badge-soft {
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-weight: 500;
    font-size: 0.75rem;
    display: inline-block;
}
.badge-soft-info { background-color: #e0f2fe; color: #0369a1; }
.badge-soft-success { background-color: #d1fae5; color: #047857; }
.badge-soft-danger { background-color: #ffe4e6; color: #be123c; }
.badge-soft-indigo { background-color: #eef2ff; color: #4338ca; }
</style>
