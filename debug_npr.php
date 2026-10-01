<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
require 'common/config/bootstrap.php';
require 'backend/config/bootstrap.php';
$config = yii\helpers\ArrayHelper::merge(
    require 'common/config/main.php',
    require 'common/config/main-local.php',
    require 'backend/config/main.php',
    require 'backend/config/main-local.php'
);
(new yii\web\Application($config));

$m1 = \backend\models\PurchaseMaster::findOne(['docnum' => 'NPR202608120001']);
echo "NPR1: \n";
if ($m1) {
    echo "job_no = " . $m1->job_no . "\n";
    $job = \backend\models\Job::findOne($m1->job_no);
    if ($job) {
        echo "Found Job ID " . $job->id . ", job_no=" . $job->job_no . "\n";
        if ($job->quotation) {
            echo "Quotation for Job: " . $job->quotation->quotation_no . "\n";
        } else {
            echo "No quotation for Job\n";
        }
    } else {
        echo "Job ID " . $m1->job_no . " not found\n";
        $qt = \backend\models\Quotation::findOne($m1->job_no);
        if ($qt) {
            echo "Found Quotation ID " . $qt->id . ", qt_no=" . $qt->quotation_no . "\n";
        } else {
            echo "Quotation ID " . $m1->job_no . " not found\n";
        }
    }
}
