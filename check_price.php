<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';
require __DIR__ . '/backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/backend/config/main.php',
    require __DIR__ . '/backend/config/main-local.php'
);

(new yii\web\Application($config));

$rows = \Yii::$app->db->createCommand("
    SELECT id, code, name, cost_price, sale_price 
    FROM product
    WHERE code LIKE '%WOEL00001%' OR name LIKE '%RCBO Combined%'
")->queryAll();
print_r($rows);
