<?php
/**
 * @var $this yii\web\View
 */

use yii\helpers\Html;

$emp_info = \backend\models\Employee::findEmpInfo($model->created_by);

// ข้อมูลเอกสาร
$issueDate = date('d/m/Y', strtotime($model->trans_date));
$documentNumber = $model->journal_no;
$issueDept = $emp_info != null ? $emp_info['department_name'] : '';
$issueType = 'withdraw'; // 'borrow', 'transfer', 'withdraw'

// รายการสินค้า
$items = [];
foreach ($model_line as $line) {
    $items[] = [
        'item' => $line->id,
        'description' => \backend\models\Product::findProductName($line->product_id),
        'issue_qty' => $model->stock_type_id == 2 ? $line->qty : 0,
        'return_qty' => $model->stock_type_id == 1 ? $line->qty : 0,
        'price_unit' => $line->line_price,
        'remark' => ''
    ];
}

// ลายเซ็น
$issue_signature = \backend\models\User::findEmployeeSignature($model->emp_trans_id);
$approve_signature = \backend\models\User::findEmployeeSignature($model->approve_by);
?>

<style>
    @media print {
        @page {
            size: A5 landscape;
            margin: 5mm;
        }

        body {
            margin: 0;
            padding: 0;
            background: white !important;
            font-family: 'Sarabun', Arial, sans-serif !important;
        }

        .print-container {
            width: 210mm !important;
            height: 148mm !important;
            margin: 0 auto !important;
            padding: 5mm !important;
            background: white !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-size: 10px !important;
            line-height: 1.2 !important;
        }

        .no-print {
            display: none !important;
        }
    }

    /* สำหรับแสดงในหน้าเว็บ */
    .print-container {
        width: 210mm;
        height: 148mm;
        margin: 0 auto;
        padding: 5mm;
        background: white;
        font-family: 'Sarabun', Arial, sans-serif;
        font-size: 14px;
        color: #000;
        box-sizing: border-box;
        /*border: 1px solid #ccc;*/
    }

    .header-section {
        border: 1px solid #000;
        padding: 3mm;
        margin-bottom: 0;
    }

    .company-name {
        font-size: 14px;
        font-weight: bold;
    }

    .form-title {
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        margin: 2px 0;
    }

    .doc-info {
        font-size: 14px;
        text-align: right;
    }

    .issue-type {
        display: flex;
        justify-content: space-around;
        margin: 2mm 0;
        flex-wrap: wrap;
    }

    .issue-type label {
        font-size: 14px;
    }

    .issue-type input {
        width: 10px;
        height: 10px;
        margin-right: 2px;
    }

    .doc-number-section {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        margin-top: 2mm;
    }

    .table-section {
        border: 1px solid #000;
        border-top: none;
        margin-top: 1mm;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #000;
        padding: 2px;
        font-size: 9px;
        text-align: center;
        vertical-align: middle;
    }

    .items-table th {
        font-weight: bold;
        background-color: #f9f9f9;
    }

    .items-table .description {
        text-align: left;
        padding-left: 3px;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        border: 1px solid #000;
        border-top: none;
        height: 35mm;
        margin-top: 1mm;
    }

    .signature-box {
        flex: 1;
        border-right: 1px solid #000;
        text-align: center;
        padding: 2mm;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .signature-box:last-child {
        border-right: none;
    }

    .signature-label {
        font-weight: bold;
        font-size: 10px;
        margin-top: 5mm;
    }

    .signature-name img {
        max-width: 40mm;
        max-height: 15mm;
        object-fit: contain;
    }

    .no-print {
        text-align: center;
        margin-bottom: 10px;
    }

    .print-button {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
    }

    .print-button:hover {
        background-color: #0056b3;
    }
</style>

<div class="no-print">
    <button class="print-button" onclick="window.print()">🖨️ พิมพ์ใบเบิก A5</button>
</div>

<div class="print-container">
    <!-- Header -->
    <div class="header-section">
        <table width="100%">
            <tr>
                <td width="20%">
                    <img src="../../backend/web/uploads/logo/mco_logo.png" class="company-logo" style="max-width: 60px;">
                </td>
                <td width="55%" style="text-align: center;">
                    <div class="company-name">M.C.O. COMPANY LIMITED</div>
                    <div class="form-title">ใบเบิก - คืน</div>
                </td>
                <td width="25%" class="doc-info">
                    <div>เลขที่: <b><?= Html::encode($documentNumber) ?></b></div>
                    <div>วันที่เบิก: <b><?= Html::encode($issueDate) ?></b></div>
                    <div>หน่วยงาน: <b><?= Html::encode($issueDept) ?></b></div>
                </td>
            </tr>
        </table>

        <div class="issue-type">
            <label><input type="checkbox" <?= $issueType == 'borrow' ? 'checked' : '' ?>> วัสดุสิ้นเปลือง</label>
            <label><input type="checkbox" <?= $issueType == 'transfer' ? 'checked' : '' ?>> อุปกรณ์ไฟฟ้า</label>
            <label><input type="checkbox" <?= $issueType == 'withdraw' ? 'checked' : '' ?>> เครื่องมือ</label>
            <label><input type="checkbox"> วัสดุประกอบงาน</label>
            <label><input type="checkbox"> สินค้าซ่อมไปขาย</label>
            <label><input type="checkbox"> N-Vent</label>
        </div>

        <div class="doc-number-section">
            <div><b>ชื่องาน:</b> <?= Html::encode($model->customer_name) ?></div>
            <div><b>RY-QT:</b> <?= Html::encode(\backend\models\Job::findJobNo($model->job_id)) ?></div>
        </div>
    </div>

    <!-- รายการสินค้า -->
    <div class="table-section">
        <table class="items-table">
            <thead>
            <tr>
                <th>ลำดับ</th>
                <th>รายละเอียด/รายการ</th>
                <th>จำนวนเบิก</th>
                <th>จำนวนคืน</th>
                <th>วันที่คืน</th>
                <th>ราคา/ชิ้น</th>
                <th>หมายเหตุ</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['item'] ?></td>
                    <td class="description"><?= Html::encode($item['description']) ?></td>
                    <td><?= $item['issue_qty'] ?></td>
                    <td><?= $item['return_qty'] ?></td>
                    <td><?= $item['return_qty'] > 0 ? date('d/m/Y') : '' ?></td>
                    <td><?= number_format($item['price_unit'], 2) ?></td>
                    <td><?= Html::encode($item['remark']) ?></td>
                </tr>
            <?php endforeach; ?>

            <?php for ($i = count($items); $i < 10; $i++): ?>
                <tr>
                    <td style="padding:10px;">&nbsp;</td>
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

    <!-- ลายเซ็น -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-name">
                <?php if ($model->stock_type_id == 2): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?= $issue_signature ?>" alt="">
                <?php endif; ?>
            </div>
            <div class="signature-label">ผู้เบิก</div>
        </div>
        <div class="signature-box">
            <div class="signature-name">
                <img src="../../backend/web/uploads/employee_signature/<?= $approve_signature ?>" alt="">
            </div>
            <div class="signature-label">ผู้จ่ายเครื่องมือ</div>
        </div>
        <div class="signature-box">
            <div class="signature-name">
                <img src="../../backend/web/uploads/employee_signature/<?= $approve_signature ?>" alt="">
            </div>
            <div class="signature-label">ผู้ตรวจสอบ</div>
        </div>
        <div class="signature-box">
            <div class="signature-name">
                <?php if ($model->stock_type_id == 1): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?= $issue_signature ?>" alt="">
                <?php endif; ?>
            </div>
            <div class="signature-label">ผู้คืน</div>
        </div>
    </div>
</div>
