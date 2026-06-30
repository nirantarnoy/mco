<?php
ob_start();
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
require 'e:/xampp/htdocs/mco/vendor/autoload.php';
require 'e:/xampp/htdocs/mco/vendor/yiisoft/yii2/Yii.php';
require 'e:/xampp/htdocs/mco/common/config/bootstrap.php';
require 'e:/xampp/htdocs/mco/backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require 'e:/xampp/htdocs/mco/common/config/main.php',
    require 'e:/xampp/htdocs/mco/common/config/main-local.php',
    require 'e:/xampp/htdocs/mco/backend/config/main.php',
    require 'e:/xampp/htdocs/mco/backend/config/main-local.php'
);

$application = new yii\web\Application($config);
$query = \backend\models\Purch::find()
    ->with(['vendor', 'purchLines'])
    ->orderBy(['purch_date' => SORT_ASC, 'purch_no' => SORT_ASC])
    ->limit(10);
$models = $query->all();

foreach ($models as $model) {
    echo "PO: " . $model->purch_no . PHP_EOL;
    $details = $model->getPurchLines()->all();
    foreach ($details as $detail) {
        $productCode = $detail->getProductCode();
        $stkcod = $productCode ? strtoupper(preg_replace('/[\s\/\x22\x27]/', '', $productCode)) : '';
        echo "  - Line: " . $detail->product_name . " | Code: " . $stkcod . PHP_EOL;
    }
}
