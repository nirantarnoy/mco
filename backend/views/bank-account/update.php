<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\BankAccount $model */

$this->title = 'แก้ไขบัญชีธนาคาร: ' . $model->bank_name;
$this->params['breadcrumbs'][] = ['label' => 'บัญชีธนาคาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->bank_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="bank-account-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
