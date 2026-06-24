<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/common/env.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';
require __DIR__ . '/console/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/console/config/main.php',
    require __DIR__ . '/console/config/main-local.php'
);

$application = new yii\console\Application($config);

use backend\models\InvoicePaymentHistory;

$histories = InvoicePaymentHistory::find()->orderBy(['id' => SORT_DESC])->limit(5)->asArray()->all();
print_r($histories);
