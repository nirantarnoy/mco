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
$rows = \backend\models\JournalTrans::find()->where(['journal_no' => 'AIN202607170001'])->asArray()->all();
print_r($rows);
$st = \backend\models\StockTrans::find()->where(['journal_trans_id' => $rows[0]['id']])->asArray()->all();
print_r($st);
