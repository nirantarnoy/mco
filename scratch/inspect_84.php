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

$rows = (new \yii\db\Query())
    ->from('vehicle_expense')
    ->where(['BINARY(TRIM(job_no))' => 'RY-QT26-000084'])
    ->orderBy(['id' => SORT_ASC])
    ->all();

echo "Total records for RY-QT26-000084: " . count($rows) . "\n";

$sumDistRaw = 0;
$sumDistAbs = 0;
$sumCostRaw = 0;
$sumWageRaw = 0;

foreach ($rows as $i => $r) {
    $dist = (float)$r['total_distance'];
    $cost = (float)$r['vehicle_cost'];
    $wage = (float)$r['total_wage'];
    
    $sumDistRaw += $dist;
    $sumDistAbs += abs($dist);
    $sumCostRaw += $cost;
    $sumWageRaw += $wage;

    if (abs($dist) > 500 || $dist < 0) {
        echo "--> HIGH/NEG ROW ID {$r['id']} | Date: {$r['expense_date']} | Vehicle: {$r['vehicle_no']} | Dist: $dist | Cost: $cost | Wage: $wage\n";
    }
}

echo "\nSUM SUMMARY:\n";
echo "Sum Distance (Raw): $sumDistRaw\n";
echo "Sum Distance (Abs): $sumDistAbs\n";
echo "Sum Cost (Raw): $sumCostRaw\n";
echo "Sum Wage (Raw): $sumWageRaw\n";
