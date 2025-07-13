<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\JournalTrans */
/* @var $warehouses array */
/* @var $products array */

$this->title = 'เบิกสินค้า';
$this->params['breadcrumbs'][] = ['label' => 'จัดการ Transaction', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Register JS for dynamic form and calculations
$this->registerJs("
var rowIndex = 0;

function addItem() {
    var html = `
        <tr class='item-row' data-index='` + rowIndex + `'>
            <td class='text-center'>` + (rowIndex + 1) + `</td>
            <td>
                <select name='items[` + rowIndex + `][product_id]' class='form-control product-select' data-index='` + rowIndex + `' required>
                    <option value=''>เลือกสินค้า</option>
                    " . implode('', array_map(function($id, $name) {
        return "<option value='{$id}'>{$name}</option>";
    }, array_keys($products), $products)) . "
                </select>
            </td>
            <td><span class='stock-display' id='stock-` + rowIndex + `'>-</span></td>
            <td>
                <input type='number' name='items[` + rowIndex + `][qty]' class='form-control qty-input' 
                       step='0.01' min='0.01' data-index='` + rowIndex + `' required>
            </td>
            <td><span class='price-display' id='price-` + rowIndex + `'>0.00</span></td>
            <td><span class='total-display' id='total-` + rowIndex + `'>0.00</span></td>
            <td>
                <input type='text' name='items[` + rowIndex + `][remark]' class='form-control' 
                       placeholder='หมายเหตุ'>
            </td>
            <td class='text-center'>
                <button type='button' class='btn btn-sm btn-danger' onclick='removeItem(this)'>
                    <i class='fas fa-trash'></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#items-table tbody').append(html);
    updateRowNumbers();
    rowIndex++;
}

function removeItem(btn) {
    $(btn).closest('tr').remove();
    updateRowNumbers();
    calculateTotal();
}

function updateRowNumbers() {
    $('#items-table tbody tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
    });
}

function calculateTotal() {
    var total = 0;
    $('.total-display').each(function() {
        total += parseFloat($(this).text()) || 0;
    });
    $('#grand-total').text(total.toFixed(2));
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
                    $('#stock-' + index).text(data.stock_qty);
                    $('#price-' + index).text(data.sale_price);
                    $('.qty-input[data-index=' + index + ']').attr('max', data.stock_qty);
                    calculateLineTotal(index);
                }
            }
        });
    } else {
        $('#stock-' + index).text('-');
        $('#price-' + index).text('0.00');
        $('#total-' + index).text('0.00');
        calculateTotal();
    }
});

$(document).on('keyup change', '.qty-input', function() {
    var index = $(this).data('index');
    var qty = parseFloat($(this).val()) || 0;
    var maxQty = parseFloat($(this).attr('max')) || 0;
    
    if (qty > maxQty) {
        $(this).val(maxQty);
        alert('จำนวนเบิกไม่สามารถเกินจำนวนคงเหลือได้');
        qty = maxQty;
    }
    
    calculateLineTotal(index);
});

function calculateLineTotal(index) {
    var qty = parseFloat($('.qty-input[data-index=' + index + ']').val()) || 0;
    var price = parseFloat($('#price-' + index).text()) || 0;
    var total = qty * price;
    
    $('#total-' + index).text(total.toFixed(2));
    calculateTotal();
}

$(document).ready(function() {
    addItem(); // Add first row
});
");
?>

<div class="transaction-issue-stock">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <?= Html::a('กลับ', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'issue-stock-form',
        'options' => ['class' => 'form-horizontal'],
    ]); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">ข้อมูลการเบิกสินค้า</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'trans_date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'เลือกวันที่'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ]
                    ]) ?>

                    <?= $form->field($model, 'warehouse_id')->widget(Select2::class, [
                        'data' => $warehouses,
                        'options' => [
                            'placeholder' => 'เลือกคลังสินค้า...',
                            'required' => true,
                        ],
                        'pluginOptions' => [
                            'allowClear' => false,
                            'width' => '100%',
                        ],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'customer_name')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'ชื่อผู้เบิก',
                        'required' => true,
                    ]) ?>

                    <?= $form->field($model, 'remark')->textarea([
                        'rows' => 3,
                        'placeholder' => 'หมายเหตุการเบิกสินค้า'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">รายการสินค้าที่เบิก</h5>
            <button type="button" class="btn btn-success btn-sm" onclick="addItem()">
                <i class="fas fa-plus"></i> เพิ่มรายการ
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="items-table">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 250px;">สินค้า</th>
                        <th style="width: 100px;">คงเหลือ</th>
                        <th style="width: 100px;">จำนวนเบิก</th>
                        <th style="width: 100px;">ราคา/หน่วย</th>
                        <th style="width: 100px;">รวมเงิน</th>
                        <th style="width: 150px;">หมายเหตุ</th>
                        <th style="width: 50px;">ลบ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Dynamic rows will be added here -->
                    </tbody>
                    <tfoot class="table-secondary">
                    <tr>
                        <td colspan="5" class="text-end"><strong>รวมทั้งสิ้น:</strong></td>
                        <td class="text-center">
                            <strong><span id="grand-total">0.00</span> บาท</strong>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <div class="d-flex justify-content-between">
            <?= Html::submitButton('<i class="fas fa-save"></i> บันทึกการเบิกสินค้า', [
                'class' => 'btn btn-danger',
                'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะบันทึกการเบิกสินค้า?'
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

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .stock-display, .price-display, .total-display {
        font-weight: bold;
        color: #495057;
    }

    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
</style>