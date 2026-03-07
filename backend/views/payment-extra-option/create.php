<?php

use yii\helpers\Html;

$this->title = 'เพิ่มหัวข้อรับเงินอื่นๆ';
$this->params['breadcrumbs'][] = ['label' => 'จัดการหัวข้อรับเงินอื่นๆ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-extra-option-create">
    <div class="card card-success card-outline">
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
