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
use backend\models\Invoice;
use backend\models\PettyCashVoucher;

$fromDate = '2026-08-19';
$toDate = '2026-08-21';
$companyId = '';

$fromTs = strtotime($fromDate . ' 00:00:00');
$toTs = strtotime($toDate . ' 23:59:59');
$fromDateTime = $fromDate . ' 00:00:00';
$toDateTime = $toDate . ' 23:59:59';

$expensesQuery = Purch::find()->where(['purch.approve_status' => 1]);
$nonePrQuery = PurchaseMaster::find()->where(['purchase_master.approve_status' => PurchaseMaster::APPROVE_STATUS_APPROVED]);
$pettyCashQuery = PettyCashVoucher::find()->where(['status' => 1]);

$expensesQuery->andWhere([
    'or',
    ['between', 'purch.purch_date', $fromDateTime, $toDateTime],
    ['and', ['purch.purch_date' => null], ['between', 'purch.created_at', $fromTs, $toTs]]
]);
$nonePrQuery->andWhere([
    'or',
    ['between', 'purchase_master.docdat', $fromDate, $toDate],
    ['and', ['purchase_master.docdat' => null], ['between', 'purchase_master.created_at', $fromTs, $toTs]]
]);
$pettyCashQuery->andWhere(['between', 'date', $fromDate, $toDate]);


$totalPoExpenses = (float)(clone $expensesQuery)->sum('net_amount') - (float)(clone $expensesQuery)->sum('vat_amount');
$totalNonePrExpenses = (float)(clone $nonePrQuery)->sum('total_amount') - (float)(clone $nonePrQuery)->sum('vat_amount');
$totalPettyCashExpenses = (float)(clone $pettyCashQuery)->sum('amount');
$totalExpenses = $totalPoExpenses + $totalNonePrExpenses + $totalPettyCashExpenses;

echo "PO Expenses: $totalPoExpenses\n";
echo "None PR Expenses: $totalNonePrExpenses\n";
echo "Petty Cash Expenses: $totalPettyCashExpenses\n";
echo "Total Expenses: $totalExpenses\n";
