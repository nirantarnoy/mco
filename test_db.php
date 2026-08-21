<?php
$db = require __DIR__.'/common/config/main-local.php';
$pdo = new PDO($db['components']['db']['dsn'], $db['components']['db']['username'], $db['components']['db']['password']);

$qs = $pdo->query("SELECT DISTINCT invoice_type FROM invoices")->fetchAll(PDO::FETCH_ASSOC);
print_r($qs);
$qs2 = $pdo->query("SELECT invoice_date, created_at FROM invoices LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($qs2);
