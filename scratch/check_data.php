<?php
require 'vendor/autoload.php';
require 'vendor/yiisoft/yii2/Yii.php';
require 'common/config/bootstrap.php';
require 'console/config/bootstrap.php';
$config = yii\helpers\ArrayHelper::merge(
    require 'common/config/main.php',
    require 'common/config/main-local.php',
    require 'console/config/main.php',
    require 'console/config/main-local.php'
);
new yii\console\Application($config);
$results = Yii::$app->db->createCommand('SELECT id, purch_no, company_id FROM purch ORDER BY id DESC LIMIT 5')->queryAll();
echo "Recent POs:\n";
print_r($results);

$resultsReq = Yii::$app->db->createCommand('SELECT id, purch_req_no, company_id FROM purch_req ORDER BY id DESC LIMIT 5')->queryAll();
echo "\nRecent PRs:\n";
print_r($resultsReq);
