<?php
use yii\helpers\Html;
use yii\helpers\Url;
use backend\models\JobActivityStatus;

$this->title = 'สถานะกิจกรรม MCOAutomation: Job ' . Html::encode($job->job_no);
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
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <a href="<?= Url::to(['executive-dashboard/index']) ?>" class="text-indigo-600 text-decoration-none small fw-medium mb-1 d-inline-block" style="color: #4f46e5;">
                <i class="fas fa-arrow-left me-1"></i> ย้อนกลับหน้า Executive Dashboard
            </a>
            <h3 class="fw-bold text-slate-800 mb-0" style="color: #0f172a; font-family: 'Prompt', sans-serif;">
                <i class="fas fa-tasks text-indigo-600 me-2" style="color: #4f46e5;"></i> สถานะกิจกรรม 15 ขั้นตอน
            </h3>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary rounded-pill btn-sm px-3 shadow-sm fw-medium" data-bs-toggle="modal" data-bs-target="#timelineModal" data-toggle="modal" data-target="#timelineModal" style="font-family: 'Prompt', sans-serif; background-color: #4f46e5; border-color: #4f46e5;">
                <i class="fas fa-file-alt me-1"></i> เอกสาร/กิจกรรมที่เกี่ยวข้อง
            </button>
            <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm px-3 shadow-sm fw-medium" onclick="window.print()" style="font-family: 'Prompt', sans-serif;">
                <i class="fas fa-print me-1"></i> พิมพ์ Report
            </button>
            <span class="badge bg-slate-100 text-slate-700 px-3 py-2 rounded-pill fw-medium" style="background-color: #f1f5f9; color: #334155;">
                Job No: <?= Html::encode($job->job_no) ?>
            </span>
        </div>
    </div>
    
    <div class="d-none d-print-block mb-4 text-center">
        <h3 class="fw-bold mb-2" style="font-family: 'Prompt', sans-serif;">รายงานสถานะกิจกรรม 15 ขั้นตอน</h3>
        <h5 class="text-secondary">Job No: <?= Html::encode($job->job_no) ?></h5>
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

            <!-- Financial Summary Grid (6 Columns) -->
            <div class="row g-3 text-center pt-3 border-top" style="border-color: #f1f5f9 !important;">
                <div class="col-md" style="flex: 1 0 0%;">
                    <div class="small text-slate-400" style="color: #94a3b8;">มูลค่างานรวม (Revenue)</div>
                    <div class="fs-5 fw-bold" style="color: #047857; font-family: 'Inter', sans-serif;"><?= number_format($jobRevenue, 2) ?> <small class="fs-6">บาท</small></div>
                </div>
                <div class="col-md" style="flex: 1 0 0%;">
                    <div class="small text-slate-400" style="color: #94a3b8;" title="PO: <?= number_format($jobPoCostWithInterest, 2) ?> | None PR: <?= number_format($jobNonePrCostWithInterest, 2) ?> | Stock: <?= number_format($jobInventoryCostWithInterest, 2) ?>" data-bs-toggle="tooltip">ต้นทุนรวม (ดบ.1%)</div>
                    <div class="fs-5 fw-bold" style="color: #be123c; font-family: 'Inter', sans-serif;"><?= number_format($jobTotalExpenses, 2) ?> <small class="fs-6">บาท</small></div>
                </div>
                <div class="col-md" style="flex: 1 0 0%;">
                    <div class="small text-slate-400" style="color: #94a3b8;">ค่าใช้รถยนต์ (<?= number_format($jobKmTotal, 1) ?> กม. x 5 บ.)</div>
                    <div class="fs-5 fw-bold" style="color: #0369a1; font-family: 'Inter', sans-serif;"><?= number_format($jobVehicleCost > $jobKmCostAt5 ? $jobVehicleCost : $jobKmCostAt5, 2) ?> <small class="fs-6">บาท</small></div>
                </div>
                <div class="col-md" style="flex: 1 0 0%;">
                    <div class="small text-slate-400" style="color: #94a3b8;">ค่าจ้างใช้งานรถยนต์ (Total Wage)</div>
                    <div class="fs-5 fw-bold" style="color: #7e22ce; font-family: 'Inter', sans-serif;"><?= number_format($jobVehicleWage, 2) ?> <small class="fs-6">บาท</small></div>
                </div>
                <div class="col-md" style="flex: 1 0 0%;">
                    <div class="small text-slate-400" style="color: #94a3b8;">กำไร/ขาดทุนก่อนหักภาษี</div>
                    <div class="fs-5 fw-bold" style="color: <?= $jobProfitBeforeTax >= 0 ? '#047857' : '#be123c' ?>; font-family: 'Inter', sans-serif;">
                        <?= number_format($jobProfitBeforeTax, 2) ?> <small class="fs-6">บาท</small>
                    </div>
                </div>
                <div class="col-md" style="flex: 1 0 0%;">
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
                            <th class="text-center" style="width: 8%">ขั้นตอน</th>
                            <th style="width: 25%">ชื่อกิจกรรมในระบบ</th>
                            <th style="width: 37%">สถานะประมวลผล & เอกสาร/ไฟล์แนบในระบบ</th>
                            <th class="text-center" style="width: 15%">สถานะจากระบบ</th>
                            <th class="text-center" style="width: 15%">การยกเลิก</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stepsDef as $sNo => $sInfo): 
                            $statusObj = $activityStatuses[$sNo] ?? null;
                            $stVal = $statusObj ? $statusObj->status : JobActivityStatus::STATUS_RED;
                            $detailText = $stepDetails[$sNo] ?? $sInfo['detail'];
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
                                    <div class="fw-medium text-slate-700 mb-2" style="color: #334155;">
                                        <i class="fas fa-info-circle me-1 text-indigo-500"></i> <?= Html::encode($detailText) ?>
                                    </div>

                                    <!-- Action / File Attachment Buttons per Step -->
                                    <div>
                                        <?php if ($sNo == 1): ?>
                                            <?php 
                                            $poDocsList = \backend\models\JobPoDoc::find()->where(['job_id' => $job->id])->all();
                                            $hasCusPo = !empty($job->cus_po_doc) || !empty($poDocsList);
                                            ?>
                                            <?php if ($hasCusPo): ?>
                                                <?php if (!empty($job->cus_po_doc)): ?>
                                                    <?php 
                                                    $poFiles = explode(',', $job->cus_po_doc);
                                                    foreach ($poFiles as $pf): 
                                                        $cleanPf = trim($pf);
                                                        if (empty($cleanPf)) continue;
                                                    ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($cleanPf) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill me-1 mb-1 shadow-sm">
                                                            <i class="fas fa-file-pdf me-1"></i> ดูไฟล์ PO ลูกค้า (<?= Html::encode($cleanPf) ?>)
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <?php if (!empty($poDocsList)): ?>
                                                    <?php foreach ($poDocsList as $pDoc): ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($pDoc->file_path) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill me-1 mb-1 shadow-sm" title="<?= Html::encode($pDoc->file_name) ?>">
                                                            <i class="fas fa-file-pdf me-1"></i> ดูไฟล์ PO: <?= Html::encode($pDoc->file_name ?: $pDoc->file_path) ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted fw-normal me-2"><i class="fas fa-exclamation-circle me-1"></i> ยังไม่มีไฟล์ PO ลูกค้า</span>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-xs btn-outline-indigo rounded-pill shadow-sm btn-open-timeline-search" data-kw="PO" data-bs-toggle="modal" data-bs-target="#timelineModal" data-toggle="modal" data-target="#timelineModal" style="color: #4f46e5; border-color: #c7d2fe;">
                                                <i class="fas fa-search me-1"></i> ค้นหาเอกสารใบขอซื้อ/PO ในระบบ
                                            </button>

                                        <?php elseif ($sNo == 2): ?>
                                            <button type="button" class="btn btn-xs btn-indigo-modern rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#poSearchModal" data-toggle="modal" data-target="#poSearchModal">
                                                <i class="fas fa-search me-1"></i> ดูรายการ PO / ค้นหาตาม Vendor & สินค้า (<?= count($jobPosDetail ?? []) ?> รายการ)
                                            </button>

                                        <?php elseif ($sNo == 3): ?>
                                            <button type="button" class="btn btn-xs btn-outline-info rounded-pill shadow-sm btn-open-timeline-search" data-kw="สั่งซื้อ" data-bs-toggle="modal" data-bs-target="#timelineModal" data-toggle="modal" data-target="#timelineModal">
                                                <i class="fas fa-search me-1"></i> ค้นหาเอกสารรับของจาก Vendor / Invoice
                                            </button>

                                        <?php elseif ($sNo == 4): ?>
                                            <button type="button" class="btn btn-xs btn-outline-info rounded-pill shadow-sm btn-open-timeline-search" data-kw="เบิก" data-bs-toggle="modal" data-bs-target="#timelineModal" data-toggle="modal" data-target="#timelineModal">
                                                <i class="fas fa-search me-1"></i> ค้นหาเอกสารเบิก/คืนสินค้า (Journal)
                                            </button>

                                        <?php elseif ($sNo == 5): ?>
                                            <?php if (!empty($job->jsa_doc)): ?>
                                                <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($job->jsa_doc) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill me-1 shadow-sm">
                                                    <i class="fas fa-file-pdf me-1"></i> ดูไฟล์ JSA/เซฟตี้ (<?= Html::encode($job->jsa_doc) ?>)
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted fw-normal me-2"><i class="fas fa-exclamation-circle me-1"></i> ยังไม่ได้แนบไฟล์ JSA</span>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill shadow-sm btn-open-timeline-search" data-kw="" data-bs-toggle="modal" data-bs-target="#timelineModal" data-toggle="modal" data-target="#timelineModal">
                                                <i class="fas fa-search me-1"></i> ค้นหาเอกสารทั้งหมด
                                            </button>

                                        <?php elseif ($sNo == 6): ?>
                                            <button type="button" class="btn btn-xs btn-outline-info rounded-pill shadow-sm btn-open-timeline-search" data-kw="" data-bs-toggle="modal" data-bs-target="#timelineModal" data-toggle="modal" data-target="#timelineModal">
                                                <i class="fas fa-search me-1"></i> ค้นหาเอกสาร Engineering / Timeline
                                            </button>

                                        <?php elseif ($sNo == 7): ?>
                                            <?php 
                                            $reportDocsList = \backend\models\JobReportDoc::find()->where(['job_id' => $job->id])->all();
                                            $hasReportDoc = !empty($job->report_doc) || !empty($reportDocsList);
                                            ?>
                                            <?php if ($hasReportDoc): ?>
                                                <?php if (!empty($job->report_doc)): ?>
                                                    <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($job->report_doc) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill me-1 mb-1 shadow-sm">
                                                        <i class="fas fa-file-pdf me-1"></i> ดูไฟล์ Final Report (<?= Html::encode($job->report_doc) ?>)
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($reportDocsList)): ?>
                                                    <?php foreach ($reportDocsList as $rDoc): ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($rDoc->file_path) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill me-1 mb-1 shadow-sm" title="<?= Html::encode($rDoc->file_name) ?>">
                                                            <i class="fas fa-file-pdf me-1"></i> ดูไฟล์ Report: <?= Html::encode($rDoc->file_name ?: $rDoc->file_path) ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted fw-normal me-2"><i class="fas fa-exclamation-circle me-1"></i> ยังไม่ได้แนบไฟล์ Final Report</span>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill shadow-sm btn-open-timeline-search" data-kw="" data-bs-toggle="modal" data-bs-target="#timelineModal" data-toggle="modal" data-target="#timelineModal">
                                                <i class="fas fa-search me-1"></i> ค้นหาเอกสารทั้งหมด
                                            </button>

                                        <?php elseif ($sNo == 9): ?>
                                            <button type="button" class="btn btn-xs btn-outline-success rounded-pill shadow-sm btn-open-timeline-search" data-kw="Invoice" data-bs-toggle="modal" data-bs-target="#timelineModal" data-toggle="modal" data-target="#timelineModal">
                                                <i class="fas fa-search me-1"></i> ค้นหาเอกสาร Invoice & ใบกำกับภาษี
                                            </button>

                                        <?php elseif ($sNo == 12): ?>
                                            <button type="button" class="btn btn-xs btn-outline-success rounded-pill shadow-sm btn-open-timeline-search" data-kw="ชำระ" data-bs-toggle="modal" data-bs-target="#timelineModal" data-toggle="modal" data-target="#timelineModal">
                                                <i class="fas fa-search me-1"></i> ค้นหาเอกสารใบเสร็จ & การชำระเงิน
                                            </button>

                                        <?php elseif ($sNo == 13): ?>
                                            <a href="#vehicle-details-card" class="btn btn-xs btn-outline-primary rounded-pill shadow-sm">
                                                <i class="fas fa-car me-1"></i> ดูรายละเอียดใบบันทึกการใช้รถยนต์
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill shadow-sm btn-open-timeline-search" data-kw="" data-bs-toggle="modal" data-bs-target="#timelineModal" data-toggle="modal" data-target="#timelineModal">
                                                <i class="fas fa-search me-1"></i> ค้นหาเอกสารในระบบ
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center" id="status-badge-container-<?= $sNo ?>">
                                    <?= JobActivityStatus::getStatusLabel($stVal) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($canCancel): ?>
                                        <button type="button" class="btn btn-xs btn-light-modern btn-cancel-step" data-step="<?= $sNo ?>" title="ยกเลิกขั้นตอน" style="color: #e11d48; border-color: #fecdd3;">
                                            <i class="fas fa-ban me-1"></i> กดยกเลิก
                                        </button>
                                    <?php else: ?>
                                        <span class="text-slate-400 small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (!empty($jobVehicleExpList)): ?>
        <!-- Vehicle Usage & Driver Wage Details Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" id="vehicle-details-card" style="background-color: #ffffff; border-radius: 16px;">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-1 text-slate-800" style="color: #1e293b; font-family: 'Prompt', sans-serif;">
                        <i class="fas fa-car text-indigo-600 me-2" style="color: #4f46e5;"></i> รายละเอียดบันทึกการใช้รถยนต์และค่าจ้างประจำ Job นี้
                    </h6>
                    <div class="small text-slate-500" style="color: #64748b;">
                        พบข้อมูลบันทึกการเดินทางทั้งหมด <?= count($jobVehicleExpList) ?> รายการ | รวมระยะทาง <?= number_format($jobKmTotal, 1) ?> กม. | ค่ารถ (ใช้ค่าที่มากกว่า) <?= number_format(max($jobKmCostAt5, $jobVehicleCost), 2) ?> บาท | ค่าจ้างรวม <?= number_format($jobVehicleWage, 2) ?> บาท
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%">#</th>
                                <th class="text-center" style="width: 12%">วันที่ใช้งาน</th>
                                <th class="text-center" style="width: 13%">ทะเบียนรถ</th>
                                <th class="text-end" style="width: 20%">ระยะทาง (กม.)</th>
                                <th class="text-end" style="width: 20%">ค่าใช้จ่ายรถ (บาท)</th>
                                <th class="text-end" style="width: 20%">ค่าจ้างรวม (บาท)</th>
                                <th class="text-center" style="width: 10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $vIdx = 0;
                            foreach ($jobVehicleExpList as $veItem): 
                                $vIdx++;
                                $isNegative = ($veItem->total_distance < 0 || $veItem->vehicle_cost < 0);
                            ?>
                                <tr class="<?= $isNegative ? 'table-warning' : '' ?>">
                                    <td class="text-center text-slate-400"><?= $vIdx ?></td>
                                    <td class="text-center fw-medium"><?= date('d/m/Y', strtotime($veItem->expense_date)) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-slate-100 text-slate-700 px-2 py-1" style="background-color: #f1f5f9; color: #334155;">
                                            <?= Html::encode($veItem->vehicle_no ?: '-') ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold" style="color: #0369a1;">
                                        <?= number_format(abs((float)$veItem->total_distance), 1) ?> กม.
                                        <?php if ($isNegative): ?>
                                            <span class="badge bg-danger text-white ms-1" style="font-size: 0.7rem;" title="รายการนี้คีย์ติดลบในระบบฉบับดั้งเดิม (<?= number_format($veItem->total_distance, 1) ?>)">
                                                <i class="fas fa-exclamation-triangle"></i> ติดลบ
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold" style="color: #047857;">
                                        <?= number_format(abs((float)$veItem->vehicle_cost), 2) ?>
                                    </td>
                                    <td class="text-end fw-bold" style="color: #7e22ce;">
                                        <?= number_format(abs((float)$veItem->total_wage), 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= Url::to(['vehicle-expense/update', 'id' => $veItem->id]) ?>" target="_blank" class="btn btn-xs btn-outline-secondary rounded-pill" title="แก้ไขใบบันทึกการใช้รถใบนี้">
                                            <i class="fas fa-edit me-1"></i> แก้ไข
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-end mb-4">
        <a href="<?= Url::to(['executive-dashboard/index']) ?>" class="btn btn-indigo-modern px-4">
            <i class="fas fa-arrow-left me-1"></i> ย้อนกลับไปหน้า Executive Dashboard
        </a>
    </div>

</div>

<!-- Timeline Modal -->
<div class="modal fade" id="timelineModal" tabindex="-1" aria-labelledby="timelineModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold" id="timelineModalLabel" style="font-family: 'Prompt', sans-serif; color: #1e293b;">
            <i class="fas fa-history text-indigo-600 me-2" style="color: #4f46e5;"></i> กิจกรรมและเอกสารที่เกี่ยวข้อง (Timeline)
        </h5>
        <button type="button" class="btn-close close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true" class="d-none d-sm-inline">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0" id="timeline-modal-body" style="background-color: #f8fafc;">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status" style="color: #4f46e5 !important;">
              <span class="visually-hidden sr-only">Loading...</span>
            </div>
            <div class="mt-2 text-muted fw-medium" style="font-family: 'Prompt', sans-serif;">กำลังโหลดข้อมูล...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Interactive PO & None-PR Search Modal -->
<div class="modal fade" id="poSearchModal" tabindex="-1" aria-labelledby="poSearchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 92%;">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
      <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border-top-left-radius: 20px; border-top-right-radius: 20px;">
        <div>
            <h5 class="modal-title fw-bold mb-1" id="poSearchModalLabel" style="font-family: 'Prompt', sans-serif;">
                <i class="fas fa-file-invoice me-2"></i> รายการ PO และ None-PR ทั้งหมดของ Job No: <?= Html::encode($job->job_no) ?>
            </h5>
            <div class="small text-white-50">ค้นหาตาม 1. Vendor 2. รายละเอียดสินค้า (เช่น Duct+S/S, Trane + ACCU, Insulation) และเปิดไฟล์แนบได้ทันที</div>
        </div>
        <button type="button" class="btn-close btn-close-white close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true" class="d-none d-sm-inline">&times;</span>
        </button>
      </div>

      <div class="modal-body p-4" style="background-color: #f8fafc;">
        
        <!-- Live Search Input & Filter Tags -->
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #ffffff; border-radius: 16px;">
            <div class="card-body p-3">
                <div class="row align-items-center g-3">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3" style="color: #4f46e5;">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="poSearchKeyword" class="form-control form-control-lg border-start-0 rounded-end-pill fs-6" placeholder="พิมพ์ชื่อ Vendor, รายละเอียดสินค้า (เช่น Duct+S/S, Trane + ACCU, Insulation) หรือเลขที่ PO..." style="font-family: 'Prompt', sans-serif;">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span class="small text-muted me-1 fw-medium"><i class="fas fa-tags me-1"></i> ตัวอย่างคำค้นหา:</span>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-quick-search" data-keyword="Duct+S/S">Duct+S/S</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-quick-search" data-keyword="Trane">Trane + ACCU</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-quick-search" data-keyword="Insulation">Insulation</button>
                            <button type="button" class="btn btn-xs btn-outline-danger rounded-pill btn-quick-search" data-keyword="">ล้างค้นหา</button>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                    <div class="small text-secondary">
                        แสดงทั้งหมด <strong class="text-indigo-600" id="showingPoCount"><?= count($jobPosDetail ?? []) ?></strong> รายการ
                    </div>
                    <div class="small text-muted">
                        <i class="fas fa-info-circle me-1"></i> สามารถคลิกปุ่มดูไฟล์แนบ หรือกดเปิดดูเอกสารฉบับเต็มได้
                    </div>
                </div>
            </div>
        </div>

        <!-- PO List Table -->
        <div class="card border-0 shadow-sm rounded-4" style="background: #ffffff; border-radius: 16px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0" id="poSearchTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 4%">#</th>
                                <th style="width: 15%">ประเภท / เลขที่เอกสาร</th>
                                <th class="text-center" style="width: 10%">วันที่</th>
                                <th style="width: 20%">ผู้จำหน่าย (Vendor)</th>
                                <th style="width: 33%">รายการสินค้า / รายละเอียด</th>
                                <th class="text-end" style="width: 10%">มูลค่า (บาท)</th>
                                <th class="text-center" style="width: 8%">ไฟล์แนบ / Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($jobPosDetail)): ?>
                                <?php 
                                $poIdx = 0;
                                foreach ($jobPosDetail as $item): 
                                    $poIdx++;
                                    // Build search index string
                                    $searchTerms = [];
                                    $searchTerms[] = $item['doc_no'];
                                    $searchTerms[] = $item['vendor_name'];
                                    $searchTerms[] = $item['type'];
                                    if (!empty($item['lines'])) {
                                        foreach ($item['lines'] as $l) {
                                            if (!empty($l['product_name'])) $searchTerms[] = $l['product_name'];
                                            if (!empty($l['raw_product_name'])) $searchTerms[] = $l['raw_product_name'];
                                            if (!empty($l['product_description'])) $searchTerms[] = $l['product_description'];
                                            if (!empty($l['raw_product_description'])) $searchTerms[] = $l['raw_product_description'];
                                            if (!empty($l['brand'])) {
                                                $searchTerms[] = $l['brand'];
                                                $searchTerms[] = '(' . $l['brand'] . ')';
                                                $searchTerms[] = '(' . $l['brand'];
                                                $searchTerms[] = $l['brand'] . ')';
                                            }
                                            if (!empty($l['model_name'])) $searchTerms[] = $l['model_name'];
                                            if (!empty($l['product_code'])) $searchTerms[] = $l['product_code'];
                                            if (!empty($l['note'])) $searchTerms[] = $l['note'];
                                            if (!empty($l['stkdes'])) $searchTerms[] = $l['stkdes'];
                                            if (!empty($l['stkcod'])) $searchTerms[] = $l['stkcod'];
                                        }
                                    }
                                    $searchText = mb_strtolower(implode(' ', array_filter($searchTerms)));
                                ?>
                                    <tr class="po-item-row" data-search="<?= Html::encode($searchText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $poIdx ?></td>
                                        <td>
                                            <span class="badge <?= $item['type'] == 'PO' ? 'bg-primary' : 'bg-warning text-dark' ?> me-1">
                                                <?= $item['type'] ?>
                                            </span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($item['doc_no']) ?></strong>
                                        </td>
                                        <td class="text-center small text-secondary">
                                            <?= $item['doc_date'] != '-' ? date('d/m/Y', strtotime($item['doc_date'])) : '-' ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800" style="color: #1e293b;"><?= Html::encode($item['vendor_name']) ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['lines'])): ?>
                                                <ul class="list-unstyled mb-0 small">
                                                    <?php foreach ($item['lines'] as $lineItem): 
                                                        $pName = trim($lineItem['product_name'] ?? $lineItem['stkdes'] ?? 'สินค้า');
                                                        $pDesc = trim($lineItem['product_description'] ?? '');
                                                        $pBrand = trim($lineItem['brand'] ?? '');
                                                        $pModel = trim($lineItem['model_name'] ?? '');
                                                        $showDesc = ($pDesc !== '' && mb_strtolower($pDesc) !== mb_strtolower($pName)) ? $pDesc : '';
                                                    ?>
                                                        <li class="mb-1 pb-1 border-bottom border-light">
                                                            <i class="fas fa-cube text-indigo-500 me-1" style="color: #6366f1;"></i>
                                                            <strong class="text-dark"><?= Html::encode($pName) ?></strong>
                                                            <?php if (!empty($lineItem['note'])): ?>
                                                                <span class="badge bg-light text-secondary ms-1" title="Note"><i class="far fa-sticky-note me-1 text-indigo-500"></i><?= Html::encode($lineItem['note']) ?></span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($showDesc)): ?>
                                                                <span class="text-secondary ms-1">(<?= Html::encode($showDesc) ?>)</span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($pBrand)): ?>
                                                                <span class="text-indigo-600 fw-bold ms-1">(<?= Html::encode($pBrand) ?>)</span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($pModel)): ?>
                                                                <span class="badge bg-slate-100 text-slate-700 ms-1"><?= Html::encode($pModel) ?></span>
                                                            <?php endif; ?>
                                                            <div class="text-muted small ms-3">
                                                                จำนวน: <?= number_format($lineItem['qty'] ?? 0, 1) ?> <?= Html::encode($lineItem['unit'] ?? '') ?>
                                                                | ราคา/หน่วย: <?= number_format($lineItem['line_price'] ?? ($lineItem['unitpr'] ?? 0), 2) ?> บาท
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted small">- ไม่มีรายละเอียดสินค้า -</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            <?= number_format($item['amount'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <?php if (!empty($item['docs'])): ?>
                                                    <?php foreach ($item['docs'] as $dFile): 
                                                        $dName = is_array($dFile) ? ($dFile['doc_name'] ?? '') : $dFile;
                                                        if (empty($dName)) continue;
                                                    ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/purch_doc/<?= Html::encode($dName) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100" title="ดูไฟล์แนบ">
                                                            <i class="fas fa-paperclip me-1"></i> ดูไฟล์แนบ
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <span class="text-muted small">ไม่มีไฟล์แนบ</span>
                                                <?php endif; ?>

                                                <a href="<?= $item['detail_url'] ?>" target="_blank" class="btn btn-xs btn-light-modern rounded-pill w-100 mt-1">
                                                    <i class="fas fa-external-link-alt me-1"></i> เปิดดู PO
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-folder-open fa-2x mb-2 d-block text-slate-300"></i>
                                        ไม่พบข้อมูล PO หรือ None-PR สำหรับ Job นี้
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Custom CSS & Styling -->
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
    padding: 0.4rem 1rem;
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
    font-size: 0.75rem;
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

@media print {
    body { background-color: #fff !important; }
    .job-pipeline-container { padding: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
    .d-print-none, .main-sidebar, .main-header, footer { display: none !important; }
    .content-wrapper { margin-left: 0 !important; padding-top: 0 !important; background-color: #fff !important; }
    .table-custom td, .table-custom th { padding: 0.5rem !important; }
    .badge { border: 1px solid #ccc; color: #000 !important; background-color: #f8f9fa !important; }
}
</style>

<?php
$jobIdVal = $job->id;
$cancelUrl = Url::to(['executive-dashboard/cancel-step']);
$timelineUrl = Url::to(['job/timeline', 'id' => $job->id]);

$js = <<<JS
var activeTimelineKeyword = '';
$(document).on('click', '.btn-open-timeline-search', function() {
    activeTimelineKeyword = $(this).data('kw') || '';
});

$('#timelineModal').on('show.bs.modal shown.bs.modal', function (e) {
    var modalBody = $('#timeline-modal-body');
    var applySearch = function() {
        if (activeTimelineKeyword !== undefined && activeTimelineKeyword !== '') {
            $('#timelineSearchKeyword').val(activeTimelineKeyword).trigger('keyup');
        }
    };
    
    if(modalBody.data('loaded')) {
        applySearch();
        return;
    }
    
    $.ajax({
        url: '{$timelineUrl}',
        type: 'GET',
        success: function(res) {
            modalBody.html(res);
            modalBody.data('loaded', true);
            applySearch();
        },
        error: function() {
            modalBody.html('<div class="alert alert-danger m-4">เกิดข้อผิดพลาดในการโหลดข้อมูล กรุณาลองใหม่อีกครั้ง</div>');
        }
    });
});

$('.btn-cancel-step').on('click', function() {
    var stepNo = $(this).data('step');
    if (confirm('คุณต้องการกดยกเลิกขั้นตอนกิจกรรมนี้ใช่หรือไม่?')) {
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

function escapeRegExp(str) {
    return str.split('').map(function(ch) {
        return ('.*+?^\${}()|[]\\\\'.indexOf(ch) !== -1) ? ('\\\\' + ch) : ch;
    }).join('');
}

function highlightKeywordsInRow(rowEl, keywords) {
    var jRow = $(rowEl);
    // 1. Remove previous highlights
    jRow.find('mark.highlight-kw').each(function() {
        var parent = this.parentNode;
        $(this).replaceWith(this.childNodes);
        if (parent) parent.normalize();
    });

    if (!keywords || keywords.length === 0) return;

    // 2. Prepare clean keywords
    var cleanKeywords = [];
    keywords.forEach(function(k) {
        var clean = k.replace(/^[()]+|[()]+$/g, '').trim();
        if (k.length > 0) cleanKeywords.push(k);
        if (clean.length > 0 && clean !== k) cleanKeywords.push(clean);
    });

    if (cleanKeywords.length === 0) return;

    cleanKeywords = cleanKeywords.filter(function(v, i, a) { return a.indexOf(v) === i; });
    var escapedKw = cleanKeywords.map(function(k) {
        return escapeRegExp(k);
    }).join('|');
    
    if (!escapedKw) return;
    var regex = new RegExp('(' + escapedKw + ')', 'gi');

    // 3. Walk text nodes inside cells
    jRow.find('td').each(function() {
        var jTd = $(this);
        if (jTd.hasClass('text-center') && jTd.find('a').length > 0) return;

        function walkNode(node) {
            if (node.nodeType === 3) {
                var val = node.nodeValue;
                if (val && regex.test(val)) {
                    var span = document.createElement('span');
                    span.innerHTML = val.replace(regex, function(m) {
                        return '<mark class="highlight-kw" style="background-color: #fef08a; color: #854d0e; padding: 1px 4px; border-radius: 4px; font-weight: 700; border: 1px solid #fde047;">' + m + '</mark>';
                    });
                    node.parentNode.replaceChild(span, node);
                }
            } else if (node.nodeType === 1 && node.childNodes && !/^(script|style|mark|a|button)$/i.test(node.tagName)) {
                for (var i = 0; i < node.childNodes.length; i++) {
                    walkNode(node.childNodes[i]);
                }
            }
        }
        walkNode(jTd[0]);
    });
}

// Live PO & Product Search Filter with Yellow Text Highlighting
$('#poSearchKeyword').on('keyup input', function() {
    var rawKw = $(this).val().toLowerCase().trim();
    if (rawKw === '') {
        $('.po-item-row').show().each(function() {
            highlightKeywordsInRow(this, []);
        });
        $('#showingPoCount').text($('.po-item-row').length);
        return;
    }
    
    var keywords = rawKw.split(/[\s+]+/).filter(function(k) { return k.length > 0; });
    var count = 0;

    $('.po-item-row').each(function() {
        var itemRow = this;
        var jRow = $(itemRow);
        var rawSearch = jRow.attr('data-search') || '';
        var searchIndex = rawSearch.toLowerCase()
            .replace(/&quot;/g, '"')
            .replace(/&#039;/g, "'")
            .replace(/&amp;/g, '&');
        
        var matched = keywords.every(function(kw) {
            var cleanKw = kw.replace(/^[()]+|[()]+$/g, '').trim();
            if (searchIndex.indexOf(kw) !== -1) return true;
            if (cleanKw !== '' && searchIndex.indexOf(cleanKw) !== -1) return true;
            return false;
        });

        if (matched) {
            jRow.show();
            highlightKeywordsInRow(itemRow, keywords);
            count++;
        } else {
            jRow.hide();
            highlightKeywordsInRow(itemRow, []);
        }
    });
    $('#showingPoCount').text(count);
});

$('.btn-quick-search').on('click', function() {
    var kw = $(this).data('keyword');
    $('#poSearchKeyword').val(kw).trigger('keyup');
});
JS;
$this->registerJs($js);
?>
