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

use backend\models\Quotation;
use backend\models\Invoice;

$ids = [93, 72]; // User mentioned 93 and then 72 for 194
foreach ($ids as $id) {
    $q = Quotation::findOne($id);
    if ($q) {
        echo "Quotation ID: " . $q->id . "\n";
        echo "No: " . $q->quotation_no . "\n";
        echo "Status: " . $q->status . " (" . $q->getStatusLabel() . ")\n";
        echo "Company ID: " . $q->company_id . "\n";
        
        $usedIn = Invoice::find()->where(['quotation_id' => $q->id, 'status' => Invoice::STATUS_ACTIVE])->all();
        if ($usedIn) {
            echo "Used in active invoices:\n";
            foreach ($usedIn as $invoice) {
                echo "- " . $invoice->invoice_number . " (Type: " . $invoice->invoice_type . ", Status: " . $invoice->status . ")\n";
            }
        } else {
            echo "Not used in any active invoices.\n";
        }
    } else {
        echo "Quotation ID $id not found.\n";
    }
    echo "--------------------\n";
}

// Search by Number just in case
$nums = ['ARC-QT25-000193', 'ARC-QT25-000194'];
foreach ($nums as $num) {
    $q = Quotation::find()->where(['quotation_no' => $num])->one();
    if ($q) {
        echo "Found by No $num -> ID: " . $q->id . ", Status: " . $q->status . ", Company: " . $q->company_id . "\n";
    } else {
        echo "Not found by No $num\n";
    }
}
