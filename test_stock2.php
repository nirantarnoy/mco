<?php
$pdo = new PDO('mysql:host=localhost;dbname=mco_db', 'root', '');
$stmt = $pdo->query("SELECT id, code, name FROM product WHERE name LIKE '%JX0706%' OR name LIKE '%PRIMARY FILTER%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
if ($rows) {
    $pid = $rows[0]['id'];
    $stmt = $pdo->query("SELECT * FROM stock_sum WHERE product_id = $pid");
    $srows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($srows);
}
