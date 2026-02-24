<?php
$config = require(__DIR__ . '/config/main-local.php');
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/config/main.php'),
    require(__DIR__ . '/config/main-local.php')
);

(new yii\web\Application($config));

$prs = [
    'PR-00076' => 84,
    'PR-00077' => 85,
    'PR-00078' => 86,
    'PR-00079' => 87,
];

foreach ($prs as $old_prefix => $new_num) {
    echo "Processing $old_prefix...\n";
    $models = \backend\models\PurchReq::find()->where(['like', 'purch_req_no', $old_prefix . '%', false])->all();
    foreach ($models as $m) {
        $old_no = $m->purch_req_no;
        $new_prefix = 'PR-' . sprintf('%05d', $new_num);
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
        }
    }
}
echo "Done.\n";
