<?php
require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
$config = require('common/config/main.php');
$configLocal = require('common/config/main-local.php');
$config = yii\helpers\ArrayHelper::merge($config, $configLocal);
$config['id'] = 'debug-app';
$config['basePath'] = dirname(__DIR__);
new yii\web\Application($config);

$schema = Yii::$app->db->schema->getTableNames();
foreach ($schema as $tableName) {
    try {
        $cols = Yii::$app->db->getTableSchema($tableName)->columnNames;
        $foundCols = [];
        foreach ($cols as $col) {
            if (stripos($col, 'no') !== false || stripos($col, 'num') !== false) {
                $foundCols[] = $col;
            }
        }
        
        if (!empty($foundCols)) {
            foreach ($foundCols as $col) {
                $rows = (new \yii\db\Query())
                    ->select([$col])
                    ->from($tableName)
                    ->where(['like', $col, '492'])
                    ->limit(5)
                    ->all();
                if (!empty($rows)) {
                    echo "Table: $tableName, Column: $col\n";
                    foreach ($rows as $row) {
                        echo "- {$row[$col]}\n";
                    }
                }
            }
        }
    } catch (\Exception $e) {}
}
