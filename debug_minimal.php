<?php
define('YII_DEBUG', true);
define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';

$config = [
    'id' => 'temp',
    'basePath' => __DIR__,
    'components' => [
        'db' => require __DIR__ . '/common/config/db.php',
    ],
];

(new yii\console\Application($config));

$db = Yii::$app->db;

$nums = ['ARC-QT25-000193', 'ARC-QT25-000194'];
foreach ($nums as $num) {
    $q = $db->createCommand("SELECT id, quotation_no, status, company_id FROM quotation WHERE quotation_no = :no", [':no' => $num])->queryOne();
    if ($q) {
        echo "Found: ID: " . $q['id'] . " | No: " . $q['quotation_no'] . " | Status: " . $q['status'] . " | Company: " . $q['company_id'] . "\n";
        
        $invoices = $db->createCommand("SELECT invoice_number, invoice_type, status FROM invoice WHERE quotation_id = :qid AND status = 1 AND invoice_type = 2", [':qid' => $q['id']])->queryAll();
        if ($invoices) {
            echo "   Used in active Tax Invoices (Type 2):\n";
            foreach ($invoices as $inv) {
                echo "   - " . $inv['invoice_number'] . " (Status: " . $inv['status'] . ")\n";
            }
        } else {
            echo "   Not used in any active Tax Invoices.\n";
        }
    } else {
        echo "Not found: $num\n";
    }
    echo "--------------------\n";
}
