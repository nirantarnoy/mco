<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Agency $model */

$this->title = 'แก้ไขข้อมูลหน่วยงาน: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'หน่วยงานราชการ', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="agency-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
