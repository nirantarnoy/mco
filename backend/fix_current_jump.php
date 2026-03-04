<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/config/main.php'),
    require(__DIR__ . '/config/main-local.php')
);

(new yii\web\Application($config));

// 1. Find the highest "correct" number before the jump.
// Usually users can tell us, but let's assume anything > 400 is the jump based on the screenshot.
$jump_threshold = 400;

$last_correct = Yii::$app->db->createCommand("
    SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED)) 
    FROM purch_req 
    WHERE CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) < :threshold
", [':threshold' => $jump_threshold])->queryScalar();

$next_num = ($last_correct ?: 0) + 1;

echo "Last correct number found: $last_correct\n";
echo "Next number should be: $next_num\n";

// 2. Find records that jumped
$jumped_records = \backend\models\PurchReq::find()
    ->where(['>=', 'CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, "-", 2), "-", -1) AS UNSIGNED)', $jump_threshold])
    ->orderBy(['id' => SORT_ASC])
    ->all();

if (empty($jumped_records)) {
    echo "No jumped records found (>= $jump_threshold).\n";
    exit;
}

echo "Found " . count($jumped_records) . " jumped records. Renumbering...\n";

$transaction = Yii::$app->db->beginTransaction();
try {
    foreach ($jumped_records as $m) {
        $old_no = $m->purch_req_no;
        
        // Extract the original prefix (e.g., PR-00494)
        $parts = explode('-', $old_no);
        if (count($parts) < 2) continue;
        
        $old_prefix = $parts[0] . '-' . $parts[1];
        $new_prefix = 'PR-' . sprintf('%05d', $next_num);
        
        $new_no = str_replace($old_prefix, $new_prefix, $old_no);
        
        echo "Updating PR ID {$m->id}: $old_no -> $new_no\n";
        $m->purch_req_no = $new_no;
        if ($m->save(false)) {
            // Check if converted to PO
            if ($m->purch_id) {
                $po = \backend\models\Purch::findOne($m->purch_id);
                if ($po) {
                    $old_po = $po->purch_no;
                    $old_po_prefix = str_replace('PR', 'PO', $old_prefix);
                    $new_po_prefix = str_replace('PR', 'PO', $new_prefix);
                    $new_po = str_replace($old_po_prefix, $new_po_prefix, $old_po);
                    echo "  Updating PO ID {$po->id}: $old_po -> $new_po\n";
                    $po->purch_no = $new_po;
                    $po->save(false);
                }
            }
            $next_num++;
        }
    }
    $transaction->commit();
    echo "Renumbering completed successfully.\n";
} catch (\Exception $e) {
    $transaction->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
