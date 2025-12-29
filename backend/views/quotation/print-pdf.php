<?php
use yii\helpers\Html;

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
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "thsarabun", Arial, sans-serif;
            font-size: 14pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .quotation-container {
            padding: 0;
        }

        .header {
            margin-bottom: 30px;
        }

        .header table {
            width: 100%;
            border: none;
        }

        .logo {
            font-size: 36pt;
            font-weight: bold;
        }

        .logo .m { color: #FFA500; }
        .logo .c { color: #000080; }
        .logo .o { color: #008000; }

        .quotation-title {
            font-size: 24pt;
            font-weight: bold;
            text-align: right;
        }

        .info-section {
            background-color: #E6F2FF;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .info-row {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }

        .address-section {
            margin-bottom: 20px;
        }

        .address-section table {
            width: 100%;
            border: none;
        }

        .address-header {
            background-color: #E6F2FF;
            padding: 5px 10px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.items th {
            background-color: #E6F2FF;
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
            font-weight: bold;
        }

        table.items td {
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
            margin-top: 20px;
        }

        .summary-section table {
            width: 100%;
            border: none;
        }

        .terms-section {
            font-size: 12pt;
        }

        .signature-section {
            margin-top: 50px;
        }

        .signature-section table {
            width: 100%;
            border: none;
        }

        .signature-box {
            text-align: center;
            padding: 0 20px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin: 50px 0 10px 0;
            width: 150px;
            display: inline-block;
        }

        .pagebreak {
            page-break-after: always;
        }
    </style>
</head>
<body>

<div class="quotation-container">
    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td width="50%">
                    <span class="logo">
                        <span class="m">M</span><span class="c">C</span><span class="o">O</span>
                    </span>
                </td>
                <td width="50%" class="quotation-title">
                    Quotation
                </td>
            </tr>
        </table>
    </div>

    <!-- Company Info -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Company Name :</span>
            <span>M.C.O. COMPANY LIMITED</span>
        </div>
        <div class="info-row">
            <span class="info-label"></span>
            <span>5/15 Koh-Kloy Road,</span>
        </div>
        <div class="info-row">
            <span class="info-label"></span>
            <span>Tambon Chompoeng,</span>
        </div>
        <div class="info-row">
            <span class="info-label"></span>
            <span>Amphur Muang ,</span>
        </div>
        <div class="info-row">
            <span class="info-label"></span>
            <span>Rayong 21000 Thailand.</span>
        </div>
        <div class="info-row">
            <span class="info-label"></span>
            <span>info@thai-mco.com</span>
        </div>
    </div>

    <!-- Quotation Details -->
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td width="50%">
                <div class="info-row">
                    <span class="info-label">Date</span>
                    <span>: <?= Yii::$app->formatter->asDate($quotation->quotation_date, 'php:d/m/Y') ?></span>
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
                    <span>: 66-38-619569</span>
                </div>
                <div class="info-row">
                    <span class="info-label">TEL</span>
                    <span>: 038-875259 875229</span>
                </div>
                <div class="info-row">
                    <span class="info-label">YOUR REF</span>
                    <span>: </span>
                </div>
            </td>
            <td width="50%" style="text-align: right; vertical-align: top;">
                <div>Certificate ISO 9001:2015</div>
                <div>Certificate No. TH08/2024</div>
                <div>Issued by Bureau Veritas Certification (Thailand) Ltd.</div>
            </td>
        </tr>
    </table>

    <!-- Customer Info -->
    <div class="address-section">
        <div class="address-header">Customer :</div>
        <div>
            <div class="info-row">
                <span class="info-label">Tel :</span>
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

    <!-- Items Table -->
    <table class="items">
        <thead>
        <tr>
            <th style="width: 5%;">ITEM</th>
            <th style="width: 40%;">DESCRIPTION</th>
            <th style="width: 5%;">QTY</th>
            <th style="width: 10%;">UNIT</th>
            <th colspan="2" style="width: 20%;">
                MATERIAL<br>
                <span style="font-size: 11pt;">UNIT PRICE &nbsp;&nbsp;&nbsp; TOTAL</span>
            </th>
            <th colspan="2" style="width: 20%;">
                LABOUR<br>
                <span style="font-size: 11pt;">UNIT PRICE &nbsp;&nbsp;&nbsp; TOTAL</span>
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
        <?php for ($i = count($quotationLines); $i < 10; $i++): ?>
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
        <table>
            <tr>
                <td width="70%">
                    <div class="terms-section">
                        <div><strong>EXCLUDES VAT AND SEPARATED PURCHASING IS NOT ALLOWED.</strong></div>
                        <div><strong>CURRENCY :</strong> Baht</div>
                        <div><strong>DELIVERY :</strong></div>
                        <div><strong>PAYMENT :</strong> <?= $quotation->payment_term_text ?></div>
                        <div><strong>VALIDITY :</strong> 7 day after today.</div>
                        <div><strong>REMARK</strong></div>
                    </div>
                </td>
                <td width="30%">
                    <table style="margin-bottom: 0;">
                        <tr>
                            <td style="text-align: right; border: none; padding: 5px;"><strong>Total</strong></td>
                            <td style="text-align: right; border: 1px solid #ccc; padding: 5px; width: 100px;">
                                <?= number_format($subtotal, 2) ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; border: none; padding: 5px;"><strong>Discount</strong></td>
                            <td style="text-align: right; border: 1px solid #ccc; padding: 5px;">
                                <?= number_format($discount, 2) ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; border: none; padding: 5px;"><strong>Vat 7%</strong></td>
                            <td style="text-align: right; border: 1px solid #ccc; padding: 5px;">
                                <?= number_format($vat, 2) ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; border: none; padding: 5px;"><strong>Grand Total</strong></td>
                            <td style="text-align: right; border: 1px solid #ccc; padding: 5px; background-color: #E6F2FF;">
                                <strong><?= number_format($grandTotal, 2) ?></strong>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <table>
            <tr>
                <td width="33%" class="signature-box">
                    <div class="signature-line"></div>
                    <div>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                    <div><strong>ACCEPT ABOVE QUOTATION</strong></div>
                    <div>Purchaser</div>
                </td>
                <td width="33%" class="signature-box">
                    <div class="signature-line"></div>
                    <div>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                    <div><strong>QUOTED BY</strong></div>
                </td>
                <td width="33%" class="signature-box">
                    <div class="signature-line"></div>
                    <div>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                    <div><strong>AUTHORIZED SIGNATURE</strong></div>
                </td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>