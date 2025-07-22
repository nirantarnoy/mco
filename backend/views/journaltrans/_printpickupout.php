<?php
/**
 * @var $this yii\web\View
 */

use yii\helpers\Html;

// Mock data
$requestorName = 'นายสมชาย ใจดี';
$requestDate = date('d/m/Y');
$movementType = 'out'; // 'out' or 'in'
$purpose = 'นำอุปกรณ์คอมพิวเตอร์ไปซ่อมที่ร้านภายนอก';
$vehicle = 'รถยนต์ส่วนตัว ทะเบียน กข-1234';

// Asset items
$items = [
    [
        'no' => 1,
        'asset_name' => 'โน้ตบุ๊ค Dell Latitude 5520',
        'quantity' => 1,
        'remark' => 'S/N: DELL-2024-001'
    ],
    [
        'no' => 2,
        'asset_name' => 'จอมอนิเตอร์ Samsung 24 นิ้ว',
        'quantity' => 1,
        'remark' => 'S/N: SAM-MON-24-002'
    ],
    [
        'no' => 3,
        'asset_name' => 'แป้นพิมพ์ Logitech K380',
        'quantity' => 2,
        'remark' => 'สำหรับทดสอบ'
    ],
    [
        'no' => 4,
        'asset_name' => 'เมาส์ไร้สาย Logitech M331',
        'quantity' => 2,
        'remark' => 'ใช้คู่กับแป้นพิมพ์'
    ],
    [
        'no' => 5,
        'asset_name' => 'สายแปลง HDMI to VGA',
        'quantity' => 3,
        'remark' => ''
    ]
];

// Signatures
$inspectorName = 'นายตรวจนับ พิจารณา';
$inspectorDate = $requestDate;
$requestorSignName = 'นายสมชาย ใจดี';
$requestorSignDate = $requestDate;
$approverName = 'นายอนุมัติ ผู้จัดการ';
$approverDate = '';

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
        font-family: 'Sarabun', Arial, sans-serif;
        font-size: 14px;
        line-height: 1.5;
        color: #000;
    }

    .header-section {
        text-align: center;
        margin-bottom: 20px;
    }

    .company-header {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }

    .company-logo {
        max-width: 100px;
        margin-right: 20px;
    }

    .company-info {
        text-align: left;
    }

    .company-name {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .form-title {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .form-info {
        margin-bottom: 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .info-left {
        flex: 1;
    }

    .info-right {
        text-align: right;
    }

    .form-field {
        display: inline-block;
        margin-bottom: 8px;
    }

    .form-label {
        font-weight: bold;
        margin-right: 10px;
    }

    .form-value {
        border-bottom: 1px solid #000;
        display: inline-block;
        min-width: 200px;
        padding: 0 5px;
    }

    .movement-type {
        margin: 20px 0;
        text-align: left;
    }

    .movement-type label {
        display: inline-block;
        margin-right: 30px;
        font-size: 16px;
    }

    .movement-type input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 8px;
        vertical-align: middle;
    }

    .purpose-section {
        margin-bottom: 15px;
        text-align: left;
    }

    .purpose-label {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .purpose-line {
        border-bottom: 1px solid #000;
        width: 100%;
        min-height: 25px;
        margin-bottom: 5px;
        padding: 2px 5px;
    }

    .vehicle-section {
        margin-bottom: 20px;
        text-align: left;
    }

    .table-section {
        margin-bottom: 30px;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: center;
    }

    .items-table th {
        background-color: #f0f0f0;
        font-weight: bold;
        font-size: 14px;
    }

    .items-table td {
        font-size: 13px;
        height: 30px;
    }

    .items-table .no-col {
        width: 50px;
    }

    .items-table .asset-col {
        width: 50%;
        text-align: left;
        padding-left: 10px;
    }

    .items-table .qty-col {
        width: 100px;
    }

    .items-table .remark-col {
        width: auto;
        text-align: left;
        padding-left: 10px;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        margin-top: 50px;
    }

    .signature-box {
        width: 30%;
        text-align: center;
    }

    .signature-label {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        margin: 40px 10px 5px 10px;
        min-height: 30px;
    }

    .signature-date {
        margin-top: 5px;
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

    .note-text {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
</style>

<div class="no-print">
    <button class="print-button" onclick="window.print()">พิมพ์ใบนำทรัพย์สินเข้า-ออก</button>
</div>

<div class="print-container">
    <!-- Header Section -->
    <div class="header-section">
        <div class="company-header">
            <?= Html::img('@web/images/mco-logo.png', ['class' => 'company-logo', 'alt' => 'MCO Logo']) ?>
            <div class="company-info">
                <div class="company-name">บริษัท เอ็ม.ซี.โอ. จำกัด</div>
            </div>
        </div>
        <h2 class="form-title">ใบนำทรัพย์สินเข้า - ออกนอกบริษัทฯ</h2>
    </div>

    <!-- Form Information -->
    <div class="form-info">
        <div class="info-row">
            <div class="info-left">
                <span class="form-label">ชื่อ :</span>
                <span class="form-value"><?= Html::encode($requestorName) ?></span>
            </div>
            <div class="info-right">
                <span class="form-label">วันที่</span>
                <span class="form-value" style="min-width: 150px;"><?= Html::encode($requestDate) ?></span>
            </div>
        </div>
    </div>

    <!-- Movement Type -->
    <div class="movement-type">
        <label>
            <input type="checkbox" <?= $movementType == 'out' ? 'checked' : '' ?>>
            นำทรัพย์สินออกนอกบริษัทฯ
        </label>
        <label>
            <input type="checkbox" <?= $movementType == 'in' ? 'checked' : '' ?>>
            นำทรัพย์สินเข้ามาในบริษัทฯ
        </label>
    </div>

    <!-- Purpose Section -->
    <div class="purpose-section">
        <div class="purpose-label">วัตถุประสงค์ :</div>
        <div class="purpose-line"><?= Html::encode($purpose) ?></div>
        <div class="purpose-line"></div>
    </div>

    <!-- Vehicle Section -->
    <div class="vehicle-section">
        <span class="form-label">พาหนะในการขนส่ง :</span>
        <span class="form-value" style="min-width: 400px;"><?= Html::encode($vehicle) ?></span>
    </div>

    <!-- Items Table -->
    <div class="table-section">
        <div style="margin-bottom: 10px; font-weight: bold;">รายการทรัพย์สิน :</div>
        <table class="items-table">
            <thead>
            <tr>
                <th class="no-col">No</th>
                <th class="asset-col">ชื่อทรัพย์สิน</th>
                <th class="qty-col">จำนวน</th>
                <th class="remark-col">หมายเหตุ</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['no'] ?></td>
                    <td class="asset-col"><?= Html::encode($item['asset_name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td class="remark-col"><?= Html::encode($item['remark']) ?></td>
                </tr>
            <?php endforeach; ?>

            <!-- Empty rows -->
            <?php for ($i = count($items); $i < 15; $i++): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td class="asset-col">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="remark-col">&nbsp;</td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">ผู้ตรวจนับ :</div>
            <div class="signature-line"><?= Html::encode($inspectorName) ?></div>
            <div class="signature-date">
                วันที่ : <?= Html::encode($inspectorDate) ?>
            </div>
        </div>

        <div class="signature-box">
            <div class="signature-label">ผู้ขอเบิก :</div>
            <div class="signature-line"><?= Html::encode($requestorSignName) ?></div>
            <div class="signature-date">
                วันที่ : <?= Html::encode($requestorSignDate) ?>
            </div>
        </div>

        <div class="signature-box">
            <div class="signature-label">ผู้อนุมัติ :</div>
            <div class="note-text" style="margin-bottom: 5px;">ผู้จัดการ/ผู้ช่วยผู้จัดการ</div>
            <div class="signature-line"><?= Html::encode($approverName) ?></div>
            <div class="signature-date">
                วันที่ : ............/............../..............
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