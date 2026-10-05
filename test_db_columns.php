<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/common/config/main.php';
$config['id'] = 'basic-console';
$config['basePath'] = __DIR__;
$dbConfig = require __DIR__ . '/common/config/main-local.php';
$dbConfig['components']['db']['dsn'] = str_replace('localhost', '127.0.0.1', $dbConfig['components']['db']['dsn']);
$config['components']['db'] = $dbConfig['components']['db'];
$app = new yii\console\Application($config);

$table = Yii::$app->db->getTableSchema('purch');
if ($table) {
    echo "Columns in purch table:\n";
    foreach ($table->columns as $column) {
        echo "- " . $column->name . " (" . $column->type . ")\n";
    }
} else {
    echo "Table purch not found.\n";
}
