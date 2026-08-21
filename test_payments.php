<?php
require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/common/config/bootstrap.php');
require(__DIR__ . '/backend/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/common/config/main.php'),
    require(__DIR__ . '/common/config/main-local.php'),
    require(__DIR__ . '/backend/config/main.php'),
    require(__DIR__ . '/backend/config/main-local.php')
);
$application = new yii\web\Application($config);

$payments = \backend\models\InvoicePaymentReceipt::find()->with('invoice')->all();
foreach($payments as $p) {
    if($p->invoice) {
        echo "Payment ID {$p->id} for Invoice ID {$p->invoice->id} (Type: {$p->invoice->invoice_type}) Amount: {$p->amount}\n";
    }
}
