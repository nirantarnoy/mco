<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\ArrayHelper;
use backend\models\Quotation;
use backend\models\Product;

/* @var $this yii\web\View */
/* @var $model backend\models\Quotation */
/* @var $form yii\widgets\ActiveForm */

// Register JS for calculations
$this->registerJs("
function calculateLineTotal(index) {
    var qty = parseFloat($('#quotationline-' + index + '-qty').val()) || 0;
    var price = parseFloat($('#quotationline-' + index + '-line_price').val()) || 0;
    var discount = parseFloat($('#quotationline-' + index + '-discount_amount').val()) || 0;
    var subtotal = qty * price;
    var total = subtotal - discount;
    $('#quotationline-' + index + '-line_total').val(total.toFixed(2));
    calculateGrandTotal();
}

function calculateGrandTotal() {
    var total = 0;
    $('.line-total').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    
    $('#quotation-total_amount').val(total.toFixed(2));
    
    // Update summary display
    $('#summary-total').text(total.toFixed(2));
}

$(document).on('change', '.product-select', function() {
    var productId = $(this).val();
    var index = $(this).data('index');
    
    if (productId) {
        $.ajax({
            url: '" . \yii\helpers\Url::to(['get-product-info']) . "',
            type: 'GET',
            data: {id: productId},
            success: function(data) {
                if (data) {
                    $('#quotationline-' + index + '-product_name').val(data.product_name);
                    $('#quotationline-' + index + '-line_price').val(data.price);
                    calculateLineTotal(index);
                }
            }
        });
    }
});

$(document).on('change keyup', '.qty-input, .price-input, .discount-input', function() {
    var index = $(this).data('index');
    calculateLineTotal(index);
});

// Handle dynamic form events
$('.dynamicform_wrapper').on('afterInsert', function(e, item) {
    updateItemNumbers();
    
    // Re-initialize Select2 for new items
    item.find('.product-select').select2({
        allowClear: true,
        width: '100%',
        placeholder: 'เลือกสินค้า...'
    });
    
    // Update data-index attributes
    var index = $('.item').index(item);
    item.find('.product-select').attr('data-index', index);
    item.find('.qty-input').attr('data-index', index);
    item.find('.price-input').attr('data-index', index);
    item.find('.discount-input').attr('data-index', index);
    
    calculateGrandTotal();
});

$('.dynamicform_wrapper').on('afterDelete', function() {
    updateItemNumbers();
    calculateGrandTotal();
});

function updateItemNumbers() {
    $('.item').each(function(index) {
        $(this).find('.item-number').text(index + 1);
    });
}

$(document).ready(function() {
    calculateGrandTotal();
});
");
?>

<div class="quotation-form">

    <?php $form = ActiveForm::begin([
        'id' => 'quotation-form',
        'options' => ['class' => 'form-horizontal'],
    ]); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">ข้อมูลใบเสนอราคา</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'quotation_no')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'ระบบจะสร้างอัตโนมัติหากไม่ระบุ'
                    ]) ?>

                    <?= $form->field($model, 'quotation_date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'เลือกวันที่'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ]
                    ]) ?>

                    <?= $form->field($model, 'customer_name')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'ชื่อลูกค้า'
                    ]) ?>

                    <?= $form->field($model, 'status')->dropDownList([
                        Quotation::STATUS_DRAFT => 'ร่าง',
                        Quotation::STATUS_ACTIVE => 'ใช้งาน',
                        Quotation::STATUS_CANCELLED => 'ยกเลิก',
                    ], ['prompt' => 'เลือกสถานะ']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'approve_status')->dropDownList([
                        Quotation::APPROVE_STATUS_PENDING => 'รอพิจารณา',
                        Quotation::APPROVE_STATUS_APPROVED => 'อนุมัติ',
                        Quotation::APPROVE_STATUS_REJECTED => 'ไม่อนุมัติ',
                    ], ['prompt' => 'เลือกสถานะอนุมัติ']) ?>

                    <?= $form->field($model, 'total_amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'readonly' => true,
                        'style' => 'background-color: #f8f9fa;',
                    ]) ?>

                    <?= $form->field($model, 'note')->textarea([
                        'rows' => 4,
                        'placeholder' => 'หมายเหตุ'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">รายละเอียดสินค้า</h5>
        </div>
        <div class="card-body">
            <?php DynamicFormWidget::begin([
                'widgetContainer' => 'dynamicform_wrapper',
                'widgetBody' => '.container-items',
                'widgetItem' => '.item',
                'limit' => 10,
                'min' => 1,
                'insertButton' => '.add-item',
                'deleteButton' => '.remove-item',
                'model' => $model->quotationLines[0] ?? new \backend\models\QuotationLine(),
                'formId' => 'quotation-form',
                'formFields' => [
                    'product_id',
                    'product_name',
                    'qty',
                    'line_price',
                    'discount_amount',
                    'line_total',
                ],
            ]); ?>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">ลำดับ</th>
                        <th style="width: 200px;">รายการ/สินค้า</th>
                        <th style="width: 100px;">จำนวน</th>
                        <th style="width: 120px;">ราคาต่อหน่วย</th>
                        <th style="width: 100px;">ส่วนลด</th>
                        <th style="width: 120px;">รวมเงิน</th>
                        <th style="width: 50px;">
                            <button type="button" class="btn btn-success btn-sm add-item">
                                <i class="fas fa-plus"></i>
                            </button>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="container-items">
                    <?php if (empty($model->quotationLines)): ?>
                        <?php $model->quotationLines = [new \backend\models\QuotationLine()]; ?>
                    <?php endif; ?>
                    <?php foreach ($model->quotationLines as $index => $quotationLine): ?>
                        <tr class="item">
                            <td class="text-center align-middle">
                                <span class="item-number"><?= $index + 1 ?></span>
                            </td>
                            <td>
                                <?php
                                if (!$quotationLine->isNewRecord) {
                                    echo $form->field($quotationLine, "[$index]id")->hiddenInput()->label(false);
                                }
                                ?>
                                <?= $form->field($quotationLine, "[$index]product_id")->widget(Select2::class, [
                                    'data' => Product::getProductList(),
                                    'options' => [
                                        'placeholder' => 'เลือกสินค้า...',
                                        'class' => 'product-select',
                                        'data-index' => $index,
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'width' => '100%',
                                    ],
                                ])->label(false) ?>
                                <?= $form->field($quotationLine, "[$index]product_name")->textInput([
                                    'placeholder' => 'รายการ/สินค้า',
                                    'maxlength' => true,
                                ])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($quotationLine, "[$index]qty")->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => '0',
                                    'placeholder' => '0',
                                    'class' => 'form-control qty-input',
                                    'data-index' => $index,
                                ])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($quotationLine, "[$index]line_price")->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => '0',
                                    'placeholder' => '0.00',
                                    'class' => 'form-control price-input',
                                    'data-index' => $index,
                                ])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($quotationLine, "[$index]discount_amount")->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => '0',
                                    'placeholder' => '0.00',
                                    'class' => 'form-control discount-input',
                                    'data-index' => $index,
                                ])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($quotationLine, "[$index]line_total")->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => true,
                                    'class' => 'form-control line-total',
                                    'style' => 'background-color: #f8f9fa;',
                                ])->label(false) ?>
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-danger btn-sm remove-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php DynamicFormWidget::end(); ?>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">สรุปยอดเงิน</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8"><strong>ยอดรวมทั้งสิ้น:</strong></div>
                                <div class="col-4 text-end">
                                    <span id="summary-total" class="fw-bold text-primary h5">0.00</span> บาท
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <div class="d-flex justify-content-between">
            <?= Html::submitButton($model->isNewRecord ? 'สร้างใบเสนอราคา' : 'บันทึกการแก้ไข', [
                'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
            ]) ?>
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .item-number {
        font-weight: bold;
        color: #6c757d;
    }

    .dynamicform_wrapper .btn-success {
        margin-right: 5px;
    }

    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>