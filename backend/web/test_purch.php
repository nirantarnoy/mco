<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__ . '/../../common/config/main.php';
$config['id'] = 'basic-web';
$config['basePath'] = __DIR__ . '/../../backend';
$config['components']['db'] = (require __DIR__ . '/../../common/config/main-local.php')['components']['db'];
$config['components']['request'] = ['cookieValidationKey' => 'test'];
$app = new yii\web\Application($config);

$purch = \backend\models\Purch::find()->where(['approve_status' => 1])->orderBy(['id' => SORT_DESC])->one();
if ($purch) {
    echo "ID: " . $purch->id . "\n";
    echo "Approve Status: " . $purch->approve_status . "\n";
    echo "Approve By: " . $purch->approve_by . "\n";
    echo "Has Attribute approve_by: " . ($purch->hasAttribute('approve_by') ? 'Yes' : 'No') . "\n";
} else {
    echo "No approved purch found.\n";
}
