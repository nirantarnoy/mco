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

$resPr = Yii::$app->db->createCommand("
    SELECT purch_req_no, company_id, CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) as num
    FROM purch_req 
    WHERE CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) >= 400
    ORDER BY num DESC
    LIMIT 5
")->queryAll();

echo "High PR numbers:\n";
foreach ($resPr as $row) {
    echo "No: {$row['purch_req_no']}, Company: {$row['company_id']}, Num: {$row['num']}\n";
}

$resPo = Yii::$app->db->createCommand("
    SELECT purch_no, company_id, CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_no, '-', 2), '-', -1) AS UNSIGNED) as num
    FROM purch 
    WHERE CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_no, '-', 2), '-', -1) AS UNSIGNED) >= 400
    ORDER BY num DESC
    LIMIT 5
")->queryAll();

echo "\nHigh PO numbers:\n";
foreach ($resPo as $row) {
    echo "No: {$row['purch_no']}, Company: {$row['company_id']}, Num: {$row['num']}\n";
}

$resPrLow = Yii::$app->db->createCommand("
    SELECT purch_req_no, company_id, CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) as num
    FROM purch_req 
    WHERE (company_id = $company_id OR company_id IS NULL OR company_id = 0)
    ORDER BY num DESC
    LIMIT 5
")->queryAll();

echo "\nLatest PR numbers for Company $company_id:\n";
foreach ($resPrLow as $row) {
    echo "No: {$row['purch_req_no']}, Company: {$row['company_id']}, Num: {$row['num']}\n";
}
