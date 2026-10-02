<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/common/config/main.php';
$config['id'] = 'basic-console';
$config['basePath'] = __DIR__;
$config['components']['db'] = (require __DIR__ . '/common/config/main-local.php')['components']['db'];
$app = new yii\console\Application($config);
$schema = Yii::$app->db->getTableSchema('purch');
echo "Columns:\n";
print_r($schema->columnNames);
