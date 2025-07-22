<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Employer $model */

$this->title = 'แก้ไขข้อมูลนายจ้าง: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'นายจ้าง', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="employer-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
