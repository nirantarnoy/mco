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

use backend\models\JournalTrans;
use backend\models\JournalTransLine;

$journalNo = 'ISS202510150001';
$model = JournalTrans::find()->where(['journal_no' => $journalNo])->one();

if (!$model) {
    echo "Transaction $journalNo not found.\n";
    exit;
}

echo "Transaction: " . $model->journal_no . " (ID: " . $model->id . ")\n";
echo "Status: " . $model->status . "\n";
echo "--------------------------------------------------\n";

foreach ($model->journalTransLines as $index => $line) {
    echo "Line " . ($index + 1) . ":\n";
    echo "  Product: " . ($line->product ? $line->product->code . ' - ' . $line->product->name : 'N/A') . " (ID: " . $line->product_id . ")\n";
    echo "  Qty: " . $line->qty . "\n";
    echo "  Warehouse ID: " . ($line->warehouse_id ?: 'NULL (Empty)') . "\n";
    echo "  Warehouse Name: " . ($line->warehouse ? $line->warehouse->name : 'N/A') . "\n";
    
    if ($line->product_id && $line->warehouse_id) {
        $available = $line->product->getAvailableStockInWarehouse($line->warehouse_id);
        echo "  Check Available Stock in this Warehouse: " . $available . "\n";
    } else {
        echo "  Check Available Stock: Cannot check (Missing Product or Warehouse)\n";
    }
    echo "--------------------------------------------------\n";
}
