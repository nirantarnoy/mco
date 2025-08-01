<?php
use yii\helpers\Html;

$company = [
    'name' => 'บริษัท เอ็ม.ซี.โอ. จำกัด',
    'name_en' => 'M.C.O. CO.,LTD.',
    'address' => '8/18 ถนนเกาะกลอย ตำบลเชิงเนิน อำเภอเมือง จังหวัดระยอง 21000',
    'address_en' => '8/18 Koh-Kloy Rd., Tambon Cherngnoeh, Amphur Muang, Rayong 21000',
    'phone' => 'Tel : (038) 875258-9',
    'fax' => 'Fax : (038) 619559',
    'email' => 'e-mail: info@thai-mco.com',
    'website' => 'www.thai-mco.com',
    'tax_id' => '0215543000985'
];

function convertToThaiText($number) {
    // Function to convert number to Thai text
    // Implementation for Thai number to text conversion
    return 'สองแสนเจ็ดหมื่นบาทถ้วน'; // Placeholder
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Prompt', sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 20px;
        }

        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-logo {
            width: 100px;
            height: 80px;
            background: #ff6b35;
            color: white;
            text-align: center;
            line-height: 80px;
            font-weight: bold;
            font-size: 24px;
        }

        .company-details {
            flex: 1;
            margin-left: 20px;
        }

        .document-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }

        .document-info {
            margin-bottom: 20px;
        }

        .document-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .document-info td {
            padding: 5px;
            vertical-align: top;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .items-table th {
            background-color: #f5f5f5;
            text-align: center;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary-box {
            border: 1px solid #000;
            padding: 10px;
            margin-top: 20px;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .dotted-line {
            border-bottom: 1px dotted #000;
            height: 30px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
<!-- Header -->
<div class="header">
    <div class="company-info">
        <div class="company-logo">MCO</div>
        <div class="company-details">
            <h2><?= $company['name'] ?></h2>
            <h3><?= $company['name_en'] ?></h3>
            <p><?= $company['phone'] ?> <?= $company['fax'] ?></p>
            <p><?= $company['email'] ?> <?= $company['website'] ?></p>
            <p><?= $company['address'] ?></p>
            <p><?= $company['address_en'] ?></p>
        </div>
    </div>
</div>

<!-- Document Title -->
<div class="document-title">
    ใบเพิ่มหนี้ / ใบกำกับภาษี<br>
    DEBIT NOTE / TAX INVOICE
</div>

<!-- Document Information -->
<div class="document-info">
    <table>
        <tr>
            <td width="50%">
                <strong>เลขประจำตัวผู้เสียภาษี:</strong> <?= $company['tax_id'] ?><br>
                <strong>รหัสลูกค้า:</strong> <?= $model->customer->customer_code ?><br>
                <strong>ชื่อลูกค้า:</strong> <?= $model->customer->customer_name ?><br>
                <strong>ที่อยู่:</strong> <?= $model->customer->address ?>
            </td>
            <td width="50%" class="text-right">
                <strong>เลขที่:</strong> <?= $model->debit_note_no ?><br>
                <strong>วันที่:</strong> <?= date('d/m/Y', strtotime($model->issue_date)) ?>
            </td>
        </tr>
    </table>
</div>

<!-- Items Table -->
<table class="items-table">
    <thead>
    <tr>
        <th width="8%">ลำดับ</th>
        <th width="52%">รายการ</th>
        <th width="15%">จำนวน</th>
        <th width="12%">ราคา</th>
        <th width="13%">ราคารวม</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($model->items as $item): ?>
        <tr>
            <td class="text-center"><?= str_pad($item->item_no, 4, '0', STR_PAD_LEFT) ?></td>
            <td><?= Html::encode($item->description) ?></td>
            <td class="text-center"><?= $item->quantity ?> Lot</td>
            <td class="text-right"><?= number_format($item->unit_price, 2) ?></td>
            <td class="text-right"><?= number_format($item->total_price, 2) ?></td>
        </tr>
    <?php endforeach; ?>

    <!-- Empty rows for spacing -->
    <?php for ($i = count($model->items); $i < 5; $i++): ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>

