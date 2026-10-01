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
$models = \backend\models\PurchaseMaster::find()->where(['in', 'docnum', ['NPR202608280004', 'NPR202608280005', 'NPR202609050001', 'NPR202609050002']])->all();
foreach($models as $m) {
    echo $m->docnum . ' job: ' . $m->job_no . ' ref: ' . $m->refnum . PHP_EOL;
}
