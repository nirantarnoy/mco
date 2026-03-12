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
                    $total_received = 0;
                    $total_extras = 0;
                    foreach ($history as $item) {
                        $total_received += $item->amount;
                        $extras_sum = \backend\models\InvoicePaymentExtra::find()->where(['payment_receipt_id' => $item->id])->sum('amount') ?: 0;
                        $total_extras += $extras_sum;
                    }
                    $total_paid = $total_received + $total_extras;
                    $remaining = $invoice->total_amount - $total_paid;
                    ?>
                    <div class="text-right p-3" style="background: #f8f9fa; border-radius: 8px;">
                        <span class="text-muted">ยอดรับรวม (ไม่รวมรายการเพิ่มเติม):</span> <span class="text-success h5 font-weight-bold"><?= number_format($total_received, 2) ?></span><br>
                        <?php if ($total_extras > 0): ?>
                            <span class="text-muted">ยอดปรับปรุงเพิ่ม:</span> <span class="text-info h5 font-weight-bold"><?= number_format($total_extras, 2) ?></span><br>
                        <?php endif; ?>
                        <div class="mt-2 pt-2 border-top">
                            <span class="text-muted">ยอดคงค้างสุทธิ:</span> <span class="<?= $remaining > 0 ? 'text-danger' : 'text-success' ?> h4 font-weight-bold"><?= number_format($remaining, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> ส่วนที่ 1: บันทึกรับเงิน (Payment Details)</h3>
                        </div>
                        <div class="card-body">
                            <?php $form = ActiveForm::begin([
                                'id' => 'payment-form',
                                'options' => ['enctype' => 'multipart/form-data']
                            ]); ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <?= $form->field($model, 'payment_date')->widget(DatePicker::classname(), [
                                        'options' => ['placeholder' => 'เลือกวันที่', 'id' => 'payment-date-1', 'value' => date('Y-m-d')],
                                        'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayHighlight' => true]
                                    ]) ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'id' => 'payment-amount-field', 'value' => $remaining > 0 ? $remaining : 0]) ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, 'payment_method')->dropDownList([
                                        'เงินสด' => 'เงินสด',
                                        'โอนเงินธนาคาร' => 'โอนเงินธนาคาร',
                                        'เช็ค' => 'เช็ค',
                                    ], ['prompt' => '-- เลือกช่องทาง --', 'id' => 'payment-method-select']) ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, 'file')->fileInput(['class' => 'form-control-file mt-1']) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 bank-account-section" style="display: none;">
                                    <?= $form->field($model, 'bank_account')->widget(Select2::classname(), [
                                        'data' => ArrayHelper::map(BankAccount::find()->where(['status' => 1])->all(), 'account_no', function($model) {
                                            return $model->bank_name . ' (' . $model->account_no . ') ' . $model->account_name;
                                        }),
                                        'options' => ['placeholder' => '-- เลือกบัญชีธนาคาร --', 'id' => 'bank-account-1'],
                                        'pluginOptions' => ['allowClear' => true],
                                    ]) ?>
                                </div>
                                <div class="col-md-6 cheque-number-section" style="display: none;">
                                    <?= $form->field($model, 'cheque_number')->textInput(['placeholder' => 'ระบุเลขที่เช็ค', 'id' => 'cheque-no-1']) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <?= $form->field($model, 'note')->textInput(['placeholder' => 'ระบุหมายเหตุ (ถ้ามี)', 'id' => 'note-1']) ?>
                                </div>
                            </div>
                            <div class="text-right">
                                <?= Html::submitButton('<i class="fas fa-save"></i> บันทึกข้อมูลการรับเงิน', ['class' => 'btn btn-primary']) ?>
                            </div>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>

                    <div class="card card-teal card-outline">
                        <div class="card-header">
                            <h3 class="card-title text-teal"><i class="fas fa-plus-circle"></i> ส่วนที่ 2: รายการเพิ่มเติม (ปรับปรุงยอด / Adjustment)</h3>
                        </div>
                        <div class="card-body">
                            <?php $form2 = ActiveForm::begin([
                                'id' => 'adjustment-form',
                                'options' => ['enctype' => 'multipart/form-data']
                            ]); ?>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <?= $form2->field($model, 'payment_date')->widget(DatePicker::classname(), [
                                        'options' => ['placeholder' => 'เลือกวันที่', 'id' => 'payment-date-2', 'value' => date('Y-m-d')],
                                        'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayHighlight' => true]
                                    ])->label('วันที่บันทึกรายการปรับปรุง') ?>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="table-extras">
                                    <thead class="bg-teal text-white">
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
                                            <td class="text-right font-weight-bold">รวมยอดปรับปรุงเพิ่มเติม:</td>
                                            <td class="text-right"><span id="net-total-display" class="h5 font-weight-bold text-teal">0.00</span></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="text-left mb-3">
                                    <button type="button" class="btn btn-teal btn-xs" id="add-extra-row"><i class="fas fa-plus"></i> เพิ่มรายการปรับปรุง</button>
                                </div>
                            </div>

                            <?= $form2->field($model, 'note')->textarea(['rows' => 2, 'placeholder' => 'ระบุหมายเหตุรายการปรับปรุง (ถ้ามี)', 'id' => 'note-2']) ?>
                            
                            <!-- Hidden default amount for adjustment form -->
                            <?= Html::hiddenInput('InvoicePaymentReceipt[amount]', 0) ?>

                            <div class="text-right mt-3">
                                <?= Html::submitButton('<i class="fas fa-save"></i> บันทึกรายการปรับปรุงยอด', ['class' => 'btn btn-teal']) ?>
                            </div>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
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
                                        <?= Html::a('<i class="fas fa-paperclip"></i> ดูไฟล์', Yii::$app->request->baseUrl . '/' . $item->attachment, ['target' => '_blank', 'class' => 'btn btn-outline-info btn-xs']) ?>
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
                        <td colspan="4" class="text-right">ยอดรับรวมทั้งสิ้น (รวมรายการปรับปรุง):</td>
                        <td class="text-right text-success"><?= number_format($total_paid, 2) ?></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr class="<?= $remaining > 0 ? 'text-danger' : 'text-success' ?>" style="font-size: 1.1em; font-weight: bold;">
                        <td colspan="4" class="text-right">คงเหลือยอดค้างชำระสุทธิ:</td>
                        <td class="text-right"><?= number_format($remaining, 2) ?></td>
                        <td colspan="2"></td>
                    </tr>
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
$optionsHtml = '';
foreach ($extraOptions as $id => $name) {
    $optionsHtml .= '<option value="' . $id . '">' . Html::encode($name) . '</option>';
}

$js = <<<JS
    function checkPaymentMethod() {
        let method = $('#payment-method-select').val();
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

    $(document).on('change', '#payment-method-select', function(){
        checkPaymentMethod();
    });

    // Check on load
    checkPaymentMethod();

    // Extra Rows Logic
    $('#add-extra-row').on('click', function() {
        let optionsHtml = '{$optionsHtml}';

        let row = `
            <tr>
                <td>
                    <select class="form-control form-control-sm extra-option-select" name="extras_option_id[]" required>
                        <option value="">-- เลือกหัวข้อ --</option>
                        \${optionsHtml}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm text-right extra-amount" name="extras_amount[]" required>
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
        calculateNetTotal();
    });

    $(document).on('input', '.extra-amount', function() {
        calculateNetTotal();
    });

    function calculateNetTotal() {
        let extraTotal = 0;
        $('.extra-amount').each(function() {
            let val = parseFloat($(this).val()) || 0;
            extraTotal += val;
        });
        $('#net-total-display').text(extraTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    // Initial calc
    calculateNetTotal();
JS;
$this->registerJs($js);
?>
