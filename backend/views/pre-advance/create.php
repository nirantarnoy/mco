<?php
use yii\helpers\Html;

$this->title = 'Create Pre-Advance';
$this->params['breadcrumbs'][] = ['label' => 'Pre-Advances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pre-advance-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
