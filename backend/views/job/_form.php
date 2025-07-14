<?php

use kartik\date\DatePicker;
use kartik\select2\Select2;
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
            <?= $form->field($model, 'quotation_id')->widget(Select2::className(),
                [
                    'data' => \yii\helpers\ArrayHelper::map(\backend\models\Quotation::find()->all(), 'id', 'quotation_no'),
                    'language' => 'th',
                    'options' => ['placeholder' => 'เลือกใบเสนอราคา'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ]
                ])->label('เลขที่ใบเสนอราคา') ?>
        </div>
        <div class="col-lg-4">
            <?php $model->job_date = $model->job_date ? date('d-m-Y', strtotime($model->job_date)) : ''; ?>
            <?= $form->field($model, 'job_date')->widget(DatePicker::className(),
                ['options' =>
                    [
                        'placeholder' => 'เลือกวันที่',
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd-mm-yyyy',
                    ]
                ])->label('วันที่') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <label for="">สถานะ</label>
            <?= \backend\models\Job::getJobStatusBadge($model->status) ?>
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
