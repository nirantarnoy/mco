<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
$config = require('common/config/main.php');
if (file_exists('common/config/main-local.php')) {
    $config = yii\helpers\ArrayHelper::merge($config, require('common/config/main-local.php'));
}
new yii\web\Application($config);

$records = \backend\models\PettyCashVoucher::find()->orderBy(['id' => SORT_DESC])->limit(5)->all();
foreach ($records as $record) {
    echo $record->pcv_no . "\n";
}
