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

echo "--- Credit Note ---\n";
$table = Yii::$app->db->getTableSchema('credit_note');
if ($table) {
    foreach ($table->columns as $column) {
        echo $column->name . ' (' . $column->type . ")\n";
    }
}

echo "\n--- Debit Note ---\n";
$table = Yii::$app->db->getTableSchema('debit_note');
if ($table) {
    foreach ($table->columns as $column) {
        echo $column->name . ' (' . $column->type . ")\n";
    }
}
