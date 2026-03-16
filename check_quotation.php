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

$quotationNo = 'arc-qt25-000194';
$q = Quotation::find()->where(['quotation_no' => $quotationNo])->one();

if (!$q) {
    echo "Quotation $quotationNo not found.\n";
    
    // Try partial match
    $qs = Quotation::find()->where(['like', 'quotation_no', $quotationNo])->all();
    if ($qs) {
        echo "Found similar quotations:\n";
        foreach ($qs as $item) {
            echo "- " . $item->quotation_no . " (ID: " . $item->id . ", Status: " . $item->status . ")\n";
        }
    } else {
        echo "No similar quotations found. Listing last 10 quotations:\n";
        try {
            $last10 = Quotation::find()->orderBy(['id' => SORT_DESC])->limit(10)->all();
            foreach ($last10 as $item) {
                echo "- " . $item->quotation_no . " (ID: " . $item->id . ", Status: " . $item->status . ")\n";
            }
        } catch (\Exception $e) {
            echo "Error listing quotations: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "Quotation: " . $q->quotation_no . " (ID: " . $q->id . ")\n";
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
}
