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

// กรองเฉพาะรายการที่เป็น QT26 (เพื่อให้ไม่ไปยุ่งกับ QT25 ที่มี 493)
$target_year = 'QT26';

// หาเลขที่ถูกต้องล่าสุดของ QT26
$last_correct = Yii::$app->db->createCommand("
    SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED)) 
    FROM purch_req 
    WHERE (company_id IN (1, 2, 0) OR company_id IS NULL)
    AND purch_req_no LIKE 'PR-%-{$target_year}-%'
    AND CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) < 400
")->queryScalar();

$next_num = ($last_correct ?: 0) + 1;

echo "Target Year: $target_year\n";
echo "Last correct number for $target_year: $last_correct\n";
echo "Repairing will start from: $next_num\n";

// ดึงรายการที่กระโดดเฉพาะของ QT26
$jumped_records = \backend\models\PurchReq::find()
    ->where(['like', 'purch_req_no', "PR-%-{$target_year}-%"])
    ->andWhere(['>=', 'CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, "-", 2), "-", -1) AS UNSIGNED)', 400])
    ->orderBy(['id' => SORT_ASC])
    ->all();

if (empty($jumped_records)) {
    echo "No jumped records found for $target_year.\n";
    exit;
}

$transaction = Yii::$app->db->beginTransaction();
try {
    foreach ($jumped_records as $m) {
        $old_no = $m->purch_req_no;
        $parts = explode('-', $old_no);
        if (count($parts) < 2) continue;

        $old_prefix = $parts[0] . '-' . $parts[1];
        $new_prefix = 'PR-' . sprintf('%05d', $next_num);
        $new_no = str_replace($old_prefix, $new_prefix, $old_no);

        echo "Updating ID {$m->id}: $old_no -> $new_no\n";
        $m->purch_req_no = $new_no;
        if ($m->save(false)) {
            if ($m->purch_id) {
                $po = \backend\models\Purch::findOne($m->purch_id);
                if ($po) {
                    $old_po_prefix = str_replace('PR', 'PO', $old_prefix);
                    $new_po_prefix = str_replace('PR', 'PO', $new_prefix);
                    $po->purch_no = str_replace($old_po_prefix, $new_po_prefix, $po->purch_no);
                    $po->save(false);
                    echo "  -> Updated PO: {$po->purch_no}\n";
                }
            }
            $next_num++;
        }
    }
    $transaction->commit();
    echo "Repair completed successfully for $target_year.\n";
} catch (\Exception $e) {
    $transaction->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
