<?php
/**
 * @var $this yii\web\View
 */

use yii\helpers\Html;

// Mock data
$documentNo = 'RY-QT-2024-0156';
$issueDate = date('d/m/Y');
$issueTime = date('H:i');
$documentNumber = 'WD-2024-0156';

// Issue type
$issueType = 'withdraw'; // 'borrow', 'transfer', 'withdraw'

// Items
$items = [
    [
        'item' => 1,
        'description' => 'กระดาษ A4 80 แกรม Double A',
        'request_qty' => 10,
        'issue_qty' => 10,
        'return_qty' => 0,
        'price_unit' => 120.00,
        'remark' => 'ใช้งานฝ่ายบัญชี'
    ],
    [
        'item' => 2,
        'description' => 'หมึกพิมพ์ Canon PG-740 Black',
        'request_qty' => 2,
        'issue_qty' => 2,
        'return_qty' => 0,
        'price_unit' => 450.00,
        'remark' => 'เปลี่ยนหมึกเครื่องพิมพ์'
    ],
    [
        'item' => 3,
        'description' => 'แฟ้มเอกสาร 2 นิ้ว สีน้ำเงิน',
        'request_qty' => 5,
        'issue_qty' => 5,
        'return_qty' => 0,
        'price_unit' => 65.00,
        'remark' => 'จัดเก็บเอกสารประจำปี'
    ],
    [
        'item' => 4,
        'description' => 'ปากกาลูกลื่น Pilot 0.5mm สีน้ำเงิน',
        'request_qty' => 20,
        'issue_qty' => 20,
        'return_qty' => 0,
        'price_unit' => 15.00,
        'remark' => ''
    ],
    [
        'item' => 5,
        'description' => 'ลวดเย็บกระดาษ No.10',
        'request_qty' => 10,
        'issue_qty' => 10,
        'return_qty' => 0,
        'price_unit' => 12.00,
        'remark' => ''
    ]
];

// Signatures
$issuerName = 'นายสมชาย ใจดี';
$receiverName = 'นางสาวสมศรี รับของ';
$approverName = 'นายอนุมัติ ผู้จัดการ';
$stockerName = 'นายคลัง สินค้าดี';

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
            background: #fcf8e3 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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
        background: #fcf8e3;
        font-family: 'Sarabun', Arial, sans-serif;
        font-size: 14px;
        line-height: 1.4;
        color: #000;
        position: relative;
    }

    .header-section {
        border: 2px solid #000;
        padding: 10px;
        margin-bottom: 0;
    }

    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .company-section {
        display: flex;
        align-items: center;
    }

    .company-logo {
        max-width: 80px;
        margin-right: 20px;
    }

    .company-name {
        font-size: 18px;
        font-weight: bold;
    }

    .form-title {
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        margin: 5px 0;
    }

    .doc-info {
        text-align: right;
        font-size: 13px;
    }

    .doc-info-item {
        margin-bottom: 3px;
    }

    .issue-type {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin: 10px 0;
    }

    .issue-type label {
        display: flex;
        align-items: center;
        font-size: 14px;
    }

    .issue-type input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 5px;
    }

    .doc-number-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }

    .doc-label {
        font-weight: bold;
        font-size: 14px;
    }

    .table-section {
        margin: 0;
        border: 2px solid #000;
        border-top: none;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #000;
        padding: 5px;
        text-align: center;
        font-size: 13px;
    }

    .items-table th {
        background-color: #fcf8e3;
        font-weight: bold;
        height: 35px;
    }

    .items-table td {
        height: 30px;
    }

    .items-table .description {
        text-align: left;
        padding-left: 10px;
    }

    .items-table .number {
        text-align: right;
        padding-right: 10px;
    }

    .items-table .item-col {
        width: 50px;
    }

    .items-table .desc-col {
        width: 40%;
    }

    .items-table .qty-col {
        width: 80px;
    }

    .items-table .price-col {
        width: 80px;
    }

    .items-table .remark-col {
        width: auto;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        border: 2px solid #000;
        border-top: none;
        height: 120px;
    }

    .signature-box {
        flex: 1;
        border-right: 1px solid #000;
        padding: 10px;
        text-align: center;
        position: relative;
    }

    .signature-box:last-child {
        border-right: none;
    }

    .signature-label {
        font-weight: bold;
        font-size: 14px;
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
    }

    .signature-name {
        margin-top: 40px;
        font-size: 13px;
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
    <button class="print-button" onclick="window.print()">พิมพ์ใบเบิก</button>
</div>

<div class="print-container">
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-top">
            <div class="company-section">
                <?= Html::img('@web/images/mco-logo.png', ['class' => 'company-logo', 'alt' => 'MCO Logo']) ?>
                <div>
                    <div class="company-name">M.C.O. COMPANY LIMITED</div>
                    <div class="form-title">ใบเบิก - คืน</div>
                </div>
            </div>
            <div class="doc-info">
                <div class="doc-info-item">เลขที่.......................</div>
                <div style="display: flex; gap: 20px; justify-content: flex-end;">
                    <div>วันที่เบิก : <?= Html::encode($issueDate) ?></div>
                    <div>หน่วยงาน : ฝ่ายบัญชี</div>
                </div>
            </div>
        </div>

        <div class="issue-type">
            <label>
                <input type="checkbox" <?= $issueType == 'borrow' ? 'checked' : '' ?>>
                <span>วัสดุสิ้นเปลือง</span>
            </label>
            <label>
                <input type="checkbox" <?= $issueType == 'transfer' ? 'checked' : '' ?>>
                <span>อุปกรณ์ไฟฟ้า</span>
            </label>
            <label>
                <input type="checkbox" <?= $issueType == 'withdraw' ? 'checked' : '' ?>>
                <span>เครื่องมือ</span>
            </label>
            <label>
                <input type="checkbox">
                <span>วัสดุประกอบงาน</span>
            </label>
            <label>
                <input type="checkbox">
                <span>สินค้าซ่อมวายไป</span>
            </label>
            <label>
                <input type="checkbox">
                <span>N-Vent</span>
            </label>
        </div>

        <div class="doc-number-section">
            <div>
                <span class="doc-label">ช่องงาน:</span> _____________________
            </div>
            <div>
                <span class="doc-label">RY-QT:</span> <?= Html::encode($documentNo) ?>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-section">
        <table class="items-table">
            <thead>
            <tr>
                <th class="item-col">ลำดับ</th>
                <th class="desc-col">รายละเอียด/รายการ</th>
                <th class="qty-col">จำนวนที่เบิก</th>
                <th class="qty-col">จำนวนจับจ่าย</th>
                <th class="qty-col">วันที่รับคืน</th>
                <th class="price-col">ราคา/ชิ้น</th>
                <th class="remark-col">หมายเหตุ</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['item'] ?></td>
                    <td class="description"><?= Html::encode($item['description']) ?></td>
                    <td><?= $item['request_qty'] ?></td>
                    <td><?= $item['issue_qty'] ?></td>
                    <td><?= $item['return_qty'] > 0 ? date('d/m/Y') : '' ?></td>
                    <td class="number"><?= number_format($item['price_unit'], 2) ?></td>
                    <td class="description"><?= Html::encode($item['remark']) ?></td>
                </tr>
            <?php endforeach; ?>

            <!-- Empty rows -->
            <?php for ($i = count($items); $i < 10; $i++): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
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
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-name"><?= Html::encode($issuerName) ?></div>
            <div class="signature-label">ผู้เบิก</div>
        </div>
        <div class="signature-box">
            <div class="signature-name"><?= Html::encode($receiverName) ?></div>
            <div class="signature-label">ผู้จ่ายเครื่องมือ</div>
        </div>
        <div class="signature-box">
            <div class="signature-name"><?= Html::encode($approverName) ?></div>
            <div class="signature-label">ผู้ตรวจสอบ</div>
        </div>
        <div class="signature-box">
            <div class="signature-name"><?= Html::encode($stockerName) ?></div>
            <div class="signature-label">ผู้คืน</div>
        </div>
    </div>
</div>

<script>
    // Optional: Add functionality to print directly when page loads
    // window.onload = function() {
    //     window.print();
    // };
</script>