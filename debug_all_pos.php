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

$pos = \backend\models\Purch::find()->where(['like', 'purch_no', 'PO-00327'])->all();
echo "Found " . count($pos) . " POs matching PO-00327\n";
foreach ($pos as $po) {
    echo "PO ID: {$po->id}, No: {$po->purch_no}\n";
    echo " - Lines: " . count($po->purchLines) . "\n";
    foreach ($po->purchLines as $pl) {
        echo "   - PName: {$pl->product_name}, Note: {$pl->note}\n";
    }
}
