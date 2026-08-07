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
$transaction = $db->beginTransaction();
try {
    $todayPrefix = date('ymd');
    
    // Find highest lot_no for today in stock_sum or journal_trans_line to avoid collision
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
    echo "Updated $updatedCount legacy stock_sum records with new lot numbers.\n";
} catch (\Exception $e) {
    $transaction->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
