<?php
require 'e:/xampp/htdocs/mco/vendor/autoload.php';
require 'e:/xampp/htdocs/mco/common/config/bootstrap.php';
require 'e:/xampp/htdocs/mco/console/config/bootstrap.php';
$config = yii\helpers\ArrayHelper::merge(
    require('e:/xampp/htdocs/mco/common/config/main.php'),
    require('e:/xampp/htdocs/mco/common/config/main-local.php'),
    require('e:/xampp/htdocs/mco/console/config/main.php'),
    require('e:/xampp/htdocs/mco/console/config/main-local.php')
);
$app = new yii\console\Application($config);

$keyword = 'เสื้อ';
$productIds = \backend\models\Product::find()
    ->where(['or', ['like', 'code', $keyword], ['like', 'name', $keyword]])
    ->select('id')
    ->column();
    
$productCodes = \backend\models\Product::find()
    ->where(['or', ['like', 'code', $keyword], ['like', 'name', $keyword]])
    ->select('code')
    ->column();

echo "Found " . count($productIds) . " products matching '$keyword'\n";

if (class_exists('\backend\models\Job')) {
    $jobs = \backend\models\Job::find()->joinWith('jobLines')->where(['in', 'job_line.product_id', $productIds])->count();
    echo "Jobs: $jobs\n";
}
if (class_exists('\backend\models\Quotation')) {
    $quots = \backend\models\Quotation::find()->joinWith('quotationLines')->where(['in', 'quotation_line.product_id', $productIds])->count();
    echo "Quotations: $quots\n";
}
if (class_exists('\backend\models\Purch')) {
    $purchs = \backend\models\Purch::find()->joinWith('purchLines')->where(['in', 'purch_line.product_id', $productIds])->count();
    echo "Purch: $purchs\n";
}
if (class_exists('\backend\models\PurchaseMaster')) {
    $nprs = \backend\models\PurchaseMaster::find()->joinWith('purchaseDetails')->where(['in', 'purchase_detail.stkcod', $productCodes])->count();
    echo "NPRs: $nprs\n";
}
if (class_exists('\backend\models\Orders')) {
    $orders = \backend\models\Orders::find()->joinWith('ordersLines')->where(['in', 'orders_line.product_id', $productIds])->count();
    echo "Orders: $orders\n";
}
if (class_exists('\backend\models\DeliveryNote')) {
    $dns = \backend\models\DeliveryNote::find()->joinWith('deliveryNoteLines')->where(['in', 'delivery_note_line.product_id', $productIds])->count();
    echo "Delivery Notes: $dns\n";
}
if (class_exists('\backend\models\Invoice')) {
    $invs = \backend\models\Invoice::find()->joinWith('invoiceItems')->where(['in', 'invoice_item.product_id', $productIds])->count();
    echo "Invoices: $invs\n";
}
