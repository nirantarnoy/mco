<?php
require 'vendor/autoload.php';
require 'common/config/bootstrap.php';
require 'backend/config/bootstrap.php';
$config = yii\helpers\ArrayHelper::merge(
    require 'common/config/main.php',
    require 'common/config/main-local.php',
    require 'backend/config/main.php',
    require 'backend/config/main-local.php'
);
$app = new yii\web\Application($config);
$row = (new \yii\db\Query())->from('journal_trans_line')->where('line_price > 0 AND qty > 1')->one();
print_r($row);
