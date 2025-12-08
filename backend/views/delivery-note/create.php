<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\DeliveryNote */
/* @var $details backend\models\DeliveryNoteLine[] */

$this->title = 'สร้างใบส่งของ';
$this->params['breadcrumbs'][] = ['label' => 'ใบส่งของ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="delivery-note-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'details' => $details,
    ]) ?>

</div>
