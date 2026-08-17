<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\PositionSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="journal-trans-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <div class="row" style="margin-bottom: 10px;">
        <div class="col-lg-3 col-md-4 col-sm-6">
            <?= $form->field($model, 'globalSearch')->textInput(['placeholder' => 'ค้นหา (เลขที่เอกสาร, ลูกค้า, หมายเหตุ)', 'class' => 'form-control'])->label(false) ?>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6">
            <?= $form->field($model, 'trans_type_id')->dropDownList(
                \backend\models\JournalTrans::getTransTypeOptions(),
                [
                    'prompt' => '-- ทุกประเภทกิจกรรม --',
                    'class' => 'form-control',
                    'onchange' => '$(this).closest("form").submit()',
                ]
            )->label(false) ?>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-12">
            <?= Html::submitButton('<i class="fa fa-search"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fa fa-refresh"></i> รีเซ็ต', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
