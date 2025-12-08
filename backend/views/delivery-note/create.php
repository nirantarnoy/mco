<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\DeliveryNote */
/* @var $details backend\models\DeliveryNoteLine[] */

$this->title = 'สร้างใบตรวจรับ';
$this->params['breadcrumbs'][] = ['label' => 'ใบตรวจรับ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="delivery-note-create">


    <?= $this->render('_form', [
        'model' => $model,
        'details' => $details,
    ]) ?>

</div>
