<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Currency $model */

$this->title = 'แก้ไขสกุลเงิน: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'สกุลเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="currency-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
