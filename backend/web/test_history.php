<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../common/env.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../../backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../../backend/config/main.php',
    require __DIR__ . '/../../backend/config/main-local.php'
);

(new yii\web\Application($config));

use backend\models\InvoicePaymentHistory;

$history = new InvoicePaymentHistory();
$history->invoice_id = 1;
$history->receipt_id = 2;
$history->amount = 1000;
$history->payment_date = date('Y-m-d');
$history->company_id = 1;

if (!$history->validate()) {
    echo "Validation errors:\n";
    print_r($history->getErrors());
} else {
    echo "Validation passed\n";
}

$recent = InvoicePaymentHistory::find()->orderBy(['id' => SORT_DESC])->limit(5)->asArray()->all();
echo "Recent history:\n";
print_r($recent);
