<?php

use yii\helpers\Html;

$this->title = 'Quotation - ' . $quotation->quotation_no;

// คำนวณราคารวม
$subtotal = 0;
$vat = 0;
$discount = $quotation->total_discount_amount;
$grandTotal = 0;

if ($quotationLines) {
    foreach ($quotationLines as $line) {
        $subtotal += $line->line_total;
    }
}

// คำนวณ VAT 7%
//$vat = $subtotal * 0.07;
//$grandTotal = $subtotal + $vat - $discount;

$vat = $quotation->vat_total_amount == null ? 0 : $quotation->vat_total_amount;
$grandTotal = $quotation->total_amount == null ? 0 : $quotation->total_amount;

$customer_info = \backend\models\Customer::findCustomerInfo($quotation->customer_id);
//print_r($customer_info);return;
$customer_name = $customer_info !== null && count($customer_info) > 0 ? $customer_info['name'] : '';

// Build address from parts, filtering out empty values and '-'
$address_parts = [];
if ($customer_info !== null && count($customer_info) > 0) {
    if (!empty($customer_info['home_number']) && $customer_info['home_number'] !== '-') $address_parts[] = $customer_info['home_number'];
    if (!empty($customer_info['street']) && $customer_info['street'] !== '-') $address_parts[] = $customer_info['street'];
    if (!empty($customer_info['aisle']) && $customer_info['aisle'] !== '-') $address_parts[] = $customer_info['aisle'];
    if (!empty($customer_info['district_name']) && $customer_info['district_name'] !== '-') $address_parts[] = $customer_info['district_name'];
    if (!empty($customer_info['city_name']) && $customer_info['city_name'] !== '-') $address_parts[] = $customer_info['city_name'];
    if (!empty($customer_info['province_name']) && $customer_info['province_name'] !== '-') $address_parts[] = $customer_info['province_name'];
    if (!empty($customer_info['zipcode']) && $customer_info['zipcode'] !== '-') $address_parts[] = $customer_info['zipcode'];
}
$customer_address = implode(', ', $address_parts);

$phone = $customer_info !== null && count($customer_info) > 0 ? $customer_info['phone'] : '';
$email = $customer_info !== null && count($customer_info) > 0 ? $customer_info['email'] : '';
$customer_taxid = $customer_info !== null && count($customer_info) > 0 ? $customer_info['taxid'] : '';
?>

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

    .quotation-container {
        font-family: 'THSarabunPSK', sans-serif !important;
        max-width: 260mm;
        margin: 0 auto;
        padding: 5px;
        background: white;
    }

    .header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1px;
    }

    .logo-section {
        display: flex;
        align-items: center;
        margin-bottom: -15px;
    }

    .logo {
        font-size: 25px;
        font-weight: bold;
        color: #FF0000;
        margin-right: 10px;
    }

    .logo .m {
        color: #FFA500;
    }

    .logo .c {
        color: #000080;
    }

    .logo .o {
        color: #008000;
    }

    .th-flag {
        background: linear-gradient(to bottom, #FF0000 33%, #FFFFFF 33% 66%, #000080 66%);
        width: 30px;
        height: 20px;
        display: inline-block;
        margin-left: 5px;
    }

    .quotation-title {
        font-size: 60px;
        font-weight: 900;
        color: #203764;
        -webkit-text-stroke: 2px #203764;
        letter-spacing: 2px;
    }

    .info-section {
        background: #E6F2FF;
        padding: 5px;
        /*margin-bottom: 5px;*/
        border-radius: 5px;
    }

    .info-row {
        display: flex;
        margin-bottom: 0;
    }

    .info-label {
        font-weight: bold;
        margin-right: 10px;
        min-width: 100px;
        font-weight: 900;
        font-size: 20px;
    }

    .address-section {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1px;
    }

    .address-box {
        width: 48%;
    }

    .address-header {
        background: #E6F2FF;
        /*padding: 5px 10px;*/
        font-weight: bold;
        margin-bottom: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1px;
    }

    th {
        background: #E6F2FF;
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;
        font-weight: bold;
    }

    td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;
    }

    .description-cell {
        text-align: left !important;
    }

    .number-cell {
        text-align: right !important;
    }

    .summary-section {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }

    .summary-box {
        border: 1px solid #ccc;
        padding: 10px;
    }

    .summary-label {
        font-weight: bold;
        margin-right: 10px;
    }

    .label-font {
        font-size: 18px;
        font-weight: 900;
    }

    .label-left {
        text-align: left;
        margin-left: 110px;
        font-size: 20px;
        font-weight: 900;
        -webkit-text-stroke: 0.5px black;
    }

    .text-infomation {
        margin-top: -15px;
        font-size: 30px;
    }

    .signature-section {
        display: flex !important;
        justify-content: space-between;
        font-size: 18px;
        /* font-weight: 900; */
    }

    .signature-section {
        display: flex !important;
        justify-content: space-between;
        margin-top: 50px;
        text-align: center;
        width: 100%;
    }

    .signature-box {
        width: 33%;
        position: relative;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        margin: 0 10px 10px 10px;
        height: 60px;
        position: relative;
    }

    .signature-line img {
        max-width: 100px;
        max-height: 50px;
        position: absolute;
        bottom: 2px;
        left: 50%;
        transform: translateX(-50%);
    }

    @media print {
        .quotation-container {
            padding: 0;
        }
    }
</style>

<div class="quotation-container">
    <!-- Header Selection Dropdown (No Print) -->
    <div class="no-print" style="margin-bottom: 15px; padding: 10px; background: #f5f5f5; border-radius: 5px;">
        <label for="headerSelect" style="font-weight: bold; margin-right: 10px;">เลือกหัวบริษัท:</label>
        <select id="headerSelect" onchange="changeHeader()" style="padding: 5px 10px; font-size: 14px; border-radius: 4px; border: 1px solid #ccc;">
            <?php
            $companies = \backend\models\Company::find()->all();
            foreach ($companies as $comp) {
                echo '<option value="' . Html::encode($comp->name) . '">' . Html::encode($comp->name) . '</option>';
            }
            ?>
        </select>
    </div>

    <!-- Header -->
    <div class="header">
        <table style="width: 100%;border: none;">
            <tr>
                <td style="width: 50%;border: none;text-align: left;">
                    <div class="logo-section">
                        <div class="logo">
                            <img id="companyLogo" src="../../backend/web/uploads/logo/mco_logo.png" width="50%" alt="">
                        </div>

                    </div>
                </td>
                <td style="width: 50%;border: none;text-align: center;">
                    <div class="quotation-title">Quotation</div>
                </td>
            </tr>
        </table>
    </div>
    <table style="width: 100%;border: none;">
        <tr>
            <td style="width: 50%;border: none;text-align: left;">
                <div id="companyInfo">
                    <div class="row-color" style="background-color: #8ea9db;">
                        <span class="info-label" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;Company Name :</span>
                    </div>
                    <div>
                        <span class="label-font" style="font-weight: bold;">&nbsp;<span id="companyName">M.C.O. COMPANY LIMITED</span></span>
                    </div>
                    <div class="text-infomation">
                        <span class="label-font" style="font-weight: bold;">&nbsp;<span id="companyAddress1">8/18 Koh-kloy Road,</span></span>
                    </div>
                    <div class="text-infomation">
                        <span class="label-font" style="font-weight: bold;">&nbsp;<span id="companyAddress2">Tambon Cherngnern,</span></span>
                    </div>
                    <div class="text-infomation">
                        <span class="label-font" style="font-weight: bold;">&nbsp;<span id="companyAddress3">Amphur Muang ,</span></span>
                    </div>
                    <div class="text-infomation">
                        <span class="label-font" style="font-weight: bold;">&nbsp;<span id="companyAddress4">Rayong 21000 Thailand.</span></span>
                    </div>
                    <div class="text-infomation">
                        <span class="label-font" style="font-weight: bold;">&nbsp;<span id="companyEmail">info@thai-mco.com</span></span>
                    </div>
                </div>
            </td>
            <td style="width: 50%;border: none;text-align: right;vertical-align: top;">
                <div style="margin-top: -2px">
                    <div class="info-row" style="margin-bottom: 5px;">
                        <span class="label-font label-left" id="labelDate">Date</span>
                        <span class="label-font" style="margin-top: 2px">: <?= Yii::$app->formatter->asDate($quotation->quotation_date, 'php:d/m/Y') ?></span>
                    </div>
                    <div class="info-row" style="margin-bottom: 5px;">
                        <span class="label-font label-left" id="labelOurRef">OUR REF</span>
                        <span class="label-font" style="margin-top: 2px">: <?= Html::encode($quotation->quotation_no) ?></span>
                    </div>
                    <div class="info-row" style="margin-bottom: 5px;">
                        <span class="label-font label-left" id="labelFrom">FROM</span>
                        <span class="label-font" style="margin-top: 2px">: <?= Html::encode(\backend\models\Employee::findFullName($quotation->sale_emp_id) ?? '') ?></span>
                    </div>
                    <div class="info-row" style="margin-bottom: 5px;">
                        <span class="label-font label-left" id="labelFax">FAX</span>
                        <span class="label-font" style="margin-top: 2px">: 66-38-619559</span>
                    </div>
                    <div class="info-row" style="margin-bottom: 5px;">
                        <span class="label-font label-left" id="labelTel">TEL</span>
                        <span class="label-font" style="margin-top: 2px">: 038-875258 875259</span>
                    </div>
                    <div class="info-row">
                        <span class="label-font label-left" id="labelYourRef">YOUR REF</span>
                        <span class="label-font" style="margin-top: 2px">: </span>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%;border: none;text-align: left;padding : 0px">
                <table style="width: 100%;border: none;">
                    <tr>
                        <td style="width: 100%;border: none;text-align: left;padding : 8px !important;">
                            <div class="row-color" style="background-color: #8ea9db;">
                                <span class="info-label" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;Customer :</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:100%; border:none; text-align:left; font-weight:bold;">
                            <div style="margin-left: 3px;margin-top: -9px;" class="label-font">
                                <?= Html::encode($customer_name) ?><br>
                                <?= Html::encode($customer_address) ?><br>
                                tax id : <?= Html::encode($customer_taxid) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 100%;border: none;text-align: left;margin-top: -25px;padding-top: 0px;">
                            <div class="address-section">
                                <div>
                                    <div class="info-row">
                                        <span class="info-label" id="labelCustomerTel" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;Tel : <?= Html::encode($phone) ?></span>
                                        <span class="label-font"></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label" id="labelCustomerFax" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;Fax :</span>
                                        <span class="label-font"></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label" id="labelTo" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;To :</span>
                                        <span class="label-font"><?= Html::encode($quotation->customer_name ?? '') ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label" id="labelPurchaser" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;Purchaser</span>
                                        <span class="label-font"></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label" id="labelProjectName" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;Project Name :</span>
                                        <span class="label-font"></span>
                                    </div>
                                </div>

                            </div>
                        </td>
                    </tr>
                </table>

            </td>
            <td style="width: 50%;border: none;text-align: left;vertical-align: top;">
                <div style="margin-left: 90px;margin-top: 90px;">
                    <img style="margin-left: 2px;" src="../../backend/web/uploads/logo/verity.jpg" width="50%" alt=""> <br>
                    <span style="margin-left: 12px;font-size: 20px; font-weight: 900; -webkit-text-stroke: 0.5px black;" class="label-font">Certified ISO 9001:2015</span> <br>
                    <span style="margin-left: 12px;font-size: 20px; font-weight: 900; -webkit-text-stroke: 0.5px black;" class="label-font">Certificate No: TH020629</span> <br>
                    <span style="margin-left: 12px; font-size: 20px; font-weight: 900; -webkit-text-stroke: 0.5px black;" class="label-font">Issued by Bureau Veritas Certification (Thailand) Ltd.</span>
                </div>
            </td>
        </tr>

    </table>

    <div class="row">
        <div class="col-lg-12">

        </div>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <!-- Customer Info -->

        </div>
        <div class="col-lg-4">
        </div>
    </div>
    <!-- Items Table -->
    <table>
        <thead>
            <tr style="background: #6d9de4ff !important;">
                <th class="label-font" style="width: 5%; background: #8aafe6ff !important; font-weight: 900; -webkit-text-stroke: 0.5px black;">ITEM</th>
                <th class="label-font" style="width: 40%; background: #8aafe6ff !important; font-weight: 900; -webkit-text-stroke: 0.5px black;">DESCRIPTION</th>
                <th class="label-font" style="width: 5%; background: #8aafe6ff !important; font-weight: 900; -webkit-text-stroke: 0.5px black;">QTY</th>
                <th class="label-font" style="width: 10%; background: #8aafe6ff !important; font-weight: 900; -webkit-text-stroke: 0.5px black;">UNIT</th>
                <th class="label-font" colspan="2" style="width: 20%; background: #8aafe6ff !important; font-weight: 900; -webkit-text-stroke: 0.5px black;">
                    MATERIAL<br>
                    <span style="font-size: 15px;text-align: left;margin-left: -20px">UNIT PRICE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; TOTAL</span>
                </th>
                <th class="label-font" colspan="2" style="width: 20%; background: #8aafe6ff !important; font-weight: 900; -webkit-text-stroke: 0.5px black;">
                    LABOUR<br>
                    <span style="font-size: 15px;">UNIT PRICE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; TOTAL</span>
                </th>
                <th class="label-font" style="width: 10%; background: #8aafe6ff !important; font-weight: 900; -webkit-text-stroke: 0.5px black;">TOTAL</th>
            </tr>
        </thead>
        <style>
            .striped-row {
                background-color: #d9e1f2;
            }
        </style>
        <tbody class="label-font">
            <?php if ($quotationLines): ?>
                <?php $itemNo = 1; ?>
                <?php foreach ($quotationLines as $line): ?>
                    <?php
                    $is_labour_price = 0;
                    if (substr($line->product_name, 0, 3) == 'SER') {
                        $is_labour_price = 1;
                    }
                    ?>
                    <tr class="<?= ($itemNo % 2 == 0) ? 'striped-row' : '' ?>">
                        <td class="text-detail"><?= $itemNo++ ?></td>
                        <?php
                            $product_name = $line->product_name;
                            $product_code = $line->product ? $line->product->code : '';
                            if ($product_code && strpos($product_name, $product_code) === 0) {
                                $product_name = trim(substr($product_name, strlen($product_code)));
                                // Remove leading parenthesis if present (from old format "Code (Name)")
                                if (strpos($product_name, '(') === 0) {
                                    $product_name = trim(substr($product_name, 1));
                                    // Remove trailing parenthesis if it matches the old format
                                    if (substr($product_name, -1) === ')') {
                                        $product_name = substr($product_name, 0, -1);
                                    }
                                }
                            }
                        ?>
                        <td class="description-cell text-detail"><?= Html::encode($product_name) ?></td>
                        <td class="text-detail"><?= number_format($line->qty, 1) ?></td>
                        <td class="text-detail"><?= Html::encode(\backend\models\Unit::findName($line->product->unit_id) ?? '') ?></td>
                        <td class="number-cell text-detail"><?= $is_labour_price == 0 ? number_format($line->line_price, 2) : '-' ?></td>
                        <td class="number-cell text-detail"><?= $is_labour_price == 0 ? number_format($line->line_total, 2) : '-' ?></td>
                        <td class="number-cell text-detail"><?= $is_labour_price == 1 ? number_format($line->line_price, 2) : '-' ?></td>
                        <td class="number-cell text-detail"><?= $is_labour_price == 1 ? number_format($line->line_total, 2) : '-' ?></td>
                        <td class="number-cell text-detail"><?= number_format($line->line_total, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Empty rows -->

            <?php for ($i = count($quotationLines); $i < 8; $i++): ?>
                <tr class="<?= (($i + 1) % 2 == 0) ? 'striped-row' : '' ?>">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary-section">
        <div style="width: 70%;">
            <div class="terms-section">
                <div class="label-font" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">EXCLUDES VAT AND SEPARATED PURCHASING IS NOT ALLOWED.</div>
                <div class="label-font"><span style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">CURRENCY :</span> Baht</div>
                <div class="label-font"><span style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">DELIVERY : <?= $quotation->delivery_day_text ?></span></div>
                <div class="label-font"><span style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">PAYMENT :</span> <?= \backend\models\Paymentterm::findName($quotation->payment_term_id) ?></div>
                <div class="label-font"><span style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">VALIDITY :</span> 7 day after today.</div>
                <div class="label-font" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">REMARK</div>
            </div>
        </div>

        <div style="width: 25%;" class="label-font">
            <table style="margin-bottom: 0;">
                <tr>
                    <td style="text-align: right; border: none; padding: 5px; font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">Total</td>
                    <td style="text-align: right; border: 1px solid #ccc; padding: 5px; background: #fff2cc; font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;"><?= number_format($subtotal, 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; border: none; padding: 5px; font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">Discount</td>
                    <td style="text-align: right; border: 1px solid #ccc; padding: 5px; background: #fff2cc; font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;"><?= number_format($discount, 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; border: none; padding: 5px; font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">Vat 7%</td>
                    <td style="text-align: right; border: 1px solid #ccc; padding: 5px; background: #fff2cc; font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;"><?= number_format($vat, 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; border: none; padding: 5px; font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">Grand Total</td>
                    <td style="text-align: right; border: 1px solid #ccc; padding: 5px; background: #fff2cc; font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">
                        <?= number_format($grandTotal, 2) ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-section label-font">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
            <div style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">ACCEPT ABOVE QUOTATION</div>
            <div style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">Purchaser</div>
        </div>

        <div class="signature-box">
            <div class="signature-line">
                <?php
                $requestor_signature = \backend\models\User::findEmployeeSignature($quotation->created_by);
                if (!empty($requestor_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?= $requestor_signature ?>" alt="Requestor Signature">
                <?php endif; ?>
            </div>
            <div>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
            <div style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">QUOTED BY</div>
        </div>

        <div class="signature-box">
            <div class="signature-line">
                <?php
                $approve_signature = \backend\models\User::findEmployeeSignature($quotation->approve_by);
                if (!empty($approve_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?= $approve_signature ?>" alt="Requestor Signature">
                <?php endif; ?>
            </div>
            <div>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
            <div style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">AUTHORIZED SIGNATURE</div>
        </div>
    </div>
</div>

<script>
    function changeHeader() {
        const headerSelect = document.getElementById('headerSelect');
        const selectedValues = headerSelect.value;

        // Update Company Name Only (Logo remains fixed as per request)
        const companyName = document.getElementById('companyName');
        if (companyName) {
            companyName.textContent = selectedValues;
        }
    }
</script>

<!-- Print Button -->
<div class="no-print" style="text-align: center; margin: 20px; display: flex; justify-content: center; align-items: center; gap: 15px;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <label for="languageSelect" style="font-weight: bold; margin: 0;">ภาษา / Language:</label>
        <select id="languageSelect" onchange="changeLanguage()" style="padding: 8px 15px; font-size: 14px; border-radius: 4px; border: 1px solid #ccc;">
            <option value="en" selected>English</option>
            <option value="th">ไทย (Thai)</option>
        </select>
    </div>

    <button onclick="window.print()" class="btn btn-primary">
        <i class="glyphicon glyphicon-print"></i> Print
    </button>
    <a href="<?= \yii\helpers\Url::to(['print', 'id' => $quotation->id, 'format' => 'pdf']) ?>"
        class="btn btn-danger" target="_blank">
        <i class="glyphicon glyphicon-file"></i> Download PDF
    </a>
    <button onclick="window.close()" class="btn btn-default">Close</button>
</div>

<script>
    function changeLanguage() {
        const lang = document.getElementById('languageSelect').value;

        // Define translations
        const translations = {
            quotationTitle: {
                en: 'Quotation',
                th: 'ใบเสนอราคา'
            },
            companyNameLabel: {
                en: 'Company Name :',
                th: 'ชื่อบริษัท :'
            },
            customerLabel: {
                en: 'Customer :',
                th: 'ลูกค้า :'
            },
            headerLabels: {
                date: {
                    en: 'Date',
                    th: 'วันที่'
                },
                ourRef: {
                    en: 'OUR REF',
                    th: 'เลขที่เอกสาร'
                },
                from: {
                    en: 'FROM',
                    th: 'จาก'
                },
                fax: {
                    en: 'FAX',
                    th: 'แฟกซ์'
                },
                tel: {
                    en: 'TEL',
                    th: 'โทร'
                },
                yourRef: {
                    en: 'YOUR REF',
                    th: 'เลขที่อ้างอิง'
                }
            },
            customerInfoLabels: {
                tel: {
                    en: 'Tel :',
                    th: 'โทร :'
                },
                fax: {
                    en: 'Fax :',
                    th: 'แฟกซ์ :'
                },
                to: {
                    en: 'To :',
                    th: 'ถึง :'
                },
                purchaser: {
                    en: 'Purchaser',
                    th: 'ผู้ซื้อ'
                },
                projectName: {
                    en: 'Project Name :',
                    th: 'ชื่อโครงการ :'
                }
            },
            tableHeaders: {
                item: {
                    en: 'ITEM',
                    th: 'ลำดับ'
                },
                description: {
                    en: 'DESCRIPTION',
                    th: 'รายละเอียด'
                },
                qty: {
                    en: 'QTY',
                    th: 'จำนวน'
                },
                unit: {
                    en: 'UNIT',
                    th: 'หน่วย'
                },
                material: {
                    en: 'MATERIAL',
                    th: 'วัสดุ'
                },
                labour: {
                    en: 'LABOUR',
                    th: 'แรงงาน'
                },
                unitPrice: {
                    en: 'UNIT PRICE',
                    th: 'ราคา/หน่วย'
                },
                total: {
                    en: 'TOTAL',
                    th: 'รวม'
                }
            },
            summaryLabels: {
                excludeVat: {
                    en: 'EXCLUDES VAT AND SEPARATED PURCHASING IS NOT ALLOWED.',
                    th: 'ราคายังไม่รวมภาษีมูลค่าเพิ่ม และไม่อนุญาตให้แยกซื้อ'
                },
                currency: {
                    en: 'CURRENCY :',
                    th: 'สกุลเงิน :'
                },
                delivery: {
                    en: 'DELIVERY :',
                    th: 'การจัดส่ง :'
                },
                payment: {
                    en: 'PAYMENT :',
                    th: 'การชำระเงิน :'
                },
                validity: {
                    en: 'VALIDITY :',
                    th: 'อายุใบเสนอราคา :'
                },
                validityText: {
                    en: '7 day after today.',
                    th: '7 วันหลังจากวันนี้'
                },
                remark: {
                    en: 'REMARK',
                    th: 'หมายเหตุ'
                },
                totalLabel: {
                    en: 'Total',
                    th: 'รวม'
                },
                discount: {
                    en: 'Discount',
                    th: 'ส่วนลด'
                },
                vat: {
                    en: 'Vat 7%',
                    th: 'ภาษีมูลค่าเพิ่ม 7%'
                },
                grandTotal: {
                    en: 'Grand Total',
                    th: 'รวมทั้งสิ้น'
                }
            },
            signatureLabels: {
                acceptQuotation: {
                    en: 'ACCEPT ABOVE QUOTATION',
                    th: 'ยอมรับใบเสนอราคาข้างต้น'
                },
                purchaser: {
                    en: 'Purchaser',
                    th: 'ผู้ซื้อ'
                },
                quotedBy: {
                    en: 'QUOTED BY',
                    th: 'ผู้เสนอราคา'
                },
                authorizedSignature: {
                    en: 'AUTHORIZED SIGNATURE',
                    th: 'ผู้มีอำนาจลงนาม'
                }
            }
        };

        // Update quotation title
        document.querySelector('.quotation-title').textContent = translations.quotationTitle[lang];

        // Update header labels (Date, OUR REF, FROM, FAX, TEL, YOUR REF)
        document.getElementById('labelDate').textContent = translations.headerLabels.date[lang];
        document.getElementById('labelOurRef').textContent = translations.headerLabels.ourRef[lang];
        document.getElementById('labelFrom').textContent = translations.headerLabels.from[lang];
        document.getElementById('labelFax').textContent = translations.headerLabels.fax[lang];
        document.getElementById('labelTel').textContent = translations.headerLabels.tel[lang];
        document.getElementById('labelYourRef').textContent = translations.headerLabels.yourRef[lang];

        // Update customer info labels (Tel, Fax, To, Purchaser, Project Name)
        const phoneValue = document.getElementById('labelCustomerTel').textContent.split(':')[1] || '';
        document.getElementById('labelCustomerTel').innerHTML = '&nbsp;' + translations.customerInfoLabels.tel[lang] + phoneValue;
        document.getElementById('labelCustomerFax').innerHTML = '&nbsp;' + translations.customerInfoLabels.fax[lang];
        document.getElementById('labelTo').innerHTML = '&nbsp;' + translations.customerInfoLabels.to[lang];
        document.getElementById('labelPurchaser').innerHTML = '&nbsp;' + translations.customerInfoLabels.purchaser[lang];
        document.getElementById('labelProjectName').innerHTML = '&nbsp;' + translations.customerInfoLabels.projectName[lang];

        // Update Company Name label
        const companyNameLabels = document.querySelectorAll('.info-label');
        if (companyNameLabels[0]) {
            companyNameLabels[0].innerHTML = '&nbsp;' + translations.companyNameLabel[lang];
        }

        // Update Customer label
        if (companyNameLabels[1]) {
            companyNameLabels[1].innerHTML = '&nbsp;' + translations.customerLabel[lang];
        }

        // Update table headers
        const tableHeaders = document.querySelectorAll('table thead th');
        if (tableHeaders.length >= 9) {
            tableHeaders[0].textContent = translations.tableHeaders.item[lang];
            tableHeaders[1].textContent = translations.tableHeaders.description[lang];
            tableHeaders[2].textContent = translations.tableHeaders.qty[lang];
            tableHeaders[3].textContent = translations.tableHeaders.unit[lang];

            // Material header
            tableHeaders[4].innerHTML = translations.tableHeaders.material[lang] + '<br><span style="font-size: 15px;text-align: left;margin-left: -20px">' +
                translations.tableHeaders.unitPrice[lang] + ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' +
                translations.tableHeaders.total[lang] + '</span>';

            // Labour header  
            tableHeaders[5].innerHTML = translations.tableHeaders.labour[lang] + '<br><span style="font-size: 15px;">' +
                translations.tableHeaders.unitPrice[lang] + ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' +
                translations.tableHeaders.total[lang] + '</span>';

            tableHeaders[6].textContent = translations.tableHeaders.total[lang];
        }

        // Update summary section
        const termsSectionDivs = document.querySelectorAll('.terms-section .label-font');
        if (termsSectionDivs.length >= 6) {
            termsSectionDivs[0].textContent = translations.summaryLabels.excludeVat[lang];
            termsSectionDivs[1].innerHTML = '<span style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">' +
                translations.summaryLabels.currency[lang] + '</span> Baht';
            termsSectionDivs[2].innerHTML = '<span style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">' +
                translations.summaryLabels.delivery[lang] + ' <?= $quotation->delivery_day_text ?></span>';
            termsSectionDivs[3].innerHTML = '<span style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">' +
                translations.summaryLabels.payment[lang] + '</span> <?= \backend\models\Paymentterm::findName($quotation->payment_term_id) ?>';
            termsSectionDivs[4].innerHTML = '<span style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">' +
                translations.summaryLabels.validity[lang] + '</span> ' + translations.summaryLabels.validityText[lang];
            termsSectionDivs[5].textContent = translations.summaryLabels.remark[lang];
        }

        // Update summary table
        const summaryTable = document.querySelectorAll('.summary-section table td');
        if (summaryTable.length >= 8) {
            summaryTable[0].textContent = translations.summaryLabels.totalLabel[lang];
            summaryTable[2].textContent = translations.summaryLabels.discount[lang];
            summaryTable[4].textContent = translations.summaryLabels.vat[lang];
            summaryTable[6].textContent = translations.summaryLabels.grandTotal[lang];
        }

        // Update signature labels
        const signatureBoxes = document.querySelectorAll('.signature-box div');
        if (signatureBoxes.length >= 12) {
            signatureBoxes[1].textContent = translations.signatureLabels.acceptQuotation[lang];
            signatureBoxes[2].textContent = translations.signatureLabels.purchaser[lang];
            signatureBoxes[6].textContent = translations.signatureLabels.quotedBy[lang];
            signatureBoxes[11].textContent = translations.signatureLabels.authorizedSignature[lang];
        }
    }
</script>