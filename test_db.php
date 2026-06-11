<?php
require 'common/config/main-local.php';
$pdo = new PDO('mysql:host=localhost;dbname=mco_db', 'root', '');
$stmt = $pdo->query('SHOW COLUMNS FROM pre_advance_ref');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
