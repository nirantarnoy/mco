<?php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../common/config/bootstrap.php');
require(__DIR__ . '/../backend/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/../backend/config/main.php'),
    require(__DIR__ . '/../backend/config/main-local.php')
);

(new yii\web\Application($config));

foreach (Yii::$app->db->schema->getTableNames() as $tableName) {
    $table = Yii::$app->db->getTableSchema($tableName);
    foreach ($table->columns as $column) {
        if (strpos($column->name, 'date') !== false) {
            if ($column->name == 'doc_date' || $column->name == 'issue_date') {
                echo "Table: $tableName, Column: {$column->name}\n";
            }
        }
    }
}
