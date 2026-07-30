<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/backend/config/main.php';
// Need to mock web app or console app
$app = new yii\web\Application($config);

$model = \backend\models\Purch::findOne(754);
if($model) {
    echo "ID: " . $model->id . "\n";
    echo "IS_VAT: " . var_export($model->is_vat, true) . "\n";
} else {
    echo "Model not found";
}
