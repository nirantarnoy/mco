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

$q = \backend\models\Quotation::findOne(128);
echo 'QT ID 128: ' . ($q ? $q->quotation_no : 'Not found') . PHP_EOL;

$j = \backend\models\Job::findOne(128);
echo 'Job ID 128: ' . ($j ? $j->job_no . ' QT: ' . ($j->quotation ? $j->quotation->quotation_no : 'no qt') : 'Not found') . PHP_EOL;

$pm = \backend\models\PurchaseMaster::findOne(['docnum' => 'NPR202608280004']);
echo 'NPR202608280004 job_no: ' . ($pm ? $pm->job_no : 'Not found') . PHP_EOL;
echo 'NPR202608280004 refnum: ' . ($pm ? $pm->refnum : 'Not found') . PHP_EOL;
