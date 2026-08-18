<?php
use yii\helpers\Html;

$this->title = 'Update Pre-Advance: ' . $model->pre_advance_no;
$this->params['breadcrumbs'][] = ['label' => 'Pre-Advances', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->pre_advance_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pre-advance-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
