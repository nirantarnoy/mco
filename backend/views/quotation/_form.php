<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\ArrayHelper;
use backend\models\Quotation;
use backend\models\Product;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\Quotation */
/* @var $form yii\widgets\ActiveForm */

// CSS สำหรับซ่อนปุ่มและจัดรูปแบบ
$css = '
.dynamicform_wrapper .panel-heading {
    display: none;
}
.dynamicform_wrapper .item {
    border: none;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}
.dynamicform_wrapper .item:last-child {
    border-bottom: none;
}
.form-buttons-container {
    margin-bottom: 15px;
    padding: 10px;
    background-color: #f5f5f5;
    border-radius: 4px;
}
.select2-container {
    width: 100% !important;
}
.item-number {
    font-weight: bold;
    color: #6c757d;
}
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.bg-light {
    background-color: #f8f9fa !important;
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

';

// CSS สำหรับ autocomplete
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

/* ซ่อน form-group ของ hidden input */
.product-field-container .form-group:has(input[type="hidden"]) {
    display: none;
}

/* สำหรับ browser ที่ไม่รองรับ :has() */
.product-field-container .form-group {
    margin-bottom: 15px;
}

.product-field-container .form-group:first-child {
    display: none; /* ซ่อน form-group แรก (hidden input) */
}

/* จัดแนว field ให้เสมอกัน */
.panel-body .row {
    align-items: flex-start;
}

.panel-body .col-sm-3,
.panel-body .col-sm-2,
.panel-body .col-sm-1 {
    display: flex;
    flex-direction: column;
}

.panel-body .form-group {
    margin-bottom: 15px;
    flex: 1;
}

/* ให้ label อยู่ในแนวเดียวกัน */
.panel-body .control-label {
    height: 20px;
    line-height: 20px;
    margin-bottom: 5px;
}

/* ให้ input field อยู่ในแนวเดียวกัน */
.panel-body .form-control {
    height: 34px;
}

/* Style สำหรับปุ่มลบ */
.col-sm-1[style*="padding-top"] {
    padding-top: 25px !important;
    display: flex;
    align-items: flex-start;
}
.table-responsive {
    overflow: visible !important; /* แทน auto */
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.table-responsive .table {
    overflow: visible !important;
}
CSS;

$this->registerCss($autocompleteCSS);

// Register Font Awesome
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
// JavaScript สำหรับการคำนวณ
$calculationJs = <<<JS
// ฟังก์ชันคำนวณยอดรวมในแต่ละรายการ
function calculateLineTotal(index) {
    var qty = parseFloat($('.qty-input[data-index="' + index + '"]').val()) || 0;
    var price = parseFloat($('.price-input[data-index="' + index + '"]').val()) || 0;
    var discount = parseFloat($('.discount-input[data-index="' + index + '"]').val()) || 0;
    var subtotal = qty * price;
    var total = subtotal - discount;
    $('.line-total[data-index="' + index + '"]').val(total.toFixed(2));
    calculateGrandTotal();
}

// ฟังก์ชันคำนวณยอดรวมทั้งหมด
function calculateGrandTotal() {
    var total = 0;
    $('.line-total').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    
    $('#quotation-total_amount').val(total.toFixed(2));
    $('#summary-total').text(total.toFixed(2));
}

// Event handlers
$(document).ready(function() {
    // คำนวณยอดรวมเริ่มต้น
    calculateGrandTotal();
    
    // Event เมื่อเปลี่ยนข้อมูลในฟอร์ม
    $(document).on('change keyup input', '.qty-input, .price-input, .discount-input', function() {
        var index = $(this).attr('data-index');
        if (index !== undefined) {
            calculateLineTotal(index);
        }
    });
});
JS;

// URL สำหรับ AJAX
$ajax_url = Url::to(['get-product-info']);
// JavaScript สำหรับ autocomplete
$autocompleteJs = <<<JS
// ตัวแปรเก็บข้อมูลสินค้า
var productsData = [];
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

// ฟังก์ชันค้นหาสินค้า
function searchProducts(query) {
    if (!query || query.length < 1) return [];
    
    query = query.toLowerCase();
    return productsData.filter(function(product) {
        return product.name.toLowerCase().includes(query) || 
               product.code.toLowerCase().includes(query) ||
               product.display.toLowerCase().includes(query);
    }).slice(0, 10); // จำกัดผลลัพธ์ 10 รายการ
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
    
    // ซ่อน dropdown
    $('.autocomplete-dropdown[data-index="' + index + '"]').hide();
    
    // คำนวณยอดรวม
    calculateLineTotal(index);
}

$(document).ready(function() {
    // โหลดข้อมูลสินค้าตอนเริ่มต้น
    loadProductsData();
    
    // Event สำหรับ autocomplete
    $(document).on('input', '.product-autocomplete', function() {
        var input = $(this);
        var query = input.val();
        
        // ถ้ายังไม่ได้โหลดข้อมูล ให้โหลดก่อน
        if (!isProductsLoaded) {
            loadProductsData();
            return;
        }
        
        var results = searchProducts(query);
        showAutocompleteResults(input, results);
    });
    
    // Event เมื่อ focus
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
    
    // Event เมื่อ blur
    $(document).on('blur', '.product-autocomplete', function() {
        var index = $(this).attr('data-index');
        hideAutocomplete(index);
    });
    
    // Event เมื่อคลิกรายการ
    $(document).on('click', '.autocomplete-item', function() {
        var product = $(this).data('product');
        var dropdown = $(this).closest('.autocomplete-dropdown');
        var index = dropdown.attr('data-index');
        var input = $('.product-autocomplete[data-index="' + index + '"]');
        selectProduct(input, product);
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
?>

    <div class="quotation-form">

        <?php $form = ActiveForm::begin([
            'id' => 'dynamic-form',
            'options' => ['class' => 'form-horizontal'],
        ]); ?>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">ข้อมูลใบเสนอราคา</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
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

                        <?= $form->field($model, 'customer_id')->widget(Select2::className(), [
                            'data' => ArrayHelper::map(\backend\models\Customer::find()->all(), 'id', 'name'),
                            'options' => ['placeholder' => 'เลือกลูกค้า'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ]
                        ]) ?>

                        <?= $form->field($model, 'status')->dropDownList([
                            Quotation::STATUS_DRAFT => 'ร่าง',
                            Quotation::STATUS_ACTIVE => 'ใช้งาน',
                            Quotation::STATUS_CANCELLED => 'ยกเลิก',
                        ], ['prompt' => 'เลือกสถานะ']) ?>
                    </div>
                    <div class="col-md-4">
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
                    <div class="col-lg-4">
                        <?= $form->field($model, 'payment_term_id')->widget(Select2::className(), [
                            'data' => ArrayHelper::map(\backend\models\Paymentterm::find()->all(), 'id', 'name'),
                            'options' => ['placeholder' => 'เลือกเงื่อนไขชําระเงิน'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ]
                        ]) ?>
                        <?= $form->field($model, 'payment_method_id')->widget(Select2::className(), [
                            'data' => ArrayHelper::map(\backend\models\Paymentmethod::find()->all(), 'id', 'name'),
                            'options' => ['placeholder' => 'เลือกวิธีชําระเงิน'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ]
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
                    'formId' => 'dynamic-form',
                    'formFields' => [
                        'product_id',
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
                            <th style="width: 200px;">ชื่อสินค้า</th>
                            <th style="width: 100px;">จำนวน</th>
                            <th style="width: 120px;">ราคาต่อหน่วย</th>
                            <th style="width: 120px;">ส่วนลด</th>
                            <th style="width: 120px;">ราคารวม</th>
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
                                <?php if (!$quotationLine->isNewRecord): ?>
                                    <?= Html::activeHiddenInput($quotationLine, "[{$index}]id") ?>
                                <?php endif; ?>
                                <td style="text-align: center">
                                    <span class="item-number"><?= $index + 1 ?></span>
                                </td>
                                <td>
                                    <div class="product-field-container">
                                        <?= Html::activeHiddenInput($quotationLine, "[{$index}]product_id", [
                                            'class' => 'product-id-hidden',
                                            'data-index' => $index,
                                        ]) ?>

                                        <?= $form->field($quotationLine, "[{$index}]product_name")->textInput([
                                            'class' => 'form-control product-autocomplete',
                                            'placeholder' => 'พิมพ์ชื่อสินค้าหรือรหัสสินค้า...',
                                            'data-index' => $index,
                                            'autocomplete' => 'off'
                                        ])->label(false) ?>

                                        <div class="autocomplete-dropdown" data-index="<?= $index ?>"></div>
                                    </div>
                                </td>
                                <td>
                                    <?= $form->field($quotationLine, "[{$index}]qty")->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'min' => '0',
                                        'placeholder' => '0',
                                        'class' => 'form-control qty-input',
                                        'data-index' => $index,
                                    ])->label(false) ?>
                                </td>
                                <td><?= $form->field($quotationLine, "[{$index}]line_price")->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'min' => '0',
                                        'placeholder' => '0.00',
                                        'class' => 'form-control price-input',
                                        'data-index' => $index,
                                    ])->label(false) ?></td>
                                <td>
                                    <?= $form->field($quotationLine, "[{$index}]discount_amount")->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'min' => '0',
                                        'placeholder' => '0.00',
                                        'class' => 'form-control discount-input',
                                        'data-index' => $index,
                                    ])->label(false) ?>
                                </td>
                                <td>
                                    <?= $form->field($quotationLine, "[{$index}]line_total")->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'readonly' => true,
                                        'class' => 'form-control line-total',
                                        'style' => 'background-color: #f8f9fa;',
                                        'data-index' => $index,
                                    ])->label(false) ?>
                                </td>
                                <td>
                                    <button type="button" class="remove-item btn btn-danger btn-xs">
                                        <i class="fa fa-trash"></i>
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

        <?php if ($model->isNewRecord || $model->status == Quotation::STATUS_DRAFT): ?>
            <div class="form-group mt-3">
                <div class="d-flex justify-content-between">
                    <?= Html::submitButton($model->isNewRecord ? 'สร้างใบเสนอราคา' : 'บันทึกการแก้ไข', [
                        'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
                    ]) ?>
                    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php ActiveForm::end(); ?>

    </div>

<?php
// Simple Dynamic Form JavaScript พร้อม autocomplete
$dynamicFormJs = <<<JS
$(document).ready(function() {
    
    // จัดการเมื่อเพิ่มรายการใหม่
    $('.dynamicform_wrapper').on('afterInsert', function(e, item) {
        console.log('afterInsert triggered');
        
        // รอให้ DOM อัพเดตก่อน
        setTimeout(function() {
            // อัพเดต data-index สำหรับทุกรายการ
            updateAllDataIndexes();
            
            // อัพเดตหมายเลขรายการ
            updateItemNumbers();
            
            // ล้างค่าในรายการใหม่
            var \$item = $(item);
            \$item.find('.product-autocomplete').val('');
            \$item.find('.product-id-hidden').val('');
            \$item.find('input[type="number"]').val('');
            
            // คำนวณยอดรวมใหม่
            calculateGrandTotal();
        }, 100);
    });
    
    // จัดการเมื่อลบรายการ
    $('.dynamicform_wrapper').on('afterDelete', function(e) {
        setTimeout(function() {
            updateAllDataIndexes();
            updateItemNumbers();
            calculateGrandTotal();
        }, 100);
    });
    
    // ฟังก์ชันอัพเดต data-index สำหรับทุกรายการ
    function updateAllDataIndexes() {
        $('.dynamicform_wrapper .item').each(function(index) {
            var \$item = $(this);
            
            // อัพเดต data-index สำหรับ autocomplete
            \$item.find('.product-autocomplete').attr('data-index', index);
            \$item.find('.product-id-hidden').attr('data-index', index);
            \$item.find('.autocomplete-dropdown').attr('data-index', index);
            
            // อัพเดต data-index สำหรับ input อื่นๆ
            \$item.find('.qty-input').attr('data-index', index);
            \$item.find('.price-input').attr('data-index', index);
            \$item.find('.discount-input').attr('data-index', index);
            \$item.find('.line-total').attr('data-index', index);
            
            console.log('Updated item index:', index);
        });
    }
    
    // ฟังก์ชันอัพเดตหมายเลขรายการ
    function updateItemNumbers() {
        $('.dynamicform_wrapper .item').each(function(index) {
            $(this).find('.item-number').text(index + 1);
        });
    }
    
    // เริ่มต้นการทำงาน
    updateAllDataIndexes();
    updateItemNumbers();
    calculateGrandTotal();
});

// ฟังก์ชันคำนวณยอดรวมในแต่ละรายการ
function calculateLineTotal(index) {
    console.log('Calculating line total for index:', index);
    
    var qty = parseFloat($('.qty-input[data-index="' + index + '"]').val()) || 0;
    var price = parseFloat($('.price-input[data-index="' + index + '"]').val()) || 0;
    var discount = parseFloat($('.discount-input[data-index="' + index + '"]').val()) || 0;
    var subtotal = qty * price;
    var total = subtotal - discount;
    
    console.log('Qty:', qty, 'Price:', price, 'Discount:', discount, 'Total:', total);
    
    $('.line-total[data-index="' + index + '"]').val(total.toFixed(2));
    calculateGrandTotal();
}

// ฟังก์ชันคำนวณยอดรวมทั้งหมด
function calculateGrandTotal() {
    var total = 0;
    $('.line-total').each(function() {
        var value = parseFloat($(this).val()) || 0;
        total += value;
    });
    
    console.log('Grand total:', total);
    
    $('#quotation-total_amount').val(total.toFixed(2));
    $('#summary-total').text(total.toFixed(2));
}

// Event handlers สำหรับการคำนวณ
$(document).on('change keyup input', '.qty-input, .price-input, .discount-input', function() {
    var index = $(this).attr('data-index');
    console.log('Input changed, index:', index);
    if (index !== undefined) {
        calculateLineTotal(index);
    }
});
JS;

$this->registerJs($dynamicFormJs, \yii\web\View::POS_READY);
?>