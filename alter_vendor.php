<?php
$config = require __DIR__ . '/common/config/main-local.php';
$db = $config['components']['db'];
$dsn = $db['dsn'];
$user = $db['username'];
$pass = $db['password'];

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE vendor ADD is_vat TINYINT(1) DEFAULT NULL COMMENT '1=VAT, 2=NO VAT'");
    echo "Successfully added 'is_vat' column to 'vendor' table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'is_vat' already exists in 'vendor' table.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
