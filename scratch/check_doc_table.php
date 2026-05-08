<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php'
);
new yii\web\Application(['id' => 'test', 'basePath' => __DIR__, 'components' => $config['components']]);

try {
    $table = Yii::$app->db->getTableSchema('credit_note_doc');
    if ($table) {
        echo "Table credit_note_doc exists\n";
        foreach ($table->columns as $column) {
            echo "Column: {$column->name} ({$column->dbType})\n";
        }
    } else {
        echo "Table credit_note_doc NOT found\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
