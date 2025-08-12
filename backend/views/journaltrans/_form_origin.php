<?php

use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\models\JournalTrans;
use backend\models\Product;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\JournalTransX */
/* @var $lines common\models\JournalTransLineX[] */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile('@web/js/journal-trans.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// CSS สำหรับ autocomplete และ alerts
$autocompleteCSS = <<<CSS
.autocomplete-dropdown {
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.autocomplete-item:hover {
    background-color: #f5f5f5;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item.highlighted {
    background-color: #007bff;
    color: white;
}

.product-code {
    color: #666;
    font-size: 12px;
}

.product-field-container {
    position: relative;
}

.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;
    background: white;
    border: 1px solid #ccc;
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    display: none;
}

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
    overflow: visible !important;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.table-responsive .table {
    overflow: visible !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.stock-alert {
    position: relative;
}

.stock-warning {
    background-color: #fff3cd !important;
    border-color: #ffeaa7 !important;
    color: #856404;
}

.stock-error {
    background-color: #f8d7da !important;
    border-color: #f5c6cb !important;
    color: #721c24;
}

.warehouse-option-with-stock {
    display: flex;
    justify-content: space-between;
}

.warehouse-stock-info {
    color: #666;
    font-size: 0.9em;
}

.alert-message {
    position: absolute;
    top: -25px;
    left: 0;
    right: 0;
    z-index: 1000;
    font-size: 11px;
    padding: 2px 5px;
    border-radius: 3px;
    display: none;
}

.alert-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
CSS;

$this->registerCss($autocompleteCSS);

// Register Font Awesome
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

// URL สำหรับ AJAX
$ajax_url = Url::to(['get-product-info']);
$stock_url = Url::to(['get-product-stock']);

// JavaScript สำหรับ autocomplete และ stock management
$autocompleteJs = <<<JS
// ตัวแปรเก็บข้อมูลสินค้า
var productsData = [];
var productStockData = {};
var isProductsLoaded = false;

// ฟังก์ชันโหลดข้อมูลสินค้า
function loadProductsData() {
    if (isProductsLoaded) return;
    
    $.ajax({
        url: '$ajax_url',
        type: 'GET',
        data: { action: 'get-all-products' },
        dataType: 'json',
        success: function(data) {
            productsData = data;
            isProductsLoaded = true;
        },
        error: function() {
            console.log('Error loading products data');
            productsData = [];
        }
    });
}

// ฟังก์ชันโหลดข้อมูลสต็อกสินค้า
function loadProductStock(productId, index) {
    $.ajax({
        url: '$stock_url',
        type: 'GET',
        data: { 
            action: 'get-product-stock',
            product_id: productId 
        },
        dataType: 'json',
        success: function(data) {
            productStockData[productId] = data;
            updateWarehouseOptions(index, productId);
        },
        error: function() {
            console.log('Error loading product stock data');
            productStockData[productId] = [];
        }
    });
}


// ฟังก์ชันอัพเดตตัวเลือกคลังสินค้า
function updateWarehouseOptions(index, productId) {
    var warehouseSelect = $('.warehouse-select[data-index="' + index + '"]');
    var stockData = productStockData[productId] || [];
    console.log('stockData:', stockData);
    // Clear current options
    warehouseSelect.empty();
    warehouseSelect.append('<option value="">-- เลือกคลัง --</option>');
    
    // Add warehouses that have this product in stock
    stockData.forEach(function(stock) {
        if (stock.qty > 0) {
            var optionText = stock.warehouse_name + ' (คงเหลือ: ' + stock.qty + ' ' + stock.unit + ')';
            console.log('optionText:', optionText);
            warehouseSelect.append('<option value="' + stock.warehouse_id + '" data-stock="' + stock.qty + '">' + optionText + '</option>');
        }
    });
    
    // Clear stock display
    $('.line-product-onhand[data-index="' + index + '"]').val('');
}

// ฟังก์ชันค้นหาสินค้า
function searchProducts(query) {
    if (!query || query.length < 1) return [];
    
    query = query.toLowerCase();
    return productsData.filter(function(product) {
        return product.name.toLowerCase().includes(query) || 
               product.code.toLowerCase().includes(query) ||
               product.display.toLowerCase().includes(query);
    }).slice(0, 10);
}

// ฟังก์ชันแสดงผลลัพธ์
function showAutocompleteResults(input, results) {
    var index = input.attr('data-index');
    var dropdown = $('.autocomplete-dropdown[data-index="' + index + '"]');
    
    dropdown.empty();
    
    if (results.length === 0) {
        dropdown.hide();
        return;
    }
    
    results.forEach(function(product) {
        var item = $('<div class="autocomplete-item">')
            .html('<div>' + product.name + '</div><div class="product-code">' + product.code + '</div>')
            .data('product', product);
        dropdown.append(item);
    });
    
    dropdown.show();
}

// ฟังก์ชันซ่อน dropdown
function hideAutocomplete(index) {
    setTimeout(function() {
        $('.autocomplete-dropdown[data-index="' + index + '"]').hide();
    }, 200);
}

// ฟังก์ชันเลือกสินค้า
function selectProduct(input, product) {
    var index = input.attr('data-index');
    
    // อัพเดตค่า
    input.val(product.display);
    $('.product-id-hidden[data-index="' + index + '"]').val(product.id);
    
    // อัพเดตราคา
    $('.price-input[data-index="' + index + '"]').val(product.price);
    
    $('.line-unit-id[data-index="' + index + '"]').val(product.unit_id);
     
    $('.line-unit-name[data-index="' + index + '"]').val(product.unit_name);
    
    // โหลดข้อมูลสต็อกและอัพเดตคลังสินค้า
    // alert(product.id);
    loadProductStock(product.id, index);
    
    // ล้างค่าคลังสินค้าและจำนวน
    $('.warehouse-select[data-index="' + index + '"]').val('');
    $('.line-product-onhand[data-index="' + index + '"]').val('');
    $('.qty-input[data-index="' + index + '"]').val('');
    
    // ซ่อน dropdown
    $('.autocomplete-dropdown[data-index="' + index + '"]').hide();
    
    // คำนวณยอดรวม
    calculateLineTotal(index);
}

// ฟังก์ชันตรวจสอบจำนวนสินค้า
function validateQuantity(index) {
    var qtyInput = $('.qty-input[data-index="' + index + '"]');
    var stockOnHand = parseFloat($('.line-product-onhand[data-index="' + index + '"]').val()) || 0;
    var requestedQty = parseFloat(qtyInput.val()) || 0;
    var alertDiv = $('.alert-message[data-index="' + index + '"]');
    
    // Remove existing alert classes
    qtyInput.removeClass('stock-warning stock-error');
    alertDiv.hide();
    
    if (requestedQty > stockOnHand && stockOnHand > 0) {
        alert('issue=' + qtyInput.val() +' stock=' + stockOnHand);
        qtyInput.addClass('stock-error');
        qtyInput.val(stockOnHand);
        
        alertDiv.removeClass('alert-warning').addClass('alert-danger');
        alertDiv.html('จำนวนไม่เพียงพอ! ปรับเป็น ' + stockOnHand + ' แทน');
        alertDiv.show();
        
        // Hide alert after 3 seconds
        setTimeout(function() {
            alertDiv.fadeOut();
        }, 3000);
        
        return stockOnHand;
    } else if (requestedQty > stockOnHand * 0.8 && stockOnHand > 0) {
        qtyInput.addClass('stock-warning');
        alertDiv.removeClass('alert-danger').addClass('alert-warning');
        alertDiv.html('เตือน: ใกล้หมดสต็อก!');
        alertDiv.show();
        
        setTimeout(function() {
            alertDiv.fadeOut();
        }, 3000);
    }
    
    return requestedQty;
}

$(document).ready(function() {
    // โหลดข้อมูลสินค้าตอนเริ่มต้น
    loadProductsData();
    
    // Event สำหรับ autocomplete
    $(document).on('input', '.product-autocomplete', function() {
        var input = $(this);
        var query = input.val();
        
        if (!isProductsLoaded) {
            loadProductsData();
            return;
        }
        
        var results = searchProducts(query);
        showAutocompleteResults(input, results);
    });
    
    $(document).on('focus', '.product-autocomplete', function() {
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
    
    $(document).on('blur', '.product-autocomplete', function() {
        var index = $(this).attr('data-index');
        hideAutocomplete(index);
    });
    
    $(document).on('click', '.autocomplete-item', function() {
        var product = $(this).data('product');
        var dropdown = $(this).closest('.autocomplete-dropdown');
        var index = dropdown.attr('data-index');
        var input = $('.product-autocomplete[data-index="' + index + '"]');
        selectProduct(input, product);
    });
    
    // Event การเลือกคลังสินค้า
    $(document).on('change', '.warehouse-select', function() {
        var index = $(this).attr('data-index');
        var selectedOption = $(this).find('option:selected');
        var stockQty = selectedOption.data('stock') || 0;
        
        $('.line-product-onhand[data-index="' + index + '"]').val(stockQty);
        
        // Clear quantity when warehouse changes
        $('.qty-input[data-index="' + index + '"]').val('');
        calculateLineTotal(index);
    });
    
    // Event การกรอกจำนวน
    $(document).on('input change', '.qty-input', function() {
        var index = $(this).attr('data-index');
        var validatedQty = validateQuantity(index);
        $(this).val(validatedQty);
        //calculateLineTotal(index);
    });
    
    // Event navigation ด้วย keyboard
    $(document).on('keydown', '.product-autocomplete', function(e) {
        var index = $(this).attr('data-index');
        var dropdown = $('.autocomplete-dropdown[data-index="' + index + '"]');
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
});
JS;

$this->registerJs($autocompleteJs, \yii\web\View::POS_READY);

// 3. แก้ไข JavaScript - ลบ click handler ที่ซ้ำซ้อน
$dynamicFormJs = <<<JS
$(document).ready(function() {
    console.log('Dynamic form JS loaded');
    
    // ลบ click handler เดิมออก และใช้แค่ event ของ DynamicFormWidget
    
    // จัดการเมื่อเพิ่มรายการใหม่
    $('.dynamicform_wrapper').on('afterInsert', function(e, item) {
        console.log('After insert triggered');
        setTimeout(function() {
            updateAllDataIndexes();
            updateItemNumbers();
            
            var \$item = $(item);
            \$item.find('.product-autocomplete').val('');
            \$item.find('.product-id-hidden').val('');
            \$item.find('input[type="number"]').val('');
            
            calculateGrandTotal();
        }, 100);
    });
    
    $('.dynamicform_wrapper').on('afterDelete', function(e) {
        console.log('After delete triggered');
        setTimeout(function() {
            updateAllDataIndexes();
            updateItemNumbers();
            calculateGrandTotal();
        }, 100);
    });
    
    // เพิ่ม beforeInsert event เพื่อ debug
    $('.dynamicform_wrapper').on('beforeInsert', function(e, item) {
        console.log('Before insert triggered');
    });
    
    function updateAllDataIndexes() {
        $('.dynamicform_wrapper .item').each(function(index) {
            var \$item = $(this);
            
            \$item.find('.product-autocomplete').attr('data-index', index);
            \$item.find('.product-id-hidden').attr('data-index', index);
            \$item.find('.autocomplete-dropdown').attr('data-index', index);
            \$item.find('.warehouse-select').attr('data-index', index);
            \$item.find('.line-product-onhand').attr('data-index', index);
            \$item.find('.qty-input').attr('data-index', index);
            \$item.find('.price-input').attr('data-index', index);
            \$item.find('.line-unit-id').attr('data-index', index);
            \$item.find('.line-unit-name').attr('data-index', index);
            \$item.find('.line-total').attr('data-index', index);
        });
    }
    
    function updateItemNumbers() {
        $('.dynamicform_wrapper .item').each(function(index) {
            $(this).find('.item-number').text(index + 1);
        });
    }
    
    // Debug: เช็ค DynamicFormWidget initialization
    setTimeout(function() {
        console.log('Add button count:', $('.add-item').length);
        console.log('DynamicForm wrapper:', $('.dynamicform_wrapper').length);
        
        // Test manual click
        $('.add-item').off('click').on('click', function(e) {
            console.log('Manual add button clicked');
            e.preventDefault();
            // DynamicFormWidget จะจัดการเอง
        });
    }, 1000);
    
    updateAllDataIndexes();
    updateItemNumbers();
});
JS;
$this->registerJs($dynamicFormJs, \yii\web\View::POS_READY);
// 4. เพิ่ม debug script เพิ่มเติม
$debugJs = <<<JS
$(document).ready(function() {
    // ตรวจสอบ jQuery และ DynamicFormWidget
    console.log('jQuery version:', $.fn.jquery);
    console.log('DynamicFormWidget available:', typeof $.fn.dynamicform !== 'undefined');
    
    // เช็ค elements หลังจาก DOM ready
    setTimeout(function() {
        console.log('Form ID:', $('#journal-trans-form').length);
        console.log('Dynamic wrapper:', $('.dynamicform_wrapper').length);
        console.log('Add buttons:', $('.add-item').length);
        console.log('Container items:', $('.container-items').length);
        console.log('Items:', $('.item').length);
        
        // เช็ค data attributes
        $('.add-item').each(function(i) {
            console.log('Add button ' + i + ' classes:', this.className);
        });
    }, 2000);
});
JS;

$this->registerJs($debugJs, \yii\web\View::POS_READY);
// JavaScript สำหรับการคำนวณ
$calculationJs = <<<JS
function calculateLineTotal(index) {
    var qty = parseFloat($('.qty-input[data-index="' + index + '"]').val()) || 0;
    var price = parseFloat($('.price-input[data-index="' + index + '"]').val()) || 0;
    var total = qty * price;
    $('.line-total[data-index="' + index + '"]').val(total.toFixed(2));
    calculateGrandTotal();
}

function calculateGrandTotal() {
    var subtotal = 0;
    $('.line-total').each(function() {
        subtotal += parseFloat($(this).val()) || 0;
    });
    
    var discount = parseFloat($('#purchreq-discount_amount').val()) || 0;
    var afterDiscount = subtotal - discount;
    var vat = afterDiscount * 0.07; // 7% VAT
    var netAmount = afterDiscount + vat;
    
    $('#purchreq-total_amount').val(subtotal.toFixed(2));
    $('#purchreq-vat_amount').val(vat.toFixed(2));
    $('#purchreq-net_amount').val(netAmount.toFixed(2));
    
    // Update summary display
    $('#summary-subtotal').text(subtotal.toFixed(2));
    $('#summary-discount').text(discount.toFixed(2));
    $('#summary-vat').text(vat.toFixed(2));
    $('#summary-net').text(netAmount.toFixed(2));
}

$(document).on('change keyup input', '.price-input', function() {
    var index = $(this).attr('data-index');
    if (index !== undefined) {
        calculateLineTotal(index);
    }
});

$(document).on('change keyup', '#purchreq-discount_amount', function() {
    calculateGrandTotal();
});

$(document).ready(function() {
    calculateGrandTotal();
});


JS;

$this->registerJs($calculationJs, \yii\web\View::POS_READY);
?>

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

<?php if (\Yii::$app->session->hasFlash('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= \Yii::$app->session->getFlash('warning') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (\Yii::$app->session->hasFlash('info')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <?= \Yii::$app->session->getFlash('info') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


<div class="journal-trans-form">

    <?php $form = ActiveForm::begin([
        'id' => 'journal-trans-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-sm-12\">{input}\n{error}</div>",
            'labelOptions' => ['class' => 'col-sm-3 control-label'],
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?php $model->trans_date = $model->isNewRecord ? date('Y-m-d') : date('Y-m-d', strtotime($model->trans_date)); ?>
            <?= $form->field($model, 'trans_date')->widget(DatePicker::class, [
                'options' => ['placeholder' => 'Select transaction date'],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                ]
            ]) ?>

            <?php $crate_type = $_GET['type'] ?? null; ?>
            <?= $form->field($model, 'trans_type_id')->dropDownList(
                JournalTrans::getTransTypeOptions(),
                [
                    'prompt' => 'Select Transaction Type',
                    'id' => 'trans-type-select',
                    'value' => $model->isNewRecord ? $crate_type : $model->trans_type_id,
                    'onchange' => 'updateStockType(this.value)'
                ]
            ) ?>
            <?php //echo $crate_type; ?>
            <?php if ($model->isNewRecord && $crate_type): ?>
                <?php
                $this->registerJs("
                     $(document).ready(function() {
                        $('#trans-type-select').val('$crate_type').trigger('change');
                         // ตรวจสอบ type และเปิดใช้งาน return-for-trans-select ถ้าเป็น 4 หรือ 5
                        
                            var crateType = parseInt('$crate_type');
                            // alert(crateType);
                            if (crateType === 4 || crateType === 5) {
                                $('#return-for-trans-select').prop('disabled', false);
                            }
                     });
                  ");
                ?>
            <?php endif; ?>

            <?= $form->field($model, 'stock_type_id')->dropDownList(
                JournalTrans::getStockTypeOptions(),
                ['prompt' => 'Select Stock Type', 'id' => 'stock-type-select', 'readonly' => true]
            ) ?>

            <?= $form->field($model, 'job_id')->widget(Select2::class, [
                'data' => ArrayHelper::map(\backend\models\Job::find()->asArray()->all(), 'id', 'job_no'),
                'options' => ['placeholder' => 'เลือกงาน'],
                'pluginOptions' => ['allowClear' => true],
            ])->label() ?>
            <?= $form->field($model, 'emp_trans_id')->widget(Select2::class, [
                'data' => ArrayHelper::map(\backend\models\Employee::find()->all(), 'id', function ($model) {
                    return $model->fname . ' - ' . $model->lname;
                }),
                'options' => [
                    'id' => 'emp-trans-select',
                    'placeholder' => 'เลือกผู้เบิก/คืน',
                ],
            ]) ?>

        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'customer_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'return_for_trans_id')->widget(Select2::className(), [
                'data' => ArrayHelper::map(JournalTrans::find()->where(['trans_type_id' => 3])->asArray()->all(), 'id', 'journal_no'),
                'options' => ['id' => 'return-for-trans-select', 'placeholder' => 'Select Return for Transaction', 'disabled' => true],
            ]) ?>

            <?= $form->field($model, 'remark')->textarea(['rows' => 3]) ?>

            <?php if (!$model->isNewRecord): ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Journal No</label>
                    <div class="col-sm-9">
                        <p class="form-control-static"><?= Html::encode($model->journal_no) ?></p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Status</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            <span class="label label-<?= $model->status === 'approved' ? 'success' : 'default' ?>">
                                <?= Html::encode(ucfirst($model->status)) ?>
                            </span>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <hr>
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
                'model' => $model->journalTransLines[0] ?? new \backend\models\JournalTransLine(),
                'formId' => 'journal-trans-form',
                'formFields' => [
                    'product_id',
                    'warehouse_id',
                    'stock_onhand',
                    'qty',
                    'line_price',
                    'unit',
                    'line_total',
                ],
            ]); ?>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">ลำดับ</th>
                        <th style="width: 200px;">สินค้า</th>
                        <th style="width: 100px;">คลังจัดเก็บ</th>
                        <th style="width: 120px;">ยอดคงเหลือ</th>
                        <th style="width: 120px;">ยอดเบิก</th>
                        <th style="width: 120px;">ราคาต่อหน่วย</th>
                        <th style="width: 120px;">หน่วยนับ</th>
                        <th style="width: 50px;">
                            <button type="button" class="btn btn-success btn-sm add-item">
                                <i class="fas fa-plus"></i>
                            </button>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="container-items">
                    <?php if (empty($model->journalTransLines)): ?>
                        <?php $model->journalTransLinesline = [new \backend\models\JournalTransLine()]; ?>
                    <?php endif; ?>
                    <?php foreach ($model->journalTransLinesline as $index => $journaltransline): ?>
                        <tr class="item">
                            <td class="text-center align-middle">
                                <?= $form->field($journaltransline, "[{$index}]unit_id")->hiddenInput([
                                        'class' => 'line-unit-id',
                                ])->label(false) ?>
                                <span class="item-number"><?= $index + 1 ?></span>
                            </td>
                            <td>
                                <?php if (!$journaltransline->isNewRecord): ?>
                                    <?= Html::activeHiddenInput($journaltransline, "[{$index}]id") ?>
                                <?php endif; ?>

                                <div class="product-field-container">
                                    <?= Html::activeHiddenInput($journaltransline, "[{$index}]product_id", [
                                        'class' => 'product-id-hidden',
                                        'data-index' => $index,
                                    ]) ?>

                                    <?= $form->field($journaltransline, "[{$index}]product_name")->textInput([
                                        'class' => 'form-control product-autocomplete',
                                        'placeholder' => 'พิมพ์ชื่อสินค้าหรือรหัสสินค้า...',
                                        'data-index' => $index,
                                        'autocomplete' => 'off'
                                    ])->label(false) ?>

                                    <div class="autocomplete-dropdown" data-index="<?= $index ?>"></div>
                                </div>
                            </td>

                            <td>
                                <?= $form->field($journaltransline, "[{$index}]warehouse_id")->dropDownList(
                                    [], // Empty initially, will be populated by JavaScript
                                    [
                                        'prompt' => '-- เลือกคลัง --',
                                        'class' => 'form-control warehouse-select',
                                        'data-index' => $index
                                    ]
                                )->label(false) ?>
                            </td>
                            <td>
                                <input type="text" class="form-control line-product-onhand" name="stock_qty"
                                       readonly value="" data-index="<?= $index ?>">
                            </td>
                            <td>
                                <div class="stock-alert" style="position: relative;">
                                    <?= $form->field($journaltransline, "[{$index}]qty")->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'min' => '0',
                                        'placeholder' => '0',
                                        'class' => 'form-control qty-input',
                                        'data-index' => $index,
                                    ])->label(false) ?>
                                    <div class="alert-message" data-index="<?= $index ?>"></div>
                                </div>
                            </td>
                            <td>
                                <?= $form->field($journaltransline, "[{$index}]line_price")->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => '0',
                                    'placeholder' => '0.00',
                                    'class' => 'form-control price-input',
                                    'data-index' => $index,
                                ])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($journaltransline, "[{$index}]unit_name")->textInput([
                                    'readonly' => true,
                                    'class' => 'form-control line-unit-name',
                                    'style' => 'background-color: #f8f9fa;',
                                    'data-index' => $index,
                                ])->label(false) ?>
                            </td>
                            <td class="text-center align-middle" style="text-align: center">
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

    <hr>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', [
                'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
            ]) ?>
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
    // Transaction type to stock type mapping
    const transTypeStockTypeMap = {
        '1': '1', // PO Receive -> Stock In
        '2': '2', // Cancel PO Receive -> Stock Out
        '3': '2', // Issue Stock -> Stock Out
        '4': '1', // Return Issue -> Stock In
        '5': '2', // Issue Borrow -> Stock Out
        '6': '1'  // Return Borrow -> Stock In
    };

    function updateStockType(transTypeId) {
        const stockTypeSelect = document.getElementById('stock-type-select');
        if (transTypeStockTypeMap[transTypeId]) {
            stockTypeSelect.value = transTypeStockTypeMap[transTypeId];
        }

        // Show/hide return borrow fields
        const isReturnBorrow = transTypeId == '6';
        $('.return-borrow-fields').toggle(isReturnBorrow);

        if (isReturnBorrow) {
            $('.return-borrow-fields').show();
        } else {
            $('.return-borrow-fields').hide();
            $('.return-type-select').val('');
            $('.return-note-field').hide();
        }
    }

    // Initialize on page load
    // $(document).ready(function() {
    //     const transType = $('#trans-type-select').val();
    //     if (transType) {
    //         updateStockType(transType);
    //     }
    // });
</script>