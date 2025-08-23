<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Currency $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="currency-form">

    <?php $form = ActiveForm::begin(); ?>



    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-10">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'description')->textarea(['maxlength' => true]) ?>

            <?= $form->field($model, 'status')->widget(\toxor88\switchery\Switchery::className())->label(false) ?>


            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

        </div>
        <div class="col-lg-1"></div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
