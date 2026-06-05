<?php
use yii\helpers\Html;

$this->title = 'Create Pre-Advance';
$this->params['breadcrumbs'][] = ['label' => 'Pre-Advances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pre-advance-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
