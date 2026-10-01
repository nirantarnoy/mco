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

$poNos = ['PO-00327-QT26-000084.137'];

foreach ($poNos as $poNo) {
    echo "--- PO: $poNo ---\n";
    $pm = \backend\models\Purch::find()->where(['purch_no' => $poNo])->one();
    if ($pm) {
        echo "PO Attributes:\n";
        print_r($pm->attributes);
    }
    
    $actual_qt_no = 'RY-QT26-000084';
    $qt = \backend\models\Quotation::find()->where(['quotation_no' => $actual_qt_no])->one();
    if ($qt) {
        echo "\nQT Attributes:\n";
        print_r($qt->attributes);
        
        $job = \backend\models\Job::find()->where(['job_no' => $actual_qt_no])->one();
        if (!$job && $qt->job_id) {
            $job = \backend\models\Job::findOne($qt->job_id);
        }
        if ($job) {
            echo "\nJob Attributes:\n";
            print_r($job->attributes);
        } else {
            echo "\nNo Job found.\n";
        }
    }
}
