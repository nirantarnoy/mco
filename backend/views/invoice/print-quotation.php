<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Invoice */

$this->title = 'พิมพ์ใบแจ้งหนี้ - ' . $model->invoice_number;

// Add print styles that match the original form exactly with multi-copy support
$this->registerCss("
@page {
    size: A4;
    margin: 8mm;
}
    @font-face {
        font-family: 'THSarabunPSK';
        src: url('../../backend/web/fonts/thsarabun/THSarabunPSK.ttf') format('truetype');
        font-weight: normal;
    }

    @font-face {
        font-family: 'THSarabunPSK';
        src: url('../../backend/web/fonts/thsarabun/THSarabunPSK-Bold.ttf') format('truetype');
        font-weight: bold;
    }

    @font-face {
        font-family: 'THSarabunPSK';
        src: url('../../backend/web/fonts/thsarabun/THSarabunPSK-Italic.ttf') format('truetype');
        font-style: italic;
    }

@media print {
    .no-print { display: none !important; }
    .main-footer { display: none !important; }
    body { 
        margin: 0; 
        padding: 0; 
        font-family: 'THSarabunPSK' !important;
        font-size: 13px;
        color: #000;
    }
    .print-container { 
        font-family: 'THSarabunPSK' !important;
        max-width: 0 auto; 
        width: 100%;
        page-break-after: always;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
    }
    .print-container:last-child {
        page-break-after: auto;
    }
    .copy-watermark {
        display: none !important;
    }
}

.print-container {
    font-family: 'THSarabunPSK' !important;
    max-width: 0 auto;
    margin: 0 auto;
    background: white;
    padding: 15mm;
    border: 1px solid #ddd;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    position: relative;
}

.copy-watermark {
    display: none;
}

/* Header Section */
.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.company-logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo {
    width: 300px;
    height: 100px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    font-weight: bold;
}

.company-info {
    flex: 1;
    margin-left: 12px;
    text-align: center;
}

.company-name-thai {
    font-size: 32px;
    font-weight: 900;
    -webkit-text-stroke: 0.5px black;
}

.company-name-eng {
    font-size: 28px;
    font-weight: 900;
    margin-left: -135px;
    margin-top: -10px;
    -webkit-text-stroke: 0.5px black;
}

.company-address {
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 2px;
    font-weight: 900;
    -webkit-text-stroke: 0.3px black;
}

.invoice-info {
    text-align: center;
    min-width: 200px;
}

.invoice-title {
    font-size: 30px;
    font-weight: 900;
    margin-bottom: 5px;
    position: relative;
    -webkit-text-stroke: 0.3px black;
}

.invoice-subtitle {
    font-size: 12px;
    margin-bottom: 5px;
    color: #666;
}

.not-tax-invoice {
    font-size: 12px;
    color: #666;
    margin-bottom: 10px;
}

/* Copy Label Styles */
.invoice-title-section {
    text-align: center;
    position: relative;
    margin: 8px 0;
}

.copy-label {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    font-weight: bold;
    padding: 4px 8px;
    background-color: rgba(255, 255, 255, 0.9);
}

.copy-label.original {
    color: #0066cc;
    border: 2px solid #0066cc;
}

.copy-label.copy {
    color: #ff0000;
    border: 2px solid #ff0000;
}

/* Customer Section */
.customer-section {
    display: flex;
    justify-content: space-between;
    margin: 10px 0;
    gap: 20px;
    font-size: 16px !important;
}

.customer-left, .customer-right {
    flex: 1;
}

.field-group {
    margin-bottom: 3px;
    display: flex;
    align-items: flex-start;
}

.field-label {
    min-width: 100px;
    font-weight: 800;
    -webkit-text-stroke: 0.25px black;
}

.field-value {
    flex: 1;
    padding: 2px 5px;
    min-height: 18px;
    line-height: 1.3;
}

/* Items Table */
.items-section {
    margin: 20px 0;
}

// .items-table {
//     width: 100%;
//     border-collapse: collapse;
//     border: 2px solid #000;
//     font-size: 12px;
// }

// .items-table th,
// .items-table td {
//     border: 1px solid #000;
//     padding: 8px 4px;
//     text-align: center;
//     vertical-align: middle;
// }

// .items-table th {
//     background-color: #f8f9fa;
//     font-weight: bold;
//     height: 35px;
//     font-size: 11px;
// }

// .items-table td {
//     height: 30px;
// }

// .items-table .text-left { text-align: left; padding-left: 8px; }
// .items-table .text-right { text-align: right; padding-right: 8px; }

.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

/* เส้นรอบนอก */
.items-table th,
.items-table tfoot td {
    border: 1px solid #000;
}

/* Header */
.items-table thead th {
    border-bottom: 1px solid #000;
    background-color: #f8f9fa;
    padding: 4px;
    font-weight: 800;
    -webkit-text-stroke: 0.25px black;
}

/* เนื้อหาแถว ไม่ต้องมีเส้นขอบ */
.items-table tbody td {
    border: none !important;
    padding: 3px;
}

/* ให้มีแค่เส้นระหว่าง head → tbody */
.items-table thead tr {
    border-bottom: 1px solid #000;
}

/* Footer (summary) */
.items-table tfoot td {
    padding: 4px;
}




/* Empty rows */
.empty-row {
    height: 35px;
}

/* Summary Section */
.summary-section {
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.summary-left {
    flex: 1;
    padding: 10px;
}

.summary-right {
    width: 250px;
    border: 1px solid #000;
    padding: 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 8px;
    border-bottom: 1px solid #000;
    font-size: 14px;
    font-weight: 800;
    -webkit-text-stroke: 0.25px black;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    font-weight: bold;
    background-color: #f0f0f0;
}

.amount-text {
    font-size: 14px;
    margin-bottom: 5px;
    font-weight: 800;
    -webkit-text-stroke: 0.25px black;
}

/* Notes Section */
.notes-section {
    margin: 10px 0;
    font-size: 12px;
    line-height: 1.3;
    font-weight: 800;
    -webkit-text-stroke: 0.15px black;
}

.notes-title {
    font-weight: 800;
    margin-bottom: 5px;
    -webkit-text-stroke: 0.25px black;
}

.note-item {
    margin-bottom: 3px;
    text-indent: 15px;
}

/* Signature Section */
.signature-section {
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.signature-box {
    flex: 1;
    border: 1px solid #000;
    padding: 8px;
    text-align: center;
    height: 120px;
    position: relative;
}

.signature-title {
    font-weight: 800;
    margin-bottom: 3px;
    font-size: 12px;
    -webkit-text-stroke: 0.25px black;
}

.signature-line {
    border-bottom: 1px solid #000;
    margin: 20px 5px 8px;
}

.signature-date {
    font-size: 10px;
    position: absolute;
    bottom: 5px;
    left: 50%;
    transform: translateX(-50%);
}

/* Print buttons and progress */
.print-controls {
    margin-bottom: 20px;
    text-align: center;
}

.btn {
    padding: 8px 16px;
    margin: 0 5px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn:hover {
    opacity: 0.8;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Progress Bar */
.progress-container {
    margin: 20px 0;
    display: none;
}

.progress-bar {
    width: 100%;
    height: 30px;
    background-color: #f0f0f0;
    border-radius: 15px;
    overflow: hidden;
    border: 2px solid #ddd;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #007bff, #28a745);
    width: 0%;
    transition: width 0.5s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 12px;
}

.progress-text {
    text-align: center;
    margin-top: 10px;
    font-weight: bold;
    color: #333;
}

.header-flex {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    width: 100%;
}

/* ช่องเว้นระหว่างคอลัมน์ */
.header-flex > div {
    flex: 1;
}

/* กล่องโลโก้ */
.logo-box {
    max-width: 22%;
    margin-top: -3px;
}

/* กล่องข้อมูลบริษัท */
.company-info-box {
    text-align: center;
    max-width: 56%;
    text-size: 6px;
}

/* กล่อง TAXID */
.taxid-box {
    text-align: right;
    max-width: 22%;
    margin-top: 33px;
    font-size: 20px;
    font-weight: 900;
    -webkit-text-stroke: 0.5px black;
}

/* ป้องกันภาพดัน layout */
.logo-box img {
    max-width: 180px;
    height: auto;
}

.field-label-group {
    width: 80px; /* กำหนดความกว้าง label ทั้งก้อน */
}


/* Utilities */
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-left { text-align: left; }
.font-bold { font-weight: bold; }

/* Responsive adjustments */
@media screen and (max-width: 768px) {
    .header {
        flex-direction: column;
        text-align: center;
    }
    
    .customer-section {
        flex-direction: column;
    }
    
    .signature-section {
        flex-direction: column;
        gap: 20px;
    }
    
    .signature-box {
        width: 100%;
    }
}
");

// Enhanced JavaScript for multi-copy printing with progress
$this->registerJs("
// Global variables
let printInProgress = false;
let currentCopy = 0;
const totalCopies = 3;

// Function declarations with window object to ensure global scope
window.updateProgress = function(current, total) {
    const progressContainer = document.querySelector('.progress-container');
    const progressFill = document.querySelector('.progress-fill');
    const progressText = document.querySelector('.progress-text');
    
    if (!progressContainer) return;
    
    progressContainer.style.display = 'block';
    const percentage = (current / total) * 100;
    progressFill.style.width = percentage + '%';
    progressFill.textContent = percentage.toFixed(0) + '%';
    
    if (current === 0) {
        progressText.textContent = 'กำลังเตรียมพิมพ์...';
    } else if (current === total) {
        progressText.textContent = 'พิมพ์เสร็จสิ้น!';
        setTimeout(() => {
            progressContainer.style.display = 'none';
            progressFill.style.width = '0%';
        }, 2000);
    } else {
        progressText.textContent = 'กำลังพิมพ์ใบที่ ' + current + ' จาก ' + total + ' ใบ';
    }
};

window.createPrintCopies = function() {
    // Remove existing copies if any
    const existingCopies = document.querySelectorAll('.print-copy');
    existingCopies.forEach(copy => copy.remove());
    
    let originalContainer = document.querySelector('.print-container.original');
    if (!originalContainer) {
        const container = document.querySelector('.print-container');
        if (container) {
            container.classList.add('original');
            originalContainer = container;
        }
    }
    
    if (!originalContainer) return;
    
    // Add original label to the first copy
    const originalTitleSection = originalContainer.querySelector('.invoice-title-section');
    if (originalTitleSection && !originalTitleSection.querySelector('.copy-label')) {
        const originalLabel = document.createElement('div');
        originalLabel.className = 'copy-label original';
        originalLabel.textContent = 'ต้นฉบับ';
        originalTitleSection.appendChild(originalLabel);
    }
    
    // Create 2 copies
    for (let i = 1; i <= 2; i++) {
        const copy = originalContainer.cloneNode(true);
        copy.classList.remove('original');
        copy.classList.add('print-copy');
        
        // Add copy label next to invoice title
        const titleSection = copy.querySelector('.invoice-title-section');
        if (titleSection) {
            // Remove existing label if any
            const existingLabel = titleSection.querySelector('.copy-label');
            if (existingLabel) {
                existingLabel.remove();
            }
            
            const copyLabel = document.createElement('div');
            copyLabel.className = 'copy-label copy';
            copyLabel.textContent = 'สำเนา';
            titleSection.appendChild(copyLabel);
        }
        
        originalContainer.parentNode.appendChild(copy);
    }
};

window.printMultipleCopies = function() {
    if (printInProgress) return;
    
    printInProgress = true;
    currentCopy = 0;
    
    // Disable print button
    const printBtn = document.querySelector('.btn-print');
    if (printBtn) {
        printBtn.disabled = true;
        printBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i> กำลังพิมพ์...';
    }
    
    window.updateProgress(0, totalCopies);
    
    // Create copies for printing
    window.createPrintCopies();
    
    // Start printing process
    setTimeout(() => {
        window.print();
    }, 500);
};

// Enhanced print event handlers
window.addEventListener('beforeprint', function() {
    document.body.style.zoom = '1';
    window.updateProgress(1, totalCopies);
});

window.addEventListener('afterprint', function() {
    currentCopy++;
    window.updateProgress(totalCopies, totalCopies);
    
    // Re-enable print button
    const printBtn = document.querySelector('.btn-print');
    if (printBtn) {
        printBtn.disabled = false;
        printBtn.innerHTML = '<i class=\"fas fa-print\"></i> พิมพ์ 3 ใบ';
    }
    
    printInProgress = false;
    
    // Clean up copies after printing
    setTimeout(() => {
        const copies = document.querySelectorAll('.print-copy');
        copies.forEach(copy => copy.remove());
        
        // Also remove the original label from the first copy
        const originalLabel = document.querySelector('.copy-label.original');
        if (originalLabel) {
            originalLabel.remove();
        }
    }, 1000);
});

// Auto print when page loads (disabled for demo)
// window.onload = function() {
//     setTimeout(function() {
//         window.printMultipleCopies();
//     }, 1000);
// };
");
?>

<div class="no-print text-center mb-4">
    <div class="print-controls">
        <div class="btn-group">
            <button onclick="window.printMultipleCopies()" class="btn btn-primary btn-print">
                <i class="fas fa-print"></i> พิมพ์ 3 ใบ (ต้นฉบับ + สำเนา 2 ใบ)
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="fas fa-times"></i> ปิด
            </button>
            <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-info">
                <i class="fas fa-eye"></i> ดูรายละเอียด
            </a>
        </div>

        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <div class="progress-text">เตรียมพิมพ์...</div>
        </div>
    </div>
</div>

<div class="print-container original">
    <!-- Header -->
    <div class="header-flex">

        <div class="logo-box">
            <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png', [
                'style' => 'max-width:180px;'
            ]) ?>
        </div>

        <div class="company-info-box">
            <div class="company-name-thai">บริษัท เอ็ม. ซี. โอ. จำกัด (สำนักงานใหญ่)</div>
            <div class="company-name-eng">M.C.O. COMPANY LIMITED</div>
            <div class="company-address" style="margin-left: 48px">
                8/18 ถ.เกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000 โทร 66-(0)-38875258-59 แฟ๊กซ์66-(0)-3861-9559
            </div>
            <div class="company-address" style="margin-left: 82px">
                8/18 Koh-Kloy-Rd., Cherngnoen, Muang, Rayong 21000 Tel. 66-(0)3887-5258-59 Fax. 66-(0)3861-9559
            </div>
        </div>

        <div class="taxid-box">
            <div style="font-weight: bold; margin: 3px 0;">TAXID: 0215543000985</div>
        </div>

    </div>

    <br>
    <div class="row">
        <div class="col-lg-12" style="text-align: center">
            <div class="invoice-title-section">
                <div class="invoice-title">ใบแจ้งหนี้/ใบส่งสินค้า-บริการ</div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12" style="text-align: right">
            <div class="invoice-infox">
                <div class="not-tax-invoice">(ไม่ใช่ใบกำกับภาษี)</div>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-section">
        <div class="customer-left">
            <div class="field-group">
                <div class="field-label-group">
                    <div class="field-label">รหัสลูกค้า :</div>
                    <div class="field-label">Code</div>
                </div>
                <span class="field-value">
                    <?= Html::encode($model->quotation->customer->code ?: '') ?>
                </span>
            </div>
            <div class="field-group">
                <div class="field-label-group">
                    <div class="field-label">ขายให้ :</div>
                    <div class="field-label">Sold To</div>
                </div>

                <span class="field-value">
                    <?= Html::encode($model->quotation->customer->name ?: '') ?> <br>
                    <?= Html::encode($model->customer_address ?: '') ?><br>
                    เลขประจำตัวผู้เสียภาษี
                    <?= Html::encode($model->customer_tax_id ?: '') ?>
                </span>
            </div>
        </div>
        <div class="customer-right" style="margin-left: 85px;">
            <div class="field-group">
                <span class="field-label">วันที่ / Date:</span>
                <span class="field-value"><?= Yii::$app->formatter->asDate($model->invoice_date, '') ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">เลขที่ / In.No.:</span>
                <span class="field-value"><?= Html::encode($model->invoice_number) ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">ใบสั่งซื้อเลขที่ / P/O No.:</span>
                <span class="field-value"><?= Html::encode($model->po_number ?: '') ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">วันที่สั่งซื้อ / P/O Date:</span>
                <span class="field-value"><?= $model->po_date ? Yii::$app->formatter->asDate($model->po_date, '') : '' ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">เงื่อนไข / กำหนดชำระ / Credit, Due:</span>
                <span class="field-value"><?= Html::encode(\backend\models\Paymentterm::findName($model->payment_term_id)) ?></span>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="items-section">
        <table class="items-table">
            <thead>
                <tr style="text-align: center;">
                    <th style="width: 8%;">ลำดับ<br>Item</th>
                    <th style="width: 40%;">รายการ<br>Description</th>
                    <th style="width: 12%;">จำนวน<br>Quantity</th>
                    <th style="width: 15%;">ราคาต่อหน่วย<br>Unit/Price</th>
                    <th style="width: 15%;">จำนวนเงินรวม<br>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $model_line = \backend\models\InvoiceItem::find()->where(['invoice_id' => $model->id])->all();
                ?>
                <?php if (!empty($model_line)): ?>
                    <?php foreach ($model_line as $index => $item): ?>
                        <tr>
                            <td style="padding:8px;text-align: center"><?= $index + 1 ?></td>
                            <td class="text-left" style="padding:8px;"><?= nl2br(Html::encode($item->item_description)) ?></td>
                            <td style="padding:8px;text-align: center"><?= number_format($item->quantity, 0) ?> <?= Html::encode($item->unit) ?></td>
                            <td class="text-right" style="padding:8px;"><?= number_format($item->unit_price, 2) ?></td>
                            <td class="text-right" style="padding:8px;"><?= number_format($item->amount, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default sample data -->
                    <tr>
                        <td style="padding:8px;"></td>
                        <td class="text-left" style="padding:8px;"></td>
                        <td style="padding:8px;"></td>
                        <td class="text-right" style="padding:8px;"></td>
                        <td class="text-right" style="padding:8px;"></td>
                    </tr>
                <?php endif; ?>

                <!-- Empty rows for spacing -->
                <?php for ($i = count($model_line); $i < 16; $i++): ?>
                    <tr>
                        <td style="padding:8px;">&nbsp;</td>
                        <td style="padding:8px;">&nbsp;</td>
                        <td style="padding:8px;">&nbsp;</td>
                        <td style="padding:8px;">&nbsp;</td>
                        <td style="padding:8px;">&nbsp;</td>
                    </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" rowspan="3" style="padding: 8px;text-align: left;">
                        <div class="summary-left">
                            <div class="amount-text">(ตัวอักษร)</div>
                            <div style="font-size: 14px; font-weight: 800; text-align: center; -webkit-text-stroke: 0.25px black;">
                                <?= $model->total_amount_text ?: '' ?>
                            </div>

                            <br>
                            <br>

                        </div>
                    </td>
                    <td><span style="font-weight: 800; -webkit-text-stroke: 0.25px black;">รวมเงิน / Total</span></td>
                    <td class="text-right"><span style="font-weight: 800; -webkit-text-stroke: 0.25px black;"><?= number_format($model->subtotal, 2) ?></span></td>
                </tr>
                <tr>
                    <!--                <td colspan="3" style="padding: 8px;"></td>-->
                    <td>
                        <span style="font-weight: 800; -webkit-text-stroke: 0.25px black;">ภาษีมูลค่าเพิ่ม / Vat <?= $model->vat_percent ?>%</span>
                    </td>
                    <td class="text-right">
                        <span style="font-weight: 800; -webkit-text-stroke: 0.25px black;"><?= number_format($model->vat_amount, 2) ?></span>
                    </td>
                </tr>
                <tr>
                    <!--                <td colspan="3" style="padding: 8px;"></td>-->
                    <td>
                        <span style="font-weight: 800; -webkit-text-stroke: 0.25px black;">รวมเงินทั้งสิ้น / Grand Total</span>
                    </td>
                    <td class="text-right">
                        <span style="font-weight: 800; -webkit-text-stroke: 0.25px black;"><?= number_format($model->total_amount, 2) ?></span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>


    <!-- Notes Section -->
    <div class="notes-section">
        <div class="notes-title">หมายเหตุ :
            <span style="line-height: 2;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1. ตามรายการข้างต้น แม้จะได้ส่งมอบสินค้าแก่ผู้ซื้อแล้วก็ยังเป็นทรัพย์สินของผู้ขายจนกว่าผู้ซื้อจะได้รับชำระเงิน</span><br>
            <span style="line-height: 2;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. สินค้าที่ซื้อไปเกินกว่า 7 วัน ทางบริษัทฯใคร่ขอสงวนสิทธิ์ไม่รับคืนสินค้าและคิดดอกเบี้ยร้อยละ1.5 ต่อเดือน</span><br>
            <span style="line-height: 2;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3. สามารถชำระผ่านช่องทางธนาคารกรุงเทพจำกัด (มหาชน) สาขาระยอง ชื่อบัญชีบจ.เอ็ม.ซี.โอ. เลขบัญชี277-3-02318-5 บัญชีกระแสรายวัน</span>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div style="font-size: 12px; font-weight: 900; margin-bottom: 10px;">ได้ตรวจรับสินค้าตามรายการข้างต้นถูกต้อง</div>
            <div class="signature-line" style="margin-top: 30px;"></div>
            <div class="signature-title">ผู้รับสินค้า / Received By</div>
            <div style="text-align: center; font-size: 12px; font-weight: 900;">วันที่ / Date______/_____/_____</div>
        </div>
        <div class="signature-box">
            <div style="height: 22px;"></div>
            <div class="signature-line" style="margin-top: 30px;"></div>
            <div class="signature-title">ผู้ส่งสินค้า / Send By</div>
            <div style="text-align: center; font-size: 12px; font-weight: 900;">วันที่ / Date______/_____/_____</div>
        </div>
        <div class="signature-box">
            <div style="height: 22px;"></div>
            <div class="signature-line" style="margin-top: 30px;"></div>
            <div class="signature-title">ผู้มีอำนาจลงนาม / Authorized Signature</div>
            <div style="text-align: center; font-size: 12px; font-weight: 900;">วันที่ / Date______/_____/_____</div>
        </div>
    </div>
</div>