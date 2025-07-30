<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Paymentterm $model */

$this->title = 'แก้ไขเงื่อนไขชำระเงิน: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'เงื่อนไขชำระเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="paymentterm-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
