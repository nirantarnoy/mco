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
    if (!$pm) {
        echo "PO not found\n";
        continue;
    }
    
    echo "PO Found. ID: {$pm->id}\n";
    if (isset($pm->purchLines) && count($pm->purchLines) > 0) {
        echo "PO Lines: ".count($pm->purchLines)."\n";
        foreach ($pm->purchLines as $pl) {
            echo " - Name: {$pl->product_name}, Desc: {$pl->product_description}, Note: {$pl->note}\n";
        }
    } else {
        echo "No PO Lines\n";
    }
    
    // QT
    $actual_qt_no = '';
    if ($pm->job && $pm->job->quotation) {
        $actual_qt_no = $pm->job->quotation->quotation_no;
    } elseif ($pm->job) {
        $actual_qt_no = $pm->job->job_no;
    } elseif (is_numeric($pm->job_id)) {
        $qt = \backend\models\Quotation::findOne($pm->job_id);
        if ($qt) $actual_qt_no = $qt->quotation_no;
    }
    
    if (empty($actual_qt_no)) $actual_qt_no = $pm->ref_no;
    if (empty($actual_qt_no)) {
        if (preg_match('/-(QT[A-Za-z0-9-]+)(?:\.|$)/i', $pm->purch_no, $match)) {
            $actual_qt_no = $match[1];
        }
    }
    
    echo "Resolved QT No: $actual_qt_no\n";
    
    if (!empty($actual_qt_no)) {
        $qt = \backend\models\Quotation::find()->where(['like', 'quotation_no', $actual_qt_no])->one();
        if ($qt) {
            echo "Found QT: {$qt->quotation_no}\n";
            if (isset($qt->quotationLines)) {
                echo "QT Lines: ".count($qt->quotationLines)."\n";
                foreach ($qt->quotationLines as $ql) {
                    echo " - PName: {$ql->product_name}, Note: {$ql->note}\n";
                }
            } else {
                echo "No QT Lines\n";
            }
        } else {
            echo "QT not found\n";
        }
    }
}
