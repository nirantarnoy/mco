<?php
use yii\helpers\Html;
use yii\helpers\Url;
use backend\models\JobActivityStatus;

$this->title = '8.8.3 สถานะกิจกรรม MCOAutomation: Job ' . Html::encode($job->job_no);
$this->params['breadcrumbs'][] = ['label' => 'Executive Dashboard', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Steps definition array
$stepsDef = [
    1 => ['name' => '1. เปิด Job No.', 'detail' => 'แนบ PO ลูกค้า', 'icon' => 'fa-folder-open'],
    2 => ['name' => '2. เปิด PO และไม่เปิด PO', 'detail' => 'แนบใบเซ็นรับ PO จาก Vendor + ใบโอนเงิน (มีกำหนดวันเตือน)', 'icon' => 'fa-file-invoice'],
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

<div class="job-pipeline-view">

    <!-- Header Job Summary Info Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h4 class="mb-1 text-warning fw-bold">
                    <i class="fas fa-project-diagram me-2"></i> Job No: <?= Html::encode($job->job_no) ?>
                </h4>
                <div class="small opacity-75">
                    ลูกค้า: <strong><?= Html::encode($job->customer_name ?: ($job->customer ? $job->customer->name : '-')) ?></strong>
                    | บริษัท: <strong><?= $job->company ? Html::encode($job->company->name) : '-' ?></strong>
                </div>
            </div>
            <div class="text-end">
                <!-- Profit Margin Badge -->
                <?php if ($jobProfitPercent >= 20): ?>
                    <span class="badge bg-success fs-6 p-2 mb-1"><i class="fas fa-check-circle me-1"></i> กำไร <?= number_format($jobProfitPercent, 1) ?>% (🟢 สีเขียว)</span>
                <?php elseif ($jobProfitPercent > 0): ?>
                    <span class="badge bg-warning text-dark fs-6 p-2 mb-1"><i class="fas fa-exclamation-triangle me-1"></i> กำไรน้อย <?= number_format($jobProfitPercent, 1) ?>% (🟠 สีส้ม)</span>
                <?php else: ?>
                    <span class="badge bg-danger fs-6 p-2 mb-1"><i class="fas fa-times-circle me-1"></i> ขาดทุน <?= number_format($jobProfitPercent, 1) ?>% (🔴 สีแดง)</span>
                <?php endif; ?>

                <div class="small text-white opacity-75">
                    <i class="fas fa-clock me-1"></i> เหลือเวลาทำงาน: <strong><?= $daysRemaining ?> วัน</strong>
                </div>
            </div>
        </div>
        <div class="card-body bg-light py-3">
            <div class="row text-center g-3">
                <div class="col-md-3 border-end">
                    <div class="text-muted small">มูลค่างานรวม (Revenue)</div>
                    <div class="fs-5 fw-bold text-success"><?= number_format($jobRevenue, 2) ?> บาท</div>
                </div>
                <div class="col-md-3 border-end">
                    <div class="text-muted small">ต้นทุนรวม (PO + ค่าใช้จ่าย)</div>
                    <div class="fs-5 fw-bold text-danger"><?= number_format($jobTotalExpenses, 2) ?> บาท</div>
                </div>
                <div class="col-md-3 border-end">
                    <div class="text-muted small">ค่าใช้รถยนต์ (<?= number_format($jobKmTotal, 1) ?> กม. x 5 บาท)</div>
                    <div class="fs-5 fw-bold text-info"><?= number_format($jobKmCostAt5, 2) ?> บาท</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">กำไร/ขาดทุนสุทธิ Job นี้</div>
                    <div class="fs-5 fw-bold <?= $jobNetProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= number_format($jobNetProfit, 2) ?> บาท
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 15 Steps Activity Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fas fa-list-check me-2"></i> สถานะกิจกรรม 15 ขั้นตอนใน MCOAutomation</h6>
            <div class="small">
                🔴 สีแดง: ยังไม่ได้ทำ | 🟠 สีส้ม: ทำแล้วรอจัดเก็บไฟล์ | 🟢 สีเขียว: เก็บไฟล์แล้ว | ⚪ ยกเลิก: สิทธิ์ R1/R2
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="activity-steps-table">
                    <thead class="table-light text-uppercase small">
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
                                <td class="text-center fw-bold fs-6"><?= $sNo ?></td>
                                <td>
                                    <div class="fw-bold text-dark mb-1">
                                        <i class="fas <?= $sInfo['icon'] ?> text-primary me-2"></i> <?= Html::encode($sInfo['name']) ?>
                                    </div>
                                    <div class="small text-muted"><?= Html::encode($sInfo['detail']) ?></div>
                                </td>
                                <td>
                                    <?php if ($sNo == 10): ?>
                                        <strong class="<?= $jobProfitPercent >= 20 ? 'text-success' : ($jobProfitPercent > 0 ? 'text-warning' : 'text-danger') ?>">
                                            อัตรากำไร: <?= number_format($jobProfitPercent, 2) ?>% 
                                            (<?= $jobProfitPercent >= 20 ? '🟢 สีเขียว' : ($jobProfitPercent > 0 ? '🟠 สีส้ม' : '🔴 สีแดง') ?>)
                                        </strong>
                                    <?php elseif ($sNo == 11): ?>
                                        <strong>เหลือเวลาทำงาน: <span class="badge bg-secondary"><?= $daysRemaining ?> วัน</span></strong>
                                    <?php elseif ($sNo == 13): ?>
                                        <strong>ระยะทางรวม: <?= number_format($jobKmTotal, 1) ?> กม. (คิดเป็น <?= number_format($jobKmCostAt5, 2) ?> บาท)</strong>
                                    <?php elseif ($sNo == 15): ?>
                                        <strong class="<?= $jobNetProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                            กำไรสุทธิ: <?= number_format($jobNetProfit, 2) ?> บาท
                                        </strong>
                                    <?php else: ?>
                                        <span class="text-muted small">พร้อมแนบ/ดาวน์โหลดไฟล์เอกสารในระบบ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" id="status-badge-container-<?= $sNo ?>">
                                    <?= JobActivityStatus::getStatusLabel($stVal) ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm mb-1" role="group">
                                        <button type="button" class="btn btn-outline-danger btn-change-status" data-step="<?= $sNo ?>" data-status="0" title="ยังไม่ได้ทำ (Red)">
                                            🔴
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-change-status" data-step="<?= $sNo ?>" data-status="1" title="ทำแล้ว รอจัดเก็บไฟล์ (Orange)">
                                            🟠
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-change-status" data-step="<?= $sNo ?>" data-status="2" title="เก็บไฟล์เรียบร้อยแล้ว (Green)">
                                            🟢
                                        </button>
                                    </div>

                                    <?php if ($canCancel): ?>
                                        <div>
                                            <button type="button" class="btn btn-xs btn-outline-secondary btn-cancel-step mt-1" data-step="<?= $sNo ?>" title="ยกเลิกขั้นตอน (สิทธิ์ R1/R2)">
                                                <i class="fas fa-ban text-danger me-1"></i> กดยกเลิก (R1/R2)
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
        <a href="<?= Url::to(['executive-dashboard/index']) ?>" class="btn btn-secondary px-4">
            <i class="fas fa-arrow-left me-1"></i> ย้อนกลับไปหน้า Executive Dashboard
        </a>
    </div>

</div>

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
