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
$db = Yii::$app->db;

echo "--- STARTING PRODUCTION UPDATE ---\n\n";

// 1. Schema Updates
echo "[1] Updating Database Schema...\n";
$tables = [
    'journal_trans_line' => 'ALTER TABLE journal_trans_line ADD COLUMN lot_no VARCHAR(50) DEFAULT NULL COMMENT "Lot No."',
    'stock_trans' => 'ALTER TABLE stock_trans ADD COLUMN lot_no VARCHAR(50) DEFAULT NULL COMMENT "Lot No."',
    'stock_sum' => 'ALTER TABLE stock_sum ADD COLUMN lot_no VARCHAR(50) DEFAULT NULL COMMENT "Lot No."'
];

foreach ($tables as $table => $sql) {
    try {
        $db->createCommand($sql)->execute();
        echo "  - Added lot_no to {$table}\n";
    } catch (\Exception $e) { 
        echo "  - Warning ({$table}): " . $e->getMessage() . "\n"; 
    }
}

// 2. Index Updates
echo "\n[2] Updating Unique Indexes on stock_sum...\n";
try {
    $db->createCommand('ALTER TABLE stock_sum DROP INDEX product_id')->execute();
} catch (\Exception $e) { }

try {
    $db->createCommand('ALTER TABLE stock_sum DROP INDEX product_id_2')->execute();
} catch (\Exception $e) { }

try {
    $db->createCommand('CREATE UNIQUE INDEX idx_stock_sum_unique ON stock_sum (product_id, warehouse_id, lot_no)')->execute();
    echo "  - Created new unique index (product_id, warehouse_id, lot_no) on stock_sum\n";
} catch (\Exception $e) { 
    echo "  - Warning (Index): " . $e->getMessage() . "\n"; 
}

// 3. Legacy Data Update
echo "\n[3] Updating Legacy stock_sum records...\n";
$transaction = $db->beginTransaction();
try {
    $todayPrefix = date('ymd');
    
    // Find highest lot_no for today to avoid collision
    $lastLot = $db->createCommand("SELECT MAX(lot_no) as max_lot FROM stock_sum WHERE lot_no LIKE '{$todayPrefix}%'")->queryScalar();
    $nextSeq = 1;
    if ($lastLot) {
        $lastSeq = substr($lastLot, 6);
        if (is_numeric($lastSeq)) {
            $nextSeq = intval($lastSeq) + 1;
        }
    }
    
    // Find all legacy stock_sum records
    $legacyStocks = $db->createCommand("SELECT id FROM stock_sum WHERE lot_no IS NULL OR lot_no = ''")->queryAll();
    
    $updatedCount = 0;
    foreach ($legacyStocks as $stock) {
        $lotNo = $todayPrefix . sprintf('%04d', $nextSeq);
        $nextSeq++;
        
        $db->createCommand("UPDATE stock_sum SET lot_no = :lot_no WHERE id = :id")
            ->bindValue(':lot_no', $lotNo)
            ->bindValue(':id', $stock['id'])
            ->execute();
        $updatedCount++;
    }
    
    $transaction->commit();
    echo "  - Successfully updated {$updatedCount} legacy stock_sum records.\n";
} catch (\Exception $e) {
    $transaction->rollBack();
    echo "  - Error updating legacy data: " . $e->getMessage() . "\n";
}

echo "\n--- PRODUCTION UPDATE COMPLETED ---\n";
