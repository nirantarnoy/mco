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

$mainPo = 'PO-00327';
$pm = \backend\models\Purch::find()->where(['purch_no' => $mainPo])->one();
if ($pm) {
    echo "Main PO $mainPo Found!\n";
    echo "Lines: " . count($pm->purchLines) . "\n";
    foreach ($pm->purchLines as $pl) {
        echo " - " . $pl->product_name . "\n";
    }
} else {
    echo "Main PO $mainPo NOT FOUND.\n";
}
