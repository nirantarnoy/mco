<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\PurchPayment */
/* @var $paymentLines array */

$this->title = 'สร้างรายการจ่ายเงิน';
$this->params['breadcrumbs'][] = ['label' => 'รายการบันทึกการจ่ายเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purch-payment-create">

    <?= $this->render('_form', [
        'model' => $model,
        'paymentLines' => $paymentLines,
    ]) ?>

</div>