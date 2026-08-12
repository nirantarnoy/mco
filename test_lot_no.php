<?php
try {
    $db = require 'common/config/main-local.php';
    if(isset($db['components']['db'])) {
        $dsn = $db['components']['db']['dsn'];
        $user = $db['components']['db']['username'];
        $pass = $db['components']['db']['password'];
        $pdo = new PDO($dsn, $user, $pass);
        $stmt = $pdo->query("SELECT count(*) FROM stock_trans WHERE lot_no IS NOT NULL AND lot_no != ''");
        echo "COUNT_LOT_NO_IN_STOCK_TRANS: " . $stmt->fetchColumn() . "\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
