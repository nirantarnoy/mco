<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\PurchReq */

$this->title = 'แก้ไขใบเสนอราคา: ' . $model->quotation_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบเสนอราคา', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->quotation_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="purch-req-update">

<!--    <div class="d-flex justify-content-between align-items-center mb-4">-->
<!--        <h1>--><?php //= Html::encode($this->title) ?><!--</h1>-->
<!--    </div>-->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>