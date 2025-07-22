<?php
/**
 * @var $this yii\web\View
 */

use yii\helpers\Html;

// Mock data
$repairNo = date('Y') . ' ' . date('m') . '-001';
$issuerName = 'นายสมชาย ใจดี';
$issueDate = date('d/m/Y');
$expectedDate = date('d/m/Y', strtotime('+7 days'));

// Repair details
$repairIssue = 'เครื่องคอมพิวเตอร์เปิดไม่ติด มีเสียงบี๊บดังเป็นจังหวะ';
$productCode = 'PC-IT-2024-015';
$productDescription = 'คอมพิวเตอร์ตั้งโต๊ะ Dell OptiPlex 7090 (ฝ่ายบัญชี)';
$serviceMgrName = 'ฝ่ายไอที';

// Repair solution
$howToRepair = 'ตรวจสอบพบว่า RAM หลวม ทำการถอดและใส่ใหม่ ทำความสะอาดช่องเสียบ RAM และทดสอบการทำงาน';

// Cost breakdown
$directMaterial = 150.00;
$directMaterialDesc = 'น้ำยาทำความสะอาด Contact Cleaner';
$directLabour = 300.00;
$directLabourDesc = 'ค่าแรงตรวจสอบและซ่อม 1 ชั่วโมง';
$overhead = 50.00;
$overheadDesc = 'ค่าขนส่งและเบ็ดเตล็ด';
$totalCost = $directMaterial + $directLabour + $overhead;

// Status
$isFinished = false;

// Signatures
$issuerSign = 'นายสมชาย ใจดี';
$operatorSign = 'นายช่างเทค นิคอล';
$approverSign = '';

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
            background: white !important;
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
        background: white;
        font-family: 'Sarabun', Arial, sans-serif;
        font-size: 14px;
        line-height: 1.4;
        color: #000;
    }

    .form-table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid #000;
    }

    .form-table td {
        border: 1px solid #000;
        padding: 8px;
        vertical-align: top;
    }

    .header-row {
        background-color: #f0f0f0;
    }

    .header-cell {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        padding: 10px;
    }

    .repair-no-cell {
        background-color: #ffffcc;
        text-align: center;
        font-weight: bold;
        padding: 10px;
    }

    .label-cell {
        background-color: #ffffcc;
        font-weight: bold;
        width: 150px;
    }

    .content-cell {
        background-color: white;
    }

    .picture-section {
        min-height: 200px;
        text-align: center;
        vertical-align: middle;
        position: relative;
    }

    .picture-placeholder {
        display: inline-block;
        width: 80%;
        height: 180px;
        border: 2px dashed #999;
        margin: 10px;
        line-height: 180px;
        color: #999;
        font-size: 16px;
    }

    .cost-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cost-table td {
        padding: 5px;
        border: none;
    }

    .cost-item {
        border-bottom: 1px solid #ccc;
        padding: 8px 0;
    }

    .total-row {
        font-weight: bold;
        font-size: 16px;
        border-top: 2px solid #000;
        padding-top: 10px;
    }

    .status-section {
        padding: 10px;
    }

    .status-item {
        display: inline-block;
        margin-right: 30px;
        font-size: 14px;
    }

    .checkbox {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 1px solid #000;
        margin-right: 5px;
        vertical-align: middle;
        background: white;
    }

    .checkbox.checked {
        background: #000;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding: 20px;
    }

    .signature-box {
        width: 30%;
        text-align: center;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        margin: 40px 0 5px 0;
        min-height: 30px;
    }

    .signature-label {
        font-weight: bold;
        font-size: 14px;
    }

    .company-logo {
        max-width: 100px;
        float: left;
        margin-right: 20px;
    }

    .form-number {
        text-align: right;
        font-size: 12px;
        margin-top: 20px;
        padding-right: 10px;
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

    .bilingual {
        font-size: 13px;
        color: #555;
    }
</style>

<div class="no-print">
    <button class="print-button" onclick="window.print()">พิมพ์ใบแจ้งซ่อม</button>
</div>

<div class="print-container">
    <table class="form-table">
        <!-- Header -->
        <tr class="header-row">
            <td colspan="2" class="header-cell">
                <?= Html::img('@web/images/mco-logo.png', ['class' => 'company-logo', 'alt' => 'MCO Logo']) ?>
                <div style="display: inline-block; vertical-align: middle;">
                    <div>Repairing request</div>
                    <div>ใบแจ้งซ่อม</div>
                </div>
            </td>
            <td class="repair-no-cell">
                <div>หมายเลขใบแจ้งซ่อม</div>
                <div style="font-size: 16px; margin-top: 5px;">Year Month-xxx</div>
                <div style="font-size: 18px; margin-top: 5px;"><?= Html::encode($repairNo) ?></div>
            </td>
        </tr>

        <!-- Issuer and Date -->
        <tr>
            <td class="label-cell">ผู้แจ้ง <span class="bilingual">(Issued)</span></td>
            <td class="content-cell"><?= Html::encode($issuerName) ?></td>
            <td class="content-cell" style="text-align: center;">
                <strong>วันที่ <span class="bilingual">(Date)</span></strong><br>
                <?= Html::encode($issueDate) ?>
            </td>
        </tr>

        <!-- Issue Description -->
        <tr>
            <td class="label-cell">เรื่องที่แจ้ง<br><span class="bilingual">(What)</span></td>
            <td colspan="2" class="content-cell">
                <?= Html::encode($repairIssue) ?>
            </td>
        </tr>

        <!-- Product Code -->
        <tr>
            <td class="label-cell">รหัสสินค้า<br><span class="bilingual">(Code No.)</span></td>
            <td colspan="2" class="content-cell">
                <?= Html::encode($productCode) ?>
            </td>
        </tr>

        <!-- Product Description -->
        <tr>
            <td class="label-cell">รายละเอียดสินค้า<br><span class="bilingual">(Description)</span></td>
            <td colspan="2" class="content-cell">
                <?= Html::encode($productDescription) ?>
            </td>
        </tr>

        <!-- Expected Date and Picture -->
        <tr>
            <td class="label-cell">กำหนดเสร็จ<br><span class="bilingual">(Expected date)</span></td>
            <td class="content-cell" style="width: 30%;">
                <?= Html::encode($expectedDate) ?>
            </td>
            <td rowspan="2" class="content-cell picture-section">
                <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">
                    ผู้จัดการ รับทราบ<br>
                    <span class="bilingual">(Service Mgr.)</span>
                </div>
                <div style="margin-top: 20px; font-size: 16px;">
                    <?= Html::encode($serviceMgrName) ?>
                </div>
            </td>
        </tr>

        <!-- Picture -->
        <tr>
            <td class="label-cell">รูปภาพ <span class="bilingual">(Picture)</span></td>
            <td class="content-cell picture-section">
                <div class="picture-placeholder">
                    [รูปภาพ]
                </div>
            </td>
        </tr>

        <!-- How to Repair -->
        <tr>
            <td class="label-cell">การแก้ไข<br><span class="bilingual">(How to repair)</span></td>
            <td colspan="2" class="content-cell" style="min-height: 80px;">
                <?= Html::encode($howToRepair) ?>
            </td>
        </tr>

        <!-- Cost Section -->
        <tr>
            <td colspan="3" style="padding: 0;">
                <table class="cost-table">
                    <tr>
                        <td colspan="3" style="text-align: center; font-weight: bold; background-color: #ffffcc; padding: 8px;">
                            ค่าใช้จ่าย <span class="bilingual">(Cost)</span>
                        </td>
                    </tr>
                    <tr class="cost-item">
                        <td style="width: 40%; padding-left: 20px;">
                            1. วัสดุที่ใช้ <span class="bilingual">(Direct mat'l)</span>
                        </td>
                        <td style="width: 40%;">
                            <?= Html::encode($directMaterialDesc) ?>
                        </td>
                        <td style="text-align: right; padding-right: 20px;">
                            <?= number_format($directMaterial, 2) ?> บาท
                        </td>
                    </tr>
                    <tr class="cost-item">
                        <td style="padding-left: 20px;">
                            2. แรงงาน <span class="bilingual">(Direct Labour)</span>
                        </td>
                        <td>
                            <?= Html::encode($directLabourDesc) ?>
                        </td>
                        <td style="text-align: right; padding-right: 20px;">
                            <?= number_format($directLabour, 2) ?> บาท
                        </td>
                    </tr>
                    <tr class="cost-item">
                        <td style="padding-left: 20px;">
                            3. ค่าโสหุ้ย <span class="bilingual">(Overhead)</span>
                        </td>
                        <td>
                            <?= Html::encode($overheadDesc) ?>
                        </td>
                        <td style="text-align: right; padding-right: 20px;">
                            <?= number_format($overhead, 2) ?> บาท
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2" style="padding-left: 20px;">
                            รวมเป็นเงิน <span class="bilingual">(Total)</span>
                        </td>
                        <td style="text-align: right; padding-right: 20px; font-size: 18px;">
                            <?= number_format($totalCost, 2) ?> บาท
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Status -->
        <tr>
            <td class="label-cell">สถานะ <span class="bilingual">(Status)</span></td>
            <td colspan="2" class="content-cell status-section">
                <div class="status-item">
                    <span class="checkbox <?= $isFinished ? 'checked' : '' ?>">
                        <?= $isFinished ? '✓' : '' ?>
                    </span>
                    เสร็จแล้ว <span class="bilingual">(Finished)</span>
                </div>
                <div class="status-item">
                    <span class="checkbox <?= !$isFinished ? 'checked' : '' ?>">
                        <?= !$isFinished ? '✓' : '' ?>
                    </span>
                    ยังไม่เสร็จ <span class="bilingual">(Not Finished)</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"><?= Html::encode($issuerSign) ?></div>
            <div class="signature-label">ผู้แจ้ง</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"><?= Html::encode($operatorSign) ?></div>
            <div class="signature-label">ผู้ดำเนินการ</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"><?= Html::encode($approverSign) ?></div>
            <div class="signature-label">ผู้อนุมัติ</div>
        </div>
    </div>

    <!-- Form Number -->
    <div class="form-number">
        F-WP-MCO-003-007 Rev.1
    </div>
</div>

<script>
    // Optional: Add functionality to print directly when page loads
    // window.onload = function() {
    //     window.print();
    // };
</script>