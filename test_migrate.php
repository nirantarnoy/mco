<?php
try {
    $db = require 'common/config/main-local.php';
    if(isset($db['components']['db'])) {
        $dsn = $db['components']['db']['dsn'];
        $user = $db['components']['db']['username'];
        $pass = $db['components']['db']['password'];
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sql = "CREATE TABLE IF NOT EXISTS `stock_monthly_snapshot` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `warehouse_id` int(11) NOT NULL,
            `lot_no` varchar(50) DEFAULT NULL,
            `qty` float DEFAULT '0',
            `snapshot_period` varchar(7) NOT NULL COMMENT 'Format YYYY-MM',
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx-stock_monthly_snapshot-period` (`snapshot_period`),
            KEY `idx-stock_monthly_snapshot-product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $pdo->exec($sql);
        echo "Table created successfully.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
