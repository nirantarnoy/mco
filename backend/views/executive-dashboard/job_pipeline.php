<?php
use yii\helpers\Html;
use yii\helpers\Url;
use backend\models\JobActivityStatus;

$this->title = '8.8.3 สถานะกิจกรรม MCOAutomation: Job ' . Html::encode($job->job_no);
$this->params['breadcrumbs'][] = ['label' => 'Executive Dashboard', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$stepsDef = [
    1 => ['name' => '1. เปิด Job No.', 'detail' => 'แนบ PO ลูกค้า', 'icon' => 'fa-folder-open'],
    2 => ['name' => '2. เปิด PO และไม่เปิด PO', 'detail' => 'แนบใบเซ็นรับ PO จาก Vendor + ใบโอนเงิน (กำหนดวันเตือน)', 'icon' => 'fa-file-invoice'],
    3 => ['name' => '3. รับของจาก Vendor', 'detail' => 'Invoice ใบรับสินค้า', 'icon' => 'fa-truck-loading'],
    4 => ['name' => '4. เบิกของ / คืนของ', 'detail' => 'กำหนดวันเตือนคืนสินค้า', 'icon' => 'fa-boxes'],
    5 => ['name' => '5. อบรมเซฟตี้ & JSA', 'detail' => 'แนบเอกสารอบรมที่ลูกค้าอนุมัติ (ลูกค้าดาวน์โหลดได้)', 'icon' => 'fa-user-shield'],
    6 => ['name' => '6. Engineering จบ', 'detail' => 'พิจารณาและสรุปแบบการออกแบบ', 'icon' => 'fa-drafting-compass'],
    7 => ['name' => '7. ออก Final Report / Certificate', 'detail' => 'ลูกค้าตรวจรับงาน (ลูกค้าดาวน์โหลดได้)', 'icon' => 'fa-certificate'],
    8 => ['name' => '8. ประเมินผลจากลูกค้า', 'detail' => 'แบบฟอร์มประเมินให้ลูกค้ากรอก/ดาวน์โหลด', 'icon' => 'fa-star'],
    9 => ['name' => '9. ออก Invoice', 'detail' => 'ใบเซ็นรับสินค้า/บริการจากลูกค้า', 'icon' => 'fa-file-signature'],
    10 => ['name' => '10. อัตรากำไรสุทธิ %', 'detail' => '🟢 กำไร ≥20% | 🟠 กำไร <20% | 🔴 ขาดทุน', 'icon' => 'fa-chart-pie'],
    11 => ['name' => '11. เหลือเวลาทำงานกี่วัน', 'detail' => 'นับถอยหลังวันส่งมอบงานตามสัญญา', 'icon' => 'fa-hourglass-half'],
    12 => ['name' => '12. ใบเสร็จเงินเข้าบัญชีหรือยัง', 'detail' => 'แนบหลักฐานการโอนเงินชำระจากลูกค้า', 'icon' => 'fa-receipt'],
    13 => ['name' => '13. ระยะทางใช้รถยนต์ (กม.)', 'detail' => 'คำนวณ กม.ละ 5.00 บาท จากระบบบันทึกรถยนต์', 'icon' => 'fa-car'],
    14 => ['name' => '14. จำนวนบุคลากรปฏิบัติงาน', 'detail' => 'จำนวนพนักงาน/ช่างที่ลงปฏิบัติงาน', 'icon' => 'fa-users'],
    15 => ['name' => '15. คำนวณสรุปกำไรขาดทุนประจำ Job', 'detail' => 'สรุปรายได้ หัก ต้นทุนและค่าใช้จ่ายรวม', 'icon' => 'fa-calculator'],
];
?>

<!-- Google Font Inter & Prompt -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap">

<div class="job-pipeline-container py-3">

    <!-- Top Navigation Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= Url::to(['executive-dashboard/index']) ?>" class="text-indigo-600 text-decoration-none small fw-medium mb-1 d-inline-block" style="color: #4f46e5;">
                <i class="fas fa-arrow-left me-1"></i> ย้อนกลับหน้า Executive Dashboard
            </a>
            <h3 class="fw-bold text-slate-800 mb-0" style="color: #0f172a; font-family: 'Prompt', sans-serif;">
                <i class="fas fa-tasks text-indigo-600 me-2" style="color: #4f46e5;"></i> สถานะกิจกรรม 15 ขั้นตอน (8.8.3)
            </h3>
        </div>
        <div>
            <span class="badge bg-slate-100 text-slate-700 px-3 py-2 rounded-pill fw-medium" style="background-color: #f1f5f9; color: #334155;">
                Job No: <?= Html::encode($job->job_no) ?>
            </span>
        </div>
    </div>

    <!-- Header Job Summary Light Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: #ffffff; border-radius: 16px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #1e293b; font-family: 'Prompt', sans-serif;">
                        Job No: <span style="color: #4f46e5;"><?= Html::encode($job->job_no) ?></span>
                    </h4>
                    <div class="text-slate-500 small" style="color: #64748b;">
                        ลูกค้า: <strong style="color: #334155;"><?= Html::encode($job->customerName) ?></strong> 
                        | บริษัท: <strong style="color: #334155;"><?= $job->company ? Html::encode($job->company->name) : '-' ?></strong>
                    </div>
                </div>
                <div class="text-end">
                    <?php if ($jobProfitPercent >= 20): ?>
                        <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #d1fae5; color: #047857; font-size: 0.85rem;">
                            🟢 กำไร <?= number_format($jobProfitPercent, 1) ?>% (สีเขียว)
                        </span>
                    <?php elseif ($jobProfitPercent > 0): ?>
                        <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #fef3c7; color: #b45309; font-size: 0.85rem;">
                            🟠 กำไรน้อย <?= number_format($jobProfitPercent, 1) ?>% (สีส้ม)
                        </span>
                    <?php else: ?>
                        <span class="badge px-3 py-2 rounded-pill fw-bold" style="background-color: #ffe4e6; color: #be123c; font-size: 0.85rem;">
                            🔴 ขาดทุน <?= number_format($jobProfitPercent, 1) ?>% (สีแดง)
                        </span>
                    <?php endif; ?>

                    <div class="small mt-2" style="color: #64748b;">
                        <i class="fas fa-clock me-1 text-indigo-500"></i> เหลือเวลาทำงาน: <strong><?= $daysRemaining ?> วัน</strong>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Grid -->
            <div class="row g-3 text-center pt-3 border-top" style="border-color: #f1f5f9 !important;">
                <div class="col-md-3">
                    <div class="small text-slate-400" style="color: #94a3b8;">มูลค่างานรวม (Revenue)</div>
                    <div class="fs-5 fw-bold" style="color: #047857; font-family: 'Inter', sans-serif;"><?= number_format($jobRevenue, 2) ?> <small class="fs-6">บาท</small></div>
                </div>
                <div class="col-md-3">
                    <div class="small text-slate-400" style="color: #94a3b8;">ต้นทุนรวม (PO + None PR)</div>
                    <div class="fs-5 fw-bold" style="color: #be123c; font-family: 'Inter', sans-serif;"><?= number_format($jobPoTotal + $jobNonePrTotal, 2) ?> <small class="fs-6">บาท</small></div>
                </div>
                <div class="col-md-3">
                    <div class="small text-slate-400" style="color: #94a3b8;">ค่าใช้รถยนต์ (<?= number_format($jobKmTotal, 1) ?> กม. x 5 บาท)</div>
                    <div class="fs-5 fw-bold" style="color: #0369a1; font-family: 'Inter', sans-serif;"><?= number_format($jobKmCostAt5, 2) ?> <small class="fs-6">บาท</small></div>
                </div>
                <div class="col-md-3">
                    <div class="small text-slate-400" style="color: #94a3b8;">กำไร/ขาดทุนสุทธิ Job นี้</div>
                    <div class="fs-5 fw-bold" style="color: <?= $jobNetProfit >= 0 ? '#047857' : '#be123c' ?>; font-family: 'Inter', sans-serif;">
                        <?= number_format($jobNetProfit, 2) ?> <small class="fs-6">บาท</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 15 Steps Activity Light Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: #ffffff; border-radius: 16px;">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-1 text-slate-800" style="color: #1e293b; font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-list-check text-indigo-600 me-2" style="color: #4f46e5;"></i> สถานะกิจกรรม 15 ขั้นตอนใน MCOAutomation
                </h6>
                <div class="small text-slate-500" style="color: #64748b;">
                    🔴 สีแดง: ยังไม่ได้ทำ | 🟠 สีส้ม: ทำแล้วรอจัดเก็บไฟล์ | 🟢 สีเขียว: เก็บไฟล์แล้ว | ⚪ ยกเลิก: สิทธิ์ R1/R2
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0" id="activity-steps-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%">ขั้นตอน</th>
                            <th style="width: 25%">ชื่อกิจกรรมในระบบ</th>
                            <th style="width: 30%">รายละเอียด & เอกสารแนบ</th>
                            <th class="text-center" style="width: 15%">สถานะปัจจุบัน</th>
                            <th class="text-center" style="width: 25%">การจัดการ (Action / R1,R2 Cancel)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stepsDef as $sNo => $sInfo): 
                            $statusObj = $activityStatuses[$sNo] ?? null;
                            $stVal = $statusObj ? $statusObj->status : JobActivityStatus::STATUS_RED;
                        ?>
                            <tr id="step-row-<?= $sNo ?>">
                                <td class="text-center fw-bold fs-6" style="color: #4f46e5;"><?= $sNo ?></td>
                                <td>
                                    <div class="fw-semibold mb-1" style="color: #1e293b;">
                                        <i class="fas <?= $sInfo['icon'] ?> text-indigo-500 me-2" style="color: #6366f1;"></i> <?= Html::encode($sInfo['name']) ?>
                                    </div>
                                    <div class="small text-slate-400" style="color: #94a3b8;"><?= Html::encode($sInfo['detail']) ?></div>
                                </td>
                                <td>
                                    <?php if ($sNo == 10): ?>
                                        <span class="fw-bold" style="color: <?= $jobProfitPercent >= 20 ? '#047857' : ($jobProfitPercent > 0 ? '#b45309' : '#be123c') ?>;">
                                            อัตรากำไร: <?= number_format($jobProfitPercent, 2) ?>% 
                                            (<?= $jobProfitPercent >= 20 ? '🟢 สีเขียว' : ($jobProfitPercent > 0 ? '🟠 สีส้ม' : '🔴 สีแดง') ?>)
                                        </span>
                                    <?php elseif ($sNo == 11): ?>
                                        <span class="fw-semibold" style="color: #334155;">เหลือเวลาทำงาน: <span class="badge-soft badge-soft-info"><?= $daysRemaining ?> วัน</span></span>
                                    <?php elseif ($sNo == 13): ?>
                                        <span class="fw-semibold" style="color: #334155;">ระยะทางรวม: <?= number_format($jobKmTotal, 1) ?> กม. (<?= number_format($jobKmCostAt5, 2) ?> บาท)</span>
                                    <?php elseif ($sNo == 15): ?>
                                        <span class="fw-bold" style="color: <?= $jobNetProfit >= 0 ? '#047857' : '#be123c' ?>;">
                                            กำไรสุทธิ: <?= number_format($jobNetProfit, 2) ?> บาท
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 small" style="color: #94a3b8;">พร้อมแนบ/ดาวน์โหลดไฟล์เอกสารในระบบ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" id="status-badge-container-<?= $sNo ?>">
                                    <?= JobActivityStatus::getStatusLabel($stVal) ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm mb-1" role="group">
                                        <button type="button" class="btn btn-light-modern btn-change-status px-2" data-step="<?= $sNo ?>" data-status="0" title="ยังไม่ได้ทำ (Red)">
                                            🔴 Red
                                        </button>
                                        <button type="button" class="btn btn-light-modern btn-change-status px-2" data-step="<?= $sNo ?>" data-status="1" title="ทำแล้ว รอจัดเก็บไฟล์ (Orange)">
                                            🟠 Orange
                                        </button>
                                        <button type="button" class="btn btn-light-modern btn-change-status px-2" data-step="<?= $sNo ?>" data-status="2" title="เก็บไฟล์แล้ว (Green)">
                                            🟢 Green
                                        </button>
                                    </div>

                                    <?php if ($canCancel): ?>
                                        <div>
                                            <button type="button" class="btn btn-xs btn-light-modern btn-cancel-step mt-1" data-step="<?= $sNo ?>" title="ยกเลิกขั้นตอน (สิทธิ์ R1/R2)" style="color: #e11d48; border-color: #fecdd3;">
                                                <i class="fas fa-ban me-1"></i> กดยกเลิก (R1/R2)
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-end mb-4">
        <a href="<?= Url::to(['executive-dashboard/index']) ?>" class="btn btn-indigo-modern px-4">
            <i class="fas fa-arrow-left me-1"></i> ย้อนกลับไปหน้า Executive Dashboard
        </a>
    </div>

</div>

<!-- Tailwind CSS Light Style Custom CSS -->
<style>
body {
    background-color: #f8fafc !important;
    font-family: 'Inter', 'Prompt', sans-serif;
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
    background-color: #f8fafc;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}
.btn-light-modern:hover {
    background-color: #f1f5f9;
    color: #0f172a;
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
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #f1f5f9;
}
.table-custom tr:last-child td {
    border-bottom: none;
}
.badge-soft-info { background-color: #e0f2fe; color: #0369a1; padding: 0.25rem 0.6rem; border-radius: 9999px; }
</style>

<?php
$jobIdVal = $job->id;
$cancelUrl = Url::to(['executive-dashboard/cancel-step']);
$updateStatusUrl = Url::to(['executive-dashboard/update-step-status']);

$js = <<<JS
$('.btn-change-status').on('click', function() {
    var stepNo = $(this).data('step');
    var status = $(this).data('status');

    $.ajax({
        url: '{$updateStatusUrl}',
        type: 'POST',
        data: {job_id: {$jobIdVal}, step_no: stepNo, status: status},
        success: function(res) {
            if (res.success) {
                $('#status-badge-container-' + stepNo).html(res.status_html);
            } else {
                alert(res.message || 'ไม่สามารถอัปเดตสถานะได้');
            }
        }
    });
});

$('.btn-cancel-step').on('click', function() {
    var stepNo = $(this).data('step');
    if (confirm('คุณต้องการกดยกเลิกขั้นตอนกิจกรรมนี้ใช่หรือไม่? (สิทธิ์ R1 / R2)')) {
        $.ajax({
            url: '{$cancelUrl}',
            type: 'POST',
            data: {job_id: {$jobIdVal}, step_no: stepNo},
            success: function(res) {
                if (res.success) {
                    $('#status-badge-container-' + stepNo).html(res.status_html);
                } else {
                    alert(res.message || 'ไม่สามารถกดยกเลิกได้');
                }
            }
        });
    }
});
JS;
$this->registerJs($js);
?>
