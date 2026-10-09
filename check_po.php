<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';
require __DIR__ . '/console/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/console/config/main.php',
    require __DIR__ . '/console/config/main-local.php'
);

$application = new yii\console\Application($config);

$sql = "SELECT id, purch_no, vendor_name, approve_date, approve_by 
        FROM purch 
        WHERE approve_status = 1 
        AND (approve_date IS NULL OR approve_by IS NULL OR approve_date = '' OR approve_by = 0)";

$results = Yii::$app->db->createCommand($sql)->queryAll();

if (empty($results)) {
    echo "ไม่พบ PO ที่มี approve_status = 1 แต่ไม่มี approve_date หรือ approve_by\n";
} else {
    echo "พบข้อมูลทั้งหมด " . count($results) . " รายการ ดังนี้:\n";
    echo str_pad("ID", 10) . " | " . str_pad("PO No", 20) . " | " . "Vendor Name\n";
    echo str_repeat("-", 60) . "\n";
    foreach ($results as $row) {
        echo str_pad($row['id'], 10) . " | " . str_pad($row['purch_no'], 20) . " | " . $row['vendor_name'] . "\n";
    }
}
