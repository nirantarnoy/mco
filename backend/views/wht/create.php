<?php
use yii\helpers\Html;

$this->title = 'สร้างรายการ หัก ณ ที่จ่าย (WHT)';
$this->params['breadcrumbs'][] = ['label' => 'WHT', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wht-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
