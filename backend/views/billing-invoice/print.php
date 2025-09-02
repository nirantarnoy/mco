<?php
// backend/views/billing-invoice/print.php
use yii\helpers\Html;

$this->title = 'พิมพ์ใบวางบิล - ' . $model->billing_number;

// เพิ่ม CSS สำหรับการพิมพ์หลายสำเนาและ progress bar
$this->registerCss("
@page {
    size: A4 portrait;
    margin: 1.5cm;
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
        font-family: 'Sarabun', 'THSarabunNew', Arial, sans-serif !important; 
        font-size: 12px !important;
        color: #000 !important;
    }
    .print-container { 
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

body {
    font-family: 'Sarabun', 'THSarabunNew', Arial, sans-serif;
    font-size: 12px;
    line-height: 1.4;
    margin: 0;
    padding: 0;
    color: #000;
    background: #fff;
}

.print-container {
    max-width: 210mm;
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

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
    border-bottom: 1px solid #ccc;
    padding-bottom: 15px;
}

.mco-logo {
    font-size: 36px;
    font-weight: bold;
    color: #333;
    font-family: 'Arial Black', Arial, sans-serif;
    letter-spacing: 2px;
}

.company-details {
    text-align: right;
    font-size: 11px;
    line-height: 1.3;
}

.company-details h1 {
    font-size: 16px;
    margin: 0 0 8px 0;
    font-weight: bold;
    color: #333;
}

.invoice-title-section {
    text-align: center;
    position: relative;
    margin: 25px 0;
}

.invoice-title {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

/* Copy Label Styles - เหมือนกับใบกำกับภาษี */
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

.billing-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 25px;
}

.customer-details {
    width: 65%;
}

.billing-numbers {
    width: 30%;
}

.customer-details table {
    width: 100%;
    font-size: 12px;
}

.customer-details td {
    padding: 3px 0;
    vertical-align: top;
}

.customer-details td:first-child {
    width: 80px;
    font-weight: bold;
}

.number-box {
    background-color: #f8f8f8;
    border: 1px solid #ddd;
    padding: 12px;
    margin-bottom: 8px;
    text-align: center;
    border-radius: 3px;
}

.number-box .label {
    font-weight: bold;
    font-size: 11px;
    margin-bottom: 5px;
}

.number-box .value {
    font-size: 14px;
    font-weight: bold;
    color: #333;
}

.section-header {
    background-color: #e8e8e8;
    border: 1px solid #999;
    padding: 10px;
    text-align: center;
    font-weight: bold;
    font-size: 14px;
    margin: 25px 0 0 0;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    margin-bottom: 20px;
}

.items-table th {
    background-color: #e8e8e8;
    border: 1px solid #333;
    padding: 8px 4px;
    text-align: center;
    font-weight: bold;
    font-size: 10px;
    line-height: 1.2;
}

.items-table td {
    border: 1px solid #333;
    padding: 8px 4px;
    text-align: center;
    vertical-align: middle;
}

.items-table .text-left {
    text-align: left;
}

.items-table .text-right {
    text-align: right;
}

.items-table .big-number {
    font-size: 28px;
    font-weight: bold;
    color: #666;
    padding: 20px 4px;
}

.total-section {
    margin: 25px 0;
    text-align: right;
}

.total-box {
    display: inline-block;
    border-top: 2px solid #333;
    border-bottom: 2px solid #333;
    padding: 12px 20px;
    font-size: 16px;
    font-weight: bold;
    min-width: 200px;
}
.total-box2 {
    display: inline-block;
    border-top: 2px solid #333;
    padding: 12px 20px;
    font-size: 16px;
    font-weight: bold;
    min-width: 200px;
}

.signature-section {
    display: flex;
    justify-content: space-between;
    margin-top: 60px;
}

.signature-block {
    width: 45%;
    text-align: center;
}

.signature-line {
    border-bottom: 1px solid #333;
    height: 60px;
    margin-bottom: 8px;
    position: relative;
}

.signature-name {
    position: absolute;
    bottom: 10px;
    right: 10px;
    font-style: italic;
    font-size: 14px;
    color: #666;
}

.signature-label {
    font-weight: bold;
    margin-bottom: 15px;
    font-size: 12px;
}

.signature-date {
    margin-top: 15px;
    font-size: 11px;
}

/* Print controls */
.print-controls {
    margin-bottom: 20px;
    text-align: center;
    padding: 10px;
    background: #f0f0f0;
    border-radius: 5px;
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

/* Responsive adjustments */
@media screen and (max-width: 768px) {
    .header-section {
        flex-direction: column;
        text-align: center;
    }
    
    .billing-info {
        flex-direction: column;
    }
    
    .customer-details, .billing-numbers {
        width: 100%;
    }
}
");

// JavaScript สำหรับการพิมพ์หลายสำเนา - เหมือนกับใบกำกับภาษี
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
        
        // Add copy label next to billing title
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
    <div class="header-section">
        <div class="mco-logo">
            <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png', ['style' => 'max-width: 120px;']) ?>
        </div>
        <div class="company-details">
            <h1>บริษัท เอ็ม.ซี.โอ. จำกัด</h1>
            <p>8/18 ถนนเกาะกลอย ตำบลเชิงเนิน อำเภอเมือง จังหวัดระยอง 21000</p>
            <p><strong>Tel :</strong> (038) 875258-9, &nbsp; <strong>Fax :</strong> (038) 619559</p>
        </div>
    </div>

    <div class="invoice-title-section">
        <div class="invoice-title">ใบวางบิล</div>
    </div>

    <div class="billing-info">
        <div class="customer-details">
            <table>
                <tr>
                    <td><strong>ชื่อลูกค้า</strong></td>
                    <td>
                        <strong><?= Html::encode($model->customer->name ?? 'บริษัท ส.สิริเสถ จำกัด (สำนักงานใหญ่)') ?></strong>
                    </td>
                </tr>
                <tr>
                    <td><strong>ที่อยู่</strong></td>
                    <td>
                        <?= Html::encode($model->customer->address ?? '140 ถ.วิภาวดีรังสิต แขวงดินแดง เขตดินแดง') ?><br>
                        <?= Html::encode($model->customer->tax_id ?? 'กรุงเทพมหานคร 10400 TAXID 0105520017611.') ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="billing-numbers">
            <div class="number-box">
                <div class="label">เลขที่ใบวางบิล</div>
                <div class="value"><?= Html::encode($model->billing_number) ?></div>
            </div>
            <div class="number-box">
                <div class="label">วันที่ใบวางบิล</div>
                <div class="value"><?= Yii::$app->formatter->asDate($model->billing_date, 'php:j/n/y') ?></div>
            </div>
        </div>
    </div>

    <table class="items-table">
        <thead>
        <tr>
            <th style="width: 8%;">ลำดับที่</th>
            <th style="width: 18%;">หมายเลขใบสั่งซื้อ</th>
            <th style="width: 18%;">เลขที่เอกสารตั้งหนี้</th>
            <th style="width: 12%;">ลงวันที่</th>
            <th style="width: 12%;">วันที่ครบกำหนดชำระ</th>
            <th style="width: 15%;">จำนวนเงิน</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $totalAmount = 0;
        $itemCount = 0;

        // แสดงรายการจริงจากฐานข้อมูล
        foreach ($model->billingInvoiceItems as $index => $item):
            $itemCount++;
            $totalAmount += $item->amount;
            $invoice = $item->invoice; // ดึงข้อมูล invoice ที่เกี่ยวข้อง
            ?>
            <tr>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;"><?= $itemCount ?></td>
                <td class="text-left"
                    style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;"><?= Html::encode($invoice->invoice_number ?? '-') ?></td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;"><?= Html::encode($invoice->invoice_number ?? '-') ?></td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;"><?= Yii::$app->formatter->asDate($invoice->invoice_date ?? $model->billing_date, 'php:j/n/y') ?></td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;"><?= Yii::$app->formatter->asDate($invoice->payment_due_date ?? $model->payment_due_date ?? date('Y-m-d', strtotime($model->billing_date . ' +30 days')), 'php:j/n/y') ?></td>
                <td class="text-right"
                    style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;"><?= number_format($item->amount, 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <?php
        // ถ้าไม่มีข้อมูลให้แสดงข้อความว่าง
        if ($itemCount == 0):
            $totalAmount = $model->total_amount;
            ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #999; font-style: italic;">
                    ไม่มีรายการใบกำกับภาษี
                </td>
            </tr>
        <?php endif; ?>

        <?php
        // เติมแถวว่าง
        $emptyRows = 22 - $itemCount;
        if ($itemCount == 0) $emptyRows = 14;

        for ($i = 0; $i < $emptyRows; $i++):
            ?>
            <?php if ($i<$emptyRows-1): ?>
            <tr style="height: 35px;">
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">
                    &nbsp;
                </td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">
                    &nbsp;
                </td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">
                    &nbsp;
                </td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">
                    &nbsp;
                </td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">
                    &nbsp;
                </td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">
                    &nbsp;
                </td>
            </tr>
        <?php else: ?>
            <tr style="height: 35px;">
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; padding:8px;">
                    &nbsp;
                </td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; padding:8px;">
                    &nbsp;
                </td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; padding:8px;">
                    &nbsp;
                </td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; padding:8px;">
                    &nbsp;
                </td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000;  padding:8px;">
                    &nbsp;
                </td>
                <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; padding:8px;">
                    &nbsp;
                </td>
            </tr>
        <?php endif; ?>
        <?php endfor; ?>
        </tbody>
    </table>

    <div class="total-section">
        <table style="width: 100%">
            <tr>
                <td style="text-align: left;font-weight: bold;font-size: 16px">
                    <u>รวมเงินทั้งสิ้น</u> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?= \backend\models\PurchReq::numtothai($model->total_amount) ?>
                </td>
                <td style="text-align: right">
                    <div class="total-box">
                        <?= number_format($totalAmount > 0 ? $totalAmount : $model->total_amount, 2) ?>
                    </div>
                    <div style="height: 5px;"></div>
                    <div class="total-box2" style="top: 15px;"></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="signature-section">
        <table style="width: 100%">
            <tr>
                <td style="width: 50%">
                    <table style="width: 100%">
                        <tr>
                            <td style="width: 80%;font-size: 14px;padding:10px;text-align: left">
                                <u>ผู้รับวางบิล</u> <span> ..................................................................</span>
                            </td>
                            <td style="text-align: left;"></td>
                        </tr>
                        <tr>
                            <td style="width: 80%;font-size: 14px;text-align: left">
                                <u>วันนัดรับเช็ค</u> <span> ..................................................................</span>
                            </td>
                            <td style="text-align: left"></td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%">
                    <table style="width: 100%">
                        <tr>
                            <td style="width: 80%;font-size: 14px">
                                <u>ผู้วางบิล</u> <span> ..................................................................</span>
                            </td>
                            <td style="text-align: left"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>