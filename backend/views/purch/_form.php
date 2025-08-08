<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\ArrayHelper;
use backend\models\Purch;
use backend\models\Product;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\Purch */
/* @var $form yii\widgets\ActiveForm */

$model_doc = \common\models\PurchDoc::find()->where(['purch_id' => $model->id])->all();
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
    overflow: visible !important; /* แทน auto */
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.table-responsive .table {
    overflow: visible !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}
CSS;

$this->registerCss($autocompleteCSS);

// Register Font Awesome
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');


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

// JavaScript สำหรับการคำนวณ
$calculationJs = <<<JS
function calculateLineTotal(index) {
    var qty = parseFloat($('.qty-input[data-index="' + index + '"]').val()) || 0;
    var price = parseFloat($('.price-input[data-index="' + index + '"]').val()) || 0;
    var total = qty * price;
    $('.line-total[data-index="' + index + '"]').val(total.toFixed(2));
    calculateGrandTotal();
    calculateGrandTotal2();
}

function calculateGrandTotal() {
    var subtotal = 0;
    $('.line-total').each(function() {
        subtotal += parseFloat($(this).val()) || 0;
    });
    
    // var discount = parseFloat($('#purchreq-discount_amount').val()) || 0;
    // var afterDiscount = subtotal - discount;
    // var vat = afterDiscount * 0.07; // 7% VAT
    // var netAmount = afterDiscount + vat;
    
    var purch_req_is_vat =  $("#purch-req-is-vat").val();
    
    var discount = 0;
    var discount_per = parseFloat($('#purch-discount_per').val()) || 0;
    var discount_amount = parseFloat($('#purch-discount_amount').val()) || 0;
    
  //  alert(discount_per);
    
    if(discount_per > 0){
        discount = subtotal * (discount_per / 100);
    }
    discount = discount + discount_amount;
    
    var afterDiscount = subtotal - discount;
    var vat = 0;
    if(purch_req_is_vat === 1 || purch_req_is_vat =='1'){
        vat = afterDiscount * 0.07; // 7% VAT
    }
    
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

$(document).on('change keyup input', '.qty-input, .price-input', function() {
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

// Dynamic Form JavaScript
$dynamicFormJs = <<<JS
$(document).ready(function() {
    
    // จัดการเมื่อเพิ่มรายการใหม่
    $('.dynamicform_wrapper').on('afterInsert', function(e, item) {
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
        setTimeout(function() {
            updateAllDataIndexes();
            updateItemNumbers();
            calculateGrandTotal();
        }, 100);
    });
    
    function updateAllDataIndexes() {
        $('.dynamicform_wrapper .item').each(function(index) {
            var \$item = $(this);
            
            \$item.find('.product-autocomplete').attr('data-index', index);
            \$item.find('.product-id-hidden').attr('data-index', index);
            \$item.find('.autocomplete-dropdown').attr('data-index', index);
            \$item.find('.qty-input').attr('data-index', index);
            \$item.find('.price-input').attr('data-index', index);
            \$item.find('.line-total').attr('data-index', index);
        });
    }
    
    function updateItemNumbers() {
        $('.dynamicform_wrapper .item').each(function(index) {
            $(this).find('.item-number').text(index + 1);
        });
    }
    
    updateAllDataIndexes();
    updateItemNumbers();
    calculateGrandTotal();
});
JS;

$this->registerJs($dynamicFormJs, \yii\web\View::POS_READY);
?>


    <div class="purch-form">
        <input type="hidden" id="purch-req-is-vat" value="<?=$model->isNewRecord?'':$model->is_vat?>">
        <?php $form = ActiveForm::begin([
            'id' => 'purch-form',
            'options' => ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data'],
        ]); ?>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">ข้อมูลใบสั่งซื้อ</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'purch_no')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'ระบบจะสร้างอัตโนมัติหากไม่ระบุ'
                        ]) ?>

                        <?= $form->field($model, 'purch_date')->widget(DatePicker::class, [
                            'options' => ['placeholder' => 'เลือกวันที่'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ]
                        ]) ?>

                        <?= $form->field($model, 'vendor_id')->widget(Select2::className(), [
                            'data' => ArrayHelper::map(\backend\models\Vendor::find()->all(), 'id', 'name'),
                            'options' => ['placeholder' => 'เลือกผู้จําหน่าย'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ]
                        ]) ?>
                        <?= $form->field($model, 'discount_per')->textInput([
                            'type' => 'number',
                            'min' => 0,
                            'id' => 'purch-discount_per',
                            'onchange' => 'calculateGrandTotal2();',

                        ]) ?>
                        <?= $form->field($model, 'discount_amount')->textInput([
                            'type' => 'number',
                            'min' => 0,
                            'id' => 'purch-discount_amount',
                            'onchange' => 'calculateGrandTotal2();'

                        ]) ?>
                        <?= $form->field($model, 'status')->dropDownList([
                            Purch::STATUS_DRAFT => 'ร่าง',
                            Purch::STATUS_ACTIVE => 'ใช้งาน',
                            Purch::STATUS_CANCELLED => 'ยกเลิก',
                        ], ['prompt' => 'เลือกสถานะ']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'approve_status')->dropDownList([
                            Purch::APPROVE_STATUS_PENDING => 'รอพิจารณา',
                            Purch::APPROVE_STATUS_APPROVED => 'อนุมัติ',
                            Purch::APPROVE_STATUS_REJECTED => 'ไม่อนุมัติ',
                        ], ['prompt' => 'เลือกสถานะอนุมัติ']) ?>

                        <?= $form->field($model, 'ref_no')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'REF NO'
                        ]) ?>
                        <?= $form->field($model, 'is_vat')->dropDownList([
                            '1' => 'VAT',
                            '2' => 'NO VAT',
                        ],
                            [
                                'onchange' => 'enableVat($(this))',
                                'prompt' => 'เลือกคำนวน VAT'
                            ],) ?>
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
                    'model' => $model->purchLines[0] ?? new \backend\models\PurchLine(),
                    'formId' => 'purch-form',
                    'formFields' => [
                        'product_id',
                        'product_name',
//                    'product_description',
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
                            <th style="width: 100px;">จำนวน</th>
                            <th style="width: 120px;">ราคาต่อหน่วย</th>
                            <th style="width: 120px;">ราคารวม</th>
                            <th style="width: 50px;">
                                <button type="button" class="btn btn-success btn-sm add-item">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="container-items">
                        <?php if (empty($model->purchLines)): ?>
                            <?php $model->purchLines = [new \backend\models\PurchLine()]; ?>
                        <?php endif; ?>
                        <?php foreach ($model->purchLines as $index => $purchLine): ?>
                            <tr class="item">
                                <td class="text-center align-middle">
                                    <span class="item-number"><?= $index + 1 ?></span>
                                </td>
                                <td>
                                    <?php if (!$purchLine->isNewRecord): ?>
                                        <?= Html::activeHiddenInput($purchLine, "[{$index}]id") ?>
                                    <?php endif; ?>

                                    <div class="product-field-container">
                                        <?= Html::activeHiddenInput($purchLine, "[{$index}]product_id", [
                                            'class' => 'product-id-hidden',
                                            'data-index' => $index,
                                        ]) ?>

                                        <?= $form->field($purchLine, "[{$index}]product_name")->textInput([
                                            'class' => 'form-control product-autocomplete',
                                            'placeholder' => 'พิมพ์ชื่อสินค้าหรือรหัสสินค้า...',
                                            'data-index' => $index,
                                            'autocomplete' => 'off'
                                        ])->label(false) ?>

                                        <div class="autocomplete-dropdown" data-index="<?= $index ?>"></div>
                                    </div>
                                </td>
                                <td>
                                    <?= $form->field($purchLine, "[{$index}]qty")->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'min' => '0',
                                        'placeholder' => '0',
                                        'class' => 'form-control qty-input',
                                        'data-index' => $index,
                                    ])->label(false) ?>
                                </td>
                                <td>
                                    <?= $form->field($purchLine, "[{$index}]line_price")->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'min' => '0',
                                        'placeholder' => '0.00',
                                        'class' => 'form-control price-input',
                                        'data-index' => $index,
                                    ])->label(false) ?>
                                </td>
                                <td>
                                    <?= $form->field($purchLine, "[{$index}]line_total")->textInput([
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'readonly' => true,
                                        'class' => 'form-control line-total',
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

        <!-- Summary Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">สรุปยอดเงิน</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-lg-12">
                                <?= $form->field($model, 'delivery_note')->textInput(['placeholder' => 'Delivery Note']) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <?= $form->field($model, 'payment_note')->textInput(['placeholder' => 'Payment Note']) ?>
                            </div>
                        </div>
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
                                <td><?= $value->doc_name ?></td>
                                <td style="text-align: center">
                                    <a href="<?= Yii::$app->request->BaseUrl . '/uploads/purch_doc/' . $value->doc_name ?>"
                                       target="_blank">
                                        ดูเอกสาร
                                    </a>
                                </td>
                                <td style="text-align: center">
                                    <div class="btn btn-danger" data-var="<?= trim($value->doc_name) ?>"
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

        <form action="<?= Url::to(['purch/add-doc-file'], true) ?>" method="post" enctype="multipart/form-data">
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
        <form id="form-delete-doc-file" action="<?= Url::to(['purch/delete-doc-file'], true) ?>" method="post">
            <input type="hidden" name="id" value="<?= $model->id ?>">
            <input type="hidden" class="delete-doc-list" name="doc_delete_list" value="">
        </form>

    </div>

<?php
$script = <<< JS
function delete_doc(e){
    var file_name = e.attr('data-var');
    if(file_name != null){
        $(".delete-doc-list").val(file_name);
        $("#form-delete-doc-file").submit();
    }
}

function enableVat(e){
    var id = $(e).val();
    if(id!=null || id!=''){
        $("#purch-req-is-vat").val(id);
    }else{
        $("#purch-req-is-vat").val('');
    }
    
    calculateGrandTotal2();
}
function calculateGrandTotal2() {
    var subtotal = 0;
    $('.line-total').each(function() {
        subtotal += parseFloat($(this).val()) || 0;
    });
    
    var purch_req_is_vat =  $("#purch-req-is-vat").val();
    
    var discount = 0;
    var discount_per = parseFloat($('#purch-discount_per').val()) || 0;
    var discount_amount = parseFloat($('#purch-discount_amount').val()) || 0;
    
  //  alert(discount_per);
    
    if(discount_per > 0){
        discount = subtotal * (discount_per / 100);
    }
    discount = discount + discount_amount;
    
    var afterDiscount = subtotal - discount;
    var vat = 0;
    if(purch_req_is_vat === 1 || purch_req_is_vat =='1'){
        vat = afterDiscount * 0.07; // 7% VAT
    }
    
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
JS;
$this->registerJs($script, static::POS_END);
?>