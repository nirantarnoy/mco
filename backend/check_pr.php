<?php
$config = require(__DIR__ . '/config/main-local.php');
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/config/main.php'),
    require(__DIR__ . '/config/main-local.php')
);

(new yii\web\Application($config));

$models = \backend\models\PurchReq::find()->orderBy(['id' => SORT_DESC])->limit(20)->all();
echo "ID | PR No | Job ID | Created At\n";
echo str_repeat("-", 50) . "\n";
foreach ($models as $m) {
    echo $m->id . " | " . $m->purch_req_no . " | " . $m->job_id . " | " . date('Y-m-d H:i:s', $m->created_at) . "\n";
}
