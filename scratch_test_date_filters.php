<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
Yii::setAlias('@common', __DIR__ . '/common');
Yii::setAlias('@backend', __DIR__ . '/backend');
Yii::setAlias('@console', __DIR__ . '/console');

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/console/config/main.php'
);
$app = new yii\console\Application($config);

$fromDate = '2026-07-27';
$toDate = '2026-08-31';

$fromTs = strtotime($fromDate . ' 00:00:00');
$toTs = strtotime($toDate . ' 23:59:59');
$fromDateTime = $fromDate . ' 00:00:00';
$toDateTime = $toDate . ' 23:59:59';

echo "fromTs: $fromTs, toTs: $toTs\n";
echo "fromDateTime: $fromDateTime, toDateTime: $toDateTime\n\n";

// Invoices check
$invCount = \backend\models\Invoice::find()->count();
echo "Total Invoices in DB: $invCount\n";

$invWithDate = \backend\models\Invoice::find()
    ->where([
        'or',
        ['between', 'invoice_date', $fromDate, $toDate],
        ['between', 'created_at', $fromDateTime, $toDateTime]
    ])->sum('total_amount');
echo "Invoices Sum (with proper date filter): " . number_format((float)$invWithDate, 2) . "\n";

// Jobs check
$jobsSum = \backend\models\Job::find()->sum('job_amount');
echo "Total Jobs Sum in DB: " . number_format((float)$jobsSum, 2) . "\n";

// Quotations check
$quotsSum = \backend\models\Quotation::find()->sum('total_amount');
echo "Total Quotations Sum in DB: " . number_format((float)$quotsSum, 2) . "\n";

// POs check
$poSum = \backend\models\Purch::find()
    ->where([
        'or',
        ['between', 'created_at', $fromTs, $toTs],
        ['between', 'purch_date', $fromDateTime, $toDateTime]
    ])->sum('net_amount');
echo "POs Sum (with proper date filter): " . number_format((float)$poSum, 2) . "\n";

// None PR check
$nprSum = \backend\models\PurchaseMaster::find()
    ->where([
        'or',
        ['between', 'created_at', $fromTs, $toTs],
        ['between', 'docdat', $fromDate, $toDate]
    ])->sum('total_amount');
echo "None PR Sum (with proper date filter): " . number_format((float)$nprSum, 2) . "\n";

// Vehicle Expense check
$veKm = \backend\models\VehicleExpense::find()
    ->where([
        'or',
        ['between', 'expense_date', $fromDate, $toDate],
        ['between', 'created_at', $fromDateTime, $toDateTime]
    ])->sum('total_distance');
echo "Vehicle Total Distance (with proper date filter): " . number_format((float)$veKm, 2) . "\n";
