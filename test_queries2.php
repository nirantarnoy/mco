<?php
require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/common/config/bootstrap.php');
require(__DIR__ . '/backend/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/common/config/main.php'),
    require(__DIR__ . '/common/config/main-local.php'),
    require(__DIR__ . '/backend/config/main.php'),
    require(__DIR__ . '/backend/config/main-local.php')
);

$application = new yii\web\Application($config);

use backend\models\Job;
use backend\models\Purch;
use backend\models\PurchaseMaster;
use backend\models\PettyCashVoucher;

$expensesQuery = Purch::find()->where(['purch.approve_status' => 1]);
$nonePrQuery = PurchaseMaster::find()->where(['purchase_master.approve_status' => PurchaseMaster::APPROVE_STATUS_APPROVED]);
$pettyCashQuery = PettyCashVoucher::find()->where(['status' => 1]);

$totalPoExpenses = (float)(clone $expensesQuery)->sum('net_amount') - (float)(clone $expensesQuery)->sum('vat_amount');
$totalNonePrExpenses = (float)(clone $nonePrQuery)->sum('total_amount') - (float)(clone $nonePrQuery)->sum('vat_amount');
$totalPettyCashExpenses = (float)(clone $pettyCashQuery)->sum('amount');
$totalExpenses = $totalPoExpenses + $totalNonePrExpenses + $totalPettyCashExpenses;

echo "All Time Expenses: $totalExpenses\n";

$jobsWithPoQuery = Job::find()->where(['job.status' => [1, 2]])
    ->andWhere(['not', ['job.cus_po_doc' => null]])
    ->andWhere(['!=', 'job.cus_po_doc', '']);
$totalRevenue = 0;
foreach ($jobsWithPoQuery->all() as $j) {
    $totalRevenue += $j->getJobAmountNoVat();
}
echo "All Time Revenue: $totalRevenue\n";
