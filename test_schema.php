<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
$config = require 'backend/config/main.php';
(new yii\web\Application($config));
$columns = Yii::$app->db->getTableSchema('purch_req')->columnNames;
echo json_encode($columns);
