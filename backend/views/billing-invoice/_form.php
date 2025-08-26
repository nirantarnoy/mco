<?php
// backend/views/billing-invoice/_form.php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Customer;
use kartik\select2\Select2;
use kartik\date\DatePicker;

$this->registerJs("
var selectedInvoices = [];
var invoiceData = {};

$(document).ready(function() {
    $('#customer-select').change(function() {
        var customerId = $(this).val();
        if (customerId) {
            loadUnbilledInvoices(customerId);
        } else {
            $('#unbilled-invoices').html('');
            clearSelectedInvoices();
        }
    });

    // Initialize discount and VAT calculation
    $('#billinginvoice-discount_percent, #billinginvoice-vat_percent').on('input', function() {
        calculateTotals();
    });
});

function loadUnbilledInvoices(customerId) {
    $.ajax({
        url: '" . \yii\helpers\Url::to(['get-unbilled-invoices']) . "',
        type: 'POST',
        data: {
            customer_id: customerId,
            '" . Yii::$app->request->csrfParam . "': '" . Yii::$app->request->csrfToken . "'
        },
        dataType: 'json',
        beforeSend: function() {
            $('#unbilled-invoices').html('<div class=\"text-center\"><i class=\"fa fa-spinner fa-spin\"></i> กำลังโหลดข้อมูล...</div>');
        },
        success: function(response) {
            console.log('Response:', response); // Debug
            if (response.success) {
                renderInvoicesList(response.data);
            } else {
                $('#unbilled-invoices').html('<div class=\"alert alert-warning\">' + response.message + '</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error); // Debug
            $('#unbilled-invoices').html('<div class=\"alert alert-danger\">เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + error + '</div>');
        }
    });
}

function renderInvoicesList(data) {
    var html = '';
    
    if (data && data.length > 0) {
        // Store invoice data for later use
        invoiceData = {};
        $.each(data, function(index, invoice) {
            invoiceData[invoice.id] = invoice;
        });

        html += '<div class=\"panel panel-default\">';
        html += '<div class=\"panel-heading\">';
        html += '<h4 class=\"panel-title\"><i class=\"fa fa-list\"></i> ใบแจ้งหนี้ที่ยังไม่ได้ออกใบวางบิล (' + data.length + ' รายการ)</h4>';
        html += '</div>';
        html += '<div class=\"panel-body\">';
        
        // Available invoices section
        html += '<h5><strong>รายการที่พร้อมเลือก:</strong></h5>';
        html += '<div class=\"table-responsive\">';
        html += '<table class=\"table table-bordered table-hover\" id=\"available-invoices-table\">';
        html += '<thead class=\"bg-info\">';
        html += '<tr>';
        html += '<th width=\"40\"><input type=\"checkbox\" id=\"select-all\"> </th>';
        html += '<th>เลขที่ใบแจ้งหนี้</th>';
        html += '<th>วันที่</th>';
        html += '<th>ประเภท</th>';
        html += '<th width=\"120\" class=\"text-right\">จำนวนเงิน</th>';
        html += '<th width=\"80\" class=\"text-center\">เลือก</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';
        
        $.each(data, function(index, invoice) {
            html += '<tr id=\"available-row-' + invoice.id + '\">';
            html += '<td><input type=\"checkbox\" class=\"select-invoice\" data-invoice-id=\"' + invoice.id + '\"></td>';
            html += '<td><strong>' + invoice.invoice_number + '</strong></td>';
            html += '<td>' + invoice.invoice_date + '</td>';
            html += '<td><span class=\"label ' + (invoice.invoice_type === 'tax_invoice' ? 'label-success' : 'label-primary') + '\">' + 
                   (invoice.invoice_type === 'tax_invoice' ? 'ใบกำกับภาษี' : 'ใบแจ้งหนี้') + '</span></td>';
            html += '<td class=\"text-right\"><strong>' + invoice.total_amount + '</strong></td>';
            html += '<td class=\"text-center\">';
            html += '<button type=\"button\" class=\"btn btn-xs btn-success add-invoice\" data-invoice-id=\"' + invoice.id + '\">';
            html += '<i class=\"fa fa-plus\"></i> เพิ่ม';
            html += '</button>';
            html += '</td>';
            html += '</tr>';
        });
        
        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        
        // Selected invoices section
        html += '<hr>';
        html += '<h5><strong>รายการที่เลือกแล้ว:</strong></h5>';
        html += '<div class=\"table-responsive\">';
        html += '<table class=\"table table-bordered\" id=\"selected-invoices-table\">';
        html += '<thead class=\"bg-success\">';
        html += '<tr>';
        html += '<th width=\"50\">ลำดับ</th>';
        html += '<th>เลขที่ใบแจ้งหนี้</th>';
        html += '<th>วันที่</th>';
        html += '<th>ประเภท</th>';
        html += '<th width=\"120\" class=\"text-right\">จำนวนเงิน</th>';
        html += '<th width=\"80\" class=\"text-center\">ลบ</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody id=\"selected-invoices-tbody\">';
        html += '<tr id=\"no-selected-row\">';
        html += '<td colspan=\"6\" class=\"text-center text-muted\"><em>ยังไม่ได้เลือกรายการใด</em></td>';
        html += '</tr>';
        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        
        // Summary section
        html += '<div class=\"row\" style=\"margin-top: 20px;\">';
        html += '<div class=\"col-md-8\"></div>';
        html += '<div class=\"col-md-4\">';
        html += '<div class=\"panel panel-info\">';
        html += '<div class=\"panel-heading\"><strong><i class=\"fa fa-calculator\"></i> สรุปยอดเงิน</strong></div>';
        html += '<div class=\"panel-body\">';
        html += '<table class=\"table table-condensed\" style=\"margin-bottom: 0;\">';
        html += '<tr><td><strong>ยอดรวม:</strong></td><td class=\"text-right\"><span id=\"subtotal-amount\">0.00</span> บาท</td></tr>';
        html += '<tr><td><strong>ส่วนลด:</strong></td><td class=\"text-right\"><span id=\"discount-amount\">0.00</span> บาท</td></tr>';
        html += '<tr><td><strong>หลังหักส่วนลด:</strong></td><td class=\"text-right\"><span id=\"after-discount-amount\">0.00</span> บาท</td></tr>';
        html += '<tr><td><strong>ภาษีมูลค่าเพิ่ม:</strong></td><td class=\"text-right\"><span id=\"vat-amount\">0.00</span> บาท</td></tr>';
        html += '<tr class=\"success\"><td><strong>รวมทั้งสิ้น:</strong></td><td class=\"text-right\"><strong><span id=\"total-amount\">0.00</span> บาท</strong></td></tr>';
        html += '</table>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        
        html += '</div>';
        html += '</div>';
    } else {
        html += '<div class=\"alert alert-info\"><i class=\"fa fa-info-circle\"></i> ไม่พบใบแจ้งหนี้ที่ยังไม่ได้ออกใบวางบิล</div>';
    }
    
    $('#unbilled-invoices').html(html);
    bindEvents();
}

function bindEvents() {
    // Select all checkbox
    $('#select-all').off('change').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.select-invoice').prop('checked', isChecked);
        
        if (isChecked) {
            $('.select-invoice').each(function() {
                var invoiceId = $(this).data('invoice-id');
                if (selectedInvoices.indexOf(invoiceId) === -1) {
                    addInvoiceToSelected(invoiceId);
                }
            });
        } else {
            // Clear all selections
            selectedInvoices = [];
            $('#selected-invoices-tbody').html('<tr id=\"no-selected-row\"><td colspan=\"6\" class=\"text-center text-muted\"><em>ยังไม่ได้เลือกรายการใด</em></td></tr>');
            $('.add-invoice').show();
            updateTotals();
        }
    });
    
    // Individual checkbox
    $('.select-invoice').off('change').on('change', function() {
        var invoiceId = $(this).data('invoice-id');
        var isChecked = $(this).prop('checked');
        
        if (isChecked) {
            addInvoiceToSelected(invoiceId);
        } else {
            removeInvoiceFromSelected(invoiceId);
        }
    });
    
    // Add invoice button
    $('.add-invoice').off('click').on('click', function() {
        var invoiceId = $(this).data('invoice-id');
        addInvoiceToSelected(invoiceId);
        $('#available-row-' + invoiceId + ' .select-invoice').prop('checked', true);
    });
}

function addInvoiceToSelected(invoiceId) {
    if (selectedInvoices.indexOf(invoiceId) !== -1) {
        return; // Already added
    }
    
    selectedInvoices.push(invoiceId);
    var invoice = invoiceData[invoiceId];
    
    if (!invoice) {
        console.error('Invoice data not found for ID:', invoiceId);
        return;
    }
    
    // Remove 'no selected' row if exists
    $('#no-selected-row').remove();
    
    // Add to selected table
    var rowHtml = '<tr id=\"selected-row-' + invoiceId + '\">';
    rowHtml += '<td>' + selectedInvoices.length + '</td>';
    rowHtml += '<td><strong>' + invoice.invoice_number + '</strong></td>';
    rowHtml += '<td>' + invoice.invoice_date + '</td>';
    rowHtml += '<td><span class=\"label ' + (invoice.invoice_type === 'tax_invoice' ? 'label-success' : 'label-primary') + '\">' + 
              (invoice.invoice_type === 'tax_invoice' ? 'ใบกำกับภาษี' : 'ใบแจ้งหนี้') + '</span></td>';
    rowHtml += '<td class=\"text-right\"><strong>' + invoice.total_amount + '</strong></td>';
    rowHtml += '<td class=\"text-center\">';
    rowHtml += '<button type=\"button\" class=\"btn btn-xs btn-danger remove-invoice\" data-invoice-id=\"' + invoiceId + '\">';
    rowHtml += '<i class=\"fa fa-times\"></i> ลบ';
    rowHtml += '</button>';
    rowHtml += '</td>';
    rowHtml += '</tr>';
    
    $('#selected-invoices-tbody').append(rowHtml);
    
    // Hide add button for this invoice
    $('#available-row-' + invoiceId + ' .add-invoice').hide();
    
    // Bind remove event
    $('.remove-invoice[data-invoice-id=\"' + invoiceId + '\"]').off('click').on('click', function() {
        removeInvoiceFromSelected(invoiceId);
        $('#available-row-' + invoiceId + ' .select-invoice').prop('checked', false);
    });
    
    updateTotals();
    updateHiddenInputs();
}

function removeInvoiceFromSelected(invoiceId) {
    var index = selectedInvoices.indexOf(invoiceId);
    if (index === -1) {
        return; // Not in list
    }
    
    selectedInvoices.splice(index, 1);
    $('#selected-row-' + invoiceId).remove();
    $('#available-row-' + invoiceId + ' .add-invoice').show();
    
    // Renumber rows
    $('#selected-invoices-tbody tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
    });
    
    // Show 'no selected' row if empty
    if (selectedInvoices.length === 0) {
        $('#selected-invoices-tbody').html('<tr id=\"no-selected-row\"><td colspan=\"6\" class=\"text-center text-muted\"><em>ยังไม่ได้เลือกรายการใด</em></td></tr>');
    }
    
    updateTotals();
    updateHiddenInputs();
}

function clearSelectedInvoices() {
    selectedInvoices = [];
    invoiceData = {};
    updateTotals();
}

function updateTotals() {
    var subtotal = 0;
    
    $.each(selectedInvoices, function(index, invoiceId) {
        var invoice = invoiceData[invoiceId];
        if (invoice) {
            subtotal += parseFloat(invoice.total_amount_raw);
        }
    });
    
    var discountPercent = parseFloat($('#billinginvoice-discount_percent').val()) || 0;
    var vatPercent = parseFloat($('#billinginvoice-vat_percent').val()) || 7;
    
    var discountAmount = subtotal * (discountPercent / 100);
    var afterDiscount = subtotal - discountAmount;
    var vatAmount = afterDiscount * (vatPercent / 100);
    var totalAmount = afterDiscount + vatAmount;
    
    // Update display
    $('#subtotal-amount').text(subtotal.toLocaleString('th-TH', {minimumFractionDigits: 2}));
    $('#discount-amount').text(discountAmount.toLocaleString('th-TH', {minimumFractionDigits: 2}));
    $('#after-discount-amount').text(afterDiscount.toLocaleString('th-TH', {minimumFractionDigits: 2}));
    $('#vat-amount').text(vatAmount.toLocaleString('th-TH', {minimumFractionDigits: 2}));
    $('#total-amount').text(totalAmount.toLocaleString('th-TH', {minimumFractionDigits: 2}));
}

function calculateTotals() {
    updateTotals();
}

function updateHiddenInputs() {
    // Remove existing hidden inputs
    $('input[name=\"BillingInvoice[selectedInvoices][]\"]').remove();
    
    // Add new hidden inputs
    $.each(selectedInvoices, function(index, invoiceId) {
        $('<input>').attr({
            type: 'hidden',
            name: 'BillingInvoice[selectedInvoices][]',
            value: invoiceId
        }).appendTo('#billing-invoice-form');
    });
}
");

$new_billing_no = \backend\models\BillingInvoice::generateBillingNumber();
?>

<div class="billing-invoice-form">

    <?php $form = ActiveForm::begin(['id' => 'billing-invoice-form']); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'billing_number')->textInput(['maxlength' => true, 'value'=>$model->isNewRecord?$new_billing_no:$model->billing_number]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'billing_date')->widget(DatePicker::class, [
                'options' => ['placeholder' => 'เลือกวันที่'],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'customer_id')->widget(Select2::class, [
                'data' => ArrayHelper::map(Customer::find()->where(['status' => 1])->all(), 'id', function($model) {
                    return $model->code . ' - ' . $model->name;
                }),
                'options' => [
                    'placeholder' => 'เลือกลูกค้า...',
                    'id' => 'customer-select'
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'credit_terms')->textInput(['type' => 'number']) ?>
        </div>
    </div>

    <div id="unbilled-invoices"></div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'discount_percent')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0', 'max' => '100']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'vat_percent')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0', 'max' => '100']) ?>
        </div>
    </div>

    <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>

    <div class="form-group">
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-default btn-lg']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<style>
    .panel-title {
        color: #31708f;
    }

    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }

    .label {
        font-size: 11px;
        padding: 3px 6px;
    }

    .btn-xs {
        padding: 2px 8px;
        font-size: 11px;
    }

    #selected-invoices-table tbody tr {
        background-color: #f9f9f9;
    }

    #selected-invoices-table tbody tr:hover {
        background-color: #f0f0f0;
    }

    .text-muted em {
        font-style: italic;
        color: #999;
    }

    .fa-spin {
        animation: fa-spin 1s infinite linear;
    }

    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .bg-info {
        background-color: #d9edf7 !important;
    }

    .bg-success {
        background-color: #dff0d8 !important;
    }
</style>