<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\ChartOfAccountSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="chart-of-account-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'account_code') ?>

    <?= $form->field($model, 'account_name') ?>

    <?= $form->field($model, 'account_group') ?>

    <?= $form->field($model, 'account_level') ?>

    <?php // echo $form->field($model, 'account_type') ?>

    <?php // echo $form->field($model, 'parent_account_id') ?>

    <?php // echo $form->field($model, 'company_id') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
