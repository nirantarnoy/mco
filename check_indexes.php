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

$application = new yii\console\Application($config);
$db = Yii::$app->db;
$indexes = $db->createCommand('SHOW INDEX FROM stock_sum')->queryAll();
foreach($indexes as $idx) {
    if ($idx['Non_unique'] == 0) {
        echo "Unique Index: " . $idx['Key_name'] . " - " . $idx['Column_name'] . "\n";
    }
}
