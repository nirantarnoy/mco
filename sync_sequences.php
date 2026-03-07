<?php
/**
 * Script to sync invoice_sequences table with actual max numbers in invoices table
 */
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php'
);

// Create console application to use Yii components
(new yii\console\Application($config));

$db = Yii::$app->db;
$year = date('Y');
$types = ['quotation', 'bill_placement', 'tax_invoice', 'receipt'];

echo "Starting Sync Sequence for $year...\n";

foreach ($types as $type) {
    echo "Processing type: $type... ";
    
    // Find highest sequence number currently used in invoices table for this type and year
    $invoices = \backend\models\Invoice::find()
        ->where(['invoice_type' => $type])
        ->andWhere(['like', 'invoice_number', '-' . $year . '-', false])
        ->orWhere(['like', 'invoice_number', $year, false]) // For cases like INV2600001
        ->all();
        
    $maxNumber = 0;
    foreach ($invoices as $inv) {
        $numPart = '';
        if ($type == 'bill_placement') {
            // BP-2026-006 -> 6
            if (preg_match('/-([0-9]{3,})$/', $inv->invoice_number, $matches)) {
                $numPart = $matches[1];
            }
        } else if ($type == 'tax_invoice') {
            // INV2600001 -> 1
            if (preg_match('/([0-9]{5,})$/', $inv->invoice_number, $matches)) {
                $numPart = $matches[1];
            }
        } else {
            // QT26-000001 -> 1
            if (preg_match('/-([0-9]{6,})$/', $inv->invoice_number, $matches)) {
                $numPart = $matches[1];
            }
        }
        
        if ($numPart !== '') {
            $num = (int)$numPart;
            if ($num > $maxNumber) $maxNumber = $num;
        }
    }
    
    echo "Max found in table: $maxNumber. ";
    
    // Update sequence table
    $db->createCommand()->upsert('invoice_sequences', [
        'invoice_type' => $type,
        'year' => $year,
        'month' => 0,
        'last_number' => $maxNumber,
        'prefix' => ($type == 'bill_placement' ? 'BP' : ($type == 'tax_invoice' ? 'INV' : ($type == 'quotation' ? 'QT' : 'RE')))
    ], [
        'last_number' => $maxNumber
    ])->execute();
    
    echo "Sequence table updated.\n";
}

echo "\nSync Complete.\n";
