<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php'
);

$dbConfig = $config['components']['db'];
if (isset($dbConfig['class'])) unset($dbConfig['class']);

try {
    $db = new \yii\db\Connection($dbConfig);
    $db->open();
    echo "Connected successfully to " . $db->dsn . "\n";

    $count = (new \yii\db\Query())->from('invoices')->count('*', $db);
    echo "Total invoices: " . $count . "\n";

    $rows = (new \yii\db\Query())
        ->from('invoices')
        ->where(['invoice_type' => 'bill_placement'])
        ->orderBy('id DESC')
        ->limit(10)
        ->all($db);
    
    echo "Recent bill placements:\n";
    foreach ($rows as $row) {
        echo "ID: {$row['id']} | NO: {$row['invoice_number']} | Date: {$row['invoice_date']} | Customer: {$row['customer_name']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
