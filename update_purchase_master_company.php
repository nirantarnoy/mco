<?php
/**
 * Script to update company_id in purchase_master table based on document prefixes.
 * ry-qt -> company_id = 1
 * arc-qt -> company_id = 2
 */

define('YII_DEBUG', true);
define('YII_ENV', 'dev');

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

(new yii\web\Application($config));

use backend\models\PurchaseMaster;

$transaction = Yii::$app->db->beginTransaction();

try {
    echo "Starting update for purchase_master...\n";

    // Update for ry-qt -> company_id = 1
    $ry_count = PurchaseMaster::updateAll(
        ['company_id' => 1],
        ['or', 
            ['like', 'docnum', 'ry-qt'],
            ['like', 'refnum', 'ry-qt'],
            ['like', 'job_no', 'ry-qt']
        ]
    );
    echo "Updated $ry_count records for ry-qt (company_id = 1)\n";

    // Update for arc-qt -> company_id = 2
    $arc_count = PurchaseMaster::updateAll(
        ['company_id' => 2],
        ['or', 
            ['like', 'docnum', 'arc-qt'],
            ['like', 'refnum', 'arc-qt'],
            ['like', 'job_no', 'arc-qt']
        ]
    );
    echo "Updated $arc_count records for arc-qt (company_id = 2)\n";

    $transaction->commit();
    echo "Update completed successfully.\n";

} catch (\Exception $e) {
    $transaction->rollBack();
    echo "Error during update: " . $e->getMessage() . "\n";
}
