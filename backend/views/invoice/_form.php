<?php

use backend\models\Unit;
use yii\helpers\ArrayHelper;
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

// Check if this is a Receipt Copy scenario
$isCopyReceipt = ($model->invoice_type == Invoice::TYPE_RECEIPT && !empty($copy_from));

// ตั้งค่าตัวแปร URLs ก่อน
$getProductInfoUrl = Url::to(['get-product-info']);
$getJobItemsUrl = Url::to(['get-job-items']);
$getCustomerUrl = Url::to(['get-customer']);
$getJobUrl = Url::to(['get-job']);

// เรียง units ตามตัวอักษร
$units = Unit::find()->where(['status' => 1])->all();
usort($units, function ($a, $b) {
    return strcmp($a->name, $b->name);
});

$sortedUnits = ArrayHelper::map($units, 'id', 'name');
$unitsData = json_encode($sortedUnits);

$js = <<<JS
// ตัวแปรเก็บข้อมูลสินค้า
var productsData = [];
var isProductsLoaded = false;

// ตัวแปรเก็บข้อมูล units
var unitsData = {$unitsData} || {};

// ฟังก์ชันสร้าง unit options (เรียงตามตัวอักษร)
function createUnitOptions(selectedUnit) {
    var options = '<option value="">เลือกหน่วย</option>';
    
    // แปลง object เป็น array และเรียงตามชื่อ
    var unitsArray = Object.keys(unitsData).map(function(id) {
        return { id: id, name: unitsData[id] };
    });
    
    // เรียงตามตัวอักษร
    unitsArray.sort(function(a, b) {
        return a.name.localeCompare(b.name);
    });
    
    // สร้าง options
    unitsArray.forEach(function(unit) {
        var selected = (unit.name === selectedUnit || unit.id == selectedUnit) ? 'selected' : '';
        options += '<option value="' + unit.id + '" ' + selected + '>' + unit.name + '</option>';
    });
    
    return options;
}

// ฟังก์ชันโหลดข้อมูลสินค้า
function loadProductsData() {
    if (isProductsLoaded) return;
    
    $.ajax({
        url: '{$getProductInfoUrl}',
        type: 'GET',
        data: { action: 'get-all-products' },
        dataType: 'json',
        success: function(data) {
            productsData = data || [];
            isProductsLoaded = true;
        },
        error: function() {
            console.log('Error loading products data');
            productsData = [];
        }
    });
}

// ฟังก์ชันค้นหาสินค้า
function searchProducts(query) {
    if (!query || query.length < 1) return [];
    
    query = query.toLowerCase();
    return productsData.filter(function(product) {
            }
        } else if (e.keyCode === 38) {
            e.preventDefault();
            if (highlighted.length === 0) {
                items.last().addClass('highlighted');
            } else {
                highlighted.removeClass('highlighted');
                var prev = highlighted.prev('.autocomplete-item');
                if (prev.length) {
                    prev.addClass('highlighted');
                } else {
                    items.last().addClass('highlighted');
                }
            }
        } else if (e.keyCode === 13) {
            e.preventDefault();
            if (highlighted.length) {
                var product = highlighted.data('product');
                selectProduct($(this), product);
            }
        } else if (e.keyCode === 27) {
            dropdown.hide();
        }
    });
}

function showMessage(type, message) {
    var alertClass = 'alert-info';
    switch(type) {
        case 'success': alertClass = 'alert-success'; break;
        case 'error': alertClass = 'alert-danger'; break;
        case 'warning': alertClass = 'alert-warning'; break;
    }
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>';
    $('.invoice-form').prepend(alertHtml);
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

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

$(document).on('click', '.btn-load-job-items', function() {
    var jobId = $('#invoice-job-id').val();
    if (jobId) {
        loadJobItems(jobId);
    } else {
        showMessage('warning', 'กรุณาเลือกใบเสนอราคาก่อน');
    }
});

$(document).on('click', '.autocomplete-item', function() {
    var product = $(this).data('product');
    var dropdown = $(this).closest('.autocomplete-dropdown');
    var row = dropdown.closest('tr');
    var input = row.find('.item-description-input');
    selectProduct(input, product);
});

$(document).on('change', '#invoice-customer_code', function() {
    loadCustomerData($(this).val());
});

$(document).on('change', '#invoice-job-id', function() {
    var jobId = $(this).val();
    if (jobId) {
        $.ajax({
            url: '{$getJobUrl}',
            data: {id: jobId},
            dataType: 'json',
            type: 'POST',
            success: function(data) {
                if (data != null) {
                    $('#invoice-customer-id').val(data[0].customer_id);
                    $('#invoice-customer-name').val(data[0].customer_name);
                    $('#invoice-customer-address').val(cleanAddress(data[0].customer_address));
                    $('#invoice-customer-tax-id').val(data[0].customer_tax_id);
                    $('#invoice-due-date').val(data[0].invoice_due_date);
                }
            }
        });
    } else {
        $('#invoice-job_name').val('');
        $('#invoice-job_address').val('');
        $('#invoice-job_tax_id').val('');
        $('#invoice-due-date').val('');
    }
});

$(document).ready(function() {
    loadProductsData();
    calculateTotal();
    $('.item-description-input').each(function() {
        initializeAutocomplete($(this));
    });
});
JS;

$this->registerJs($js);

$typeLabels = Invoice::getTypeOptions();
$currentTypeLabel = isset($typeLabels[$model->invoice_type]) ? $typeLabels[$model->invoice_type] : 'เอกสาร';
?>

<div class="invoice-form">
    <?php if (\Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

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
                <div class="col-md-4">
                    <?= $form->field($model, 'invoice_number')->textInput([
                        'maxlength' => true,
                        'readonly' => !$model->isNewRecord && !$isCopyReceipt,
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

                    <?= $form->field($model, 'quotation_id')->widget(Select2::class, [
                        'data' => ArrayHelper::map(\backend\models\Quotation::find()->where(\Yii::$app->session->get('company_id') == 1 ? ['in', 'company_id', [1, 2]] : ['company_id' => \Yii::$app->session->get('company_id')])->all(), 'id', 'quotation_no'),
                        'options' => [
                            'placeholder' => '...เลือกใบเสนอราคา...',
                            'id' => 'invoice-job-id',
                            'disabled' => $isCopyReceipt,
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
                        ],
                    ]) ?>
                    <?php if ($isCopyReceipt): ?>
                        <?= Html::activeHiddenInput($model, 'quotation_id') ?>
                    <?php endif; ?>

                    <?= $form->field($model, 'customer_name')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'ชื่อลูกค้า',
                        'id' => 'invoice-customer-name',
                        'readonly' => $isCopyReceipt,
                    ]) ?>

                    <?= $form->field($model, 'customer_address')->textarea([
                        'rows' => 3,
                        'placeholder' => 'ที่อยู่ลูกค้า',
                        'id' => 'invoice-customer-address',
                        'readonly' => $isCopyReceipt,
                    ]) ?>
                </div>
                <div class="col-md-4" style="padding-top: 20px;">
                    <?= $form->field($model, 'customer_tax_id')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'เลขประจำตัวผู้เสียภาษี',
                        'id' => 'invoice-customer-tax-id',
                        'readonly' => $isCopyReceipt,
                    ]) ?>

                    <?= $form->field($model, 'payment_term_id')->widget(
                        Select2::class,
                        [
                            'data' => \yii\helpers\ArrayHelper::map(\backend\models\Paymentterm::find()->all(), 'id', 'name'),
                            'options' => [
                                'placeholder' => 'เลือกเงื่อนไขชําระเงิน...',
                                'id' => 'invoice-payment_term_id',
                                'onchange' => 'calculateDueDate($(this))',
                                'disabled' => $isCopyReceipt,
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]
                    ) ?>
                    <?php if ($isCopyReceipt): ?>
                        <?= Html::activeHiddenInput($model, 'payment_term_id') ?>
                    <?php endif; ?>

                    <?= $form->field($model, 'due_date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'วันครบกำหนดชำระ', 'id' => 'invoice-due-date', 'readonly' => true],
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
                <div class="col-md-4" style="padding-top: 20px;">
                    <?= $form->field($model, 'po_number')->textInput(['maxlength' => true, 'placeholder' => 'เลขที่ใบสั่งซื้อ', 'readonly' => $isCopyReceipt]) ?>

                    <?= $form->field($model, 'po_date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'วันที่ใบสั่งซื้อ', 'readonly' => $isCopyReceipt],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                        ]
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <?= $form->field($model, 'special_note')->textarea([
                        'rows' => 3,
                        'placeholder' => 'บันทึกอื่นๆ'
                    ]) ?>
                </div>
            </div>
                <button type="button" class="btn btn-sm btn-info btn-load-job-items me-2">
                    <i class="fas fa-download"></i> โหลดจากใบงาน
                </button>
                <button type="button" class="btn btn-sm btn-primary btn-add-item">
                    <i class="fas fa-plus"></i> เพิ่มรายการ
                </button>
                <?php endif; ?>
            </div>
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
                                    <?= Html::hiddenInput("InvoiceItem[{$index}][product_id]", $item->product_id, [
                                        'class' => 'product-id-input'
                                    ]) ?>
                                    <?php
                                    // ถ้ามี product_id ให้ดึงรหัสสินค้ามาแสดง ถ้าไม่มีให้แสดง item_description เดิม
                                    $displayValue = $item->product_id
                                        ? \backend\models\Product::findCode($item->product_id)
                                        : $item->item_description;
                                    ?>
                                    <?= Html::textInput("InvoiceItem[{$index}][item_description]", $displayValue, [
                                        'class' => 'form-control form-control-sm item-description-input',
                                        'placeholder' => 'รายละเอียดสินค้า/บริการ',
                                        'autocomplete' => 'off',
                                        'readonly' => $isCopyReceipt,
                                    ]) ?>
                                    <div class="autocomplete-dropdown"></div>
                                </td>
                                <td>
                                    <?= Html::textInput("InvoiceItem[{$index}][quantity]", $item->quantity ?: '1', [
                                        'class' => 'form-control form-control-sm quantity-input text-right',
                                        'type' => 'number',
                                        'step' => '0.001',
                                        'min' => '0',
                                        'readonly' => $isCopyReceipt,
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::dropDownList(
                                        "InvoiceItem[{$index}][unit_id]",
                                        $item->unit_id,
                                        $sortedUnits,
                                        [
                                            'prompt' => 'เลือกหน่วย',
                                            'class' => 'form-control form-control-sm text-center',
                                            'disabled' => $isCopyReceipt,
                                        ]
                                    ) ?>
                                    <?php if ($isCopyReceipt): ?>
                                        <?= Html::hiddenInput("InvoiceItem[{$index}][unit_id]", $item->unit_id) ?>
                                    <?php endif; ?>
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
                                    <?php if (!$isCopyReceipt): ?>
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-item"
                                        title="ลบรายการ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
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
                            <?= Html::hiddenInput('Invoice[subtotal]', $model->subtotal, ['id' => 'invoice-subtotal-hidden']) ?>
                            <?= $form->field($model, 'subtotal')->textInput([
                                'type' => 'text',
                                'readonly' => true,
                                'disabled' => true,
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
                                'class' => 'form-control text-right',
                                'readonly' => $isCopyReceipt,
                            ]) ?>
                        </div>
                        <div class="col-sm-6">
                            <?= Html::hiddenInput('Invoice[discount_amount]', $model->discount_amount, ['id' => 'invoice-discount_amount-hidden']) ?>
                            <?= $form->field($model, 'discount_amount')->textInput([
                                'type' => 'text',
                                'readonly' => true,
                                'disabled' => true,
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
                                'class' => 'form-control text-right',
                                'readonly' => $isCopyReceipt,
                            ]) ?>
                        </div>
                        <div class="col-sm-6">
                            <?= Html::hiddenInput('Invoice[vat_amount]', $model->vat_amount, ['id' => 'invoice-vat_amount-hidden']) ?>
                            <?= $form->field($model, 'vat_amount')->textInput([
                                'type' => 'text',
    display: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.autocomplete-item {
    padding: 0.5rem;
    cursor: pointer;
    border-bottom: 1px solid #e9ecef;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item:hover, .autocomplete-item.highlighted {
    background-color: #f8f9fa;
}

.product-code {
    color: #666;
    font-size: 12px;
}

.autocomplete-item.highlighted .product-code {
    color: #e9ecef;
}

td {
    position: relative;
}

/* ป้องกันปัญหา overflow ของ table */
.table-responsive {
    overflow: visible !important;
}

.table {
    overflow: visible !important;
}

.card-body {
    overflow: visible !important;
}

/* เพิ่ม z-index สำหรับ table cell ที่มี autocomplete */
td:has(.autocomplete-dropdown) {
    z-index: 1000;
    position: relative;
}
</style>

<?php
$url_to_get_payment_term_day = Url::to(['invoice/get-payment-term-day'], true);
$script = <<< JS
function delete_doc(e){
    var file_name = e.attr('data-var');
    if(file_name != null){
        $(".delete-doc-list").val(file_name);
        $("#form-delete-doc-file").submit();
    }
}

function calculateDueDate(e){
    var id = $(e).val();
    if(id!=null || id!=''){
        $.ajax({
            url: '$url_to_get_payment_term_day',
            type: 'post',
            dataType: 'html',
            data: {id: id},
            success: function(data){
                 //alert(data);
                var startDate = new Date();
                var dueDate = new Date(startDate); // copy date object
                dueDate.setDate(dueDate.getDate() + parseInt(data)); // เพิ่มจำนวนวัน
                $("#invoice-due-date").val(
                    dueDate.getFullYear() + "-" + 
                    ("0" + (dueDate.getMonth() + 1)).slice(-2) + "-" + 
                    ("0" + dueDate.getDate()).slice(-2)
                );
            },
            error: function(data){
                console.log(data);
            }
            });
    }
    
   
}
JS;
$this->registerJs($script, static::POS_END);
?>