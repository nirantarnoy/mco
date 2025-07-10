<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\PurchReq */

$this->title = 'สร้างใบเสนอราคาใหม่';
$this->params['breadcrumbs'][] = ['label' => 'ใบเสนอราคา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purch-req-create">

<!--    <div class="d-flex justify-content-between align-items-center mb-4">-->
<!--        <h1>--><?php //= Html::encode($this->title) ?><!--</h1>-->
<!--    </div>-->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>