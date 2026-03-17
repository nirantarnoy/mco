<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../common/config/bootstrap.php';
require __DIR__ . '/../backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php',
    require __DIR__ . '/../backend/config/main.php',
    require __DIR__ . '/../backend/config/main-local.php'
);

(new yii\web\Application($config));

$db = Yii::$app->db;
foreach (['invoices', 'quotation'] as $tableName) {
    echo "Table: $tableName\n";
    $schema = $db->getTableSchema($tableName);
    if ($schema) {
        $table = $db->createCommand("SHOW INDEX FROM $tableName")->queryAll();
        echo "Indexes:\n";
        foreach ($table as $index) {
            echo "- {$index['Key_name']}: {$index['Column_name']} (Unique: " . ($index['Non_unique'] == 0 ? 'Yes' : 'No') . ")\n";
        }
    } else {
        echo "Table not found\n";
    }
    echo "\n";
}
