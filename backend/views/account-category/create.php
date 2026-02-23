<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\AccountCategory */

$this->title = 'เพิ่มหมวดบัญชี';
$this->params['breadcrumbs'][] = ['label' => 'จัดการหมวดบัญชี', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-category-create">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
