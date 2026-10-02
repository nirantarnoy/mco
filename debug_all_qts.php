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
(new yii\web\Application($config));

$qts = \backend\models\Quotation::find()->where(['like', 'quotation_no', 'QT26-000084'])->all();
echo "Found " . count($qts) . " QTs matching QT26-000084\n";
foreach ($qts as $qt) {
    echo "QT ID: {$qt->id}, No: {$qt->quotation_no}\n";
    echo " - Lines: " . count($qt->quotationLines) . "\n";
    foreach ($qt->quotationLines as $ql) {
        echo "   - PName: {$ql->product_name}, Note: {$ql->note}\n";
    }
}
