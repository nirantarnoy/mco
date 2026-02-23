<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\AccountCategory */

$this->title = 'แก้ไขหมวดบัญชี: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'จัดการหมวดบัญชี', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="account-category-update">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
