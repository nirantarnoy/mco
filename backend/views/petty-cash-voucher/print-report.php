<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PettyCashReportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'รายงานเงินสดย่อย M.C.O.CO.,LTD';

// Get total summary
$totalSummary = $searchModel->getTotalSummary();

// Add print styles
$this->registerCss("
@page {
    size: A4 landscape;
    margin: 0.5in;
}

@media print {
    .no-print { display: none !important; }
    body { 
        margin: 0; 
        padding: 0; 
        font-family: 'Sarabun', 'TH SarabunPSK', Arial, sans-serif; 
        font-size: 12px;
        color: #000;
    }
    .print-container { 
        max-width: 100%; 
        width: 100%;
    }
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; page-break-after: auto; }
    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }
}

.print-container {
    max-width: 100%;
    margin: 0 auto;
    background: white;
    padding: 20px;
}

.report-header {
    text-align: center;
    margin-bottom: 20px;
    border-bottom: 2px solid #000;
    padding-bottom: 15px;
}

.company-name {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 5px;
}

.report-title {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 10px;
}

.report-period {
    font-size: 14px;
    margin-bottom: 5px;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    font-size: 11px;
}

.report-table th,
.report-table td {
    border: 1px solid #000;
    padding: 6px 4px;
    text-align: center;
    vertical-align: middle;
    line-height: 1.2;
}

.report-table th {
    background-color: #f8f9fa;
    font-weight: bold;
    height: 30px;
}

.report-table .text-left { text-align: left; }
.report-table .text-right { text-align: right; }
.report-table .text-center { text-align: center; }

.total-row {
    background-color: #fff2cc;
    font-weight: bold;
}

.summary-section {
    margin-top: 20px;
    display: table;
    width: 100%;
}

.summary-box {
    display: table-cell;
    width: 25%;
    border: 1px solid #000;
    padding: 10px;
    text-align: center;
    vertical-align: middle;
}

.summary-label {
    font-weight: bold;
    margin-bottom: 5px;
}

.summary-amount {
    font-size: 14px;
    font-weight: bold;
}

.form-code {
    position: fixed;
    bottom: 10px;
    right: 20px;
    font-size: 10px;
    color: #666;
}

.remaining-balance {
    margin-top: 15px;
    text-align: right;
    font-weight: bold;
}
");

// Auto print when page loads
$this->registerJs("
window.onload = function() {
    setTimeout(function() {
        window.print();
    }, 1000);
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
        <a href="<?= \yii\helpers\Url::to(['report']) ?>" class="btn btn-info">
            <i class="fas fa-arrow-left"></i> กลับไปรายงาน
        </a>
    </div>
</div>

<div class="print-container">
    <!-- Report Header -->
    <div class="report-header">
        <div class="company-name">รายงานเงินสดย่อย M.C.O.CO.,LTD</div>
        <div class="report-period">
            ระหว่างวันที่ <?= Yii::$app->formatter->asDate($searchModel->date_from, 'dd/MM/yyyy') ?>
            ถึง <?= Yii::$app->formatter->asDate($searchModel->date_to, 'dd/MM/yyyy') ?>
        </div>
        <?php if (!empty($searchModel->ac_code)): ?>
            <div>รหัสบัญชี: <?= Html::encode($searchModel->ac_code) ?></div>
        <?php endif; ?>
        <?php if ($searchModel->vat_type !== 'all'): ?>
            <div>ประเภท: <?= $searchModel->vat_type === 'vat' ? 'มี VAT' : 'ไม่มี VAT' ?></div>
        <?php endif; ?>
    </div>

    <!-- Report Table -->
    <?php if ($searchModel->report_type === 'summary'): ?>
        <!-- Summary Report Table -->
        <table class="report-table">
            <thead>
            <tr>
                <th style="width: 5%;">ลำดับ</th>
                <th style="width: 10%;">A/C CODE</th>
                <th style="width: 12%;">รายจ่าย</th>
                <th style="width: 10%;">VAT</th>
                <th style="width: 10%;">VAT ต้องห้าม</th>
                <th style="width: 10%;">W/H</th>
                <th style="width: 10%;">อื่นๆ</th>
                <th style="width: 12%;">ทั้งหมด</th>
                <th style="width: 8%;">จำนวนรายการ</th>
                <th style="width: 13%;">คงเหลือ</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $models = $dataProvider->getModels();
            $runningBalance = 0; // You might want to get opening balance from settings
            foreach ($models as $index => $model):
                $runningBalance -= $model['grand_total']; // Subtract expenses
                ?>
                <tr>
                    <td class="text-center"><?= $index + 1 ?></td>
                    <td class="text-center"><?= Html::encode($model['ac_code'] ?: '-') ?></td>
                    <td class="text-right"><?= number_format($model['total_amount'], 2) ?></td>
                    <td class="text-right"><?= number_format($model['total_vat_amount'], 2) ?></td>
                    <td class="text-right"><?= number_format($model['total_vat_prohibit'], 2) ?></td>
                    <td class="text-right"><?= number_format($model['total_wht'], 2) ?></td>
                    <td class="text-right"><?= number_format($model['total_other'], 2) ?></td>
                    <td class="text-right"><?= number_format($model['grand_total'], 2) ?></td>
                    <td class="text-center"><?= number_format($model['count_transactions']) ?></td>
                    <td class="text-right"><?= number_format($runningBalance, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-center"><strong>รวมทั้งหมด</strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['total_amount'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['total_vat_amount'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['total_vat_prohibit'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['total_wht'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['total_other'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['grand_total'], 2) ?></strong></td>
                <td class="text-center"><strong><?= number_format($totalSummary['count_transactions']) ?></strong></td>
                <td class="text-right"><strong><?= number_format($runningBalance, 2) ?></strong></td>
            </tr>
            </tfoot>
        </table>
    <?php else: ?>
        <!-- Detail Report Table -->
        <table class="report-table">
            <thead>
            <tr>
                <th style="width: 8%;">วันที่</th>
                <th style="width: 15%;">รายการ</th>
                <th style="width: 8%;">รายรับ</th>
                <th style="width: 8%;">รายจ่าย</th>
                <th style="width: 8%;">VAT</th>
                <th style="width: 8%;">VAT ต้องห้าม</th>
                <th style="width: 8%;">W/H</th>
                <th style="width: 8%;">อื่นๆ</th>
                <th style="width: 9%;">ทั้งหมด</th>
                <th style="width: 10%;">คงเหลือ</th>
                <th style="width: 10%;">เลขที่เอกสาร</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $models = $dataProvider->getModels();
            $runningBalance = 0; // You might want to get opening balance from settings
            foreach ($models as $model):
                $runningBalance -= $model['total']; // Subtract expenses
                ?>
                <tr>
                    <td class="text-center"><?= Yii::$app->formatter->asDate($model['voucher_date'], 'dd/MM/yy') ?></td>
                    <td class="text-left"><?= Html::encode($model['detail']) ?></td>
                    <td class="text-right">-</td>
                    <td class="text-right"><?= number_format($model['amount'], 2) ?></td>
                    <td class="text-right"><?= number_format($model['vat_amount'], 2) ?></td>
                    <td class="text-right"><?= number_format($model['vat_prohibit'], 2) ?></td>
                    <td class="text-right"><?= number_format($model['wht'], 2) ?></td>
                    <td class="text-right"><?= number_format($model['other'], 2) ?></td>
                    <td class="text-right"><?= number_format($model['total'], 2) ?></td>
                    <td class="text-right"><?= number_format($runningBalance, 2) ?></td>
                    <td class="text-center"><?= Html::encode($model['pcv_no']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-center"><strong>รวมรายจ่าย</strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['total_amount'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['total_vat_amount'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['total_vat_prohibit'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['total_wht'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['total_other'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($totalSummary['grand_total'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($runningBalance, 2) ?></strong></td>
                <td class="text-center"><strong>-</strong></td>
            </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-box" style="width: 20%;">
            <div class="summary-label">รายจ่ายรวม</div>
            <div class="summary-amount"><?= number_format($totalSummary['total_amount'], 2) ?></div>
        </div>
        <div class="summary-box" style="width: 20%;">
            <div class="summary-label">VAT รวม</div>
            <div class="summary-amount"><?= number_format($totalSummary['total_vat_amount'], 2) ?></div>
        </div>
        <div class="summary-box" style="width: 20%;">
            <div class="summary-label">VAT ต้องห้าม</div>
            <div class="summary-amount"><?= number_format($totalSummary['total_vat_prohibit'], 2) ?></div>
        </div>
        <div class="summary-box" style="width: 20%;">
            <div class="summary-label">หัก ณ ที่จ่าย</div>
            <div class="summary-amount"><?= number_format($totalSummary['total_wht'], 2) ?></div>
        </div>
        <div class="summary-box" style="width: 20%;">
            <div class="summary-label">คงเหลือ</div>
            <div class="summary-amount"><?= number_format($runningBalance, 2) ?></div>
        </div>
    </div>

    <div class="remaining-balance">
        <div>ยอดคงเหลือ: <?= number_format($runningBalance, 2) ?> บาท</div>
    </div>

    <!-- Form Code -->
    <div class="form-code">
        F-WP-FMA-004-003 Rev.N
    </div>
</div>