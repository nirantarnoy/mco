<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\PaymentVoucher */

$this->title = 'สร้างรายการ Payment Voucher';
$this->params['breadcrumbs'][] = ['label' => 'Payment Voucher', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-voucher-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
