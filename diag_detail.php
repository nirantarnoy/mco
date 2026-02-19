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

$withdrawTransactions = (new \yii\db\Query())
    ->select(['id', 'journal_no', 'status', 'job_id', 'trans_date'])
    ->from('journal_trans')
    ->where(['trans_type_id' => 3])
    ->all();

echo "Withdraw Transactions (Type 3):\n";
foreach ($withdrawTransactions as $t) {
    $lineCount = (new \yii\db\Query())
        ->from('journal_trans_line')
        ->where(['journal_trans_id' => $t['id']])
        ->count();
    echo "ID: {$t['id']}, No: {$t['journal_no']}, Status: {$t['status']}, JobID: " . ($t['job_id'] ?? 'NULL') . ", Lines: {$lineCount}\n";
}

$reportQuery = (new \yii\db\Query())
    ->select([
        't.status',
        't.job_id',
        'COUNT(*) as count',
        'SUM(l.qty) as total_qty'
    ])
    ->from('journal_trans_line l')
    ->join('INNER JOIN', 'journal_trans t', 't.id = l.journal_trans_id')
    ->where(['t.trans_type_id' => 3])
    ->groupBy(['t.status', 't.job_id'])
    ->all();

echo "\nReport Query Breakdown for Type 3:\n";
foreach ($reportQuery as $row) {
    echo "Status: {$row['status']}, JobID: " . ($row['job_id'] ?? 'NULL') . ", Records: {$row['count']}, TotalQty: {$row['total_qty']}\n";
}
