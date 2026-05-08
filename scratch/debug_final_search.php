<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
$config = require('common/config/main.php');
$configLocal = require('common/config/main-local.php');
$config = yii\helpers\ArrayHelper::merge($config, $configLocal);
$config['id'] = 'debug-app';
$config['basePath'] = dirname(__DIR__);
new yii\web\Application($config);

$rows = (new \yii\db\Query())
    ->select(['purch_no', 'company_id'])
    ->from('purch')
    ->where(['like', 'purch_no', '492'])
    ->all();

echo "Purch records containing '492':\n";
foreach ($rows as $row) {
    echo "- No: {$row['purch_no']}, Company: {$row['company_id']}\n";
}

$rows2 = (new \yii\db\Query())
    ->select(['purch_req_no', 'company_id'])
    ->from('purch_req')
    ->where(['like', 'purch_req_no', '492'])
    ->all();

echo "\nPurchReq records containing '492':\n";
foreach ($rows2 as $row) {
    echo "- No: {$row['purch_req_no']}, Company: {$row['company_id']}\n";
}
