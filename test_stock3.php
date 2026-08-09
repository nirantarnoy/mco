<?php
$pdo = new PDO('mysql:host=localhost;dbname=mco_db', 'root', '');
$stmt = $pdo->query("SELECT * FROM journal_trans WHERE id = 1360");
$jt = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($jt);

$stmt = $pdo->query("SELECT * FROM journal_trans_line WHERE journal_trans_id = 1360");
$lines = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($lines);

if ($lines) {
    foreach ($lines as $line) {
        $pid = $line['product_id'];
        $wid = $line['warehouse_id'];
        echo "Product ID: $pid, Warehouse ID: $wid\n";
        $stmt = $pdo->query("SELECT * FROM stock_sum WHERE product_id = $pid");
        $ss = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($ss);
    }
}
