<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
$config = require('common/config/main.php');
$configLocal = require('common/config/main-local.php');
$config = yii\helpers\ArrayHelper::merge($config, $configLocal);
$config['id'] = 'debug-app';
$config['basePath'] = dirname(__DIR__);
new yii\web\Application($config);

$res = Yii::$app->db->createCommand("
    SELECT purch_req_no as no, company_id, 'PR' as type
    FROM purch_req 
    WHERE CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) = 492
    UNION
    SELECT purch_no as no, company_id, 'PO' as type
    FROM purch 
    WHERE CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_no, '-', 2), '-', -1) AS UNSIGNED) = 492
")->queryAll();

echo "Records with number 492:\n";
foreach ($res as $row) {
    echo "Type: {$row['type']}, No: {$row['no']}, Company: {$row['company_id']}\n";
}

$res2 = Yii::$app->db->createCommand("
    SELECT purch_req_no as no, company_id, 'PR' as type
    FROM purch_req 
    WHERE CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) = 192
    UNION
    SELECT purch_no as no, company_id, 'PO' as type
    FROM purch 
    WHERE CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_no, '-', 2), '-', -1) AS UNSIGNED) = 192
")->queryAll();

echo "\nRecords with number 192:\n";
foreach ($res2 as $row) {
    echo "Type: {$row['type']}, No: {$row['no']}, Company: {$row['company_id']}\n";
}
