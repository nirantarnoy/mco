<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\ChartOfAccount $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="chart-of-account-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6"><?= $form->field($model, 'account_code')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-6"><?= $form->field($model, 'account_name')->textInput(['maxlength' => true]) ?></div>
    </div>

    <div class="row">
        <div class="col-md-4"><?= $form->field($model, 'account_group')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'account_level')->textInput(['type' => 'number']) ?></div>
        <div class="col-md-4">
            <?= $form->field($model, 'account_type')->dropDownList([
                1 => 'คุม (Control)',
                2 => 'ย่อย (Detail)'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'parent_account_id')->widget(\kartik\select2\Select2::class, [
                'data' => \backend\models\ChartOfAccount::getAccountOptions(),
                'options' => ['placeholder' => 'เลือกบัญชีคุม...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'status')->dropDownList([1 => 'ใช้งาน', 0 => 'ไม่ใช้งาน']) ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
