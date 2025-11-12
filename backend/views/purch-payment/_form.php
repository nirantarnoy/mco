<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use backend\models\Purch;
use backend\models\Paymentmethod;
use yii\helpers\Url;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\PurchPayment */
/* @var $paymentLines array */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="purch-payment-form">

        <?php $form = ActiveForm::begin([
            'id' => 'purch-payment-form',
            'options' => ['enctype' => 'multipart/form-data']
        ]); ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ข้อมูลการจ่ายเงิน</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'purch_id')->widget(Select2::classname(), [
                            'data' => ArrayHelper::map(Purch::find()->orderBy(['id' => SORT_DESC])->all(), 'id', function($model) {
                                return $model->purch_no . ' - ' . $model->vendor_name . ' (' . Yii::$app->formatter->asDecimal($model->net_amount, 2) . ' บาท)';
                            }),
                            'language' => 'th',
                            'options' => [
                                'placeholder' => 'เลือกใบสั่งซื้อ...',
                                'id' => 'purch_id_select',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ])->label('เลือกใบสั่งซื้อ') ?>
                    </div>

                    <div class="col-md-3">
                        <?= $form->field($model, 'trans_date')->widget(DatePicker::classname(), [
                            'options' => ['placeholder' => 'เลือกวันที่...'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ]
                        ]) ?>
                    </div>

                    <div class="col-md-3">
                        <?= $form->field($model, 'status')->textInput(['maxlength' => true, 'placeholder' => 'สถานะ']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- แสดงรายละเอียดใบสั่งซื้อ -->
        <div class="card mt-3" id="purch-detail-card" style="display: none;">
            <div class="card-header bg-info">
                <h3 class="card-title">รายละเอียดใบสั่งซื้อ</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>เลขที่ใบสั่งซื้อ:</strong>
                        <div id="purch-no">-</div>
                    </div>
                    <div class="col-md-3">
                        <strong>ชื่อผู้ขาย:</strong>
                        <div id="vendor-name">-</div>
                    </div>
                    <div class="col-md-3">
                        <strong>วันที่สั่งซื้อ:</strong>
                        <div id="purch-date">-</div>
                    </div>
                    <div class="col-md-3">
                        <strong>ยอดสุทธิ:</strong>
                        <div id="net-amount" class="text-danger font-weight-bold">-</div>
                        <input type="hidden" id="purch-net-amount" value="0">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="thead-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">รหัสสินค้า</th>
                            <th width="25%">ชื่อสินค้า</th>
                            <th width="15%">รายละเอียด</th>
                            <th width="10%" class="text-center">จำนวน</th>
                            <th width="10%" class="text-right">ราคา/หน่วย</th>
                            <th width="10%" class="text-right">ราคารวม</th>
                            <th width="10%">หมายเหตุ</th>
                        </tr>
                        </thead>
                        <tbody id="purch-lines-tbody">
                        <tr>
                            <td colspan="8" class="text-center">กรุณาเลือกใบสั่งซื้อ</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- บันทึกรายการโอนเงิน -->
        <div class="card mt-3">
            <div class="card-header bg-success">
                <h3 class="card-title">บันทึกรายการโอนเงิน</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" id="add-payment-line">
                        <i class="fas fa-plus"></i> เพิ่มรายการ
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="payment-lines-table">
                        <thead class="thead-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">ธนาคาร</th>
                            <th width="20%">ประเภทการโอน</th>
                            <th width="15%">จำนวนเงิน</th>
                            <th width="20%">อัพโหลดสลิป</th>
                            <th width="20%">หมายเหตุ</th>
                            <th width="5%"></th>
                        </tr>
                        </thead>
                        <tbody id="payment-lines-tbody">
                        <?php foreach ($paymentLines as $index => $line): ?>
                            <tr class="payment-line-row">
                                <td class="text-center line-number"><?= $index + 1 ?></td>
                                <td>
                                    <?= Html::activeTextInput($line, "[{$index}]bank_name", [
                                        'class' => 'form-control form-control-sm',
                                        'placeholder' => 'ชื่อธนาคาร'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Select2::widget([
                                        'model' => $line,
                                        'attribute' => "[{$index}]payment_method_id",
                                        'data' => ArrayHelper::map(Paymentmethod::find()->where(['status' => 1])->all(), 'id', 'name'),
                                        'options' => [
                                            'placeholder' => 'เลือกประเภท...',
                                            'class' => 'form-control-sm'
                                        ],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        ],
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::activeTextInput($line, "[{$index}]pay_amount", [
                                        'class' => 'form-control form-control-sm text-right',
                                        'placeholder' => '0.00',
                                        'type' => 'number',
                                        'step' => '0.01'
                                    ]) ?>
                                </td>
                                <td>
                                    <?php if (!$model->isNewRecord && !empty($line->doc)): ?>
                                        <div class="mb-1">
                                            <a href="<?= Yii::getAlias('@web/' . $line->doc) ?>" target="_blank" class="btn btn-info btn-xs">
                                                <i class="fas fa-file"></i> ดูไฟล์
                                            </a>
                                            <?= Html::hiddenInput("PurchPaymentLine[{$index}][doc]", $line->doc) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?= Html::fileInput("PurchPaymentLine[{$index}][doc_file]", null, [
                                        'class' => 'form-control-file form-control-sm'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::activeTextInput($line, "[{$index}]nodet", [
                                        'class' => 'form-control form-control-sm',
                                        'placeholder' => 'หมายเหตุ'
                                    ]) ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm remove-line" <?= count($paymentLines) <= 1 ? 'disabled' : '' ?>>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>ยอดรวมที่โอน:</strong></td>
                            <td class="text-right">
                                <strong><span id="total-payment">0.00</span> บาท</strong>
                                <div id="payment-warning" class="text-warning small" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i> ยอดโอนเกินยอดสุทธิของใบสั่งซื้อ
                                </div>
                            </td>
                            <td colspan="3"></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="form-group mt-3">
            <?= Html::submitButton('<i class="fas fa-save"></i> บันทึก', ['class' => 'btn btn-success', 'id' => 'submit-btn']) ?>
            <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

    <!-- Modal แจ้งเตือน -->
    <div class="modal fade" id="warningModal" tabindex="-1" role="dialog" aria-labelledby="warningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="warningModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> แจ้งเตือน
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong>ยอดเงินที่โอนมากกว่าจำนวนเงินในใบสั่งซื้อ</strong></p>
                    <div class="alert alert-info mb-0">
                        <div class="row">
                            <div class="col-6">ยอดสุทธิใบสั่งซื้อ:</div>
                            <div class="col-6 text-right"><strong id="modal-net-amount">0.00</strong> บาท</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">ยอดรวมที่โอน:</div>
                            <div class="col-6 text-right text-danger"><strong id="modal-total-payment">0.00</strong> บาท</div>
                        </div>
                        <hr class="my-2">
                        <div class="row">
                            <div class="col-6">ส่วนต่าง:</div>
                            <div class="col-6 text-right text-danger"><strong id="modal-difference">0.00</strong> บาท</div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0">คุณต้องการบันทึกข้อมูลต่อหรือไม่?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> ยกเลิก
                    </button>
                    <button type="button" class="btn btn-success" id="confirm-submit-btn">
                        <i class="fas fa-check"></i> บันทึกต่อ
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php
$getPurchLinesUrl = Url::to(['get-purch-lines']);
$paymentMethodData = json_encode(ArrayHelper::map(Paymentmethod::find()->where(['status' => 'active'])->all(), 'id', 'name'));

$this->registerJs("
var lineIndex = " . count($paymentLines) . ";
var paymentMethodOptions = $paymentMethodData;

// เมื่อเลือกใบสั่งซื้อ
$('#purch_id_select').on('change', function() {
    var purchId = $(this).val();
    
    if (!purchId) {
        $('#purch-detail-card').hide();
        return;
    }
    
    $.ajax({
        url: '$getPurchLinesUrl',
        type: 'POST',
        data: {
            purch_id: purchId,
            _csrf: yii.getCsrfToken()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#purch-no').text(response.purch.purch_no);
                $('#vendor-name').text(response.purch.vendor_name);
                $('#purch-date').text(response.purch.purch_date);
                $('#net-amount').text(parseFloat(response.purch.net_amount).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
                
                // เก็บค่ายอดสุทธิไว้ใน hidden input
                $('#purch-net-amount').val(response.purch.net_amount);
                
                var tbody = $('#purch-lines-tbody');
                tbody.empty();
                
                if (response.lines.length > 0) {
                    $.each(response.lines, function(index, line) {
                        var row = '<tr>' +
                            '<td class=\"text-center\">' + (index + 1) + '</td>' +
                            '<td>' + (line.product_id || '-') + '</td>' +
                            '<td>' + (line.product_name || '-') + '</td>' +
                            '<td>' + (line.product_description || '-') + '</td>' +
                            '<td class=\"text-center\">' + (line.qty || 0) + '</td>' +
                            '<td class=\"text-right\">' + parseFloat(line.line_price || 0).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
                            '<td class=\"text-right\">' + parseFloat(line.line_total || 0).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
                            '<td>' + (line.note || '-') + '</td>' +
                            '</tr>';
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan=\"8\" class=\"text-center\">ไม่พบรายการสินค้า</td></tr>');
                }
                
                $('#purch-detail-card').show();
                
                // เช็คยอดโอนเมื่อเลือกใบสั่งซื้อใหม่
                updateTotal();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
        }
    });
});

// เพิ่มรายการโอนเงิน
$('#add-payment-line').on('click', function() {
    var newRow = '<tr class=\"payment-line-row\">' +
        '<td class=\"text-center line-number\"></td>' +
        '<td>' +
            '<input type=\"text\" name=\"PurchPaymentLine[' + lineIndex + '][bank_name]\" class=\"form-control form-control-sm\" placeholder=\"ชื่อธนาคาร\">' +
        '</td>' +
        '<td>' +
            '<select name=\"PurchPaymentLine[' + lineIndex + '][payment_method_id]\" class=\"form-control form-control-sm\">' +
                '<option value=\"\">เลือกประเภท...</option>';
    
    $.each(paymentMethodOptions, function(id, name) {
        newRow += '<option value=\"' + id + '\">' + name + '</option>';
    });
    
    newRow += '</select>' +
        '</td>' +
        '<td>' +
            '<input type=\"number\" step=\"0.01\" name=\"PurchPaymentLine[' + lineIndex + '][pay_amount]\" class=\"form-control form-control-sm text-right pay-amount\" placeholder=\"0.00\">' +
        '</td>' +
        '<td>' +
            '<input type=\"file\" name=\"PurchPaymentLine[' + lineIndex + '][doc_file]\" class=\"form-control-file form-control-sm\">' +
        '</td>' +
        '<td>' +
            '<input type=\"text\" name=\"PurchPaymentLine[' + lineIndex + '][nodet]\" class=\"form-control form-control-sm\" placeholder=\"หมายเหตุ\">' +
        '</td>' +
        '<td class=\"text-center\">' +
            '<button type=\"button\" class=\"btn btn-danger btn-sm remove-line\"><i class=\"fas fa-trash\"></i></button>' +
        '</td>' +
        '</tr>';
    
    $('#payment-lines-tbody').append(newRow);
    lineIndex++;
    updateLineNumbers();
    updateTotal();
});

// ลบรายการ
$(document).on('click', '.remove-line', function() {
    if ($('.payment-line-row').length > 1) {
        $(this).closest('tr').remove();
        updateLineNumbers();
        updateTotal();
    }
});

// อัพเดตเลขที่แถว
function updateLineNumbers() {
    $('.payment-line-row').each(function(index) {
        $(this).find('.line-number').text(index + 1);
    });
    
    // Enable/Disable remove buttons
    if ($('.payment-line-row').length <= 1) {
        $('.remove-line').prop('disabled', true);
    } else {
        $('.remove-line').prop('disabled', false);
    }
}

// คำนวณยอดรวม
function updateTotal() {
    var total = 0;
    $('input[name*=\"[pay_amount]\"]').each(function() {
        var value = parseFloat($(this).val()) || 0;
        total += value;
    });
    $('#total-payment').text(total.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    
    // ตรวจสอบว่ายอดโอนเกินยอดสุทธิหรือไม่
    var netAmount = parseFloat($('#purch-net-amount').val()) || 0;
    if (netAmount > 0 && total > netAmount) {
        $('#payment-warning').show();
    } else {
        $('#payment-warning').hide();
    }
}

// อัพเดตยอดรวมเมื่อมีการเปลี่ยนแปลง
$(document).on('input', 'input[name*=\"[pay_amount]\"]', function() {
    updateTotal();
});

// เรียกใช้ครั้งแรก
updateTotal();

// Trigger change event ถ้ามีค่าเริ่มต้น (สำหรับ Update)
if ($('#purch_id_select').val()) {
    $('#purch_id_select').trigger('change');
}

// ตรวจสอบก่อนกดปุ่มบันทึก
var formSubmitted = false;
$('#submit-btn').on('click', function(e) {
    if (formSubmitted) {
        return true; // ให้ submit ได้
    }
    
    e.preventDefault();
    
    var netAmount = parseFloat($('#purch-net-amount').val()) || 0;
    var total = 0;
    $('input[name*=\"[pay_amount]\"]').each(function() {
        var value = parseFloat($(this).val()) || 0;
        total += value;
    });
    
    // ตรวจสอบว่ายอดโอนเกินยอดสุทธิหรือไม่
    if (netAmount > 0 && total > netAmount) {
        // แสดงข้อมูลใน Modal
        var difference = total - netAmount;
        $('#modal-net-amount').text(netAmount.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#modal-total-payment').text(total.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#modal-difference').text(difference.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        
        // แสดง Modal
        $('#warningModal').modal('show');
    } else {
        // ถ้ายอดไม่เกิน ให้ submit ได้เลย
        formSubmitted = true;
        $('#purch-payment-form').submit();
    }
});

// เมื่อกดยืนยันใน Modal
$('#confirm-submit-btn').on('click', function() {
    formSubmitted = true;
    $('#warningModal').modal('hide');
    $('#purch-payment-form').submit();
});
");
?>