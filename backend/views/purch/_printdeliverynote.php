<?php
/**
 * @var $this yii\web\View
 */

use yii\helpers\Html;

// Mock data
$deliveryDate = date('m/d/Y');
$ourRef = 'DN-2024-0156';
$pageNo = '1 of 1';

// Recipient information
$toCompany = 'บริษัท ลูกค้าตัวอย่าง จำกัด';
$toAddress1 = '999 อาคารสำนักงาน ชั้น 15';
$toAddress2 = 'ถนนพระราม 4 แขวงสีลม เขตบางรัก';
$toAddress3 = 'กรุงเทพฯ 10500';
$toAttn = 'คุณสมศักดิ์ ผู้จัดการฝ่ายจัดซื้อ';

// Sender information
$from = 'ฝ่ายคลังสินค้า MCO';
$tel = '(038)-875258-9';
$refNo = 'PO-2024-0890';

// Delivery items
$items = [
    [
        'item' => 1,
        'description' => 'กระดาษ A4 80 แกรม Double A Premium',
        'part_no' => 'A4-80-DA',
        'qty' => 50,
        'unit' => 'รีม'
    ],
    [
        'item' => 2,
        'description' => 'หมึกพิมพ์ Canon PG-740 Black Original',
        'part_no' => 'CN-PG740-BK',
        'qty' => 10,
        'unit' => 'ขวด'
    ],
    [
        'item' => 3,
        'description' => 'แฟ้มเอกสาร 2 นิ้ว สีน้ำเงิน Elephant',
        'part_no' => 'FL-2IN-BL',
        'qty' => 30,
        'unit' => 'แฟ้ม'
    ],
    [
        'item' => 4,
        'description' => 'ปากกาลูกลื่น Pilot 0.5mm สีน้ำเงิน',
        'part_no' => 'PN-PIL-05-BL',
        'qty' => 100,
        'unit' => 'ด้าม'
    ],
    [
        'item' => 5,
        'description' => 'กรรไกร 8 นิ้ว สแตนเลส 3M',
        'part_no' => 'SC-8IN-3M',
        'qty' => 5,
        'unit' => 'อัน'
    ],
    [
        'item' => 6,
        'description' => 'เทปใส 3/4 นิ้ว x 36 หลา 3M Scotch',
        'part_no' => 'TP-3M-34',
        'qty' => 20,
        'unit' => 'ม้วน'
    ],
    [
        'item' => 7,
        'description' => 'ลวดเย็บกระดาษ No.10 MAX',
        'part_no' => 'ST-MAX-10',
        'qty' => 50,
        'unit' => 'กล่อง'
    ],
    [
        'item' => 8,
        'description' => 'Post-it 3x3 นิ้ว สีเหลือง 3M',
        'part_no' => 'PS-3M-YL',
        'qty' => 20,
        'unit' => 'แพ็ค'
    ]
];

// Signatures
$recipientName = '';
$recipientDate = '';
$senderName = 'นายสมชาย ใจดี';
$senderDate = $deliveryDate;

?>

<style>
    @media print {
        body {
            margin: 0;
            padding: 0;
        }
        .print-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0;
            padding: 10mm;
        }
        .no-print {
            display: none;
        }
    }

    .print-container {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        padding: 10mm;
        background: white;
        font-family: Arial, sans-serif;
        font-size: 12pt;
        line-height: 1.4;
        color: #000;
        position: relative;
    }

    .header-section {
        margin-bottom: 20px;
    }

    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .company-section {
        flex: 1;
    }

    .company-logo {
        max-width: 150px;
        margin-bottom: 10px;
    }

    .company-info {
        font-size: 11pt;
        line-height: 1.3;
    }

    .company-info .company-name {
        font-weight: bold;
        font-size: 14pt;
        margin-bottom: 5px;
    }

    .form-title {
        text-align: center;
        font-size: 20pt;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .date-section {
        text-align: right;
        font-size: 11pt;
    }

    .info-section {
        display: flex;
        margin-bottom: 20px;
    }

    .info-left {
        flex: 1;
        padding-right: 20px;
    }

    .info-right {
        flex: 1;
    }

    .info-row {
        display: flex;
        margin-bottom: 8px;
        align-items: baseline;
    }

    .info-label {
        font-weight: bold;
        width: 80px;
        flex-shrink: 0;
    }

    .info-value {
        flex: 1;
        border-bottom: 1px solid #000;
        padding: 0 5px;
        min-height: 20px;
    }

    .table-section {
        margin-bottom: 30px;
    }

    .delivery-table {
        width: 100%;
        border-collapse: collapse;
    }

    .delivery-table th,
    .delivery-table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: center;
    }

    .delivery-table th {
        background-color: #e8e8e8;
        font-weight: bold;
        font-size: 11pt;
    }

    .delivery-table td {
        font-size: 10pt;
    }

    .delivery-table .description {
        text-align: left;
        padding-left: 10px;
    }

    .delivery-table .item-col {
        width: 60px;
    }

    .delivery-table .description-col {
        width: 45%;
    }

    .delivery-table .pn-col {
        width: 20%;
    }

    .delivery-table .qty-col {
        width: 10%;
    }

    .delivery-table .unit-col {
        width: 10%;
    }

    .delivery-table tbody tr:nth-child(even) {
        background-color: #f5f5f5;
    }

    .delivery-table tbody tr {
        height: 30px;
    }

    .footer-section {
        margin-top: 40px;
    }

    .footer-note {
        font-size: 11pt;
        font-weight: bold;
        margin-bottom: 30px;
        text-align: center;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        margin-top: 40px;
    }

    .signature-box {
        width: 40%;
        text-align: center;
    }

    .signature-label {
        font-weight: bold;
        margin-bottom: 40px;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        margin: 0 20px 5px 20px;
        min-height: 30px;
    }

    .signature-name {
        margin-bottom: 5px;
    }

    .signature-date {
        margin-top: 10px;
    }

    .no-print {
        text-align: center;
        margin: 20px 0;
    }

    .print-button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        border-radius: 4px;
    }

    .print-button:hover {
        background-color: #0056b3;
    }
</style>

<div class="no-print">
    <button class="print-button" onclick="window.print()">พิมพ์ใบตรวจรับ</button>
</div>

<div class="print-container">
    <!-- Header Section -->
    <div class="header-section">
        <h1 class="form-title">ใบตรวจรับ / Delivery note</h1>

        <div class="header-top">
            <div class="company-section">
                <?= Html::img('../../backend/web/uploads/logo/mco_logo.png', ['class' => 'company-logo', 'alt' => 'MCO Logo']) ?>
                <div class="company-info">
                    <div class="company-name">M.C.O. COMPANY LIMITED</div>
                    <div>8/18 Koh-Kloy Rd. ,T. Cherngnoen,</div>
                    <div>A. Muang , Rayong 21000 Thailand.</div>
                    <div>ID.NO. 0215543000985</div>
                    <div>Tel : (038)-875258-9 , 094-6984555</div>
                </div>
            </div>
            <div class="date-section">
                <strong>Date :</strong> <?= Html::encode($deliveryDate) ?>
            </div>
        </div>
    </div>

    <!-- Information Section -->
    <div class="info-section">
        <div class="info-left">
            <div class="info-row">
                <span class="info-label">To :</span>
                <span class="info-value"><?= Html::encode($toCompany) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">&nbsp;</span>
                <span class="info-value"><?= Html::encode($toAddress1) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">&nbsp;</span>
                <span class="info-value"><?= Html::encode($toAddress2) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">&nbsp;</span>
                <span class="info-value"><?= Html::encode($toAddress3) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Attn :</span>
                <span class="info-value"><?= Html::encode($toAttn) ?></span>
            </div>
        </div>
        <div class="info-right">
            <div class="info-row">
                <span class="info-label">OUR REF :</span>
                <span class="info-value"><?= Html::encode($ourRef) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">FROM :</span>
                <span class="info-value"><?= Html::encode($from) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">TEL :</span>
                <span class="info-value"><?= Html::encode($tel) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">REF.NO. :</span>
                <span class="info-value"><?= Html::encode($refNo) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Page No. :</span>
                <span class="info-value"><?= Html::encode($pageNo) ?></span>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-section">
        <table class="delivery-table">
            <thead>
            <tr>
                <th class="item-col">ITEM</th>
                <th class="description-col">DESCRIPTION</th>
                <th class="pn-col">P/N</th>
                <th class="qty-col">Q'TY</th>
                <th class="unit-col">UNIT</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= Html::encode($item['item']) ?></td>
                    <td class="description"><?= Html::encode($item['description']) ?></td>
                    <td><?= Html::encode($item['part_no']) ?></td>
                    <td><?= Html::encode($item['qty']) ?></td>
                    <td><?= Html::encode($item['unit']) ?></td>
                </tr>
            <?php endforeach; ?>

            <!-- Empty rows to fill the table -->
            <?php for ($i = count($items); $i < 20; $i++): ?>
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
    </div>

    <!-- Footer Section -->
    <div class="footer-section">
        <div class="footer-note">
            Get the products listed above is in good condition and property completed.
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Recipient</div>
                <div class="signature-line"><?= Html::encode($recipientName) ?></div>
                <div class="signature-name">(________________________)</div>
                <div class="signature-date">Date _____________________</div>
            </div>

            <div class="signature-box">
                <div class="signature-label">Sender</div>
                <div class="signature-line"><?= Html::encode($senderName) ?></div>
                <div class="signature-name">(<?= Html::encode($senderName) ?>)</div>
                <div class="signature-date">Date <?= Html::encode($senderDate) ?></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Optional: Add functionality to print directly when page loads
    // window.onload = function() {
    //     window.print();
    // };
</script>