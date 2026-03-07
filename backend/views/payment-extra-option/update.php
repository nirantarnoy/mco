<?php

use yii\helpers\Html;

$this->title = 'แก้ไขหัวข้อรับเงินอื่นๆ: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'จัดการหัวข้อรับเงินอื่นๆ', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="payment-extra-option-update">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>
