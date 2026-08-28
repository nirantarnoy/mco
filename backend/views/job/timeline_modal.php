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
/* @var $billingInvoices array */
/* @var $pettyCashVouchers array */
/* @var $paymentReceipts array */
/* @var $vehicleExpense array */
/* @var $purchasesnonepr array */
/* @var $jobExpenses array */
/* @var $jobPoDocs array */
/* @var $jobReportDocs array */

// Register Modern Custom Styling
$this->registerCss('
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap");

.job-timeline-view {
    font-family: "Prompt", sans-serif;
    color: #1e293b;
}

.table-custom {
    border-collapse: separate;
    border-spacing: 0;
}

.table-custom thead th {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-custom tbody tr {
    transition: all 0.2s ease;
}

.table-custom tbody tr:hover {
    background-color: #f1f5f9 !important;
}

.table-custom tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: 0.9rem;
}

.timeline-section-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    background: #ffffff;
    margin-bottom: 24px;
    overflow: hidden;
}

.timeline-section-card .card-header {
    padding: 16px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.btn-indigo-modern {
    background-color: #4f46e5;
    color: #ffffff;
    border: none;
}
.btn-indigo-modern:hover {
    background-color: #4338ca;
    color: #ffffff;
}

.btn-light-modern {
    background-color: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
}
.btn-light-modern:hover {
    background-color: #e2e8f0;
    color: #0f172a;
}
');
?>

<div class="job-timeline-view">

    <!-- Live Document Search Bar Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0;">
        <div class="card-body p-3">
            <div class="row align-items-center g-3">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3" style="color: #4f46e5;">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="timelineSearchKeyword" class="form-control form-control-lg border-start-0 rounded-end-pill fs-6" placeholder="พิมพ์ชื่อ Vendor, รายละเอียดสินค้า, เลขที่เอกสาร (PO, PR, บิล, Invoice, ใบเสร็จ)..." style="font-family: 'Prompt', sans-serif;">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <span class="small text-muted me-1 fw-medium"><i class="fas fa-tags me-1"></i> ตัวอย่างคำค้นหา:</span>
                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill btn-timeline-tag" data-kw="PO">PO ลูกค้า/สั่งซื้อ</button>
                        <button type="button" class="btn btn-xs btn-outline-info rounded-pill btn-timeline-tag" data-kw="PR">ใบขอซื้อ (PR)</button>
                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill btn-timeline-tag" data-kw="JSA">JSA</button>
                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill btn-timeline-tag" data-kw="Report">Report</button>
                        <button type="button" class="btn btn-xs btn-outline-warning rounded-pill btn-timeline-tag" data-kw="None">None PR</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill btn-timeline-tag" data-kw="เบิก">เบิก-คืนของ</button>
                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill btn-timeline-tag" data-kw="Invoice">Invoice</button>
                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill btn-timeline-tag" data-kw="ใบเสร็จ">ใบเสร็จ/ชำระเงิน</button>
                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill btn-timeline-tag" data-kw="">ล้างค้นหา</button>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                <div class="small text-secondary">
                    <i class="fas fa-filter me-1 text-indigo-500" style="color: #4f46e5;"></i> รายการเอกสารทั้งหมดของ Job No: <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($model->job_no) ?></strong>
                    (ค้นพบ <strong class="text-indigo-600" id="showingTimelineCount">0</strong> รายการ)
                </div>
                <div class="small text-muted">
                    <i class="fas fa-info-circle me-1"></i> สามารถกดเปิดดูไฟล์แนบ หรือกดเปิดดูเอกสารฉบับเต็มได้ทันที
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Sections List -->
    <div class="timeline-sections-wrapper">

        <!-- 1. Customer PO Section -->
        <?php 
        $poDocsList = isset($jobPoDocs) ? $jobPoDocs : \backend\models\JobPoDoc::find()->where(['job_id' => $model->id])->all();
        $hasCustomerPo = !empty($model->cus_po_doc) || !empty($poDocsList);
        ?>
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-file-invoice me-2"></i> 1. เอกสารคำสั่งซื้อลูกค้า (Customer PO)
                    <span class="badge bg-white text-primary ms-2"><?= (!empty($model->cus_po_doc) ? 1 : 0) + count($poDocsList) ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if ($hasCustomerPo): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 25%">ประเภท / เลขที่เอกสาร</th>
                                    <th class="text-center" style="width: 12%">วันที่</th>
                                    <th style="width: 20%">ลูกค้า</th>
                                    <th style="width: 24%">รายละเอียดไฟล์ PO</th>
                                    <th class="text-end" style="width: 5%">-</th>
                                    <th class="text-center" style="width: 10%">ไฟล์แนบ / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $cIdx = 0;
                                if (!empty($model->cus_po_doc)):
                                    $cIdx++;
                                    $cleanPf = trim($model->cus_po_doc);
                                    $sText = mb_strtolower('po ลูกค้า customer po ' . $cleanPf . ' ' . ($model->customer ? $model->customer->name : ''));
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $cIdx ?></td>
                                        <td>
                                            <span class="badge bg-primary me-1">PO ลูกค้า</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($cleanPf) ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">-</td>
                                        <td class="fw-semibold text-slate-800"><?= Html::encode($model->customer ? $model->customer->name : 'ลูกค้าทั่วไป') ?></td>
                                        <td class="text-secondary small">
                                            <i class="far fa-file-pdf text-danger me-1"></i> ไฟล์ PO หลักของ Job No: <?= Html::encode($model->job_no) ?>
                                        </td>
                                        <td class="text-end">-</td>
                                        <td class="text-center">
                                            <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($cleanPf) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100 shadow-sm">
                                                <i class="fas fa-paperclip me-1"></i> ดูไฟล์ PO
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($poDocsList)): ?>
                                    <?php foreach ($poDocsList as $pDoc): 
                                        $cIdx++;
                                        $pName = $pDoc->file_name ?: $pDoc->file_path;
                                        $sText = mb_strtolower('po ลูกค้า customer po ' . $pName . ' ' . ($model->customer ? $model->customer->name : ''));
                                    ?>
                                        <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                            <td class="text-center fw-bold text-muted"><?= $cIdx ?></td>
                                            <td>
                                                <span class="badge bg-primary me-1">PO ลูกค้า</span>
                                                <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($pName) ?></strong>
                                            </td>
                                            <td class="text-center text-secondary small">
                                                <?= $pDoc->uploaded_at ? date('d/m/Y', $pDoc->uploaded_at) : '-' ?>
                                            </td>
                                            <td class="fw-semibold text-slate-800"><?= Html::encode($model->customer ? $model->customer->name : 'ลูกค้าทั่วไป') ?></td>
                                            <td class="text-secondary small">
                                                <i class="far fa-file-pdf text-danger me-1"></i> ไฟล์แนบ PO ลูกค้าเพิ่มเติม
                                            </td>
                                            <td class="text-end">-</td>
                                            <td class="text-center">
                                                <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($pDoc->file_path) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100 shadow-sm">
                                                    <i class="fas fa-paperclip me-1"></i> ดูไฟล์ PO
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ยังไม่มีการแนบไฟล์เอกสาร PO ลูกค้าสำหรับ Job นี้</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. Purchase Request (PR) Section -->
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-file-alt me-2"></i> 2. ใบขอซื้อ (Purchase Request / PR)
                    <span class="badge bg-white text-info ms-2"><?= count($purchReqs) ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($purchReqs)): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 18%">ประเภท / เลขที่เอกสาร</th>
                                    <th class="text-center" style="width: 10%">วันที่</th>
                                    <th style="width: 18%">ผู้ขอซื้อ</th>
                                    <th style="width: 30%">รายการสินค้า / วัตถุประสงค์</th>
                                    <th class="text-end" style="width: 10%">มูลค่า (บาท)</th>
                                    <th class="text-center" style="width: 10%">ไฟล์แนบ / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $prIdx = 0;
                                foreach ($purchReqs as $req):
                                    $prIdx++;
                                    $reqName = ($req['fname'] ?? '') . ' ' . ($req['lname'] ?? '');
                                    $sText = mb_strtolower('pr ใบขอซื้อ ' . $req['purch_req_no'] . ' ' . $reqName . ' ' . ($req['note'] ?? ''));
                                    if (!empty($req['lines'])) {
                                        foreach ($req['lines'] as $l) {
                                            $sText .= ' ' . mb_strtolower($l['product_name'] ?? '');
                                        }
                                    }
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $prIdx ?></td>
                                        <td>
                                            <span class="badge bg-info text-white me-1">PR</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($req['purch_req_no']) ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">
                                            <?= date('d/m/Y', strtotime($req['purch_req_date'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800"><?= Html::encode($reqName) ?></div>
                                            <span class="badge bg-light text-secondary border mt-1">
                                                <?= $req['approve_status'] == 1 ? 'อนุมัติแล้ว' : ($req['approve_status'] == 2 ? 'ไม่อนุมัติ' : 'รอพิจารณา') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($req['lines'])): ?>
                                                <ul class="list-unstyled mb-0 small">
                                                    <?php foreach ($req['lines'] as $lItem): ?>
                                                        <li class="mb-1 pb-1 border-bottom border-light">
                                                            <i class="fas fa-cube text-info me-1"></i>
                                                            <strong><?= Html::encode($lItem['product_name'] ?? 'สินค้า') ?></strong>
                                                            <span class="text-muted ms-1">(จำนวน: <?= number_format($lItem['qty'] ?? 0, 1) ?> | ราคา: <?= number_format($lItem['line_price'] ?? 0, 2) ?> บาท)</span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted small"><?= Html::encode($req['note'] ?: '- ไม่มีรายละเอียด -') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            <?= number_format($req['total_amount'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <?php if (!empty($req['docs'])): ?>
                                                    <?php foreach ($req['docs'] as $dFile): 
                                                        $dName = is_array($dFile) ? ($dFile['doc'] ?? $dFile['doc_name'] ?? '') : $dFile;
                                                        if (empty($dName)) continue;
                                                    ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/purch_req_doc/<?= Html::encode($dName) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100" title="ดูไฟล์แนบ">
                                                            <i class="fas fa-paperclip me-1"></i> ดูไฟล์แนบ
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <a href="<?= Url::to(['purchreq/view', 'id' => $req['id']]) ?>" target="_blank" class="btn btn-xs btn-light-modern rounded-pill w-100">
                                                    <i class="fas fa-external-link-alt me-1"></i> เปิดดู PR
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ไม่มีข้อมูลใบขอซื้อ (PR) สำหรับใบงานนี้</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. Purchase Order (PO & None PR) Section -->
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-shopping-cart me-2"></i> 3. ใบสั่งซื้อ (Purchase Order / PO & None-PR)
                    <span class="badge bg-dark text-white ms-2"><?= count($purchases) + count($purchasesnonepr) ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($purchases) || !empty($purchasesnonepr)): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 18%">ประเภท / เลขที่เอกสาร</th>
                                    <th class="text-center" style="width: 10%">วันที่</th>
                                    <th style="width: 18%">ผู้จำหน่าย (Vendor)</th>
                                    <th style="width: 30%">รายการสินค้า / รายละเอียด</th>
                                    <th class="text-end" style="width: 10%">มูลค่า (บาท)</th>
                                    <th class="text-center" style="width: 10%">ไฟล์แนบ / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $poIdx = 0;
                                foreach ($purchases as $po):
                                    $poIdx++;
                                    $sText = mb_strtolower('po ใบสั่งซื้อ ' . $po['purch_no'] . ' ' . ($po['vendor_name'] ?? ''));
                                    if (!empty($po['lines'])) {
                                        foreach ($po['lines'] as $l) {
                                            $sText .= ' ' . mb_strtolower($l['product_name'] ?? '');
                                        }
                                    }
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $poIdx ?></td>
                                        <td>
                                            <span class="badge bg-primary text-white me-1">PO</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($po['purch_no']) ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">
                                            <?= date('d/m/Y', strtotime($po['purch_date'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800"><?= Html::encode($po['vendor_name']) ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($po['lines'])): ?>
                                                <ul class="list-unstyled mb-0 small">
                                                    <?php foreach ($po['lines'] as $lItem): ?>
                                                        <li class="mb-1 pb-1 border-bottom border-light">
                                                            <i class="fas fa-cube text-warning me-1"></i>
                                                            <strong><?= Html::encode($lItem['product_name'] ?? 'สินค้า') ?></strong>
                                                            <span class="text-muted ms-1">(จำนวน: <?= number_format($lItem['qty'] ?? 0, 1) ?> | ราคา: <?= number_format($lItem['line_price'] ?? 0, 2) ?> บาท)</span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted small">- ไม่มีรายละเอียด -</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            <?= number_format($po['net_amount'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <?php if (!empty($po['docs'])): ?>
                                                    <?php foreach ($po['docs'] as $dFile): 
                                                        $dName = is_array($dFile) ? ($dFile['doc'] ?? $dFile['doc_name'] ?? '') : $dFile;
                                                        if (empty($dName)) continue;
                                                    ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/purch_doc/<?= Html::encode($dName) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100" title="ดูไฟล์แนบ">
                                                            <i class="fas fa-paperclip me-1"></i> ดูไฟล์แนบ
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <a href="<?= Url::to(['purch/view', 'id' => $po['id']]) ?>" target="_blank" class="btn btn-xs btn-light-modern rounded-pill w-100">
                                                    <i class="fas fa-external-link-alt me-1"></i> เปิดดู PO
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php foreach ($purchasesnonepr as $pnone):
                                    $poIdx++;
                                    $sText = mb_strtolower('none pr ' . $pnone['purch_no'] . ' ' . ($pnone['vendor_name'] ?? ''));
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $poIdx ?></td>
                                        <td>
                                            <span class="badge bg-warning text-dark me-1">None-PR</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($pnone['purch_no']) ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">
                                            <?= $pnone['purch_date'] != '-' ? date('d/m/Y', strtotime($pnone['purch_date'])) : '-' ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800"><?= Html::encode($pnone['vendor_name']) ?></div>
                                        </td>
                                        <td class="text-secondary small">รายการสั่งซื้อแบบ None PR</td>
                                        <td class="text-end fw-bold text-danger">
                                            <?= number_format($pnone['total_amount'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <?php if (!empty($pnone['docs'])): ?>
                                                    <?php foreach ($pnone['docs'] as $dFile): 
                                                        $dName = is_array($dFile) ? ($dFile['doc'] ?? $dFile['doc_name'] ?? '') : $dFile;
                                                        if (empty($dName)) continue;
                                                    ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/purch_doc/<?= Html::encode($dName) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100">
                                                            <i class="fas fa-paperclip me-1"></i> ดูไฟล์แนบ
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <a href="<?= Url::to(['purchase-master/view', 'id' => $pnone['id']]) ?>" target="_blank" class="btn btn-xs btn-light-modern rounded-pill w-100">
                                                    <i class="fas fa-external-link-alt me-1"></i> เปิดดู None-PR
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ไม่มีข้อมูลใบสั่งซื้อ (PO) สำหรับใบงานนี้</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. Receive Goods Section (Journal Receive) -->
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-truck-loading me-2"></i> 4. รายการรับสินค้าจาก Vendor (Receive Goods)
                    <?php 
                    $recvTrans = array_filter($journalTrans, function($item) { return ($item['trans_type_id'] ?? 0) == 1; });
                    ?>
                    <span class="badge bg-white text-dark ms-2"><?= count($recvTrans) ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recvTrans)): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 18%">ประเภท / เลขที่เอกสาร</th>
                                    <th class="text-center" style="width: 10%">วันที่</th>
                                    <th style="width: 18%">ผู้จำหน่าย (Vendor)</th>
                                    <th style="width: 30%">รายการสินค้าที่รับ</th>
                                    <th class="text-end" style="width: 10%">รวมจำนวน</th>
                                    <th class="text-center" style="width: 10%">ไฟล์แนบ / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rIdx = 0;
                                foreach ($recvTrans as $jt):
                                    $rIdx++;
                                    $sText = mb_strtolower('รับของ รับสินค้า ' . $jt['journal_no'] . ' ' . ($jt['customer_name'] ?? ''));
                                    if (!empty($jt['lines'])) {
                                        foreach ($jt['lines'] as $l) {
                                            $sText .= ' ' . mb_strtolower($l['product_name'] ?? '');
                                        }
                                    }
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $rIdx ?></td>
                                        <td>
                                            <span class="badge bg-secondary text-white me-1">รับของ</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($jt['journal_no']) ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">
                                            <?= date('d/m/Y', strtotime($jt['trans_date'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800"><?= Html::encode($jt['customer_name'] ?: 'Vendor') ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($jt['lines'])): ?>
                                                <ul class="list-unstyled mb-0 small">
                                                    <?php foreach ($jt['lines'] as $lItem): ?>
                                                        <li class="mb-1 pb-1 border-bottom border-light">
                                                            <i class="fas fa-box text-secondary me-1"></i>
                                                            <strong><?= Html::encode($lItem['product_name'] ?? 'สินค้า') ?></strong>
                                                            <span class="text-muted ms-1">(จำนวนรับ: <?= number_format($lItem['qty'] ?? 0, 1) ?>)</span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted small"><?= Html::encode($jt['remark'] ?: '- ไม่มีรายละเอียด -') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            <?= number_format($jt['qty'] ?? 0, 1) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <?php if (!empty($jt['docs'])): ?>
                                                    <?php foreach ($jt['docs'] as $dFile): 
                                                        $dName = is_array($dFile) ? ($dFile['doc'] ?? $dFile['doc_name'] ?? '') : $dFile;
                                                        if (empty($dName)) continue;
                                                    ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/journal_trans_doc/<?= Html::encode($dName) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100">
                                                            <i class="fas fa-paperclip me-1"></i> ดูไฟล์แนบ
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <a href="<?= Url::to(['journaltrans/view', 'id' => $jt['id']]) ?>" target="_blank" class="btn btn-xs btn-light-modern rounded-pill w-100">
                                                    <i class="fas fa-external-link-alt me-1"></i> เปิดดูใบรับสินค้า
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ยังไม่มีรายการรับสินค้าจาก Vendor สำหรับ Job นี้</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 5. Issue/Return Goods Section (Journal Issue) -->
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-boxes me-2"></i> 5. รายการเบิก-คืนสินค้า (Issue/Return Goods)
                    <?php 
                    $issueTrans = array_filter($journalTrans, function($item) { return ($item['trans_type_id'] ?? 0) != 1; });
                    ?>
                    <span class="badge bg-white text-dark ms-2"><?= count($issueTrans) ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($issueTrans)): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 18%">ประเภท / เลขที่เอกสาร</th>
                                    <th class="text-center" style="width: 10%">วันที่</th>
                                    <th style="width: 18%">ผู้เบิกสินค้า</th>
                                    <th style="width: 30%">รายการสินค้าที่เบิก</th>
                                    <th class="text-end" style="width: 10%">จำนวนรวม</th>
                                    <th class="text-center" style="width: 10%">ไฟล์แนบ / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $iIdx = 0;
                                foreach ($issueTrans as $jt):
                                    $iIdx++;
                                    $sText = mb_strtolower('เบิกสินค้า คืนของ ' . $jt['journal_no'] . ' ' . ($jt['customer_name'] ?? ''));
                                    if (!empty($jt['lines'])) {
                                        foreach ($jt['lines'] as $l) {
                                            $sText .= ' ' . mb_strtolower($l['product_name'] ?? '');
                                        }
                                    }
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $iIdx ?></td>
                                        <td>
                                            <span class="badge bg-dark text-white me-1">เบิกสินค้า</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($jt['journal_no']) ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">
                                            <?= date('d/m/Y', strtotime($jt['trans_date'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800"><?= Html::encode($jt['customer_name'] ?: 'พนักงานเบิก') ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($jt['lines'])): ?>
                                                <ul class="list-unstyled mb-0 small">
                                                    <?php foreach ($jt['lines'] as $lItem): ?>
                                                        <li class="mb-1 pb-1 border-bottom border-light">
                                                            <i class="fas fa-cube text-dark me-1"></i>
                                                            <strong><?= Html::encode($lItem['product_name'] ?? 'สินค้า') ?></strong>
                                                            <span class="text-muted ms-1">(จำนวนเบิก: <?= number_format($lItem['qty'] ?? 0, 1) ?>)</span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted small"><?= Html::encode($jt['remark'] ?: '- ไม่มีรายละเอียด -') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            <?= number_format($jt['qty'] ?? 0, 1) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <?php if (!empty($jt['docs'])): ?>
                                                    <?php foreach ($jt['docs'] as $dFile): 
                                                        $dName = is_array($dFile) ? ($dFile['doc'] ?? $dFile['doc_name'] ?? '') : $dFile;
                                                        if (empty($dName)) continue;
                                                    ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/journal_trans_doc/<?= Html::encode($dName) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100">
                                                            <i class="fas fa-paperclip me-1"></i> ดูไฟล์แนบ
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <a href="<?= Url::to(['journaltrans/view', 'id' => $jt['id']]) ?>" target="_blank" class="btn btn-xs btn-light-modern rounded-pill w-100">
                                                    <i class="fas fa-external-link-alt me-1"></i> เปิดดูใบเบิก
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ไม่มีรายการเบิก/คืนสินค้า สำหรับ Job นี้</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 6. JSA & Safety Document Section -->
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-user-shield me-2"></i> 6. เอกสารอบรมเซฟตี้ & JSA
                    <span class="badge bg-white text-info ms-2"><?= !empty($model->jsa_doc) ? 1 : 0 ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($model->jsa_doc)): 
                    $sText = mb_strtolower('jsa เซฟตี้ safety ' . $model->jsa_doc);
                ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 30%">ประเภท / ชื่อไฟล์เอกสาร</th>
                                    <th class="text-center" style="width: 15%">วันที่</th>
                                    <th style="width: 35%">รายละเอียดเอกสาร</th>
                                    <th class="text-center" style="width: 16%">ไฟล์แนบ / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                    <td class="text-center fw-bold text-muted">1</td>
                                    <td>
                                        <span class="badge bg-info text-white me-1">JSA</span>
                                        <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($model->jsa_doc) ?></strong>
                                    </td>
                                    <td class="text-center text-secondary small">-</td>
                                    <td class="text-secondary small">
                                        <i class="far fa-file-pdf text-danger me-1"></i> เอกสารอบรมเซฟตี้ & JSA (Job Safety Analysis)
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($model->jsa_doc) ?>" target="_blank" class="btn btn-xs btn-outline-info rounded-pill w-100 shadow-sm">
                                            <i class="fas fa-paperclip me-1"></i> เปิดดูไฟล์ JSA
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ยังไม่ได้แนบไฟล์เอกสาร JSA/เซฟตี้ สำหรับ Job นี้</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 7. Final Report & Certificate Section -->
        <?php 
        $reportDocsList = isset($jobReportDocs) ? $jobReportDocs : \backend\models\JobReportDoc::find()->where(['job_id' => $model->id])->all();
        $hasReportDoc = !empty($model->report_doc) || !empty($reportDocsList);
        ?>
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-certificate me-2"></i> 7. เอกสาร Final Report / Certificate
                    <span class="badge bg-white text-success ms-2"><?= (!empty($model->report_doc) ? 1 : 0) + count($reportDocsList) ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if ($hasReportDoc): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 25%">โฟลเดอร์ / ชื่อไฟล์เอกสาร</th>
                                    <th class="text-center" style="width: 15%">วันที่อัปโหลด</th>
                                    <th style="width: 35%">รายละเอียดเอกสาร</th>
                                    <th class="text-center" style="width: 16%">ไฟล์แนบ / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rIdx = 0;
                                if (!empty($model->report_doc)):
                                    $rIdx++;
                                    $sText = mb_strtolower('report final report certificate ' . $model->report_doc);
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $rIdx ?></td>
                                        <td>
                                            <span class="badge bg-success text-white me-1">Report หลัก</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($model->report_doc) ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">-</td>
                                        <td class="text-secondary small">
                                            <i class="far fa-file-pdf text-danger me-1"></i> เอกสารรายงาน Final Report หลัก
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($model->report_doc) ?>" target="_blank" class="btn btn-xs btn-outline-success rounded-pill w-100 shadow-sm">
                                                <i class="fas fa-paperclip me-1"></i> เปิดดูไฟล์ Report
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($reportDocsList)): ?>
                                    <?php foreach ($reportDocsList as $rDoc): 
                                        $rIdx++;
                                        $rName = $rDoc->file_name ?: $rDoc->file_path;
                                        $sText = mb_strtolower('report final report certificate ' . $rName . ' ' . $rDoc->folder_name);
                                    ?>
                                        <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                            <td class="text-center fw-bold text-muted"><?= $rIdx ?></td>
                                            <td>
                                                <span class="badge bg-info text-white me-1"><?= Html::encode($rDoc->folder_name ?: 'ทั่วไป') ?></span>
                                                <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($rName) ?></strong>
                                            </td>
                                            <td class="text-center text-secondary small">
                                                <?= $rDoc->uploaded_at ? date('d/m/Y', $rDoc->uploaded_at) : '-' ?>
                                            </td>
                                            <td class="text-secondary small">
                                                <i class="far fa-file-pdf text-danger me-1"></i> เอกสาร Final Report / Certificate ย่อย
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= Yii::$app->request->baseUrl ?>/uploads/job/<?= Html::encode($rDoc->file_path) ?>" target="_blank" class="btn btn-xs btn-outline-success rounded-pill w-100 shadow-sm">
                                                    <i class="fas fa-paperclip me-1"></i> เปิดดูไฟล์ Report
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ยังไม่ได้แนบไฟล์เอกสาร Final Report สำหรับ Job นี้</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 8. Petty Cash Voucher (PCV) Section -->
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-wallet me-2"></i> 8. ใบสำคัญจ่ายเงินสดย่อย (Petty Cash Voucher)
                    <span class="badge bg-dark text-white ms-2"><?= count($pettyCashVouchers) ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($pettyCashVouchers)): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 18%">ประเภท / เลขที่เอกสาร</th>
                                    <th class="text-center" style="width: 10%">วันที่</th>
                                    <th style="width: 18%">ผู้เบิกเงินสดย่อย</th>
                                    <th style="width: 30%">รายละเอียดการจ่ายเงิน</th>
                                    <th class="text-end" style="width: 10%">จำนวนเงิน (บาท)</th>
                                    <th class="text-center" style="width: 10%">ไฟล์แนบ / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $pcvIdx = 0;
                                foreach ($pettyCashVouchers as $pcv):
                                    $pcvIdx++;
                                    $sText = mb_strtolower('เงินสดย่อย pcv ' . $pcv['pcv_no'] . ' ' . ($pcv['payee_name'] ?? '') . ' ' . ($pcv['description'] ?? ''));
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $pcvIdx ?></td>
                                        <td>
                                            <span class="badge bg-warning text-dark me-1">เงินสดย่อย</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($pcv['pcv_no']) ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">
                                            <?= date('d/m/Y', strtotime($pcv['pcv_date'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800"><?= Html::encode($pcv['payee_name'] ?: 'พนักงาน') ?></div>
                                        </td>
                                        <td class="text-secondary small">
                                            <?= Html::encode($pcv['description'] ?: '- ไม่มีรายละเอียด -') ?>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            <?= number_format($pcv['amount'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <?php if (!empty($pcv['slips'])): ?>
                                                    <?php foreach ($pcv['slips'] as $slip): ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/pettycash_doc_slip/<?= Html::encode($slip->doc) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100">
                                                            <i class="fas fa-file-invoice me-1"></i> ดูสลิป
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <?php if (!empty($pcv['bills'])): ?>
                                                    <?php foreach ($pcv['bills'] as $bill): ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/pettycash_doc_bill/<?= Html::encode($bill->doc) ?>" target="_blank" class="btn btn-xs btn-outline-info rounded-pill w-100">
                                                            <i class="fas fa-file-alt me-1"></i> ดูบิล
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <a href="<?= Url::to(['petty-cash-voucher/view', 'id' => $pcv['id']]) ?>" target="_blank" class="btn btn-xs btn-light-modern rounded-pill w-100">
                                                    <i class="fas fa-external-link-alt me-1"></i> เปิดดู PCV
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ไม่มีข้อมูลใบสำคัญจ่ายเงินสดย่อย สำหรับ Job นี้</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 9. Invoice & Billing Section -->
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-receipt me-2"></i> 9. ใบแจ้งหนี้ / ใบวางบิล / ใบกำกับภาษี (Invoice / Billing)
                    <span class="badge bg-white text-primary ms-2"><?= count($invoices) + count($billingInvoices) ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($invoices) || !empty($billingInvoices)): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 18%">ประเภท / เลขที่เอกสาร</th>
                                    <th class="text-center" style="width: 10%">วันที่</th>
                                    <th style="width: 18%">ลูกค้า</th>
                                    <th style="width: 30%">รายการสินค้า / รายละเอียดวางบิล</th>
                                    <th class="text-end" style="width: 10%">ยอดสุทธิ (บาท)</th>
                                    <th class="text-center" style="width: 10%">ไฟล์แนบ / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $invIdx = 0;
                                foreach ($invoices as $inv):
                                    $invIdx++;
                                    $sText = mb_strtolower('invoice ใบกำกับภาษี ใบแจ้งหนี้ ' . $inv['invoice_number'] . ' ' . ($inv['customer_name'] ?? ''));
                                    if (!empty($inv['items'])) {
                                        foreach ($inv['items'] as $l) {
                                            $sText .= ' ' . mb_strtolower($l['item_description'] ?? '');
                                        }
                                    }
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $invIdx ?></td>
                                        <td>
                                            <span class="badge bg-primary me-1">Invoice</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($inv['invoice_number']) ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">
                                            <?= date('d/m/Y', strtotime($inv['invoice_date'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800"><?= Html::encode($inv['customer_name']) ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($inv['items'])): ?>
                                                <ul class="list-unstyled mb-0 small">
                                                    <?php foreach ($inv['items'] as $iItem): ?>
                                                        <li class="mb-1 pb-1 border-bottom border-light">
                                                            <i class="fas fa-file-invoice-dollar text-primary me-1"></i>
                                                            <strong><?= Html::encode($iItem['item_description'] ?? 'รายการ') ?></strong>
                                                            <span class="text-muted ms-1">(จำนวน: <?= number_format($iItem['quantity'] ?? 0, 1) ?> | ยอด: <?= number_format($iItem['amount'] ?? 0, 2) ?> บาท)</span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted small"><?= Html::encode($inv['notes'] ?: '- ไม่มีรายละเอียด -') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            <?= number_format($inv['total_amount'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <?php if (!empty($inv['docs'])): ?>
                                                    <?php foreach ($inv['docs'] as $dFile): 
                                                        $dName = is_array($dFile) ? ($dFile['doc'] ?? $dFile['doc_name'] ?? '') : $dFile;
                                                        if (empty($dName)) continue;
                                                    ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/invoice_doc/<?= Html::encode($dName) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100">
                                                            <i class="fas fa-paperclip me-1"></i> ดูไฟล์แนบ
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <a href="<?= Url::to(['invoice/view', 'id' => $inv['id']]) ?>" target="_blank" class="btn btn-xs btn-light-modern rounded-pill w-100">
                                                    <i class="fas fa-external-link-alt me-1"></i> เปิดดู Invoice
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php foreach ($billingInvoices as $bi):
                                    $invIdx++;
                                    $sText = mb_strtolower('ใบวางบิล billing ' . $bi['billing_number'] . ' ' . ($bi['customer_name'] ?? ''));
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $invIdx ?></td>
                                        <td>
                                            <span class="badge bg-info text-white me-1">ใบวางบิล</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($bi['billing_number']) ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">
                                            <?= date('d/m/Y', strtotime($bi['billing_date'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800"><?= Html::encode($bi['customer_name']) ?></div>
                                        </td>
                                        <td class="text-secondary small">
                                            เอกสารใบวางบิลครบกำหนดชำระวันที่: <?= $bi['payment_due_date'] ? date('d/m/Y', strtotime($bi['payment_due_date'])) : '-' ?>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            <?= number_format($bi['total_amount'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <?php if (!empty($bi['docs'])): ?>
                                                    <?php foreach ($bi['docs'] as $dFile): 
                                                        $dName = is_array($dFile) ? ($dFile['doc'] ?? $dFile['doc_name'] ?? '') : $dFile;
                                                        if (empty($dName)) continue;
                                                    ?>
                                                        <a href="<?= Yii::$app->request->baseUrl ?>/uploads/invoice_doc/<?= Html::encode($dName) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100">
                                                            <i class="fas fa-paperclip me-1"></i> ดูไฟล์แนบ
                                                        </a>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <a href="<?= Url::to(['billing-invoice/view', 'id' => $bi['id']]) ?>" target="_blank" class="btn btn-xs btn-light-modern rounded-pill w-100">
                                                    <i class="fas fa-external-link-alt me-1"></i> เปิดดูใบวางบิล
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ไม่มีข้อมูลใบแจ้งหนี้/ใบวางบิล สำหรับ Job นี้</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 10. Payment Receipt Section -->
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-check-circle me-2"></i> 10. ใบเสร็จรับเงิน & ยอดรับชำระเงิน (Payment Receipt)
                    <span class="badge bg-white text-success ms-2"><?= count($paymentReceipts) ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($paymentReceipts)): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 18%">ประเภท / เลขที่เอกสาร</th>
                                    <th class="text-center" style="width: 10%">วันที่ชำระ</th>
                                    <th style="width: 18%">ลูกค้า / ผู้รับเงิน</th>
                                    <th style="width: 30%">ช่องทางชำระ & รายการวางบิล</th>
                                    <th class="text-end" style="width: 10%">ยอดรับชำระ (บาท)</th>
                                    <th class="text-center" style="width: 10%">ไฟล์แนบ / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $prcIdx = 0;
                                foreach ($paymentReceipts as $receipt):
                                    $prcIdx++;
                                    $sText = mb_strtolower('ใบเสร็จ ชำระเงิน ' . $receipt['receipt_number'] . ' ' . ($receipt['customer_name'] ?? '') . ' ' . ($receipt['payment_method'] ?? ''));
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $prcIdx ?></td>
                                        <td>
                                            <span class="badge bg-success me-1">ใบเสร็จ</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($receipt['receipt_number']) ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">
                                            <?= date('d/m/Y', strtotime($receipt['payment_date'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800"><?= Html::encode($receipt['customer_name']) ?></div>
                                            <div class="text-muted small">ผู้รับเงิน: <?= Html::encode($receipt['receiver_name']) ?></div>
                                        </td>
                                        <td class="text-secondary small">
                                            <span class="badge bg-light text-dark border me-1"><?= Html::encode($receipt['payment_method']) ?></span>
                                            <?php if (!empty($receipt['bank_name'])): ?>
                                                <span>ธนาคาร: <?= Html::encode($receipt['bank_name']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            <?= number_format($receipt['received_amount'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-1 align-items-center">
                                                <?php if (!empty($receipt['attachment_name']) || !empty($receipt['attachment_path'])): ?>
                                                    <a href="<?= Yii::$app->request->baseUrl ?>/<?= Html::encode($receipt['attachment_path']) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill w-100">
                                                        <i class="fas fa-paperclip me-1"></i> ดูสลิป/แนบ
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= Url::to(['payment-receipt/view', 'id' => $receipt['id']]) ?>" target="_blank" class="btn btn-xs btn-light-modern rounded-pill w-100">
                                                    <i class="fas fa-external-link-alt me-1"></i> เปิดดูใบเสร็จ
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ไม่มีข้อมูลใบเสร็จรับเงิน สำหรับ Job นี้</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 11. Vehicle Expense Section -->
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-car me-2"></i> 11. ค่าใช้จ่ายยานพาหนะ & การเดินทาง (Vehicle Expense)
                    <span class="badge bg-white text-dark ms-2"><?= count($vehicleExpense) ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($vehicleExpense)): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 18%">ประเภท / ทะเบียนรถ</th>
                                    <th class="text-center" style="width: 10%">วันที่</th>
                                    <th style="width: 18%">ผู้ขับขี่ / รายละเอียด</th>
                                    <th style="width: 30%">รายละเอียดการใช้รถ</th>
                                    <th class="text-end" style="width: 10%">จำนวนเงิน (บาท)</th>
                                    <th class="text-center" style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $veIdx = 0;
                                foreach ($vehicleExpense as $ve):
                                    $veIdx++;
                                    $sText = mb_strtolower('ค่ารถ ยานพาหนะ ' . ($ve['plate_no'] ?? '') . ' ' . ($ve['description'] ?? ''));
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $veIdx ?></td>
                                        <td>
                                            <span class="badge bg-dark text-white me-1">ค่ารถ</span>
                                            <strong class="text-indigo-600" style="color: #4f46e5;"><?= Html::encode($ve['plate_no'] ?? 'ไม่ระบุ') ?></strong>
                                        </td>
                                        <td class="text-center text-secondary small">
                                            <?= date('d/m/Y', strtotime($ve['trans_date'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800"><?= Html::encode($ve['driver_name'] ?? '-') ?></div>
                                        </td>
                                        <td class="text-secondary small">
                                            <?= Html::encode($ve['description'] ?: '- ไม่มีรายละเอียด -') ?>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            <?= number_format($ve['amount'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted small">-</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ไม่มีข้อมูลค่าใช้จ่ายยานพาหนะ สำหรับ Job นี้</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 12. Job Expense Section -->
        <div class="card timeline-section-card border-0 shadow-sm">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="font-family: 'Prompt', sans-serif;">
                    <i class="fas fa-coins me-2"></i> 12. ค่าใช้จ่ายอื่นๆ (Job Expenses)
                    <span class="badge bg-white text-danger ms-2"><?= count($jobExpenses) ?> รายการ</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($jobExpenses)): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%">#</th>
                                    <th style="width: 18%">ประเภท</th>
                                    <th class="text-center" style="width: 10%">วันที่</th>
                                    <th style="width: 18%">ผู้บันทึก</th>
                                    <th style="width: 30%">รายละเอียดค่าใช้จ่าย</th>
                                    <th class="text-end" style="width: 10%">จำนวนเงิน (บาท)</th>
                                    <th class="text-center" style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $jeIdx = 0;
                                foreach ($jobExpenses as $expense):
                                    $jeIdx++;
                                    $sText = mb_strtolower('คชจ อื่นๆ ' . ($expense->description ?? '') . ' ' . ($expense->remark ?? ''));
                                ?>
                                    <tr class="timeline-item-row" data-search="<?= Html::encode($sText) ?>">
                                        <td class="text-center fw-bold text-muted"><?= $jeIdx ?></td>
                                        <td>
                                            <span class="badge bg-danger me-1">คชจ.อื่นๆ</span>
                                        </td>
                                        <td class="text-center text-secondary small">
                                            <?= date('d/m/Y', strtotime($expense->trans_date)) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800"><?= $expense->createdBy ? Html::encode($expense->createdBy->username) : '-' ?></div>
                                        </td>
                                        <td class="text-secondary small">
                                            <?= Html::encode($expense->description) ?>
                                            <?php if (!empty($expense->remark)): ?>
                                                <div class="text-muted ms-1">(<?= Html::encode($expense->remark) ?>)</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            <?= number_format($expense->line_amount ?: $expense->amount, 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted small">-</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-3 text-muted small"><i class="fas fa-info-circle me-1"></i> ไม่มีข้อมูลค่าใช้จ่ายอื่นๆ สำหรับ Job นี้</div>
                <?php endif; ?>
            </div>
        </div>

    </div> <!-- End Timeline Sections Wrapper -->

</div> <!-- End Job Timeline View -->

<script>
function updateTimelineSearchCount() {
    var count = $('.timeline-item-row:visible').length;
    $('#showingTimelineCount').text(count);
}

// Initial count
updateTimelineSearchCount();

$(document).off('keyup input', '#timelineSearchKeyword').on('keyup input', '#timelineSearchKeyword', function() {
    var kw = $.trim($(this).val()).toLowerCase();
    
    $('.timeline-item-row').each(function() {
        var searchData = $(this).attr('data-search') || '';
        if (kw === '' || searchData.indexOf(kw) !== -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });

    $('.timeline-section-card').each(function() {
        var section = $(this);
        var visibleRows = section.find('.timeline-item-row:visible').length;
        var totalRows = section.find('.timeline-item-row').length;
        if (totalRows > 0 && visibleRows === 0 && kw !== '') {
            section.hide();
        } else {
            section.show();
        }
    });

    updateTimelineSearchCount();
});

$(document).off('click', '.btn-timeline-tag').on('click', '.btn-timeline-tag', function() {
    var kw = $(this).attr('data-kw') || '';
    $('#timelineSearchKeyword').val(kw).trigger('keyup');
});
</script>