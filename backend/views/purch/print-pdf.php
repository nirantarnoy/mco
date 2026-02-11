<?php
use yii\helpers\Html;

// คำนวณราคารวม
$subtotal = 0;
$discount = $purchase->discount_amount ?? 0;
$vat = 0;
$total = 0;

if ($purchaseLines) {
    foreach ($purchaseLines as $line) {
        $subtotal += $line->line_total;
    }
}

// คำนวณ VAT 7%
$netAmount = $subtotal - $discount;
$vat = $netAmount * 0.07;
$total = $netAmount + $vat;
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

            .po-container {
                padding: 0;
            }

            .header {
                margin-bottom: 20px;
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

            .po-title {
                font-size: 24pt;
                font-weight: bold;
                text-align: right;
            }

            .thai-title {
                font-size: 20pt;
                text-align: right;
            }

            .company-info {
                margin-bottom: 20px;
                line-height: 1.6;
            }

            .company-name {
                font-weight: bold;
                font-size: 16pt;
            }

            .info-section {
                margin-bottom: 20px;
            }

            .info-section table {
                width: 100%;
                border: none;
            }

            .info-label {
                font-weight: bold;
                width: 120px;
            }

            .info-value {
                border-bottom: 1px solid #000;
                padding-left: 5px;
            }

            table.items {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }

            table.items th {
                background-color: #87CEEB;
                border: 1px solid #000;
                padding: 8px;
                text-align: center;
                font-weight: bold;
            }

            table.items td {
                border: 1px solid #000;
                padding: 8px;
                text-align: center;
                height: 25px;
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

            .summary-table {
                width: 40%;
                float: right;
                border-collapse: collapse;
            }

            .summary-table td {
                padding: 5px;
                border: 1px solid #000;
            }

            .summary-label {
                text-align: right;
                font-weight: bold;
                background-color: #87CEEB;
                width: 50%;
            }

            .acknowledgment {
                text-align: center;
                margin: 10px 0;
                font-weight: bold;
                float: left;
                width: 50%;
            }

            .signature-section {
                margin-top: 100px;
                clear: both;
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

            .terms {
                margin-top: 20px;
                font-size: 12pt;
                clear: both;
            }

            .clear {
                clear: both;
            }
        </style>
    </head>
<body>

<div class="po-container">
    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td width="50%">
                    <span class="logo">
                        <span class="m">M</span><span class="c">C</span><span class="o">O</span>
                    </span>
                </td>
                <td width="50%">
                    <div class="po-title">PURCHASE ORDER</div>
                    <div class="thai-title">ใบสั่งซื้อ</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Company Info -->
    <div class="company-info">
        <div class="company-name">M.C.O. COMPANY LIMITED</div>
        <div>บริษัท เอ็ม.ซี.โอ. จำกัด</div>
        <div>5/15 ถนนเกาะกลอย ตำบลจอมพลเจ้าพระยา อำเภอเมือง จังหวัดระยอง 21000</div>
        <div>โทรศัพท์/โทรสาร : 021564300025</div>
        <div>Tel : (038) 475259-6 , 0364364555</div>
        <div>e-mail : info@thai-mco.com</div>
        <div><strong>SUPPLIER :</strong></div>
    </div>

    <!-- PO Details -->
    <div class="info-section">
        <table>
            <tr>
                <td width="25%">
                    <span class="info-label">PO.NO. :</span>
                </td>
                <td width="25%" class="info-value">
                    <?= Html::encode($purchase->purch_no) ?>
                </td>
                <td width="25%">
                    <span class="info-label">DATE :</span>
                </td>
                <td width="25%" class="info-value">
                    <?= Yii::$app->formatter->asDate($purchase->purch_date, 'php:d/m/Y') ?>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="info-label">PR.NO. :</span>
                </td>
                <td class="info-value">&nbsp;</td>
                <td>
                    <span class="info-label">PAGE :</span>
                </td>
                <td class="info-value">1</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>
                    <span class="info-label">EXPRICE JOB NO. :</span>
                </td>
                <td class="info-value">
                    <?= Html::encode($purchase->job_id ?? '') ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td width="25%">
                    <span class="info-label">TEL :</span>
                </td>
                <td width="25%" class="info-value">&nbsp;</td>
                <td width="25%">
                    <span class="info-label">CURRENCY :</span>
                </td>
                <td width="25%" class="info-value">BAHT</td>
            </tr>
            <tr>
                <td>
                    <span class="info-label">FAX :</span>
                </td>
                <td class="info-value">&nbsp;</td>
                <td>
                    <span class="info-label">REF. NO. :</span>
                </td>
                <td class="info-value">&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <span class="info-label">CONTACT :</span>
                </td>
                <td class="info-value">&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </div>

    <!-- Items Table -->
    <table class="items">
    <thead>
    <tr>
        <th style="width: 5%;">ITEM</th>
        <th style="width: 15%;">CODE NO.</th>
        <th style="width: 35%;">DESCRIPTION</th>
        <th style="width: 10%;">P/N</th>
        <th style="width: 10%;">QTY</th>
        <th style="width: 10%;">UNIT</th>
        <th style="width: 10%;">UNIT PRICE</th>
        <th style="width: 15%;">AMOUNT</th>
    </tr>
    </thead>
    <tbody>
<?php if ($purchaseLines): ?>
    <?php $itemNo = 1; ?>
    <?php foreach ($purchaseLines as $line): ?>
        <tr>
            <td><?= $itemNo++ ?></td>
            <td><?= Html::encode($line->product->code ?? '') ?></td>
            <td class="description-cell"><?= Html::encode($line->product->name ?? $line->product_name) ?></td>
            <td>&nbsp;</td>
            <td><?= number_format($line->qty, 0) ?></td>
            <td><?= Html::encode($line->unit_id ?? '') ?></td>
            <td class="number-cell"><?= number_format($line->line_price, 2) ?></td>
            <td class="number-cell"><?= number_format($line->line_total, 2) ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Empty rows -->
<?php for ($i = count($purchaseLines); $i < 10; $i++): ?>
    <tr>
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
        <div class="acknowledgment">ACKNOWLEDGMENT BY :</div>
        <table class="summary-table">
            <tr>
                <td class="summary-label">TOTAL</td>
                <td class="number-cell"><?= number_format($subtotal, 2) ?></td>
            </tr>
            <tr>
                <td class="summary-label">DISCOUNT</td>
                <td class="number-cell"><?= number_format($discount, 2) ?></td>
            </tr>
            <tr>
                <td class="summary-label">NET AMOUNT</td>
                <td class="number-cell"><?= number_format($netAmount, 2) ?></td>
            </tr>
            <tr>
                <td class="summary-label">VAT 7%</td>
                <td class="number-cell"><?= number_format($vat, 2) ?></td>
            </tr>
            <tr>
                <td class="summary-label" style="font-size: 16pt;">TOTAL</td>
                <td class="number-cell" style="font-size: 16pt; font-weight: bold;">
                    <?= number_format($total, 2) ?>
                </td>
            </tr>
        </table>
        <div class="clear"></div>
    </div>

    <!-- Terms -->
    <div class="terms">
        <div><strong>Delivery :</strong></div>
        <div><strong>Payment :</strong></div>
        <div><strong>Note 1.</strong> กรณีสินค้าหรือบริการนั้นต้องนำเข้าจากต่างประเทศให้เป็นหน้าที่ของผู้ขายต้องรับผิดชอบในการดำเนินการ 0.5% จากราคาทั้งหมด ถ้าผู้ขายไม่ทำการจัดส่งตามเวลา 10 ของราคาทั้งหมด</div>
        <?php if (!empty($purchase->note)): ?>
            <div><strong>หมายเหตุ:</strong> <?= Html::encode($purchase->note) ?></div>
        <?php endif; ?>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <table>
            <tr>
                <td width="33%" class="signature-box">
                    <div class="signature-line"></div>
                    <div>PURCHASING</div>
                </td>
                <td width="33%" class="signature-box">
                    <div class="signature-line"></div>
                    <div>REQUEST BY</div>
                </td>
                <td width="33%" class="signature-box">
                    <div class="signature-line"></div>
                    <div>AUTHORIZED SIGNATURE</div>
                    <div>F-WP-FMA-002-002 R.1</div>
                </td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>