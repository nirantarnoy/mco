<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use backend\models\Invoice;

/* @var $this yii\web\View */
/* @var $model backend\models\Invoice */
/* @var $items backend\models\InvoiceItem[] */
/* @var $customers array */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("
// Function to calculate item amount
function calculateItemAmount(row) {
    var quantity = parseFloat(row.find('.quantity-input').val()) || 0;
    var unitPrice = parseFloat(row.find('.unit-price-input').val()) || 0;
    
    var amount = quantity * unitPrice;
    row.find('.amount-input').val(amount.toFixed(3));
    
    calculateTotal();
}

// Function to calculate total
function calculateTotal() {
    var subtotal = 0;
    $('.amount-input').each(function() {
        subtotal += parseFloat($(this).val()) || 0;
    });
    
    $('#invoice-subtotal').val(subtotal.toFixed(2));
    
    // Calculate discount
    var discountPercent = parseFloat($('#invoice-discount_percent').val()) || 0;
    var discountAmount = subtotal * (discountPercent / 100);
    $('#invoice-discount_amount').val(discountAmount.toFixed(2));
    
    var afterDiscount = subtotal - discountAmount;
    
    // Calculate VAT
    var vatPercent = parseFloat($('#invoice-vat_percent').val()) || 0;
    var vatAmount = afterDiscount * (vatPercent / 100);
    $('#invoice-vat_amount').val(vatAmount.toFixed(2));
    
    var totalAmount = afterDiscount + vatAmount;
    $('#invoice-total_amount').val(totalAmount.toFixed(2));
}

// Add new item row
function addItemRow() {
    var rowIndex = $('#items-table tbody tr').length;
    
    var newRowHtml = `
    <tr>
        <td class=\"text-center\">` + (rowIndex + 1) + `</td>
        <td>
            <textarea name=\"InvoiceItem[` + rowIndex + `][item_description]\" class=\"form-control form-control-sm\" rows=\"2\" placeholder=\"รายละเอียดสินค้า/บริการ\"></textarea>
        </td>
        <td>
            <input type=\"number\" name=\"InvoiceItem[` + rowIndex + `][quantity]\" class=\"form-control form-control-sm quantity-input text-right\" step=\"0.001\" min=\"0\" value=\"1.000\">
        </td>
        <td>
            <input type=\"text\" name=\"InvoiceItem[` + rowIndex + `][unit]\" class=\"form-control form-control-sm text-center\" value=\"หน่วย\">
        </td>
        <td>
            <input type=\"number\" name=\"InvoiceItem[` + rowIndex + `][unit_price]\" class=\"form-control form-control-sm unit-price-input text-right\" step=\"0.001\" min=\"0\" value=\"0.000\">
        </td>
        <td>
            <input type=\"number\" name=\"InvoiceItem[` + rowIndex + `][amount]\" class=\"form-control form-control-sm amount-input text-right\" readonly style=\"background-color: #f8f9fa;\" value=\"0.000\">
        </td>
        <td class=\"text-center\">
            <button type=\"button\" class=\"btn btn-sm btn-danger btn-remove-item\" title=\"ลบรายการ\">
                <i class=\"fas fa-trash\"></i>
            </button>
        </td>
    </tr>`;
    
    $('#items-table tbody').append(newRowHtml);
}

// Remove item row
function removeItemRow(button) {
    var rowCount = $('#items-table tbody tr').length;
    if (rowCount > 1) {
        $(button).closest('tr').remove();
        
        // Re-index rows
        $('#items-table tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
            $(this).find('input, textarea, select').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
        
        calculateTotal();
    } else {
        alert('ต้องมีรายการอย่างน้อย 1 รายการ');
    }
}

// Load customer data
function loadCustomerData(customerCode) {
    if (customerCode) {
        $.ajax({
            url: '" . Url::to(['get-customer']) . "',
            data: {code: customerCode},
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#invoice-customer_name').val(response.data.customer_name);
                    $('#invoice-customer_address').val(response.data.customer_address);
                    $('#invoice-customer_tax_id').val(response.data.customer_tax_id);
                    $('#invoice-credit_terms').val(response.data.credit_terms);
                }
            }
        });
    }
}

// Event handlers
$(document).on('input', '.quantity-input, .unit-price-input', function() {
    calculateItemAmount($(this).closest('tr'));
});

$(document).on('input', '#invoice-discount_percent, #invoice-vat_percent', function() {
    calculateTotal();
});

$(document).on('click', '.btn-add-item', function() {
    addItemRow();
});

$(document).on('click', '.btn-remove-item', function() {
    removeItemRow(this);
});

$(document).on('change', '#invoice-customer_code', function() {
    loadCustomerData($(this).val());
});

// Initialize calculations on page load
$(document).ready(function() {
    calculateTotal();
});
");

$typeLabels = Invoice::getTypeOptions();
$currentTypeLabel = isset($typeLabels[$model->invoice_type]) ? $typeLabels[$model->invoice_type] : 'เอกสาร';
?>

    <div class="invoice-form">

        <?php $form = ActiveForm::begin([
            'id' => 'invoice-form',
            'options' => ['class' => 'form-horizontal'],
        ]); ?>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice"></i> ข้อมูล<?= $currentTypeLabel ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'invoice_type')->hiddenInput(['value' => $model->invoice_type])->label(false) ?>

                        <?= $form->field($model, 'invoice_number')->textInput([
                            'maxlength' => true,
                            'readonly' => !$model->isNewRecord,
                            'placeholder' => 'จะสร้างอัตโนมัติ'
                        ]) ?>

                        <?= $form->field($model, 'invoice_date')->widget(DatePicker::class, [
                            'options' => ['placeholder' => 'เลือกวันที่'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ]
                        ]) ?>

                        <?= $form->field($model, 'customer_code')->widget(Select2::class, [
                            'data' => $customers,
                            'options' => [
                                'placeholder' => 'เลือกลูกค้า...',
                                'id' => 'invoice-customer_code'
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
                            ],
                        ]) ?>

                        <?= $form->field($model, 'customer_name')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'ชื่อลูกค้า'
                        ]) ?>

                        <?= $form->field($model, 'customer_address')->textarea([
                            'rows' => 3,
                            'placeholder' => 'ที่อยู่ลูกค้า'
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'customer_tax_id')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'เลขประจำตัวผู้เสียภาษี'
                        ]) ?>

                        <?= $form->field($model, 'po_number')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'เลขที่ใบสั่งซื้อ'
                        ]) ?>

                        <?= $form->field($model, 'po_date')->widget(DatePicker::class, [
                            'options' => ['placeholder' => 'วันที่ใบสั่งซื้อ'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ]
                        ]) ?>

                        <?= $form->field($model, 'credit_terms')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'เช่น 30 DAYS, COD'
                        ]) ?>

                        <?= $form->field($model, 'due_date')->widget(DatePicker::class, [
                            'options' => ['placeholder' => 'วันครบกำหนดชำระ'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ]
                        ]) ?>

                        <?php if ($model->invoice_type == Invoice::TYPE_BILL_PLACEMENT): ?>
                            <?= $form->field($model, 'payment_due_date')->widget(DatePicker::class, [
                                'options' => ['placeholder' => 'วันนัดชำระเงิน'],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                            ]) ?>

                            <?= $form->field($model, 'check_due_date')->widget(DatePicker::class, [
                                'options' => ['placeholder' => 'วันนัดรับเช็ค'],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list"></i> รายการสินค้า/บริการ
                </h5>
                <button type="button" class="btn btn-sm btn-primary btn-add-item">
                    <i class="fas fa-plus"></i> เพิ่มรายการ
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="items-table" class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                        <tr>
                            <th width="5%">ลำดับ</th>
                            <th width="35%">รายการ</th>
                            <th width="10%">จำนวน</th>
                            <th width="10%">หน่วย</th>
                            <th width="15%">ราคาต่อหน่วย</th>
                            <th width="15%">จำนวนเงิน</th>
                            <th width="10%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td>
                                    <?= Html::textarea("InvoiceItem[{$index}][item_description]", $item->item_description, [
                                        'class' => 'form-control form-control-sm',
                                        'rows' => 2,
                                        'placeholder' => 'รายละเอียดสินค้า/บริการ'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("InvoiceItem[{$index}][quantity]", $item->quantity ?: '1.000', [
                                        'class' => 'form-control form-control-sm quantity-input text-right',
                                        'type' => 'number',
                                        'step' => '0.001',
                                        'min' => '0'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("InvoiceItem[{$index}][unit]", $item->unit ?: 'หน่วย', [
                                        'class' => 'form-control form-control-sm text-center'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("InvoiceItem[{$index}][unit_price]", $item->unit_price ?: '0.000', [
                                        'class' => 'form-control form-control-sm unit-price-input text-right',
                                        'type' => 'number',
                                        'step' => '0.001',
                                        'min' => '0'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("InvoiceItem[{$index}][amount]", $item->amount ?: '0.000', [
                                        'class' => 'form-control form-control-sm amount-input text-right',
                                        'readonly' => true,
                                        'style' => 'background-color: #f8f9fa;'
                                    ]) ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-item" title="ลบรายการ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calculator"></i> สรุปยอดเงิน
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'notes')->textarea([
                            'rows' => 4,
                            'placeholder' => 'หมายเหตุเพิ่มเติม'
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-sm-12">
                                <?= $form->field($model, 'subtotal')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => true,
                                    'class' => 'form-control text-right',
                                    'style' => 'background-color: #f8f9fa;'
                                ]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <?= $form->field($model, 'discount_percent', [
                                    'template' => '{label}<div class="input-group"><div class="input-group-prepend"><span class="input-group-text">%</span></div>{input}</div>{error}'
                                ])->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => '0',
                                    'max' => '100',
                                    'class' => 'form-control text-right'
                                ]) ?>
                            </div>
                            <div class="col-sm-6">
                                <?= $form->field($model, 'discount_amount')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => true,
                                    'class' => 'form-control text-right',
                                    'style' => 'background-color: #f8f9fa;'
                                ]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <?= $form->field($model, 'vat_percent', [
                                    'template' => '{label}<div class="input-group"><div class="input-group-prepend"><span class="input-group-text">%</span></div>{input}</div>{error}'
                                ])->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => '0',
                                    'max' => '100',
                                    'class' => 'form-control text-right'
                                ]) ?>
                            </div>
                            <div class="col-sm-6">
                                <?= $form->field($model, 'vat_amount')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'readonly' => true,
                                    'class' => 'form-control text-right',
                                    'style' => 'background-color: #f8f9fa;'
                                ]) ?>
                            </div>
                        </div>

                        <?= $form->field($model, 'total_amount')->textInput([
                            'type' => 'number',
                            'step' => '0.01',
                            'readonly' => true,
                            'class' => 'form-control text-right font-weight-bold',
                            'style' => 'background-color: #e3f2fd; font-size: 16px; border: 2px solid #2196f3;'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mt-4">
            <div class="text-center">
                <?= Html::submitButton($model->isNewRecord ? '<i class="fas fa-save"></i> บันทึก' : '<i class="fas fa-save"></i> แก้ไข', [
                    'class' => $model->isNewRecord ? 'btn btn-success btn-lg' : 'btn btn-primary btn-lg'
                ]) ?>
                <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['index'], ['class' => 'btn btn-secondary btn-lg']) ?>
                <?php if (!$model->isNewRecord): ?>
                    <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                        'class' => 'btn btn-info btn-lg',
                        'target' => '_blank'
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

<?php
$this->registerCss("
.invoice-form .card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.invoice-form .card-header {
    background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    border-bottom: 1px solid #dee2e6;
}

.invoice-form .table th {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    font-weight: 600;
    font-size: 13px;
}

.invoice-form .form-control-sm {
    font-size: 13px;
}

.invoice-form .btn-lg {
    padding: 12px 30px;
    font-size: 16px;
}

.invoice-form .input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
    font-weight: 600;
}

#invoice-total_amount {
    font-size: 18px !important;
    font-weight: bold !important;
}
");
?>