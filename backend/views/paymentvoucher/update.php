<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\PaymentVoucher */

$this->title = 'แก้ไข Payment Voucher: ' . $model->voucher_no;
$this->params['breadcrumbs'][] = ['label' => 'Payment Voucher', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->voucher_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="payment-voucher-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
