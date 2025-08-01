<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\ArrayHelper;
use backend\models\Customer;
use backend\models\Invoice;
use backend\models\Unit;

/* @var $this yii\web\View */
/* @var $model backend\models\DebitNote */
/* @var $form yii\widgets\ActiveForm */
/* @var $modelsItem backend\models\DebitNoteItem[] */

$js = '
jQuery(".dynamicform_wrapper").on("afterInsert", function(e, item) {
    jQuery(".dynamicform_wrapper .panel-title-address").each(function(index) {
        jQuery(this).html("รายการ: " + (index + 1))
    });
    
    // Recalculate on new item
    calculateTotal();
});

jQuery(".dynamicform_wrapper").on("afterDelete", function(e) {
    jQuery(".dynamicform_wrapper .panel-title-address").each(function(index) {
        jQuery(this).html("รายการ: " + (index + 1))
    });
    
    // Recalculate after delete
    calculateTotal();
});

// Calculate line total
$(document).on("change", ".quantity, .unit-price", function() {
    var row = $(this).closest(".item");
    var quantity = parseFloat(row.find(".quantity").val()) || 0;
    var unitPrice = parseFloat(row.find(".unit-price").val()) || 0;
    var amount = quantity * unitPrice;
    
    row.find(".amount").val(amount.toFixed(2));
    calculateTotal();
});

// Calculate document total
function calculateTotal() {
    var subtotal = 0;
    $(".amount").each(function() {
        subtotal += parseFloat($(this).val()) || 0;
    });
    
    var vatPercent = parseFloat($("#vat-percent").val()) || 0;
    var vat = subtotal * (vatPercent / 100);
    var total = subtotal + vat;
    
    $("#adjust-amount").val(subtotal.toFixed(2));
    $("#vat-amount").val(vat.toFixed(2));
    $("#total-amount").val(total.toFixed(2));
}

// Load invoice data
$("#invoice-id").on("change", function() {
    var invoiceId = $(this).val();
    if (invoiceId) {
        $.get("' . \yii\helpers\Url::to(['invoice/get-invoice-data']) . '", {id: invoiceId}, function(data) {
            if (data) {
                $("#original-invoice-no").val(data.invoice_number);
                $("#original-invoice-date").val(data.invoice_date);
                $("#original-amount").val(data.total_amount);
                $("#customer-id").val(data.customer_id).trigger("change");
            }
        });
    }
});
';

$this->registerJs($js);
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');

    .debit-note-form {
        font-family: 'Prompt', sans-serif;
    }

    .panel-heading {
        background-color: #f5f5f5;
        padding: 10px 15px;
        border-bottom: 1px solid #ddd;
    }

    .item {
        margin-bottom: 10px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 5px;
    }

    .form-section {
        margin-bottom: 30px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #007bff;
    }
</style>

<div class="debit-note-form">

    <?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ข้อมูลใบเพิ่มหนี้</h3>
        </div>
        <div class="card-body">

            <div class="form-section">
                <h4 class="section-title">ข้อมูลเอกสาร</h4>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'document_no')->textInput(['maxlength' => true, 'readonly' => !$model->isNewRecord]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'document_date')->widget(DatePicker::class, [
                            'options' => ['placeholder' => 'เลือกวันที่...'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd'
                            ]
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'customer_id')->widget(Select2::class, [
                            'data' => ArrayHelper::map(Customer::find()->orderBy('code')->all(), 'id', function($model) {
                                return $model->code . ' - ' . $model->name;
                            }),
                            'options' => [
                                'placeholder' => 'เลือกลูกค้า...',
                                'id' => 'customer-id'
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4 class="section-title">ข้อมูลใบกำกับภาษีเดิม</h4>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'invoice_id')->widget(Select2::class, [
                            'data' => ArrayHelper::map(Invoice::find()->orderBy('invoice_number DESC')->all(), 'id', 'invoice_number'),
                            'options' => [
                                'placeholder' => 'เลือกใบแจ้งหนี้...',
                                'id' => 'invoice-id'
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'original_invoice_no')->textInput(['maxlength' => true, 'id' => 'original-invoice-no']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'original_invoice_date')->widget(DatePicker::class, [
                            'options' => [
                                'placeholder' => 'เลือกวันที่...',
                                'id' => 'original-invoice-date'
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd'
                            ]
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'original_amount')->textInput([
                            'type' => 'number',
                            'step' => '0.01',
                            'id' => 'original-amount'
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'reason')->textarea(['rows' => 3]) ?>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4 class="section-title">รายการสินค้า</h4>

                <?php DynamicFormWidget::begin([
                    'widgetContainer' => 'dynamicform_wrapper',
                    'widgetBody' => '.container-items',
                    'widgetItem' => '.item',
                    'limit' => 20,
                    'min' => 1,
                    'insertButton' => '.add-item',
                    'deleteButton' => '.remove-item',
                    'model' => $modelsItem[0],
                    'formId' => 'dynamic-form',
                    'formFields' => [
                        'description',
                        'quantity',
                        'unit',
                        'unit_price',
                        'amount',
                    ],
                ]); ?>

                <div class="container-items">
                    <?php foreach ($modelsItem as $i => $modelItem): ?>
                        <div class="item panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title pull-left panel-title-address">รายการ: <?= ($i + 1) ?></h3>
                                <div class="pull-right">
                                    <button type="button" class="add-item btn btn-success btn-xs"><i class="fa fa-plus"></i></button>
                                    <button type="button" class="remove-item btn btn-danger btn-xs"><i class="fa fa-minus"></i></button>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body">
                                <?php
                                // necessary for update action.
                                if (! $modelItem->isNewRecord) {
                                    echo Html::activeHiddenInput($modelItem, "[{$i}]id");
                                }
                                ?>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <?= $form->field($modelItem, "[{$i}]description")->textarea(['rows' => 2]) ?>
                                    </div>
                                    <div class="col-sm-2">
                                        <?= $form->field($modelItem, "[{$i}]quantity")->textInput([
                                            'type' => 'number',
                                            'step' => '0.01',
                                            'class' => 'form-control quantity'
                                        ]) ?>
                                    </div>
                                    <div class="col-sm-1">
                                        <?= $form->field($modelItem, "[{$i}]unit")->dropDownList(
                                            ArrayHelper::map(Unit::find()->where(['status' => 1])->all(), 'unit_code', 'unit_name_th'),
                                            ['prompt' => '']
                                        ) ?>
                                    </div>
                                    <div class="col-sm-1">
                                        <?= $form->field($modelItem, "[{$i}]unit_price")->textInput([
                                            'type' => 'number',
                                            'step' => '0.01',
                                            'class' => 'form-control unit-price'
                                        ]) ?>
                                    </div>
                                    <div class="col-sm-2">
                                        <?= $form->field($modelItem, "[{$i}]amount")->textInput([
                                            'type' => 'number',
                                            'step' => '0.01',
                                            'class' => 'form-control amount',
                                            'readonly' => true
                                        ]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php DynamicFormWidget::end(); ?>
            </div>

            <div class="form-section">
                <h4 class="section-title">สรุปยอดเงิน</h4>
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <td>รวมมูลค่าเพิ่มหนี้</td>
                                <td>
                                    <?= $form->field($model, 'adjust_amount')->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'id' => 'adjust-amount',
                                        'readonly' => true
                                    ])->label(false) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="input-group">
                                        <span>ภาษีมูลค่าเพิ่ม</span>
                                        <?= $form->field($model, 'vat_percent')->textInput([
                                            'type' => 'number',
                                            'step' => '0.01',
                                            'id' => 'vat-percent',
                                            'style' => 'width: 60px; display: inline-block; margin-left: 10px;',
                                            'onchange' => 'calculateTotal()'
                                        ])->label(false) ?>
                                        <span>%</span>
                                    </div>
                                </td>
                                <td>
                                    <?= $form->field($model, 'vat_amount')->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'id' => 'vat-amount',
                                        'readonly' => true
                                    ])->label(false) ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>รวมเป็นเงินทั้งสิ้น</strong></td>
                                <td>
                                    <?= $form->field($model, 'total_amount')->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'id' => 'total-amount',
                                        'readonly' => true
                                    ])->label(false) ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer">
            <?= Html::submitButton('<i class="fa fa-save"></i> บันทึก', ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fa fa-times"></i> ยกเลิก', ['index'], ['class' => 'btn btn-danger']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>