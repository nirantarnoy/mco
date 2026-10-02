<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/common/config/main.php';
$config['id'] = 'basic-console';
$config['basePath'] = __DIR__;
$dbConfig = require __DIR__ . '/common/config/main-local.php';
$dbConfig['components']['db']['dsn'] = str_replace('localhost', '127.0.0.1', $dbConfig['components']['db']['dsn']);
$config['components']['db'] = $dbConfig['components']['db'];
$app = new yii\console\Application($config);

$purch = \backend\models\Purch::find()->where(['approve_status' => 1])->orderBy(['id' => SORT_DESC])->one();
if ($purch) {
    echo "ID: " . $purch->id . "\n";
    echo "Approve Status: " . $purch->approve_status . "\n";
    echo "Approve By: " . $purch->approve_by . "\n";
    echo "Has Attribute approve_by: " . ($purch->hasAttribute('approve_by') ? 'Yes' : 'No') . "\n";
} else {
    echo "No approved purch found.\n";
}
