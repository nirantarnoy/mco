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

$db = Yii::$app->db;

$quotations = $db->createCommand("SELECT id, quotation_no, status, company_id FROM quotation WHERE quotation_no IN ('ARC-QT25-000193', 'ARC-QT25-000194')")->queryAll();

foreach ($quotations as $q) {
    echo "ID: " . $q['id'] . " | No: " . $q['quotation_no'] . " | Status: " . $q['status'] . " | Company: " . $q['company_id'] . "\n";
    
    $invoices = $db->createCommand("SELECT invoice_number, invoice_type, status FROM invoice WHERE quotation_id = :qid AND status = 1", [':qid' => $q['id']])->queryAll();
    if ($invoices) {
        echo "   Used in active invoices:\n";
        foreach ($invoices as $inv) {
            echo "   - " . $inv['invoice_number'] . " (Type: " . $inv['invoice_type'] . ", Status: " . $inv['status'] . ")\n";
        }
    } else {
        echo "   Not used in any active invoices.\n";
    }
    echo "--------------------\n";
}
