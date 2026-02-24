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

echo "Top 20 PRs in database:\n";
echo "ID | PR No | Company ID | Created At\n";
echo str_repeat("-", 60) . "\n";

$rows = Yii::$app->db->createCommand("
    SELECT id, purch_req_no, company_id, created_at 
    FROM purch_req 
    ORDER BY id DESC 
    LIMIT 20
")->queryAll();

foreach ($rows as $row) {
    echo $row['id'] . " | " . $row['purch_req_no'] . " | " . $row['company_id'] . " | " . date('Y-m-d H:i', $row['created_at']) . "\n";
}

echo "\nSummary by Company:\n";
$summary = Yii::$app->db->createCommand("
    SELECT company_id, COUNT(*) as cnt, MAX(purch_req_no) as last_no 
    FROM purch_req 
    GROUP BY company_id
")->queryAll();

foreach ($summary as $s) {
    echo "Company ID: {$s['company_id']} | Count: {$s['cnt']} | Last PR: {$s['last_no']}\n";
}
