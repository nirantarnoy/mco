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

$counts = (new \yii\db\Query())
    ->select(['trans_type_id', 'status', 'COUNT(*) as count'])
    ->from('journal_trans')
    ->groupBy(['trans_type_id', 'status'])
    ->all();

echo "Transaction Type and Status counts:\n";
foreach ($counts as $row) {
    echo "Type: {$row['trans_type_id']}, Status: {$row['status']}, Count: {$row['count']}\n";
}
