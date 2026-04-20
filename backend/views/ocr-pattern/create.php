<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\OcrPattern */

$this->title = 'เพิ่ม OCR Pattern ใหม่';
$this->params['breadcrumbs'][] = ['label' => 'จัดการ OCR Pattern', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ocr-pattern-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
