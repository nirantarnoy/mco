<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
require 'common/config/bootstrap.php';
require 'console/config/bootstrap.php';
$config = yii\helpers\ArrayHelper::merge(
    require 'common/config/main.php',
    require 'common/config/main-local.php',
    require 'console/config/main.php',
    require 'console/config/main-local.php'
);
new yii\console\Application($config);
$schema = Yii::$app->db->getTableSchema('purch');
if ($schema) {
    echo "Columns in 'purch' table:\n";
    foreach ($schema->columns as $col) {
        echo "- " . $col->name . " (" . $col->dbType . ")\n";
    }
} else {
    echo "Table 'purch' not found\n";
}

$schemaReq = Yii::$app->db->getTableSchema('purch_req');
if ($schemaReq) {
    echo "\nColumns in 'purch_req' table:\n";
    foreach ($schemaReq->columns as $col) {
        echo "- " . $col->name . " (" . $col->dbType . ")\n";
    }
}
