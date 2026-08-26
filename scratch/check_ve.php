<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../common/config/bootstrap.php';
require __DIR__ . '/../backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php',
    require __DIR__ . '/../backend/config/main.php',
    require __DIR__ . '/../backend/config/main-local.php'
);

$application = new yii\web\Application($config);

$jobNo = 'RY-QT26-000084';
$rows = (new \yii\db\Query())->from('vehicle_expense')->where(['job_no' => $jobNo])->all();

echo "Records for '$jobNo': " . count($rows) . "\n";
foreach($rows as $r) {
    echo "ID: {$r['id']} | JobNo: {$r['job_no']} | VehicleNo: {$r['vehicle_no']} | Dist: {$r['total_distance']} | Cost: {$r['vehicle_cost']} | Wage: {$r['total_wage']}\n";
}
