<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Invoice */

$this->title = 'พิมพ์ใบกำกับภาษี - ' . $model->invoice_number;

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
    .main-footer,
    .main-header,
    .main-sidebar,
    .content-wrapper .content-header { 
        display: none !important; 
    }
    body { 
        margin: 0 !important; 
        padding: 0 !important; 
        font-family: 'THSarabunPSK', sans-serif !important;
        font-size: 20px !important;
        color: #000 !important;
    }
    .print-container { 
        font-family: 'THSarabunPSK', sans-serif !important;
        max-width: none !important; 
        box-shadow: none !important; 
        border: none !important; 
        page-break-after: always !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    .print-container:last-child {
        page-break-after: auto !important;
    }
    .copy-watermark {
        display: none !important;
    }
    * {
        box-sizing: border-box !important;
    }
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Sarabun', 'TH SarabunPSK', Arial, sans-serif;
    font-size: 20px;
    line-height: 1.3;
    color: #000;
    background: #fff;
}

.print-container {
    font-family: 'THSarabunPSK', sans-serif !important;
    max-width: 0 auto;
    margin: 0 auto;
    background: white;
    padding: 15px;
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
    padding-bottom: 10px;
}

.company-logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo {
    width: 70px;
    height: 70px;
    background: linear-gradient(45deg, #d32f2f, #f57c00);
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
    // margin-left: 12px;
}

.company-name-thai {
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 2px;
    margin-left: -36px
}

.company-name-eng {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 3px;
    margin-left: 80px
}

.company-address {
    font-size: 12px;
    line-height: 1.2;
    margin-bottom: 2px;
}

.invoice-info {
    text-align: center;
    min-width: 180px;
}

.invoice-title {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 5px;
    position: relative;
}

.invoice-subtitle {
    font-size: 16px;
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
    margin: 15px 0;
    gap: 20px;
}

.customer-left, .customer-right {
    flex: 1;
    line-height: 1.7;
}

.field-group {
    display: flex;
    align-items: flex-start; /* ให้กลุ่มอยู่บน */
}

.field-value {
    display: block; 
    margin-top: 0; 
    padding-top: 0;
    margin-left: 10px;
}



.field-label {
    min-width: 100px;
    font-weight: bold;
}


/* Items Table */
.items-section {
    margin: 20px 0;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 16px;
    // border: 1px solid #000; /* เส้นรอบนอกเท่านั้น */
}

/* Header: ให้มีเฉพาะเส้นบนและเส้นล่าง */
.items-table thead th {
    border-top: 1px solid #000;
    border-bottom: 1px solid #000;

    /* ❗ สำคัญ: ห้ามมี border-left/right มิฉะนั้นจะซ้อนกับ tbody */
    border-left: none !important;
    border-right: none !important;

    background-color: #f8f9fa;
    font-weight: bold;
    padding: 6px 4px;
}

/* ป้องกัน tr ของ thead สร้างเส้นเอง */
.items-table thead tr {
    border: none !important;
}

/* Tbody: ไม่มีเส้นแนวนอนเลย */
.items-table tbody td {
    padding: 4px 6px;

    border-top: none !important;
    border-bottom: none !important;

}

/* ❗ แถวสุดท้ายก่อน footer ยังไม่ต้องมีเส้นล่าง */
.items-table tbody tr:last-child td {
    border-bottom: none !important;
}


.items-table .text-left { text-align: left; padding-left: 8px; }
.items-table .text-right { text-align: right; padding-right: 8px; }

/* Empty rows */
.empty-row {
    height: 40px;
}

/* Summary Section */
.summary-section {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.summary-left {
    flex: 1;
    border: 1px solid #000;
    padding: 12px;
    height: fit-content;
}

.summary-right {
    width: 300px;
    height: fit-content;
    border: 1px solid #000;
    padding: 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 10px;
    // border-bottom: 1px solid #000;
    font-size: 16px;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    font-weight: bold;
    background-color: #f0f0f0;
}

.amount-text {
    margin-top: 5px;
    font-size: 16px;
}

/* Notes Section */
.notes-section {
    margin: 15px 0;
    font-size: 16px;
    line-height: 1.4;
}

.notes-title {
    font-weight: bold;
    margin-bottom: 5px;
}

.note-item {
    margin-bottom: 3px;
    text-indent: 15px;
}

/* Signature Section */
.signature-section {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.signature-box {
    flex: 1;
    border: 1px solid #000;
    padding: 15px 10px;
    text-align: center;
    height: 120px;
    position: relative;
}

.signature-title {
    font-weight: bold;
    margin-bottom: 10px;
    font-size: 12px;
}

.signature-line {
    border-bottom: 1px solid #000;
    margin: 40px 10px 10px;
}

.signature-date {
    font-size: 12px;
    position: absolute;
    bottom: 2px;
    padding-top: 25px;
    left: 50%;
    transform: translateX(-50%);
}

/* Print controls */
.print-controls {
    margin-bottom: 20px;
    text-align: center;
}

.btn {
    padding: 10px 20px;
    margin: 0 5px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
}

.btn-primary { background-color: #007bff; color: white; }
.btn-success { background-color: #28a745; color: white; }
.btn-secondary { background-color: #6c757d; color: white; }
.btn:hover { opacity: 0.8; }

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
    .field-label-group {
    width: 80px; /* กำหนดความกว้าง label ทั้งก้อน */
}

/* Utilities */
.text-center { text-align: center; }
.text-right { text-align: right; }
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
        printBtn.innerHTML = '⏳ กำลังพิมพ์...';
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
        printBtn.innerHTML = '🖨️ พิมพ์ 3 ใบ';
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

<div class="print-controls no-print">
    <button onclick="window.printMultipleCopies()" class="btn btn-primary btn-print">
        🖨️ พิมพ์ 3 ใบ (ต้นฉบับ + สำเนา 2 ใบ)
    </button>
    <button onclick="window.close()" class="btn btn-secondary">
        ❌ ปิด
    </button>
    <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-success">
        👁️ ดูรายละเอียด
    </a>

    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        <div class="progress-text">เตรียมพิมพ์...</div>
    </div>
</div>

<div class="print-container original">
    <!-- Header -->
    <div class="header">
        <div class="company-logo">
            <div class="logox">
                <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png', ['style' => 'max-width: 180px;']) ?>
            </div>
            <div class="company-info">
                <div class="company-name-thai">บริษัท เอ็ม. ซี. โอ. จำกัด (สำนักงานใหญ่)</div>
                <div class="company-name-eng">M. C. O. COMPANY LIMITED</div>
                <div class="company-address" style="margin-left:80px;">
                    8/18 ถ.เกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000 โทร 66-(0)-38875258-59 แฟ๊กซ์ 66-(0)-3861-9559
                </div>
                <div class="company-address" style="margin-left: 80px;">
                    8/18 Koh-Kloy-Rd., Cherngnoen, Muang, Rayong 21000 Tel. 66-(0)3887-5258-59 Fax. 66-(0)3861-9559
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12" style="text-align: center">
            <div class="invoice-title-section">
                <div class="invoice-title">ใบกำกับภาษี</div>
                <div class="invoice-subtitle">Tax Invoice</div>
            </div>
        </div>
    </div>

    <?php
    $customer_code = '';
    $po_no = '';
    $po_date  = null;
    $job_id = 0;
    $quotation_data = \backend\models\Quotation::find()->where(['id' => $model->quotation_id])->one();
    if ($quotation_data != null) {
        $customer_code = \backend\models\Customer::findCode($quotation_data->customer_id);
    }

    $po_no = $model->po_number;
    $po_date = $model->po_date;
    ?>

    <!-- Customer Information -->
    <div class="customer-section">
        <table>
            <td>
                <div class="customer-left">
                    <div class="field-group">
                        <span class="field-label">เลขประจำตัวผู้เสียภาษี:</span>
                        <span class="field-value">0215543000985</span>
                    </div>
                    <div class="field-group">
                        <div class="field-label-group">
                            <div class="field-label">รหัสลูกค้า :</div>
                            <div class="field-label">Code</div>
                        </div>
                        <span class="field-value">
                            <?= $customer_code ?>
                        </span>
                    </div>
                    <div class="field-group">
                        <div class="field-label-group">
                            <div class="field-label">ขายให้ :</div>
                            <div class="field-label">Sold To</div>
                        </div>
                        <span class="field-value">
                            <?= Html::encode($model->customer_name ?: '') ?><br>
                            <?= Html::encode($model->customer_address ?: '') ?><br>
                            TAX ID: <?= Html::encode($model->customer_tax_id ?: '') ?>
                        </span>
                    </div>
                </div>
            </td>
            <td>
                <div class="customer-right" style="margin-left: 80px;">
                    <div class="field-group">
                        <span class="field-label">วันที่ / Date:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <span class="field-value-right"><?= Yii::$app->formatter->asDate($model->invoice_date, 'MM/dd/yyyy') ?></span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">เลขที่ / Inv.No.:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <span class="field-value-right"><?= Html::encode($model->invoice_number) ?></span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">ใบสั่งซื้อเลขที่ / P/O No.:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <span class="field-value-right"><?= Html::encode($po_no ?: '') ?></span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">วันที่สั่งซื้อ / P/O Date:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <span class="field-value-right"><?= $po_date ? Yii::$app->formatter->asDate($po_date, 'MM/dd/yyyy') : '' ?></span>
                    </div>
                    <div class="field-group">
                        <span class="field-label">เงื่อนไข / กำหนดชำระ / Credit, Due:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <span class="field-value-right"><?= Html::encode($model->paymentTerm ? $model->paymentTerm->name : '') ?> <?= $model->due_date ? Yii::$app->formatter->asDate($model->due_date, 'd/M/yy') : '' ?></span>
                    </div>
                </div>
            </td>
        </table>

    </div>

    <!-- Items Table -->
    <div class="items-section">
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 8%;border-left: 1px solid #000;text-align:center"">ลำดับ<br>Item</th>
                    <th style=" width: 40%;text-align:center"">รายการ<br>Description</th>
                    <th style="width: 12%;text-align:center"">จำนวน<br>Quantity</th>
                    <th style=" width: 15%;text-align:center"">ราคาต่อหน่วย<br>Unit/Price</th>
                    <th style="width: 15%;border-right: 1px solid #000;text-align:center"">จำนวนเงินรวม<br>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $model_line = \backend\models\InvoiceItem::find()->where(['invoice_id' => $model->id])->all();
                ?>
                <?php if (!empty($model_line)): ?>
                    <?php foreach ($model_line as $index => $item): ?>
                        <tr>
                            <td style=" padding:8px;text-align:center"><?= $index + 1 ?></td>
                    <td class="text-left" style=" padding:8px;"><?= Html::encode($item->item_description) ?></td>
                    <td style="padding:8px;text-align:center""><?= number_format($item->quantity, 0) ?> <?= Html::encode(\backend\models\Unit::findName($item->unit_id)) ?></td>
                            <td class=" text-right" style="padding:8px;"><?= number_format($item->unit_price, 3) ?></td>
                    <td class="text-right" style="padding:8px;"><?= number_format($item->amount, 3) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>

        <?php endif; ?>

        <!-- Empty rows for spacing -->
        <?php for ($i = count($model_line); $i < 12; $i++): ?>
            <tr class="empty-row">
                <td style="padding:8px;">&nbsp;</td>
                <td style="padding:8px;">&nbsp;</td>
                <td style="padding:8px;">&nbsp;</td>
                <td style="padding:8px;">&nbsp;</td>
                <td style="padding:8px;">&nbsp;</td>
            </tr>
        <?php endfor; ?>
        </tbody>
        </table>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-left" style="border: 1px solid gray;">
            <div class="font-bold">ตัวอักษร</div>
            <div style="display:flex; justify-content:flex-end; gap:10px; text-align:right;">
                <span style="margin-right: 15px;">Discount</span>
                <span><?= number_format($model->discount_amount, 2) ?></span>
            </div>
            <br>
            <div class="amount-text" style="text-align:left;">
                <h6><?= $model->total_amount_text ?: '' ?></h6>
            </div>
            <br>
            <br>
        </div>
        <div class="summary-right">

            <div class="summary-row">
                <span>รวมเงิน<br>Total</span>
                <span><?= number_format($model->subtotal, 2) ?></span>
            </div>

            <div class="summary-row">
                <span>ภาษีมูลค่าเพิ่ม<br>VAT <?= $model->vat_percent ?>%</span>
                <span><?= number_format($model->vat_amount, 2) ?></span>
            </div>

            <div class="summary-row total">
                <span>รวมเงินทั้งสิ้น<br>TOTAL</span>
                <span><?= number_format($model->total_amount, 2) ?></span>
            </div>

        </div>
    </div>

    <!-- Notes Section -->
    <div class="notes-section">
        <div class="notes-title">หมายเหตุ :</div>
        <div class="note-item">1. ตามรายการข้างต้น แม้จะได้ส่งมอบสินค้าแก่ผู้ซื้อแล้วก็ยังเป็นทรัพย์สินของบริษัทฯ จนกว่าจะได้รับชำระเงินครบถ้วน</div>
        <div class="note-item">2. สินค้าที่ซื้อไปเกินกว่า 7 วัน ทางบริษัทฯ ใคร่ขอสงวนสิทธิ์ไม่รับคืนสินค้า และคิดดอกเบี้ยร้อยละ 1.5 ต่อเดือน</div>
        <div class="note-item">3. สามารถชำระผ่านช่องทางธนาคารกรุงเทพจำกัด (มหาชน) สาขาระยอง ชื่อบัญชี บจ.เอ็ม.ซี.โอ. เลขบัญชี 277-3-02318-5 บัญชีกระแสรายวัน</div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-title">ได้ตรวจรับสินค้าตามรายการข้างต้นถูกต้อง</div>
            <div class="signature-line"></div>
            <div class="signature-title">ผู้รับสินค้า / Received By</div>
            <div class="signature-date">วันที่/Date ____/_____/_____</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">&nbsp;</div>
            <div class="signature-line"></div>
            <div class="signature-title">ผู้ส่งสินค้า / Delivery By</div>
            <div class="signature-date">วันที่/Date ____/_____/_____</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">&nbsp;</div>
            <div class="signature-line"></div>
            <div class="signature-title">ผู้มีอำนาจลงนาม / Authorized Signature</div><br />
            <div class="signature-date">วันที่/Date ____/_____/_____</div>
        </div>
    </div>