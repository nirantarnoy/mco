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

$jobs = (new \yii\db\Query())
    ->from('job j')
    ->select(['j.id', 'j.job_no', 'j.job_amount'])
    ->all();

echo "Checking all " . count($jobs) . " jobs:\n";
foreach($jobs as $j) {
    $jobObj = \backend\models\Job::findOne($j['id']);
    if ($jobObj) {
        $controller = new \backend\controllers\ExecutiveDashboardController('executive-dashboard', Yii::$app);
        try {
            $eval = $controller->evaluateJobStepStatuses($jobObj);
            if ($eval['metrics']['jobKmTotal'] > 10000 || stristr($jobObj->job_no, '000084') !== false) {
                echo "Match Job ID: {$jobObj->id} | JobNo: '{$jobObj->job_no}' | KmTotal: {$eval['metrics']['jobKmTotal']} | Cost: {$eval['metrics']['jobVehicleCost']} | Wage: {$eval['metrics']['jobVehicleWage']}\n";
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
