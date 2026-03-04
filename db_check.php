<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';
require __DIR__ . '/console/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/console/config/main.php',
    require __DIR__ . '/console/config/main-local.php'
);

(new yii\console\Application($config));

echo "Columns in purch_req:\n";
$columns = Yii::$app->db->getTableSchema('purch_req')->columnNames;
print_r($columns);

echo "\nTop 10 PRs:\n";
$rows = Yii::$app->db->createCommand("SELECT id, purch_req_no, company_id FROM purch_req ORDER BY id DESC LIMIT 10")->queryAll();
print_r($rows);
