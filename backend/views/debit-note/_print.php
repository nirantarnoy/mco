<?php

use yii\helpers\Html;
//use backend\models\Company;

/* @var $this yii\web\View */
/* @var $model backend\models\DebitNote */

$company = '';// Company::findOne(1); // Get company info
$formatter = Yii::$app->formatter;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ใบเพิ่มหนี้ <?= $model->document_no ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: 'Prompt', sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #000;
        }

        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .company-info {
            flex: 1;
        }

        .company-logo {
            width: 120px;
            height: auto;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 12px;
            line-height: 1.6;
        }

        .document-info {
            text-align: right;
            margin-top: 20px;
        }

        .document-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .document-title-en {
            font-size: 16px;
            font-weight: 400;
            margin-bottom: 15px;
        }

        .customer-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .section-row {
            display: flex;
            margin-bottom: 8px;
        }

        .label {
            font-weight: 500;
            width: 120px;
            flex-shrink: 0;
        }

        .value {
            flex: 1;
        }

        .items-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .items-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: 600;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 8px;
        }

        .items-table .text-center {
            text-align: center;
        }

        .items-table .text-right {
            text-align: right;
        }

        .summary-section {
            margin-top: 20px;
        }

        .summary-box {
            border: 1px solid #000;
            padding: 15px;
            margin-bottom: 10px;
        }

        .summary-table {
            width: 100%;
        }

        .summary-table td {
            padding: 5px 10px;
        }

        .summary-table .label {
            text-align: right;
            padding-right: 20px;
        }

        .summary-table .value {
            text-align: right;
            font-weight: 500;
        }

        .total-row {
            font-size: 16px;
            font-weight: 600;
            border-top: 2px solid #000;
            padding-top: 10px;
        }

        .amount-text {
            text-align: center;
            font-size: 16px;
            font-weight: 500;
            margin: 20px 0;
            padding: 10px;
            background-color: #f9f9f9;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px dotted #000;
            margin: 50px 20px 10px;
        }

        .original-invoice-info {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
        }

        .reason-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 15px 0;
            min-height: 60px;
        }

        @media print {
            body {
                margin: 0;
            }

            .container {
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <?= Html::img('../../backend/web/uploads/logo/mco_logo.png',['style' => 'max-width: 150px;']) ?>
            <div class="company-name">
                บริษัท เอ็ม.ซี.โอ. จำกัด (สำนักงานใหญ่)
            </div>
            <div class="company-name" style="font-size: 14px;">
                M.C.O. COMPANY LIMITED
            </div>
            <div class="company-details">
                TAXID: <?= $company ? $company->tax_id : '0215543000985' ?><br>
                8/18 ถนนเกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000 โทร 66-(0)-38875258-59
            </div>
        </div>

        <div class="document-info">
            <div class="document-title">ใบเพิ่มหนี้/ใบกำกับภาษี</div>
            <div class="document-title-en">DEBIT NOTE / TAX INVOICE</div>
        </div>
    </div>

    <!-- Customer Section -->
    <div class="customer-section">
        <div class="section-row">
            <div class="label">ทะเบียนเลขที่</div>
            <div class="value"><?= $company ? $company->tax_id : '0215543000985' ?></div>
            <div class="label" style="margin-left: 50px;">เลขที่</div>
            <div class="value"><?= Html::encode($model->document_no) ?></div>
        </div>
        <div class="section-row">
            <div class="label">รหัสลูกค้า</div>
            <div class="value"><?= $model->customer_id!=null ? Html::encode(\backend\models\Customer::findCode($model->customer_id)) : Html::encode(\backend\models\Vendor::findCode($model->vendor_id)) ?></div>
            <div class="label" style="margin-left: 50px;">วันที่</div>
            <div class="value"><?= $formatter->asDate($model->document_date, 'php:m/d/Y') ?></div>
        </div>
        <div class="section-row">
            <div class="label">ชื่อลูกค้า</div>
            <div class="value"><?= $model->customer_id != null ? Html::encode(\backend\models\Customer::findName($model->customer_id)): Html::encode(\backend\models\Vendor::findName($model->vendor_id)) ?></div>
        </div>
        <div class="section-row">
            <div class="label">ที่อยู่</div>
            <div class="value"><?= $model->customer_id !=null ? Html::encode(Html::encode(\backend\models\Customer::findFullAddress($model->customer_id))) : Html::encode(Html::encode(\backend\models\Vendor::findFullAddress($model->vendor_id))) ?></div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
        <tr>
            <th style="width: 60px;">ลำดับ</th>
            <th>รายการ</th>
            <th style="width: 100px;">จำนวน</th>
            <th style="width: 120px;">ราคา</th>
            <th style="width: 120px;">ราคารวม</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($model->debitNoteItems as $index => $item): ?>
            <tr>
                <td class="text-center"><?= str_pad($index + 1, 4, '0', STR_PAD_LEFT) ?></td>
                <td><?= nl2br(Html::encode($item->description)) ?></td>
                <td class="text-center">
                    <?= $formatter->asDecimal($item->quantity, 0) ?>
                    <?= Html::encode($item->unit) ?>
                </td>
                <td class="text-right"><?= $formatter->asDecimal($item->unit_price, 2) ?></td>
                <td class="text-right"><?= $formatter->asDecimal($item->amount, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-box">
            <table class="summary-table" style="width: 100%;">
                <tr>
                    <td class="label" style="width:50%;text-align: left;">รวมมูลค่าสินค้าทั้งสิ้น</td>
                    <td class="value"><?= $formatter->asDecimal($model->adjust_amount, 2) ?></td>
                </tr>
            </table>
        </div>

        <!-- Original Invoice Info -->
        <div class="original-invoice-info">
            <div class="section-row">
                <div class="label">ใบกำกับภาษีเดิมเลขที่:</div>
                <div class="value"><?= Html::encode($model->original_invoice_no) ?></div>
                <div class="label" style="margin-left: 30px;">ลงวันที่:</div>
                <div class="value"><?= $model->original_invoice_date ? $formatter->asDate($model->original_invoice_date, 'php:d/m/Y') : '' ?></div>
            </div>
            <div class="section-row">
                <div class="label">มูลค่าสินค้าตามใบกำกับฯเดิม</div>
                <div class="value"><?= $formatter->asDecimal($model->original_amount, 2) ?></div>
            </div>
        </div>

        <!-- Reason -->
        <div class="reason-box">
            <div style="font-weight: 500; margin-bottom: 5px;">เหตุผลที่ต้องเพิ่มหนี้:</div>
            <?= nl2br(Html::encode($model->reason)) ?>
        </div>

        <!-- Final Summary -->
        <div class="summary-box">
            <table class="summary-table" style="width: 100%">
                <tr>
                    <td class="label" style="width:50%;text-align: left;">รวมมูลค่าเพิ่มหนี้</td>
                    <td class="value"><?= $formatter->asDecimal($model->adjust_amount, 2) ?></td>
                </tr>
                <tr>
                    <td class="label" style="width:50%;text-align: left;">ภาษีมูลค่าเพิ่ม <?= $formatter->asDecimal($model->vat_percent, 0) ?>%</td>
                    <td class="value"><?= $formatter->asDecimal($model->vat_amount, 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td class="label" style="width:50%;text-align: left;">รวมเป็นเงินทั้งสิ้น</td>
                    <td class="value"><?= $formatter->asDecimal($model->total_amount, 2) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Amount in Text -->
    <div class="amount-text">
        (ตัวอักษร) <?= Html::encode($model->amount_text) ?>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div>บริษัท เอ็ม.ซี.โอ. จำกัด</div>
            <div class="signature-line"></div>
            <div>ผู้มีอำนาจลงนาม / ผู้รับมอบอำนาจ</div>
            <div style="margin-top: 10px;">_____/_____/_____</div>
        </div>

        <div class="signature-box">
            <div>&nbsp;</div>
            <div class="signature-line"></div>
            <div>ลายเซ็นผู้รับเอกสาร</div>
            <div style="margin-top: 10px;">_____/_____/_____</div>
        </div>
    </div>
</div>
</body>
</html>