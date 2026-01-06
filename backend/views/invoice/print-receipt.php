<?php

use yii\helpers\Html; ?>
<style>
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

    @page {
        size: A5 landscape;
        margin: 3mm;
    }



    @media print {

        .no-print,
        .receipt-print-controls,
        .main-footer,
        .main-header,
        .main-sidebar,
        .content-wrapper .content-header {
            display: none !important;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        .receipt-container {
            box-shadow: none !important;
            margin: 0 !important;
            padding: 8px !important;
            max-width: 100% !important;
        }
    }

    * {
        box-sizing: border-box;
    }

    table {
        page-break-inside: avoid;
    }

    .receipt-container {
        max-width: 210mm;
        margin: 0 auto;
        background: white;
        padding: 5px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        font-family: 'THSarabunPSK', Arial, sans-serif;
        font-size: 10px;
        line-height: 1.05;
    }

    @media screen {
        .receipt-container {
            min-height: 148mm;
            max-height: 148mm;
            overflow: auto;
        }
    }



    /* Print Controls */
    .receipt-print-controls {
        margin-bottom: 20px;
        text-align: center;
    }

    .receipt-btn-group {
        display: inline-flex;
        gap: 10px;
    }

    .receipt-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 11px;
        text-decoration: none;
        display: inline-block;
    }

    .receipt-btn-primary {
        background-color: #007bff;
        color: white;
    }

    .receipt-btn-success {
        background-color: #28a745;
        color: white;
    }

    .receipt-btn:hover {
        opacity: 0.8;
    }

    /* Header Section */
    .receipt-header-section {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        margin-bottom: 1px;
        padding-bottom: 0;
    }

    .receipt-header-left {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 10px;
    }

    .receipt-logo-section {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 3px;
        margin-right: 20px;
    }

    .receipt-company-info {
        text-align: left;
    }

    .receipt-company-name-thai {
        font-size: 60px;
        font-weight: 800;
        color: #000;
        text-shadow: none;
        text-decoration: underline;
        text-decoration-thickness: 2px;
        text-decoration-color: #000;
        text-underline-offset: 6px;
    }

    .receipt-company-name-eng {
        font-size: 45px;
        font-weight: 800;
        color: #000;
        margin-bottom: 1px;
        margin-top: -15px;
    }

    .receipt-company-address {
        font-size: 10px;
        line-height: 1.1;
        color: #333;
        font-weight: 700;
    }

    .receipt-row {
        display: flex;
        margin-bottom: 2px;
    }

    .receipt-col {
        flex: 1;
        padding: 2px;
    }

    .receipt-title {
        font-size: 20px;
        font-weight: 800;
        text-align: center;
        color: #0066CC;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 0;
        background: none;
        border: none;
        box-shadow: none;
    }

    /* Customer and Details Table */
    .receipt-details-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2px;
        font-size: 13px;
        border: 1px solid #000;
    }

    .receipt-details-table td {
        border: 1px solid #000;
        padding: 3px 5px;
        vertical-align: middle;
    }

    /* Label cells are bold */
    .receipt-label-cell {
        font-weight: 700;
        color: #000;
    }

    .receipt-data-cell {
        font-weight: 600;
    }

    /* Items Table */
    .receipt-items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2px;
        font-size: 13px;
        border: 1px solid #000;
    }

    .receipt-items-table th,
    .receipt-items-table td {
        border: 1px solid #000;
        padding: 4px 6px;
        text-align: center;
        vertical-align: middle;
    }

    .receipt-items-table th {
        background: white;
        color: black;
        font-weight: 800;
        height: 22px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .receipt-items-table td {
        height: 18px;
        font-weight: 800;
    }

    .receipt-items-table tfoot td {
        background-color: #fff;
        font-weight: 700;
    }

    .receipt-text-left {
        text-align: left !important;
    }

    .receipt-text-right {
        text-align: right !important;
    }

    /* Footer Note */
    .receipt-footer-note {
        font-size: 12px;
        text-align: left;
        margin: 3px 0;
        line-height: 1.3;
        color: #555;
    }

    /* Payment and Signature Section */
    .receipt-payment-signature-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9px;
        border: 1px solid #000;
    }

    .receipt-payment-signature-table td {
        border: 1px solid #000;
        padding: 3px;
        vertical-align: top;
    }

    .receipt-checkbox {
        width: 12px;
        height: 12px;
        border: 1.5px solid #333;
        display: inline-block;
        margin-right: 5px;
        vertical-align: middle;
        border-radius: 2px;
    }

    .receipt-signature-line {
        border-bottom: 1.5px solid #333;
        height: 50px;
        margin-bottom: 5px;
    }

    /* Form Code */
    .receipt-form-code {
        text-align: right;
        font-size: 11px;
        margin-top: 3px;
        color: #666;
        font-style: italic;
    }

    /* Compact spacing */
    .receipt-compact-row {
        margin-bottom: 1px;
    }

    /* Summary Section Enhancement */
    .receipt-summary-highlight {
        background: linear-gradient(135deg, #fff9c4 0%, #fff59d 100%);
        font-weight: 800;
        font-size: 13px;
    }
</style>

<div class="receipt-print-controls no-print">
    <div style="display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap; margin-bottom: 10px;">
        <!-- Language Switcher -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <label for="languageSelect" style="font-weight: bold; margin: 0;">ภาษา:</label>
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
        <div class="receipt-btn-group">
            <button onclick="printReceipt()" class="receipt-btn receipt-btn-primary">
                <i class="fas fa-print"></i> พิมพ์
            </button>
            <button onclick="generateNewReceipt()" class="receipt-btn receipt-btn-success">
                <i class="fas fa-plus"></i> สร้างใหม่
            </button>
        </div>
    </div>
</div>

<div class="receipt-container">
    <!-- Header Section -->
    <div class="receipt-header-section">
        <div class="receipt-header-left">
            <div class="receipt-logo-section">
                <img id="companyLogo" src="../../backend/web/uploads/logo/mco_logo_2.png" style="max-width: 190px;" alt="">
            </div>
            <div class="receipt-company-info">
                <div class="receipt-company-name-thai" id="companyNameThai">บริษัท เอ็ม.ซี.โอ. จำกัด</div>
                <div class="receipt-company-name-eng" id="companyNameEng">M.C.O. COMPANY LIMITED</div>
                <div class="receipt-company-address" style="margin-top: -12px;" id="addressThai">
                    8/18 ถ.เกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000 &nbsp;
                    โทร. 66-(0) 3887-5258-59 แฟกซ์ 66-(0)3861-9559
                </div>
                <div class="receipt-company-address" id="addressEng">8/18 Koh-Kloy-Rd., Cherngnoen, Muang, Rayong 21000 &nbsp; Tel. 66-(0)3887-5258-59 &nbsp; Fax. 66-(0)3861-9559</div>
            </div>
        </div>
    </div>

    <!-- Title and Receipt Info -->
    <div class="receipt-row receipt-compact-row">
        <div class="receipt-col" style="text-align: left; padding-left: 10px;">
            <strong id="taxIdLabel" style="font-size: 12px; margin-top: 10px; display: inline-block;">เลขประจำตัวผู้เสียภาษี: 0215543000985</strong>
        </div>
        <div class="receipt-col">
            <div class="receipt-title">ใบเสร็จรับเงิน<br>RECEIPT</div>
        </div>
        <div class="receipt-col"></div>
    </div>

    <!-- Customer Details Table -->
    <table class="receipt-details-table">
        <tr>
            <td class="receipt-label-cell" style="width: 9%; border-right: none;">รหัสลูกค้า<br>CODE</td>
            <td class="receipt-data-cell" style="width: 22%;"><?= Html::encode(\backend\models\Customer::findCode($model->customer_id) ?: '') ?></td>
            <td class="receipt-label-cell" style="width: 12%; border-right: none;">วันที่<br>DATE</td>
            <td class="receipt-data-cell" style="width: 22%;"><?= date('d/m/Y', strtotime($model->invoice_date)) ?></td>
            <td class="receipt-label-cell" style="width: 10%; border-right: none;">เลขที่<br>NO</td>
            <td class="receipt-data-cell" style="width: 22%;"><?= Html::encode($model->invoice_number) ?></td>
        </tr>
        <tr>
            <td class="receipt-label-cell" rowspan="2" style="border-right: none;">ขายให้<br>SALE TO</td>
            <td class="receipt-data-cell" style="border-bottom: none;"><?= Html::encode($model->customer_name ?: '') ?></td>
            <td class="receipt-label-cell" style="border-right: none;">ใบสั่งซื้อเลขที่<br>PO NO</td>
            <td class="receipt-data-cell"><?= Html::encode($model->po_number ?: '') ?></td>
            <td class="receipt-label-cell" style="border-right: none;">วันที่สั่งซื้อ<br>PO DATE</td>
            <td class="receipt-data-cell"><?= date('d/m/Y', strtotime($model->po_date)) ?></td>
        </tr>
        <tr>
            <td class="receipt-data-cell" style="border-top: none;">
                <?php
                // Clean up address by removing empty fields like "ซอย -", "ถนน -", etc.
                $address = $model->customer_address ?: '';
                $address = preg_replace('/\s*(ซอย|ถนน|ตำบล|อำเภอ|จังหวัด|แขวง|เขต|หมู่|Soi|Road|Sub-district|District|Province)\s*-\s*/u', '', $address);
                $address = preg_replace('/\s+/', ' ', $address); // Remove extra spaces
                $address = trim($address);
                echo Html::encode($address);
                ?><br>
                TAXID: <?= Html::encode($model->customer_tax_id ?: '') ?>
            </td>
            <?php
            // Determine the model to use for totals and reference info
            $totalModel = $model;
            $refNo = '';
            $refDate = '';
            
            if ($model->invoice_type == 'receipt' || $model->invoice_type == 'bill_placement') {
                 $refInvoice = \backend\models\Invoice::findOne($model->quotation_id);
                 if($refInvoice){
                     // $totalModel = $refInvoice;
                     $refNo = $refInvoice->invoice_number;
                     $refDate = $refInvoice->invoice_date;
                 }
            } else {
                 $refQuotation = \backend\models\Quotation::findOne($model->quotation_id);
                 if($refQuotation){
                     $refNo = $refQuotation->quotation_no;
                     $refDate = $refQuotation->quotation_date;
                 }
            }
            ?>
            <td class="receipt-label-cell" style="border-right: none;">อ้างถึงเลขที่ใบแจ้งหนี้<br>RFQ.IV</td>
            <td class="receipt-data-cell"><?= Html::encode($refNo) ?></td>
            <td class="receipt-label-cell" style="border-right: none;">อ้างถึงวันที่ใบแจ้งหนี้<br>RFQ.DATE.IV</td>
            <td class="receipt-data-cell">
                <?= $refDate ? date('d/m/Y', strtotime($refDate)) : '' ?>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="receipt-items-table">
        <thead>
            <tr>
                <th style="width: 8%;">ลำดับ<br>ITEM</th>
                <th style="width: 50%;">รายการ<br>DESCRIPTION</th>
                <th style="width: 12%;">จำนวน<br>QUANTITY</th>
                <th style="width: 15%;">ราคาต่อหน่วย<br>UNIT PRICE</th>
                <th style="width: 15%;">จำนวนเงิน<br>AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if we are referencing another document (Invoice or Bill Placement)
            $isRefDocument = ($model->invoice_type == 'receipt' || $model->invoice_type == 'bill_placement') && !empty($refNo);
            
            if ($isRefDocument) {
                // Display summary row for referenced document
                ?>
                <tr>
                    <td style="text-align: center;"><b>1</b></td>
                    <td class="receipt-text-left">
                        <b>ใบกำกับภาษี เลขที่ <?= Html::encode($refNo) ?></b>
                    </td>
                    <td>1 <?= Html::encode('job') ?></td>
                    <td class="receipt-text-right"><?= number_format($totalModel->subtotal, 2) ?></td>
                    <td class="receipt-text-right"><?= number_format($totalModel->subtotal, 2) ?></td>
                </tr>
                <?php
            } else {
                // Display detailed items for standard invoice/quotation
                $model_line = \backend\models\InvoiceItem::find()->where(['invoice_id' => $model->id])->all();
                if (!empty($model_line)): 
                    foreach ($model_line as $index => $item): ?>
                        <tr>
                            <td style="text-align: center;"><b><?= $index + 1 ?></b></td>
                            <td class="receipt-text-left"><?= nl2br(Html::encode($item->item_description)) ?></td>
                            <td><?= number_format($item->quantity, 0) ?> <?= Html::encode($item->unit) ?></td>
                            <td class="receipt-text-right"><?= number_format($item->unit_price, 2) ?></td>
                            <td class="receipt-text-right"><?= number_format($item->amount, 2) ?></td>
                        </tr>
                    <?php endforeach; 
                endif; 
            }
            ?>
            <?php for ($i = 0; $i < 1; $i++): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php endfor; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" rowspan="2" style="border-top: 1px solid #000; border-right: none; border-bottom: none; border-left: none;">
                    <div class="receipt-footer-note">
                        <strong>ผิด ตก ยกเว็น E.&O.E.</strong><br>
                        <strong>หมายเหตุ</strong> ใบเสร็จรับเงินฉบับนี้จะสมบูรณ์ต่อเมื่อเก็บเงินตามเช็คได้เรียบร้อยแล้ว<br>
                        <strong>This receipt will be valued only when the cheque is cleared with the Bank</strong>
                    </div>
                </td>
                <td class="receipt-text-left" style="border: 1px solid #000; border-top: 1px solid #000;"><strong style="font-weight: 800;">รวมเงิน<br>TOTAL</strong></td>
                <td class="receipt-text-right" style="border: 1px solid #000; border-top: 1px solid #000;"><strong style="font-weight: 800;"><?= number_format($totalModel->subtotal, 2) ?></strong></td>
            </tr>
            <tr>
                <td class="receipt-text-left" style="border: 1px solid #000; border-top: none;"><strong style="font-weight: 800;">ภาษีมูลค่าเพิ่ม<br>VAT 7%</strong></td>
                <td class="receipt-text-right" style="border: 1px solid #000; border-top: none;"><strong style="font-weight: 800;"><?= number_format($totalModel->vat_amount, 2) ?></strong></td>
            </tr>
            <tr>
                <td colspan="3" class="receipt-summary-highlight" style="border: none; padding: 6px;">
                    <?php
                    if (isset($totalModel->total_amount_text) && !empty($totalModel->total_amount_text)) {
                        $textThai = $totalModel->total_amount_text;
                    } else {
                        // Use Quotation's numtothai method as a fallback
                        $textThai = (new \backend\models\Quotation())->numtothai($totalModel->total_amount);
                    }
                    $textEng = \backend\helpers\NumberToText::convert($totalModel->total_amount);
                    ?>
                    <strong id="amountText" data-th="<?= Html::encode($textThai) ?>" data-en="<?= Html::encode($textEng) ?>">
                        <?= Html::encode($textThai) ?>
                    </strong>
                </td>
                <td class="receipt-text-left receipt-summary-highlight" style="border: 1px solid #000; border-top: none;"><strong style="font-weight: 800;">รวมเงินทั้งสิ้น<br>TOTAL AMOUNT</strong></td>
                <td class="receipt-text-right receipt-summary-highlight" style="border: 1px solid #000; border-top: none;"><strong style="font-weight: 800;"><?= number_format($totalModel->total_amount, 2) ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Payment and Signature Section -->
    <table class="receipt-payment-signature-table">
        <tr>
            <td style="width: 35%; padding: 8px; min-height: 40px;">
                <strong style="font-size: 11px;">ชำระโดย PAID BY</strong>
                <span class="receipt-checkbox"></span> <strong style="font-size: 11px;">เงินสด CASH</strong>
                <span class="receipt-checkbox"></span> <strong style="font-size: 11px;">เช็ค CHEQUE</strong>
            </td>
            <td colspan="2" style="padding: 8px; min-height: 40px; font-size: 11px;">
                <strong>ธนาคาร BANK:</strong> _____________________________________
                <strong>เลขที่เช็ค CHEQUE NO.:</strong> ______________________________
                <strong>ลงวันที่ DATE:</strong> _______________________________________
            </td>
        </tr>
        <tr>
            <td style="text-align: center; padding: 10px; height: 100px; vertical-align: bottom;">
                <div class="receipt-signature-line"></div>
                <strong style="font-size: 10px;">จำนวนเงิน / AMOUNT</strong>
            </td>
            <td style="text-align: center; width: 32.5%; padding: 10px; height: 100px; vertical-align: bottom;">
                <div class="receipt-signature-line"></div>
                <strong style="font-size: 10px;">ผู้เก็บเงิน / COLLECTOR</strong><br>
                <strong style="font-size: 10px;">วันที่ / DATE</strong>
            </td>
            <td style="text-align: center; width: 32.5%; padding: 10px; height: 100px; vertical-align: bottom;">
                <div class="receipt-signature-line"></div>
                <strong style="font-size: 10px;">ผู้จัดการ / ผู้มีอำนาจลงนาม</strong><br>
                <strong style="font-size: 10px;">MANAGER / AUTHORIZED SIGNATURE</strong>
            </td>
        </tr>
    </table>

    <!-- Form Code -->
    <div class="receipt-form-code">
        F-WP-FMA-006-002Rev.N
    </div>
</div>

<script>
    // Function to change company header
    function changeHeader() {
        const headerSelect = document.getElementById('headerSelect');
        const selectedValue = headerSelect.value;

        const companyNameThai = document.getElementById('companyNameThai');
        const companyNameEng = document.getElementById('companyNameEng');

        if (selectedValue === 'mco') {
            // Restore MCO Layout
            if (companyNameThai) {
                companyNameThai.style.display = 'block';
                companyNameThai.textContent = 'บริษัท เอ็ม.ซี.โอ. จำกัด';
            }
            if (companyNameEng) {
                companyNameEng.style.display = 'block';
                companyNameEng.textContent = 'M.C.O. COMPANY LIMITED';
            }
        } else {
            // Other Company - Show Name Only (using the Eng container)
            if (companyNameThai) companyNameThai.style.display = 'none';

            if (companyNameEng) {
                companyNameEng.style.display = 'block';
                companyNameEng.textContent = selectedValue;
            }
        }
    }

    function changeLanguage() {
        const lang = document.getElementById('languageSelect').value;

        // Tax ID label
        const taxIdLabel = document.getElementById('taxIdLabel');
        if (taxIdLabel) {
            taxIdLabel.textContent = lang === 'en' ? 'Tax ID: 0215543000985' : 'เลขประจำตัวผู้เสียภาษี: 0215543000985';
        }

        // Receipt title
        const receiptTitle = document.querySelector('.receipt-title');
        if (receiptTitle) {
            receiptTitle.innerHTML = lang === 'en' ? 'RECEIPT' : 'ใบเสร็จรับเงิน<br>RECEIPT';
        }

        // Amount Text
        const amountText = document.getElementById('amountText');
        if (amountText) {
            amountText.textContent = lang === 'en' ? amountText.getAttribute('data-en') : amountText.getAttribute('data-th');
        }

        // Details table labels
        const detailsLabels = document.querySelectorAll('.receipt-details-table .receipt-label-cell');
        if (lang === 'en') {
            if (detailsLabels[0]) detailsLabels[0].innerHTML = 'CODE';
            if (detailsLabels[1]) detailsLabels[1].innerHTML = 'DATE';
            if (detailsLabels[2]) detailsLabels[2].innerHTML = 'NO';
            if (detailsLabels[3]) detailsLabels[3].innerHTML = 'SALE TO';
            if (detailsLabels[4]) detailsLabels[4].innerHTML = 'PO NO';
            if (detailsLabels[5]) detailsLabels[5].innerHTML = 'PO DATE';
            if (detailsLabels[6]) detailsLabels[6].innerHTML = 'RFQ.IV';
            if (detailsLabels[7]) detailsLabels[7].innerHTML = 'RFQ.DATE.IV';
        } else {
            if (detailsLabels[0]) detailsLabels[0].innerHTML = 'รหัสลูกค้า<br>CODE';
            if (detailsLabels[1]) detailsLabels[1].innerHTML = 'วันที่<br>DATE';
            if (detailsLabels[2]) detailsLabels[2].innerHTML = 'เลขที่<br>NO';
            if (detailsLabels[3]) detailsLabels[3].innerHTML = 'ขายให้<br>SALE TO';
            if (detailsLabels[4]) detailsLabels[4].innerHTML = 'ใบสั่งซื้อเลขที่<br>PO NO';
            if (detailsLabels[5]) detailsLabels[5].innerHTML = 'วันที่สั่งซื้อ<br>PO DATE';
            if (detailsLabels[6]) detailsLabels[6].innerHTML = 'อ้างถึงเลขที่ใบแจ้งหนี้<br>RFQ.IV';
            if (detailsLabels[7]) detailsLabels[7].innerHTML = 'อ้างถึงวันที่ใบแจ้งหนี้<br>RFQ.DATE.IV';
        }

        // Items table headers
        const itemHeaders = document.querySelectorAll('.receipt-items-table thead th');
        if (itemHeaders.length >= 5) {
            if (lang === 'en') {
                itemHeaders[0].innerHTML = 'ITEM';
                itemHeaders[1].innerHTML = 'DESCRIPTION';
                itemHeaders[2].innerHTML = 'QUANTITY';
                itemHeaders[3].innerHTML = 'UNIT PRICE';
                itemHeaders[4].innerHTML = 'AMOUNT';
            } else {
                itemHeaders[0].innerHTML = 'ลำดับ<br>ITEM';
                itemHeaders[1].innerHTML = 'รายการ<br>DESCRIPTION';
                itemHeaders[2].innerHTML = 'จำนวน<br>QUANTITY';
                itemHeaders[3].innerHTML = 'ราคาต่อหน่วย<br>UNIT PRICE';
                itemHeaders[4].innerHTML = 'จำนวนเงิน<br>AMOUNT';
            }
        }

        // Footer summary labels
        const footerRows = document.querySelectorAll('.receipt-items-table tfoot tr');
        if (footerRows.length >= 3) {
            const totalLabel = footerRows[0].querySelectorAll('td')[1];
            if (totalLabel && totalLabel.querySelector('strong')) {
                totalLabel.querySelector('strong').innerHTML = lang === 'en' ? 'TOTAL' : 'รวมเงิน<br>TOTAL';
            }

            const vatLabel = footerRows[1].querySelectorAll('td')[0];
            if (vatLabel && vatLabel.querySelector('strong')) {
                vatLabel.querySelector('strong').innerHTML = lang === 'en' ? 'VAT 7%' : 'ภาษีมูลค่าเพิ่ม<br>VAT 7%';
            }

            const totalAmountLabel = footerRows[2].querySelectorAll('td')[1];
            if (totalAmountLabel && totalAmountLabel.querySelector('strong')) {
                totalAmountLabel.querySelector('strong').innerHTML = lang === 'en' ? 'TOTAL AMOUNT' : 'รวมเงินทั้งสิ้น<br>TOTAL AMOUNT';
            }
        }

        // Footer note
        const footerNote = document.querySelector('.receipt-footer-note');
        if (footerNote) {
            if (lang === 'en') {
                footerNote.innerHTML = '<strong>E.&O.E.</strong><br><strong>Note:</strong> This receipt will be valued only when the cheque is cleared with the Bank';
            } else {
                footerNote.innerHTML = '<strong>ผิด ตก ยกเว็น E.&O.E.</strong><br><strong>หมายเหตุ</strong> ใบเสร็จรับเงินฉบับนี้จะสมบูรณ์ต่อเมื่อเก็บเงินตามเช็คได้เรียบร้อยแล้ว<br><strong>This receipt will be valued only when the cheque is cleared with the Bank</strong>';
            }
        }

        // Payment section
        const paymentLabels = document.querySelectorAll('.receipt-payment-signature-table strong');
        if (lang === 'en') {
            if (paymentLabels[0]) paymentLabels[0].textContent = 'PAID BY';
            if (paymentLabels[1]) paymentLabels[1].textContent = 'CASH';
            if (paymentLabels[2]) paymentLabels[2].textContent = 'CHEQUE';
            if (paymentLabels[3]) paymentLabels[3].textContent = 'BANK:';
            if (paymentLabels[4]) paymentLabels[4].textContent = 'CHEQUE NO.:';
            if (paymentLabels[5]) paymentLabels[5].textContent = 'DATE:';
            if (paymentLabels[6]) paymentLabels[6].textContent = 'AMOUNT';
            if (paymentLabels[7]) paymentLabels[7].innerHTML = 'COLLECTOR';
            if (paymentLabels[8]) paymentLabels[8].textContent = 'DATE';
            if (paymentLabels[9]) paymentLabels[9].innerHTML = 'MANAGER / AUTHORIZED SIGNATURE';
            if (paymentLabels[10]) paymentLabels[10].textContent = '';
        } else {
            if (paymentLabels[0]) paymentLabels[0].textContent = 'ชำระโดย PAID BY';
            if (paymentLabels[1]) paymentLabels[1].textContent = 'เงินสด CASH';
            if (paymentLabels[2]) paymentLabels[2].textContent = 'เช็ค CHEQUE';
            if (paymentLabels[3]) paymentLabels[3].textContent = 'ธนาคาร BANK:';
            if (paymentLabels[4]) paymentLabels[4].textContent = 'เลขที่เช็ค CHEQUE NO.:';
            if (paymentLabels[5]) paymentLabels[5].textContent = 'ลงวันที่ DATE:';
            if (paymentLabels[6]) paymentLabels[6].textContent = 'จำนวนเงิน / AMOUNT';
            if (paymentLabels[7]) paymentLabels[7].innerHTML = 'ผู้เก็บเงิน / COLLECTOR';
            if (paymentLabels[8]) paymentLabels[8].textContent = 'วันที่ / DATE';
            if (paymentLabels[9]) paymentLabels[9].innerHTML = 'ผู้จัดการ / ผู้มีอำนาจลงนาม';
            if (paymentLabels[10]) paymentLabels[10].textContent = 'MANAGER / AUTHORIZED SIGNATURE';
        }
    }

    function printReceipt() {
        window.print();
    }

    function generateNewReceipt() {
        const receipts = [{
                number: 'MCO-25-000043',
                date: '9/5/2025',
                customer: 'บริษัท ไทยออยล์ จำกัด (มหาชน)',
                amount: 125000.00
            },
            {
                number: 'MCO-25-000044',
                date: '10/5/2025',
                customer: 'บริษัท ปตท. สำรวจและผลิตปิโตรเลียม จำกัด (มหาชน)',
                amount: 89500.00
            }
        ];

        const randomReceipt = receipts[Math.floor(Math.random() * receipts.length)];
        console.log('Generated new receipt:', randomReceipt);
    }

    window.addEventListener('beforeprint', function() {
        document.body.style.zoom = '1';
    });
</script>
