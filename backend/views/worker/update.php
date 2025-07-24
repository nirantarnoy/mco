<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Worker $model */

$this->title = 'แก้ไขข้อมูลลูกจ้าง: ' . $model->fnam.' '.$model->lname;
$this->params['breadcrumbs'][] = ['label' => 'ลูกจ้าง', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->fnam.' '.$model->lname, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="worker-update">
    <?= $this->render('_form', [
        'model' => $model,
        'model_doc' => $model_doc,
    ]) ?>

</div>
