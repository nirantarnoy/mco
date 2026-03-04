<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
defined('YII_ENV_TEST') or define('YII_ENV_TEST', false);

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/config/main.php'),
    require(__DIR__ . '/config/main-local.php')
);

(new yii\web\Application($config));

$results = Yii::$app->db->createCommand("
    SELECT id, purch_req_no, company_id, created_at 
    FROM purch_req 
    ORDER BY CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) DESC 
    LIMIT 100
")->queryAll();

foreach ($results as $row) {
    echo "ID: {$row['id']} | NO: {$row['purch_req_no']} | CO: {$row['company_id']} | Date: " . date('Y-m-d H:i:s', $row['created_at'] ?? 0) . "\n";
}
