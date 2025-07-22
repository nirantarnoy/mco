<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchParams array */

$this->title = 'รายงานใบงาน';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        body {
            font-family: 'TH SarabunPSK', sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .report-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-date {
            font-size: 14px;
            color: #666;
        }

        .search-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }

        .search-info h4 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }

        .search-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
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
            margin-top: 30px;
            border: 2px solid #000;
            padding: 15px;
        }

        .summary-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #ccc;
        }

        .summary-label {
            font-weight: bold;
        }

        .summary-value {
            text-align: right;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="company-name">MCO GROUP</div>
    <div class="report-title"><?= Html::encode($this->title) ?></div>
    <div class="report-date">วันที่พิมพ์: <?= date('d/m/Y H:i:s') ?></div>
</div>

<!-- ข้อมูลการค้นหา -->
<div class="search-info">
    <h4>เงื่อนไขการค้นหา:</h4>
    <?php if (!empty($searchParams['JobReportSearch']['job_no'])): ?>
        <span class="search-item"><strong>เลขใบงาน:</strong> <?= Html::encode($searchParams['JobReportSearch']['job_no']) ?></span>
    <?php endif; ?>

    <?php if (!empty($searchParams['JobReportSearch']['status'])): ?>
        <span class="search-item"><strong>สถานะ:</strong> <?= Html::encode($searchParams['JobReportSearch']['status']) ?></span>
    <?php endif; ?>

    <?php if (!empty($searchParams['JobReportSearch']['start_date_from'])): ?>
        <span class="search-item"><strong>วันที่เริ่ม:</strong> <?= Html::encode($searchParams['JobReportSearch']['start_date_from']) ?></span>
    <?php endif; ?>

    <?php if (!empty($searchParams['JobReportSearch']['start_date_to'])): ?>
        <span class="search-item"><strong>ถึงวันที่:</strong> <?= Html::encode($searchParams['JobReportSearch']['start_date_to']) ?></span>
    <?php endif; ?>

    <?php if (empty($searchParams) || empty(array_filter($searchParams))): ?>
        <span class="search-item">แสดงข้อมูลทั้งหมด</span>
    <?php endif; ?>
</div>

<!-- ตารางข้อมูล -->
<table class="data-table">
    <thead>
    <tr>
        <th style="width: 5%;">ลำดับ</th>
        <th style="width: 15%;">เลขใบงาน</th>
        <th style="width: 12%;">วันที่เริ่ม</th>
        <th style="width: 10%;">สถานะ</th>
        <th style="width: 15%;">มูลค่างาน (บาท)</th>
        <th style="width: 15%;">มูลค่าเบิกของ (บาท)</th>
        <th style="width: 15%;">กำไร/ขาดทุน (บาท)</th>
        <th style="width: 13%;">เปอร์เซ็นต์ (%)</th>
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
            <td><?= Yii::$app->formatter->asDate($model->start_date) ?></td>
            <td><?= Html::encode($model->getStatusText()) ?></td>
            <td class="text-right"><?= number_format($model->job_amount, 2) ?></td>
            <td class="text-right"><?= number_format($withdrawAmount, 2) ?></td>
            <td class="text-right <?= $profitLoss >= 0 ? 'profit' : 'loss' ?>">
                <?= number_format($profitLoss, 2) ?>
            </td>
            <td class="text-right <?= $percentage >= 0 ? 'profit' : 'loss' ?>">
                <?= number_format($percentage, 2) ?>%
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

    <div class="summary-row">
        <span class="summary-label">จำนวนใบงานทั้งหมด:</span>
        <span class="summary-value"><?= number_format(count($models)) ?> ใบ</span>
    </div>

    <div class="summary-row">
        <span class="summary-label">มูลค่างานรวม:</span>
        <span class="summary-value"><?= number_format($totalJobAmount, 2) ?> บาท</span>
    </div>

    <div class="summary-row">
        <span class="summary-label">มูลค่าเบิกของรวม:</span>
        <span class="summary-value"><?= number_format($totalWithdrawAmount, 2) ?> บาท</span>
    </div>

    <div class="summary-row">
        <span class="summary-label">กำไร/ขาดทุนรวม:</span>
        <span class="summary-value <?= $totalProfitLoss >= 0 ? 'profit' : 'loss' ?>">
            <?= number_format($totalProfitLoss, 2) ?> บาท
        </span>
    </div>

    <div class="summary-row">
        <span class="summary-label">เปอร์เซ็นต์รวม:</span>
        <span class="summary-value <?= $totalPercentage >= 0 ? 'profit' : 'loss' ?>">
            <?= number_format($totalPercentage, 2) ?>%
        </span>
    </div>
</div>

<div class="footer">
    <p>รายงานนี้ถูกสร้างโดยระบบอัตโนมัติ เมื่อวันที่ <?= date('d/m/Y H:i:s') ?></p>
</div>

<script>
    // Auto print เมื่อโหลดหน้าเสร็จ
    window.onload = function() {
        window.print();
    };
</script>

</body>
</html>