<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Customer $model */

$this->title = 'แก้ไขข้อมูลลูกค้า: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'ลูกค้า', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แกไข';
?>
<div class="customer-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
