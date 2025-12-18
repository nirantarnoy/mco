<?php

use yii\helpers\Html;
use backend\models\Company;

/* @var $this yii\web\View */
/* @var $model backend\models\CreditNote */

$company = ''; // Company::findOne(1); // Get company info
$formatter = Yii::$app->formatter;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>ใบลดหนี้ <?= $model->document_no ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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
            .no-print {
                display: none !important;
            }

            .main-footer {
                display: none !important;
            }

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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: relative;
        }

        body {
            font-family: 'Prompt', sans-serif;
            font-size: 18px;
            line-height: 1.4;
            color: #000;
        }

        .container {
            font-family: 'THSarabunPSK' !important;
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
        }

        .header {
            /* display: block; */
            /* justify-content: space-between; */
            /* align-items: flex-start; */
            margin-bottom: 20px;
            /* border-bottom: 2px solid #000; */
            padding-bottom: 10px;
        }

        .company-info {
            /* flex: 1; */
            display: block;
        }

        .company-logo {
            width: 120px;
            height: auto;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 28px;
            font-weight: 900;
            margin-bottom: 5px;
            color: #0000ff;
            -webkit-text-stroke: 0.5px black;
        }

        .company-details {
            font-size: 16px;
            line-height: 1.6;
            font-weight: 800;
            -webkit-text-stroke: 0.25px black;
        }

        .document-info {
            text-align: center;
        }

        .title-row {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .document-title {
            font-size: 32px;
            font-weight: 900;
            margin-bottom: 10px;
            -webkit-text-stroke: 0.5px black;
        }

        .document-title-en {
            font-size: 24px;
            font-weight: 900;
            margin-bottom: 15px;
            -webkit-text-stroke: 0.3px black;
        }

        .document-copy {
            position: absolute;
            right: 0;
            font-weight: bold;
            margin-bottom: 15px;
            margin-right: 30px;
        }

        .customer-section {
            margin: 0px 0;
            padding: 0px;
            /* border: 1px solid #ddd; */
            /* border-radius: 5px; */
        }

        .section-row {
            display: flex;
            margin-bottom: 8px;
        }

        .label {
            font-weight: 800;
            width: 150px;
            flex-shrink: 0;
            -webkit-text-stroke: 0.25px black;
        }

        .label-right {
            font-weight: 800;
            width: 100px;
            flex-shrink: 0;
            -webkit-text-stroke: 0.25px black;
        }

        .value {
            flex: 1;
        }

        .items-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .items-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: 800;
            font-size: 18px;
            -webkit-text-stroke: 0.25px black;
        }

        .items-table td {
            /* border: 1px solid #000; */
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            padding: 8px;
            font-size: 16px;
        }

        .items-table .text-center {
            text-align: center;
        }

        .items-table .text-right {
            text-align: right;
        }

        .summary-section {
            margin-top: 20px;
        }

        .summary-box {
            border: 1px solid #000;
            padding: 15px;
            margin-bottom: 10px;
        }

        .summary-table {
            width: 100%;
        }

        .summary-table td {
            padding: 5px 10px;
        }

        .summary-table .label {
            text-align: right;
            padding-right: 20px;
        }

        .summary-table .value {
            text-align: right;
            font-weight: 500;
        }

        .total-row {
            font-size: 18px;
            font-weight: 800;
            border-top: 2px solid #000;
            padding-top: 10px;
            -webkit-text-stroke: 0.25px black;
        }

        .amount-text {
            display: block;
            text-align: center;
            font-size: 18px;
            font-weight: 800;
            margin: 20px 0;
            padding: 10px;
            background-color: #f9f9f9;
            -webkit-text-stroke: 0.25px black;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px dotted #000;
            margin: 50px 20px 10px;
        }

        .original-invoice-info {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
        }

        .reason-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 15px 0;
            min-height: 60px;
        }
    </style>
    <script>
        // Function to change company header
        function changeHeader() {
            const headerSelect = document.getElementById('headerSelect');
            const selectedValue = headerSelect.value;

            const companyData = {
                mco: {
                    logo: '../../backend/web/uploads/logo/mco_logo_2.png',
                    nameThai: 'บริษัท เอ็ม.ซี.โอ. จำกัด',
                    nameEng: 'M.C.O. CO.,LTD.',
                    address: '8/18 Koh-Kloy Rd., Tambon Cherngnoen, Amphur Muang, Rayong 21000<br>8/18 ถนนเกาะกลอย ตำบลเชิงเนิน อำเภอเมือง จังหวัดระยอง 21000',
                    contact: 'Tel : (038) 875258-9 Fax : (038) 619559<br>e-mail: info@thai-mco.com www.thai-mco.com<br>'
                },
                alternative: {
                    logo: '../../backend/web/uploads/logo/mco_logo.png',
                    nameThai: 'บริษัทอื่น จำกัด',
                    nameEng: 'ALTERNATIVE COMPANY LTD.',
                    address: '123 Example St., District, Province 12345<br>123 ถนนตัวอย่าง เขต/อำเภอ จังหวัด 12345',
                    contact: 'Tel : 02-123-4567 Fax : 02-123-4568<br>e-mail: info@example.com www.example.com<br>'
                }
            };

            const company = companyData[selectedValue];
            document.getElementById('companyLogo').src = company.logo;
            document.getElementById('companyNameThai').textContent = company.nameThai;
            document.getElementById('companyNameEng').textContent = company.nameEng;
            document.getElementById('companyAddress').innerHTML = company.address;
            document.getElementById('companyContact').innerHTML = company.contact;
        }

        function changeLanguage() {
            const lang = document.getElementById('languageSelect').value;

            // Document titles
            const docTitle = document.querySelector('.document-title');
            const docTitleEn = document.querySelector('.document-title-en');
            if (lang === 'en') {
                if (docTitle) docTitle.textContent = 'CREDIT NOTE / TAX INVOICE';
                if (docTitleEn) docTitleEn.style.display = 'none';
            } else {
                if (docTitle) docTitle.textContent = 'ใบลดหนี้ / ใบกำกับภาษี';
                if (docTitleEn) {
                    docTitleEn.style.display = 'block';
                    docTitleEn.textContent = 'CREDIT NOTE / TAX INVOICE';
                }
            }

            // Customer section labels
            const labels = document.querySelectorAll('.label, .label-right');
            labels.forEach(label => {
                const text = label.textContent.trim();
                if (lang === 'en') {
                    if (text === 'เลขประจำตัวผู้เสียภาษี') label.textContent = 'Tax ID';
                    else if (text === 'รหัสลูกค้า') label.textContent = 'Customer Code';
                    else if (text === 'ชื่อลูกค้า') label.textContent = 'Customer Name';
                    else if (text === 'ที่อยู่') label.textContent = 'Address';
                    else if (text === 'เลขที่') label.textContent = 'No.';
                    else if (text === 'วันที่') label.textContent = 'Date';
                    else if (text === 'ใบกำกับภาษีเดิมเลขที่') label.textContent = 'Original Invoice No.';
                    else if (text === 'ลงวันที่') label.textContent = 'Date';
                } else {
                    if (text === 'Tax ID') label.textContent = 'เลขประจำตัวผู้เสียภาษี';
                    else if (text === 'Customer Code') label.textContent = 'รหัสลูกค้า';
                    else if (text === 'Customer Name') label.textContent = 'ชื่อลูกค้า';
                    else if (text === 'Address') label.textContent = 'ที่อยู่';
                    else if (text === 'No.') label.textContent = 'เลขที่';
                    else if (text === 'Date' && !label.classList.contains('label-right')) label.textContent = 'ลงวันที่';
                    else if (text === 'Date' && label.classList.contains('label-right')) label.textContent = 'วันที่';
                    else if (text === 'Original Invoice No.') label.textContent = 'ใบกำกับภาษีเดิมเลขที่';
                }
            });

            // Table headers
            const tableHeaders = document.querySelectorAll('.items-table thead th');
            if (tableHeaders.length >= 5) {
                if (lang === 'en') {
                    tableHeaders[0].textContent = 'No.';
                    tableHeaders[1].textContent = 'Description';
                    tableHeaders[2].textContent = 'Quantity';
                    tableHeaders[3].textContent = 'Price';
                    tableHeaders[4].textContent = 'Total';
                } else {
                    tableHeaders[0].textContent = 'ลำดับ';
                    tableHeaders[1].textContent = 'รายการ';
                    tableHeaders[2].textContent = 'จำนวน';
                    tableHeaders[3].textContent = 'ราคา';
                    tableHeaders[4].textContent = 'ราคารวม';
                }
            }

            // Summary labels in tfoot
            const summaryLabels = document.querySelectorAll('.items-table tfoot td');
            summaryLabels.forEach(cell => {
                const text = cell.textContent.trim();
                if (lang === 'en') {
                    if (text === 'มูลค่าสินค้าตามใบกำกับฯเดิม') cell.textContent = 'Original Invoice Amount';
                    else if (text === 'มูลค่าสินค้าตามจริง') cell.textContent = 'Actual Amount';
                    else if (text === 'รวมมูลค่าสินค้า') cell.textContent = 'Total Amount';
                    else if (text.includes('ภาษีมูลค่าเพิ่ม')) {
                        const vatPercent = text.match(/\d+/);
                        cell.textContent = 'VAT ' + (vatPercent ? vatPercent[0] : '7') + '%';
                    } else if (text === 'รวมเป็นเงินทั้งสิ้น') cell.textContent = 'Grand Total';
                } else {
                    if (text === 'Original Invoice Amount') cell.textContent = 'มูลค่าสินค้าตามใบกำกับฯเดิม';
                    else if (text === 'Actual Amount') cell.textContent = 'มูลค่าสินค้าตามจริง';
                    else if (text === 'Total Amount') cell.textContent = 'รวมมูลค่าสินค้า';
                    else if (text.includes('VAT')) {
                        const vatPercent = text.match(/\d+/);
                        cell.textContent = 'ภาษีมูลค่าเพิ่ม ' + (vatPercent ? vatPercent[0] : '7') + '%';
                    } else if (text === 'Grand Total') cell.textContent = 'รวมเป็นเงินทั้งสิ้น';
                }
            });

            // Reason label
            const reasonLabel = document.querySelector('div[style*="font-weight: 500"]');
            if (reasonLabel && reasonLabel.textContent.includes('เหตุผล')) {
                reasonLabel.textContent = lang === 'en' ? 'Reason for credit note:' : 'เหตุผลที่ต้องลดหนี้:';
            }

            // Amount text label & content
            const amountLabel = document.querySelector('td[width="20%"]');
            if (amountLabel) {
                // Check content or just set based on lang strictly
                const text = amountLabel.textContent.trim();
                // Simple toggle logic or direct set
                amountLabel.textContent = lang === 'en' ? '(In Words)' : '(ตัวอักษร)';
            }

            const amountTextSpan = document.getElementById('amountText');
            if (amountTextSpan) {
                amountTextSpan.innerHTML = lang === 'en' ? amountTextSpan.getAttribute('data-en') : amountTextSpan.getAttribute('data-th');
            }

            // Signature labels
            const signatureBoxes = document.querySelectorAll('.signature-box div');
            signatureBoxes.forEach(div => {
                const text = div.textContent.trim();
                if (lang === 'en') {
                    if (text.includes('ผู้มีอำนาจลงนาม')) div.textContent = 'Authorized Signature';
                    else if (text === 'ลายเซ็นผู้รับเอกสาร') div.textContent = 'Receiver Signature';
                } else {
                    if (text === 'Authorized Signature') div.textContent = 'ผู้มีอำนาจลงนาม / ผู้รับมอบอำนาจ';
                    else if (text === 'Receiver Signature') div.textContent = 'ลายเซ็นผู้รับเอกสาร';
                }
            });

            // Signature company name
            const sigComName = document.getElementById('sigComName');
            const headerSelect = document.getElementById('headerSelect');
            if (sigComName) {
                // If not MCO, keep the selected company name
                if (headerSelect && headerSelect.value !== 'mco') {
                    sigComName.textContent = headerSelect.value;
                } else {
                    sigComName.textContent = lang === 'en' ? 'M.C.O. CO.,LTD.' : 'บริษัท เอ็ม.ซี.โอ. จำกัด';
                }
            }
        }
    </script>
</head>


<body>
    <div class="no-print text-center mb-4">
        <div class="print-controls">
            <!-- Combined Controls Row -->
            <div style="display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap; margin-bottom: 10px;">
                <!-- Language Switcher -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label for="languageSelect" style="font-weight: bold; margin: 0;">ภาษา / Language:</label>
                    <select id="languageSelect" onchange="changeLanguage()" style="padding: 8px 15px; font-size: 14px; border-radius: 4px; border: 1px solid #ccc;">
                        <option value="th" selected>ไทย</option>
                        <option value="en">English Only</option>
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
                <div class="btn-group">
                    <button onclick="window.printMultipleCopies()" class="btn btn-primary btn-print">
                        <i class="fas fa-print"></i> พิมพ์
                    </button>
                    <button onclick="window.close()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> ปิด
                    </button>
                    <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-info">
                        <i class="fas fa-eye"></i> ดูรายละเอียด
                    </a>
                </div>
            </div>

            <!-- Progress Bar -->
            <!-- <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text">เตรียมพิมพ์...</div>
            </div> -->
        </div>
    </div>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <table style="width: 100%;">
                    <td>
                        <div class="logo" style="margin-left: -4px;margin-top: -12px;">
                            <img id="companyLogo" src="../../backend/web/uploads/logo/mco_logo_2.png" style="max-width: 180px;" alt="">
                        </div>
                        <div class="company-details" style="margin-top: 12px;" id="companyContact">
                            Tel : (038) 875258-9 Fax : (038) 619559<br>
                            e-mail: info@thai-mco.com www.thai-mco.com<br>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="company-name" style="margin-top: 25px;" id="companyNameThai">
                            บริษัท เอ็ม.ซี.โอ. จำกัด
                        </div>
                        <div class="company-name" style="font-size: 20px;" id="companyNameEng">
                            M.C.O. CO.,LTD.
                        </div>
                        <div class="company-details" id="companyAddress">
                            8/18 Koh-Kloy Rd., Tambon Cherngnoen, Amphur Muang, Rayong 21000<br>
                            8/18 ถนนเกาะกลอย ตำบลเชิงเนิน อำเภอเมือง จังหวัดระยอง 21000
                        </div>
                    </td>
                </table>
            </div>
        </div>

        <div class="document-info">

            <div class="document-title">ใบลดหนี้ / ใบกำกับภาษี</div>

            <div class="title-row">
                <div class="document-title-en">CREDIT NOTE / TAX INVOICE</div>
                <div class="document-copy">Copy</div>
            </div>

        </div>


        <!-- Customer Section -->
        <div class="customer-section">
            <table>
                <td style="width: 50%;">
                    <div class="section-row">
                        <div class="label">เลขประจำตัวผู้เสียภาษี</div>
                        <div class="value"><?= $company ? $company->tax_id : '0215543000985' ?></div>
                    </div>
                    <div class="section-row">
                        <div class="label">รหัสลูกค้า</div>
                        <div class="value"><?= $model->customer_id != null ? Html::encode(\backend\models\Customer::findCode($model->customer_id)) : Html::encode(\backend\models\Vendor::findCode($model->vendor_id)) ?></div>
                    </div>
                    <div class="section-row">
                        <div class="label">ชื่อลูกค้า</div>
                        <div class="value"><?= $model->customer_id != null ? Html::encode(\backend\models\Customer::findName($model->customer_id)) : Html::encode(\backend\models\Vendor::findName($model->vendor_id)) ?></div>
                    </div>
                    <div class="section-row">
                        <div class="label">ที่อยู่</div>
                        <div class="value"><?= $model->customer_id != null ? Html::encode(Html::encode(\backend\models\Customer::findFullAddress($model->customer_id))) : Html::encode(Html::encode(\backend\models\Vendor::findFullAddress($model->vendor_id))) ?><br>
                            <?php if ($model->customer_id != null): ?>
                                <?php if ($model->customer->taxid): ?>
                                    <div class="section-row">
                                        <div class="value">เลขประจำตัวผู้เสียภาษี <?= Html::encode($model->customer->taxid) ?></div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($model->vendor->taxid): ?>
                                    <div class="section-row">
                                        <div class="value">เลขประจำตัวผู้เสียภาษี <?= Html::encode($model->vendor->taxid) ?></div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="content-right" style="margin-top: -50px">
                        <div class="section-row">
                            <div class="label-right" style="margin-left: 150px;">เลขที่</div>
                            <div class="value"><?= Html::encode($model->document_no) ?></div>
                        </div>
                        <div class="section-row">
                            <div class="label-right" style="margin-left: 150px;">วันที่</div>
                            <div class="value"><?= $formatter->asDate($model->document_date, 'php:d/m/Y') ?></div>
                        </div>
                    </div>
                </td>
            </table>


        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 60px;">ลำดับ</th>
                    <th>รายการ</th>
                    <th style="width: 100px;">จำนวน</th>
                    <th style="width: 100px;">ราคา</th>
                    <th style="width: 120px;">ราคารวม</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalDiscount = 0;
                foreach ($model->creditNoteItems as $index => $item):
                    $totalDiscount += $item->discount_amount;
                ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td><?= nl2br(Html::encode($item->description)) ?></td>
                        <td class="text-center">
                            <?= $formatter->asDecimal($item->quantity, 0) ?>
                            <?= Html::encode($item->unit) ?>
                        </td>
                        <td class="text-right"><?= $formatter->asDecimal($item->unit_price, 2) ?></td>
                        <td class="text-right"><?= $formatter->asDecimal($item->quantity * $item->unit_price, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($totalDiscount > 0): ?>
                    <tr>
                        <td colspan="4" class="text-right">Discount</td>
                        <td class="text-right"><?= $formatter->asDecimal(-$totalDiscount, 2) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <!-- ช่องซ้าย (รวม 5 แถว) -->
                    <td colspan="2" rowspan="5" style="border:1px solid #000; padding:8px; vertical-align: top;">
                        <!-- แถวบน (2 คอลัมน์) -->
                        <div style="display: flex; justify-content: space-between;">
                            <!-- คอลัมน์ซ้าย -->
                            <div>
                                <div class="label">ใบกำกับภาษีเดิมเลขที่</div>
                                <div><?= Html::encode($model->original_invoice_no) ?></div>
                            </div>
                            <!-- คอลัมน์ขวา -->
                            <div>
                                <div class="label">ลงวันที่</div>
                                <div><?= $model->original_invoice_date ? $formatter->asDate($model->original_invoice_date, 'php:d/m/Y') : '' ?></div>
                            </div>
                        </div>

                        <!-- เว้นบรรทัดเล็กน้อย -->
                        <div style="height: 6px;"></div>

                        <!-- เหตุผล -->
                        <div>
                            <div style="font-weight: 500; margin-bottom: 5px;">เหตุผลที่ต้องลดหนี้:</div>
                            <?= nl2br(Html::encode($model->reason)) ?>
                        </div>
                    </td>

                    <!-- แถวที่ 1 -->
                    <td colspan="2" style="border:1px solid #000; font-weight: 800; -webkit-text-stroke: 0.25px black;">มูลค่าสินค้าตามใบกำกับฯเดิม</td>
                    <td style="border:1px solid #000; text-align:right; font-weight: 800;"><?= $formatter->asDecimal($model->original_amount, 2) ?></td>
                </tr>
                <tr>
                    <!-- แถวที่ 2 -->
                    <td colspan="2" style="border:1px solid #000; font-weight: 800; -webkit-text-stroke: 0.25px black;">มูลค่าสินค้าตามจริง</td>
                    <td style="border:1px solid #000; text-align:right; font-weight: 800;"><?= $formatter->asDecimal($model->actual_amount, 2) ?></td>
                </tr>
                <tr>
                    <!-- แถวที่ 3 -->
                    <td colspan="2" style="border:1px solid #000; font-weight: 800; -webkit-text-stroke: 0.25px black;">รวมมูลค่าสินค้า</td>
                    <td style="border:1px solid #000; text-align:right; font-weight: 800;"><?= $formatter->asDecimal($model->adjust_amount, 2) ?></td>
                </tr>
                <tr>
                    <!-- แถวที่ 4 -->
                    <td colspan="2" style="border:1px solid #000; font-weight: 800; -webkit-text-stroke: 0.25px black;">ภาษีมูลค่าเพิ่ม <?= $formatter->asDecimal($model->vat_percent, 0) ?>%</td>
                    <td style="border:1px solid #000; text-align:right; font-weight: 800;"><?= $formatter->asDecimal($model->vat_amount, 2) ?></td>
                </tr>
                <tr>
                    <!-- แถวที่ 5 -->
                    <td colspan="2" style="border:1px solid #000; font-weight: 800; -webkit-text-stroke: 0.25px black;">รวมเป็นเงินทั้งสิ้น</td>
                    <td style="border:1px solid #000; font-weight: 800; text-align:right; -webkit-text-stroke: 0.25px black;"><?= $formatter->asDecimal($model->total_amount, 2) ?></td>
                </tr>
            </tfoot>
        </table>


        <!-- Amount in Text -->
        <table width="100%">
            <tr>
                <td width="20%">
                    (ตัวอักษร)
                </td>
                <td width="80%">
                    <div class="amount-text">
                        <?php
                        $textThai = $model->amount_text; // Assuming this is correct from DB/Model
                        $textEng = \backend\helpers\NumberToText::convert($model->total_amount);
                        ?>
                        <span id="amountText" data-th="<?= Html::encode($textThai) ?>" data-en="<?= Html::encode($textEng) ?>">
                            <?= nl2br(Html::encode($textThai)) ?>
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div id="sigComName" style="font-weight: 800; -webkit-text-stroke: 0.25px black;">บริษัท เอ็ม.ซี.โอ. จำกัด</div>
                <div class="signature-line"></div>
                <div style="font-weight: 800; -webkit-text-stroke: 0.25px black;">ผู้มีอำนาจลงนาม / ผู้รับมอบอำนาจ</div>
                <div style="margin-top: 10px; font-weight: 800;">_____/_____/_____</div>
            </div>

            <div class="signature-box">
                <div>&nbsp;</div>
                <div class="signature-line"></div>
                <div style="font-weight: 800; -webkit-text-stroke: 0.25px black;">ลายเซ็นผู้รับเอกสาร</div>
                <div style="margin-top: 10px; font-weight: 800;">_____/_____/_____</div>
            </div>
        </div>
    </div>
</body>

</html>

<script>
    // Function to change company header
    function changeHeader() {
        const headerSelect = document.getElementById('headerSelect');
        const selectedValue = headerSelect.value;

        const companyNameThai = document.getElementById('companyNameThai');
        const companyNameEng = document.getElementById('companyNameEng');
        const sigComName = document.getElementById('sigComName');
        const langSelect = document.getElementById('languageSelect');
        const currentLang = langSelect ? langSelect.value : 'th';

        if (selectedValue === 'mco') {
            // Restore MCO Layout
            if (companyNameThai) {
                companyNameThai.style.display = 'block';
                companyNameThai.textContent = 'บริษัท เอ็ม.ซี.โอ. จำกัด';
            }
            if (companyNameEng) {
                companyNameEng.style.display = 'block';
                companyNameEng.textContent = 'M.C.O. CO.,LTD.';
            }
            // Restore Sig Name based on current language
            if (sigComName) {
                sigComName.textContent = currentLang === 'en' ? 'M.C.O. CO.,LTD.' : 'บริษัท เอ็ม.ซี.โอ. จำกัด';
            }
        } else {
            // Other Company
            if (companyNameThai) companyNameThai.style.display = 'none';

            if (companyNameEng) {
                companyNameEng.style.display = 'block';
                companyNameEng.textContent = selectedValue;
            }

            // Update Sig Name to selected company name
            if (sigComName) sigComName.textContent = selectedValue;
        }
    }
</script>

<?php
$js = <<<JS
// Global variables

// Global variables
let printInProgress = false;
let currentCopy = 0;
const totalCopies = 1; // พิมพ์แค่ใบเดียว

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
        progressText.textContent = 'กำลังพิมพ์...';
    }
};

window.createPrintCopies = function() {
    // ไม่ต้องสร้างสำเนา (ใบเดียว)
};

window.printMultipleCopies = function() {
    if (printInProgress) return;

    printInProgress = true;
    currentCopy = 0;

    const printBtn = document.querySelector('.btn-print');
    if (printBtn) {
        printBtn.disabled = true;
        printBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังพิมพ์...';
    }

    window.updateProgress(0, totalCopies);

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
        printBtn.innerHTML = '<i class="fas fa-print"></i> พิมพ์';
    }

    printInProgress = false;
});
JS;

$this->registerJs($js);
?>