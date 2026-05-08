<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
$config = require('common/config/main.php');
$configLocal = require('common/config/main-local.php');
$config = yii\helpers\ArrayHelper::merge($config, $configLocal);
// Add ID to avoid error
$config['id'] = 'debug-app';
new yii\web\Application($config);

$rows = (new \yii\db\Query())
    ->select(['purch_no', 'purch_date', 'company_id'])
    ->from('purch')
    ->where(['like', 'purch_date', '2025', false])
    ->limit(10)
    ->all();

echo "Sample POs from 2025:\n";
foreach ($rows as $row) {
    echo "No: {$row['purch_no']}, Date: {$row['purch_date']}, Company: {$row['company_id']}\n";
}

$rows2 = (new \yii\db\Query())
    ->select(['purch_no', 'purch_date', 'company_id'])
    ->from('purch')
    ->where(['like', 'purch_date', '2026', false])
    ->orderBy(['purch_date' => SORT_DESC])
    ->limit(10)
    ->all();

echo "\nSample POs from 2026:\n";
foreach ($rows2 as $row) {
    echo "No: {$row['purch_no']}, Date: {$row['purch_date']}, Company: {$row['company_id']}\n";
}
