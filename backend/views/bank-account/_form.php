<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\BankAccount $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="bank-account-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card card-default">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <?= $form->field($model, 'bank_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-lg-6">
                    <?= $form->field($model, 'account_no')->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?= $form->field($model, 'account_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-lg-6">
                    <?= $form->field($model, 'branch')->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?= $form->field($model, 'status')->dropDownList([1 => 'ใช้งาน', 0 => 'ไม่ใช้งาน']) ?>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
                <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-danger']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
