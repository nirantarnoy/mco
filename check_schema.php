<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';

$config = require 'common/config/main-local.php';
$dbConfig = $config['components']['db'];

$dsn = str_replace('localhost', '127.0.0.1', $dbConfig['dsn']);
$username = $dbConfig['username'];
$password = $dbConfig['password'];

try {
    $pdo = new PDO($dsn, $username, $password);
    
    $tables = ['purch', 'purch_req', 'purch_line', 'purch_req_line'];
    
    foreach ($tables as $table) {
        echo "Table: $table\n";
        $stmt = $pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            if (in_array($col['Field'], ['note', 'special_note', 'reason', 'delivery_note', 'payment_note', 'product_description'])) {
                echo "  {$col['Field']}: {$col['Type']}\n";
            }
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
