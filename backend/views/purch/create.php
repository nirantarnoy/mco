<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Purch */

$this->title = 'สร้างใบสั่งซื้อใหม่';
$this->params['breadcrumbs'][] = ['label' => 'ใบสั่งซื้อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purch-create">

<!--    <div class="d-flex justify-content-between align-items-center mb-4">-->
<!--        <h1>--><?php //= Html::encode($this->title) ?><!--</h1>-->
<!--    </div>-->

    <?= $this->render('_form', [
        'model' => $model,
        'model_deposit_all' => null,
        'model_deposit_line_all' => null,
    ]) ?>

</div>