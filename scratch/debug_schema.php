<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
$config = require('common/config/main.php');
$configLocal = require('common/config/main-local.php');
$config = yii\helpers\ArrayHelper::merge($config, $configLocal);
$config['id'] = 'debug-app';
$config['basePath'] = dirname(__DIR__);
new yii\web\Application($config);

$table = Yii::$app->db->getTableSchema('purch');
echo "Columns in 'purch' table:\n";
foreach ($table->columns as $column) {
    echo "- {$column->name} ({$column->type})\n";
}

$table2 = Yii::$app->db->getTableSchema('purch_req');
echo "\nColumns in 'purch_req' table:\n";
foreach ($table2->columns as $column) {
    echo "- {$column->name} ({$column->type})\n";
}
