<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

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

(new yii\console\Application($config));

$prs = [
    'PR-00076-' => 84,
    'PR-00077-' => 85,
    'PR-00078-' => 86,
    'PR-00079-' => 87,
];

echo "Searching for PRs to update...\n";
foreach ($prs as $old_prefix => $new_num) {
    echo "Prefix: $old_prefix\n";
    $models = \backend\models\PurchReq::find()->where(['like', 'purch_req_no', $old_prefix . '%', false])->all();
    if (empty($models)) {
        echo "  No records found for $old_prefix\n";
        continue;
    }

    foreach ($models as $m) {
        $old_no = $m->purch_req_no;
        $new_prefix = 'PR-' . sprintf('%05d', $new_num) . '-';
        $new_no = str_replace($old_prefix, $new_prefix, $old_no);
        
        echo "Found PR ID {$m->id}: $old_no -> $new_no\n";
        
        // Transaction for safety
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $m->purch_req_no = $new_no;
            if ($m->save(false)) {
                echo "  SUCCESS: Updated PR #{$m->id}\n";
                
                // Check if converted to PO
                // Based on model, relation might be via purch_id
                if ($m->purch_id) {
                    $po = \backend\models\Purch::findOne($m->purch_id);
                    if ($po) {
                        $old_po = $po->purch_no;
                        $old_po_prefix = str_replace('PR', 'PO', $old_prefix);
                        $new_po_prefix = str_replace('PR', 'PO', $new_prefix);
                        $new_po = str_replace($old_po_prefix, $new_po_prefix, $old_po);
                        
                        echo "  Found PO ID {$po->id}: $old_po -> $new_po\n";
                        $po->purch_no = $new_po;
                        if ($po->save(false)) {
                             echo "  SUCCESS: Updated PO #{$po->id}\n";
                        } else {
                             echo "  ERROR: Failed to update PO #{$po->id}\n";
                             $transaction->rollBack();
                             continue 2;
                        }
                    }
                }
                $transaction->commit();
            } else {
                echo "  ERROR: Failed to update PR #{$m->id}\n";
                $transaction->rollBack();
            }
        } catch (\Exception $e) {
            echo "  EXCEPTION: " . $e->getMessage() . "\n";
            $transaction->rollBack();
        }
    }
}
echo "Done.\n";
