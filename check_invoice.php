<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';
require __DIR__ . '/console/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/console/config/main.php',
    require __DIR__ . '/console/config/main-local.php'
);

$app = new yii\console\Application($config);

$tables = Yii::$app->db->schema->getTableNames();
foreach ($tables as $table) {
    try {
        $rows = Yii::$app->db->createCommand("SELECT * FROM `$table` WHERE CONCAT_WS(',', " . implode(',', array_map(function($c) { return "`$c`"; }, Yii::$app->db->getTableSchema($table)->columnNames)) . ") LIKE '%INV2600029%'")->queryAll();
        if ($rows) {
            echo "Found in table: $table\n";
            print_r($rows);
        }
    } catch (\Exception $e) {}
}
