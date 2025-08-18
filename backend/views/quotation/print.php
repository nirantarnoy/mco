<?php
use yii\helpers\Html;

$this->title = 'Quotation - ' . $quotation->quotation_no;

// คำนวณราคารวม
$subtotal = 0;
$vat = 0;
$discount = 0;
$grandTotal = 0;

if ($quotationLines) {
    foreach ($quotationLines as $line) {
        $subtotal += $line->line_total;
    }
}

// คำนวณ VAT 7%
$vat = $subtotal * 0.07;
$grandTotal = $subtotal + $vat - $discount;

$customer_info = \backend\models\Customer::findCustomerInfo($quotation->customer_id);
$customer_name = $customer_info !== null ? $customer_info['name'] : '';
$customer_address = $customer_info !== null ? $customer_info['home_number'].' '.$customer_info['street'].' '.$customer_info['aisle'].', '.$customer_info['district_name'].', '.$customer_info['city_name'].', '.$customer_info['province_name'].', '.$customer_info['zipcode'] : '';
$phone = $customer_info !== null ? $customer_info['phone'] : '';
$email = $customer_info !== null ? $customer_info['email'] : '';
?>

<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .quotation-container {
        max-width: 210mm;
        width: 100%;
        margin: 0 auto;
        padding: 10mm;
        background: white;
        font-family: Arial, sans-serif;
        font-size: 12px;
        line-height: 1.2;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        height: 60px;
    }

    .logo-section img {
        height: 50px;
        width: auto;
    }

    .quotation-title {
        font-size: 20px;
        font-weight: bold;
    }

    .info-section {
        background: #E6F2FF;
        padding: 8px;
        margin-bottom: 12px;
        border-radius: 3px;
        font-size: 11px;
    }

    .info-row {
        display: flex;
        margin-bottom: 2px;
        line-height: 1.3;
    }

    .info-label {
        font-weight: bold;
        margin-right: 8px;
        min-width: 80px;
        font-size: 11px;
    }

    .row {
        display: flex;
        margin-bottom: 10px;
    }

    .col-lg-6 {
        width: 50%;
        padding-right: 10px;
    }

    .col-lg-8 {
        width: 66.67%;
    }

    .col-lg-4 {
        width: 33.33%;
    }

    .col-lg-12 {
        width: 100%;
    }

    .address-header {
        background: #E6F2FF;
        padding: 4px 8px;
        font-weight: bold;
        margin-bottom: 8px;
        font-size: 11px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
        font-size: 10px;
    }

    th {
        background: #E6F2FF;
        border: 1px solid #999;
        padding: 4px;
        text-align: center;
        font-weight: bold;
        font-size: 9px;
        line-height: 1.1;
    }

    td {
        border: 1px solid #999;
        padding: 3px;
        text-align: center;
        font-size: 10px;
        line-height: 1.2;
        height: 18px;
    }

    .description-cell {
        text-align: left !important;
        padding-left: 5px !important;
    }

    .number-cell {
        text-align: right !important;
        padding-right: 5px !important;
    }

    .summary-section {
        display: flex;
        justify-content: space-between;
        margin-top: 12px;
        font-size: 10px;
    }

    .terms-section {
        width: 70%;
        font-size: 10px;
        line-height: 1.3;
    }

    .terms-section > div {
        margin-bottom: 2px;
    }

    .signature-section {
        display: flex;
        justify-content: space-around;
        margin-top: 20px;
        text-align: center;
        font-size: 10px;
    }

    .signature-box {
        width: 30%;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        margin: 25px 0 8px 0;
        height: 25px;
        position: relative;
    }

    .signature-line img {
        max-height: 20px;
        width: auto;
        position: absolute;
        bottom: 5px;
        left: 50%;
        transform: translateX(-50%);
    }

    /* ปรับสำหรับการพิมพ์ */
    @media print {
        @page {
            size: A4;
            margin: 10mm;
        }

        .quotation-container {
            padding: 0;
            max-width: none;
            width: 100%;
            font-size: 11px;
        }

        .no-print {
            display: none !important;
        }

        table {
            font-size: 9px;
        }

        th {
            font-size: 8px;
        }

        td {
            font-size: 9px;
            height: 16px;
        }

        .header {
            height: 50px;
            margin-bottom: 10px;
        }

        .signature-section {
            margin-top: 15px;
        }

        .signature-line {
            margin: 20px 0 6px 0;
            height: 20px;
        }
    }

    /* ปรับสำหรับหน้าจอขนาดเล็ก */
    @media screen and (max-width: 768px) {
        .quotation-container {
            padding: 5mm;
            font-size: 11px;
        }

        .row {
            flex-direction: column;
        }

        .col-lg-6, .col-lg-8, .col-lg-4 {
            width: 100%;
            padding-right: 0;
            margin-bottom: 10px;
        }
    }
</style>

<div class="quotation-container">
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <img src="../../backend/web/uploads/logo/mco_logo_2.png" alt="MCO Logo">
        </div>
        <div class="quotation-title">Quotation</div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <!-- Company Info -->
            <div class="info-section">
                <div><span class="info-label">Company Name :</span></div>
                <div class="info-row"><span>M.C.O. COMPANY LIMITED</span></div>
                <div class="info-row"><span>8/18 Koh-kloy Road, Tambon Cherngnern,</span></div>
                <div class="info-row"><span>Amphur Muang, Rayong 21000 Thailand.</span></div>
                <div class="info-row"><span>info@thai-mco.com</span></div>
            </div>
        </div>
        <div class="col-lg-6">
            <!-- Quotation Details -->
            <div>
                <div class="info-row">
                    <span class="info-label">Date</span>
                    <span>: <?= Yii::$app->formatter->asDate($quotation->quotation_date, 'php:m/d/Y') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">OUR REF.</span>
                    <span>: <?= Html::encode($quotation->quotation_no) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">FROM</span>
                    <span>: <?= Html::encode(\backend\models\Employee::findFullName($quotation->sale_emp_id) ?? '') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">FAX</span>
                    <span>: 66-38-619559</span>
                </div>
                <div class="info-row">
                    <span class="info-label">TEL</span>
                    <span>: 038-875258 875259</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <table style="border: none; margin-bottom: 8px;">
                <tr>
                    <td style="border: none; text-align: left; padding: 4px;">
                        <div class="address-header">Customer :</div>
                        <?= Html::encode($customer_name).'<br />'.Html::encode($customer_address) ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="info-row">
                <span class="info-label">Tel : <?= Html::encode($phone) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">To :</span>
                <span><?= Html::encode($quotation->customer_name ?? 'Purchaser') ?></span>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
        <tr>
            <th style="width: 5%;">ITEM</th>
            <th style="width: 35%;">DESCRIPTION</th>
            <th style="width: 6%;">QTY</th>
            <th style="width: 8%;">UNIT</th>
            <th colspan="2" style="width: 18%;">
                MATERIAL<br>
                <span style="font-size: 8px;">UNIT PRICE / TOTAL</span>
            </th>
            <th colspan="2" style="width: 18%;">
                LABOUR<br>
                <span style="font-size: 8px;">UNIT PRICE / TOTAL</span>
            </th>
            <th style="width: 10%;">TOTAL</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($quotationLines): ?>
            <?php $itemNo = 1; ?>
            <?php foreach ($quotationLines as $line): ?>
                <tr>
                    <td><?= $itemNo++ ?></td>
                    <td class="description-cell"><?= Html::encode($line->product->name ?? $line->product_name) ?></td>
                    <td><?= number_format($line->qty, 0) ?></td>
                    <td><?= Html::encode(\backend\models\Unit::findName($line->product->unit_id) ?? '') ?></td>
                    <td class="number-cell"><?= number_format($line->line_price, 2) ?></td>
                    <td class="number-cell"><?= number_format($line->line_total, 2) ?></td>
                    <td class="number-cell">-</td>
                    <td class="number-cell">-</td>
                    <td class="number-cell"><?= number_format($line->line_total, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Empty rows - ลดจำนวนแถวว่าง -->
        <?php for ($i = count($quotationLines); $i < 5; $i++): ?>
            <tr>
                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary-section">
        <div class="terms-section">
            <div><strong>EXCLUDES VAT AND SEPARATED PURCHASING IS NOT ALLOWED.</strong></div>
            <div><strong>CURRENCY :</strong> Baht &nbsp;&nbsp; <strong>DELIVERY :</strong> &nbsp;&nbsp; <strong>PAYMENT :</strong> Cash</div>
            <div><strong>VALIDITY :</strong> 7 day after today.</div>
        </div>

        <div style="width: 28%;">
            <table style="margin-bottom: 0; font-size: 10px;">
                <tr>
                    <td style="text-align: right; border: none; padding: 3px;"><strong>Total</strong></td>
                    <td style="text-align: right; border: 1px solid #999; padding: 3px;"><?= number_format($subtotal, 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; border: none; padding: 3px;"><strong>Discount</strong></td>
                    <td style="text-align: right; border: 1px solid #999; padding: 3px;"><?= number_format($discount, 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; border: none; padding: 3px;"><strong>Vat 7%</strong></td>
                    <td style="text-align: right; border: 1px solid #999; padding: 3px;"><?= number_format($vat, 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; border: none; padding: 3px;"><strong>Grand Total</strong></td>
                    <td style="text-align: right; border: 1px solid #999; padding: 3px; background: #E6F2FF;">
                        <strong><?= number_format($grandTotal, 2) ?></strong>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>( _________________ )</div>
            <div><strong>ACCEPT ABOVE QUOTATION</strong></div>
            <div>Purchaser</div>
        </div>

        <div class="signature-box">
            <div class="signature-line">
                <?php
                $requestor_signature = \backend\models\User::findEmployeeSignature($quotation->created_by);
                if(!empty($requestor_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?=$requestor_signature?>" alt="Requestor Signature">
                <?php endif; ?>
            </div>
            <div>( _________________ )</div>
            <div><strong>QUOTED BY</strong></div>
        </div>

        <div class="signature-box">
            <div class="signature-line">
                <?php
                $approve_signature = \backend\models\User::findEmployeeSignature($quotation->approve_by);
                if(!empty($approve_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?=$approve_signature?>" alt="Approve Signature">
                <?php endif; ?>
            </div>
            <div>( _________________ )</div>
            <div><strong>AUTHORIZED SIGNATURE</strong></div>
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