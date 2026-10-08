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

foreach(['purch', 'purchasemaster', 'purch_req', 'pre_advance', 'payment_voucher'] as $tbl) {
    echo "Columns in $tbl:\n";
    $table = Yii::$app->db->getTableSchema($tbl);
    if ($table) {
        foreach ($table->columns as $column) {
            if (strpos($column->name, 'approve') !== false || strpos($column->name, 'by') !== false) {
                echo "- " . $column->name . "\n";
            }
        }
    }
    echo "\n";
}
