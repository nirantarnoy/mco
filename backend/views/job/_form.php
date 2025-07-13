<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Job $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="job-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'job_no')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-4">
            <?= $form->field($model, 'quotation_id')->textInput() ?>
        </div>
        <div class="col-lg-4">
            <?= $form->field($model, 'job_date')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <label for="">สถานะ</label>
            <?= \backend\models\Job::getJobStatusBadge($model->status)?>
        </div>
        <div class="col-lg-4">
            <?= $form->field($model, 'job_amount')->textInput() ?>
        </div>
    </div>

    <?= $form->field($model, 'status')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
