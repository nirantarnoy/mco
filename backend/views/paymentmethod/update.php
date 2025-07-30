<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Paymentmethod $model */

$this->title = 'แก้ไขวิธีชำระเงิน: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'วิธีชำระเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="paymentmethod-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
