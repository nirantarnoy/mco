<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
$config = require('common/config/main.php');
$configLocal = require('common/config/main-local.php');
$config = yii\helpers\ArrayHelper::merge($config, $configLocal);
$config['id'] = 'debug-app';
$config['basePath'] = dirname(__DIR__);
new yii\web\Application($config);

$company_id = 1;

$res = Yii::$app->db->createCommand("
    SELECT purch_req_no as no, 'PR' as type
    FROM purch_req 
    WHERE company_id = $company_id
    AND CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) BETWEEN 200 AND 600
    UNION
    SELECT purch_no as no, 'PO' as type
    FROM purch 
    WHERE company_id = $company_id
    AND CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_no, '-', 2), '-', -1) AS UNSIGNED) BETWEEN 200 AND 600
")->queryAll();

echo "Records for Company $company_id with numbers 200-600:\n";
foreach ($res as $row) {
    echo "Type: {$row['type']}, No: {$row['no']}\n";
}
