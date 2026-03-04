<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mocking minimal Yii environment
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

try {
    $rows = (new \yii\db\Query())
        ->select(['id', 'purch_req_no', 'company_id'])
        ->from('purch_req')
        ->orderBy(['id' => SORT_DESC])
        ->limit(20)
        ->all();

    foreach ($rows as $row) {
        echo "ID: " . $row['id'] . " | PR NO: " . $row['purch_req_no'] . " | Co ID: " . $row['company_id'] . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
