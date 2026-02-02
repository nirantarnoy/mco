<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use backend\models\PaymentVoucher;

/* @var $this yii\web\View */
/* @var $model backend\models\PaymentVoucher */
/* @var $form yii\widgets\ActiveForm */


$pullMultipleUrl = Url::to(['pull-multiple']);
$getPrByVendorUrl = Url::to(['get-pr-by-vendor']);
$getPoByVendorUrl = Url::to(['get-po-by-vendor']);

$js = <<<JS
var line_count = 0;

function addLine(data = null) {
    var tr = $('<tr class="line-item">');
    tr.append('<td><input type="text" name="line_account_code[]" class="form-control" value="' + (data ? data.account_code : '') + '"></td>');
    tr.append('<td><input type="text" name="line_bill_code[]" class="form-control" value="' + (data ? data.bill_code : '') + '"></td>');
    tr.append('<td><input type="text" name="line_description1[]" class="form-control" value="' + (data ? data.description1 : '') + '"></td>');
    tr.append('<td><input type="text" name="line_description2[]" class="form-control" value="' + (data ? data.description2 : '') + '"></td>');
    tr.append('<td><input type="number" name="line_debit[]" class="form-control line-debit" step="0.01" value="' + (data ? data.debit : '0.00') + '"></td>');
    tr.append('<td><input type="number" name="line_credit[]" class="form-control line-credit" step="0.01" value="' + (data ? data.credit : '0.00') + '"></td>');
    tr.append('<td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-line"><i class="fa fa-trash"></i></button></td>');
    $('#voucher-lines tbody').append(tr);
}

function calculateTotal() {
    var total_debit = 0;
    var total_credit = 0;
    $('.line-debit').each(function() {
        total_debit += parseFloat($(this).val()) || 0;
    });
    $('.line-credit').each(function() {
        total_credit += parseFloat($(this).val()) || 0;
    });
    $('#total-debit').text(total_debit.toFixed(2));
    $('#total-credit').text(total_credit.toFixed(2));
    $('#paymentvoucher-amount').val(total_debit > 0 ? total_debit : total_credit);
}

function loadPrPoByVendor(vendorId) {
    if (!vendorId) {
        $('#pr-po-section').hide();
        $('#pull-data-section').hide();
        $('#pr-select').empty().trigger('change');
        $('#po-select').empty().trigger('change');
        return;
    }
    
    $('#pr-po-section').show();
    
    // โหลด PR
    $.ajax({
        url: '{$getPrByVendorUrl}',
        data: {vendor_id: vendorId},
        success: function(data) {
            $('#pr-select').empty();
            $.each(data, function(i, item) {
                var option = new Option(item.text, item.id, false, false);
                $('#pr-select').append(option);
            });
            $('#pr-select').trigger('change');
        }
    });
    
    // โหลด PO
    $.ajax({
        url: '{$getPoByVendorUrl}',
        data: {vendor_id: vendorId},
        success: function(data) {
            $('#po-select').empty();
            $.each(data, function(i, item) {
                var option = new Option(item.text, item.id, false, false);
                $('#po-select').append(option);
            });
            $('#po-select').trigger('change');
        }
    });
}

function pullMultipleData() {
    var pr_ids = $('#pr-select').val() || [];
    var po_ids = $('#po-select').val() || [];
    
    if (pr_ids.length === 0 && po_ids.length === 0) {
        alert('กรุณาเลือก PR หรือ PO อย่างน้อย 1 รายการ');
        return;
    }
    
    $('#pull-data-section').show();
    
    $.ajax({
        url: '{$pullMultipleUrl}',
        type: 'POST',
        data: {pr_ids: pr_ids, po_ids: po_ids},
        success: function(res) {
            if(res.success) {
                $('#paymentvoucher-amount').val(res.amount);
                $('#paymentvoucher-paid_for').val(res.paid_for);
                
                // ดึงชื่อ vendor
                var vendorName = $('#vendor-select option:selected').text();
                $('#paymentvoucher-recipient_name').val(vendorName);
                
                // บันทึก pr_ids และ po_ids ลง hidden inputs
                $('#hidden-pr-ids').val(JSON.stringify(res.pr_ids));
                $('#hidden-po-ids').val(JSON.stringify(res.po_ids));
                
                $('#voucher-lines tbody').empty();
                res.lines.forEach(function(line) {
                    addLine(line);
                });
                calculateTotal();
            }
        }
    });
}

// แสดงปุ่มดึงข้อมูลเมื่อมีการเลือก PR/PO
$('#pr-select, #po-select').on('change', function() {
    var pr_ids = $('#pr-select').val() || [];
    var po_ids = $('#po-select').val() || [];
    
    if (pr_ids.length > 0 || po_ids.length > 0) {
        $('#pull-data-section').show();
    } else {
        $('#pull-data-section').hide();
    }
});

$(document).ready(function() {
    calculateTotal();
    
    // Event listener สำหรับปุ่มเพิ่มแถว
    $('#btn-add-line').on('click', function() {
        addLine();
    });
    
    // Event delegation สำหรับ debit/credit inputs
    $('#voucher-lines tbody').on('input', '.line-debit, .line-credit', function() {
        calculateTotal();
    });
    
    // Event delegation สำหรับปุ่มลบแถว
    $('#voucher-lines tbody').on('click', '.btn-remove-line', function() {
        $(this).closest('tr').remove();
        calculateTotal();
    });
    
    // อัปเดต hidden inputs เมื่อ PR/PO select เปลี่ยน
    $('#pr-select').on('change', function() {
        var selectedPrIds = $(this).val() || [];
        $('#hidden-pr-ids').val(JSON.stringify(selectedPrIds));
    });
    
    $('#po-select').on('change', function() {
        var selectedPoIds = $(this).val() || [];
        $('#hidden-po-ids').val(JSON.stringify(selectedPoIds));
    });
});
JS;
$this->registerJs($js);
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

?>

<div class="payment-voucher-form">

    <?php $form = ActiveForm::begin(['id' => 'payment-voucher-form']); ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">ข้อมูล Payment Voucher</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <?= $form->field($model, 'vendor_id')->widget(Select2::className(), [
                        'data' => ArrayHelper::map(\backend\models\Vendor::find()->all(), 'id', 'name'),
                        'options' => ['placeholder' => 'เลือก Vendor...', 'id' => 'vendor-select'],
                        'pluginOptions' => ['allowClear' => true],
                        'pluginEvents' => [
                            "change" => "function() { loadPrPoByVendor($(this).val()); }",
                        ]
                    ])->label('Vendor') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'voucher_no')->textInput(['maxlength' => true, 'placeholder' => 'ระบุเลขที่ หรือปล่อยว่างเพื่อสร้างอัตโนมัติ']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'trans_date')->widget(DatePicker::className(), [
                        'options' => ['placeholder' => 'เลือกวันที่'],
                        'pluginOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'autoclose' => true,
                            'todayHighlight' => true,
                        ]
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3" id="pr-po-section" style="display:none;">
                <div class="col-md-6">
                    <label class="form-label">เลือกใบขอซื้อ (PR) - เลือกได้หลายรายการ</label>
                    <?= Select2::widget([
                        'name' => 'pr_ids[]',
                        'data' => [],
                        'options' => [
                            'placeholder' => 'เลือก PR...',
                            'multiple' => true,
                            'id' => 'pr-select'
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                    <small class="text-muted">กรุณาเลือก Vendor ก่อน</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">เลือกใบสั่งซื้อ (PO) - เลือกได้หลายรายการ</label>
                    <?= Select2::widget([
                        'name' => 'po_ids[]',
                        'data' => [],
                        'options' => [
                            'placeholder' => 'เลือก PO...',
                            'multiple' => true,
                            'id' => 'po-select'
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                    <small class="text-muted">กรุณาเลือก Vendor ก่อน</small>
                </div>
            </div>

            <!-- <div class="row mb-3" id="pull-data-section" style="display:none;">
                <div class="col-md-12 text-center">
                    <button type="button" class="btn btn-primary" onclick="pullMultipleData()">
                        <i class="fa fa-download"></i> ดึงข้อมูลจาก PR/PO ที่เลือก
                    </button>
                </div>
            </div> -->

            <div class="row mb-3">
                <div class="col-md-4">
                    <?= $form->field($model, 'recipient_name')->textInput(['maxlength' => true, 'placeholder' => 'ชื่อผู้รับเงิน']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'payment_method')->radioList(PaymentVoucher::getPaymentMethodOptions(), [
                        'item' => function ($index, $label, $name, $checked, $value) {
                            $check = $checked ? 'checked' : '';
                            return "<div class='form-check form-check-inline'>
                                <input class='form-check-input' type='radio' name='{$name}' value='{$value}' {$check} id='pay_method_{$value}'>
                                <label class='form-check-label' for='pay_method_{$value}'>{$label}</label>
                            </div>";
                        }
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3 cheque-info" style="<?= $model->payment_method == PaymentVoucher::PAY_METHOD_CHEQUE ? '' : 'display:none;' ?>">
                <div class="col-md-4">
                    <?= $form->field($model, 'cheque_no')->textInput(['maxlength' => true, 'placeholder' => 'เลขที่เช็ค']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'cheque_date')->widget(DatePicker::className(), [
                        'options' => ['placeholder' => 'เลือกวันที่หน้าเช็ค'],
                        'pluginOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'autoclose' => true,
                        ]
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'readonly' => true]) ?>
                </div>
                <div class="col-md-8">
                    <?= $form->field($model, 'paid_for')->textInput(['maxlength' => true, 'placeholder' => 'จ่ายสำหรับ PR/PO/QT เลขที่...']) ?>
                </div>
            </div>

            <?= Html::activeHiddenInput($model, 'ref_id') ?>
            <?= Html::activeHiddenInput($model, 'ref_type') ?>
            <input type="hidden" id="hidden-pr-ids" name="pr_ids" value="">
            <input type="hidden" id="hidden-po-ids" name="po_ids" value="">
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">รายละเอียดการลงบัญชี</h5>
            <button type="button" class="btn btn-primary btn-sm" id="btn-add-line">
                <i class="fa fa-plus"></i> เพิ่มแถว
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="voucher-lines">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%">Code Acc.</th>
                            <th style="width: 10%">Code Bill</th>
                            <th style="width: 25%">Description</th>
                            <th style="width: 25%"></th>
                            <th style="width: 12%">Debit</th>
                            <th style="width: 12%">Credit</th>
                            <th style="width: 6%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($model->isNewRecord): ?>
                            <!-- Empty row for new record -->
                        <?php else: ?>
                            <?php foreach ($model->paymentVoucherLines as $line): ?>
                                <?php
                                // แยก description ออกเป็น 2 ช่อง (ถ้ามี)
                                $descriptions = explode('|||', $line->description);
                                $desc1 = $descriptions[0] ?? '';
                                $desc2 = $descriptions[1] ?? '';
                                ?>
                                <tr class="line-item">
                                    <td><input type="text" name="line_account_code[]" class="form-control" value="<?= Html::encode($line->account_code) ?>"></td>
                                    <td><input type="text" name="line_bill_code[]" class="form-control" value="<?= Html::encode($line->bill_code) ?>"></td>
                                    <td><input type="text" name="line_description1[]" class="form-control" value="<?= Html::encode($desc1) ?>"></td>
                                    <td><input type="text" name="line_description2[]" class="form-control" value="<?= Html::encode($desc2) ?>"></td>
                                    <td><input type="number" name="line_debit[]" class="form-control line-debit" step="0.01" value="<?= $line->debit ?>"></td>
                                    <td><input type="number" name="line_credit[]" class="form-control line-credit" step="0.01" value="<?= $line->credit ?>"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-line">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">ยอดรวม</th>
                            <th id="total-debit" class="text-end">0.00</th>
                            <th id="total-credit" class="text-end">0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="form-group text-end mt-4">
        <?= Html::submitButton($model->isNewRecord ? 'บันทึกรายการ' : 'อัพเดทรายการ', ['class' => 'btn btn-success btn-lg px-5']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$chequeValue = PaymentVoucher::PAY_METHOD_CHEQUE;
$script = <<<JS
$('input[name="PaymentVoucher[payment_method]"]').change(function() {
    if($(this).val() == '{$chequeValue}') {
        $('.cheque-info').show();
    } else {
        $('.cheque-info').hide();
    }
});
JS;
$this->registerJs($script);
?>

<style>
.card { border-radius: 8px; overflow: hidden; }
.card-header { font-weight: 600; }
.form-label { font-weight: 500; }
#voucher-lines th { text-align: center; }
#voucher-lines td { padding: 8px; }
.btn-lg { border-radius: 30px; }
</style>
