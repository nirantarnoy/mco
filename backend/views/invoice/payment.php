<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use backend\models\BankAccount;
use backend\models\PaymentExtraOption;

$extraOptions = ArrayHelper::map(PaymentExtraOption::find()->where(['status' => 1])->all(), 'id', 'name');

$this->title = 'บันทึกรับเงิน: ' . $invoice->invoice_number;
?>
<div class="invoice-payment-form">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-dollar-sign"></i> <?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><th width="120">ลูกค้า:</th><td><?= Html::encode($invoice->customer_name) ?></td></tr>
                        <tr><th>เลขที่เอกสาร:</th><td><span class="badge badge-warning"><?= $invoice->invoice_number ?></span></td></tr>
                        <tr><th>ยอดเงินใบเสร็จ:</th><td><strong><?= number_format($invoice->total_amount, 2) ?></strong> บาท</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <?php
                    $total_paid = array_sum(array_column($history, 'amount'));
                    $remaining = $invoice->total_amount - $total_paid;
                    ?>
                    <div class="text-right p-3" style="background: #f8f9fa; border-radius: 8px;">
                        <span class="text-muted">ยอดรับแล้ว:</span> <span class="text-success h4 font-weight-bold"><?= number_format($total_paid, 2) ?></span><br>
                        <span class="text-muted">ยอดคงค้าง:</span> <span class="text-danger h4 font-weight-bold"><?= number_format($remaining, 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="card bg-light">
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'payment_date')->widget(DatePicker::classname(), [
                                'options' => ['placeholder' => 'เลือกวันที่', 'value' => date('Y-m-d')],
                                'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayHighlight' => true]
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'value' => $remaining > 0 ? $remaining : 0]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'file')->fileInput(['class' => 'form-control-file mt-2']) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'payment_method')->dropDownList([
                                'เงินสด' => 'เงินสด',
                                'โอนเงินธนาคาร' => 'โอนเงินธนาคาร',
                                'เช็ค' => 'เช็ค',
                            ], ['prompt' => '-- เลือกช่องทาง --']) ?>
                        </div>
                        <div class="col-md-4 bank-account-section" style="display: none;">
                            <?= $form->field($model, 'bank_account')->widget(Select2::classname(), [
                                'data' => ArrayHelper::map(BankAccount::find()->where(['status' => 1])->all(), 'account_no', function($model) {
                                    return $model->bank_name . ' (' . $model->account_no . ') ' . $model->account_name;
                                }),
                                'options' => ['placeholder' => '-- เลือกบัญชีธนาคาร --'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]) ?>
                        </div>
                        <div class="col-md-4 cheque-number-section" style="display: none;">
                            <?= $form->field($model, 'cheque_number')->textInput(['placeholder' => 'ระบุเลขที่เช็ค']) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'note')->textarea(['rows' => 2, 'placeholder' => 'ระบุหมายเหตุ (ถ้ามี)']) ?>
                        </div>
                    </div>

                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title text-info"><i class="fas fa-plus-circle"></i> รายการเพิ่มเติม (ปรับปรุงยอด)</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-info btn-xs" id="add-extra-row"><i class="fas fa-plus"></i> เพิ่มรายการ</button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered" id="table-extras">
                                <thead class="bg-info text-white">
                                    <tr>
                                        <th width="60%">หัวข้อ</th>
                                        <th width="30%">จำนวนเงิน (บาท)</th>
                                        <th width="10%"></th>
                                    </tr>
                                </thead>
                                <tbody id="extra-rows">
                                    <!-- Dynamic Rows -->
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td class="text-right font-weight-bold">รวมยอดรับสุทธิ (รวมรายการเพิ่มเติม):</td>
                                        <td class="text-right"><span id="net-total-display" class="h5 font-weight-bold text-primary">0.00</span></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="text-right">
                        <?= Html::submitButton('<i class="fas fa-save"></i> บันทึกรายการ', ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <h5 class="mt-4 mb-3"><i class="fas fa-history"></i> ประวัติการรับเงิน</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark text-center">
                    <tr>
                        <th width="60">#</th>
                        <th>วันที่รับเงิน</th>
                        <th>ช่องทางการชำระ</th>
                        <th>หมายเหตุ</th>
                        <th class="text-right">ยอดเงิน (บาท)</th>
                        <th width="150">ไฟล์แนบ</th>
                        <th width="100">จัดการ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($history): ?>
                        <?php foreach ($history as $index => $item): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td class="text-center"><?= Yii::$app->formatter->asDate($item->payment_date) ?></td>
                                <td class="text-center">
                                    <?= Html::encode($item->payment_method) ?>
                                    <?php if ($item->payment_method == 'โอนเงินธนาคาร' && $item->bank_account): ?>
                                        <div class="small text-muted">บ/ช: <?= Html::encode($item->bank_account) ?></div>
                                    <?php elseif ($item->payment_method == 'เช็ค' && $item->cheque_number): ?>
                                        <div class="small text-muted">เช็ค: <?= Html::encode($item->cheque_number) ?></div>
                                    <?php endif; ?>
                                </td>
                                    <td><?= Html::encode($item->note) ?></td>
                                    <td class="text-right font-weight-bold text-primary">
                                        <?= number_format($item->amount, 2) ?>
                                        <?php 
                                        $extras = \backend\models\InvoicePaymentExtra::find()->where(['payment_receipt_id' => $item->id])->all();
                                        if ($extras): 
                                        ?>
                                            <div class="small mt-1 pt-1 border-top">
                                                <?php foreach ($extras as $ex): ?>
                                                    <div class="text-muted"><?= Html::encode($ex->extraOption->name) ?>: <?= number_format($ex->amount, 2) ?></div>
                                                <?php endforeach; ?>
                                                <div class="text-success font-weight-bold">รวมสุทธิ: <?= number_format($item->amount + array_sum(array_column($extras, 'amount')), 2) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <td class="text-center">
                                    <?php if ($item->attachment): ?>
                                        <?= Html::a('<i class="fas fa-paperclip"></i> ดูไฟล์', ['/' . $item->attachment], ['target' => '_blank', 'class' => 'btn btn-outline-info btn-xs']) ?>
                                    <?php else: ?>
                                        <span class="text-muted small">ไม่มีไฟล์</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= Html::a('<i class="fas fa-trash-alt"></i>', ['delete-payment', 'id' => $item->id], [
                                        'class' => 'btn btn-danger btn-sm',
                                        'data' => [
                                            'confirm' => 'คุณแน่ใจว่าต้องการลบรายการรับเงินนี้?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted p-4">ยังไม่มีข้อมูลการบันทึกรับเงิน</td></tr>
                    <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-light">
                    <tr style="font-size: 1.1em; font-weight: bold;">
                        <td colspan="4" class="text-right">ยอดรับรวมทั้งสิ้น:</td>
                        <td class="text-right text-success"><?= number_format($total_paid, 2) ?></td>
                        <td colspan="2"></td>
                    </tr>
                    <?php if ($remaining > 0): ?>
                        <tr class="text-danger">
                            <td colspan="4" class="text-right">คงเหลือค้างชำระ:</td>
                            <td class="text-right"><?= number_format($remaining, 2) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    <?php endif; ?>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <?= Html::a('<i class="fas fa-chevron-left"></i> กลับหน้ารายการ', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>

<style>
    .btn-xs { padding: 1px 5px; font-size: 12px; line-height: 1.5; border-radius: 3px; }
    .table td { vertical-align: middle !important; }
</style>

<?php
$js = <<<JS
    function checkPaymentMethod() {
        let method = $('#invoicepaymentreceipt-payment_method').val();
        if(method == 'โอนเงินธนาคาร'){
            $('.bank-account-section').show();
            $('.cheque-number-section').hide();
        } else if(method == 'เช็ค'){
            $('.bank-account-section').hide();
            $('.cheque-number-section').show();
        } else {
            $('.bank-account-section').hide();
            $('.cheque-number-section').hide();
        }
    }

    $(document).on('change', '#invoicepaymentreceipt-payment_method', function(){
        checkPaymentMethod();
    });

    // Check on load
    checkPaymentMethod();

    // Extra Rows Logic
    $('#add-extra-row').on('click', function() {
        let options = '';
        <?php foreach ($extraOptions as $id => $name): ?>
            options += '<option value="<?= $id ?>"><?= Html::encode($name) ?></option>';
        <?php endforeach; ?>

        let row = `
            <tr>
                <td>
                    <select class="form-control form-control-sm extra-option-select" name="extras_option_id[]">
                        <option value="">-- เลือกหัวข้อ --</option>
                        ${options}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-right extra-amount" name="extras_amount[]" disabled>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-xs btn-remove-row"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;
        $('#extra-rows').append(row);
    });

    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        calculateNetTotal();
    });

    $(document).on('change', '.extra-option-select', function() {
        let amountInput = $(this).closest('tr').find('.extra-amount');
        if ($(this).val()) {
            amountInput.prop('disabled', false);
        } else {
            amountInput.prop('disabled', true).val('');
        }
        calculateNetTotal();
    });

    $(document).on('input', '#invoicepaymentreceipt-amount, .extra-amount', function() {
        calculateNetTotal();
    });

    function calculateNetTotal() {
        let baseAmount = parseFloat($('#invoicepaymentreceipt-amount').val()) || 0;
        let extraTotal = 0;
        $('.extra-amount').each(function() {
            let val = parseFloat($(this).val()) || 0;
            extraTotal += val;
        });
        let netTotal = baseAmount + extraTotal;
        $('#net-total-display').text(netTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    // Initial calc
    calculateNetTotal();
JS;
$this->registerJs($js);
?>
