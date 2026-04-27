<?php
/**
 * Database schema fix script to allow unlimited notes/remarks
 * Run this on your server: php fix_db_schema.php
 */

require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';

// Load local database configuration
$config = require 'common/config/main-local.php';
$dbConfig = $config['components']['db'];

$dsn = $dbConfig['dsn'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];

// Optional: If running on server and needing to override 'localhost' if connection fails
// $dsn = str_replace('localhost', '127.0.0.1', $dsn);

try {
    echo "Connecting to database: $dsn\n";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sqls = [
        "ALTER TABLE purch MODIFY note TEXT",
        "ALTER TABLE purch MODIFY delivery_note TEXT",
        "ALTER TABLE purch MODIFY payment_note TEXT",
        "ALTER TABLE purch MODIFY special_note TEXT",
        "ALTER TABLE purch_req MODIFY note TEXT",
        "ALTER TABLE purch_req MODIFY reason TEXT",
        "ALTER TABLE purch_req MODIFY special_note TEXT",
        "ALTER TABLE purch_line MODIFY note TEXT",
        "ALTER TABLE purch_line MODIFY product_description TEXT",
        "ALTER TABLE purch_req_line MODIFY note TEXT",
        "ALTER TABLE purch_req_line MODIFY product_description TEXT"
    ];
    
    foreach ($sqls as $sql) {
        echo "Executing: $sql ... ";
        try {
            $pdo->exec($sql);
            echo "DONE\n";
        } catch (Exception $inner) {
            echo "FAILED: " . $inner->getMessage() . "\n";
        }
    }
    
    echo "\nAll database changes attempted.\n";
} catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
}
