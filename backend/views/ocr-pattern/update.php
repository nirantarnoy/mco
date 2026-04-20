<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\OcrPattern */

$this->title = 'แก้ไข OCR Pattern: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'จัดการ OCR Pattern', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="ocr-pattern-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
