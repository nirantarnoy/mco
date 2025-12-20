<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use kartik\file\FileInput;
use backend\models\BillingInvoice;
use backend\models\PaymentReceipt;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model backend\models\PaymentReceipt */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("
// Function to load invoice details
function loadInvoiceDetails(invoiceId) {
    if (invoiceId) {
        $.ajax({
            url: '" . \yii\helpers\Url::to(['get-invoice-info']) . "',
            data: {id: invoiceId},
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    $('#invoice-info').html(
                        '<div class=\"alert alert-info\">' +
                        '<h6><i class=\"fas fa-file-invoice\"></i> ข้อมูลใบแจ้งหนี้</h6>' +
                        '<div class=\"row\">' +
                        '<div class=\"col-md-6\">' +
                        '<p><strong>เลขที่:</strong> ' + data.invoice_number + '</p>' +
                        '<p><strong>ลูกค้า:</strong> ' + data.customer_name + '</p>' +
                        '</div>' +
                        '<div class=\"col-md-6\">' +
                        '<p><strong>ยอดรวม:</strong> ' + Number(data.total_amount).toLocaleString() + ' บาท</p>' +
                        '<p><strong>ชำระแล้ว:</strong> ' + Number(data.paid_amount).toLocaleString() + ' บาท</p>' +
                        '<p><strong>คงเหลือ:</strong> <span class=\"text-danger font-weight-bold\">' + Number(data.remaining_balance).toLocaleString() + ' บาท</span></p>' +
                        '</div>' +
                        '</div>' +
                        '</div>'
                    );
                    
                    // Auto fill amounts
                    $('#paymentreceipt-received_amount').val(data.remaining_balance);
                    $('#paymentreceipt-vat_amount').val(data.vat_amount);
                    $('#paymentreceipt-job_id').val(data.job_id);
                    
                    calculateNetAmount();
                } else {
                    $('#invoice-info').html('<div class=\"alert alert-danger\">' + response.message + '</div>');
                }
            }
        });
    } else {
        $('#invoice-info').html('');
    }
}

// Calculate net amount
function calculateNetAmount() {
    var receivedAmount = parseFloat($('#paymentreceipt-received_amount').val()) || 0;
    var discountAmount = parseFloat($('#paymentreceipt-discount_amount').val()) || 0;
    var withholdingTax = parseFloat($('#paymentreceipt-withholding_tax').val()) || 0;
    
    var netAmount = receivedAmount - discountAmount - withholdingTax;
    $('#paymentreceipt-net_amount').val(netAmount.toFixed(2));
}

// Show/hide additional fields based on payment method
function togglePaymentFields() {
    var method = $('#paymentreceipt-payment_method').val();
    
    $('.bank-fields, .cheque-fields').hide();
    
    if (method === 'bank_transfer') {
        $('.bank-fields').show();
    } else if (method === 'cheque') {
        $('.cheque-fields').show();
    }
}

// Event listeners
$('#paymentreceipt-billing_invoice_id').on('change', function() {
    loadInvoiceDetails($(this).val());
});

$('#paymentreceipt-received_amount, #paymentreceipt-discount_amount, #paymentreceipt-withholding_tax').on('input', function() {
    calculateNetAmount();
});

$('#paymentreceipt-payment_method').on('change', function() {
    togglePaymentFields();
});

// Initialize
togglePaymentFields();
");

?>

<div class="payment-receipt-form">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-receipt"></i>
                <?= $model->isNewRecord ? 'บันทึกการรับเงิน' : 'แก้ไขการรับเงิน: ' . Html::encode($model->receipt_number) ?>
                            <div class="col-md-6">
                                <?= $form->field($model, 'bank_name')->textInput([
                                    'placeholder' => 'ชื่อธนาคาร'
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'account_number')->textInput([
                                    'placeholder' => 'เลขที่บัญชี'
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Cheque Fields -->
                    <div class="cheque-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'cheque_number')->textInput([
                                    'placeholder' => 'เลขที่เช็ค'
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'cheque_date')->widget(DatePicker::class, [
                                    'options' => ['placeholder' => 'วันที่เช็ค...'],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                    ]
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Job ID -->
                    <?= $form->field($model, 'job_id')->hiddenInput()->label(false) ?>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <div class="card bg-light">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-calculator"></i> ยอดเงิน</h6>
                        </div>
                        <div class="card-body">
                            <?= $form->field($model, 'received_amount')->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'placeholder' => '0.00',
                                'id' => 'paymentreceipt-received_amount'
                            ]) ?>

                            <?= $form->field($model, 'discount_amount')->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'placeholder' => '0.00',
                                'value' => $model->discount_amount ?: '0.00',
                                'id' => 'paymentreceipt-discount_amount'
                            ]) ?>

                            <?= $form->field($model, 'vat_amount')->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'placeholder' => '0.00',
                                'readonly' => true,
                                'id' => 'paymentreceipt-vat_amount'
                            ]) ?>

                            <?= $form->field($model, 'withholding_tax')->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'placeholder' => '0.00',
                                'value' => $model->withholding_tax ?: '0.00',
                                'id' => 'paymentreceipt-withholding_tax'
                            ]) ?>

                            <hr>

                            <?= $form->field($model, 'net_amount')->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'readonly' => true,
                                'id' => 'paymentreceipt-net_amount',
                                'class' => 'form-control font-weight-bold text-success'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Upload and Notes -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <?= $form->field($model, 'attachment_file')->widget(FileInput::class, [
                        'options' => [
                            'accept' => '.pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx'
                        ],
                        'pluginOptions' => [
                            'showPreview' => true,
                            'showUpload' => false,
                            'showRemove' => true,
                            'initialPreviewAsData' => true,
                            'browseClass' => 'btn btn-primary',
                            'browseIcon' => '<i class="fas fa-folder-open"></i> ',
                            'browseLabel' => 'เลือกไฟล์',
                            'removeLabel' => 'ลบ',
                            'msgPlaceholder' => 'เลือกไฟล์หลักฐานการชำระเงิน...',
                            'allowedFileExtensions' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'],
                            'maxFileSize' => 10240, // 10MB
                        ]
                    ]) ?>

                    <?php if (!$model->isNewRecord && $model->attachment_path): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-paperclip"></i>
                                ไฟล์ปัจจุบัน:
                                <?= Html::a(
                                    Html::encode($model->attachment_name),
                                    ['download', 'id' => $model->id],
                                    ['class' => 'text-primary', 'target' => '_blank']
                                ) ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <?= $form->field($model, 'notes')->textarea([
                        'rows' => 4,
                        'placeholder' => 'หมายเหตุเพิ่มเติม...'
                    ]) ?>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-group mt-4">
                <hr>
                <div class="d-flex justify-content-between">
                    <div>
                        <?= Html::a(
                            '<i class="fas fa-arrow-left"></i> กลับ',
                            ['index'],
                            ['class' => 'btn btn-secondary']
                        ) ?>
                    </div>
                    <div>
                        <?= Html::submitButton(
                            $model->isNewRecord
                                ? '<i class="fas fa-save"></i> บันทึก'
                                : '<i class="fas fa-save"></i> อัพเดต',
                            [
                                'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
                                'id' => 'submit-btn'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .form-label {
        font-weight: 600;
        color: #495057;
    }

    .bg-light .card-body {
        background-color: #f8f9fa !important;
    }

    .text-success {
        font-size: 1.1em;
    }

    .alert-info {
        border-left: 4px solid #17a2b8;
    }

    .bank-fields, .cheque-fields {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
    }

    #submit-btn {
        min-width: 120px;
    }
</style>