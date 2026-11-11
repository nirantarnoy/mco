<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\select2\Select2Asset;
use kartik\date\DatePicker;

Select2Asset::register($this);
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use backend\models\PaymentVoucher;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model backend\models\PaymentVoucher */
/* @var $form yii\widgets\ActiveForm */


$pullMultipleUrl = Url::to(['pull-multiple']);
$getPrByVendorUrl = Url::to(['get-pr-by-vendor']);
$getPoByVendorUrl = Url::to(['get-po-by-vendor']);
$getNonePrByVendorUrl = Url::to(['get-none-pr-by-vendor']);
$removeAttachmentUrl = Url::to(['remove-attachment']);

$chart_of_accounts = \backend\models\ChartOfAccount::find()->where(['status' => 1])->all();
$account_options = '<option value="">-- เลือก --</option>';
$account_data = [];
foreach ($chart_of_accounts as $acc) {
    if ($acc->account_type == 1) continue; // ข้ามบัญชีคุม ให้เลือกได้เฉพาะบัญชีย่อย
    $acc_label = Html::encode($acc->account_code . ' - ' . $acc->account_name);
    $account_options .= '<option value="' . Html::encode($acc->account_code) . '">' . $acc_label . '</option>';
    $account_data[$acc->account_code] = $acc->account_name;
}
$account_data_json = json_encode($account_data);

// เตรียมข้อมูล Pre-Advance ที่เลือกไว้เดิม (สำหรับกรณี update)
$selectedPrIds = \backend\models\PaymentVoucherRef::find()
    ->where(['payment_voucher_id' => $model->id, 'ref_type' => \backend\models\PaymentVoucher::REF_TYPE_PRE_ADVANCE])
    ->select('ref_id')
    ->column();
$selectedPrList = [];
if (!empty($selectedPrIds)) {
    $selectedPrList = ArrayHelper::map(\backend\models\PreAdvance::find()->where(['id' => $selectedPrIds])->all(), 'id', 'pre_advance_no');
}

$selectedPoIds = \backend\models\PaymentVoucherRef::find()
    ->where(['payment_voucher_id' => $model->id, 'ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PO])
    ->select('ref_id')
    ->column();
$selectedPoList = [];
if (!empty($selectedPoIds)) {
    $selectedPoList = ArrayHelper::map(\backend\models\Purch::find()->where(['id' => $selectedPoIds])->all(), 'id', function($m) {
        $curr = '';
        if ($m->currency_id) {
            $c = \backend\models\Currency::findOne($m->currency_id);
            if ($c && strtoupper(trim($c->code)) !== 'THB') {
                $curr = ' (' . strtoupper(trim($c->code)) . ')';
            }
        }
        return $m->purch_no . $curr;
    });
}

$selectedNonePrIds = \backend\models\PaymentVoucherRef::find()
    ->where(['payment_voucher_id' => $model->id, 'ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_NONE_PR])
    ->select('ref_id')
    ->column();
$selectedNonePrList = [];
if (!empty($selectedNonePrIds)) {
    $selectedNonePrList = ArrayHelper::map(\backend\models\PurchaseMaster::find()->where(['id' => $selectedNonePrIds])->all(), 'id', function($m) {
        return $m->docnum;
    });
}
?>

<?php
$js = <<<JS
var line_count = 0;
var account_options = '{$account_options}';
var account_data = {$account_data_json};

function addLine(data = null) {
    var tr = $('<tr class="line-item">');
    
    var acc_select = $('<select name="line_account_code[]" class="form-control acc-code-select2" style="width: 100%;">').append(account_options);
    if (data && data.account_code) {
        acc_select.val(data.account_code);
    }
    
    var side_select = $('<select class="form-control mt-1 acc-side-select"><option value="dr">Dr (ซ้าย)</option><option value="cr">Cr (ขวา)</option></select>');
    if (data && data.description2 && !data.description1) {
        side_select.val('cr');
    }
    
    var td_acc = $('<td>').append(acc_select).append(side_select);
    tr.append(td_acc);
    
    tr.append('<td><input type="text" name="line_bill_code[]" class="form-control" value="' + (data ? data.bill_code : '') + '"></td>');
    tr.append('<td><input type="text" name="line_description1[]" class="form-control" value="' + (data ? data.description1 : '') + '"></td>');
    tr.append('<td><input type="text" name="line_description2[]" class="form-control" value="' + (data ? data.description2 : '') + '"></td>');
    tr.append('<td><input type="number" name="line_debit[]" class="form-control line-debit" step="0.01" value="' + (data ? data.debit : '0.00') + '"></td>');
    tr.append('<td><input type="number" name="line_credit[]" class="form-control line-credit" step="0.01" value="' + (data ? data.credit : '0.00') + '"></td>');
    tr.append('<td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-line"><i class="fa fa-trash"></i></button></td>');
    $('#voucher-lines tbody').append(tr);

    acc_select.select2({
        width: '100%',
        placeholder: '-- เลือก --',
        allowClear: true
    });
}

function calculateTotal() {
    var total_debit = 0;
    var total_credit = 0;
    var bank_deposit_amount = 0;
    
    $('#voucher-lines tbody tr.line-item').each(function() {
        var debit = parseFloat($(this).find('.line-debit').val()) || 0;
        var credit = parseFloat($(this).find('.line-credit').val()) || 0;
        var acc_code = $(this).find('select[name="line_account_code[]"]').val();
        
        total_debit += debit;
        total_credit += credit;
        
        if (acc_code && account_data[acc_code] && account_data[acc_code].indexOf('เงินฝากกระแสรายวัน') !== -1) {
            bank_deposit_amount = debit > 0 ? debit : credit;
        }
    });
    
    $('#total-debit').text(total_debit.toFixed(2));
    $('#total-credit').text(total_credit.toFixed(2));
    
    if (bank_deposit_amount > 0) {
        $('#paymentvoucher-amount').val(bank_deposit_amount);
    }
}

function loadPrPoByVendor(vendorId) {
    var prevVal = $('#vendor-select').data('prev-val');
    if (prevVal !== vendorId) {
        // เมื่อมีการเปลี่ยน Vendor ให้ล้างรายการ PO/None PR ที่เลือกไว้เดิม
        // $('#pr-select').val(null).trigger('change'); // ไม่ต้องล้าง pre-advance เพราะเลือกอิสระได้
        $('#po-select').val(null).trigger('change');
        $('#none-pr-select').val(null).trigger('change');
    }
    $('#vendor-select').data('prev-val', vendorId);
}

function pullMultipleData() {
    var pr_ids = $('#pr-select').val() || [];
    var po_ids = $('#po-select').val() || [];
    var none_pr_ids = $('#none-pr-select').val() || [];
    
    if (pr_ids.length === 0 && po_ids.length === 0 && none_pr_ids.length === 0) {
        alert('กรุณาเลือก PR, PO หรือ None PR อย่างน้อย 1 รายการ');
        return;
    }
    
    $('#pull-data-section').show();
    
    $.ajax({
        url: '{$pullMultipleUrl}',
        type: 'POST',
        data: {pr_ids: pr_ids, po_ids: po_ids, none_pr_ids: none_pr_ids},
        success: function(res) {
            if(res.success) {
                $('#paymentvoucher-amount').val(res.amount);
                $('#paymentvoucher-paid_for').val(res.paid_for);
                

                var vendorName = res.vendor_name || $('#vendor-select option:selected').text();
                $('#paymentvoucher-recipient_name').val(vendorName);
                
                // บันทึก pr_ids, po_ids และ none_pr_ids ลง hidden inputs
                $('#hidden-pr-ids').val(JSON.stringify(res.pr_ids));
                $('#hidden-po-ids').val(JSON.stringify(res.po_ids));
                $('#hidden-none-pr-ids').val(JSON.stringify(res.none_pr_ids));
                
                $('#voucher-lines tbody').empty();
                res.lines.forEach(function(line) {
                    addLine(line);
                });
                calculateTotal();

                // ตรวจจับกรณีเป็น PO สกุลเงินต่างประเทศ
                if (res.has_foreign) {
                    $('#fx-foreign-amount').val(res.foreign_total);
                    var currCode = res.foreign_currency || 'USD';
                    $('#fx-currency-code').text(currCode);
                    $('.fx-curr-label').text(currCode);
                    $('#fx-rate').val('');
                    $('#fx-thb-amount').val('');
                    $('#fx-bank-fee').val('0.00');
                    $('#fx-net-payment').val('0.00');
                    $('#foreign-currency-section').slideDown();
                } else {
                    $('#foreign-currency-section').slideUp();
                }
            }
        }
    });
}

function updateFxCalculation(triggerSource) {
    var foreignAmt = parseFloat($('#fx-foreign-amount').val()) || 0;
    var rate = parseFloat($('#fx-rate').val()) || 0;
    var thbAmt = parseFloat($('#fx-thb-amount').val()) || 0;
    var bankFee = parseFloat($('#fx-bank-fee').val()) || 0;

    if (triggerSource === 'foreign' || triggerSource === 'rate') {
        if (foreignAmt > 0 && rate > 0) {
            thbAmt = Math.round(foreignAmt * rate * 100) / 100;
            $('#fx-thb-amount').val(thbAmt.toFixed(2));
        }
    } else if (triggerSource === 'thb') {
        if (foreignAmt > 0 && thbAmt > 0) {
            rate = Math.round((thbAmt / foreignAmt) * 10000) / 10000;
            $('#fx-rate').val(rate);
        }
    }

    var netPayment = thbAmt + bankFee;
    $('#fx-net-payment').val(netPayment.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
}

// อัปเดต hidden inputs และดึงข้อมูลเมื่อเลือก PR/PO/None PR
function onRefSelectionChange() {
    var pr_ids = $('#pr-select').val() || [];
    var po_ids = $('#po-select').val() || [];
    var none_pr_ids = $('#none-pr-select').val() || [];

    $('#hidden-pr-ids').val(JSON.stringify(pr_ids));
    $('#hidden-po-ids').val(JSON.stringify(po_ids));
    $('#hidden-none-pr-ids').val(JSON.stringify(none_pr_ids));

    if (pr_ids.length > 0 || po_ids.length > 0 || none_pr_ids.length > 0) {
        pullMultipleData();
    } else {
        $('#foreign-currency-section').slideUp();
    }
}

$(document).ready(function() {
    calculateTotal();

    $('.acc-code-select2').select2({
        width: '100%',
        placeholder: '-- เลือก --',
        allowClear: true
    });
    
    // ตั้งค่า prev-val เริ่มต้น
    $('#vendor-select').data('prev-val', $('#vendor-select').val());
    
    // Event listener สำหรับปุ่มเพิ่มแถว
    $('#btn-add-line').on('click', function() {
        addLine();
    });
    
    // Event delegation สำหรับ debit/credit inputs
    $('#voucher-lines tbody').on('input', '.line-debit, .line-credit', function() {
        calculateTotal();
    });
    
    // Event delegation สำหรับเมื่อเลือกบัญชี
    $('#voucher-lines tbody').on('change', 'select[name="line_account_code[]"]', function() {
        calculateTotal();
    });
    
    // Event delegation สำหรับปุ่มลบแถว
    $('#voucher-lines tbody').on('click', '.btn-remove-line', function() {
        $(this).closest('tr').remove();
        calculateTotal();
    });

    // อัตโนมัติ: ดึงชื่อบัญชีมาแสดงในช่อง Description เมื่อเลือกผังบัญชี (ซ้าย หรือ ขวา ตามที่เลือก)
    $('#voucher-lines tbody').on('change', 'select[name="line_account_code[]"]', function() {
        var code = $(this).val();
        var name = account_data[code] || '';
        var side = $(this).closest('td').find('.acc-side-select').val();
        var tr = $(this).closest('tr');
        
        if (side === 'dr') {
            tr.find('input[name="line_description1[]"]').val(name);
            tr.find('input[name="line_description2[]"]').val('');
        } else {
            tr.find('input[name="line_description1[]"]').val('');
            tr.find('input[name="line_description2[]"]').val(name);
        }
    });

    // สลับข้อความเมื่อเปลี่ยนฝั่ง Dr/Cr
    $('#voucher-lines tbody').on('change', '.acc-side-select', function() {
        var tr = $(this).closest('tr');
        var desc1 = tr.find('input[name="line_description1[]"]').val();
        var desc2 = tr.find('input[name="line_description2[]"]').val();
        var val = $(this).val();
        
        if (val === 'cr' && desc1 !== '' && desc2 === '') {
            tr.find('input[name="line_description2[]"]').val(desc1);
            tr.find('input[name="line_description1[]"]').val('');
        } else if (val === 'dr' && desc2 !== '' && desc1 === '') {
            tr.find('input[name="line_description1[]"]').val(desc2);
            tr.find('input[name="line_description2[]"]').val('');
        }
    });
    
    // อัปเดต hidden inputs และดึงข้อมูลเมื่อ Pre-Advance/PO/None PR select เปลี่ยน
    $('#pr-select, #po-select, #none-pr-select').on('change', function() {
        onRefSelectionChange();
    });

    // การคำนวณแปลงค่าเงินและค่าธรรมเนียม
    $('#fx-foreign-amount, #fx-rate').on('input change', function() {
        updateFxCalculation('rate');
    });

    $('#fx-thb-amount').on('input change', function() {
        updateFxCalculation('thb');
    });

    $('#fx-bank-fee').on('input change', function() {
        updateFxCalculation('fee');
    });

    $('#btn-toggle-fx').on('click', function(e) {
        e.preventDefault();
        $('#foreign-currency-section').slideToggle();
    });

    $('#btn-close-fx').on('click', function() {
        $('#foreign-currency-section').slideUp();
    });

    $('#btn-apply-fx').on('click', function() {
        var foreignAmt = parseFloat($('#fx-foreign-amount').val()) || 0;
        var currCode = $('#fx-currency-code').text() || 'USD';
        var rate = parseFloat($('#fx-rate').val()) || 0;
        var thbAmt = parseFloat($('#fx-thb-amount').val()) || 0;
        var bankFee = parseFloat($('#fx-bank-fee').val()) || 0;
        var netPayment = thbAmt + bankFee;

        if (thbAmt <= 0) {
            alert('กรุณาระบุอัตราแลกเปลี่ยน หรือยอดเงินบาท (THB)');
            $('#fx-rate').focus();
            return;
        }

        // 1. ตั้ง Amount ใน Voucher
        $('#paymentvoucher-amount').val(netPayment.toFixed(2));

        // 2. ปรับ Paid For
        var poNumbers = [];
        $('#po-select option:selected').each(function() {
            var txt = $(this).text().split('(')[0].trim();
            if (txt) poNumbers.push(txt);
        });
        var poText = poNumbers.length > 0 ? ('PO: ' + poNumbers.join(', ')) : 'จ่ายชำระต่างประเทศ';
        var note = poText + ' (' + foreignAmt.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ' + currCode;
        if (rate > 0) {
            note += ' @ ' + rate;
        }
        note += ' = ' + thbAmt.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' บ.';
        if (bankFee > 0) {
            note += ' + ค่าธรรมเนียม ' + bankFee.toFixed(2) + ' บ.';
        }
        note += ')';
        $('#paymentvoucher-paid_for').val(note);

        // 3. ใส่แถวลงตารางบันทึกบัญชี
        $('#voucher-lines tbody').empty();

        // บรรทัดที่ 1: สินค้า/บริการต่างประเทศ (ยอดเงินบาทที่แปลงแล้ว)
        addLine({
            account_code: '',
            bill_code: poNumbers.join(', '),
            description1: 'สินค้า/บริการต่างประเทศ (' + foreignAmt.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ' + currCode + ')',
            description2: '',
            debit: thbAmt.toFixed(2),
            credit: 0
        });

        // บรรทัดที่ 2: ค่าธรรมเนียมธนาคาร (ถ้ามี)
        if (bankFee > 0) {
            var feeAccCode = (account_data && account_data['5360-04']) ? '5360-04' : '';
            addLine({
                account_code: feeAccCode,
                bill_code: '',
                description1: 'ค่าธรรมเนียมธนาคารในการโอนเงินต่างประเทศ',
                description2: '',
                debit: bankFee.toFixed(2),
                credit: 0
            });
        }

        // บรรทัดที่ 3: ธนาคารเงินฝากกระแสรายวัน (ยอดสุทธิที่ตัดธนาคาร)
        var bankAccCode = '';
        if (account_data) {
            if (account_data['1112-00']) bankAccCode = '1112-00';
            else if (account_data['1112-01']) bankAccCode = '1112-01';
            else if (account_data['1112-02']) bankAccCode = '1112-02';
        }
        addLine({
            account_code: bankAccCode,
            bill_code: '',
            description1: '',
            description2: 'ธนาคารเงินฝากกระแสรายวัน',
            debit: 0,
            credit: netPayment.toFixed(2)
        });

        calculateTotal();

        alert('แปลงค่าเงินและรวมค่าธรรมเนียมเข้าตารางบัญชีเรียบร้อยแล้ว (ยอดจ่ายสุทธิ ' + netPayment.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' บาท)');
    });

    // ลบไฟล์แนบ
    $('.btn-remove-doc').on('click', function() {
        var id = $(this).data('id');
        if (confirm('คุณต้องการลบไฟล์นี้ใช่หรือไม่?')) {
            $.ajax({
                url: '{$removeAttachmentUrl}',
                type: 'GET',
                data: {id: id},
                success: function(res) {
                    if (res.success) {
                        $('#doc-row-' + id).remove();
                    } else {
                        alert('ไม่สามารถลบไฟล์ได้');
                    }
                }
            });
        }
    });
});
JS;
$this->registerJs($js);
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

?>

<div class="payment-voucher-form">

    <?php $form = ActiveForm::begin([
        'id' => 'payment-voucher-form',
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

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

            <div class="row mb-3" id="pr-po-section">
                <div class="col-md-4">
                    <label class="form-label">เลือก Pre-Advance - เลือกได้หลายรายการ</label>
                    <?= Select2::widget([
                        'name' => 'pr_ids[]',
                        'data' => $selectedPrList,
                        'value' => array_keys($selectedPrList),
                        'options' => [
                            'placeholder' => 'ค้นหา/เลือก Pre-Advance...',
                            'multiple' => true,
                            'id' => 'pr-select'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 0,
                            'ajax' => [
                                'url' => Url::to(['get-pre-advance-by-vendor']),
                                'dataType' => 'json',
                                'delay' => 250,
                                'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                                'processResults' => new JsExpression('function(data) { return {results: data.results}; }'),
                                'cache' => true
                            ],
                        ],
                    ]) ?>
                    <small class="text-muted">ค้นหาได้ทันที</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>เลือกใบสั่งซื้อ (PO) - เลือกได้หลายรายการ</span>
                        <a href="javascript:void(0)" id="btn-toggle-fx" class="text-primary small" style="text-decoration:none;"><i class="fa fa-calculator"></i> แปลงค่าเงิน/รวมค่าธรรมเนียม</a>
                    </label>
                    <?= Select2::widget([
                        'name' => 'po_ids[]',
                        'data' => $selectedPoList,
                        'value' => array_keys($selectedPoList),
                        'options' => [
                            'placeholder' => 'ค้นหา/เลือก PO...',
                            'multiple' => true,
                            'id' => 'po-select'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 0,
                            'ajax' => [
                                'url' => $getPoByVendorUrl,
                                'dataType' => 'json',
                                'delay' => 250,
                                'data' => new JsExpression('function(params) { return {q:params.term, vendor_id: $("#vendor-select").val()}; }'),
                                'processResults' => new JsExpression('function(data) { return {results: data.results}; }'),
                                'cache' => true
                            ],
                        ],
                    ]) ?>
                    <small class="text-muted">ค้นหาได้ทันที หรือเลือก Vendor เพื่อกรองรายการ</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">เลือกใบสั่งซื้อ (None PR) - เลือกได้หลายรายการ</label>
                    <?= Select2::widget([
                        'name' => 'none_pr_ids[]',
                        'data' => $selectedNonePrList,
                        'value' => array_keys($selectedNonePrList),
                        'options' => [
                            'placeholder' => 'ค้นหา/เลือก None PR...',
                            'multiple' => true,
                            'id' => 'none-pr-select'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 0,
                            'ajax' => [
                                'url' => $getNonePrByVendorUrl,
                                'dataType' => 'json',
                                'delay' => 250,
                                'data' => new JsExpression('function(params) { return {q:params.term, vendor_id: $("#vendor-select").val()}; }'),
                                'processResults' => new JsExpression('function(data) { return {results: data.results}; }'),
                                'cache' => true
                            ],
                        ],
                    ]) ?>
                    <small class="text-muted">ค้นหาได้ทันที หรือเลือก Vendor เพื่อกรองรายการ</small>
                </div>
            </div>

            <!-- ส่วนคำนวณแปลงค่าเงินต่างประเทศ & รวมค่าธรรมเนียมโอน -->
            <div id="foreign-currency-section" class="card mb-3 shadow-sm" style="display:none; border: 1px solid #ffeeba; background-color: #fffdf5;">
                <div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">
                        <i class="fa fa-calculator"></i> คำนวณแปลงค่าเงินต่างประเทศ & รวมค่าธรรมเนียมโอน (THB)
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-dark" id="btn-close-fx" title="ปิดกล่องนี้">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="card-body py-3">
                    <div class="row align-items-end">
                        <div class="col-md-2">
                            <label class="form-label font-weight-bold small">ยอดเงินต่างประเทศ</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="fx-foreign-amount" placeholder="0.00">
                                <span class="input-group-text bg-light font-weight-bold small" id="fx-currency-code">USD</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label font-weight-bold small">อัตราแลกเปลี่ยน (บาท/<span class="fx-curr-label">USD</span>)</label>
                            <input type="number" step="0.0001" class="form-control" id="fx-rate" placeholder="เช่น 35.50">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-bold small">ยอดเงินบาท (THB) ก่อนค่าธรรมเนียม</label>
                            <input type="number" step="0.01" class="form-control" id="fx-thb-amount" placeholder="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label font-weight-bold small">ค่าธรรมเนียมธนาคาร (THB)</label>
                            <input type="number" step="0.01" class="form-control" id="fx-bank-fee" value="0.00" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-bold small text-success">ยอดจ่ายสุทธิที่ตัดธนาคาร (THB)</label>
                            <input type="text" readonly class="form-control font-weight-bold text-success bg-white border-success" id="fx-net-payment" value="0.00">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12 text-end">
                            <span class="text-muted small me-3">* กรอกอัตราแลกเปลี่ยน หรือยอดเงินบาท แล้วระบบจะคำนวณและตั้งแถวลงบัญชีให้อัตโนมัติ</span>
                            <button type="button" class="btn btn-success" id="btn-apply-fx">
                                <i class="fa fa-check"></i> แปลงค่าเงินและรวมค่าธรรมเนียมลงรายการบัญชี
                            </button>
                        </div>
                    </div>
                </div>
            </div>

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
                    <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'paid_for')->textInput(['maxlength' => true, 'placeholder' => 'จ่ายสำหรับ PR/PO/QT เลขที่...']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'status')->dropDownList([
                        \backend\models\PaymentVoucher::STATUS_ACTIVE => 'จ่ายแล้ว',
                        \backend\models\PaymentVoucher::STATUS_DRAFT => 'ร่าง (ยังไม่จ่าย)',
                    ])->label('สถานะ') ?>
                </div>
            </div>

            <?= Html::activeHiddenInput($model, 'ref_id') ?>
            <?= Html::activeHiddenInput($model, 'ref_type') ?>
            <input type="hidden" id="hidden-pr-ids" name="pr_ids" value='<?= json_encode($selectedPrIds) ?>'>
            <input type="hidden" id="hidden-po-ids" name="po_ids" value='<?= json_encode($selectedPoIds) ?>'>
            <input type="hidden" id="hidden-none-pr-ids" name="none_pr_ids" value='<?= json_encode($selectedNonePrIds) ?>'>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">ไฟล์แนบเอกสาร</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">แนบสลิปโอน/หัก ณ ที่จ่าย(ถ้ามี) (เลือกได้หลายไฟล์)</label>
                        <?= Html::fileInput('upload_files[]', null, ['multiple' => true, 'class' => 'form-control', 'accept' => 'image/*,application/pdf,.doc,.docx,.xls,.xlsx']) ?>
                    </div>
                </div>
            </div>
            
            <?php if (!$model->isNewRecord && !empty($model->paymentVoucherDocs)): ?>
                <div class="mt-3">
                    <h6>รายการไฟล์แนบเดิม:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>ชื่อไฟล์</th>
                                    <th style="width: 15rem;">ผู้อัปโหลด</th>
                                    <th style="width: 10rem;">วันที่อัปโหลด</th>
                                    <th class="text-center" style="width: 10rem;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($model->paymentVoucherDocs as $doc): ?>
                                    <tr id="doc-row-<?= $doc->id ?>">
                                        <td>
                                            <?= Html::a($doc->file_name, Url::to(['site/view-file', 'folder' => 'payment_voucher', 'file' => $doc->file_path]), ['target' => '_blank']) ?>
                                            <small class="text-muted">(<?= round($doc->file_size / 1024, 2) ?> KB)</small>
                                        </td>
                                        <td><?= $doc->uploaded_by ? \common\models\User::findOne($doc->uploaded_by)->username : '-' ?></td>
                                        <td><?= date('Y-m-d H:i', $doc->uploaded_at) ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-xs btn-remove-doc" data-id="<?= $doc->id ?>">
                                                <i class="fa fa-trash"></i> ลบ
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
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
                                    <td>
                                        <select name="line_account_code[]" class="form-control acc-code-select2" style="width: 100%;">
                                            <option value="">-- เลือก --</option>
                                            <?php foreach ($chart_of_accounts as $acc): ?>
                                                <?php if($acc->account_type == 1) continue; ?>
                                                <option value="<?= Html::encode($acc->account_code) ?>" <?= $acc->account_code == $line->account_code ? 'selected' : '' ?>>
                                                    <?= Html::encode($acc->account_code . ' - ' . $acc->account_name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php $side = ($desc2 !== '' && $desc1 === '') ? 'cr' : 'dr'; ?>
                                        <select class="form-control mt-1 acc-side-select">
                                            <option value="dr" <?= $side === 'dr' ? 'selected' : '' ?>>Dr (ซ้าย)</option>
                                            <option value="cr" <?= $side === 'cr' ? 'selected' : '' ?>>Cr (ขวา)</option>
                                        </select>
                                    </td>
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
#voucher-lines td { padding: 8px; vertical-align: middle; }
.btn-lg { border-radius: 30px; }
.select2-container { width: 100% !important; }
.select2-container .select2-selection--single { height: 38px; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>
