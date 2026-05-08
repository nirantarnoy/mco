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

$table = Yii::$app->db->getTableSchema('credit_note');
if ($table) {
    foreach ($table->columns as $column) {
        echo $column->name . ' (' . $column->type . ")\n";
    }
} else {
    echo "Table credit_note not found\n";
}
