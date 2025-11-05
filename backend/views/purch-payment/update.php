<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\PurchPayment */
/* @var $paymentLines array */

$this->title = 'แก้ไขรายการจ่ายเงิน: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'รายการบันทึกการจ่ายเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="purch-payment-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'paymentLines' => $paymentLines,
    ]) ?>

</div>