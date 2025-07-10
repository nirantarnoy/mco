<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\PurchReq */

$this->title = 'พิมพ์ใบขอซื้อ: ' . $model->purch_req_no;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }

        body {
            font-family: 'Garuda', 'TH SarabunPSK', sans-serif;
            font-size: 12px;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }

        .purchase-order {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
        }

        .header {
            background-color: #e8f4fd;
            padding: 10px;
            border-bottom: 1px solid #000;
        }

        .header-left {
            float: left;
            width: 50%;
        }

        .header-right {
            float: right;
            width: 45%;
            text-align: right;
        }

        .company-logo {
            background-color: #fff;
            padding: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            display: inline-block;
        }

        .company-info {
            background-color: #b8e6b8;
            padding: 8px;
            font-size: 10px;
            margin-top: 5px;
        }

        .po-title {
            font-size: 18px;
            font-weight: bold;
            color: #003366;
            text-align: center;
            margin-bottom: 10px;
        }

        .po-info {
            background-color: #e8f4fd;
            padding: 5px;
            margin-bottom: 5px;
        }

        .supplier-info {
            border: 1px solid #000;
            padding: 10px;
            margin: 10px 0;
            min-height: 80px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        .items-table th {
            background-color: #e8f4fd;
            font-weight: bold;
            font-size: 10px;
        }

        .items-table td {
            font-size: 10px;
            min-height: 20px;
        }

        .items-table .description {
            text-align: left;
            max-width: 200px;
        }

        .items-table .number {
            text-align: right;
        }

        .totals-section {
            float: right;
            width: 300px;
            margin-top: 10px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            border: 1px solid #000;
            padding: 5px;
            background-color: #e8f4fd;
        }

        .totals-table .label {
            text-align: right;
            font-weight: bold;
            width: 50%;
        }

        .totals-table .amount {
            text-align: right;
            width: 50%;
        }

        .acknowledgment {
            clear: both;
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
            text-align: center;
        }

        .signatures {
            margin-top: 20px;
            padding: 10px;
        }

        .signature-section {
            float: left;
            width: 30%;
            text-align: center;
            margin: 0 1.5%;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 40px;
            margin-bottom: 5px;
        }

        .notes {
            clear: both;
            margin-top: 20px;
            font-size: 10px;
            padding: 10px;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div class="no-print" style="margin: 20px; text-align: center;">
    <?= Html::a('พิมพ์', 'javascript:window.print()', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('ดาวน์โหลด PDF', ['pdf', 'id' => $model->id], ['class' => 'btn btn-success', 'target' => '_blank']) ?>
    <?= Html::a('กลับ', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
</div>

<div class="purchase-order">
    <!-- Header Section -->
    <div class="header clearfix">
        <div class="header-left">
            <div class="company-logo">
                <strong style="color: #c41e3a; font-size: 16px;">MCO</strong>
                <span style="color: #ffa500; font-size: 12px;">▲</span>
            </div>
            <div class="company-info">
                <strong>M.C.O. COMPANY LIMITED</strong><br>
                เลขที่ ??? ถ.???, เขต???, กรุงเทพฯ 10000<br>
                เลขประจำตัวผู้เสียภาษี: 0215430000955<br>
                Tel: (02) 4752525-9, 094-5944555<br>
                Email: info@mcomes.com<br>
                <strong>SUPPLIER:</strong>
            </div>
        </div>
        <div class="header-right">
            <div class="po-title">PURCHASE ORDER</div>
            <div style="background-color: #e8f4fd; padding: 5px; margin-bottom: 5px;">
                <strong>ใบสั่งซื้อ</strong>
            </div>
            <div class="po-info">
                <strong>P.O. NO.:</strong> <?= Html::encode($model->purch_req_no) ?>
            </div>
            <div class="po-info">
                <strong>PR. NO.:</strong> <?= Html::encode($model->purch_req_no) ?>
            </div>
            <div class="po-info">
                <strong>DATE:</strong> <?= date('d/m/Y', strtotime($model->purch_req_date)) ?>
            </div>
            <div class="po-info">
                <strong>PAGE:</strong> 1
            </div>
            <div class="po-info">
                <strong>EXPENSE JOB NO.:</strong> _______________
            </div>
            <div class="po-info">
                <strong>CURRENCY:</strong> THB
            </div>
            <div class="po-info">
                <strong>REF. NO.:</strong> <?= Html::encode($model->purch_id ?: '_______________') ?>
            </div>
        </div>
    </div>

    <!-- Supplier Information -->
    <div class="supplier-info">
        <strong>SUPPLIER: <?= Html::encode($model->vendor_name ?: '_______________') ?></strong><br><br>
        <strong>TEL:</strong> _______________<br>
        <strong>FAX:</strong> _______________<br>
        <strong>CONTACT:</strong> _______________
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
        <tr>
            <th style="width: 40px;">ITEM</th>
            <th style="width: 80px;">CODE NO.</th>
            <th style="width: 200px;">DESCRIPTION</th>
            <th style="width: 40px;">P/N.</th>
            <th style="width: 50px;">Q'TY</th>
            <th style="width: 40px;">UNIT</th>
            <th style="width: 80px;">UNIT PRICE</th>
            <th style="width: 80px;">AMOUNT</th>
        </tr>
        </thead>
        <tbody>
        <?php $itemCount = 1; ?>
        <?php foreach ($model->purchReqLines as $line): ?>
            <tr>
                <td><?= $itemCount++ ?></td>
                <td><?= Html::encode($line->product_id ?: '') ?></td>
                <td class="description">
                    <?= Html::encode($line->product_name) ?><br>
                    <small><?= Html::encode($line->product_description) ?></small>
                </td>
                <td><?= Html::encode($line->product_id ?: '') ?></td>
                <td class="number"><?= number_format($line->qty, 2) ?></td>
                <td><?= Html::encode($line->unit) ?></td>
                <td class="number"><?= number_format($line->line_price, 2) ?></td>
                <td class="number"><?= number_format($line->line_total, 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <!-- Empty rows to fill the table -->
        <?php for ($i = count($model->purchReqLines); $i < 12; $i++): ?>
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

    <!-- Totals Section -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="label">TOTAL</td>
                <td class="amount"><?= number_format($model->total_amount, 2) ?></td>
            </tr>
            <tr>
                <td class="label">DISCOUNT</td>
                <td class="amount"><?= number_format($model->discount_amount, 2) ?></td>
            </tr>
            <tr>
                <td class="label">NET AMOUNT</td>
                <td class="amount"><?= number_format($model->total_amount - $model->discount_amount, 2) ?></td>
            </tr>
            <tr>
                <td class="label">VAT 7%</td>
                <td class="amount"><?= number_format($model->vat_amount, 2) ?></td>
            </tr>
            <tr>
                <td class="label"><strong>TOTAL</strong></td>
                <td class="amount"><strong><?= number_format($model->net_amount, 2) ?></strong></td>
            </tr>
            </tbody>
        </table>
    </div>

    <!-- Acknowledgment -->
    <div class="acknowledgment">
        <strong>ACKNOWLEDGMENT BY:</strong> _______________
    </div>

    <!-- Delivery and Payment -->
    <div style="clear: both; margin: 10px; font-size: 11px;">
        <strong>Delivery:</strong> _______________<br>
        <strong>Payment:</strong> _______________
    </div>

    <!-- Notes -->
    <div class="notes">
        <strong>Note 1:</strong> กรณีสินค้าผิดจากที่สั่ง ให้แจ้งคืนภายใน 0.5% ชั่วโมงนับจากวันที่ได้รับสินค้า 15 วันหลังจากนั้น<br>
        <?php if ($model->note): ?>
            <strong>หมายเหตุ:</strong> <?= Html::encode($model->note) ?>
        <?php endif; ?>
    </div>

    <!-- Signatures -->
    <div class="signatures clearfix">
        <div class="signature-section">
            <div class="signature-line"></div>
            <strong>PURCHASING</strong>
        </div>
        <div class="signature-section">
            <div class="signature-line"></div>
            <strong>REQUEST BY</strong>
        </div>
        <div class="signature-section">
            <div class="signature-line"></div>
            <strong>AUTHORIZED SIGNATURE</strong><br>
            <small>F-WP-FMA-002-002 R.1</small>
        </div>
    </div>
</div>

<script>
    // Auto print when page loads (optional)
    // window.onload = function() { window.print(); }
</script>
</body>
</html>