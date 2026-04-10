<?php
// backend/views/invoice/print-bill-placement.php
use yii\helpers\Html;

$this->title = 'พิมพ์ใบวางบิล - ' . $model->invoice_number;

// Copy CSS from billing-invoice/print.php
$this->registerCss("
@page {
    size: A4 portrait;
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
        font-family: 'THSarabunPSK' !important;
        font-size: 16px !important;
        color: #000 !important;
    }
    .print-container { 
        font-family: 'THSarabunPSK' !important;
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
    font-size: 18px;
    line-height: 1.4;
    margin: 0;
    padding: 0;
    color: #000;
    background: #fff;
}

.print-container {
    font-family: 'THSarabunPSK' !important;
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
    margin-bottom: 5px;
}

.mco-logo {
    font-size: 36px;
    font-weight: bold;
    color: #333;
    font-family: 'Arial Black', Arial, sans-serif;
    letter-spacing: 2px;
    max-width: 30%;
}

.company-details {
    flex: 1;
    padding: 2px 12px;
    margin: 0 10px;
    text-align: left;
}

.invoice-title-section {
    font-size: 30px;
    text-align: center;
    position: relative;
    font-weight: bold;
    color: #333;
    -webkit-text-stroke: 0.3px black;
    margin-bottom: 5px;
}

/* Copy Label Styles */
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
    margin-bottom: 10px;
}

.customer-details {
    width: 65%;
}

.billing-numbers {
    margin-left: 30px;
    width: 30%;
    font-size: 18px;
    line-height: 2.5;
    font-weight: 800;
    -webkit-text-stroke: 0.25px black;
}

.customer-details table {
    width: 100%;
    font-size: 18px;
    font-weight: 800;
    -webkit-text-stroke: 0.25px black;
}

.customer-details td {
    padding: 2px 0;
    vertical-align: top;
    word-break: normal;
    overflow-wrap: break-word;
    line-break: normal;
    text-align: left;
}

.customer-details td:first-child {
    width: 80px;
    font-weight: 800;
    -webkit-text-stroke: 0.25px black;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 18px;
    margin-bottom: 20px;
}

.items-table th {
    background-color: #e8e8e8;
    border: 1px solid #333;
    padding: 6px 4px;
    text-align: center;
    font-weight: 800;
    font-size: 18px;
    line-height: 1.2;
    -webkit-text-stroke: 0.25px black;
}

.items-table td {
    border: 1px solid #333;
    padding: 5px 4px;
    text-align: center;
    vertical-align: middle;
    font-size: 18px;
    font-weight: 800;
    -webkit-text-stroke: 0.2px black;
}

.items-table .text-left {
    text-align: left;
}

.items-table .text-right {
    text-align: right;
}

.total-box {
    padding: 12px 20px;
    font-size: 18px;
    font-weight: 800;
    min-width: 200px;
    text-align: right;
    border-bottom: 3px double #000;
    -webkit-text-stroke: 0.25px black;
}

.signature-section {
    justify-content: space-between;
    margin-top: 15px;
    line-height: 2.5;
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

.company-details p {
    margin: 0;
    padding: 0;
    line-height: 1.5;
}
");

// JavaScript for multi-copy printing
$this->registerJs("
// Global variables
let printInProgress = false;
let currentCopy = 0;
const totalCopies = 3;

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
    
    const originalTitleSection = originalContainer.querySelector('.invoice-title-section');
    if (originalTitleSection && !originalTitleSection.querySelector('.copy-label')) {
        const originalLabel = document.createElement('div');
        originalLabel.className = 'copy-label original';
        originalLabel.textContent = 'ต้นฉบับ';
        originalTitleSection.appendChild(originalLabel);
    }
    
    for (let i = 1; i <= 2; i++) {
        const copy = originalContainer.cloneNode(true);
        copy.classList.remove('original');
        copy.classList.add('print-copy');
        
        const titleSection = copy.querySelector('.invoice-title-section');
        if (titleSection) {
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
    
    const printBtn = document.querySelector('.btn-print');
    if (printBtn) {
        printBtn.disabled = true;
        printBtn.innerHTML = '⏳ กำลังพิมพ์...';
    }
    
    window.updateProgress(0, totalCopies);
    window.createPrintCopies();
    
    setTimeout(() => {
        window.print();
    }, 500);
};

window.addEventListener('beforeprint', function() {
    document.body.style.zoom = '1';
    window.updateProgress(1, totalCopies);
});

window.addEventListener('afterprint', function() {
    currentCopy++;
    window.updateProgress(totalCopies, totalCopies);
    
    const printBtn = document.querySelector('.btn-print');
    if (printBtn) {
        printBtn.disabled = false;
        printBtn.innerHTML = '🖨️ พิมพ์ 3 ใบ';
    }
    
    printInProgress = false;
    
    setTimeout(() => {
        const copies = document.querySelectorAll('.print-copy');
        copies.forEach(copy => copy.remove());
        
        const originalLabel = document.querySelector('.copy-label.original');
        if (originalLabel) {
            originalLabel.remove();
        }
    }, 1000);
});
");
?>

<div class="print-controls no-print">
    <!-- Combined Controls Row -->
    <div style="display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap; margin-bottom: 10px;">
        <!-- Language Switcher -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <label for="languageSelect" style="font-weight: bold; margin: 0;">ภาษา / Language:</label>
            <select id="languageSelect" onchange="changeLanguage()" style="padding: 8px 15px; font-size: 14px; border-radius: 4px; border: 1px solid #ccc;">
                <option value="th" selected>ไทย (Thai)</option>
                <option value="en">English</option>
            </select>
        </div>

        <!-- Header Selection -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <label for="headerSelect" style="font-weight: bold; margin: 0;">เลือกหัวบริษัท:</label>
            <select id="headerSelect" onchange="changeHeader()" style="padding: 8px 15px; font-size: 14px; border-radius: 4px; border: 1px solid #ccc;">
                <option value="mco" selected>M.C.O. Company Limited (Default)</option>
                <?php
                $companies = \backend\models\Company::find()->all();
                foreach ($companies as $comp) {
                    if (strtoupper($comp->name) !== 'M.C.O. COMPANY LIMITED') {
                        echo '<option value="' . Html::encode($comp->name) . '">' . Html::encode($comp->name) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <!-- Print Buttons -->
    <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 10px;">
        <div>
            <button onclick="window.printMultipleCopies()" class="btn btn-primary btn-print">
                🖨️ พิมพ์ 3 ใบ (ต้นฉบับ + สำเนา 2 ใบ)
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                ❌ ปิด
            </button>
            <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-success">
                👁️ ดูรายละเอียด
            </a>
        </div>
    </div>

    <div class="progress-container">
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        <div class="progress-text">เตรียมพิมพ์...</div>
    </div>
</div>

<div class="print-container original">
    <div class="header-section" style="display: flex; align-items: center; justify-content: space-between;">
        <div class="mco-logo" style="min-width: 180px;">
            <img id="companyLogo" src="../../backend/web/uploads/logo/mco_logo_2.png" style="max-width: 180px;" alt="">
        </div>
        <div class="company-details" style="text-align: left;">
            <div id="companyNameHeader" style="font-size: 28px; font-weight: 900; -webkit-text-stroke: 0.3px black;">บริษัท เอ็ม. ซี. โอ. จำกัด (สำนักงานใหญ่)</div>
            <div id="companyNameEngHeader" style="font-size: 24px; font-weight: 900; margin-top: -3px; -webkit-text-stroke: 0.3px black;">M.C.O. COMPANY LIMITED</div>
            <div id="companyAddress" style="font-size: 14px; line-height: 1.2; font-weight: 900;">8/18 ถ.เกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000 โทร 66-(0)-38875258-59 แฟ๊กซ์ 66-(0)-3861-9559</div>
        </div>
        <div class="tax-info" style="min-width: 180px; text-align: right; font-weight: bold; font-size: 18px;">
            TAXID: 0215543000985
        </div>
    </div>

    <div class="invoice-title-section">
        <div class="invoice-title">ใบวางบิล</div>
    </div>

    <div class="billing-info">
        <div class="customer-details">
            <table>
                <tr>
                    <td>
                        <strong id="labelCustomerName" style="border-bottom: 1px solid #000;">ชื่อลูกค้า</strong>
                    </td>
                    <td>
                        <strong><?= Html::encode($model->customer_name ?? '') ?></strong>
                    </td>
                </tr>
                <tr>
                    <td><strong id="labelCustomerAddress" style="border-bottom: 1px solid #000;">ที่อยู่</strong></td>
                    <td>
                        <?php
                        // ใช้ AddressHelper ทำความสะอาดที่อยู่
                        $address = \backend\helpers\AddressHelper::cleanAddress($model->customer_address ?: '');
                        echo Html::encode($address);
                        ?><br>
                        <span id="labelTaxId">เลขประจำตัวผู้เสียภาษี</span> <?= Html::encode($model->customer_tax_id ?? '') ?>
                    </td>
                </tr>
            </table>
        </div>

        <div class="billing-numbers">
            <div>
                <div class="label"><span style="font-weight: bold;border-bottom: 1px solid #000;">เลขที่ใบวางบิล</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= Html::encode($model->invoice_number) ?></div>
            </div>
            <div>
                <div class="label" id="billingDateLabel" data-original-date="<?= Yii::$app->formatter->asDate($model->invoice_date, 'php:d/m/Y') ?>"><span style="font-weight: bold;border-bottom: 1px solid #000;">วันที่ใบวางบิล</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= Yii::$app->formatter->asDate($model->invoice_date, 'php:d/m/Y') ?></div>
            </div>
        </div>
    </div>

    <p style="font-size: 14px;"><span style="border-bottom: 1px solid #000; font-weight: 800; -webkit-text-stroke: 0.25px black;">ดังรายการต่อไปนี้</span></p>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%;"><strong style="bold;border-bottom: 1px solid #000;">ลำดับที่</strong></th>
                <th style="width: 18%;"><strong style="bold;border-bottom: 1px solid #000;">หมายเลขใบสั่งซื้อ</strong></th>
                <th style="width: 18%;"><strong style="bold;border-bottom: 1px solid #000;">เลขที่เอกสารตั้งหนี้</strong></th>
                <th style="width: 12%;"><strong style="bold;border-bottom: 1px solid #000;">ลงวันที่</strong></th>
                <th style="width: 12%;"><strong style="bold;border-bottom: 1px solid #000;">นัดชําระเงินวันที่</strong></th>
                <th style="width: 15%;"><strong style="bold;border-bottom: 1px solid #000;">จำนวนเงิน</strong></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $model_line = \backend\models\InvoiceItem::find()->where(['invoice_id' => $model->id])->all();
            $totalAmount = 0;
            $itemCount = 0;
            $max_rows = 20;

            // หา parent invoice จาก invoice_relations
            $relation = \backend\models\InvoiceRelation::find()
                ->where(['child_invoice_id' => $model->id])
                ->one();

            $parentInvoice = null;
            if ($relation) {
                $parentInvoice = \backend\models\Invoice::findOne($relation->parent_invoice_id);
            }

            // ถ้ามี parent invoice ให้แสดงแค่แถวเดียว
            if ($parentInvoice) {
                $itemCount = 1;
                $totalAmount = $parentInvoice->total_amount;
            ?>
                <tr>
                    <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold"><?= $itemCount ?></td>
                    <td class="text-left" style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold"><?= Html::encode($parentInvoice->po_number ?? '-') ?></td>
                    <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold" title="Parent Invoice ID: <?= $relation->parent_invoice_id ?>"><?= Html::encode($parentInvoice->invoice_number) ?></td>
                    <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold"><?= Yii::$app->formatter->asDate($parentInvoice->invoice_date, 'php:d/m/Y') ?></td>
                    <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold"><?= $parentInvoice->due_date ? Yii::$app->formatter->asDate($parentInvoice->due_date, 'php:d/m/Y') : '' ?></td>
                    <td class="text-right" style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold"><?= number_format($parentInvoice->total_amount, 2) ?></td>
                </tr>
                <?php
            } else {
                // ถ้าไม่มี parent invoice ให้แสดงแบบเดิม (แต่ละ item)
                if (!empty($model_line)):
                    foreach (array_slice($model_line, 0, $max_rows) as $index => $item):
                        $itemCount++;
                        $totalAmount += $item->amount;
                ?>
                        <tr>
                            <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold"><?= $itemCount ?></td>
                            <td class="text-left" style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold"><?= Html::encode($model->po_number ?? '-') ?></td>
                            <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold"><?= Html::encode($model->invoice_number) ?></td>
                            <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold"><?= Yii::$app->formatter->asDate($model->invoice_date, 'php:d/m/Y') ?></td>
                            <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold"><?= $model->due_date ? Yii::$app->formatter->asDate($model->due_date, 'php:d/m/Y') : '' ?></td>
                            <td class="text-right" style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;font-weight: bold"><?= number_format($item->amount, 2) ?></td>
                        </tr>
            <?php
                    endforeach;
                endif;
            }
            ?>

            <?php
            // Fill empty rows
            $emptyRows = $max_rows - $itemCount;
            if ($itemCount == 0) $emptyRows = 13;

            for ($i = 0; $i < $emptyRows; $i++):
            ?>
                <?php if ($i < $emptyRows - 1): ?>
                    <tr style="height: 25px;">
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">&nbsp;</td>
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">&nbsp;</td>
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">&nbsp;</td>
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">&nbsp;</td>
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">&nbsp;</td>
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; border-bottom:none; padding:8px;">&nbsp;</td>
                    </tr>
                <?php else: ?>
                    <tr style="height: 25px;">
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; padding:8px;">&nbsp;</td>
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; padding:8px;">&nbsp;</td>
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; padding:8px;">&nbsp;</td>
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; padding:8px;">&nbsp;</td>
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; padding:8px;">&nbsp;</td>
                        <td style="border-top:none; border-left:1px solid #000; border-right:1px solid #000; padding:8px;">&nbsp;</td>
                    </tr>
                <?php endif; ?>
            <?php endfor; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="font-weight: 800; font-size: 18px; padding: 10px; -webkit-text-stroke: 0.25px black;">
                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <div>
                            <?php
                            $grandTotal = ($totalAmount > 0) ? $totalAmount : $model->total_amount;
                            $textThai = \backend\models\PurchReq::numtothai($grandTotal);
                            $textEng = \backend\helpers\NumberToText::convert($grandTotal);
                            ?>
                            <u id="labelTotalText" style="border-bottom: 1px solid #000;font-weight: 800;">รวมเงินทั้งสิ้น</u>&nbsp;&nbsp;
                            <span id="amountText" data-th="<?= Html::encode($textThai) ?>" data-en="<?= Html::encode($textEng) ?>" style="font-weight: 800;"><?= Html::encode($textThai) ?></span>
                        </div>
                        <div class="total-box">
                            <?= number_format($grandTotal, 2) ?>
                        </div>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>

    <br>

    <div class="signature-section">
        <table style="width: 100%;padding: 10px">
            <tr>
                <td style="width: 50%;font-size: 18px;text-align: left;padding-left: 40px;font-weight: 800;-webkit-text-stroke: 0.25px black;">
                    <u>ผู้รับวางบิล</u> <span> ...................................................................</span>
                </td>
                <td style="width: 50%;font-size: 18px;text-align: right;padding-right: 40px;font-weight: 800;-webkit-text-stroke: 0.25px black;">
                    <u>ผู้วางบิล</u> <span> ..................................................................</span>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;font-size: 18px;padding-left: 40px;font-weight: 800;-webkit-text-stroke: 0.25px black;">
                    <u>วันนัดรับเช็ค</u> <span> .................................................................</span>
                </td>
                <td style="text-align: left"></td>
            </tr>
        </table>
    </div>
</div>
<script>
    // ฟังก์ชันแปลงเดือนภาษาอังกฤษเป็นภาษาไทยแบบย่อ
    function convertToThaiMonth(dateStr) {
        const monthMap = {
            'Jan': 'ม.ค.',
            'Feb': 'ก.พ.',
            'Mar': 'มี.ค.',
            'Apr': 'เม.ย.',
            'May': 'พ.ค.',
            'Jun': 'มิ.ย.',
            'Jul': 'ก.ค.',
            'Aug': 'ส.ค.',
            'Sep': 'ก.ย.',
            'Oct': 'ต.ค.',
            'Nov': 'พ.ย.',
            'Dec': 'ธ.ค.'
        };

        // แปลงรูปแบบ 17-Dec-25 เป็น 17-ธ.ค.-25
        return dateStr.replace(/([A-Z][a-z]{2})/g, function(match) {
            return monthMap[match] || match;
        });
    }

    function changeHeader() {
        const headerSelect = document.getElementById('headerSelect');
        const selectedValue = headerSelect.value;

        const companyNameHeader = document.getElementById('companyNameHeader');
        if (companyNameHeader) {
            if (selectedValue === 'mco') {
                companyNameHeader.textContent = 'บริษัท เอ็ม.ซี.โอ. จำกัด (สำนักงานใหญ่)';
            } else {
                companyNameHeader.textContent = selectedValue;
            }
        }
    }

    function changeLanguage() {
        const lang = document.getElementById('languageSelect').value;

        // Title
        const billingTitle = document.querySelector('.invoice-title');
        if (billingTitle) billingTitle.textContent = lang === 'en' ? 'Billing Invoice' : 'ใบวางบิล';

        // Customer Labels
        const labelCustomerName = document.getElementById('labelCustomerName');
        if (labelCustomerName) labelCustomerName.textContent = lang === 'en' ? 'Customer Name' : 'ชื่อลูกค้า';

        const labelCustomerAddress = document.getElementById('labelCustomerAddress');
        if (labelCustomerAddress) labelCustomerAddress.textContent = lang === 'en' ? 'Address' : 'ที่อยู่';

        const labelTaxId = document.getElementById('labelTaxId');
        if (labelTaxId) labelTaxId.textContent = lang === 'en' ? 'Tax ID' : 'เลขประจำตัวผู้เสียภาษี';

        // Billing Labels
        const billingLabels = document.querySelectorAll('.billing-numbers .label span');
        if (billingLabels.length >= 2) {
            billingLabels[0].textContent = lang === 'en' ? 'Billing No.' : 'เลขที่ใบวางบิล';
            billingLabels[1].textContent = lang === 'en' ? 'Billing Date' : 'วันที่ใบวางบิล';
        }

        // List Text
        const listText = document.querySelector('p span[style*="border-bottom"]');
        if (listText) listText.textContent = lang === 'en' ? 'As per the following list' : 'ดังรายการต่อไปนี้';

        // Table Headers
        const tableHeaders = document.querySelectorAll('.items-table thead th strong');
        if (tableHeaders.length >= 6) {
            if (lang === 'en') {
                tableHeaders[0].textContent = 'No.';
                tableHeaders[1].textContent = 'P/O No.';
                tableHeaders[2].textContent = 'Invoice No.';
                tableHeaders[3].textContent = 'Date';
                tableHeaders[4].textContent = 'Due Date';
                tableHeaders[5].textContent = 'Amount';
            } else {
                tableHeaders[0].textContent = 'ลำดับที่';
                tableHeaders[1].textContent = 'หมายเลขใบสั่งซื้อ';
                tableHeaders[2].textContent = 'เลขที่เอกสารตั้งหนี้';
                tableHeaders[3].textContent = 'ลงวันที่';
                tableHeaders[4].textContent = 'นัดชำระเงินวันที่';
                tableHeaders[5].textContent = 'จำนวนเงิน';
            }
        }

        // แปลงวันที่ในตาราง
        const dateCells = document.querySelectorAll('.items-table tbody td');
        dateCells.forEach(cell => {
            // ใช้ original date ที่เก็บไว้
            const originalDate = cell.getAttribute('data-original-date');
            if (originalDate) {
                if (lang === 'th') {
                    cell.textContent = convertToThaiMonth(originalDate);
                } else {
                    cell.textContent = originalDate;
                }
            }
        });

        // แปลงวันที่ใบวางบิล
        const billingDateDiv = document.getElementById('billingDateLabel');
        if (billingDateDiv) {
            const originalDate = billingDateDiv.getAttribute('data-original-date');
            if (originalDate) {
                const labelText = lang === 'en' ? 'Billing Date' : 'วันที่ใบวางบิล';
                const displayDate = lang === 'th' ? convertToThaiMonth(originalDate) : originalDate;
                billingDateDiv.innerHTML = '<span style="font-weight: bold;border-bottom: 1px solid #000;">' + labelText + '</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + displayDate;
            }
        }

        // Amount Text
        const amountText = document.getElementById('amountText');
        if (amountText) amountText.textContent = lang === 'en' ? amountText.getAttribute('data-en') : amountText.getAttribute('data-th');

        const labelTotalText = document.getElementById('labelTotalText');
        if (labelTotalText) labelTotalText.textContent = lang === 'en' ? 'Grand Total' : 'รวมเงินทั้งสิ้น';

        // Signatures
        const signatureLabels = document.querySelectorAll('.signature-section u');
        if (signatureLabels.length >= 3) {
            if (lang === 'en') {
                signatureLabels[0].textContent = 'Received By';
                signatureLabels[1].textContent = 'Check Date';
                signatureLabels[2].textContent = 'Billed By';
            } else {
                signatureLabels[0].textContent = 'ผู้รับวางบิล';
                signatureLabels[1].textContent = 'วันนัดรับเช็ค';
                signatureLabels[2].textContent = 'ผู้วางบิล';
            }
        }
    }

    // เก็บวันที่ต้นฉบับและแปลงเป็นภาษาไทยเมื่อโหลดหน้าเว็บ
    document.addEventListener('DOMContentLoaded', function() {
        // เก็บวันที่ต้นฉบับในตาราง
        const dateCells = document.querySelectorAll('.items-table tbody td');
        dateCells.forEach(cell => {
            const text = cell.textContent.trim();
            // ตรวจสอบว่าเป็นรูปแบบวันที่หรือไม่ (เช่น 17-Dec-25)
            if (/^\d{1,2}-[A-Z][a-z]{2}-\d{2}$/.test(text)) {
                cell.setAttribute('data-original-date', text);
                // แปลงเป็นภาษาไทยทันที
                cell.textContent = convertToThaiMonth(text);
            }
        });

        // เก็บวันที่ใบวางบิลและแปลงเป็นภาษาไทย
        const billingDateDiv = document.getElementById('billingDateLabel');
        if (billingDateDiv) {
            const originalDate = billingDateDiv.getAttribute('data-original-date');
            if (originalDate) {
                // แปลงเป็นภาษาไทยทันที
                const thaiDate = convertToThaiMonth(originalDate);
                billingDateDiv.innerHTML = '<span style="font-weight: bold;border-bottom: 1px solid #000;">วันที่ใบวางบิล</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + thaiDate;
            }
        }
    });
</script>
