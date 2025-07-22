<?php

use toxor88\switchery\Switchery;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Employer $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="employer-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-10">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-10">
            <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-3">
            <?= $form->field($model, 'idcard_no')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'status')->widget(Switchery::className())->label(false) ?>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-3">
            <input type="hidden" name="old_doc" value="<?= $model->doc ?>">
            <?= $form->field($model, 'doc')->fileInput(['maxlength' => true])->label('ไฟล์แนบ ') ?>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-4">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th style="width: 10px;text-align: center">#</th>
                    <th>ชื่อไฟล์</th>
                    <th style="width: 5px;text-align: center">-</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($model->doc != null): ?>
                    <tr>
                        <td style="width: 10px;text-align: center">1</td>
                        <td><?= $model->doc ?></td>
                        <td style="width: 5px;text-align: center">
                            <a href="<?= Yii::$app->request->BaseUrl . '/uploads/aricat/' . $model->doc ?>" target="_blank">
                                filesname
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <br />
    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-10">
            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>
        </div>
        <div class="col-lg-1"></div>
    </div>



    <?php ActiveForm::end(); ?>

</div>
