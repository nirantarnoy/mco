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
$customer_address = $customer_info !== null && count($customer_info) > 0 ? $customer_info['home_number'] . ' ' . $customer_info['street'] . ' ' . $customer_info['aisle'] . ', ' . $customer_info['district_name'] . ', ' . $customer_info['city_name'] . ', ' . $customer_info['province_name'] . ', ' . $customer_info['zipcode'] : '';
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
    <!-- Header -->
    <div class="header">
        <table style="width: 100%;border: none;">
            <tr>
                <td style="width: 50%;border: none;text-align: left;">
                    <div class="logo-section">
                        <div class="logo">
                            <img src="../../backend/web/uploads/logo/mco_logo.png" width="50%" alt="">
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
                <div>
                    <div class="row-color" style="background-color: #8ea9db;">
                        <span class="info-label" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;Company Name :</span>
                    </div>
                    <div>
                        <span class="label-font" style="font-weight: bold;">&nbsp;M.C.O. COMPANY LIMITED</span>
                    </div>
                    <div class="text-infomation">
                        <span class="label-font" style="font-weight: bold;">&nbsp;8/18 Koh-kloy Road,</span>
                    </div>
                    <div class="text-infomation">
                        <span class="label-font" style="font-weight: bold;">&nbsp;Tambon Cherngnern,</span>
                    </div>
                    <div class="text-infomation">
                        <span class="label-font" style="font-weight: bold;">&nbsp;Amphur Muang ,</span>
                    </div>
                    <div class="text-infomation">
                        <span class="label-font" style="font-weight: bold;">&nbsp;Rayong 21000 Thailand.</span>
                    </div>
                    <div class="text-infomation">
                        <span class="label-font" style="font-weight: bold;">&nbsp;info@thai-mco.com</span>
                    </div>
                </div>
            </td>
            <td style="width: 50%;border: none;text-align: right;vertical-align: top;">
                <div style="margin-top: -2px">
                    <div class="info-row" style="margin-bottom: 5px;">
                        <span class="label-font label-left">Date</span>
                        <span class="label-font" style="margin-top: 2px">: <?= Yii::$app->formatter->asDate($quotation->quotation_date, 'php:m/d/Y') ?></span>
                    </div>
                    <div class="info-row" style="margin-bottom: 5px;">
                        <span class="label-font label-left">OUR REF</span>
                        <span class="label-font" style="margin-top: 2px">: <?= Html::encode($quotation->quotation_no) ?></span>
                    </div>
                    <div class="info-row" style="margin-bottom: 5px;">
                        <span class="label-font label-left">FROM</span>
                        <span class="label-font" style="margin-top: 2px">: <?= Html::encode(\backend\models\Employee::findFullName($quotation->sale_emp_id) ?? '') ?></span>
                    </div>
                    <div class="info-row" style="margin-bottom: 5px;">
                        <span class="label-font label-left">FAX</span>
                        <span class="label-font" style="margin-top: 2px">: 66-38-619559</span>
                    </div>
                    <div class="info-row" style="margin-bottom: 5px;">
                        <span class="label-font label-left">TEL</span>
                        <span class="label-font" style="margin-top: 2px">: 038-875258 875259</span>
                    </div>
                    <div class="info-row">
                        <span class="label-font label-left">YOUR REF</span>
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
                                        <span class="info-label" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;Tel : <?= Html::encode($phone) ?></span>
                                        <span class="label-font"></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;Fax :</span>
                                        <span class="label-font"></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;To :</span>
                                        <span class="label-font"><?= Html::encode($quotation->customer_name ?? '') ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;Purchaser</span>
                                        <span class="label-font"></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label" style="font-weight: 900; font-size: 20px; -webkit-text-stroke: 0.5px black;">&nbsp;Project Name :</span>
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
                        <td class="description-cell text-detail"><?= Html::encode($line->product_name ?? '') ?></td>
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

<!-- Print Button -->
<div class="no-print" style="text-align: center; margin: 20px;">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="glyphicon glyphicon-print"></i> Print
    </button>
    <a href="<?= \yii\helpers\Url::to(['print', 'id' => $quotation->id, 'format' => 'pdf']) ?>"
        class="btn btn-danger" target="_blank">
        <i class="glyphicon glyphicon-file"></i> Download PDF
    </a>
    <button onclick="window.close()" class="btn btn-default">Close</button>
</div>