<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Invoice */

$this->title = 'พิมพ์ใบวางบิล - ' . $model->invoice_number;

// Optimized CSS for single A4 page with multiple copies
$this->registerCss("
@page {
    size: A4;
    margin: 0.4in 0.5in;
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
        font-family: 'TH SarabunPSK', Arial, sans-serif; 
        font-size: 13px;
        color: #000;
        line-height: 1.2;
    }
    .print-container { 
        max-width: 0 auto; 
        margin: 0 auto;
        width: 100%;
        box-shadow: none;
        border: none;
        min-height: 280mm;
        display: flex;
        flex-direction: column;
        position: relative;
        page-break-after: always;
    }
    .print-container:last-child {
        page-break-after: auto;
    }
    .copy-watermark {
        display: none !important;
    }
}

.print-container {
    max-width: 0 auto;
    margin: 0 auto;
    background: white;
    padding: 15px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    min-height: 280mm;
    display: flex;
    flex-direction: column;
    position: relative;
    margin-bottom: 20px;
}

.copy-watermark {
    display: none;
}

/* Header Section - Compact */
.header-section {
    border-bottom: 2px solid #000;
    padding-bottom: 10px;
    margin-bottom: 15px;
    position: relative;
    z-index: 2;
}

.header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.company-logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo {
    max-width: 80px;
    height: auto;
}

.company-info {
    text-align: right;
    flex: 1;
    margin-left: 20px;
}

.company-name {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 3px;
}

.company-address {
    font-size: 11px;
    line-height: 1.2;
    margin-bottom: 2px;
}

.company-contact {
    font-size: 11px;
    font-weight: bold;
}

.bill-title-section {
    text-align: center;
    margin: 8px 0;
    position: relative;
}

.bill-title {
    font-size: 18px;
    font-weight: bold;
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

/* Customer Section - Compact */
.customer-section {
    border: 1px solid #000;
    padding: 10px;
    margin-bottom: 15px;
    position: relative;
    z-index: 2;
}

.customer-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.customer-left {
    flex: 1;
}

.customer-right {
    min-width: 220px;
}

.customer-field {
    margin-bottom: 6px;
    display: flex;
    font-size: 12px;
}

.customer-field strong {
    min-width: 110px;
    font-weight: bold;
}

.customer-field span {
    flex: 1;
    border-bottom: none;
    padding-left: 5px;
}

.bill-detail-field {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 5px;
    font-size: 12px;
}

.bill-detail-field strong {
    min-width: 120px;
    font-weight: bold;
}

/* Items Section - Optimized height */
.items-section {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
    position: relative;
    z-index: 2;
}

.items-title {
    font-weight: bold;
    margin-bottom: 8px;
    font-size: 13px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
    flex: 1;
}

.items-table th,
.items-table td {
    border: 1px solid #000;
    padding: 2px 3px;
    text-align: center;
    vertical-align: middle;
    font-size: 11px;
    line-height: 1.1;
}

.items-table th {
    background-color: #f8f9fa;
    font-weight: bold;
    height: 22px;
}

.items-table td {
    height: 18px;
}

.items-table .text-left { text-align: left; }
.items-table .text-right { text-align: right; }

/* Total Section */
.total-row {
    background-color: #f0f0f0;
    font-weight: bold;
}

.total-amount {
    font-size: 14px;
    font-weight: bold;
}

/* Signature Section - Fixed at bottom */
.signature-section {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    padding-top: 20px;
    position: relative;
    z-index: 2;
}

.signature-box {
    width: 45%;
    text-align: center;
}

.signature-label {
    font-weight: bold;
    margin-bottom: 15px;
    font-size: 12px;
}

.signature-line {
    border-bottom: 1px solid #000;
    height: 40px;
    margin-bottom: 8px;
}

.signature-date {
    font-size: 11px;
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

.btn-success {
    background-color: #28a745;
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

/* Responsive adjustments */
@media screen and (max-width: 768px) {
    .header-row {
        flex-direction: column;
        text-align: center;
    }
    
    .customer-row {
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
    
    // Create 2 copies
    for (let i = 1; i <= 2; i++) {
        const copy = originalContainer.cloneNode(true);
        copy.classList.remove('original');
        copy.classList.add('print-copy');
        
        // Add copy label next to bill title
        const billTitleSection = copy.querySelector('.bill-title-section');
        if (billTitleSection) {
            // Remove existing label if any
            const existingLabel = billTitleSection.querySelector('.copy-label');
            if (existingLabel) {
                existingLabel.remove();
            }
            
            const copyLabel = document.createElement('div');
            copyLabel.className = 'copy-label copy';
            copyLabel.textContent = 'สำเนา';
            billTitleSection.appendChild(copyLabel);
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
    <div class="btn-group">
        <button onclick="window.printMultipleCopies()" class="btn btn-primary btn-print">
            <i class="fas fa-print"></i> พิมพ์ 3 ใบ (ต้นฉบับ + สำเนา 2 222ใบ)
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> ปิด
        </button>
        <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-success">
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

<div class="print-container original">
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-row">
            <div class="company-logo">
                <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png', [
                    'class' => 'logo',
                    'alt' => 'Company Logo'
                ]) ?>
            </div>
            <div class="company-info">
                <div class="company-name">บริษัท เอ็ม.ซี.โอ. จำกัด</div>
                <div class="company-address">
                    8/18 ถนนเกาะกลอย ตำบลเชิงเนิน อำเภอเมืองจังหวัดระยอง 21000
                </div>
                <div class="company-contact">
                    Tel : (038) 875258-9, Fax : (038) 619559
                </div>
            </div>
        </div>
        <div class="bill-title-section">
            <div class="bill-title">ใบวางบิล</div>
            <div class="copy-label original">ต้นฉบับ</div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-section">
        <div class="customer-row">
            <div class="customer-left">
                <div class="customer-field">
                    <strong>ชื่อลูกค้า:</strong>
                    <span><?= Html::encode($model->customer_name ?: '') ?></span>
                </div>
                <div class="customer-field">
                    <strong>ที่อยู่:</strong>
                    <span><?= Html::encode($model->customer_address ?: '') ?></span>
                </div>
                <div class="customer-field">
                    <strong>เลขประจำตัวผู้เสียภาษี:</strong>
                    <span><?= Html::encode($model->customer_tax_id ?: '') ?></span>
                </div>
            </div>
            <div class="customer-right">
                <div class="bill-detail-field">
                    <strong>เลขที่ใบวางบิล:</strong>
                    <span><?= Html::encode($model->invoice_number) ?></span>
                </div>
                <div class="bill-detail-field">
                    <strong>วันที่ใบวางบิล:</strong>
                    <span><?= Yii::$app->formatter->asDate($model->invoice_date, 'dd-MMM-yy') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="items-section">
        <div class="items-title">ดังรายการต่อไปนี้</div>
        <table class="items-table">
            <thead>
            <tr>
                <th style="width: 6%;">ลำดับที่</th>
                <th style="width: 22%;">หมายเลขใบสั่งซื้อ</th>
                <th style="width: 18%;">เลขที่เอกสารตั้งหนี้</th>
                <th style="width: 14%;">ลงวันที่</th>
                <th style="width: 14%;">ชำระเงินวันที่</th>
                <th style="width: 16%;">จำนวนเงิน</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $model_line = \backend\models\InvoiceItem::find()->where(['invoice_id' => $model->id])->all();
            $max_rows = 15; // Maximum rows to fit on one page
            ?>
            <?php if (!empty($model_line)): ?>
                <?php foreach (array_slice($model_line, 0, $max_rows) as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td class="text-left"><?= Html::encode($model->po_number ?: 'RY-UJ24-0002634 Signed') ?></td>
                        <td><?= Html::encode($model->invoice_number) ?></td>
                        <td><?= Yii::$app->formatter->asDate($model->invoice_date, 'MM/dd/yyyy') ?></td>
                        <td><?= $model->due_date ? Yii::$app->formatter->asDate($model->due_date, 'MM/dd/yyyy') : '' ?></td>
                        <td class="text-right"><?= number_format($item->amount, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Fill remaining rows to maintain table structure -->
            <?php
            $filled_rows = !empty($model_line) ? min(count($model_line), $max_rows) : 1;
            for ($i = $filled_rows; $i < $max_rows; $i++):
                ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php endfor; ?>
            </tbody>
            <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-left">
                    <strong>รวมเงินทั้งสิ้น  <?= $model->total_amount_text ?: '' ?></strong>
                </td>
                <td class="text-right total-amount"><?= number_format($model->total_amount, 2) ?></td>
            </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">ผู้รับวางบิล</div>
            <div class="signature-line"></div>
            <div class="signature-date">
                วันที่ _____ / _____ / _______
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-label">ผู้วางบิล</div>
            <div class="signature-line"></div>
            <div class="signature-date">
                วันที่ _____ / _____ / _______
            </div>
        </div>
    </div>
</div>

<script>
    // Sample data for dynamic generation
    const sampleBillData = {
        billNumber: '<?= Html::encode($model->invoice_number) ?>',
        billDate: '<?= Yii::$app->formatter->asDate($model->invoice_date, 'MM/dd/yyyy') ?>',
        customer: {
            name: '<?= Html::encode($model->customer_name) ?>',
            address: '<?= Html::encode($model->customer_address) ?>',
            taxId: '<?= Html::encode($model->customer_tax_id) ?>'
        },
        items: [
            <?php if (!empty($model_line)): ?>
            <?php foreach (array_slice($model_line, 0, 15) as $index => $item): ?>
            {
                seq: <?= $index + 1 ?>,
                orderNumber: '<?= Html::encode($model->po_number ?: '') ?>',
                documentNumber: '<?= Html::encode($model->invoice_number) ?>',
                documentDate: '<?= Yii::$app->formatter->asDate($model->invoice_date, 'MM/dd/yyyy') ?>',
                paymentDate: '<?= $model->due_date ? Yii::$app->formatter->asDate($model->due_date, 'MM/dd/yyyy') : '' ?>',
                amount: <?= $item->amount ?>
            }<?= $index < min(count($model_line), 15) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
            <?php endif; ?>
        ],
        totalAmount: <?= $model->total_amount ?>,
        paymentDueDate: '<?= $model->payment_due_date ? Yii::$app->formatter->asDate($model->payment_due_date, 'MM/dd/yyyy') : '' ?>',
        checkDueDate: '<?= $model->check_due_date ? Yii::$app->formatter->asDate($model->check_due_date, 'MM/dd/yyyy') : '' ?>'
    };

    // Function to populate data dynamically
    function populateData(data) {
        console.log('Bill data:', data);
    }

    // Initialize with current data
    populateData(sampleBillData);
</script>