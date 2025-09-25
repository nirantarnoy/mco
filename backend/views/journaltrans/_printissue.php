<?php
/**
 * @var $this yii\web\View
 */

use yii\helpers\Html;

$emp_info = \backend\models\Employee::findEmpInfo($model->created_by);
// Mock data
$documentNo = '';
$issueDate = date('m/d/Y', strtotime($model->trans_date));
$issueTime = date('H:i', strtotime($model->created_at));
$documentNumber = $model->journal_no;
$issueDept = $emp_info != null ? $emp_info['department_name'] : '';

// Issue type
$issueType = 'withdraw'; // 'borrow', 'transfer', 'withdraw'

$items = [];
foreach ($model_line as $line) {
    $item = [
        'item' => $line->id,
        'description' => \backend\models\Product::findProductName($line->product_id),
        'request_qty' => $line->qty,
        'issue_qty' => $model->stock_type_id == 2 ? $line->qty : 0,
        'return_qty' => $model->stock_type_id == 1 ? $line->qty : 0,
        'price_unit' => $line->line_price,
        'remark' => ''
    ];
    $items[] = $item;
}

// Signatures
$issuerName = '';
$receiverName = '';
$approverName = '';
$stockerName = '';

?>

<style>
    @media print {
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        .print-container {
            width: 138mm !important;
            height: 200mm !important;
            margin: 0 !important;
            padding: 5mm !important;
            background: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 9px !important;
            line-height: 1.1 !important;
            box-sizing: border-box !important;
            transform-origin: top left !important;
        }

        .header-section {
            border: 1px solid #000 !important;
            padding: 3px !important;
        }

        .company-name {
            font-size: 10px !important;
        }

        .form-title {
            font-size: 12px !important;
            margin: 1px 0 !important;
        }

        .doc-info {
            font-size: 8px !important;
        }

        .doc-info-item {
            margin-bottom: 0.5px !important;
        }

        .issue-type {
            gap: 6px !important;
            margin: 3px 0 !important;
            flex-wrap: wrap !important;
        }

        .issue-type label {
            font-size: 7px !important;
        }

        .issue-type input[type="checkbox"] {
            width: 10px !important;
            height: 10px !important;
            margin-right: 1px !important;
        }

        .doc-number-section {
            margin-top: 3px !important;
            font-size: 8px !important;
        }

        .table-section {
            border: 1px solid #000 !important;
        }

        .items-table th,
        .items-table td {
            border: 0.5px solid #000 !important;
            padding: 1px !important;
            font-size: 7px !important;
        }

        .items-table th {
            background-color: white !important;
            font-weight: bold;
            height: 16px !important;
        }

        .items-table td {
            height: 14px !important;
        }

        .items-table .description {
            padding-left: 2px !important;
        }

        .items-table .number {
            padding-right: 2px !important;
        }

        .items-table .item-col {
            width: 20px !important;
        }

        .items-table .desc-col {
            width: 30% !important;
        }

        .items-table .qty-col {
            width: 30px !important;
        }

        .items-table .price-col {
            width: 30px !important;
        }

        .signature-section {
            border: 1px solid #000 !important;
            height: 50px !important;
        }

        .signature-box {
            padding: 2px !important;
        }

        .signature-label {
            font-size: 7px !important;
            bottom: 1px !important;
        }

        .signature-name {
            max-width: 50px !important;
            max-height: 30px !important;
        }

        .signature-name img {
            max-width: 50px !important;
            max-height: 30px !important;
        }

        .header-table td {
            padding: 1px !important;
        }

        .doc-info-table td {
            padding: 0.5px !important;
        }

        .header-table td:first-child {
            width: 18% !important;
        }

        .header-table td:nth-child(2) {
            width: 54% !important;
        }

        .header-table td:last-child {
            width: 28% !important;
        }

        .company-logo {
            max-width: 40px !important;
            margin-right: 6px !important;
        }

        .no-print {
            display: none;
        }

        @page {
            size: A5 portrait;
            margin: 0;
            padding: 0;
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
        background-color: white;
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
        max-width: 130px !important;
        max-height: 55px !important;
        object-fit: contain;
    }
    .signature-name img{
        max-width: 130px !important;
        max-height: 55px !important;
        object-fit: contain;
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

    /* ปรับ table ให้ใช้พื้นที่เต็มที่ */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .header-table td {
        vertical-align: top;
        padding: 2px;
    }

    .doc-info-table {
        width: 100%;
        border-collapse: collapse;
    }

    .doc-info-table td {
        text-align: left;
        padding: 1px;
    }
</style>

<div class="no-print">
    <button class="print-button" onclick="window.print()">พิมพ์ใบเบิก A5</button>
</div>

<div class="print-container">
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-top">
            <table class="header-table">
                <tr>
                    <td style="width: 20%">
                        <img src="../../backend/web/uploads/logo/mco_logo.png" width="100%" alt="">
                    </td>
                    <td style="width: 55%">
                        <div>
                            <div class="company-name" style="text-align: center;">M.C.O. COMPANY LIMITED</div>
                            <div class="form-title">ใบเบิก - คืน</div>
                        </div>
                    </td>
                    <td style="width: 25%">
                        <div class="doc-info">
                            <table class="doc-info-table">
                                <tr>
                                    <td colspan="2">
                                        <div class="doc-info-item">เลขที่ <b><?= Html::encode($documentNumber) ?></b></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="doc-info-item">วันที่เบิก : <b><?= Html::encode($issueDate) ?></b></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="doc-info-item">หน่วยงาน : <b><?= $issueDept ?></b></div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
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
                <span>สินค้าซ่อมาขายไป</span>
            </label>
            <label>
                <input type="checkbox">
                <span>N-Vent</span>
            </label>
        </div>

        <div class="doc-number-section">
            <div>
                <span class="doc-label">ชื่องาน:</span> <?=$model->customer_name?>
            </div>
            <div>
                <span class="doc-label">RY-QT:</span> <?= Html::encode(\backend\models\Job::findJobNo($model->job_id)) ?>
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
                <th class="qty-col">จำนวนรับคืน</th>
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
                    <td><?= $item['issue_qty'] ?></td>
                    <td><?= $item['return_qty'] ?></td>
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

    <?php
    $issue_signature = \backend\models\User::findEmployeeSignature($model->emp_trans_id);
    $approve_signature = \backend\models\User::findEmployeeSignature($model->approve_by);
    ?>
    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-name">
                <?php if ($model->stock_type_id == 2): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?= $issue_signature ?>"
                         alt="Approver Signature">
                <?php endif; ?>
            </div>
            <div class="signature-label">ผู้เบิก</div>
        </div>
        <div class="signature-box">
            <div class="signature-name">
                <img src="../../backend/web/uploads/employee_signature/<?= $approve_signature ?>"
                     alt="Approver Signature">
            </div>
            <div class="signature-label">ผู้จ่ายเครื่องมือ</div>
        </div>
        <div class="signature-box">
            <div class="signature-name">
                <img src="../../backend/web/uploads/employee_signature/<?= $approve_signature ?>"
                     alt="Approver Signature">
            </div>
            <div class="signature-label">ผู้ตรวจสอบ</div>
        </div>
        <div class="signature-box">
            <div class="signature-name">
                <?php if ($model->stock_type_id == 1): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?= $issue_signature ?>"
                         alt="Approver Signature">
                <?php endif; ?>
            </div>
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