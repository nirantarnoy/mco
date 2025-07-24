<?php

use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Job $model */
/** @var yii\widgets\ActiveForm $form */

$model_purch_job = \backend\models\Purch::find()->where(['job_id' => $model->id])->all();
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
            <?php $model->job_date = $model->job_date ? date('m/d/Y', strtotime($model->job_date)) : ''; ?>
            <?= $form->field($model, 'job_date')->widget(DatePicker::className(),
                ['options' =>
                    [
                        'placeholder' => 'เลือกวันที่',
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'mm/dd/yyyy',
                    ]
                ])->label('วันที่') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <?php $model->start_date = $model->start_date ? date('m/d/Y', strtotime($model->start_date)) : ''; ?>
            <?= $form->field($model, 'start_date')->widget(DatePicker::className(),
                ['options' =>
                    [
                        'placeholder' => 'เลือกวันที่',
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'mm/dd/yyyy',
                    ]
                ])->label() ?>
        </div>
        <div class="col-lg-4">
            <?php $model->end_date = $model->end_date ? date('m/d/Y', strtotime($model->end_date)) : ''; ?>
            <?= $form->field($model, 'end_date')->widget(DatePicker::className(),
                ['options' =>
                    [
                        'placeholder' => 'เลือกวันที่',
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'mm/dd/yyyy',
                    ]
                ])->label() ?></div>
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

    <br />
    <div class="row">
        <div class="col-lg-12">
            <label for="">รายการสั่งซื้อสำหรับใบงานนี้</label>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th style="width: 5%;text-align: center">#</th>
                    <th style="width: 20%">เลขที่ใบสั่งซื้อ</th>
                    <th style="width: 15%">วันที่</th>
                    <th style="width: 10%">สถานะ</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($model_purch_job!=null && !$model->isNewRecord): ?>
                    <?php foreach ($model_purch_job as $key => $purch): ?>
                        <?php
                          $puch_x = new \backend\models\Purch();
                        ?>
                        <tr>
                            <td style="text-align: center"><?= $key+1?></td>
                            <td><a href="<?= \yii\helpers\Url::to(['purch/view', 'id' => $purch->id]) ?>"><?= $purch->purch_no ?></a></td>
                            <td><?= $purch->purch_date ? date('m/d/Y', strtotime($purch->purch_date)) : '' ?></td>
                            <td><?=  $puch_x->getApproveStatusBadge($purch->status) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td style="text-align: center"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
