<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';
require __DIR__ . '/backend/config/bootstrap.php';
$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/backend/config/main.php',
    require __DIR__ . '/backend/config/main-local.php'
);
(new yii\web\Application($config));

$po = \backend\models\Purch::findOne(['purch_no' => 'PO-00330-QT26-000084.140']);
echo "PO id: " . ($po ? $po->id : 'null') . "\n";
echo "net_amount: " . ($po ? $po->net_amount : 'null') . "\n";
echo "ref_no: " . ($po ? $po->ref_no : 'null') . "\n";

$npr = \backend\models\PurchaseMaster::findOne(['docnum' => 'NPR202609140002']);
echo "NPR id: " . ($npr ? $npr->id : 'null') . "\n";
echo "total_amount: " . ($npr ? $npr->total_amount : 'null') . "\n";
echo "job_no: " . ($npr ? $npr->job_no : 'null') . "\n";
echo "refnum: " . ($npr ? $npr->refnum : 'null') . "\n";

$pa = \backend\models\PreAdvance::findOne(['pre_advance_no' => 'PA2026090006']);
echo "PA id: " . ($pa ? $pa->id : 'null') . "\n";
if ($pa) {
    foreach($pa->preAdvanceLines as $line) {
        echo "Line ID: " . $line->id . " Desc: " . $line->description . " Amount: " . $line->amount . "\n";
    }
}
