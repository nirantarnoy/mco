<?php
use yii\helpers\Html;

$this->title = 'แก้ไข WHT: ' . $model->wht_no;
$this->params['breadcrumbs'][] = ['label' => 'WHT', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->wht_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="wht-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
