<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
require 'common/config/bootstrap.php';
require 'backend/config/bootstrap.php';
$config = yii\helpers\ArrayHelper::merge(
    require 'common/config/main.php',
    require 'common/config/main-local.php',
    require 'backend/config/main.php',
    require 'backend/config/main-local.php'
);
$app = new yii\web\Application($config);

$vendor_exact = \common\models\Vendor::find()
    ->select(['code', 'COUNT(*) as cnt'])
    ->groupBy(['code'])
    ->having(['>', 'cnt', 1])
    ->asArray()
    ->all();
echo "Vendor exact code duplicates:\n";
print_r($vendor_exact);


