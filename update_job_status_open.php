<?php
/**
 * Script to update Job status from 'Unspecified / NULL / 0' to 'Open' (1)
 */

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    php_sapi_name() === 'cli' 
        ? require __DIR__ . '/console/config/main.php'
        : require __DIR__ . '/backend/config/main.php'
);

if (php_sapi_name() === 'cli') {
    new yii\console\Application($config);
} else {
    new yii\web\Application($config);
}

$db = Yii::$app->db;

echo "==================================================\n";
echo " Job Status Update Script (Unspecified -> Open)\n";
echo " Date: " . date('Y-m-d H:i:s') . "\n";
echo "==================================================\n\n";

// Find jobs with status NULL, 0, or not in (1, 2, 3)
$unspecifiedJobs = (new \yii\db\Query())
    ->from('job')
    ->where(['or', 
        ['status' => null],
        ['status' => 0],
        ['not in', 'status', [1, 2, 3]]
    ])
    ->all();

if (empty($unspecifiedJobs)) {
    echo "ไม่พบรายการใบงานที่มีสถานะ 'ไม่ระบุ' ในระบบ\n";
    exit(0);
}

echo "พบรายการใบงานที่มีสถานะ 'ไม่ระบุ' จำนวน " . count($unspecifiedJobs) . " รายการ:\n";
echo str_repeat('-', 60) . "\n";

foreach ($unspecifiedJobs as $job) {
    $statusText = ($job['status'] === null) ? 'NULL' : (string)$job['status'];
    echo "ID: {$job['id']} | Job No: {$job['job_no']} | Current Status: {$statusText} | Date: {$job['job_date']}\n";
}

echo str_repeat('-', 60) . "\n";
echo "กำลังดำเนินการอัปเดตสถานะเป็น Open (status = 1)...\n";

$updatedCount = $db->createCommand()
    ->update('job', ['status' => 1], [
        'or',
        ['status' => null],
        ['status' => 0],
        ['not in', 'status', [1, 2, 3]]
    ])
    ->execute();

echo "\nอัปเดตสถานะใบงานเป็น Open (1) สำเร็จเรียบร้อยจำนวน {$updatedCount} รายการ!\n";
echo "==================================================\n";
