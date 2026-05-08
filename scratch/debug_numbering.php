<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
$config = require('common/config/main.php');
$configLocal = require('common/config/main-local.php');
$config = yii\helpers\ArrayHelper::merge($config, $configLocal);
new yii\web\Application($config);

$company_id = 1;

$maxPr = Yii::$app->db->createCommand("
    SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED)) 
    FROM purch_req 
    WHERE company_id = " . (int)$company_id . "
    AND purch_req_no LIKE 'PR-%'
    AND CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) < 100000
")->queryScalar();

$maxPo = Yii::$app->db->createCommand("
    SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_no, '-', 2), '-', -1) AS UNSIGNED)) 
    FROM purch 
    WHERE company_id = " . (int)$company_id . "
    AND purch_no LIKE 'PO-%'
    AND CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_no, '-', 2), '-', -1) AS UNSIGNED) < 100000
")->queryScalar();

echo "Company ID: $company_id\n";
echo "Max PR Number: $maxPr\n";
echo "Max PO Number: $maxPo\n";

$company_id = 2; // Check other company just in case
$maxPr2 = Yii::$app->db->createCommand("
    SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED)) 
    FROM purch_req 
    WHERE company_id = " . (int)$company_id . "
    AND purch_req_no LIKE 'PR-%'
")->queryScalar();
echo "Company ID 2 Max PR: $maxPr2\n";

// Check without company filter
$maxPrGlobal = Yii::$app->db->createCommand("
    SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED)) 
    FROM purch_req 
    WHERE purch_req_no LIKE 'PR-%'
")->queryScalar();
echo "Global Max PR: $maxPrGlobal\n";

$maxPoGlobal = Yii::$app->db->createCommand("
    SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_no, '-', 2), '-', -1) AS UNSIGNED)) 
    FROM purch 
    WHERE purch_no LIKE 'PO-%'
")->queryScalar();
echo "Global Max PO: $maxPoGlobal\n";
