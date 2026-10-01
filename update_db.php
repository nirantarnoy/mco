<?php
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

$db = Yii::$app->db;

// Add columns
try {
    $db->createCommand("ALTER TABLE `purch` ADD COLUMN `is_ap_closed` TINYINT(1) DEFAULT 0")->execute();
    echo "Added is_ap_closed to purch.\n";
} catch (\Exception $e) {
    echo "Column is_ap_closed might already exist in purch.\n";
}

try {
    $db->createCommand("ALTER TABLE `purchase_master` ADD COLUMN `is_ap_closed` TINYINT(1) DEFAULT 0")->execute();
    echo "Added is_ap_closed to purchase_master.\n";
} catch (\Exception $e) {
    echo "Column is_ap_closed might already exist in purchase_master.\n";
}

// Mark existing RED items as closed
$slaDays = \backend\models\SystemSetting::findOne(['setting_key' => 'ap_sla_days']);
$max_sla_days = $slaDays ? (int)$slaDays->setting_value : 3;

// Purch
$pos = \backend\models\Purch::find()->where(['status' => \backend\models\Purch::STATUS_COMPLETED, 'is_ap_closed' => 0])->all();
foreach ($pos as $po) {
    $has_pv = false;
    $has_paid = false;
    $has_draft = false;
    $refs = \backend\models\PaymentVoucherRef::find()
        ->alias('r')
        ->innerJoin('payment_voucher pv', 'r.payment_voucher_id = pv.id')
        ->where(['r.ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PO, 'r.ref_id' => $po->id])
        ->andWhere(['!=', 'pv.status', \backend\models\PaymentVoucher::STATUS_CANCELLED])
        ->all();
        
    foreach ($refs as $ref) {
        $has_pv = true;
        if ($ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_COMPLETED || $ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_ACTIVE) { 
            $has_paid = true;
        }
        if ($ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_DRAFT) {
            $has_draft = true;
        }
    }
    
    if (!$has_paid && !$has_draft) {
        // Red condition
        $po->is_ap_closed = 1;
        $po->save(false);
        echo "Closed PO: {$po->purch_no}\n";
    }
}

// PurchaseMaster
$nprs = \backend\models\PurchaseMaster::find()->where(['!=', 'status', \backend\models\PurchaseMaster::STATUS_CANCELLED])->andWhere(['is_ap_closed' => 0])->all();
foreach ($nprs as $npr) {
    if ($npr->getReceiveStatus() == \backend\models\PurchaseMaster::RECEIVE_STATUS_COMPLETED) {
        $has_pv = false;
        $has_paid = false;
        $has_draft = false;
        $refs = \backend\models\PaymentVoucherRef::find()
            ->alias('r')
            ->innerJoin('payment_voucher pv', 'r.payment_voucher_id = pv.id')
            ->where(['r.ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_NONE_PR, 'r.ref_id' => $npr->id])
            ->andWhere(['!=', 'pv.status', \backend\models\PaymentVoucher::STATUS_CANCELLED])
            ->all();
            
        foreach ($refs as $ref) {
            $has_pv = true;
            if ($ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_COMPLETED || $ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_ACTIVE) { 
                $has_paid = true;
            }
            if ($ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_DRAFT) {
                $has_draft = true;
            }
        }
        
        if (!$has_paid && !$has_draft) {
            // Red condition
            $npr->is_ap_closed = 1;
            $npr->save(false);
            echo "Closed NPR: {$npr->docnum}\n";
        }
    }
}

echo "Done.\n";
