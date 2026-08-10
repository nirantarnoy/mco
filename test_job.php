<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/backend/config/main.php';
(new yii\web\Application($config));
$jobs = \backend\models\Job::find()->where(['not', ['quotation_id' => null]])->orderBy(['id' => SORT_DESC])->limit(5)->all();
foreach($jobs as $job) {
    $q = $job->quotation;
    if($q) {
        echo 'Job: ' . $job->job_no . ', sum_note: ' . $job->summary_note . ', Cust: ' . $q->customer_name . ', Note: ' . $q->note . "\n";
    }
}
