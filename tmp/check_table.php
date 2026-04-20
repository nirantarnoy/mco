<?php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
$config = require(__DIR__ . '/../common/config/main.php');
$config = array_merge($config, require(__DIR__ . '/../common/config/main-local.php'));
new yii\web\Application($config);

try {
    $exists = Yii::$app->db->createCommand("SHOW TABLES LIKE 'temp_invoice'")->queryScalar();
    echo $exists ? "Table exists" : "Table DOES NOT EXIST";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
