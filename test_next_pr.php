<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('YII_DEBUG', true);
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';

$config = [
    'id' => 'test-app',
    'basePath' => __DIR__,
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mco_db',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
];
new yii\console\Application($config);

// Import our model (since it's in a different namespace/path, we might need to require it if autoload doesn't find it)
require 'backend/models/PurchReq.php';
require 'backend/models/Job.php';

use backend\models\PurchReq;

// Mock session/user if needed by the model
Yii::$app->set('user', new class { public $isGuest = false; public $id = 1; });
Yii::$app->set('session', new class { public function get($key) { return 1; } });

echo "Testing Company 1:\n";
$nextNo = PurchReq::getNextPurchReqNo(null, 1);
echo "Next No (Co 1): " . $nextNo . "\n";

echo "Testing Company 2:\n";
$nextNo2 = PurchReq::getNextPurchReqNo(null, 2);
echo "Next No (Co 2): " . $nextNo2 . "\n";

echo "Testing with Job ID (if exists):\n";
// Try to find a job
$job = (new \yii\db\Query())->from('job')->one();
if ($job) {
    $nextNoJob = PurchReq::getNextPurchReqNo($job['id'], 1);
    echo "Next No (Job " . $job['id'] . "): " . $nextNoJob . "\n";
} else {
    echo "No job found to test.\n";
}
