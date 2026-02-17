<?php
define('YII_DEBUG', true);
define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/common/config/bootstrap.php';
require __DIR__ . '/backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/backend/config/main.php',
    require __DIR__ . '/backend/config/main-local.php'
);

(new yii\web\Application($config));

$auth = Yii::$app->authManager;
$perms = $auth->getPermissions();
echo "Total permissions: " . count($perms) . "\n";
echo "First 20 permissions:\n";
$i = 0;
foreach ($perms as $name => $perm) {
    echo "- " . $name . "\n";
    if (++$i >= 20) break;
}
