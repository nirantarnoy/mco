<?php
$pdo = new PDO('mysql:host=localhost;dbname=mco_db;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->prepare("UPDATE pre_advance SET status = 1 WHERE status = 0");
$stmt->execute();
echo "Updated " . $stmt->rowCount() . " rows.\n";
 