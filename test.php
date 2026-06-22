<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$app = new \yii\console\Application([
    'id' => 'testapp',
    'basePath' => __DIR__,
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite::memory:',
        ]
    ]
]);

$model = new \backend\models\Wht();
$form = new \yii\widgets\ActiveForm();
echo "HTML:\n";
echo $form->field($model, 'wht_percent')->textInput(['id' => 'wht_percent']);
