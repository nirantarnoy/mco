<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Quotation */

$this->title = 'พิมพ์ใบเสนอราคา: ' . $model->quotation_no;
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

        .quotation-form {
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

        .quotation-title {
            font-size: 18px;
            font-weight: bold;
            color: #003366;
            text-align: center;
            margin-bottom: 10px;
        }

        .quotation-info {
            background-color: #e8f4fd;
            padding: 5px;
            margin-bottom: 5px;
        }

        .customer-info {
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

        .terms {
            clear: both;
            margin-top: 20px;
            padding: 10px;
            font-size: 10px;
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

<div class="quotation-form">
    <!-- Header Section -->
    <div class="header clearfix">
        <div class="header-right">
            <div class="quotation-title">Quotation</div>
            <div style="background-color: #e8f4fd; padding: 5px; margin-bottom: 5px;">
                <strong>ใบเสนอราคา</strong>
            </div>
            <div class="quotation-info">
                <strong>Date:</strong> <?= date('d/m/Y', strtotime($model->quotation_date)) ?>
            </div>
            <div class="quotation-info">
                <strong>OUR REF:</strong> <?= Html::encode($model->quotation_no) ?>
            </div>
            <div class="quotation-info">
                <strong>FROM:</strong> _______________
            </div>
            <div class="quotation-info">
                <strong>FAX:</strong> 66-38-013559
            </div>
            <div class="quotation-info">
                <strong>TEL:</strong> 038-875296 875299
            </div>
            <div class="quotation-info">
                <strong>YOUR REF:</strong> _______________
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-info">
        <strong>Customer: <?= Html::encode($model->customer_name ?: '_______________') ?></strong><br><br>
        <strong>Tel:</strong> _______________<br>
        <strong>Fax:</strong> _______________<br>
        <strong>To:</strong> _______________<br>
        <strong>Purchaser:</strong> _______________<br><br>
        <strong>Project Name:</strong> _______________
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
        <tr>
            <th style="width: 40px;">ITEM</th>
            <th style="width: 200px;">DESCRIPTION</th>
            <th style="width: 50px;">Q'TY</th>
            <th style="width: 40px;">UNIT</th>
            <th colspan="2" style="width: 160px;">MATERIAL</th>
            <th colspan="2" style="width: 160px;">LABOUR</th>
            <th style="width: 80px;">TOTAL</th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th style="width: 80px;">UNIT PRICE</th>
            <th style="width: 80px;">TOTAL</th>
            <th style="width: 80px;">UNIT PRICE</th>
            <th style="width: 80px;">TOTAL</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php $itemCount = 1; ?>
        <?php foreach ($model->quotationLines as $line): ?>
            <tr>
                <td><?= $itemCount++ ?></td>
                <td class="description"><?= Html::encode($line->product_name) ?></td>
                <td class="number"><?= number_format($line->qty, 0) ?></td>
                <td>-</td>
                <td class="number"><?= number_format($line->line_price, 2) ?></td>
                <td class="number"><?= number_format($line->line_total, 2) ?></td>
                <td class="number">-</td>
                <td class="number">-</td>
                <td class="number"><?= number_format($line->line_total, 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <!-- Empty rows to fill the table -->
        <?php for ($i = count($model->quotationLines); $i < 10; $i++): ?>
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

    <!-- Totals Section -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="label">Total</td>
                <td class="amount"><?= number_format($model->total_amount, 2) ?></td>
            </tr>
            <tr>
                <td class="label">Discount</td>
                <td class="amount">-</td>
            </tr>
            <tr>
                <td class="label">Vat 7%</td>
                <td class="amount"><?= number_format($model->total_amount * 0.07, 2) ?></td>
            </tr>
            <tr>
                <td class="label"><strong>Grand Total</strong></td>
                <td class="amount"><strong><?= number_format($model->total_amount * 1.07, 2) ?></strong></td>
            </tr>
        </table>
    </div>

    <!-- Terms and Conditions -->
    <div class="terms">
        <strong>EXCLUDED VAT AND SEPARATED PURCHASING IS NOT ALLOWED.</strong><br>
        <strong>PAYMENT:</strong> Bank<br>
        <strong>DELIVERY:</strong><br>
        <strong>PAYMENT:</strong> Cash<br>
        <strong>VALIDITY:</strong> 7 day after today.<br>
        <strong>REMARK:</strong><br>
        <?php if ($model->note): ?>
            <strong>หมายเหตุ:</strong> <?= Html::encode($model->note) ?>
        <?php endif; ?>
    </div>

    <!-- Signatures -->
    <div class="signatures clearfix">
        <div class="signature-section">
            <div class="signature-line"></div>
            <strong>(</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>)</strong><br>
            <strong>ACCEPT ABOVE QUOTATION</strong><br>
            <small>Purchaser</small>
        </div>
        <div class="signature-section">
            <div class="signature-line"></div>
            <strong>(</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>)</strong><br>
            <strong>QUOTED BY</strong>
        </div>
        <div class="signature-section">
            <div class="signature-line"></div>
            <strong>(</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>)</strong><br>
            <strong>AUTHORIZED SIGNATURE</strong>
        </div>
    </div>

    <!-- Certificate Info -->
    <div style="clear: both; text-align: center; font-size: 8px; margin-top: 20px; padding: 5px;">
        Certified ISO 9001:2015<br>
        Certificate No. TH206058<br>
        Issued by Bureau Veritas Certification (Thailand) Ltd.
    </div>
</div>

<script>
    // Auto print when page loads (optional)
    // window.onload = function() { window.print(); }
</script>
</body>
</html>
<div class="company-logo">
    <strong style="color: #c41e3a; font-size: 16px;">MCO</strong>
    <span style="color: #ffa500; font-size: 12px;">▲</span>
</div>
<div class="company-info">
    <strong>Company Name:</strong><br>
    <strong>M.C.O. COMPANY LIMITED</strong><br>
    6/16 Kob-Kao Road<br>
    Tambon Changphouk,<br>
    Amphur Muang,<br>
    Rayong 21000 Thailand.<br>
    <span style="color: #0066cc;">info@thatmco.com</span><br>
    <strong>Customer:</strong>
</div>
</div>
<div class