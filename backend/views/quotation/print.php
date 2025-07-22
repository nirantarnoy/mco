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
//print_r($customer_info);return;
$customer_name = $customer_info !== null ? $customer_info['name'] : '';
$customer_address = $customer_info !== null ? $customer_info['home_number'].' '.$customer_info['street'].' '.$customer_info['aisle'].', '.$customer_info['district_name'].', '.$customer_info['city_name'].', '.$customer_info['province_name'].', '.$customer_info['zipcode'] : '';
$phone = $customer_info !== null ? $customer_info['phone'] : '';
$email = $customer_info !== null ? $customer_info['email'] : '';
?>

<style>
    .quotation-container {
        max-width: 210mm;
        margin: 0 auto;
        padding: 20px;
        background: white;
    }

    .header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
    }

    .logo-section {
        display: flex;
        align-items: center;
    }

    .logo {
        font-size: 36px;
        font-weight: bold;
        color: #FF0000;
        margin-right: 10px;
    }

    .logo .m { color: #FFA500; }
    .logo .c { color: #000080; }
    .logo .o { color: #008000; }

    .th-flag {
        background: linear-gradient(to bottom, #FF0000 33%, #FFFFFF 33% 66%, #000080 66%);
        width: 30px;
        height: 20px;
        display: inline-block;
        margin-left: 5px;
    }

    .quotation-title {
        font-size: 24px;
        font-weight: bold;
    }

    .info-section {
        background: #E6F2FF;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
    }

    .info-row {
        display: flex;
        margin-bottom: 5px;
    }

    .info-label {
        font-weight: bold;
        margin-right: 10px;
        min-width: 100px;
    }

    .address-section {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .address-box {
        width: 48%;
    }

    .address-header {
        background: #E6F2FF;
        padding: 5px 10px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
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
        margin-top: 20px;
    }

    .summary-box {
        border: 1px solid #ccc;
        padding: 10px;
    }

    .summary-label {
        font-weight: bold;
        margin-right: 10px;
    }

    .terms-section {
        margin-top: 20px;
        font-size: 12px;
    }

    .signature-section {
        display: flex;
        justify-content: space-around;
        margin-top: 50px;
        text-align: center;
    }

    .signature-box {
        width: 30%;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        margin: 50px 0 10px 0;
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
        <div class="logo-section">
            <div class="logo">
                <img src="../../backend/web/uploads/logo/mco_logo.png" width="20%" alt="">
            </div>

        </div>
        <div class="quotation-title">Quotation</div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <!-- Company Info -->
            <div class="info-section">
                <div>
                    <span class="info-label">Company Name :</span>
                </div>
                <div class="info-row">
                    <span>M.C.O. COMPANY LIMITED</span>
                </div>
                <div class="info-row">
                    <span>5/15 Koh-Kloy Road,</span>
                </div>
                <div class="info-row">
                    <span>Tambon Chompoeng,</span>
                </div>
                <div class="info-row">
                    <span>Amphur Muang ,</span>
                </div>
                <div class="info-row">
                    <span>Rayong 21000 Thailand.</span>
                </div>
                <div class="info-row">
                    <span>info@thai-mco.com</span>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <!-- Quotation Details -->
            <div style="justify-content: space-between; margin-bottom: 20px;">
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
                        <span>: <?= Html::encode($quotation->created_by ?? '') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">FAX</span>
                        <span>: 66-38-619559</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">TEL</span>
                        <span>: 038-875258 875259</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">YOUR REF</span>
                        <span>: </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <table style="width: 100%;border: none;">
                <tr>
                    <td style="width: 50%;border: none;text-align: left;">
                        <div class="address-header">Customer :</div>
                    </td>
                    <td style="width: 50%;border: none;text-align: left;"></td>
                </tr>
                <tr>
                    <td colspan="2" style="border: none;text-align: left;">
                        <?= Html::encode($customer_name).'<br />'.Html::encode($customer_address).'<br />' ?>
                    </td>
                </tr>
            </table>

        </div>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <!-- Customer Info -->
            <div class="address-section">
                <div class="address-box">

                    <div>
                        <div class="info-row">
                            <span class="info-label">Tel : <?= Html::encode($phone) ?></span>
                            <span></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Fax :</span>
                            <span></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">To :</span>
                            <span><?= Html::encode($quotation->customer_name ?? 'Purchaser') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Purchaser</span>
                            <span></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Project Name :</span>
                            <span></span>
                        </div>
                    </div>
                </div>


            </div>
        </div>
        <div class="col-lg-4">
            <div style="text-align: right;bottom: 0px;">
                <div>Certificate ISO 9001:2015</div>
                <div>Certificate No. TH08/2024</div>
                <div>Issued by Bureau Veritas Certification (Thailand) Ltd.</div>
            </div>
        </div>
    </div>
    <!-- Items Table -->
    <table>
        <thead>
        <tr>
            <th style="width: 5%;">ITEM</th>
            <th style="width: 40%;">DESCRIPTION</th>
            <th style="width: 5%;">QTY</th>
            <th style="width: 10%;">UNIT</th>
            <th colspan="2" style="width: 20%;">
                MATERIAL<br>
                <span style="font-size: 11px;">UNIT PRICE &nbsp;&nbsp;&nbsp; TOTAL</span>
            </th>
            <th colspan="2" style="width: 20%;">
                LABOUR<br>
                <span style="font-size: 11px;">UNIT PRICE &nbsp;&nbsp;&nbsp; TOTAL</span>
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
                    <td><?= Html::encode($line->product->unit_id ?? '') ?></td>
                    <td class="number-cell"><?= number_format($line->line_price, 2) ?></td>
                    <td class="number-cell"><?= number_format($line->line_total, 2) ?></td>
                    <td class="number-cell">-</td>
                    <td class="number-cell">-</td>
                    <td class="number-cell"><?= number_format($line->line_total, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Empty rows -->
        <?php for ($i = count($quotationLines); $i < 8; $i++): ?>
            <tr>
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
                <div><strong>EXCLUDES VAT AND SEPARATED PURCHASING IS NOT ALLOWED.</strong></div>
                <div><strong>CURRENCY :</strong> Baht</div>
                <div><strong>DELIVERY :</strong></div>
                <div><strong>PAYMENT :</strong> Cash</div>
                <div><strong>VALIDITY :</strong> 7 day after today.</div>
                <div><strong>REMARK</strong></div>
            </div>
        </div>

        <div style="width: 25%;">
            <table style="margin-bottom: 0;">
                <tr>
                    <td style="text-align: right; border: none; padding: 5px;"><strong>Total</strong></td>
                    <td style="text-align: right; border: 1px solid #ccc; padding: 5px;"><?= number_format($subtotal, 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; border: none; padding: 5px;"><strong>Discount</strong></td>
                    <td style="text-align: right; border: 1px solid #ccc; padding: 5px;"><?= number_format($discount, 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; border: none; padding: 5px;"><strong>Vat 7%</strong></td>
                    <td style="text-align: right; border: 1px solid #ccc; padding: 5px;"><?= number_format($vat, 2) ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; border: none; padding: 5px;"><strong>Grand Total</strong></td>
                    <td style="text-align: right; border: 1px solid #ccc; padding: 5px; background: #E6F2FF;">
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
            <div>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
            <div><strong>ACCEPT ABOVE QUOTATION</strong></div>
            <div>Purchaser</div>
        </div>

        <div class="signature-box">
            <div class="signature-line"></div>
            <div>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
            <div><strong>QUOTED BY</strong></div>
        </div>

        <div class="signature-box">
            <div class="signature-line"></div>
            <div>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
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