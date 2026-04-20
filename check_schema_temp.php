<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';

$config = require 'common/config/main.php';
if (file_exists('common/config/main-local.php')) {
    $config = yii\helpers\ArrayHelper::merge($config, require 'common/config/main-local.php');
}

new yii\console\Application([
    'id' => 'temp-console',
    'basePath' => __DIR__,
    'components' => [
        'db' => $config['components']['db']
    ]
]);

$tables = ['purch_req_line', 'purch_line'];
foreach ($tables as $table) {
    echo "--- $table ---\n";
    $columns = Yii::$app->db->getTableSchema($table)->columns;
    foreach ($columns as $name => $column) {
        if (strpos($name, 'description') !== false) {
            echo "Field: $name, Type: $column->type, Size: $column->size, AllowNull: $column->allowNull\n";
        }
    }
}
