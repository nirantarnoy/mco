<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
$config = require(__DIR__ . '/../common/config/main.php');
if (file_exists(__DIR__ . '/../common/config/main-local.php')) {
    $config = yii\helpers\ArrayHelper::merge($config, require(__DIR__ . '/../common/config/main-local.php'));
}

new yii\console\Application($config);

try {
    $cols = Yii::$app->db->createCommand("SHOW COLUMNS FROM temp_invoice_line")->queryAll();
    foreach ($cols as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
