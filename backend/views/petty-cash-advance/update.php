<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Position */

$this->title = 'แก้ไขเบิกเงินทดแทน: ' . $model->advance_no;
$this->params['breadcrumbs'][] = ['label' => 'เบิกเงินทดแทน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->advance_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="position-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
