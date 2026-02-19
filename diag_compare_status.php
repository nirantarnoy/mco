<?php
define('YII_DEBUG', true);
define('YII_ENV', 'dev');
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

(new yii\web\Application($config));

$query = \backend\models\JournalTransLine::find()
    ->select([
        'MAX(journal_trans_line.id) AS id',
        't.job_id AS job_id',
        'journal_trans_line.product_id',
        'SUM(CASE WHEN t.trans_type_id = 3 THEN journal_trans_line.qty ELSE 0 END) as total_withdraw',
        'SUM(CASE WHEN t.trans_type_id = 4 THEN journal_trans_line.qty ELSE 0 END) as total_return_withdraw',
        'SUM(CASE WHEN t.trans_type_id = 5 THEN journal_trans_line.qty ELSE 0 END) as total_borrow',
        'SUM(CASE WHEN t.trans_type_id = 6 THEN journal_trans_line.qty ELSE 0 END) as total_return_borrow',
    ])
    ->joinWith('journalTrans t')
    ->where(['IN', 't.status', [1, 2]]) // The suspect filter
    ->groupBy(['t.job_id', 'journal_trans_line.product_id'])
    ->asArray();

$results = $query->all();

echo "Report Query Results (Status 1, 2):\n";
echo str_pad("Job ID", 10) . " | " . str_pad("Prod ID", 10) . " | " . str_pad("Withdraw", 10) . " | " . str_pad("Borrow", 10) . "\n";
echo str_repeat("-", 50) . "\n";
foreach ($results as $row) {
    echo str_pad($row['job_id'] ?? 'NULL', 10) . " | " . 
         str_pad($row['product_id'], 10) . " | " . 
         str_pad($row['total_withdraw'], 10) . " | " . 
         str_pad($row['total_borrow'], 10) . "\n";
}

$queryWithDraft = \backend\models\JournalTransLine::find()
    ->select([
        't.job_id AS job_id',
        'SUM(CASE WHEN t.trans_type_id = 3 THEN journal_trans_line.qty ELSE 0 END) as total_withdraw',
    ])
    ->joinWith('journalTrans t')
    ->where(['IN', 't.status', [0, 1, 2]]) // Including Draft
    ->groupBy(['t.job_id'])
    ->asArray();

$resultsWithDraft = $queryWithDraft->all();

echo "\nSummary Including Draft (Status 0, 1, 2) Grouped by Job:\n";
foreach ($resultsWithDraft as $row) {
    echo "Job ID: " . ($row['job_id'] ?? 'NULL') . " | Total Withdraw: " . $row['total_withdraw'] . "\n";
}
