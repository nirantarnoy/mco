<?php
require 'e:/xampp/htdocs/mco/vendor/autoload.php';
require 'e:/xampp/htdocs/mco/common/config/bootstrap.php';
require 'e:/xampp/htdocs/mco/backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require 'e:/xampp/htdocs/mco/common/config/main.php',
    require 'e:/xampp/htdocs/mco/common/config/main-local.php',
    require 'e:/xampp/htdocs/mco/backend/config/main.php',
    require 'e:/xampp/htdocs/mco/backend/config/main-local.php'
);

$application = new yii\web\Application($config);
$vendors = \backend\models\Vendor::find()->where(['like', 'code', 'VA'])->orderBy(['id' => SORT_DESC])->limit(5)->all();
foreach($vendors as $v) {
    echo $v->code . PHP_EOL;
}
