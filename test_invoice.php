<?php
require "vendor/autoload.php";
require "vendor/yiisoft/yii2/Yii.php";
$config = require "backend/config/main.php";
new yii\web\Application($config);

$rows = Yii::$app->db->createCommand("SELECT invoice_type, invoice_date, created_at, status FROM invoices LIMIT 20")->queryAll();
print_r($rows);
