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
$qt = \backend\models\Quotation::find()->where(['quotation_no' => 'RY-QT26-000084'])->one();
if ($qt) {
    echo 'Found QT. Lines: '.count($qt->quotationLines) . "\n";
    foreach ($qt->quotationLines as $ql) {
        echo ' PName: '.$ql->product_name.' Note: '.$ql->note . "\n";
    }
} else {
    echo 'No QT';
}
