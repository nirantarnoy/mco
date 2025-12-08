<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\DeliveryNote */
/* @var $details backend\models\DeliveryNoteLine[] */

$this->title = 'แก้ไขใบส่งของ: ' . $model->dn_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบส่งของ', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->dn_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="delivery-note-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'details' => $details,
    ]) ?>

</div>
