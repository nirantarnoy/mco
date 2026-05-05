<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../common/config/bootstrap.php';
require __DIR__ . '/../backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php',
    require __DIR__ . '/../backend/config/main.php',
    require __DIR__ . '/../backend/config/main-local.php'
);

(new yii\web\Application($config));

use backend\models\PurchReq;
use backend\models\Purch;

echo "--- Latest 10 Purchase Requests (purch_req) ---\n";
$prs = (new \yii\db\Query())
    ->select(['id', 'purch_req_no', 'company_id', 'created_at'])
    ->from('purch_req')
    ->orderBy(['id' => SORT_DESC])
    ->limit(20)
    ->all();

foreach ($prs as $pr) {
    echo "ID: {$pr['id']} | No: {$pr['purch_req_no']} | Co: {$pr['company_id']} | Created: " . date('Y-m-d H:i:s', $pr['created_at']) . "\n";
}

echo "\n--- Latest 10 Purchase Orders (purch) ---\n";
$pos = (new \yii\db\Query())
    ->select(['id', 'purch_no', 'company_id', 'created_at'])
    ->from('purch')
    ->orderBy(['id' => SORT_DESC])
    ->limit(20)
    ->all();

foreach ($pos as $po) {
    echo "ID: {$po['id']} | No: {$po['purch_no']} | Co: {$po['company_id']} | Created: " . ($po['created_at'] ? date('Y-m-d H:i:s', $po['created_at']) : 'N/A') . "\n";
}
