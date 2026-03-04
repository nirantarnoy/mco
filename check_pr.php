<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
$config = require 'common/config/main.php';
$configLocal = require 'common/config/main-local.php';
$config = array_merge($config, $configLocal);
new yii\console\Application($config);

$rows = (new \yii\db\Query())
    ->select(['id', 'purch_req_no', 'company_id'])
    ->from('purch_req')
    ->orderBy(['id' => SORT_DESC])
    ->limit(10)
    ->all();

foreach ($rows as $row) {
    echo "ID: " . $row['id'] . " | PR NO: " . $row['purch_req_no'] . " | Co ID: " . $row['company_id'] . "\n";
}
