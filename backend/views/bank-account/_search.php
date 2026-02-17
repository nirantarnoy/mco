<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\BankAccountSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="bank-account-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <div class="row">
        <div class="col-lg-4">
             <?= $form->field($model, 'globalSearch')->textInput(['placeholder' => 'ค้นหา...'])->label(false) ?>
        </div>
        <div class="col-lg-2">
            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-search"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
                <?= Html::resetButton('รีเซ็ต', ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
