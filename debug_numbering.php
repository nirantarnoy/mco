<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';
require __DIR__ . '/console/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/console/config/main.php',
    require __DIR__ . '/console/config/main-local.php'
);

(new yii\console\Application($config));

$results = Yii::$app->db->createCommand("
    SELECT id, purch_req_no, company_id 
    FROM purch_req 
    ORDER BY id DESC 
    LIMIT 20
")->queryAll();

echo "ID | PR No | Company ID\n";
echo str_repeat("-", 40) . "\n";
foreach ($results as $row) {
    echo $row['id'] . " | " . $row['purch_req_no'] . " | " . $row['company_id'] . "\n";
}

$company_id = 1; // Testing for company 1
$maxNum = Yii::$app->db->createCommand("
    SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED)) 
    FROM purch_req 
    WHERE company_id = :company_id
", [':company_id' => $company_id])->queryScalar();

echo "\nMax Number for Company ID $company_id: " . ($maxNum ?: 'Not found') . "\n";
