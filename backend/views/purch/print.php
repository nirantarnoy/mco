<?php
use yii\helpers\Html;

$this->title = 'Purchase Order - ' . $purchase->purch_no;

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

    <style>
        .po-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: white;
            /*font-family: Arial, sans-serif;*/
            font-size: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo-section {
            display: flex;
            align-items: center;
        }

        .logo {
            font-size: 30px;
            font-weight: bold;
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

        .po-title {
            font-size: 24px;
            font-weight: bold;
        }

        .thai-title {
            font-size: 20px;
            margin-top: 5px;
        }

        .company-info {
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .company-name {
            font-weight: bold;
            font-size: 20px;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            min-width: 120px;
        }

        .info-value {
            border-bottom: 1px solid #ccc;
            flex: 1;
            padding-left: 5px;
        }

        .two-column {
            display: flex;
            gap: 50px;
        }

        .column {
            flex: 1;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table.items th {
            background: #87CEEB;
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
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .summary-left {
            width: 60%;
        }

        .summary-right {
            width: 35%;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 5px;
            border: 1px solid #000;
        }

        .summary-label {
            text-align: right;
            font-weight: bold;
            background: #87CEEB;
        }

        .acknowledgment {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
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

        .terms {
            margin-top: 20px;
            font-size: 20px;
        }

        @media print {
            .po-container {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>

    <div class="po-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">
                    <img src="../../backend/web/uploads/logo/mco_logo.png" width="20%" alt="">
                </div>
            </div>
            <div style="text-align: right;">
                <div class="po-title">PURCHASE ORDER</div>
                <div class="thai-title">ใบสั่งซื้อ</div>
            </div>
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
            <div class="two-column">
                <div class="column">
                    <div class="info-row">
                        <span class="info-label">PO.NO. :</span>
                        <span class="info-value"><?= Html::encode($purchase->purch_no) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">PR.NO. :</span>
                        <span class="info-value"></span>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <span class="info-label">DATE :</span>
                        <span class="info-value"><?= Yii::$app->formatter->asDate($purchase->purch_date, 'php:d/m/Y') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">PAGE :</span>
                        <span class="info-value">1</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">EXPRICE JOB NO. :</span>
                        <span class="info-value"><?= Html::encode($purchase->job_id ?? '') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="two-column">
                <div class="column">
                    <div class="info-row">
                        <span class="info-label">TEL :</span>
                        <span class="info-value"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">FAX :</span>
                        <span class="info-value"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">CONTACT :</span>
                        <span class="info-value"></span>
                    </div>
                </div>
                <div class="column">
                    <div class="info-row">
                        <span class="info-label">CURRENCY :</span>
                        <span class="info-value">BAHT</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">REF. NO. :</span>
                        <span class="info-value"></span>
                    </div>
                </div>
            </div>
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
                        <td></td>
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
            <div class="summary-left">
                <div class="acknowledgment">ACKNOWLEDGMENT BY :</div>
            </div>
            <div class="summary-right">
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
                        <td class="summary-label" style="font-size: 16px;">TOTAL</td>
                        <td class="number-cell" style="font-size: 16px; font-weight: bold;">
                            <?= number_format($total, 2) ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Terms -->
        <div class="terms">
            <div><strong>Delivery :</strong></div>
            <div><strong>Payment :</strong></div>
            <div><strong>Note 1.</strong> กรณีสินค้าหรือบริการนั้นต้องนำเข้าจากต่างประเทศให้เป็นหน้าที่ของผู้ขายต้องรับผิดชอบในการดำเนินการ 0.5% จากราคาทั้งหมด ถ้าผู้ขายไม่ทำการจัดส่งตามเวลา 10 ของราคาทั้งหมด</div>
        </div>

        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>PURCHASING</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>REQUEST BY</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div>AUTHORIZED SIGNATURE</div>
                <div>F-WP-FMA-002-002 R.1</div>
            </div>
        </div>
    </div>

    <!-- Print Buttons -->
<?php if ($showButtons): ?>
    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()" class="btn btn-primary" style="font-size: 20px;font-weight: bold;">
            <i class="glyphicon glyphicon-print"></i> Print
        </button>
        <a href="<?= \yii\helpers\Url::to(['print', 'id' => $purchase->id, 'format' => 'pdf']) ?>"
           class="btn btn-danger" target="_blank" style="font-size: 20px;font-weight: bold;">
            <i class="glyphicon glyphicon-file"></i> Download PDF
        </a>
        <button onclick="window.close()" class="btn btn-default" style="font-size: 20px;font-weight: bold;">Close</button>
    </div>
<?php endif; ?>