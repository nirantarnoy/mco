<?php
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

$controllers = \backend\helpers\PermissionScanner::scanAllControllers();
echo "SCANNER_RESULT:\n";
foreach ($controllers as $name => $info) {
    echo "Controller: $name\n";
    if ($name == 'purch' || strpos(strtolower($name), 'action') !== false) {
        foreach ($info['actions'] as $actionName => $actionInfo) {
            echo "  - " . \backend\helpers\PermissionScanner::createPermissionName($name, $actionName) . "\n";
        }
    }
}
echo "\nDB_PERMS_LIKE_PURCH:\n";
$perms = Yii::$app->authManager->getPermissions();
foreach ($perms as $name => $p) {
    if (strpos(strtolower($name), 'purch') !== false || strpos(strtolower($name), 'action') !== false) {
        echo "- " . $name . "\n";
    }
}
