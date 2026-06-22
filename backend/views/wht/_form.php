<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use backend\models\Vendor;

/* @var $this yii\web\View */
/* @var $model backend\models\Wht */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="wht-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'wht_type')->radioList([
                3 => 'ภ.ง.ด. 3 (บุคคลธรรมดา)',
                53 => 'ภ.ง.ด. 53 (นิติบุคคล)'
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'pay_condition')->dropDownList([
                1 => '(1) หัก ณ ที่จ่าย',
                2 => '(2) ออกภาษีให้ตลอดไป',
                3 => '(3) ออกภาษีให้ครั้งเดียว',
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'vendor_id')->widget(Select2::className(), [
                'data' => ArrayHelper::map(Vendor::find()->all(), 'id', function($m) {
                    return $m->name . ' (' . $m->taxid . ')';
                }),
                'options' => ['placeholder' => 'เลือกผู้ถูกหักภาษี...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'trans_date')->textInput(['type' => 'date']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'wht_desc')->dropDownList([
                'ค่าขนส่ง' => 'ค่าขนส่ง',
                'ค่าเบี้ยประกันวินาศภัย' => 'ค่าเบี้ยประกันวินาศภัย',
                'ค่าจ้างทำของ/ค่าบริการ' => 'ค่าจ้างทำของ/ค่าบริการ',
                'ค่าโฆษณา' => 'ค่าโฆษณา',
                'ค่าเช่า' => 'ค่าเช่า',
                'อื่นๆ' => 'อื่นๆ (ระบุ)'
            ], ['prompt' => '-- เลือกประเภทเงินได้ --']) ?>
        </div>
        <div class="col-md-8">
            <?= $form->field($model, 'other_desc')->textInput(['maxlength' => true, 'placeholder' => 'ระบุประเภทเงินได้อื่นๆ (ถ้ามี)']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'base_amount')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'wht_percent')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'wht_amount')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
    </div>

    <?= $form->field($model, 'ref_type')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'ref_id')->hiddenInput()->label(false) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('บันทึกข้อมูล', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
function calculateWht() {
    var base = parseFloat($('#wht-base_amount').val()) || 0;
    var percent = parseFloat($('#wht-wht_percent').val()) || 0;
    var amount = (base * percent) / 100;
    $('#wht-wht_amount').val(amount.toFixed(2));
}

$('#wht-base_amount, #wht-wht_percent').on('input', function() {
    calculateWht();
});

$('#wht-wht_desc').on('change', function() {
    var val = $(this).val();
    var percent = 0;
    if (val === 'ค่าขนส่ง') percent = 1;
    else if (val === 'ค่าเบี้ยประกันวินาศภัย') percent = 1;
    else if (val === 'ค่าโฆษณา') percent = 2;
    else if (val === 'ค่าจ้างทำของ/ค่าบริการ') percent = 3;
    else if (val === 'ค่าเช่า') percent = 5;
    
    if (percent > 0) {
        $('#wht-wht_percent').val(percent);
        calculateWht();
    }
});
JS;
$this->registerJs($js);
?>
