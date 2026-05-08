<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
$config = require('common/config/main.php');
$configLocal = require('common/config/main-local.php');
$config = yii\helpers\ArrayHelper::merge($config, $configLocal);
$config['id'] = 'debug-app';
$config['basePath'] = dirname(__DIR__);
new yii\web\Application($config);

$tables = ['purch', 'purch_req'];
foreach ($tables as $table) {
    $col = ($table == 'purch') ? 'purch_no' : 'purch_req_no';
    $rows = (new \yii\db\Query())
        ->select([$col, 'company_id', 'id'])
        ->from($table)
        ->all();
    
    echo "Scanning $table...\n";
    foreach ($rows as $row) {
        $val = $row[$col];
        $parts = explode('-', $val);
        if (count($parts) >= 2) {
            $numPart = $parts[1];
            if (is_numeric($numPart) && intval($numPart) >= 400) {
                echo "Found high number: $val (Company: {$row['company_id']}, ID: {$row['id']})\n";
            }
        }
    }
}
