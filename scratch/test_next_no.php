<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
$config = require('common/config/main.php');
$configLocal = require('common/config/main-local.php');
$config = yii\helpers\ArrayHelper::merge($config, $configLocal);
$config['id'] = 'debug-app';
$config['basePath'] = dirname(__DIR__);
new yii\web\Application($config);

use backend\models\PurchReq;
use backend\models\Job;

$job = Job::find()->one();
if ($job) {
    echo "Testing with Job: {$job->job_no} (ID: {$job->id})\n";
    try {
        $no = PurchReq::getNextPurchReqNo($job->id, 1);
        echo "Next PR No: $no\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
} else {
    echo "No job found to test.\n";
}
