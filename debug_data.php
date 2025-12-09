<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';
require __DIR__ . '/backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/backend/config/main.php',
    require __DIR__ . '/backend/config/main-local.php'
);

$app = new yii\web\Application($config);

echo "Querying data for Oct-Dec 2025...\n";

$sql = "SELECT 
            v.id as v_id, 
            v.pcv_no, 
            v.date as v_date, 
            v.status, 
            d.id as d_id, 
            d.detail_date, 
            d.amount 
        FROM petty_cash_detail d
        INNER JOIN petty_cash_voucher v ON d.voucher_id = v.id
        WHERE (v.date >= '2025-10-01' AND v.date <= '2025-12-31') 
           OR (d.detail_date >= '2025-10-01' AND d.detail_date <= '2025-12-31')
        ORDER BY d.detail_date DESC
        LIMIT 50";

$rows = Yii::$app->db->createCommand($sql)->queryAll();

if (empty($rows)) {
    echo "No records found.\n";
} else {
    printf("%-5s | %-10s | %-12s | %-6s | %-12s | %-10s\n", "V_ID", "PCV_NO", "V_DATE", "STATUS", "D_DATE", "AMOUNT");
    echo str_repeat("-", 70) . "\n";
    foreach ($rows as $row) {
        printf("%-5d | %-10s | %-12s | %-6d | %-12s | %-10s\n", 
            $row['v_id'], 
            $row['pcv_no'], 
            $row['v_date'], 
            $row['status'], 
            $row['detail_date'], 
            $row['amount']
        );
    }
}
