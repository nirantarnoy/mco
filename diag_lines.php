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

$lines = (new \yii\db\Query())
    ->select(['l.*', 't.journal_no', 't.trans_type_id', 't.status'])
    ->from('journal_trans_line l')
    ->join('INNER JOIN', 'journal_trans t', 't.id = l.journal_trans_id')
    ->where(['t.id' => [126, 129, 131, 132, 69]])
    ->all();

echo "Lines for Approved Withdrawal Transactions:\n";
foreach ($lines as $l) {
    echo "TransID: {$l['journal_trans_id']}, No: {$l['journal_no']}, Product: {$l['product_id']}, Qty: {$l['qty']}, Status: {$l['status']}\n";
}
