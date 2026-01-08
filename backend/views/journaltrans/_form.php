<?php

use wbraganca\dynamicform\DynamicFormWidget;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\models\JournalTrans;
use backend\models\Product;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Url;

//use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model common\models\JournalTransX */
/* @var $lines common\models\JournalTransLineX[] */
/* @var $form yii\widgets\ActiveForm */

$isApproved = $model->status === JournalTrans::STATUS_APPROVED;
$canEdit = $model->isNewRecord || ($model->status === JournalTrans::STATUS_DRAFT) ||
           ($isApproved && in_array($model->trans_type_id, [JournalTrans::TRANS_TYPE_ISSUE_STOCK, JournalTrans::TRANS_TYPE_ISSUE_BORROW]));


$this->registerJsFile('@web/js/journal-trans.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$damage_list = [['id' => 1, 'name' => 'สภาพปกติ'], ['id' => 2, 'name' => 'สภาพไม่ปกติ']];

$model_doc = \common\models\JournalTransDoc::find()->where(['journal_trans_id' => $model->id])->all();

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

.return-fields {
    display: none;
}

.return-fields.show {
    display: block;
}

.readonly-field {
    background-color: #f8f9fa !important;
    cursor: not-allowed;
}

.condition-btn {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: white;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
}

.condition-btn:hover {
    background-color: #138496;
}

.modal-body .form-group {
    margin-bottom: 15px;
}

.condition-summary {
    font-size: 11px;
    color: #666;
    margin-top: 5px;
}

.original-transaction-info {
    background-color: #e7f3ff;
    border: 1px solid #b3d7ff;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 15px;
}

.info-label {
    font-weight: bold;
    color: #0056b3;
}

.return-transaction-line {
    background-color: #f8f9fa;
}

   /*table td .form-control {*/
   /*     width: 100%;*/
   /*     box-sizing: border-box;*/
   /*     padding: 0.25rem 0.5rem;*/
   /* }*/
   
   /* .table td {*/
   /*     padding: 0.25rem 0.5rem;*/
   /*     vertical-align: middle;*/
   /* }*/
CSS;

$this->registerCss($autocompleteCSS);

// Register Font Awesome
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

// URL สำหรับ AJAX
$ajax_url = Url::to(['get-product-info']);
$stock_url = Url::to(['get-product-stock']);
$get_transaction_url = Url::to(['get-transaction-lines']);

// JavaScript สำหรับ return transaction features
$returnTransactionJs = <<<JS
var originalTransactionData = {};
var returnTransactionLines = [];

// ฟังก์ชันสำหรับโหลดข้อมูล transaction ต้นฉบับ
function loadOriginalTransaction(transactionId) {
    if (!transactionId) {
        clearReturnTransactionData();
        return;
    }
    
    $.ajax({
        url: '$get_transaction_url',
        type: 'GET',
        data: { transaction_id: transactionId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                originalTransactionData = response.data;
                displayOriginalTransactionInfo(response.data);
                populateReturnTransactionLines(response.data.lines);
            } else {
                alert('ไม่สามารถโหลดข้อมูลรายการได้: ' + response.message);
            }
        },
        error: function() {
            alert('เกิดข้อผิดพลาดในการโหลดข้อมูลรายการ');
        }
    });
}

// แสดงข้อมูล transaction ต้นฉบับ
function displayOriginalTransactionInfo(data) {
    var infoHtml = '<div class="original-transaction-info">' +
        '<h6><i class="fa fa-info-circle"></i> ข้อมูลรายการต้นฉบับ</h6>' +
        '<div class="row">' +
        '<div class="col-md-6">' +
        '<span class="info-label">เลขที่:</span> ' + data.journal_no + '<br>' +
        '<span class="info-label">วันที่:</span> ' + data.trans_date + '<br>' +
        '<span class="info-label">ลูกค้า:</span> ' + (data.customer_name || '-') +
        '</div>' +
        '<div class="col-md-6">' +
        '<span class="info-label">ประเภท:</span> ' + data.trans_type_name + '<br>' +
        '<span class="info-label">สถานะ:</span> ' + data.status + '<br>' +
        '<span class="info-label">จำนวนรายการ:</span> ' + data.lines.length + ' รายการ' +
        '</div>' +
        '</div>' +
        '</div>';
    
    $('.card-body').prepend(infoHtml);
}

// เปิด modal สำหรับบันทึกสภาพสินค้า
function openConditionModal(index) {
    currentConditionIndex = index;
    var currentData = getConditionData(index);
    
    // เติมข้อมูลใน modal
    $('#condition-good-qty').val(currentData.good_qty || '');
    $('#condition-damaged-qty').val(currentData.damaged_qty || '');
    $('#condition-missing-qty').val(currentData.missing_qty || '');
    $('#condition-note').val(currentData.condition_note || '');
    $('#return-note').val(currentData.return_note || '');
    
    updateConditionTotal();
    $('#conditionModal').modal('show');
}

// เติมข้อมูลรายการสินค้าสำหรับการคืน
function populateReturnTransactionLines(lines) {
    // ล้างรายการเดิม
    $('.container-items').empty();
    
    lines.forEach(function(line, index) {
        addReturnTransactionLine(line, index);
    });
}

// เพิ่มรายการสินค้าสำหรับการคืน
function addReturnTransactionLine(lineData, index) {
    var availableQty = getAvailableReturnQty(lineData.product_id);
    
    var rowHtml = '<tr class="item return-transaction-line">' +
        '<td class="text-center align-middle">' +
        '<span class="item-number">' + (index + 1) + '</span>' +
        '<input type="hidden" name="JournalTransLine[' + index + '][id]" value="">' +
        '<input type="hidden" name="JournalTransLine[' + index + '][product_id]" value="' + lineData.product_id + '">' +
        '</td>' +
        '<td>' +
        '<input type="text" class="form-control readonly-field" name="JournalTransLine[' + index + '][product_name]" ' +
        'value="' + lineData.product_name + '" readonly>' +
        '<small class="text-muted">รหัส: ' + lineData.product_code + '</small>' +
        '</td>' +
        '<td>' +
        '<input type="text" class="form-control readonly-field" name="JournalTransLine[' + index + '][warehouse_name]" ' +
        'value="' + lineData.warehouse_name + '" readonly>' +
        '<input type="hidden" name="JournalTransLine[' + index + '][warehouse_id]" value="' + lineData.warehouse_id + '">' +
        '</td>' +
        '<td>' +
        '<input type="text" class="form-control text-center readonly-field" ' +
        'value="' + lineData.qty + '" readonly>' +
        '<small class="text-muted">ยืมไป</small>' +
        '</td>' +
        '<td>' +
        '<input type="text" class="form-control text-center readonly-field" ' +
        'value="' + availableQty + '" readonly>' +
        '<small class="text-muted">คงเหลือ</small>' +
        '</td>' +
        '<td>' +
        '<input type="number" class="form-control qty-input" name="JournalTransLine[' + index + '][qty]" ' +
        'step="0.01" min="0" max="' + availableQty + '" placeholder="0" data-index="' + index + '" data-original-qty="' + lineData.qty + '" data-available-qty="' + availableQty + '">' +
        '</td>' +
        '<td>' +
        '<input type="text" class="form-control readonly-field" name="JournalTransLine[' + index + '][unit_name]" ' +
        'value="' + lineData.unit_name + '" readonly>' +
        '<input type="hidden" name="JournalTransLine[' + index + '][unit_id]" value="' + lineData.unit_id + '">' +
        '<td>' +
        '<select class="form-control condition-select" name="JournalTransLine[' + index + '][is_damage]" ' +
        'data-index="' + index + '" style="display: nonex;">' +
        '<option value="">เลือกสภาพ</option>' +
        '<option value="1">สภาพปกติ</option>' +
        '<option value="0">สภาพไม่ปกติ</option>' +
        '</select>' +
        '<span class="condition-placeholder text-muted" data-index="' + index + '">-</span>' +
        '</td>' +
        '<td>' +
        '<input type="text" class="form-control" name="JournalTransLine[' + index + '][return_note]" placeholder="หมายเหตุ">' +
        '</td>' +
        '</tr>';
    
    $('.container-items').append(rowHtml);
}

// คำนวณจำนวนที่สามารถคืนได้
function getAvailableReturnQty(productId) {
    // ค้นหาจำนวนที่ยืมไปจาก original transaction
    var originalQty = 0;
    if (originalTransactionData.lines) {
        originalTransactionData.lines.forEach(function(line) {
            if (line.product_id == productId) {
                originalQty += parseFloat(line.qty);
            }
        });
    }
    
    // TODO: ดึงข้อมูลจำนวนที่คืนไปแล้วจาก database
    var returnedQty = 0;
    
    return originalQty - returnedQty;
}

// ล้างข้อมูล return transaction
function clearReturnTransactionData() {
    originalTransactionData = {};
    $('.original-transaction-info').remove();
    $('.container-items').html('<tr class="item"><td colspan="8" class="text-center">กรุณาเลือกรายการที่ต้องการคืนก่อน</td></tr>');
}



var currentConditionIndex = -1;

// ฟังก์ชันสำหรับบันทึกข้อมูลสภาพสินค้า
function saveConditionData() {
    var index = currentConditionIndex;
    var qtyInput = $('.qty-input[data-index="' + index + '"]');
    var totalQty = parseFloat(qtyInput.val()) || 0;
    
    var goodQty = parseFloat($('#condition-good-qty').val()) || 0;
    var damagedQty = parseFloat($('#condition-damaged-qty').val()) || 0;
    var missingQty = parseFloat($('#condition-missing-qty').val()) || 0;
    var conditionNote = $('#condition-note').val();
    var returnNote = $('#return-note').val();
    
    var conditionTotal = goodQty + damagedQty + missingQty;
    
    if (conditionTotal !== totalQty) {
        alert('ผลรวมจำนวนสภาพสินค้า (' + conditionTotal + ') ต้องเท่ากับจำนวนที่คืน (' + totalQty + ')');
        return;
    }
    
    // บันทึกข้อมูลใน hidden fields
    setConditionHiddenFields(index, goodQty, damagedQty, missingQty, conditionNote, returnNote);
    
    // อัพเดตการแสดงผล
    updateConditionSummary(index, goodQty, damagedQty, missingQty);
    
    $('#conditionModal').modal('hide');
}

// ตั้งค่า hidden fields สำหรับข้อมูลสภาพสินค้า
function setConditionHiddenFields(index, goodQty, damagedQty, missingQty, conditionNote, returnNote) {
    var container = $('.item').eq(index);
    
    // ลบ hidden fields เดิม
    container.find('input[name*="[good_qty]"], input[name*="[damaged_qty]"], input[name*="[missing_qty]"], input[name*="[condition_note]"], input[name*="[return_note]"]').remove();
    
    // เพิ่ม hidden fields ใหม่
    container.append('<input type="hidden" name="JournalTransLine[' + index + '][good_qty]" value="' + goodQty + '">');
    container.append('<input type="hidden" name="JournalTransLine[' + index + '][damaged_qty]" value="' + damagedQty + '">');
    container.append('<input type="hidden" name="JournalTransLine[' + index + '][missing_qty]" value="' + missingQty + '">');
    container.append('<input type="hidden" name="JournalTransLine[' + index + '][condition_note]" value="' + conditionNote + '">');
    container.append('<input type="hidden" name="JournalTransLine[' + index + '][return_note]" value="' + returnNote + '">');
}

// อัพเดตการแสดงผลสรุปสภาพสินค้า
function updateConditionSummary(index, goodQty, damagedQty, missingQty) {
    var summary = [];
    if (goodQty > 0) summary.push('ดี: ' + goodQty);
    if (damagedQty > 0) summary.push('เสีย: ' + damagedQty);
    if (missingQty > 0) summary.push('หาย: ' + missingQty);
    
    $('.condition-summary[data-index="' + index + '"]').text(summary.join(', '));
}

// ดึงข้อมูลสภาพสินค้าปัจจุบัน
function getConditionData(index) {
    var container = $('.item').eq(index);
    return {
        good_qty: container.find('input[name*="[good_qty]"]').val(),
        damaged_qty: container.find('input[name*="[damaged_qty]"]').val(),
        missing_qty: container.find('input[name*="[missing_qty]"]').val(),
        condition_note: container.find('input[name*="[condition_note]"]').val(),
        return_note: container.find('input[name*="[return_note]"]').val()
    };
}

// อัพเดตผลรวมในการกรอกสภาพสินค้า
function updateConditionTotal() {
    var goodQty = parseFloat($('#condition-good-qty').val()) || 0;
    var damagedQty = parseFloat($('#condition-damaged-qty').val()) || 0;
    var missingQty = parseFloat($('#condition-missing-qty').val()) || 0;
    var total = goodQty + damagedQty + missingQty;
    
    $('#condition-total').text(total);
    
    var qtyInput = $('.qty-input[data-index="' + currentConditionIndex + '"]');
    var expectedTotal = parseFloat(qtyInput.val()) || 0;
    
    if (total === expectedTotal) {
        $('#condition-total').removeClass('text-danger').addClass('text-success');
    } else {
        $('#condition-total').removeClass('text-success').addClass('text-danger');
    }
}

$(document).ready(function() {
    // Event สำหรับการเปลี่ยน transaction type
    $('#trans-type-select').change(function() {
        var transType = parseInt($(this).val());
        var returnForSelect = $('#return-for-trans-select');
        
        if (transType === 4 || transType === 6) {
            returnForSelect.prop('disabled', false);
            $('.return-fields').addClass('show');
        } else {
            returnForSelect.prop('disabled', true).val('');
            $('.return-fields').removeClass('show');
            clearReturnTransactionData();
        }
    });
    
    // Event สำหรับการเลือก return transaction
    $('#return-for-trans-select').change(function() {
        var selectedTransId = $(this).val();
        loadOriginalTransaction(selectedTransId);
    });
    
    // Event สำหรับการกรอกจำนวนคืน
    $(document).on('input', '.qty-input', function() {
        var index = $(this).data('index');
        var availableQty = parseFloat($(this).data('available-qty')) || 0;
        var inputQty = parseFloat($(this).val()) || 0;
        
        // ตรวจสอบจำนวนที่กรอก
        if (inputQty > availableQty) {
            $(this).val(availableQty);
            alert('จำนวนที่คืนไม่สามารถเกินจำนวนที่สามารถคืนได้ (' + availableQty + ')');
            inputQty = availableQty;
        }
        
        // แสดง/ซ่อนปุ่มสภาพสินค้า
        var conditionBtn = $('.condition-btn[data-index="' + index + '"]');
        if (inputQty > 0 && ($('#trans-type-select').val() == '4') || ($('#trans-type-select').val() == '6')) { // Return Borrow
          //  conditionBtn.show();
        } else {
            conditionBtn.hide();
            // ล้างข้อมูลสภาพสินค้า
            setConditionHiddenFields(index, 0, 0, 0, '', '');
            updateConditionSummary(index, 0, 0, 0);
        }
    });
    
    // Event สำหรับการคำนวณผลรวมสภาพสินค้า
    $('#condition-good-qty, #condition-damaged-qty, #condition-missing-qty').on('input', updateConditionTotal);
    
    // Initial setup
    var currentTransType = $('#trans-type-select').val();
    if (currentTransType == '4' || currentTransType == '6') {
        $('#return-for-trans-select').prop('disabled', false);
        $('.return-fields').addClass('show');
    }
});
JS;

$this->registerJs($returnTransactionJs, \yii\web\View::POS_READY);

// JavaScript เดิมสำหรับ autocomplete และ calculation
$originalJs = <<<JS
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
}

$(document).ready(function() {
    loadProductsData();
    
    // Initialize on page load
    var transType = $('#trans-type-select').val();
    if (transType) {
        updateStockType(transType);
    }

    // Load stock for existing items
    $('.item').each(function() {
        var index = $(this).find('.product-id-hidden').attr('data-index');
        var productId = $(this).find('.product-id-hidden').val();
        var warehouseId = $(this).find('.warehouse-select').val();
        
        if (productId) {
            // Load warehouse options and stock info
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
                    
                    // Update warehouse options but keep current selection
                    var warehouseSelect = $('.warehouse-select[data-index="' + index + '"]');
                    var currentWarehouseId = warehouseId;
                    
                    // If it's not a return transaction, we might want to update options
                    // But for returns, the warehouse is usually fixed from the original transaction
                    
                    // Update stock on hand display for the selected warehouse
                    if (currentWarehouseId) {
                        var selectedStock = data.find(s => s.warehouse_id == currentWarehouseId);
                        if (selectedStock) {
                            $('.line-product-onhand[data-index="' + index + '"]').val(selectedStock.qty);
                        }
                    }
                }
            });
        }
    });
});


JS;

$this->registerJs($originalJs, \yii\web\View::POS_READY);
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

            <?= $form->field($model, 'stock_type_id')->dropDownList(
                JournalTrans::getStockTypeOptions(),
                ['prompt' => 'Select Stock Type', 'id' => 'stock-type-select', 'readonly' => true]
            ) ?>


        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'customer_name')->textInput(['maxlength' => true]) ?>

            <div class="return-fields">
                <?= $form->field($model, 'return_for_trans_id')->widget(Select2::className(), [
                    'data' => ArrayHelper::map(
                        JournalTrans::find()
                            ->where(['trans_type_id' => [3, 5]]) // Issue Stock, Issue Borrow
                            ->andWhere(['status' => JournalTrans::STATUS_APPROVED])
                            ->asArray()->all(),
                        'id', 'journal_no'
                    ),
                    'options' => [
                        'id' => 'return-for-trans-select',
                        'placeholder' => 'Select Return for Transaction',
                        'disabled' => true
                    ],
                ]) ?>
            </div>

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
    <?php
    $warehouses = ArrayHelper::map(\common\models\Warehouse::find()->all(), 'id', 'name');
    ?>
    <hr>
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">รายละเอียดสินค้า</h5>
            <?php if ($canEdit): ?>
                <button type="button" class="add-item btn btn-success btn-sm"><i class="fa fa-plus"></i> เพิ่มรายการ</button>
            <?php endif; ?>
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
                'model' => $lines[0] ?? new \backend\models\JournalTransLine(),
                'formId' => 'journal-trans-form',
                'formFields' => [
                    'product_id',
                    'warehouse_id',
                    'qty',
                    'line_price',
                    'unit_id',
                    'good_qty',
                    'damaged_qty',
                    'missing_qty',
                    'condition_note',
                    'return_note',
                ],
            ]); ?>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">ลำดับ</th>
                        <th style="width: 200px;">สินค้า</th>
                        <th style="width: 150px;">คลังจัดเก็บ</th>
                        <th style="width: 100px;">ยอดเบิกไป</th>
                        <th style="width: 100px;">คงเหลือคืนได้</th>
                        <th style="width: 120px;">จำนวนคืน</th>
                        <th style="width: 80px;">หน่วยนับ</th>
                        <th style="width: 100px;">สภาพสินค้า</th>
                        <th style="width: 100px;">หมายเหตุ</th>
                        <th style="width: 50px;">-</th>
                    </tr>
                    </thead>
                    <tbody class="container-items">
                    <?php 
                    $lines_to_show = $lines;
                    if (empty($lines_to_show)) {
                        $lines_to_show = [new \backend\models\JournalTransLine()];
                    }
                    ?>
                    <?php foreach ($lines_to_show as $index => $journaltransline): ?>
                            <tr class="item">
                                <td class="text-center align-middle">
                                    <span class="item-number"><?= $index + 1 ?></span>
                                </td>
                                <td>
                                    <?php if (!$journaltransline->isNewRecord): ?>
                                        <?= Html::activeHiddenInput($journaltransline, "[{$index}]id") ?>
                                    <?php endif; ?>

                                    <div class="product-field-container" style="width: 100%">
                                        <?= Html::activeHiddenInput($journaltransline, "[{$index}]product_id", [
                                            'class' => 'product-id-hidden',
                                            'data-index' => $index,
                                        ]) ?>

                                        <?= $form->field($journaltransline, "[{$index}]product_name")->textInput([
                                            'class' => 'form-control product-autocomplete',
                                            'placeholder' => 'พิมพ์ชื่อสินค้าหรือรหัสสินค้า...',
                                            'data-index' => $index,
                                            'autocomplete' => 'off',
                                            'style' => 'width: 100%',
                                            'value' => \backend\models\Product::findName($journaltransline->product_id),
                                            'readonly' => $isApproved
                                        ])->label(false) ?>

                                        <div class="autocomplete-dropdown" data-index="<?= $index ?>"></div>
                                    </div>
                                </td>

                                <td>
                                    <?= $form->field($journaltransline, "[{$index}]warehouse_id")->dropDownList(
                                        $warehouses,
                                        [
                                            'prompt' => '-- เลือกคลัง --',
                                            'class' => 'form-control warehouse-select',
                                            'data-index' => $index,
                                            'style' => 'width: 100%',
                                            'disabled' => $isApproved
                                        ]
                                    )->label(false) ?>
                                </td>
                                <td>
                                    <input type="text" class="form-control line-product-onhand" name="stock_qty"
                                           readonly value="" data-index="<?= $index ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control text-center readonly-field"
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
                                    <?= $form->field($journaltransline, "[{$index}]unit_id")->textInput([
                                        'readonly' => true,
                                        'class' => 'form-control line-unit-id',
                                        'style' => 'background-color: #f8f9fa;',
                                        'data-index' => $index,
                                        'value' => \backend\models\Unit::findName($journaltransline->unit_id),
                                    ])->label(false) ?>
                                </td>
                                <td class="text-center">
                                    <?= $form->field($journaltransline, "[{$index}]is_damage")->dropDownList(ArrayHelper::map($damage_list, 'id', 'name'), ['class' => 'form-control', 'data-index' => $index, 'disabled' => $isApproved])->label(false) ?>
                                </td>
                                <td class="align-middle">
                                    <?= $form->field($journaltransline, "[{$index}]return_note")->textInput([
                                        'class' => 'form-control note-input', 'readonly' => $isApproved])->label(false) ?>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($canEdit): ?>
                                        <button type="button" class="remove-item btn btn-danger btn-sm"><i class="fa fa-minus"></i></button>
                                    <?php endif; ?>
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

    <?php if ($canEdit): ?>
    <div class="form-group">
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', [
                    'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
                ]) ?>
                <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>
        <?php endif; ?>

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
                                    <a href="<?= Yii::$app->request->BaseUrl . '/uploads/journal_trans_doc/' . $value->doc_name ?>"
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

        <form action="<?= Url::to(['journaltrans/add-doc-file'], true) ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $model->id ?>">
            <div style="padding: 10px;background-color: lightgrey;border-radius: 5px">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">เอกสารแนบ</label>
                        <input type="file" name="file_doc[]" multiple>
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
        <form id="form-delete-doc-file" action="<?= Url::to(['journaltrans/delete-doc-file'], true) ?>" method="post">
            <input type="hidden" name="id" value="<?= $model->id ?>">
            <input type="hidden" class="delete-doc-list" name="doc_delete_list" value="">
        </form>

    </div>

    <?php
    // Modal สำหรับบันทึกสภาพสินค้า
    Modal::begin([
        'id' => 'conditionModal',
//    'header' => '<h4><i class="fa fa-edit"></i> บันทึกสภาพสินค้า</h4>',
        'size' => Modal::SIZE_LARGE,
        'footer' =>
            Html::button('ยกเลิก', ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) . ' ' .
            Html::button('บันทึก', ['class' => 'btn btn-primary', 'onclick' => 'saveConditionData()'])
    ]);
    ?>

    <div class="row">
        <div class="col-md-8">
            <h5>จำนวนตามสภาพสินค้า</h5>

            <div class="form-group">
                <label for="condition-good-qty">สภาพดี:</label>
                <input type="number" class="form-control" id="condition-good-qty"
                       step="0.01" min="0" placeholder="0.00">
            </div>

            <div class="form-group">
                <label for="condition-damaged-qty">เสียหาย:</label>
                <input type="number" class="form-control" id="condition-damaged-qty"
                       step="0.01" min="0" placeholder="0.00">
            </div>

            <div class="form-group">
                <label for="condition-missing-qty">สูญหาย:</label>
                <input type="number" class="form-control" id="condition-missing-qty"
                       step="0.01" min="0" placeholder="0.00">
            </div>

            <div class="form-group">
                <label for="condition-note">หมายเหตุสภาพสินค้า:</label>
                <textarea class="form-control" id="condition-note" rows="3"
                          placeholder="ระบุรายละเอียดเพิ่มเติมเกี่ยวกับสภาพสินค้า..."></textarea>
            </div>

            <div class="form-group">
                <label for="return-note">หมายเหตุการคืน:</label>
                <textarea class="form-control" id="return-note" rows="2"
                          placeholder="หมายเหตุเพิ่มเติมเกี่ยวกับการคืนสินค้า..."></textarea>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h5 class="panel-title">สรุป</h5>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label>รวมทั้งหมด:</label>
                        <div class="well well-sm">
                            <span id="condition-total" class="lead">0</span> หน่วย
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>หมายเหตุ:</strong><br>
                        ผลรวมจำนวนสภาพสินค้าต้องเท่ากับจำนวนที่คืนทั้งหมด
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php Modal::end(); ?>

    <?php
    $this->registerJs("
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
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
