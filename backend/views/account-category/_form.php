<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\AccountCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="account-category-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header">
            <h3 class="card-title">รายละเอียดหมวดบัญชี</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'เช่น 1100, 2017'])->label('รหัสบัญชี') ?>
                </div>
                <div class="col-md-8">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'ชื่อหมวดบัญชี'])->label('ชื่อหมวด') ?>
                </div>
            </div>

            <?= $form->field($model, 'description')->textarea(['rows' => 4, 'placeholder' => 'คำอธิบายเพิ่มเติม'])->label('คำอธิบาย') ?>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'status')->dropDownList([
                        1 => 'Active (ใช้งาน)',
                        0 => 'Inactive (ระงับการใช้งาน)',
                    ])->label('สถานะ') ?>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <?= Html::submitButton('<i class="fas fa-save mr-1"></i> บันทึกข้อมูล', ['class' => 'btn btn-success btn-lg px-5']) ?>
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-secondary btn-lg px-5']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
