<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);

(new yii\web\Application($config));

$fromDate = '2026-08-19';
$toDate = '2026-08-21';
$fromTs = strtotime($fromDate . ' 00:00:00');
$toTs = strtotime($toDate . ' 23:59:59');

$jobsWithPoQuery = \backend\models\Job::find()
    ->where(['job.status' => [1, 2]])
    ->andWhere(['!=', 'job.cus_po_doc', '']);

$jobsWithPoQuery->andWhere([
    'or',
    ['between', 'job.job_date', $fromDate . ' 00:00:00', $toDate . ' 23:59:59'],
    ['and', ['job.job_date' => null], ['between', 'job.created_at', $fromTs, $toTs]]
]);

$jobs = $jobsWithPoQuery->all();
echo "Jobs found: " . count($jobs) . "\n";
$totalRevenue = 0;
foreach($jobs as $j) {
    echo "Job ID: " . $j->id . " Amount: " . $j->getJobAmountNoVat() . "\n";
    $totalRevenue += $j->getJobAmountNoVat();
}
echo "Total Revenue: " . $totalRevenue . "\n";

$receiptQuery = \backend\models\Invoice::find()
    ->where(['invoices.status' => 1])
    ->andWhere(['invoices.invoice_type' => ['receipt', 4]]);

$receiptQuery->andWhere([
    'or',
    ['between', 'invoices.invoice_date', $fromDate, $toDate],
    ['and', ['invoices.invoice_date' => null], ['between', 'invoices.created_at', $fromDate . ' 00:00:00', $toDate . ' 23:59:59']]
]);

$receipts = $receiptQuery->all();
echo "Receipts found: " . count($receipts) . "\n";
