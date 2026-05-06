<?php
define('YII_DEBUG', true);
define('YII_ENV', 'dev');

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

use backend\models\PurchaseMaster;
use backend\models\Purch;

echo "Checking purchase_master...\n";
$ry_pm = PurchaseMaster::find()->where(['like', 'docnum', 'ry-qt'])->count();
$arc_pm = PurchaseMaster::find()->where(['like', 'docnum', 'arc-qt'])->count();
echo "docnum: ry-qt ($ry_pm), arc-qt ($arc_pm)\n";

$ry_pm_ref = PurchaseMaster::find()->where(['like', 'refnum', 'ry-qt'])->count();
$arc_pm_ref = PurchaseMaster::find()->where(['like', 'refnum', 'arc-qt'])->count();
echo "refnum: ry-qt ($ry_pm_ref), arc-qt ($arc_pm_ref)\n";

$ry_pm_job = PurchaseMaster::find()->where(['like', 'job_no', 'ry-qt'])->count();
$arc_pm_job = PurchaseMaster::find()->where(['like', 'job_no', 'arc-qt'])->count();
echo "job_no: ry-qt ($ry_pm_job), arc-qt ($arc_pm_job)\n";

echo "\nChecking purch...\n";
$ry_p = Purch::find()->where(['like', 'purch_no', 'ry-qt'])->count();
$arc_p = Purch::find()->where(['like', 'purch_no', 'arc-qt'])->count();
echo "purch_no: ry-qt ($ry_p), arc-qt ($arc_p)\n";

$ry_p_ref = Purch::find()->where(['like', 'ref_no', 'ry-qt'])->count();
$arc_p_ref = Purch::find()->where(['like', 'ref_no', 'arc-qt'])->count();
echo "ref_no: ry-qt ($ry_p_ref), arc-qt ($arc_p_ref)\n";

if ($ry_pm > 0 || $arc_pm > 0 || $ry_pm_ref > 0 || $arc_pm_ref > 0 || $ry_p > 0 || $arc_p > 0 || $ry_p_ref > 0 || $arc_p_ref > 0) {
    echo "\nSamples found. Please verify the table and column.\n";
} else {
    echo "\nNo records found with ry-qt or arc-qt in standard columns.\n";
    echo "Total records in purchase_master: " . PurchaseMaster::find()->count() . "\n";
    echo "Total records in purch: " . Purch::find()->count() . "\n";
}
