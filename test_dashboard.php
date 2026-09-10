<?php
require 'vendor/autoload.php';
require 'common/config/bootstrap.php';
require 'backend/config/bootstrap.php';
$config = yii\helpers\ArrayHelper::merge(
    require 'common/config/main.php',
    require 'common/config/main-local.php',
    require 'backend/config/main.php',
    require 'backend/config/main-local.php'
);
(new yii\web\Application($config));

$fromDate = '2026-01-01';
$toDate = '2026-09-10';

echo "=== REVENUE ===\n";
$query = backend\models\Job::find()->where(['job.status' => [1, 2]]);
$query->andWhere([
    'or',
    ['between', 'job.job_date', $fromDate . ' 00:00:00', $toDate . ' 23:59:59'],
    ['and', ['job.job_date' => null], ['between', 'job.created_at', strtotime($fromDate), strtotime($toDate . ' 23:59:59')]]
]);

$revenue = 0;
foreach ($query->all() as $j) {
    $baseAmt = (float)($j->job_amount ?: ($j->quotation ? $j->quotation->total_amount : 0));
    $revenue += $baseAmt;
}
echo "Job Revenue: " . number_format($revenue, 2) . "\n";

echo "=== EXPENSES ===\n";
$cPoQuery = backend\models\Purch::find()
    ->where(['!=', 'purch.status', backend\models\Purch::STATUS_CANCELLED])
    ->andWhere(['or', ['purch.approve_status' => null], ['not in', 'purch.approve_status', [2, 3, 100]]])
    ->andWhere(['between', 'purch_date', $fromDate, $toDate]);
    
$cPo = 0;
foreach ($cPoQuery->all() as $po) {
    $netNoVat = (float)$po->net_amount > 0 ? ((float)$po->net_amount - (float)($po->vat_amount ?: 0)) : ((float)$po->total_amount - (float)($po->discount_total_amount ?: 0));
    $cPo += $netNoVat;
}
echo "PO Exp: " . number_format($cPo, 2) . "\n";

$cNonePr = (float)backend\models\PurchaseMaster::find()
    ->where(['!=', 'purchase_master.status', backend\models\PurchaseMaster::STATUS_CANCELLED])
    ->andWhere(['!=', 'purchase_master.status', 100])
    ->andWhere(['or', ['purchase_master.approve_status' => null], ['not in', 'purchase_master.approve_status', [2, 3, 100]]])
    ->andWhere(['between', 'docdat', $fromDate, $toDate])
    ->sum('total_amount - COALESCE(vat_amount, 0)');
echo "None-PR Exp: " . number_format($cNonePr, 2) . "\n";

$cSalary = (float)\backend\models\CompanySalary::find()
    ->where(['between', new \yii\db\Expression('(salary_year * 100 + salary_month)'), 202601, 202609])
    ->sum('amount');
echo "Salary: " . number_format($cSalary, 2) . "\n";

// Get raw POs with high amounts
echo "\nTop 5 POs:\n";
$pos = backend\models\Purch::find()->orderBy(['net_amount' => SORT_DESC])->limit(5)->all();
foreach($pos as $p) echo "PO: {$p->purch_no} | Net: {$p->net_amount} | VAT: {$p->vat_amount}\n";

// Get raw NonePRs with high amounts
echo "\nTop 5 None-PRs:\n";
$nprs = backend\models\PurchaseMaster::find()->orderBy(['total_amount' => SORT_DESC])->limit(5)->all();
foreach($nprs as $n) echo "None-PR: {$n->docnum} | Total: {$n->total_amount} | VAT: {$n->vat_amount}\n";
