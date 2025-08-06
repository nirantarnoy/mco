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

$getProductInfoUrl = Url::to(['get-product-info']);
$getJobItemsUrl = Url::to(['get-job-items']);
$getCustomerUrl = Url::to(['get-customer']);
$getJobUrl = Url::to(['get-job']);


// ตั้งค่าตัวแปร URLs ก่อน
$getProductInfoUrl = Url::to(['get-product-info']);
$getJobItemsUrl = Url::to(['get-job-items']);
$getCustomerUrl = Url::to(['get-customer']);
$getJobUrl = Url::to(['get-job']);

$unitsData = json_encode(ArrayHelper::map(Unit::find()->where(['status' => 1])->all(), 'id', 'name'));

$js = <<<JS
// ตัวแปรเก็บข้อมูลสินค้า
var productsData = [];
var isProductsLoaded = false;

// ฟังก์ชันโหลดข้อมูลสินค้า
function loadProductsData() {
    if (isProductsLoaded) return;
    
    $.ajax({
        url: '{$getProductInfoUrl}',
        type: 'GET',
        data: { action: 'get-all-products' },
        dataType: 'json',
        success: function(data) {
            console.log('Products loaded:', data);
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
        var description = (product.description || product.item_description || '').toLowerCase();
        var code = (product.code || product.item_code || '').toLowerCase();
        var name = (product.name || product.item_name || '').toLowerCase();
        
        return description.includes(query) || 
               code.includes(query) ||
               name.includes(query);
    }).slice(0, 10);
}

// ฟังก์ชันแสดงผลลัพธ์
function showAutocompleteResults(input, results) {
    var row = input.closest('tr');
    var dropdown = row.find('.autocomplete-dropdown');
    
    dropdown.empty();
    
    if (results.length === 0) {
        dropdown.hide();
        return;
    }
    
    results.forEach(function(product) {
        var description = product.description || product.item_description || product.name || 'ไม่ระบุ';
        var code = product.code || product.item_code || '';
        
        var item = $('<div class="autocomplete-item">')
            .html('<div>' + description + '</div>' + 
                  (code ? '<div class="product-code">' + code + '</div>' : ''))
            .data('product', product);
        dropdown.append(item);
    });
    
    dropdown.show();
}

// ฟังก์ชันซ่อน dropdown
function hideAutocomplete(row) {
    setTimeout(function() {
        row.find('.autocomplete-dropdown').hide();
    }, 200);
}

// ฟังก์ชันเลือกสินค้า
function selectProduct(input, product) {
    var row = input.closest('tr');
    
    // อัพเดตค่า
    var description = product.description || product.item_description || product.name || '';
    input.val(description);
    
    // อัพเดต product_id
    var productId = product.id || product.product_id || '';
    row.find('.product-id-input').val(productId);
    
    // อัพเดตราคา
    var price = product.unit_price || product.price || product.item_price || '0.000';
    row.find('.unit-price-input').val(price);
    
    // อัพเดตหน่วย
    var unit = product.unit || product.item_unit || 'หน่วย';
    row.find('input[name*="[unit_id]"]').val(unit);
    
    // ซ่อน dropdown
    row.find('.autocomplete-dropdown').hide();
    
    // คำนวณยอดรวม
    calculateItemAmount(row);
}

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

// ตัวแปรเก็บข้อมูลหน่วย (โหลดจาก PHP)
var unitsData = {$unitsData};

// ฟังก์ชันสร้าง options สำหรับ unit dropdown
function createUnitOptions(selectedUnit = "") {
    var options = "<option value=\"\">เลือกหน่วย</option>";
    for (var unitCode in unitsData) {
        var selected = (unitCode === selectedUnit) ? "selected" : "";
        options += "<option value=\"" + unitCode + "\" " + selected + ">" + unitsData[unitCode] + "</option>";
    }
    return options;
}
// ฟังก์ชันสร้าง unit options (ต้องเพิ่มฟังก์ชันนี้)
// function createUnitOptions(selectedUnit = '') {
//     // ตัวอย่างข้อมูลหน่วย - ควรโหลดจากฐานข้อมูล
//     var units = {
//         '': 'เลือกหน่วย',
//         'piece': 'ชิ้น',
//         'box': 'กล่อง',
//         'kg': 'กิโลกรัม',
//         'meter': 'เมตร',
//         'liter': 'ลิตร'
//     };
//    
//     var options = '';
//     for (var value in units) {
//         var selected = (value === selectedUnit) ? 'selected' : '';
//         options += '<option value="' + value + '" ' + selected + '>' + units[value] + '</option>';
//     }
//    
//     return options;
// }

// Add new item row
function addItemRow(itemData = null) {
    // ลบแถวล่าสุดก่อน
    $('#items-table tbody tr:first').remove();

    // คำนวณ rowIndex หลังจากลบแถวล่าสุด
    var rowIndex = $('#items-table tbody tr').length;

    var description = itemData ? itemData.item_description || '' : '';
    var quantity = itemData ? itemData.quantity || '1.000' : '1.000';
    var unit = itemData ? itemData.unit || '' : '';
    var unitPrice = itemData ? itemData.unit_price || '0.000' : '0.000';
    var amount = itemData ? itemData.amount || '0.000' : '0.000';
    var productId = itemData ? itemData.product_id || '' : '';

    var newRowHtml = `
    <tr>
        <td class="text-center">` + (rowIndex + 1) + `</td>
        <td>
            <input type="hidden" name="InvoiceItem[` + rowIndex + `][product_id]" class="product-id-input" value="` + productId + `">
            <input type="text" name="InvoiceItem[` + rowIndex + `][item_description]" class="form-control form-control-sm item-description-input" placeholder="รายละเอียดสินค้า/บริการ" autocomplete="off" value="` + description + `">
            <div class="autocomplete-dropdown"></div>
        </td>
        <td>
            <input type="number" name="InvoiceItem[` + rowIndex + `][quantity]" class="form-control form-control-sm quantity-input text-right" step="0.001" min="0" value="` + quantity + `">
        </td>
        <td>
            <select name="InvoiceItem[` + rowIndex + `][unit_id]" class="form-control form-control-sm">
                ` + createUnitOptions(unit) + `
            </select>
        </td>
        <td>
            <input type="number" name="InvoiceItem[` + rowIndex + `][unit_price]" class="form-control form-control-sm unit-price-input text-right" step="0.001" min="0" value="` + unitPrice + `">
        </td>
        <td>
            <input type="number" name="InvoiceItem[` + rowIndex + `][amount]" class="form-control form-control-sm amount-input text-right" readonly style="background-color: #f8f9fa;" value="` + amount + `">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btn-remove-item" title="ลบรายการ">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`;

    $('#items-table tbody').append(newRowHtml);

    // Initialize autocomplete for the new row
    initializeAutocomplete($('#items-table tbody tr:last .item-description-input'));

    // Calculate amount for the new row
    if (itemData) {
        calculateItemAmount($('#items-table tbody tr:last'));
    }
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
                    var newName = name.replace(/\\[\\d+\\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
        
        calculateTotal();
    } else {
        alert('ต้องมีรายการอย่างน้อย 1 รายการ');
    }
}

// Clear all items
function clearAllItems() {
    $('#items-table tbody').empty();
    addItemRow(); // Add one empty row
}

// Load job items
function loadJobItems(jobId) {
    if (jobId) {
        $.ajax({
            url: '{$getJobItemsUrl}',
            data: {id: jobId},
            dataType: 'json',
            type: 'POST',
            success: function(response) {
                console.log('Job items response:', response);
                if (response.success && response.items && response.items.length > 0) {
                    // Show confirmation dialog
                    if (confirm('พบรายการสินค้า/บริการจากใบงานนี้ ต้องการโหลดรายการหรือไม่?\\n\\n' +
                               'หมายเหตุ: การโหลดจะเขียนทับรายการที่มีอยู่')) {
                        clearAllItems();
                        
                        // Add items from job
                        response.items.forEach(function(item) {
                            addItemRow(item);
                        });
                        
                        calculateTotal();
                        
                        // Show success message
                        showMessage('success', 'โหลดรายการสินค้า/บริการเรียบร้อยแล้ว (' + response.items.length + ' รายการ)');
                    }
                } else {
                    showMessage('info', 'ไม่พบรายการสินค้า/บริการในใบงานนี้');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading job items:', error);
                showMessage('error', 'เกิดข้อผิดพลาดในการโหลดรายการสินค้า/บริการ');
            }
        });
    }
}

// Load customer data
function loadCustomerData(customerCode) {
    if (customerCode) {
        $.ajax({
            url: '{$getCustomerUrl}',
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

// Initialize autocomplete for item description
function initializeAutocomplete(element) {
    // Initialize autocomplete events for the element
    element.off('input.autocomplete focus.autocomplete blur.autocomplete keydown.autocomplete');
    
    element.on('input.autocomplete', function() {
        var input = $(this);
        var query = input.val();
        
        if (!isProductsLoaded) {
            loadProductsData();
            return;
        }
        
        var results = searchProducts(query);
        showAutocompleteResults(input, results);
    });
    
    element.on('focus.autocomplete', function() {
        var input = $(this);
        var query = input.val();
        
        if (!isProductsLoaded) {
            loadProductsData();
            return;
        }
        
        if (query) {
            var results = searchProducts(query);
            showAutocompleteResults(input, results);
        }
    });
    
    element.on('blur.autocomplete', function() {
        var row = $(this).closest('tr');
        hideAutocomplete(row);
    });
    
    element.on('keydown.autocomplete', function(e) {
        var row = $(this).closest('tr');
        var dropdown = row.find('.autocomplete-dropdown');
        var items = dropdown.find('.autocomplete-item');
        var highlighted = items.filter('.highlighted');
        
        if (e.keyCode === 40) { // Arrow Down
            e.preventDefault();
            if (highlighted.length === 0) {
                items.first().addClass('highlighted');
            } else {
                highlighted.removeClass('highlighted');
                var next = highlighted.next('.autocomplete-item');
                if (next.length) {
                    next.addClass('highlighted');
                } else {
                    items.first().addClass('highlighted');
                }
            }
        } else if (e.keyCode === 38) { // Arrow Up
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
        } else if (e.keyCode === 13) { // Enter
            e.preventDefault();
            if (highlighted.length) {
                var product = highlighted.data('product');
                selectProduct($(this), product);
            }
        } else if (e.keyCode === 27) { // Escape
            dropdown.hide();
        }
    });
}

// Show message function
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
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
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
                console.log(data);
                if (data != null) {
                    $('#invoice-customer-name').val(data[0].customer_name);
                    $('#invoice-customer-address').val(data[0].customer_address);
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

// Initialize calculations and autocomplete on page load
$(document).ready(function() {
    loadProductsData(); // โหลดข้อมูลสินค้าตอนเริ่มต้น
    calculateTotal();
    
    // Initialize autocomplete for existing items
    $('.item-description-input').each(function() {
        initializeAutocomplete($(this));
    });
});
JS;

// ใช้งาน
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
                    <div class="col-md-6">
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

<!--                        --><?php //= $form->field($model, 'job_id')->widget(Select2::class, [
//                            'data' => ArrayHelper::map(\backend\models\Job::find()->all(), 'id', 'job_no'),
//                            'options' => [
//                                'placeholder' => '...เลือกงาน...',
//                                'id' => 'invoice-job-id',
//                            ],
//                            'pluginOptions' => [
//                                'allowClear' => true,
//                                'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
//                            ],
//                        ]) ?>

                        <?= $form->field($model, 'quotation_id')->widget(Select2::class, [
                            'data' => ArrayHelper::map(\backend\models\Quotation::find()->all(), 'id', 'quotation_no'),
                            'options' => [
                                'placeholder' => '...เลือกใบเสนอราคา...',
                                'id' => 'invoice-job-id',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
                            ],
                        ]) ?>

                        <?= $form->field($model, 'customer_name')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'ชื่อลูกค้า',
                            'id' => 'invoice-customer-name',
                        ]) ?>

                        <?= $form->field($model, 'customer_address')->textarea([
                            'rows' => 3,
                            'placeholder' => 'ที่อยู่ลูกค้า',
                            'id' => 'invoice-customer-address',
                        ]) ?>
                    </div>
                    <div class="col-md-6" style="padding-top: 20px;">
                        <?= $form->field($model, 'customer_tax_id')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'เลขประจำตัวผู้เสียภาษี',
                            'id' => 'invoice-customer-tax-id',
                        ]) ?>

<!--                        --><?php //= $form->field($model, 'po_number')->textInput([
//                            'maxlength' => true,
//                            'placeholder' => 'เลขที่ใบสั่งซื้อ'
//                        ]) ?>
<!---->
<!--                        --><?php //= $form->field($model, 'po_date')->widget(DatePicker::class, [
//                            'options' => ['placeholder' => 'วันที่ใบสั่งซื้อ'],
//                            'pluginOptions' => [
//                                'autoclose' => true,
//                                'format' => 'yyyy-mm-dd',
//                                'todayHighlight' => true,
//                            ]
//                        ]) ?>

                        <?= $form->field($model, 'payment_term_id')->widget(Select2::class, [
                                'data' => \yii\helpers\ArrayHelper::map(\backend\models\Paymentterm::find()->all(), 'id', 'name'),
                                'options' => [
                                    'placeholder' => 'เลือกเงื่อนไขชําระเงิน...',
                                    'id' => 'invoice-payment_term_id'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]
                        ) ?>

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
                </div>
            </div>
        </div>
        <?= $form->field($model, 'invoice_type')->hiddenInput(['value' => $model->invoice_type])->label(false) ?>

        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list"></i> รายการสินค้า/บริการ
                </h5>
                <div>
                    <button type="button" class="btn btn-sm btn-info btn-load-job-items me-2">
                        <i class="fas fa-download"></i> โหลดจากใบงาน
                    </button>
                    <button type="button" class="btn btn-sm btn-primary btn-add-item">
                        <i class="fas fa-plus"></i> เพิ่มรายการ
                    </button>
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
                                    <?= Html::textInput("InvoiceItem[{$index}][item_description]", $item->item_description, [
                                        'class' => 'form-control form-control-sm item-description-input',
                                        'placeholder' => 'รายละเอียดสินค้า/บริการ',
                                        'autocomplete' => 'off'
                                    ]) ?>
                                    <div class="autocomplete-dropdown"></div>
                                </td>
                                <td>
                                    <?= Html::textInput("InvoiceItem[{$index}][quantity]", $item->quantity ?: '1', [
                                        'class' => 'form-control form-control-sm quantity-input text-right',
                                        'type' => 'number',
                                        'step' => '0.001',
                                        'min' => '0'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::dropDownList(
                                        "InvoiceItem[{$index}][unit_id]",
                                        $item->unit_id,
                                        ArrayHelper::map(Unit::find()->where(['status' => 1])->all(), 'id', 'name'),
                                        [
                                            'prompt' => 'เลือกหน่วย',
                                            'class' => 'form-control form-control-sm text-center'
                                        ]
                                    ) ?>
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
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-item"
                                            title="ลบรายการ">
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

        <?php
        $model_doc = \common\models\InvoiceDoc::find()->where(['invoice_id' => $model->id])->all();
        ?>
        <hr>
        <br/>
        <div class="label">
            <h4>เอกสารแนบ</h4>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-bordered table-striped" style="width: 100%">
                    <thead>
                    <tr>
                        <th style="width: 5%;text-align: center">#</th>
                        <th style="width: 50%;text-align: center">ชื่อไฟล์</th>
                        <th style="width: 10%;text-align: center">ดูเอกสาร</th>
                        <th style="width: 5%;text-align: center">-</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($model_doc != null): ?>

                        <?php foreach ($model_doc as $key => $value): ?>
                            <tr>
                                <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                                <td><?= $value->doc ?></td>
                                <td style="text-align: center">
                                    <a href="<?= Yii::$app->request->BaseUrl . '/uploads/invoice_doc/' . $value->doc ?>"
                                       target="_blank">
                                        ดูเอกสาร
                                    </a>
                                </td>
                                <td style="text-align: center">
                                    <div class="btn btn-danger" data-var="<?= trim($value->doc) ?>"
                                         onclick="delete_doc($(this))">ลบ
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <br/>

        <form action="<?= Url::to(['invoice/add-doc-file'], true) ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $model->id ?>">
            <div style="padding: 10px;background-color: lightgrey;border-radius: 5px">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">เอกสารแนบ</label>
                        <input type="file" name="file_doc" multiple>
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-lg-12">
                        <button class="btn btn-info">
                            <i class="fas fa-upload"></i> อัพโหลดเอกสารแนบ
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <form id="form-delete-doc-file" action="<?= Url::to(['invoice/delete-doc-file'], true) ?>" method="post">
            <input type="hidden" name="id" value="<?= $model->id ?>">
            <input type="hidden" class="delete-doc-list" name="doc_delete_list" value="">
        </form>
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

.btn-load-job-items {
    margin-right: 8px;
}

.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 9999 !important;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    display: none;
}

.autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.autocomplete-item:hover, .autocomplete-item.highlighted {
    background-color: #007bff;
    color: white;
}

.autocomplete-item:last-child {
    border-bottom: none;
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
");
?>

<?php
$script = <<< JS
function delete_doc(e){
    var file_name = e.attr('data-var');
    if(file_name != null){
        $(".delete-doc-list").val(file_name);
        $("#form-delete-doc-file").submit();
    }
}
JS;
$this->registerJs($script, static::POS_END);
?>
