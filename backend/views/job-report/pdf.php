<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchParams array */

?>

<style>
    body {
        font-family: 'TH SarabunPSK', sans-serif;
        font-size: 12px;
        margin: 0;
        padding: 15px;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }

    .company-name {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .report-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .report-date {
        font-size: 12px;
        color: #666;
    }

    .search-info {
        margin-bottom: 15px;
        padding: 8px;
        background-color: #f8f8f8;
        border: 1px solid #ccc;
        font-size: 11px;
    }

    .search-info h4 {
        margin: 0 0 8px 0;
        font-size: 14px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
        font-size: 10px;
    }

    .data-table th,
    .data-table td {
        border: 0.5px solid #000;
        padding: 4px;
        text-align: center;
    }

    .data-table th {
        background-color: #e8e8e8;
        font-weight: bold;
        font-size: 10px;
    }

    .text-right {
        text-align: right !important;
    }

    .text-left {
        text-align: left !important;
    }

    .profit {
        color: #28a745;
        font-weight: bold;
    }

    .loss {
        color: #dc3545;
        font-weight: bold;
    }

    .summary {
        margin-top: 20px;
        border: 1px solid #000;
        padding: 10px;
        background-color: #f9f9f9;
    }

    .summary-title {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 10px;
        text-align: center;
    }

    .summary-table {
        width: 100%;
        font-size: 11px;
    }

    .summary-table td {
        padding: 3px 8px;
        border-bottom: 1px solid #ddd;
    }

    .summary-label {
        font-weight: bold;
        width: 60%;
    }

    .summary-value {
        text-align: right;
        width: 40%;
    }

    .footer {
        margin-top: 20px;
        text-align: center;
        font-size: 9px;
        color: #666;
        border-top: 1px solid #ccc;
        padding-top: 10px;
    }
</style>

<div class="header">
    <div class="company-name">MCO GROUP</div>
    <div class="report-title">รายงานใบงาน</div>
    <div class="report-date">วันที่พิมพ์: <?= date('d/m/Y H:i:s') ?></div>
</div>

<!-- ข้อมูลการค้นหา -->
<div class="search-info">
    <h4>เงื่อนไขการค้นหา:</h4>
    <?php
    $searchItems = [];

    if (!empty($searchParams['JobReportSearch']['job_no'])) {
        $searchItems[] = "เลขใบงาน: " . Html::encode($searchParams['JobReportSearch']['job_no']);
    }

    if (!empty($searchParams['JobReportSearch']['status'])) {
        $searchItems[] = "สถานะ: " . Html::encode($searchParams['JobReportSearch']['status']);
    }

    if (!empty($searchParams['JobReportSearch']['start_date_from'])) {
        $searchItems[] = "วันที่เริ่ม: " . Html::encode($searchParams['JobReportSearch']['start_date_from']);
    }

    if (!empty($searchParams['JobReportSearch']['start_date_to'])) {
        $searchItems[] = "ถึงวันที่: " . Html::encode($searchParams['JobReportSearch']['start_date_to']);
    }

    if (empty($searchItems)) {
        echo "แสดงข้อมูลทั้งหมด";
    } else {
        echo implode(", ", $searchItems);
    }
    ?>
</div>

<!-- ตารางข้อมูล -->
<table class="data-table">
    <thead>
    <tr>
        <th style="width: 5%;">ลำดับ</th>
        <th style="width: 18%;">เลขใบงาน</th>
        <th style="width: 12%;">วันที่เริ่ม</th>
        <th style="width: 12%;">สถานะ</th>
        <th style="width: 15%;">มูลค่างาน</th>
        <th style="width: 15%;">มูลค่าเบิกของ</th>
        <th style="width: 15%;">กำไร/ขาดทุน</th>
        <th style="width: 8%;">%</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $models = $dataProvider->getModels();
    $totalJobAmount = 0;
    $totalWithdrawAmount = 0;
    $no = 1;

    foreach ($models as $model):
        $withdrawAmount = $model->getTotalWithdrawAmount();
        $profitLoss = $model->getProfitLoss();
        $percentage = $model->getProfitLossPercentage();

        $totalJobAmount += $model->job_amount;
        $totalWithdrawAmount += $withdrawAmount;
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td class="text-left"><?= Html::encode($model->job_no) ?></td>
            <td><?= date('d/m/Y', strtotime($model->start_date)) ?></td>
            <td><?= Html::encode($model->getStatusText()) ?></td>
            <td class="text-right"><?= number_format($model->job_amount, 2) ?></td>
            <td class="text-right"><?= number_format($withdrawAmount, 2) ?></td>
            <td class="text-right <?= $profitLoss >= 0 ? 'profit' : 'loss' ?>">
                <?= number_format($profitLoss, 2) ?>
            </td>
            <td class="text-right <?= $percentage >= 0 ? 'profit' : 'loss' ?>">
                <?= number_format($percentage, 1) ?>%
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- สรุปยอดรวม -->
<?php
$totalProfitLoss = $totalJobAmount - $totalWithdrawAmount;
$totalPercentage = $totalJobAmount > 0 ? ($totalProfitLoss / $totalJobAmount) * 100 : 0;
?>

<div class="summary">
    <div class="summary-title">สรุปข้อมูลรวม</div>

    <table class="summary-table">
        <tr>
            <td class="summary-label">จำนวนใบงานทั้งหมด:</td>
            <td class="summary-value"><?= number_format(count($models)) ?> ใบ</td>
        </tr>
        <tr>
            <td class="summary-label">มูลค่างานรวม:</td>
            <td class="summary-value"><?= number_format($totalJobAmount, 2) ?> บาท</td>
        </tr>
        <tr>
            <td class="summary-label">มูลค่าเบิกของรวม:</td>
            <td class="summary-value"><?= number_format($totalWithdrawAmount, 2) ?> บาท</td>
        </tr>
        <tr>
            <td class="summary-label">กำไร/ขาดทุนรวม:</td>
            <td class="summary-value <?= $totalProfitLoss >= 0 ? 'profit' : 'loss' ?>">
                <?= number_format($totalProfitLoss, 2) ?> บาท
            </td>
        </tr>
        <tr>
            <td class="summary-label">เปอร์เซ็นต์รวม:</td>
            <td class="summary-value <?= $totalPercentage >= 0 ? 'profit' : 'loss' ?>">
                <?= number_format($totalPercentage, 2) ?>%
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    <p>รายงานนี้ถูกสร้างโดยระบบอัตโนมัติ เมื่อวันที่ <?= date('d/m/Y H:i:s') ?></p>
</div>