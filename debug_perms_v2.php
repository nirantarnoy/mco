<?php
define('YII_DEBUG', true);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';
require __DIR__ . '/backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/backend/config/main.php',
    require __DIR__ . '/backend/config/main-local.php'
);

(new yii\console\Application($config));

$auth = Yii::$app->authManager;
$perms = $auth->getPermissions();
echo "TOTAL_PERMS:" . count($perms) . "\n";
echo "SAMPLES:\n";
$i = 0;
foreach ($perms as $name => $perm) {
    echo $name . "\n";
    if (++$i >= 50) break;
}
