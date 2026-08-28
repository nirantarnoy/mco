<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\models\Job;

/* @var $this yii\web\View */
/* @var $model Job */
/* @var $purchReqs array */
/* @var $purchases array */
/* @var $journalTrans array */
/* @var $invoices array */

// Register CSS
$this->registerCss('
@import url("https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap");

.job-timeline-view {
    font-family: "Prompt", sans-serif;
    color: #334155;
}

.timeline-container {
    position: relative;
    padding-left: 45px;
    background: transparent;
    padding-top: 10px;
    padding-bottom: 30px;
}

.timeline-container::before {
    content: "";
    position: absolute;
    left: 24px;
    top: 15px;
    bottom: 0;
    width: 3px;
    background: #e2e8f0;
    border-radius: 3px;
}

.timeline-section {
    position: relative;
    margin-bottom: 35px;
    display: block;
}

.timeline-section::before {
    content: "";
    position: absolute;
    left: -29px;
    top: 20px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #ffffff;
    border: 4px solid #4f46e5;
    z-index: 10;
    box-shadow: 0 0 0 4px rgba(255,255,255,1);
}

.timeline-section:nth-child(2)::before {
    border-color: #f59e0b;
}

.timeline-section:nth-child(3)::before {
    border-color: #0ea5e9;
}

.timeline-section:nth-child(4)::before {
    border-color: #10b981;
}

.timeline-section:nth-child(5)::before {
    border-color: #8b5cf6;
}
.timeline-section:nth-child(6)::before {
    border-color: #ec4899;
}

.timeline-section .card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    overflow: hidden;
    background: #ffffff;
    border: 1px solid #f1f5f9;
}

.timeline-section .card-header {
    border: none;
    padding: 16px 24px;
    position: relative;
    overflow: hidden;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
}

.timeline-section .card-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 1.1rem;
    color: #1e293b;
}

.timeline-section .card-body {
    padding: 20px 24px;
    background: #ffffff;
}

.timeline-section .table {
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 0;
}

.timeline-section .table thead th {
    background-color: #f8fafc;
    border: none;
    color: #64748b;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 12px 16px;
    text-align: center;
    border-bottom: 1px solid #e2e8f0;
}

.timeline-section .table tbody tr:hover {
    background-color: #f8fafc;
}

.timeline-section .table td {
    border: none;
    border-bottom: 1px solid #f1f5f9;
    padding: 14px 16px;
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
    padding: 6px 12px;
    border-radius: 9999px;
    font-weight: 500;
    border: none;
}

.financial-summary .card {
    border-radius: 16px;
    transition: all 0.3s ease;
    border: 1px solid #f1f5f9;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}

.financial-summary .card-body {
    padding: 24px;
    text-align: center;
}

.financial-summary .card h5 {
    font-size: 0.85rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 10px;
}

.financial-summary .card h3 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: #0f172a;
}

.progress-timeline {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}

.progress-timeline .progress {
    height: 12px;
    border-radius: 6px;
    background-color: #f1f5f9;
    overflow: visible;
}

.progress-timeline .progress-bar {
    border-radius: 6px;
    position: relative;
    background: linear-gradient(90deg, #4f46e5 0%, #0ea5e9 100%);
}

.progress-timeline .progress-bar::after {
    content: "";
    position: absolute;
    right: -6px;
    top: -4px;
    width: 20px;
    height: 20px;
    background: #ffffff;
    border: 4px solid #0ea5e9;
    border-radius: 50%;
    box-shadow: 0 0 10px rgba(14,165,233,0.4);
}

@media (max-width: 768px) {
    .timeline-container {
        padding-left: 25px;
    }
    
    .timeline-container::before {
        left: 12px;
        width: 2px;
    }
    
    .timeline-section::before {
        left: 4px;
        width: 10px;
        height: 10px;
        border-width: 2px;
    }
    
    .financial-summary .card h3 {
        font-size: 1.5rem;
    }
    
    .timeline-section .card-header {
        padding: 15px 20px;
    }
    
    .timeline-section .card-body {
        padding: 20px 15px;
    }
}

@media print {
    .timeline-container {
        background: white !important;
        padding-left: 0 !important;
    }
    
    .timeline-container::before,
    .timeline-section::before {
        display: none !important;
    }
    
    .timeline-section .card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        margin-bottom: 20px;
    }
    
    .btn {
        display: none !important;
    }
}
');

$start = new DateTime($model->start_date);
$end   = new DateTime($model->end_date);

$today = new DateTime("now");
$is_over = 0;
$diff = $start->diff($end);
if($today > $end){
    $is_over = 1;
  $diff = $today->diff($end);
}
?>

    <div class="job-timeline-view">

        <!-- Live Document Search Bar -->
        <div class="p-3 bg-white border border-light rounded-4 mb-4 shadow-sm" style="border-radius: 16px; border-color: #e2e8f0 !important;">
            <div class="row align-items-center g-3">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3" style="color: #4f46e5;">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="timelineSearchKeyword" class="form-control border-start-0 rounded-end-pill fs-6" placeholder="พิมพ์ค้นหาเลขที่เอกสาร, Vendor, ชื่อสินค้า, เลขบิล หรือประเภทเอกสาร..." style="font-family: 'Prompt', sans-serif;">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <span class="small text-muted me-1 fw-medium"><i class="fas fa-tags me-1"></i> ตัวอย่างคำค้นหา:</span>
                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill btn-timeline-tag" data-kw="PO">PO ลูกค้า/สั่งซื้อ</button>
                        <button type="button" class="btn btn-xs btn-outline-info rounded-pill btn-timeline-tag" data-kw="JSA">JSA</button>
                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill btn-timeline-tag" data-kw="Report">Report</button>
                        <button type="button" class="btn btn-xs btn-outline-warning rounded-pill btn-timeline-tag" data-kw="None">None PR</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-timeline-tag" data-kw="เบิก">เบิก-คืนของ</button>
                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill btn-timeline-tag" data-kw="Invoice">Invoice</button>
                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill btn-timeline-tag" data-kw="">ล้างค้นหา</button>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                <div class="small text-secondary">
                    <i class="fas fa-filter me-1 text-indigo-500"></i> แสดงรายการเอกสารที่เกี่ยวข้องของ Job No: <strong class="text-indigo-600"><?= Html::encode($model->job_no) ?></strong>
                </div>
            </div>
        </div>

        <!-- Timeline Container -->
        <div class="timeline-container">

            <!-- Customer PO Section (Step 1) -->
            <?php 
            $poDocsList = isset($jobPoDocs) ? $jobPoDocs : \backend\models\JobPoDoc::find()->where(['job_id' => $model->id])->all();
            $hasCustomerPo = !empty($model->cus_po_doc) || !empty($poDocsList);
            ?>
            <div class="timeline-section">
                <div class="card border-primary shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                            <i class="fas fa-file-invoice me-2"></i>
                            1. เอกสารคำสั่งซื้อลูกค้า (Customer PO)
                            <span class="badge bg-white text-primary ms-2"><?= (!empty($model->cus_po_doc) ? 1 : 0) + count($poDocsList) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <?php if ($hasCustomerPo): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" style="width: 5%">#</th>
                                            <th style="width: 35%">ชื่อไฟล์เอกสาร PO</th>
                                            <th class="text-center" style="width: 20%">วันที่อัปโหลด</th>
                                            <th class="text-end" style="width: 15%">ขนาดไฟล์</th>
                                            <th class="text-center" style="width: 25%">เปิดดูไฟล์แนบ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $cIdx = 0;
                                        if (!empty($model->cus_po_doc)):
                                            $cIdx++;
                                            $cleanPf = trim($model->cus_po_doc);
                                        ?>
                                            <tr>
                                                <td class="text-center fw-bold text-muted"><?= $cIdx ?></td>
                                                <td>
                                                    <i class="far fa-file-pdf text-danger me-2"></i>
                                                    <strong class="text-slate-800"><?= Html::encode($cleanPf) ?></strong>
                                                    <span class="badge bg-info text-white ms-1">ไฟล์ PO หลัก</span>
                                                </td>
                                                <td class="text-center text-muted small">-</td>
                                                <td class="text-end text-muted small">-</td>
                                                <td class="text-center">
                                                    <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($cleanPf) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-3 shadow-sm">
                                                        <i class="fas fa-external-link-alt me-1"></i> เปิดดูไฟล์ PO
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if (!empty($poDocsList)): ?>
                                            <?php foreach ($poDocsList as $pDoc): $cIdx++; ?>
                                                <tr>
                                                    <td class="text-center fw-bold text-muted"><?= $cIdx ?></td>
                                                    <td>
                                                        <i class="far fa-file-pdf text-danger me-2"></i>
                                                        <strong class="text-slate-800"><?= Html::encode($pDoc->file_name ?: $pDoc->file_path) ?></strong>
                                                    </td>
                                                    <td class="text-center text-secondary small">
                                                        <?= $pDoc->uploaded_at ? date('d/m/Y H:i', $pDoc->uploaded_at) : '-' ?>
                                                    </td>
                                                    <td class="text-end text-secondary small">
                                                        <?= $pDoc->file_size ? Yii::$app->formatter->asShortSize($pDoc->file_size) : '-' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($pDoc->file_path) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-3 shadow-sm">
                                                            <i class="fas fa-external-link-alt me-1"></i> เปิดดูไฟล์ PO
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-muted mb-0">
                                <i class="fas fa-exclamation-circle me-1"></i> ยังไม่มีการแนบไฟล์เอกสาร PO ลูกค้าสำหรับ Job นี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- JSA & Safety Document Section (Step 5) -->
            <div class="timeline-section">
                <div class="card border-info shadow-sm">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                            <i class="fas fa-user-shield me-2"></i>
                            5. เอกสารอบรมเซฟตี้ & JSA
                            <span class="badge bg-white text-info ms-2"><?= !empty($model->jsa_doc) ? 1 : 0 ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <?php if (!empty($model->jsa_doc)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" style="width: 5%">#</th>
                                            <th style="width: 60%">ชื่อไฟล์เอกสาร JSA/เซฟตี้</th>
                                            <th class="text-center" style="width: 35%">เปิดดูไฟล์แนบ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center fw-bold text-muted">1</td>
                                            <td>
                                                <i class="far fa-file-pdf text-danger me-2"></i>
                                                <strong class="text-slate-800"><?= Html::encode($model->jsa_doc) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($model->jsa_doc) ?>" target="_blank" class="btn btn-xs btn-outline-info rounded-pill px-3 shadow-sm">
                                                    <i class="fas fa-external-link-alt me-1"></i> เปิดดูไฟล์ JSA/เซฟตี้
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-muted mb-0">
                                <i class="fas fa-exclamation-circle me-1"></i> ยังไม่มีการแนบไฟล์เอกสาร JSA/เซฟตี้สำหรับ Job นี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Final Report & Certificate Section (Step 7) -->
            <?php 
            $reportDocsList = isset($jobReportDocs) ? $jobReportDocs : \backend\models\JobReportDoc::find()->where(['job_id' => $model->id])->all();
            $hasReportDoc = !empty($model->report_doc) || !empty($reportDocsList);
            ?>
            <div class="timeline-section">
                <div class="card border-success shadow-sm">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                            <i class="fas fa-certificate me-2"></i>
                            7. เอกสาร Final Report / Certificate
                            <span class="badge bg-white text-success ms-2"><?= (!empty($model->report_doc) ? 1 : 0) + count($reportDocsList) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <?php if ($hasReportDoc): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" style="width: 5%">#</th>
                                            <th style="width: 25%">โฟลเดอร์</th>
                                            <th style="width: 35%">ชื่อไฟล์เอกสาร Report</th>
                                            <th class="text-center" style="width: 15%">วันที่อัปโหลด</th>
                                            <th class="text-center" style="width: 20%">เปิดดูไฟล์แนบ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rIdx = 0;
                                        if (!empty($model->report_doc)):
                                            $rIdx++;
                                        ?>
                                            <tr>
                                                <td class="text-center fw-bold text-muted"><?= $rIdx ?></td>
                                                <td><span class="badge bg-secondary">ทั่วไป</span></td>
                                                <td>
                                                    <i class="far fa-file-pdf text-danger me-2"></i>
                                                    <strong class="text-slate-800"><?= Html::encode($model->report_doc) ?></strong>
                                                    <span class="badge bg-success text-white ms-1">ไฟล์หลัก</span>
                                                </td>
                                                <td class="text-center text-muted small">-</td>
                                                <td class="text-center">
                                                    <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($model->report_doc) ?>" target="_blank" class="btn btn-xs btn-outline-success rounded-pill px-3 shadow-sm">
                                                        <i class="fas fa-external-link-alt me-1"></i> เปิดดูไฟล์ Report
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if (!empty($reportDocsList)): ?>
                                            <?php foreach ($reportDocsList as $rDoc): $rIdx++; ?>
                                                <tr>
                                                    <td class="text-center fw-bold text-muted"><?= $rIdx ?></td>
                                                    <td><span class="badge bg-info"><?= Html::encode($rDoc->folder_name ?: 'ทั่วไป') ?></span></td>
                                                    <td>
                                                        <i class="far fa-file-pdf text-danger me-2"></i>
                                                        <strong class="text-slate-800"><?= Html::encode($rDoc->file_name ?: $rDoc->file_path) ?></strong>
                                                    </td>
                                                    <td class="text-center text-secondary small">
                                                        <?= $rDoc->uploaded_at ? date('d/m/Y H:i', $rDoc->uploaded_at) : '-' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($rDoc->file_path) ?>" target="_blank" class="btn btn-xs btn-outline-success rounded-pill px-3 shadow-sm">
                                                            <i class="fas fa-external-link-alt me-1"></i> เปิดดูไฟล์ Report
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-muted mb-0">
                                <i class="fas fa-exclamation-circle me-1"></i> ยังไม่มีการแนบไฟล์เอกสาร Final Report สำหรับ Job นี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Purchase Request Section -->
            <div class="timeline-section">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt"></i>
                            ใบขอซื้อ (Purchase Request)
                            <span class="badge badge-light text-dark ml-2"><?= count($purchReqs) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($purchReqs)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขใบขอซื้อ</th>
                                        <th>วันที่</th>
                                        <th>ผู้ขอ</th>
                                        <th>สถานะ</th>
                                        <th style="text-align: right;">มูลค่า</th>
                                        <th>หมายเหตุ</th>
                                        <th>เอกสาร</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $totalPurchReq = 0;
                                    foreach ($purchReqs as $req):
                                        $totalPurchReq += $req['total_amount'];
                                        ?>
                                        <?php
                                        $line_status = '';
                                        if ($req['approve_status'] == 0) {
                                            $line_status = 'รอพิจารณา';
                                        } else if ($req['approve_status'] == 1) {
                                            $line_status = 'อนุมัติ';
                                        } else if ($req['approve_status'] == 2) {
                                            $line_status = 'ไม่อนุมัติ';
                                        } else if ($req['approve_status'] == 3) {
                                            $line_status = 'ยกเลิก';
                                        }
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($req['purch_req_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($req['purch_req_date'])) ?></td>
                                            <td style="text-align: center;"><?= Html::encode($req['fname'] . ' ' . $req['lname']) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $line_status, [
                                                    'class' => 'badge badge-' . ($line_status == 'อนุมัติ' ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                            <td class="text-right"><?= number_format($req['total_amount'], 2) ?></td>
                                            <td><?= Html::encode($req['note']) ?></td>
                                            <td style="text-align: center;"><a class="badge badge-info" href="<?=Url::to(['job/documents','id'=>$model->id,'type'=>'purch_req','activityId'=>$req['id']],true)?>"><i class="fa fa-eye"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-right font-weight-bold">รวมใบขอซื้อ:</td>
                                        <td class="text-right font-weight-bold text-primary"><?= number_format($totalPurchReq, 2) ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลใบขอซื้อสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Purchase Order Section -->
            <div class="timeline-section">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart"></i>
                            ใบสั่งซื้อ (Purchase Order)
                            <span class="badge badge-dark ml-2"><?= count($purchases) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($purchases)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขใบสั่งซื้อ</th>
                                        <th>วันที่</th>
                                        <th>ผู้จำหน่าย</th>
                                        <th>สถานะ</th>
                                        <th style="text-align: right;">มูลค่า</th>
                                        <th style="text-align: right;">ส่วนลด</th>
                                        <th style="text-align: right;">VAT</th>
                                        <th style="text-align: right;">สุทธิ</th>
                                        <th>เอกสาร</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $totalPoAmount = 0;
                                    $totalPoDiscount = 0;
                                    $totalPoVat = 0;
                                    $totalPoNet = 0;
                                    foreach ($purchases as $purchase):
                                        $totalPoAmount += $purchase['total_amount'];
                                        $totalPoDiscount += $purchase['discount_amount'];
                                        $totalPoVat += $purchase['vat_amount'];
                                        $totalPoNet += $purchase['net_amount'];
                                        ?>
                                        <?php
                                        $line_status = '';
                                        if ($purchase['approve_status'] == 0) {
                                            $line_status = 'รอพิจารณา';
                                        } else if ($purchase['approve_status'] == 1) {
                                            $line_status = 'อนุมัติ';
                                        } else if ($purchase['approve_status'] == 2) {
                                            $line_status = 'ไม่อนุมัติ';
                                        } else if ($purchase['approve_status'] == 3) {
                                            $line_status = 'ยกเลิก';
                                        }
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($purchase['purch_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($purchase['purch_date'])) ?></td>
                                            <td style="text-align: center;"><?= Html::encode($purchase['vendor_name']) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $line_status, [
                                                    'class' => 'badge badge-' . ($line_status == 'อนุมัติ' ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                            <td class="text-right"><?= number_format($purchase['total_amount'], 2) ?></td>
                                            <td class="text-right"><?= number_format($purchase['discount_amount'], 2) ?></td>
                                            <td class="text-right"><?= number_format($purchase['vat_amount'], 2) ?></td>
                                            <td class="text-right font-weight-bold"><?= number_format($purchase['net_amount'], 2) ?></td>
                                            <td style="text-align: center;"><a class="badge badge-info" href="<?=Url::to(['job/documents','id'=>$model->id,'type'=>'purch','activityId'=>$purchase['id']],true)?>"><i class="fa fa-eye"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-right font-weight-bold">รวมใบสั่งซื้อ:</td>
                                        <td class="text-right font-weight-bold"><?= number_format($totalPoAmount, 2) ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($totalPoDiscount, 2) ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($totalPoVat, 2) ?></td>
                                        <td class="text-right font-weight-bold text-danger"><?= number_format($totalPoNet, 2) ?></td>
                                        <td></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                ไม่มีข้อมูลใบสั่งซื้อสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Purchase Order None PR Section -->
            <div class="timeline-section">
                <div class="card border-warning-light">
                    <div class="card-header bg-warning-light text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart"></i>
                            ใบสั่งซื้อ (Purchase None PR)
                            <span class="badge badge-dark ml-2"><?= count($purchasesnonepr) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($purchasesnonepr)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขใบสั่งซื้อ</th>
                                        <th>วันที่</th>
                                        <th>ผู้จำหน่าย</th>
                                        <th style="text-align: right;">มูลค่า</th>
                                        <th>เอกสาร</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $totalPoNonePr = 0;
                                    foreach ($purchasesnonepr as $purchase):
                                        $totalPoNonePr += $purchase['total_amount'];
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($purchase['purch_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($purchase['purch_date'])) ?></td>
                                            <td style="text-align: center;"><?= Html::encode($purchase['vendor_name']) ?></td>
                                            <td class="text-right"><?= number_format($purchase['total_amount'], 2) ?></td>
                                            <td style="text-align: center;"><a class="badge badge-info" href="<?=Url::to(['job/documents','id'=>$model->id,'type'=>'purch','activityId'=>$purchase['id']],true)?>"><i class="fa fa-eye"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="3" class="text-right font-weight-bold">รวมใบสั่งซื้อ (None PR):</td>
                                        <td class="text-right font-weight-bold text-danger"><?= number_format($totalPoNonePr, 2) ?></td>
                                        <td></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                ไม่มีข้อมูลใบสั่งซื้อสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Journal Transaction Section -->
            <div class="timeline-section">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-warehouse"></i>
                            รายการรับ-เบิกของ (Journal Transactions)
                            <span class="badge badge-light text-dark ml-2"><?= count($journalTrans) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($journalTrans)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขเอกสาร</th>
                                        <th>วันที่</th>
                                        <th>ประเภท</th>
                                        <th>ลูกค้า</th>
                                        <th style="text-align: right;">จำนวน</th>
                                        <th style="text-align: center;">สถานะ</th>
                                        <th>หมายเหตุ</th>
                                        <th>เอกสาร</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $totalJournalQty = 0;
                                    foreach ($journalTrans as $trans):
                                        $totalJournalQty += $trans['qty'];
                                        ?>
                                        <?php
                                           $line_type_name = '';
                                           if($trans['trans_type_id']==3){
                                               $line_type_name ='เบิกสินค้า';
                                           }else if($trans['trans_type_id']==4){
                                               $line_type_name ='คืนสินค้า';
                                           }else if($trans['trans_type_id']==5){
                                               $line_type_name ='ยืมสินค้า';
                                           }else if($trans['trans_type_id']==6){
                                               $line_type_name ='คืนยืมสินค้า';
                                           }
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($trans['journal_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($trans['trans_date'])) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $line_type_name, [
                                                    'class' => 'badge badge-' . (in_array((int)$trans['trans_type_id'],[4,6] )  ? 'success' : 'danger')
                                                ]) ?>
                                            </td>
                                            <td style="text-align: left;"><?= Html::encode($trans['customer_name']) ?></td>
                                            <td style="text-align: right;"><?= number_format($trans['qty'], 0) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', 'completed', [
                                                    'class' => 'badge badge-' . ($trans['status'] == 0 ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                            <td><?= Html::encode($trans['remark']) ?></td>
                                            <td style="text-align: center;"><a class="badge badge-info" href="<?=Url::to(['job/documents','id'=>$model->id,'type'=>'journal_trans','activityId'=>$trans['id']],true)?>"><i class="fa fa-eye"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-right font-weight-bold">รวมจำนวนทั้งหมด:</td>
                                        <td style="text-align: right;" class="font-weight-bold text-primary"><?= number_format($totalJournalQty, 0) ?></td>
                                        <td colspan="3"></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลรายการรับ-เบิกของสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Petty Cash Voucher Section -->
            <div class="timeline-section">
                <div class="card border-dark">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cash-register"></i>
                            ใบเบิกเงินสดย่อย (Petty Cash Voucher)
                            <span class="badge badge-light text-dark ml-2"><?= count($pettyCashVouchers) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($pettyCashVouchers)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="text-align: center;">เลขที่ใบเบิก</th>
                                        <th style="text-align: center;">วันที่</th>
                                        <th style="text-align: center;">ผู้เบิก</th>
                                        <th style="text-align: center;">เบิกให้กับ</th>
                                        <th style="text-align: center;">วัตถุประสงค์</th>
                                        <th style="text-align: right;">จำนวนเงิน</th>
                                        <th style="text-align: center;">สถานะ</th>
                                        <th style="text-align: center;">ผู้อนุมัติ</th>
                                        <th style="text-align: center;">เอกสาร</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $totalPettyCash = 0;
                                    foreach ($pettyCashVouchers as $voucher):
                                        $totalPettyCash += $voucher['amount'];

                                        $approve_status_text = '';
                                        $approve_status_color = '';
                                        if ($voucher['approve_status'] == 0) {
                                            $approve_status_text = 'รอพิจารณา';
                                            $approve_status_color = 'warning';
                                        } else if ($voucher['approve_status'] == 1) {
                                            $approve_status_text = 'อนุมัติ';
                                            $approve_status_color = 'success';
                                        } else if ($voucher['approve_status'] == 2) {
                                            $approve_status_text = 'ไม่อนุมัติ';
                                            $approve_status_color = 'danger';
                                        } else if ($voucher['approve_status'] == 3) {
                                            $approve_status_text = 'ยกเลิก';
                                            $approve_status_color = 'secondary';
                                        }

                                        // กำหนดชื่อผู้รับเงิน
                                        $recipient_name = '';
                                        if (!empty($voucher['employee_name'])) {
                                            $recipient_name = $voucher['employee_name'] . ' (พนักงาน)';
                                        } else if (!empty($voucher['customer_name'])) {
                                            $recipient_name = $voucher['customer_name'] . ' (ลูกค้า)';
                                        } else if (!empty($voucher['vendor_name'])) {
                                            $recipient_name = $voucher['vendor_name'] . ' (ผู้จำหน่าย)';
                                        } else {
                                            $recipient_name = Html::encode($voucher['name']);
                                        }
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($voucher['pcv_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($voucher['pcv_date'])) ?></td>
                                            <td style="text-align: center;"><?= Html::encode($voucher['issued_by']) ?></td>
                                            <td style="text-align: center;"><?= $recipient_name ?></td>
                                            <td><?= Html::encode($voucher['paid_for']) ?></td>
                                            <td style="text-align: right;" class="font-weight-bold"><?= number_format($voucher['amount'], 2) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $approve_status_text, [
                                                    'class' => 'badge badge-' . $approve_status_color
                                                ]) ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <?php if (!empty($voucher['approved_by'])): ?>
                                                    <?= Html::encode($voucher['approved_by']) ?><br>
                                                    <small class="text-muted"><?= date('d/m/Y', strtotime($voucher['approved_date'])) ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <a class="badge badge-info" href="<?=Url::to(['job/documents','id'=>$model->id,'type'=>'petty_cash_voucher','activityId'=>$voucher['id']],true)?>">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <?php if (!empty($voucher['details'])): ?>
                                        <tr class="bg-light">
                                            <td colspan="9" style="padding-left: 50px;">
                                                <small>
                                                    <strong>รายละเอียด:</strong>
                                                    <?php foreach ($voucher['details'] as $detail): ?>
                                                        <br>• <?= Html::encode($detail['detail']) ?>
                                                        (<?= number_format($detail['amount'], 2) ?> บาท)
                                                    <?php endforeach; ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bold">รวมเงินสดย่อยทั้งหมด:</td>
                                        <td class="text-right font-weight-bold text-danger"><?= number_format($totalPettyCash, 2) ?></td>
                                        <td colspan="3"></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลใบเบิกเงินสดย่อยสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Vehicle Expense Section -->
            <div class="timeline-section">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-truck"></i>
                            ค่าใช้จ่ายรถ
                            <span class="badge badge-light text-dark ml-2"><?= count($vehicleExpense) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($vehicleExpense)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="text-align: center;">ทะเบียนรถ</th>
                                        <th style="text-align: center;">วันที่</th>
                                        <th style="text-align: right;">ระยะทาง</th>
                                        <th style="text-align: right;">ค่าใช้จ่ายรถ</th>
                                        <th style="text-align: right;">จำนวนคน</th>
                                        <th style="text-align: right;">ค่าจ้างรวม</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $totalVehicleCost = 0;
                                    $totalVehicleWage = 0;
                                    foreach ($vehicleExpense as $expanse):
                                        $totalVehicleCost += $expanse['vehicle_cost'];
                                        $totalVehicleWage += $expanse['total_wage'];
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($expanse['vehicle_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($expanse['expense_date'])) ?></td>
                                            <td style="text-align: right;"><?= number_format($expanse['total_distance'],0) ?></td>
                                            <td style="text-align: right;"><?= number_format($expanse['vehicle_cost'], 0) ?></td>
                                            <td style="text-align: right;"><?= number_format($expanse['passenger_count'], 0) ?></td>
                                            <td style="text-align: right;"><?= number_format($expanse['total_wage'], 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="3" class="text-right font-weight-bold">รวมค่าใช้จ่ายรถ:</td>
                                        <td style="text-align: right;" class="font-weight-bold text-danger"><?= number_format($totalVehicleCost, 2) ?></td>
                                        <td class="text-right font-weight-bold">รวมค่าจ้าง:</td>
                                        <td style="text-align: right;" class="font-weight-bold text-danger"><?= number_format($totalVehicleWage, 2) ?></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลรายการค่าใช้จ่ายรถสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Invoice Section -->
            <div class="timeline-section">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-invoice-dollar"></i>
                            ใบกำกับภาษี/ใบเสร็จ (Invoices)
                            <span class="badge badge-light text-dark ml-2"><?= count($invoices) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($invoices)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขใบกำกับ</th>
                                        <th>ประเภท</th>
                                        <th>วันที่</th>
                                        <th>ลูกค้า</th>
                                        <th>รหัสลูกค้า</th>
                                        <th style="text-align: right;">ยอดก่อนภาษี</th>
                                        <th style="text-align: right;">ส่วนลด</th>
                                        <th style="text-align: right;">VAT</th>
                                        <th style="text-align: right;">ยอดสุทธิ</th>
                                        <th style="text-align: center;">สถานะ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $totalInvSubtotal = 0;
                                    $totalInvDiscount = 0;
                                    $totalInvVat = 0;
                                    $totalInvAmount = 0;
                                    foreach ($invoices as $invoice):
                                        $totalInvSubtotal += $invoice['subtotal'];
                                        $totalInvDiscount += $invoice['discount_amount'];
                                        $totalInvVat += $invoice['vat_amount'];
                                        $totalInvAmount += $invoice['total_amount'];
                                        ?>
                                        <tr>
                                            <td><?= Html::encode($invoice['invoice_number']) ?></td>
                                            <td>
                                                <?= Html::tag('span', $invoice['invoice_type'], [
                                                    'class' => 'badge badge-' . ($invoice['invoice_type'] == 'TAX' ? 'primary' : 'info')
                                                ]) ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></td>
                                            <td><?= Html::encode($invoice['customer_name']) ?></td>
                                            <td><?= Html::encode($invoice['customer_code']) ?></td>
                                            <td class="text-right"><?= number_format($invoice['subtotal'], 2) ?></td>
                                            <td class="text-right"><?= number_format($invoice['discount_amount'], 2) ?></td>
                                            <td class="text-right"><?= number_format($invoice['vat_amount'], 2) ?></td>
                                            <td class="text-right font-weight-bold"><?= number_format($invoice['total_amount'], 2) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', 'completed', [
                                                    'class' => 'badge badge-' . ($invoice['status'] == 1 ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bold">รวมใบกำกับภาษี:</td>
                                        <td class="text-right font-weight-bold"><?= number_format($totalInvSubtotal, 2) ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($totalInvDiscount, 2) ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($totalInvVat, 2) ?></td>
                                        <td class="text-right font-weight-bold text-success"><?= number_format($totalInvAmount, 2) ?></td>
                                        <td></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลใบกำกับภาษี/ใบเสร็จสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Billing Invoice Placement Section -->
            <div class="timeline-section">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-invoice-dollar"></i>
                            ใบวางบิล (Bill Placement)
                            <span class="badge badge-light text-dark ml-2"><?= count($billingInvoices) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($billingInvoices)): ?>
                            <?php
                            $grandTotalBilling = 0;
                            foreach ($billingInvoices as $billing):
                                $grandTotalBilling += $billing['total_amount'];
                                ?>
                                <div class="billing-group mb-4">
                                    <!-- หัวข้อใบวางบิล -->
                                    <div class="billing-header bg-light p-3 rounded mb-2">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>เลขใบวางบิล:</strong>
                                                <span class="text-primary"><?= Html::encode($billing['billing_number']) ?></span>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>วันที่:</strong>
                                                <?= date('d/m/Y', strtotime($billing['billing_date'])) ?>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>ลูกค้า:</strong>
                                                <?= Html::encode($billing['customer_name']) ?>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>ยอดรวม:</strong>
                                                <span class="text-success font-weight-bold"><?= number_format($billing['total_amount'], 2) ?></span>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>สถานะ:</strong>
                                                <?= Html::tag('span', $billing['status'], [
                                                    'class' => 'badge badge-' . ($billing['status'] == 'issued' ? 'success' : 'warning')
                                                ]) ?>
                                            </div>
                                        </div>

                                        <!-- ข้อมูลเพิ่มเติม -->
                                        <div class="row mt-2">
                                            <div class="col-md-3">
                                                <small class="text-muted">ยอดก่อนภาษี: <?= number_format($billing['subtotal'], 2) ?></small>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">ส่วนลด: <?= number_format($billing['discount_amount'], 2) ?> (<?= $billing['discount_percent'] ?>%)</small>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">VAT: <?= number_format($billing['vat_amount'], 2) ?> (<?= $billing['vat_percent'] ?>%)</small>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">กำหนดชำระ: <?= $billing['payment_due_date'] ? date('d/m/Y', strtotime($billing['payment_due_date'])) : '-' ?></small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- รายการ Invoice ในใบวางบิล -->
                                    <div class="table-responsive pl-4">
                                        <table class="table table-sm table-hover border-left">
                                            <thead class="thead-light">
                                            <tr>
                                                <th>เลขใบกำกับ</th>
                                                <th>วันที่</th>
                                                <th style="text-align: right;">ยอดก่อนภาษี</th>
                                                <th style="text-align: right;">VAT</th>
                                                <th style="text-align: right;">ยอดสุทธิ</th>
                                                <th style="text-align: right;">ยอดค้างชำระ</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($billing['items'] as $item): ?>
                                                <tr>
                                                    <td><?= Html::encode($item['invoice_number']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($item['invoice_date'])) ?></td>
                                                    <td class="text-right"><?= number_format($item['subtotal'], 2) ?></td>
                                                    <td class="text-right"><?= number_format($item['vat_amount'], 2) ?></td>
                                                    <td class="text-right"><?= number_format($item['total_amount'], 2) ?></td>
                                                    <td class="text-right text-danger"><?= number_format($item['remaining_balance'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="alert alert-success mt-3 text-right">
                                <h5 class="mb-0">ยอดรวมใบวางบิลทั้งหมด: <span class="text-bold text-success"><?= number_format($grandTotalBilling, 2) ?></span> บาท</h5>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลใบวางบิลสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Payment Receipt Section -->
            <div class="timeline-section">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-receipt"></i>
                            ใบเสร็จรับเงิน (Payment Receipts)
                            <span class="badge badge-light text-dark ml-2"><?= count($paymentReceipts) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($paymentReceipts)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขที่ใบเสร็จ</th>
                                        <th>วันที่รับชำระ</th>
                                        <th>ลูกค้า</th>
                                        <th>วิธีชำระ</th>
                                        <th style="text-align: right;">ยอดรับจริง</th>
                                        <th style="text-align: right;">VAT</th>
                                        <th style="text-align: right;">WHT</th>
                                        <th style="text-align: right;">ยอดสุทธิ</th>
                                        <th style="text-align: center;">สถานะ</th>
                                        <th>ผู้รับเงิน</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $totalRecReceived = 0;
                                    $totalRecVat = 0;
                                    $totalRecWht = 0;
                                    $totalRecNet = 0;
                                    foreach ($paymentReceipts as $receipt):
                                        $totalRecReceived += $receipt['received_amount'];
                                        $totalRecVat += $receipt['vat_amount'];
                                        $totalRecWht += $receipt['withholding_tax'];
                                        $totalRecNet += $receipt['net_amount'];
                                        ?>
                                        <tr>
                                            <td class="font-weight-bold"><?= Html::encode($receipt['receipt_number']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($receipt['payment_date'])) ?></td>
                                            <td><?= Html::encode($receipt['customer_name']) ?></td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?= Html::encode($receipt['payment_method']) ?>
                                                </span>
                                                <?php if ($receipt['payment_method'] == 'Cheque'): ?>
                                                    <br><small>No: <?= Html::encode($receipt['cheque_number']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right"><?= number_format($receipt['received_amount'], 2) ?></td>
                                            <td class="text-right"><?= number_format($receipt['vat_amount'], 2) ?></td>
                                            <td class="text-right text-danger"><?= number_format($receipt['withholding_tax'], 2) ?></td>
                                            <td class="text-right font-weight-bold text-success"><?= number_format($receipt['net_amount'], 2) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $receipt['payment_status'], [
                                                    'class' => 'badge badge-' . ($receipt['payment_status'] == 'completed' ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                            <td><?= Html::encode($receipt['receiver_name']) ?></td>
                                        </tr>
                                        <?php if (!empty($receipt['details'])): ?>
                                            <tr class="bg-light">
                                                <td colspan="10" style="padding-left: 40px;">
                                                    <small>
                                                        <strong>รายการที่ชำระ:</strong>
                                                        <?php foreach ($receipt['details'] as $detail): ?>
                                                            <span class="badge badge-outline-secondary ml-2">
                                                                Invoice: <?= Html::encode($detail['invoice_number']) ?>
                                                                (<?= number_format($detail['amount'], 2) ?>)
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-right font-weight-bold">รวมใบเสร็จรับเงิน:</td>
                                        <td class="text-right font-weight-bold"><?= number_format($totalRecReceived, 2) ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format($totalRecVat, 2) ?></td>
                                        <td class="text-right font-weight-bold text-danger"><?= number_format($totalRecWht, 2) ?></td>
                                        <td class="text-right font-weight-bold text-success"><?= number_format($totalRecNet, 2) ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลใบเสร็จรับเงินสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Job Expense Section -->
            <div class="timeline-section">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-coins"></i>
                            ค่าใช้จ่ายอื่นๆ (Job Expenses)
                            <span class="badge badge-light text-dark ml-2"><?= count($jobExpenses) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($jobExpenses)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>วันที่</th>
                                        <th>รายการ</th>
                                        <th style="text-align: right;">จำนวนเงิน</th>
                                        <th>หมายเหตุ</th>
                                        <th>ผู้บันทึก</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $totalOtherExpense = 0;
                                    foreach ($jobExpenses as $expense):
                                        $totalOtherExpense += $expense->amount;
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($expense->expense_date)) ?></td>
                                            <td><?= Html::encode($expense->description) ?></td>
                                            <td style="text-align: right;" class="font-weight-bold"><?= number_format($expense->amount, 2) ?></td>
                                            <td><?= Html::encode($expense->remark) ?></td>
                                            <td style="text-align: center;"><?= $expense->createdBy ? Html::encode($expense->createdBy->username) : '-' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="2" class="text-right font-weight-bold">รวมค่าใช้จ่ายอื่นๆ:</td>
                                        <td class="text-right font-weight-bold text-danger"><?= number_format($totalOtherExpense, 2) ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลค่าใช้จ่ายอื่นๆ สำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div> <!-- End Timeline Container -->

    </div> <!-- End Job Timeline View -->

<script>
$(document).off('keyup input', '#timelineSearchKeyword').on('keyup input', '#timelineSearchKeyword', function() {
    var kw = $(this).val().toLowerCase().trim();
    $('.timeline-section').each(function() {
        var section = $(this);
        var sectionTitle = section.find('.card-header').text().toLowerCase();
        var hasMatch = false;
        
        if (kw !== '' && sectionTitle.indexOf(kw) !== -1) {
            hasMatch = true;
            section.find('tbody tr').show();
        } else {
            section.find('tbody tr').each(function() {
                var rowText = $(this).text().toLowerCase();
                if (kw === '' || rowText.indexOf(kw) !== -1) {
                    $(this).show();
                    hasMatch = true;
                } else {
                    $(this).hide();
                }
            });
        }

        if (kw === '' || hasMatch) {
            section.show();
        } else {
            section.hide();
        }
    });
});

$(document).off('click', '.btn-timeline-tag').on('click', '.btn-timeline-tag', function() {
    var kw = $(this).data('kw');
    $('#timelineSearchKeyword').val(kw).trigger('keyup');
});
</script>