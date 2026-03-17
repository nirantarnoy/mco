<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../common/config/bootstrap.php';
require __DIR__ . '/../backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php',
    require __DIR__ . '/../backend/config/main.php',
    require __DIR__ . '/../backend/config/main-local.php'
);

(new yii\web\Application($config));

$db = Yii::$app->db;
echo "Duplicate Invoices:\n";
$dupes = $db->createCommand("
    SELECT invoice_number, invoice_type, COUNT(*) as count 
    FROM invoices 
    WHERE status = 1 
    GROUP BY invoice_number, invoice_type 
    HAVING count > 1
")->queryAll();
print_r($dupes);

echo "\nDuplicate Quotations:\n";
$dupesQ = $db->createCommand("
    SELECT quotation_no, COUNT(*) as count 
    FROM quotation 
    WHERE status = 1 
    GROUP BY quotation_no 
    HAVING count > 1
")->queryAll();
print_r($dupesQ);
