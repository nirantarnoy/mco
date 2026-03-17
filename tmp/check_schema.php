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
$schema = $db->getTableSchema('invoice_sequences');
if ($schema) {
    echo "Columns:\n";
    foreach ($schema->columns as $column) {
        echo "- {$column->name} ({$column->dbType})\n";
    }
    
    // Check indexes
    $table = $db->createCommand("SHOW INDEX FROM invoice_sequences")->queryAll();
    echo "\nIndexes:\n";
    foreach ($table as $index) {
        echo "- {$index['Key_name']}: {$index['Column_name']} (Unique: {$index['Non_unique']})\n";
    }
} else {
    echo "Table not found";
}
