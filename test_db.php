<?php
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/vendor/yiisoft/yii2/Yii.php';
require __DIR__.'/common/config/bootstrap.php';
require __DIR__.'/backend/config/bootstrap.php';

$db = require __DIR__.'/common/config/main-local.php';
$pdo = new PDO($db['components']['db']['dsn'], $db['components']['db']['username'], $db['components']['db']['password']);

$qs = $pdo->query("SELECT id, quotation_no FROM quotation WHERE quotation_no LIKE '%ARC-QT26-000064%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($qs);
