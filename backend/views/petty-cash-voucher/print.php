<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\PettyCashVoucher */

$this->title = 'พิมพ์ใบสำคัญจ่ายเงินสดย่อย - ' . $model->pcv_no;

// Add print styles that match the original form exactly
$this->registerCss("
@page {
    size: A4;
    margin: 0.5in;
}

@media print {
    .no-print { display: none !important; }
    body { 
        margin: 0; 
        padding: 0; 
        font-family: 'Sarabun', 'TH SarabunPSK', Arial, sans-serif; 
        font-size: 14px;
        color: #000;
    }
    .print-container { 
        max-width: 100%; 
        width: 100%;
        page-break-inside: avoid;
    }
}

.print-container {
    max-width: 210mm;
    margin: 0 auto;
    background: white;
    padding: 10mm;
    border: 1px solid #ddd;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.company-header {
    text-align: center;
    margin-bottom: 20px;
    border-bottom: 2px solid #000;
    padding-bottom: 15px;
}

.company-logo {
    width: 50px;
    height: 50px;
    background: linear-gradient(45deg, #d32f2f, #f57c00);
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 8px;
}

.company-name {
    font-size: 20px;
    font-weight: bold;
    margin: 5px 0;
    letter-spacing: 1px;
}

.form-title {
    font-size: 16px;
    font-weight: bold;
    margin: 5px 0 0 0;
}

.voucher-header {
    margin: 20px 0;
    display: table;
    width: 100%;
}

.voucher-left {
    display: table-cell;
    width: 60%;
    vertical-align: top;
}

.voucher-right {
    display: table-cell;
    width: 40%;
    vertical-align: top;
    text-align: right;
}

.header-field {
    margin-bottom: 8px;
    line-height: 1.4;
}

.header-label {
    font-weight: bold;
    display: inline-block;
    width: 80px;
}

.paid-for-section {
    margin: 15px 0;
    border: 1px solid #000;
    padding: 10px;
    min-height: 40px;
}

.paid-for-label {
    font-weight: bold;
    margin-bottom: 5px;
}

.voucher-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    font-size: 12px;
}

.voucher-table th,
.voucher-table td {
    border: 1px solid #000;
    padding: 6px 4px;
    text-align: center;
    vertical-align: middle;
    line-height: 1.2;
}

.voucher-table th {
    background-color: #f8f9fa;
    font-weight: bold;
    height: 35px;
}

.voucher-table td {
    height: 35px;
}

.voucher-table .detail-cell {
    text-align: left;
    padding: 6px 8px;
}

.voucher-table .amount-cell {
    text-align: right;
    padding-right: 8px;
}

.total-row {
    background-color: #f0f0f0;
    font-weight: bold;
}

.signature-section {
    margin-top: 30px;
    display: table;
    width: 100%;
}

.signature-box {
    display: table-cell;
    width: 50%;
    border: 1px solid #000;
    padding: 0;
    height: 120px;
    position: relative;
    vertical-align: top;
}

.signature-box + .signature-box {
    border-left: none;
}

.signature-label {
    position: absolute;
    top: 8px;
    left: 50%;
    transform: translateX(-50%);
    font-weight: bold;
    font-size: 12px;
}

.signature-name {
    position: absolute;
    bottom: 25px;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    text-align: center;
    border-bottom: 1px solid #000;
    padding-bottom: 2px;
    min-height: 16px;
}

.signature-name img {
            max-width: 130px !important;
            max-height: 50px !important;
            object-fit: contain;
        }

.signature-date {
    position: absolute;
    bottom: 5px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 11px;
    display: flex;
    gap: 5px;
    align-items: center;
}

.date-field {
    border-bottom: 1px solid #000;
    min-width: 25px;
    text-align: center;
    padding: 0 2px;
}

.form-code {
    position: absolute;
    bottom: 5px;
    right: 10px;
    font-size: 10px;
    color: #666;
}

.text-center { text-align: center; }
.text-right { text-align: right; }
.text-left { text-align: left; }
.font-bold { font-weight: bold; }

/* Hide empty borders for better printing */
@media print {
    .signature-section,
    .voucher-table,
    .paid-for-section {
        border-color: #000 !important;
    }
}
");

// Auto print when page loads
$this->registerJs("
window.onload = function() {
    setTimeout(function() {
        window.print();
    }, 800);
};
");
?>

<div class="no-print text-center mb-4">
    <div class="btn-group">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> พิมพ์
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> ปิด
        </button>
        <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-info">
            <i class="fas fa-eye"></i> ดูรายละเอียด
        </a>
    </div>
</div>

<div class="print-container">
    <!-- Company Header -->
    <div class="company-header">
        <div class="logo">
            <img src="../../backend/web/uploads/logo/mco_logo.png" class="logo-img" style="width: 32%;bottom: 5px;" alt="">
        </div>
        <div class="company-name">M.C.O. COMPANY LIMITED</div>
        <div class="form-title">PETTY CASH VOUCHER</div>
    </div>

    <!-- Voucher Header Info -->
    <div class="voucher-header">
        <div class="voucher-left">
            <div class="header-field">
                <span class="header-label">NAME:</span>
                <span><?= Html::encode(\backend\models\Vendor::findName($model->vendor_id)) ?></span>
            </div>
            <div class="header-field">
                <span class="header-label">AMOUNT:</span>
                <span><?= number_format($model->amount, 2) ?></span>
<!--                <span style="margin-left: 20px;">Paid For</span>-->
            </div>
        </div>
        <div class="voucher-right">
            <div class="header-field">
                <span class="header-label">PCV.NO:</span>
                <span><?= Html::encode($model->pcv_no) ?></span>
            </div>
            <div class="header-field">
                <span class="header-label">DATE:</span>
                <span><?= Yii::$app->formatter->asDate($model->date, 'dd/MM/yyyy') ?></span>
            </div>
        </div>
    </div>

    <!-- Paid For Section -->
    <div class="paid-for-section">
        <div class="paid-for-label">จ่ายเพื่อ (Paid For):</div>
        <div style="min-height: 20px; margin-top: 5px;">
            <?= $model->paid_for ? nl2br(Html::encode($model->paid_for)) : '&nbsp;' ?>
        </div>
    </div>

    <!-- Details Table -->
    <table class="voucher-table">
        <thead>
        <tr>
            <th style="width: 10%;">A/C CODE</th>
            <th style="width: 8%;">DATE</th>
            <th style="width: 28%;">DETAIL</th>
            <th style="width: 12%;">AMOUNT</th>
            <th style="width: 7%;">VAT</th>
            <th style="width: 10%;">VAT<br>จำนวน</th>
            <th style="width: 7%;">W/H</th>
            <th style="width: 7%;">อื่นๆ</th>
            <th style="width: 12%;">TOTAL</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $details = $model->details;
        $totalAmount = 0;

        // Calculate actual total
        foreach ($details as $detail) {
            $totalAmount += $detail->total;
        }

        // Ensure we have at least 8 rows like the original form
        $minRows = 8;
        $maxRows = max($minRows, count($details));

        for ($i = 0; $i < $maxRows; $i++):
            $detail = isset($details[$i]) ? $details[$i] : null;
            ?>
            <tr>
                <td class="text-center">
                    <?= $detail ? Html::encode($detail->ac_code) : '&nbsp;' ?>
                </td>
                <td class="text-center">
                    <?= $detail && $detail->detail_date ? Yii::$app->formatter->asDate($detail->detail_date, 'dd/MM/yy') : '&nbsp;' ?>
                </td>
                <td class="detail-cell text-left">
                    <?= $detail ? nl2br(Html::encode($detail->detail)) : '&nbsp;' ?>
                </td>
                <td class="amount-cell">
                    <?= $detail && $detail->amount > 0 ? number_format($detail->amount, 2) : '&nbsp;' ?>
                </td>
                <td class="amount-cell">
                    <?= $detail && $detail->vat > 0 ? number_format($detail->vat, 2) : '&nbsp;' ?>
                </td>
                <td class="amount-cell">
                    <?= $detail && $detail->vat_amount > 0 ? number_format($detail->vat_amount, 2) : '&nbsp;' ?>
                </td>
                <td class="amount-cell">
                    <?= $detail && $detail->wht > 0 ? number_format($detail->wht, 2) : '&nbsp;' ?>
                </td>
                <td class="amount-cell">
                    <?= $detail && $detail->other > 0 ? number_format($detail->other, 2) : '&nbsp;' ?>
                </td>
                <td class="amount-cell font-bold">
                    <?= $detail && $detail->total > 0 ? number_format($detail->total, 2) : '&nbsp;' ?>
                </td>
            </tr>
        <?php endfor; ?>

        <!-- Total Row -->
        <tr class="total-row">
            <td colspan="8" class="text-right font-bold" style="padding-right: 15px;">
                TOTAL
            </td>
            <td class="amount-cell font-bold">
                <?= number_format($totalAmount, 2) ?>
            </td>
        </tr>
        </tbody>
    </table>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">ISSUED BY</div>
            <div class="signature-name">
                <?php
                $approve_signature = \backend\models\User::findEmployeeSignature(trim($model->created_by));
                if(!empty($approve_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?=$approve_signature?>" alt="Requestor Signature">
                <?php endif; ?>
<!--                --><?php //= $model->issued_by ? Html::encode($model->issued_by) : '&nbsp;' ?>
            </div>
            <div class="signature-date">
                <span class="date-field">
                    <?= $model->issued_date ? date('d', strtotime($model->issued_date)) : '__' ?>
                </span>
                <span>/</span>
                <span class="date-field">
                    <?= $model->issued_date ? date('m', strtotime($model->issued_date)) : '__' ?>
                </span>
                <span>/</span>
                <span class="date-field" style="min-width: 35px;">
                    <?= $model->issued_date ? date('Y', strtotime($model->issued_date)) : '____' ?>
                </span>
            </div>
        </div>

        <div class="signature-box">
            <div class="signature-label">APPROVED BY</div>
            <div style="height: 10px;"></div>
            <div class="signature-name">
                <?php
                $approve_signature = \backend\models\User::findEmployeeSignature(trim($model->approved_by));
                if(!empty($approve_signature)): ?>
                <img src="../../backend/web/uploads/employee_signature/<?=$approve_signature?>" alt="Requestor Signature">
                <?php endif; ?>
<!--                --><?php //= $model->approved_by ? Html::encode($model->approved_by) : '&nbsp;' ?>
            </div>
            <div class="signature-date">
                <span class="date-field">
                    <?= $model->approved_date ? date('d', strtotime($model->approved_date)) : '__' ?>
                </span>
                <span>/</span>
                <span class="date-field">
                    <?= $model->approved_date ? date('m', strtotime($model->approved_date)) : '__' ?>
                </span>
                <span>/</span>
                <span class="date-field" style="min-width: 35px;">
                    <?= $model->approved_date ? date('Y', strtotime($model->approved_date)) : '____' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Form Code -->
    <div class="form-code">
        F-WP-FMA-004-001 Rev.N
    </div>
</div>