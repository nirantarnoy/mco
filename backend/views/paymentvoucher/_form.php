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

$pullPrUrl = Url::to(['pull-pr']);
$pullPoUrl = Url::to(['pull-po']);

$js = <<<JS
var line_count = 0;

function addLine(data = null) {
    var tr = $('<tr class="line-item">');
    tr.append('<td><input type="text" name="line_account_code[]" class="form-control" value="' + (data ? data.account_code : '') + '"></td>');
    tr.append('<td><input type="text" name="line_bill_code[]" class="form-control" value="' + (data ? data.bill_code : '') + '"></td>');
    tr.append('<td><input type="text" name="line_description[]" class="form-control" value="' + (data ? data.description : '') + '" required></td>');
    tr.append('<td><input type="number" name="line_debit[]" class="form-control line-debit" step="0.01" value="' + (data ? data.debit : '0.00') + '" onchange="calculateTotal()"></td>');
    tr.append('<td><input type="number" name="line_credit[]" class="form-control line-credit" step="0.01" value="' + (data ? data.credit : '0.00') + '" onchange="calculateTotal()"></td>');
    tr.append('<td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest(\'tr\').remove(); calculateTotal()"><i class="fa fa-trash"></i></button></td>');
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

function pullPr(id) {
    if(!id) return;
    $.ajax({
        url: '{$pullPrUrl}',
        data: {id: id},
        success: function(res) {
            if(res.success) {
                $('#paymentvoucher-recipient_name').val(res.recipient_name);
                $('#paymentvoucher-amount').val(res.amount);
                $('#paymentvoucher-paid_for').val(res.paid_for);
                $('#paymentvoucher-ref_id').val(id);
                $('#paymentvoucher-ref_type').val(1); // PR
                
                $('#voucher-lines tbody').empty();
                res.lines.forEach(function(line) {
                    addLine(line);
                });
                calculateTotal();
            }
        }
    });
}

function pullPo(id) {
    if(!id) return;
    $.ajax({
        url: '{$pullPoUrl}',
        data: {id: id},
        success: function(res) {
            if(res.success) {
                $('#paymentvoucher-recipient_name').val(res.recipient_name);
                $('#paymentvoucher-amount').val(res.amount);
                $('#paymentvoucher-paid_for').val(res.paid_for);
                $('#paymentvoucher-ref_id').val(id);
                $('#paymentvoucher-ref_type').val(2); // PO
                
                $('#voucher-lines tbody').empty();
                res.lines.forEach(function(line) {
                    addLine(line);
                });
                calculateTotal();
            }
        }
    });
}

$(document).ready(function() {
    calculateTotal();
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
                    <label class="form-label">ดึงข้อมูลจากใบขอซื้อ (PR)</label>
                    <?= Select2::widget([
                        'name' => 'pull_pr',
                        'data' => ArrayHelper::map(\backend\models\PurchReq::find()->where(['approve_status' => 1])->orderBy(['id' => SORT_DESC])->limit(100)->all(), 'id', 'purch_req_no'),
                        'options' => ['placeholder' => 'ค้นหา PR...'],
                        'pluginOptions' => ['allowClear' => true],
                        'pluginEvents' => [
                            "change" => "function() { pullPr($(this).val()); }",
                        ]
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ดึงข้อมูลจากใบสั่งซื้อ (PO)</label>
                    <?= Select2::widget([
                        'name' => 'pull_po',
                        'data' => ArrayHelper::map(\backend\models\Purch::find()->where(['approve_status' => 1])->orderBy(['id' => SORT_DESC])->limit(100)->all(), 'id', 'purch_no'),
                        'options' => ['placeholder' => 'ค้นหา PO...'],
                        'pluginOptions' => ['allowClear' => true],
                        'pluginEvents' => [
                            "change" => "function() { pullPo($(this).val()); }",
                        ]
                    ]) ?>
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
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">รายละเอียดการลงบัญชี</h5>
            <button type="button" class="btn btn-primary btn-sm" onclick="addLine()">
                <i class="fa fa-plus"></i> เพิ่มรายการ
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="voucher-lines">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%">Code Acc.</th>
                            <th style="width: 15%">Code Bill</th>
                            <th style="width: 40%">Description</th>
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
                                <tr class="line-item">
                                    <td><input type="text" name="line_account_code[]" class="form-control" value="<?= Html::encode($line->account_code) ?>"></td>
                                    <td><input type="text" name="line_bill_code[]" class="form-control" value="<?= Html::encode($line->bill_code) ?>"></td>
                                    <td><input type="text" name="line_description[]" class="form-control" value="<?= Html::encode($line->description) ?>" required></td>
                                    <td><input type="number" name="line_debit[]" class="form-control line-debit" step="0.01" value="<?= $line->debit ?>" onchange="calculateTotal()"></td>
                                    <td><input type="number" name="line_credit[]" class="form-control line-credit" step="0.01" value="<?= $line->credit ?>" onchange="calculateTotal()"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove(); calculateTotal()">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">ยอดรวม</th>
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
