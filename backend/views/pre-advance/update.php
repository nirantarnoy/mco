<?php
use yii\helpers\Html;

$this->title = 'Update Pre-Advance: ' . $model->pre_advance_no;
$this->params['breadcrumbs'][] = ['label' => 'Pre-Advances', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->pre_advance_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pre-advance-update">
    
    <?php if (Yii::$app->session->hasFlash('update-sync-msg')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert" style="font-size: 16px;">
            <i class="fas fa-exclamation-triangle"></i> <?= Yii::$app->session->getFlash('update-sync-msg') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
