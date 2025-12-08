<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\DeliveryNote */
/* @var $details backend\models\DeliveryNoteLine[] */

$this->title = 'แก้ไขใบตรวจรับ: ' . $model->dn_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบตรวจรับ', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->dn_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="delivery-note-update">


    <?= $this->render('_form', [
        'model' => $model,
        'details' => $details,
    ]) ?>

</div>
