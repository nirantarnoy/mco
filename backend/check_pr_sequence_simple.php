<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/config/main.php'),
    require(__DIR__ . '/config/main-local.php')
);

$db = new \yii\db\Connection($config['components']['db']);
$db->open();

$rows = (new \yii\db\Query())
    ->select(['id', 'purch_req_no', 'company_id', 'created_at'])
    ->from('purch_req')
    ->orderBy(['CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, "-", 2), "-", -1) AS UNSIGNED)' => SORT_DESC])
    ->limit(50)
    ->all($db);

echo "--- START RESULTS ---\n";
foreach ($rows as $row) {
    echo "ID: " . str_pad($row['id'], 6) . " | NO: " . str_pad($row['purch_req_no'], 30) . " | CO: " . $row['company_id'] . " | Date: " . date('Y-m-d H:i:s', $row['created_at'] ?? 0) . "\n";
}
echo "--- END RESULTS ---\n";
exit;
