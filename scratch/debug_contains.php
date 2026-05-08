<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
$config = require('common/config/main.php');
$configLocal = require('common/config/main-local.php');
$config = yii\helpers\ArrayHelper::merge($config, $configLocal);
$config['id'] = 'debug-app';
$config['basePath'] = dirname(__DIR__);
new yii\web\Application($config);

$rows = Yii::$app->db->createCommand("
    SELECT purch_req_no as no FROM purch_req WHERE purch_req_no LIKE '%492%'
    UNION
    SELECT purch_no as no FROM purch WHERE purch_no LIKE '%492%'
")->queryAll();

echo "Records containing '492':\n";
foreach ($rows as $row) {
    echo "- {$row['no']}\n";
}

$rows2 = Yii::$app->db->createCommand("
    SELECT purch_req_no as no FROM purch_req WHERE purch_req_no LIKE '%493%'
    UNION
    SELECT purch_no as no FROM purch WHERE purch_no LIKE '%493%'
")->queryAll();

echo "\nRecords containing '493':\n";
foreach ($rows2 as $row) {
    echo "- {$row['no']}\n";
}
