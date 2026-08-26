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

$cleanJobNo = 'RY-QT26-000084';

// Test Query 1 (current code)
$q1 = \backend\models\VehicleExpense::find()
    ->where(['and',
        ['not', ['job_no' => null]],
        ['!=', 'job_no', ''],
        [
            'or',
            ['job_no' => $cleanJobNo],
            ['job_no' => strtoupper($cleanJobNo)],
            ['job_no' => strtolower($cleanJobNo)],
        ]
    ]);

echo "SQL Query 1:\n" . $q1->createCommand()->rawSql . "\n\n";

// Test Query 2 (simple exact match)
$q2 = \backend\models\VehicleExpense::find()
    ->where(['job_no' => $cleanJobNo]);

echo "SQL Query 2:\n" . $q2->createCommand()->rawSql . "\n\n";

// Test Query 3 (trim / binary match)
$q3 = \backend\models\VehicleExpense::find()
    ->where(['BINARY(TRIM(job_no))' => $cleanJobNo]);

echo "SQL Query 3:\n" . $q3->createCommand()->rawSql . "\n\n";
