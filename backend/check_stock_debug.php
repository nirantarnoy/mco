<?php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../common/config/bootstrap.php');
require(__DIR__ . '/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/config/main.php'),
    require(__DIR__ . '/config/main-local.php')
);

(new yii\web\Application($config));

use backend\models\Product;
use backend\models\StockSum;
use backend\models\JournalTrans;
use backend\models\JournalTransLine;

// Search for the transaction mentioned in the image
$product_code = 'CONS00048';
$product = Product::find()->where(['code' => $product_code])->one();

if ($product) {
    echo "Product: " . $product->code . " - " . $product->name . " (ID: " . $product->id . ")\n";
    echo "Total Stock (Product->stock_qty): " . $product->stock_qty . "\n";
    
    echo "Stock in Warehouses:\n";
    $stocks = StockSum::find()->where(['product_id' => $product->id])->all();
    foreach ($stocks as $s) {
        echo "  Warehouse ID: " . $s->warehouse_id . " (" . (\backend\models\Warehouse::findName($s->warehouse_id)) . ")\n";
        echo "    Qty: " . $s->qty . "\n";
        echo "    Reserv Qty: " . $s->reserv_qty . "\n";
        echo "    Available: " . $s->getAvailableQty() . "\n";
    }
    
    echo "\nSearching for transactions containing this product with Qty 12...\n";
    $lines = JournalTransLine::find()
        ->joinWith('journalTrans')
        ->where(['product_id' => $product->id, 'journal_trans_line.qty' => 12])
        ->all();
        
    foreach ($lines as $line) {
        $jt = $line->journalTrans;
        echo "Found Transaction: " . $jt->journal_no . " (ID: " . $jt->id . ")\n";
        echo "  Status: " . $jt->status . "\n";
        echo "  Line Warehouse ID: " . $line->warehouse_id . " (" . (\backend\models\Warehouse::findName($line->warehouse_id)) . ")\n";
        echo "  Line Qty: " . $line->qty . "\n";
    }
} else {
    echo "Product $product_code not found\n";
}
