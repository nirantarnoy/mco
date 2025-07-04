<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\ArrayHelper;
use backend\models\PurchReq;
use backend\models\Product;

/* @var $this yii\web\View */
/* @var $model backend\models\PurchReq */
/* @var $form yii\widgets\ActiveForm */

// Register JS for calculations
$this->registerJs("
function calculateLineTotal(index) {
    var qty = parseFloat($('#purchreqline-' + index + '-qty').val()) || 0;
    var price = parseFloat($('#purchreqline-' + index + '-line_price').val()) || 0;
    var total = qty * price;
    $('#purchreqline-' + index + '-line_total').val(total.toFixed(2));
    calculateGrandTotal();
}

function calculateGrandTotal() {
    var subtotal = 0;
    $('.line-total').each(function() {
        subtotal += parseFloat($(this).val()) || 0;
    });
    
    var discount = parseFloat($('#purch_req-discount_amount').val()) || 0;
    var afterDiscount = subtotal - discount;
    var vat = afterDiscount * 0.07; // 7% VAT
    var netAmount = afterDiscount + vat;
    
    $('#purch_req-total_amount').val(subtotal.toFixed(2));
    $('#purch_req-vat_amount').val(vat.toFixed(2));
    $('#purch_req-net_amount').val(netAmount.toFixed(2));
    
    // Update summary display
    $('#summary-subtotal').text(subtotal.toFixed(2));
    $('#summary-discount').text(discount.toFixed(2));
    $('#summary-vat').text(vat.toFixed(2));
    $('#summary-net').text(netAmount.toFixed(2));
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
                    $('#purchreqline-' + index + '-product_name').val(data.product_name);
                    $('#purchreqline-' + index + '-line_price').val(data.price);
                    $('#purchreqline-' + index + '-unit').val(data.unit);
                    calculateLineTotal(index);
                }
            }
        });
    }
});

$(document).on('change keyup', '.qty-input, .price-input', function() {
    var index = $(this).data('index');
    calculateLineTotal(index);
});

$(document).on('change keyup', '#purch_req-discount_amount', function() {
    calculateGrandTotal();
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

<div class="purch-req-form">

    <?php $form = ActiveForm::begin([
        'id' => 'purch-req-form',
        'options' => ['class' => 'form-horizontal'],
    ]); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">ข้อมูลใบขอซื้อ</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'purch_req_no')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'ระบบจะสร้างอัตโนมัติหากไม่ระบุ'
                    ]) ?>

                    <?= $form->field($model, 'purch_req_date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'เลือกวันที่'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ]
                    ]) ?>

                    <?= $form->field($model, 'vendor_name')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'ชื่อผู้ขาย'
                    ]) ?>

                    <?= $form->field($model, 'status')->dropDownList([
                        PurchReq::STATUS_DRAFT => 'ร่าง',
                        PurchReq::STATUS_ACTIVE => 'ใช้งาน',
                        PurchReq::STATUS_CANCELLED => 'ยกเลิก',
                    ], ['prompt' => 'เลือกสถานะ']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'approve_status')->dropDownList([
                        PurchReq::APPROVE_STATUS_PENDING => 'รอพิจารณา',
                        PurchReq::APPROVE_STATUS_APPROVED => 'อนุมัติ',
                        PurchReq::APPROVE_STATUS_REJECTED => 'ไม่อนุมัติ',
                    ], ['prompt' => 'เลือกสถานะอนุมัติ']) ?>

                    <?= $form->field($model, 'purch_id')->textInput([
                        'type' => 'number',
                        'placeholder' => 'รหัสใบสั่งซื้อ (ถ้ามี)'
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
                'model' => $model->purchReqLines[0] ?? new \backend\models\PurchReqLine(),
                'formId' => 'purch-req-form',
                'formFields' => [
                    'product_id',
                    'product_name',
                   // 'product_description',
                    'qty',
                    'line_price',
                    //'unit',
                    'line_total',
                ],
            ]); ?>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">ลำดับ</th>
                        <th style="width: 200px;">ชื่อสินค้า</th>
<!--                        <th style="width: 200px;">รายละเอียด</th>-->
                        <th style="width: 100px;">จำนวน</th>
<!--                        <th style="width: 120px;">ราคาต่อหน่วย</th>-->
                        <th style="width: 80px;">หน่วยนับ</th>
                        <th style="width: 120px;">ราคารวม</th>
                        <th style="width: 50px;">
                            <button type="button" class="btn btn-success btn-sm add-item">
                                <i class="fas fa-plus"></i>
                            </button>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="container-items">
                    <?php if (empty($model->purchReqLines)): ?>
                        <?php $model->purchReqLines = [new \backend\models\PurchReqLine()]; ?>
                    <?php endif; ?>
                    <?php foreach ($model->purchReqLines as $index => $purchReqLine): ?>
                        <tr class="item">
                            <td class="text-center align-middle">
                                <span class="item-number"><?= $index + 1 ?></span>
                            </td>
                            <td>
                                <?php
                                if (!$purchReqLine->isNewRecord) {
                                    echo $form->field($purchReqLine, "[$index]id")->hiddenInput()->label(false);
                                }
                                ?>
                                <?= $form->field($purchReqLine, "[$index]product_id")->widget(Select2::class, [
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
                            </td>
<!--                            <td>-->
<!--                                --><?php //= $form->field($purchReqLine, "[$index]product_description")->textInput([
//                                    'placeholder' => 'รายละเอียด',
//                                    'maxlength' => true,
//                                ])->label(false) ?>
<!--                            </td>-->
                            <td>
                                <?= $form->field($purchReqLine, "[$index]qty")->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => '0',
                                    'placeholder' => '0',
                                    'class' => 'form-control qty-input',
                                    'data-index' => $index,
                                ])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($purchReqLine, "[$index]line_price")->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => '0',
                                    'placeholder' => '0.00',
                                    'class' => 'form-control price-input',
                                    'data-index' => $index,
                                ])->label(false) ?>
                            </td>
<!--                            <td>-->
<!--                                --><?php //= $form->field($purchReqLine, "[$index]unit")->textInput([
//                                    'placeholder' => 'หน่วย',
//                                    'maxlength' => true,
//                                ])->label(false) ?>
<!--                            </td>-->
                            <td>
                                <?= $form->field($purchReqLine, "[$index]line_total")->textInput([
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
                <div class="col-md-6">
                    <!-- Left side for form inputs -->
                    <?= $form->field($model, 'total_amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'readonly' => true,
                        'style' => 'background-color: #f8f9fa;',
                    ]) ?>

                    <?= $form->field($model, 'discount_amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'placeholder' => '0.00',
                    ]) ?>

                    <?= $form->field($model, 'vat_amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'readonly' => true,
                        'style' => 'background-color: #f8f9fa;',
                    ]) ?>

                    <?= $form->field($model, 'net_amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'readonly' => true,
                        'style' => 'background-color: #f8f9fa;',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <!-- Right side for summary display -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">สรุปยอดเงิน</h6>
                            <div class="row mb-2">
                                <div class="col-8">ยอดรวม:</div>
                                <div class="col-4 text-end">
                                    <span id="summary-subtotal" class="fw-bold">0.00</span> บาท
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-8">ส่วนลด:</div>
                                <div class="col-4 text-end">
                                    <span id="summary-discount" class="fw-bold">0.00</span> บาท
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-8">VAT (7%):</div>
                                <div class="col-4 text-end">
                                    <span id="summary-vat" class="fw-bold">0.00</span> บาท
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-8"><strong>ยอดรวมสุทธิ:</strong></div>
                                <div class="col-4 text-end">
                                    <span id="summary-net" class="fw-bold text-primary h5">0.00</span> บาท
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
            <?= Html::submitButton($model->isNewRecord ? 'สร้างใบขอซื้อ' : 'บันทึกการแก้ไข', [
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