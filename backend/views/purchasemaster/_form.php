<?php

use kartik\date\DatePicker;
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
$model_doc = \common\models\PurchNonePrDoc::find()->where(['purchase_master_id' => $model->id])->all();

$urlSearchProduct = Url::to(['search-product']);
$urlGetVendor = Url::to(['get-vendor']);

$this->registerJs(<<<JS
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
    
    var discount = parseFloat($('#purchasemaster-disc').val()) || 0;
    var afterDiscount = subtotal - discount;
    if (afterDiscount < 0) afterDiscount = 0;

    var vatPercent = parseFloat($('#purchasemaster-vat_percent').val()) || 0;
    var taxPercent = parseFloat($('#purchasemaster-tax_percent').val()) || 0;
    
    var vatAmount = (afterDiscount * vatPercent) / 100;
    var taxAmount = (afterDiscount * taxPercent) / 100;
    var total = afterDiscount + vatAmount - taxAmount;
    
    $('#purchasemaster-vatpr0').val(afterDiscount.toFixed(2));
    $('#purchasemaster-vat_amount').val(vatAmount.toFixed(2));
    $('#purchasemaster-tax_amount').val(taxAmount.toFixed(2));
    $('#purchasemaster-total_amount').val(total.toFixed(2));
    
    $('#display-subtotal').text(subtotal.toFixed(2));
    $('#display-vat').text(vatAmount.toFixed(2));
    $('#display-tax').text(taxAmount.toFixed(2));
    $('#display-total').text(total.toFixed(2));
}

$(document).on('input', '#purchasemaster-disc', function() {
    calculateTotal();
});

$(document).on('input', '#purchasemaster-vat_percent, #purchasemaster-tax_percent', function() {
    calculateTotal();
});

// Autocomplete สำหรับสินค้า
function initAutocomplete() {
    $('.product-autocomplete').each(function() {
        var input = $(this);
        var index = input.data('index');
        var suggestionsDiv = $('<div class="autocomplete-suggestions"></div>');
        input.after(suggestionsDiv);
        suggestionsDiv.hide();
        
        input.on('input', function() {
            var query = $(this).val();
            
            if (query.length < 2) {
                suggestionsDiv.hide();
                return;
            }
            
            $.ajax({
                url: '$urlSearchProduct',
                data: { q: query },
                dataType: 'json',
                success: function(data) {
                    suggestionsDiv.empty();
                    
                    if (data.length > 0) {
                        $.each(data, function(i, item) {
                            var div = $('<div class="autocomplete-suggestion"></div>')
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
            
            row.find('input[name*="[stkcod]"]').val(item.code);
            row.find('input[name*="[stkdes]"]').val(item.name);
            row.find('input[name*="[unitpr]"]').val(item.price || 0);
            
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

    // ดึงข้อมูลผู้ขายเมื่อมีการเลือก
    $('#purchasemaster-supcod').on('change', function() {
        var vendorId = $(this).val();
        if (vendorId) {
            $.ajax({
                url: '$urlGetVendor',
                data: { id: vendorId },
                dataType: 'json',
                success: function(data) {
                    if (data) {
                        $('#purchasemaster-supnam').val(data.name);
                        $('#purchasemaster-addr01').val(data.addr01);
                        $('#purchasemaster-addr02').val(data.addr02);
                        $('#purchasemaster-addr03').val(data.addr03);
                        $('#purchasemaster-zipcod').val(data.zipcod);
                        $('#purchasemaster-telnum').val(data.telnum);
                        $('#purchasemaster-taxid').val(data.taxid);
                        $('#purchasemaster-orgnum').val(data.orgnum);
                    }
                }
            });
        } else {
            $('#purchasemaster-supnam').val('');
            $('#purchasemaster-addr01').val('');
            $('#purchasemaster-addr02').val('');
            $('#purchasemaster-addr03').val('');
            $('#purchasemaster-zipcod').val('');
            $('#purchasemaster-telnum').val('');
            $('#purchasemaster-taxid').val('');
            $('#purchasemaster-orgnum').val('');
        }
    });
    
    initAutocomplete();
    calculateTotal();
});
JS
);
?>

<div class="purchase-master-form">
    <!-- Flash Messages -->
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
        'id' => 'purchase-form',
        'options' => ['class' => 'form-horizontal','enctype' => 'multipart/form-data'],
    ]); ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">แผนก</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'department_id')->widget(\kartik\select2\Select2::className(),[
                                        'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Department::find()->all(),'id','name'),
                                        'options'=>['placeholder'=>'เลือกแผนก'],
                                        'pluginOptions' => ['allowClear' => true]
                                    ])->label(false) ?>
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">รหัสผู้จำหน่าย</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'supcod')->widget(\kartik\select2\Select2::className(),[
                                        'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Vendor::find()->all(),'id','code'),
                                        'options'=>['placeholder'=>'เลือกรหัสผู้ขาย'],
                                        'pluginOptions' => ['allowClear' => true]
                                    ])->label(false) ?>
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">ชื่อผู้จำหน่าย</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'supnam')->textInput(['readonly'=>'readonly', 'placeholder'=>'ดึงมาจากฐานข้อมูลผู้ขาย'])->label(false) ?>
                                </div>
                            </div>
                            
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">ที่อยู่</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'addr01')->textInput(['placeholder'=>'ที่อยู่ 1', 'class' => 'form-control form-control-sm mb-1'])->label(false) ?>
                                    <?= $form->field($model, 'addr02')->textInput(['placeholder'=>'ที่อยู่ 2', 'class' => 'form-control form-control-sm mb-1'])->label(false) ?>
                                    <?= $form->field($model, 'addr03')->textInput(['placeholder'=>'ที่อยู่ 3', 'class' => 'form-control form-control-sm'])->label(false) ?>
                                </div>
                            </div>
                            
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">รหัสไปรษณีย์</label>
                                <div class="col-sm-3">
                                    <?= $form->field($model, 'zipcod')->textInput(['placeholder'=>'รหัสไปรษณีย์', 'class' => 'form-control form-control-sm'])->label(false) ?>
                                </div>
                                <label class="col-sm-2 col-form-label text-right" style="padding-right: 5px;">เบอร์โทร</label>
                                <div class="col-sm-3">
                                    <?= $form->field($model, 'telnum')->textInput(['placeholder'=>'เบอร์โทร', 'class' => 'form-control form-control-sm'])->label(false) ?>
                                </div>
                            </div>

                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">เลขผู้เสียภาษี</label>
                                <div class="col-sm-3">
                                    <?= $form->field($model, 'taxid')->textInput(['placeholder'=>'เลขประจำตัวผู้เสียภาษี', 'class' => 'form-control form-control-sm'])->label(false) ?>
                                </div>
                                <label class="col-sm-2 col-form-label text-right" style="padding-right: 5px;">สาขา</label>
                                <div class="col-sm-3">
                                    <?= $form->field($model, 'orgnum')->textInput(['placeholder'=>'ลำดับสาขา', 'class' => 'form-control form-control-sm'])->label(false) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">เลขที่บิล</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'refnum')->textInput(['placeholder'=>'กรอกข้อมูลเอง'])->label(false) ?>
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">วันที่บิล</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'docdat')->input('date')->label(false) ?>
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">JOB No.</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'job_no')->widget(\kartik\select2\Select2::className(),[
                                        'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Job::find()->all(),'id','job_no'),
                                        'options'=>['placeholder'=>'ดึงมาจากฐานข้อมูลใบงาน'],
                                        'pluginOptions' => ['allowClear' => true]
                                    ])->label(false) ?>
                                </div>
                            </div>
                            
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">เครดิต</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'paytrm')->widget(\kartik\select2\Select2::className(),[
                                        'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Paymentterm::find()->all(),'id','name'),
                                        'options'=>['placeholder'=>'กรอกข้อมูลเอง'],
                                        'pluginOptions' => ['allowClear' => true]
                                    ])->label(false) ?>
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">วันครบกำหนด</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'duedat')->textInput(['readonly' => true, 'placeholder'=>'คำนวณจากเครดิต'])->label(false) ?>
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">หมายเหตุ</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'remark')->textInput(['placeholder'=>'กรอกข้อมูลเอง'])->label(false) ?>
                                </div>
                            </div>
                        </div>
                    </div>
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
                        <th width="50" class="text-center">ลำดับที่</th>
                        <th width="150">รหัสสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th width="100" class="text-center">จำนวน</th>
                        <th width="120" class="text-center">ราคาต่อหน่วย</th>
                        <th width="150" class="text-center">ส่วนลดแต่ละรายการ</th>
                        <th width="150" class="text-center">จำนวนเงิน</th>
                        <th width="50" class="text-center">ลบ</th>
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
                                           placeholder="ดึงมาจากฐานข้อมูลสินค้า">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                           name="PurchaseDetail[<?= $index ?>][stkdes]"
                                           value="<?= Html::encode($detail->stkdes) ?>"
                                           placeholder="กรอกข้อมูลเอง">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-right qty-input"
                                           name="PurchaseDetail[<?= $index ?>][uqnty]"
                                           value="<?= $detail->uqnty ?>" placeholder="กรอกข้อมูลเอง">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-right price-input"
                                           name="PurchaseDetail[<?= $index ?>][unitpr]"
                                           value="<?= $detail->unitpr ?>" placeholder="กรอกข้อมูลเอง">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                           name="PurchaseDetail[<?= $index ?>][disc]"
                                           value="<?= Html::encode($detail->disc) ?>"
                                           placeholder="กรอกข้อมูลเอง">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm text-right amount-input"
                                           name="PurchaseDetail[<?= $index ?>][amount]"
                                           value="<?= $detail->amount ?>" readonly placeholder="คำนวณจาก (จำนวน x ราคาต่อหน่วย)">
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
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">เลขที่ใบกำกับ</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'invoice_no')->textInput(['class' => 'form-control form-control-sm', 'placeholder' => 'กรอกข้อมูลเอง'])->label(false) ?>
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">ลงวันที่บิล</label>
                                <div class="col-sm-8">
                                    <?= $form->field($model, 'vatdat')->input('date', ['class' => 'form-control form-control-sm'])->label(false) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row mb-2">
                                <label class="col-sm-5 col-form-label">ยื่นภาษีรวมในงวด</label>
                                <div class="col-sm-7">
                                    <?= $form->field($model, 'vat_period')->textInput(['class' => 'form-control form-control-sm', 'placeholder' => 'กรอกข้อมูลเอง'])->label(false) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row mt-2">
                        <label class="col-sm-2 col-form-label">อื่นเพิ่มเติม</label>
                        <div class="col-sm-10">
                            <?= $form->field($model, 'additional_note')->textInput(['class' => 'form-control form-control-sm', 'placeholder' => 'กรอกข้อมูลเอง'])->label(false) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-body p-2">
                    <table class="table table-sm table-bordered mb-0">
                        <tr>
                            <td class="text-right" width="50%"><strong>ส่วนลดท้ายบิล</strong></td>
                            <td width="50%">
                                <?= $form->field($model, 'disc')->textInput(['id' => 'purchasemaster-disc', 'class' => 'form-control form-control-sm text-right', 'placeholder' => 'กรอกข้อมูลเอง'])->label(false) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right"><strong>รวมเงิน</strong></td>
                            <td class="text-right">
                                <span id="display-subtotal">0.00</span>
                                <?= $form->field($model, 'vatpr0')->hiddenInput()->label(false) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right">
                                <strong>VAT</strong>
                                <div class="d-inline-block" style="width: 60px;">
                                    <?= $form->field($model, 'vat_percent')->textInput(['type' => 'number', 'step' => '0.01', 'class' => 'form-control form-control-sm text-right'])->label(false) ?>
                                </div>
                                <strong>%</strong>
                            </td>
                            <td class="text-right">
                                <span id="display-vat">0.00</span>
                                <?= $form->field($model, 'vat_amount')->hiddenInput()->label(false) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right">
                                <strong>TAX</strong>
                                <div class="d-inline-block" style="width: 60px;">
                                    <?= $form->field($model, 'tax_percent')->textInput(['type' => 'number', 'step' => '0.01', 'class' => 'form-control form-control-sm text-right'])->label(false) ?>
                                </div>
                                <strong>%</strong>
                            </td>
                            <td class="text-right">
                                <span id="display-tax">0.00</span>
                                <?= $form->field($model, 'tax_amount')->hiddenInput()->label(false) ?>
                            </td>
                        </tr>
                        <tr class="table-active">
                            <td class="text-right"><strong>รวมสุทธิ</strong></td>
                            <td class="text-right">
                                <strong><span id="display-total">0.00</span></strong>
                                <?= $form->field($model, 'total_amount')->hiddenInput()->label(false) ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">ค่ามัดจำ</h6>
                    <div class="row">
                        <div class="col-lg-3">
                            <?php echo $form->field($model, 'is_deposit')->widget(\toxor88\switchery\Switchery::className(), ['options' => ['label' => '', 'class' => 'form-control']])->label(false) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3">
                            <label for="">วันที่</label>
                            <?php
                            echo DatePicker::widget([
                                'name' => 'deposit_date',
                                'value' => $model_deposit_all != null ? date('Y-m-d', strtotime($model_deposit_all->trans_date)) : date('Y-m-d'),
                                'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                                'options' => [''],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                            ]);
                            ?>
                        </div>
                        <div class="col-lg-3">
                            <label for="">จำนวนเงินมัดจำ</label>
                            <input type="number" class="form-control" name="deposit_amount" min="0"
                                   value="<?= $model_deposit_line_all != null ? $model_deposit_line_all->deposit_amount : 0 ?>">
                        </div>
                        <div class="col-lg-3">
                            <label for="">เอกสารแนบ</label>
                            <input type="file" class="form-control" name="deposit_doc">
                        </div>
                        <div class="col-lg-3">
                            <label for="">เอกสารที่แนบแล้ว</label><br />
                            <?php
                            $deposit_doc_show = '';
                            if ($model_deposit_line_all != null) {
                                $deposit_doc_show = $model_deposit_line_all->deposit_doc;
                            }
                            ?>
                            <?php if ($deposit_doc_show!=''): ?>
                                <a href="<?= Yii::$app->request->BaseUrl . '/uploads/purch_doc/' . $deposit_doc_show ?>"
                                   target="_blank">
                                    ดูเอกสาร
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-3">
                            <label for="">วันที่รับมัดจำคืน</label>
                            <?php
                            echo DatePicker::widget([
                                'name' => 'deposit_receive_date',
                                'value' => $model_deposit_line_all != null ? date('Y-m-d', strtotime($model_deposit_line_all->receive_date)) : date('Y-m-d'),
                                'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                                'options' => [''],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                ]
                            ]);
                            ?>
                        </div>
                        <div class="col-lg-3">
                            <label for="">จำนวนเงินมัดจำ</label>
                            <?php
                            $rec_amount = 0;
                            if ($model_deposit_line_all != null) {
                                if ($model_deposit_line_all->receive_doc != null) {
                                    $rec_amount = $model_deposit_line_all->deposit_amount;
                                }
                            }
                            ?>
                            <input type="number" class="form-control" name="deposit_receive_amount" min="0"
                                   value="<?= $rec_amount ?>">
                        </div>
                        <div class="col-lg-3">
                            <label for="">เอกสารแนบ</label>
                            <input type="file" class="form-control" name="deposit_receive_doc">
                        </div>
                        <div class="col-lg-3">
                            <label for="">เอกสารที่แนบแล้ว</label><br />
                            <?php
                            $receive_doc_show = '';
                            if ($model_deposit_line_all != null) {
                                $receive_doc_show = $model_deposit_line_all->receive_doc;
                            }
                            ?>
                            <?php if ($receive_doc_show!=''): ?>
                                <a href="<?= Yii::$app->request->BaseUrl . '/uploads/purch_doc/' . $receive_doc_show ?>"
                                   target="_blank">
                                    ดูเอกสาร
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <br/>
        <div class="row">
            <div class="col-lg-4">
                <div style="padding: 10px;background-color: lightgrey;border-radius: 5px">
                    <div class="row">
                        <div class="col-lg-12">
                            <label for="">เอกสารแนบ PO Acknowledge</label>
                            <input type="file" name="file_acknowledge_doc" multiple>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div style="padding: 10px;background-color: lightgrey;border-radius: 5px">
                    <div class="row">
                        <div class="col-lg-12">
                            <label for="">เอกสารแนบ ใบกำกับภาษี</label>
                            <input type="file" name="file_invoice_doc" multiple>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div style="padding: 10px;background-color: lightgrey;border-radius: 5px">
                    <div class="row">
                        <div class="col-lg-12">
                            <label for="">เอกสารแนบ เอกสารจ่ายเงิน</label>
                            <input type="file" name="file_slip_doc" multiple>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    <th style="width: 50%;text-align: center">ประเภทเอกสาร</th>
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
                            <td><?= \backend\helpers\PurchDocType::getTypeById($value->doc_type_id) ?></td>
                            <td style="text-align: center">
                                <a href="<?= Yii::$app->request->BaseUrl . '/uploads/purch_doc/' . $value->doc_name ?>"
                                   target="_blank">
                                    ดูเอกสาร
                                </a>
                            </td>
                            <td style="text-align: center">
                                <div class="btn btn-danger" data-value="<?=$value->id;?>" data-var="<?= trim($value->doc_name) ?>"
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

    <div class="form-group mt-3">
        <?= Html::submitButton('<i class="fas fa-save"></i> บันทึก', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$url_to_delete_doc = Url::to(['purchasemaster/delete-doc'],true);
$js=<<<JS
$(function(){
    
});
function delete_doc(e){
    var id = e.attr("data-value");
    var doc_name = e.attr("data-var");
    if(id){
        if(confirm('ต้องการลบไฟล์ใช่หรือไม่?')){
            $.ajax({
                 url:'$url_to_delete_doc',
                 type:'POST',
                 dataType:'html',
                 data:{id:id,doc_name:doc_name},
                 success:function(data){
                   //  alert();
                     console.log(data);
                     location.reload();
                 },
                 error:function(jqXHR, textStatus, errorThrown){
                     console.log(jqXHR, textStatus, errorThrown);
                 }
            });
        }
    }
}
JS;
$this->registerJs($js,static::POS_END);
?>