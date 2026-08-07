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
try {
    $db = Yii::$app->db;
    
    // Add lot_no to journal_trans_line
    try {
        $db->createCommand('ALTER TABLE journal_trans_line ADD COLUMN lot_no VARCHAR(50) DEFAULT NULL COMMENT "Lot No."')->execute();
        echo "Added lot_no to journal_trans_line\n";
    } catch (\Exception $e) { echo $e->getMessage() . "\n"; }

    // Add lot_no to stock_trans
    try {
        $db->createCommand('ALTER TABLE stock_trans ADD COLUMN lot_no VARCHAR(50) DEFAULT NULL COMMENT "Lot No."')->execute();
        echo "Added lot_no to stock_trans\n";
    } catch (\Exception $e) { echo $e->getMessage() . "\n"; }

    // Add lot_no to stock_sum
    try {
        $db->createCommand('ALTER TABLE stock_sum ADD COLUMN lot_no VARCHAR(50) DEFAULT NULL COMMENT "Lot No."')->execute();
        echo "Added lot_no to stock_sum\n";
    } catch (\Exception $e) { echo $e->getMessage() . "\n"; }

    // Drop old unique index and add new unique index
    try {
        // Try dropping if named differently, or get index name
        // Typically it's unique constraints on product_id and warehouse_id
        $db->createCommand('ALTER TABLE stock_sum DROP INDEX product_id')->execute();
    } catch (\Exception $e) { echo "Warning: " . $e->getMessage() . "\n"; }
    
    try {
        $db->createCommand('ALTER TABLE stock_sum DROP INDEX product_id_2')->execute();
    } catch (\Exception $e) { echo "Warning: " . $e->getMessage() . "\n"; }

    // Re-create the unique index with lot_no
    try {
        $db->createCommand('CREATE UNIQUE INDEX idx_stock_sum_unique ON stock_sum (product_id, warehouse_id, lot_no)')->execute();
        echo "Created new unique index on stock_sum\n";
    } catch (\Exception $e) { echo $e->getMessage() . "\n"; }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
