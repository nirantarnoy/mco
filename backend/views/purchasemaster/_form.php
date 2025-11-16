<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var backend\models\PurchaseMaster $model */
/** @var yii\widgets\ActiveForm $form */

$this->registerCss("
.table-details {
    font-size: 0.9rem;
}
.table-details th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.autocomplete-suggestions {
    border: 1px solid #d4d4d4;
    background: #fff;
    overflow: auto;
    max-height: 200px;
    position: absolute;
    z-index: 9999;
}
.autocomplete-suggestion {
    padding: 5px 10px;
    cursor: pointer;
}
.autocomplete-suggestion:hover {
    background: #e8e8e8;
}
");

$this->registerJs("
var detailRowIndex = 0;

// เพิ่มแถวรายละเอียด
function addDetailRow() {
    detailRowIndex++;
    var row = `
        <tr class='detail-row' data-index='` + detailRowIndex + `'>
            <td class='text-center'>` + (detailRowIndex + 1) + `</td>
            <td>
                <input type='text' class='form-control form-control-sm product-autocomplete' 
                    name='PurchaseDetail[` + detailRowIndex + `][stkcod]' 
                    data-index='` + detailRowIndex + `'
                    placeholder='รหัสสินค้า'>
            </td>
            <td>
                <input type='text' class='form-control form-control-sm' 
                    name='PurchaseDetail[` + detailRowIndex + `][stkdes]' 
                    placeholder='รายละเอียด'>
            </td>
            <td>
                <input type='number' step='0.01' class='form-control form-control-sm text-right qty-input' 
                    name='PurchaseDetail[` + detailRowIndex + `][uqnty]' 
                    value='0'>
            </td>
            <td>
                <input type='number' step='0.01' class='form-control form-control-sm text-right price-input' 
                    name='PurchaseDetail[` + detailRowIndex + `][unitpr]' 
                    value='0'>
            </td>
            <td>
                <input type='text' class='form-control form-control-sm' 
                    name='PurchaseDetail[` + detailRowIndex + `][disc]' 
                    placeholder='10% หรือ 100'>
            </td>
            <td>
                <input type='number' step='0.01' class='form-control form-control-sm text-right amount-input' 
                    name='PurchaseDetail[` + detailRowIndex + `][amount]' 
                    value='0' readonly>
            </td>
            <td class='text-center'>
                <button type='button' class='btn btn-danger btn-sm btn-remove-row'>
                    <i class='fas fa-trash'></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#detail-table tbody').append(row);
    updateRowNumbers();
    initAutocomplete();
}

// ลบแถว
$(document).on('click', '.btn-remove-row', function() {
    $(this).closest('tr').remove();
    updateRowNumbers();
    calculateTotal();
});

// อัพเดทเลขลำดับ
function updateRowNumbers() {
    $('#detail-table tbody tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
    });
}

// คำนวณยอดเงินในแต่ละแถว
$(document).on('input', '.qty-input, .price-input', function() {
    var row = $(this).closest('tr');
    var qty = parseFloat(row.find('.qty-input').val()) || 0;
    var price = parseFloat(row.find('.price-input').val()) || 0;
    var amount = qty * price;
    
    row.find('.amount-input').val(amount.toFixed(2));
    calculateTotal();
});

// คำนวณยอดรวมทั้งหมด
function calculateTotal() {
    var subtotal = 0;
    
    $('.amount-input').each(function() {
        subtotal += parseFloat($(this).val()) || 0;
    });
    
    var vatPercent = parseFloat($('#purchasemaster-vat_percent').val()) || 0;
    var taxPercent = parseFloat($('#purchasemaster-tax_percent').val()) || 0;
    
    var vatAmount = (subtotal * vatPercent) / 100;
    var taxAmount = (subtotal * taxPercent) / 100;
    var total = subtotal + vatAmount - taxAmount;
    
    $('#purchasemaster-vatpr0').val(subtotal.toFixed(2));
    $('#purchasemaster-vat_amount').val(vatAmount.toFixed(2));
    $('#purchasemaster-tax_amount').val(taxAmount.toFixed(2));
    $('#purchasemaster-total_amount').val(total.toFixed(2));
    
    $('#display-subtotal').text(subtotal.toFixed(2));
    $('#display-vat').text(vatAmount.toFixed(2));
    $('#display-tax').text(taxAmount.toFixed(2));
    $('#display-total').text(total.toFixed(2));
}

// คำนวณเมื่อเปลี่ยน VAT หรือ TAX
$(document).on('input', '#purchasemaster-vat_percent, #purchasemaster-tax_percent', function() {
    calculateTotal();
});

// Autocomplete สำหรับสินค้า
function initAutocomplete() {
    $('.product-autocomplete').each(function() {
        var input = $(this);
        var index = input.data('index');
        var suggestionsDiv = $('<div class=\"autocomplete-suggestions\"></div>');
        input.after(suggestionsDiv);
        suggestionsDiv.hide();
        
        input.on('input', function() {
            var query = $(this).val();
            
            if (query.length < 2) {
                suggestionsDiv.hide();
                return;
            }
            
            $.ajax({
                url: '" . Url::to(['search-product']) . "',
                data: { q: query },
                dataType: 'json',
                success: function(data) {
                    suggestionsDiv.empty();
                    
                    if (data.length > 0) {
                        $.each(data, function(i, item) {
                            var div = $('<div class=\"autocomplete-suggestion\"></div>')
                                .text(item.code + ' - ' + item.name)
                                .data('item', item);
                            suggestionsDiv.append(div);
                        });
                        suggestionsDiv.show();
                    } else {
                        suggestionsDiv.hide();
                    }
                }
            });
        });
        
        suggestionsDiv.on('click', '.autocomplete-suggestion', function() {
            var item = $(this).data('item');
            var row = input.closest('tr');
            
            row.find('input[name*=\"[stkcod]\"]').val(item.code);
            row.find('input[name*=\"[stkdes]\"]').val(item.name);
            row.find('input[name*=\"[unitpr]\"]').val(item.price || 0);
            
            suggestionsDiv.hide();
            
            // คำนวณยอดเงิน
            row.find('.qty-input').trigger('input');
        });
        
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.product-autocomplete, .autocomplete-suggestions').length) {
                $('.autocomplete-suggestions').hide();
            }
        });
    });
}

// เริ่มต้น
$(document).ready(function() {
    // ถ้ามีรายละเอียดอยู่แล้ว (กรณี update)
    var existingRows = $('#detail-table tbody tr').length;
    if (existingRows > 0) {
        detailRowIndex = existingRows;
    }
    
    // เพิ่มแถวเริ่มต้น 5 แถว (กรณี create)
    if (existingRows === 0) {
        for (var i = 0; i < 5; i++) {
            addDetailRow();
        }
    }
    
    initAutocomplete();
    calculateTotal();
});
", \yii\web\View::POS_END);
?>

<div class="purchase-master-form">

    <?php $form = ActiveForm::begin([
        'id' => 'purchase-form',
        'options' => ['class' => 'form-horizontal'],
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">ข้อมูลหลัก</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'docnum')->textInput(['maxlength' => true, 'readonly' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'docdat')->input('date') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'supcod')->widget(\kartik\select2\Select2::className(),[
                                    'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Vendor::find()->all(),'id','code'),
                                'options'=>[
                                        'placeholder'=>'เลือกผู้ขาย'
                                ],
                                'pluginOptions' => [
                                        'allowClear' => true,
                                ]
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'job_no')->widget(\kartik\select2\Select2::className(),[
                                'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Job::find()->all(),'id','job_no'),
                                'options'=>[
                                    'placeholder'=>'เลือก job'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ]
                            ]) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'supnam')->textInput(['maxlength' => true,'readonly'=>'readonly']) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'paytrm')->widget(\kartik\select2\Select2::className(),[
                                    'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Paymentterm::find()->all(),'id','name'),
                                'options'=>[
                                    'placeholder'=>'เลือกเครดิต'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ]
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'duedat')->input('date') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'taxid')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'discod')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">ที่อยู่และข้อมูลอื่นๆ</h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'addr01')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'addr02')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'addr03')->textInput(['maxlength' => true]) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'zipcod')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'telnum')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'orgnum')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'refnum')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'vatdat')->input('date') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title">รายละเอียดสินค้า</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" onclick="addDetailRow()">
                    <i class="fas fa-plus"></i> เพิ่มแถว
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-details mb-0" id="detail-table">
                    <thead>
                    <tr>
                        <th width="50" class="text-center">ลำดับ</th>
                        <th width="150">รหัสสินค้า</th>
                        <th>รายละเอียด</th>
                        <th width="100" class="text-center">จำนวน</th>
                        <th width="120" class="text-center">ราคา/หน่วย</th>
                        <th width="100" class="text-center">ส่วนลด</th>
                        <th width="120" class="text-center">จำนวนเงิน</th>
                        <th width="80" class="text-center">ลบ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$model->isNewRecord): ?>
                        <?php foreach ($model->purchaseDetails as $index => $detail): ?>
                            <tr class="detail-row" data-index="<?= $index ?>">
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td>
                                    <input type="text" class="form-control form-control-sm product-autocomplete"
                                           name="PurchaseDetail[<?= $index ?>][stkcod]"
                                           value="<?= Html::encode($detail->stkcod) ?>"
                                           data-index="<?= $index ?>"
                                           placeholder="รหัสสินค้า">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                           name="PurchaseDetail[<?= $index ?>][stkdes]"
                                           value="<?= Html::encode($detail->stkdes) ?>"
                                           placeholder="รายละเอียด">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-right qty-input"
                                           name="PurchaseDetail[<?= $index ?>][uqnty]"
                                           value="<?= $detail->uqnty ?>">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-right price-input"
                                           name="PurchaseDetail[<?= $index ?>][unitpr]"
                                           value="<?= $detail->unitpr ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                           name="PurchaseDetail[<?= $index ?>][disc]"
                                           value="<?= Html::encode($detail->disc) ?>"
                                           placeholder="10% หรือ 100">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-right amount-input"
                                           name="PurchaseDetail[<?= $index ?>][amount]"
                                           value="<?= $detail->amount ?>" readonly>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <?= $form->field($model, 'remark')->textarea(['rows' => 3]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-right" width="60%"><strong>มูลค่าสินค้า:</strong></td>
                            <td class="text-right" width="40%">
                                <span id="display-subtotal">0.00</span> บาท
                                <?= $form->field($model, 'vatpr0')->hiddenInput()->label(false) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right">
                                <strong>VAT:</strong>
                                <?= $form->field($model, 'vat_percent')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'class' => 'form-control form-control-sm d-inline-block',
                                    'style' => 'width: 80px;'
                                ])->label(false) ?> %
                            </td>
                            <td class="text-right">
                                <span id="display-vat">0.00</span> บาท
                                <?= $form->field($model, 'vat_amount')->hiddenInput()->label(false) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right">
                                <strong>TAX:</strong>
                                <?= $form->field($model, 'tax_percent')->textInput([
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'class' => 'form-control form-control-sm d-inline-block',
                                    'style' => 'width: 80px;'
                                ])->label(false) ?> %
                            </td>
                            <td class="text-right">
                                <span id="display-tax">0.00</span> บาท
                                <?= $form->field($model, 'tax_amount')->hiddenInput()->label(false) ?>
                            </td>
                        </tr>
                        <tr class="table-active">
                            <td class="text-right"><h5><strong>รวมทั้งสิ้น:</strong></h5></td>
                            <td class="text-right">
                                <h5><strong><span id="display-total">0.00</span> บาท</strong></h5>
                                <?= $form->field($model, 'total_amount')->hiddenInput()->label(false) ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton('<i class="fas fa-save"></i> บันทึก', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>