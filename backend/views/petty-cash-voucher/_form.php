<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\PettyCashVoucher */
/* @var $details backend\models\PettyCashDetail[] */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("
// Function to calculate row total
function calculateRowTotal(row) {
    var amount = parseFloat(row.find('.amount-input').val()) || 0;
    var vatAmount = parseFloat(row.find('.vat-amount-input').val()) || 0;
    var wht = parseFloat(row.find('.wht-input').val()) || 0;
    var other = parseFloat(row.find('.other-input').val()) || 0;
    
    var total = amount + vatAmount - wht + other;
    row.find('.total-input').val(total.toFixed(2));
    
    calculateGrandTotal();
}

// Function to calculate grand total
function calculateGrandTotal() {
    var grandTotal = 0;
    $('.total-input').each(function() {
        grandTotal += parseFloat($(this).val()) || 0;
    });
    $('#pettycashvoucher-amount').val(grandTotal.toFixed(2));
}

// Add new row
function addDetailRow() {
    var rowIndex = $('#details-table tbody tr').length;
    
    var newRowHtml = `
    <tr>
        <td>
            <input type=\"text\" name=\"PettyCashDetail[` + rowIndex + `][ac_code]\" class=\"form-control form-control-sm\" placeholder=\"รหัสบัญชี\" maxlength=\"50\">
        </td>
        <td>
            <input type=\"date\" name=\"PettyCashDetail[` + rowIndex + `][detail_date]\" class=\"form-control form-control-sm\">
        </td>
        <td>
            <textarea name=\"PettyCashDetail[` + rowIndex + `][detail]\" class=\"form-control form-control-sm\" rows=\"2\" placeholder=\"รายละเอียดการจ่าย\"></textarea>
        </td>
        <td>
            <input type=\"number\" name=\"PettyCashDetail[` + rowIndex + `][amount]\" class=\"form-control form-control-sm amount-input text-right\" step=\"0.01\" min=\"0\" placeholder=\"0.00\" value=\"0.00\">
        </td>
        <td>
            <input type=\"number\" name=\"PettyCashDetail[` + rowIndex + `][vat]\" class=\"form-control form-control-sm text-right\" step=\"0.01\" min=\"0\" placeholder=\"0.00\" value=\"0.00\">
        </td>
        <td>
            <input type=\"number\" name=\"PettyCashDetail[` + rowIndex + `][vat_amount]\" class=\"form-control form-control-sm vat-amount-input text-right\" step=\"0.01\" min=\"0\" placeholder=\"0.00\" value=\"0.00\">
        </td>
        <td>
            <input type=\"number\" name=\"PettyCashDetail[` + rowIndex + `][wht]\" class=\"form-control form-control-sm wht-input text-right\" step=\"0.01\" min=\"0\" placeholder=\"0.00\" value=\"0.00\">
        </td>
        <td>
            <input type=\"number\" name=\"PettyCashDetail[` + rowIndex + `][other]\" class=\"form-control form-control-sm other-input text-right\" step=\"0.01\" min=\"0\" placeholder=\"0.00\" value=\"0.00\">
        </td>
        <td>
            <input type=\"text\" name=\"PettyCashDetail[` + rowIndex + `][total]\" class=\"form-control form-control-sm total-input text-right\" readonly style=\"background-color: #f8f9fa;\" value=\"0.00\">
        </td>
        <td class=\"text-center\">
            <button type=\"button\" class=\"btn btn-sm btn-danger btn-remove-row\" title=\"ลบรายการ\">
                <i class=\"fas fa-trash\"></i>
            </button>
        </td>
    </tr>`;
    
    $('#details-table tbody').append(newRowHtml);
}

// Remove row
function removeDetailRow(button) {
    var rowCount = $('#details-table tbody tr').length;
    if (rowCount > 1) {
        $(button).closest('tr').remove();
        
        // Re-index remaining rows
        $('#details-table tbody tr').each(function(index) {
            $(this).find('input, textarea, select').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
        
        calculateGrandTotal();
    } else {
        alert('ต้องมีรายการอย่างน้อย 1 รายการ');
    }
}

// Calculate VAT amount automatically (7%)
function calculateVAT(row) {
    var amount = parseFloat(row.find('.amount-input').val()) || 0;
    var vatRate = parseFloat(row.find('input[name$=\"[vat]\"]').val()) || 0;
    
    if (vatRate > 0) {
        var vatAmount = (amount * vatRate) / 100;
        row.find('.vat-amount-input').val(vatAmount.toFixed(2));
        calculateRowTotal(row);
    }
}

// Event handlers
$(document).on('input', '.amount-input, .vat-amount-input, .wht-input, .other-input', function() {
    calculateRowTotal($(this).closest('tr'));
});

$(document).on('input', 'input[name$=\"[vat]\"]', function() {
    calculateVAT($(this).closest('tr'));
});

$(document).on('click', '.btn-add-row', function() {
    addDetailRow();
});

$(document).on('click', '.btn-remove-row', function() {
    removeDetailRow(this);
});

// Initialize calculations on page load
$(document).ready(function() {
    calculateGrandTotal();
});
");
?>

    <div class="petty-cash-voucher-form">

        <?php $form = ActiveForm::begin([
            'id' => 'petty-cash-form',
            'options' => ['class' => 'form-horizontal'],
            'fieldConfig' => [
                'template' => '<div class="col-sm-3">{label}</div><div class="col-sm-9">{input}{error}</div>',
                'labelOptions' => ['class' => 'control-label'],
            ]
        ]); ?>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-money-bill-wave"></i> ข้อมูลใบสำคัญจ่ายเงินสดย่อย
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'pcv_no')->textInput([
                            'maxlength' => true,
                            'readonly' => !$model->isNewRecord,
                            'placeholder' => 'จะสร้างอัตโนมัติ'
                        ]) ?>

                        <?= $form->field($model, 'date')->widget(DatePicker::class, [
                            'options' => ['placeholder' => 'เลือกวันที่'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ]
                        ]) ?>

                        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'ชื่อผู้รับเงิน']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'amount')->textInput([
                            'type' => 'number',
                            'step' => '0.01',
                            'readonly' => true,
                            'class' => 'form-control text-right'
                        ]) ?>

                        <?= $form->field($model, 'issued_by')->textInput(['maxlength' => true, 'placeholder' => 'ผู้จัดทำ']) ?>

                        <?= $form->field($model, 'approved_by')->textInput(['maxlength' => true, 'placeholder' => 'ผู้อนุมัติ']) ?>
                    </div>
                </div>

                <?= $form->field($model, 'paid_for')->textarea([
                    'rows' => 3,
                    'placeholder' => 'จ่ายเพื่อ...'
                ])->label('จ่ายเพื่อ') ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <table style="width: 100%">
                    <tr>
                        <td>
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list"></i> รายละเอียดการจ่าย
                            </h5>
                        </td>
                        <td style="text-align: right">
                            <button type="button" class="btn btn-sm btn-primary btn-add-row">
                                <i class="fas fa-plus"></i> เพิ่มรายการ
                            </button>
                        </td>
                    </tr>
                </table>


            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="details-table" class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                        <tr>
                            <th width="10%">A/C CODE</th>
                            <th width="10%">DATE</th>
                            <th width="25%">DETAIL</th>
                            <th width="12%">AMOUNT</th>
                            <th width="8%">VAT</th>
                            <th width="10%">VAT จำนวน</th>
                            <th width="8%">W/H</th>
                            <th width="8%">อื่นๆ</th>
                            <th width="12%">TOTAL</th>
                            <th width="5%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($details as $index => $detail): ?>
                            <tr>
                                <td>
                                    <?= Html::textInput("PettyCashDetail[{$index}][ac_code]", $detail->ac_code, [
                                        'class' => 'form-control form-control-sm',
                                        'placeholder' => 'รหัสบัญชี'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::input('date', "PettyCashDetail[{$index}][detail_date]", $detail->detail_date, [
                                        'class' => 'form-control form-control-sm'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textarea("PettyCashDetail[{$index}][detail]", $detail->detail, [
                                        'class' => 'form-control form-control-sm',
                                        'rows' => 2,
                                        'placeholder' => 'รายละเอียด'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("PettyCashDetail[{$index}][amount]", $detail->amount, [
                                        'class' => 'form-control form-control-sm amount-input text-right',
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'placeholder' => '0.00'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("PettyCashDetail[{$index}][vat]", $detail->vat, [
                                        'class' => 'form-control form-control-sm text-right',
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'placeholder' => '0.00'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("PettyCashDetail[{$index}][vat_amount]", $detail->vat_amount, [
                                        'class' => 'form-control form-control-sm vat-amount-input text-right',
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'placeholder' => '0.00'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("PettyCashDetail[{$index}][wht]", $detail->wht, [
                                        'class' => 'form-control form-control-sm wht-input text-right',
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'placeholder' => '0.00'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("PettyCashDetail[{$index}][other]", $detail->other, [
                                        'class' => 'form-control form-control-sm other-input text-right',
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'placeholder' => '0.00'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("PettyCashDetail[{$index}][total]", $detail->total, [
                                        'class' => 'form-control form-control-sm total-input text-right',
                                        'readonly' => true,
                                        'style' => 'background-color: #f8f9fa;'
                                    ]) ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-row" title="ลบรายการ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="form-group mt-4">
            <div class="text-center">
                <?= Html::submitButton($model->isNewRecord ? '<i class="fas fa-save"></i> บันทึก' : '<i class="fas fa-save"></i> แก้ไข', [
                    'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
                ]) ?>
                <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['index'], ['class' => 'btn btn-secondary']) ?>
                <?php if (!$model->isNewRecord): ?>
                    <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                        'class' => 'btn btn-info',
                        'target' => '_blank'
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

        <?php
        $model_doc_slip = \common\models\PettyCashVoucherDocSlip::find()->where(['petty_cash_voucher_id' => $model->id])->all();
        $model_doc_bill = \common\models\PettyCashVoucherDocBill::find()->where(['petty_cash_voucher_id' => $model->id])->all();
        ?>
        <hr>
        <?php if (!$model->isNewRecord): ?>
            <br/>
            <div class="label">
                <h4>เอกสารแนบสลิป</h4>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <table class="table table-bordered table-striped" style="width: 100%">
                        <thead>
                        <tr>
                            <th style="width: 5%;text-align: center">#</th>
                            <th style="width: 50%;text-align: center">ชื่อไฟล์</th>
                            <th style="width: 10%;text-align: center">ดูเอกสาร</th>
                            <th style="width: 5%;text-align: center">-</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($model_doc_slip != null): ?>

                            <?php foreach ($model_doc_slip as $key => $value): ?>
                                <tr>
                                    <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                                    <td><?= $value->doc ?></td>
                                    <td style="text-align: center">
                                        <a href="<?= Yii::$app->request->BaseUrl . '/uploads/pettycash_doc_slip/' . $value->doc ?>"
                                           target="_blank">
                                            ดูเอกสาร
                                        </a>
                                    </td>
                                    <td style="text-align: center">
                                        <div class="btn btn-danger" data-var="<?= trim($value->doc) ?>"
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
            <br/>

            <form action="<?= Url::to(['petty-cash-voucher/add-doc-file'], true) ?>" method="post"
                  enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $model->id ?>">
                <div style="padding: 10px;background-color: lightgrey;border-radius: 5px">
                    <div class="row">
                        <div class="col-lg-12">
                            <label for="">เอกสารแนบ</label>
                            <input type="file" name="file_doc" multiple>
                        </div>
                    </div>
                    <br/>
                    <div class="row">
                        <div class="col-lg-12">
                            <button class="btn btn-info">
                                <i class="fas fa-upload"></i> อัพโหลดเอกสารแนบสลิป
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <form id="form-delete-doc-file" action="<?= Url::to(['petty-cash-voucher/delete-doc-file'], true) ?>"
                  method="post">
                <input type="hidden" name="id" value="<?= $model->id ?>">
                <input type="hidden" class="delete-doc-list" name="doc_delete_list" value="">
            </form>

            <hr>
            <br/>
            <div class="label">
                <h4>เอกสารแนบใบเสร็จค่าสินค้า</h4>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <table class="table table-bordered table-striped" style="width: 100%">
                        <thead>
                        <tr>
                            <th style="width: 5%;text-align: center">#</th>
                            <th style="width: 50%;text-align: center">ชื่อไฟล์</th>
                            <th style="width: 10%;text-align: center">ดูเอกสาร</th>
                            <th style="width: 5%;text-align: center">-</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($model_doc_bill != null): ?>

                            <?php foreach ($model_doc_bill as $key => $value): ?>
                                <tr>
                                    <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                                    <td><?= $value->doc ?></td>
                                    <td style="text-align: center">
                                        <a href="<?= Yii::$app->request->BaseUrl . '/uploads/pettycash_doc_bill/' . $value->doc ?>"
                                           target="_blank">
                                            ดูเอกสาร
                                        </a>
                                    </td>
                                    <td style="text-align: center">
                                        <div class="btn btn-danger" data-var="<?= trim($value->doc) ?>"
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
            <br/>

            <form action="<?= Url::to(['petty-cash-voucher/add-doc-file-bill'], true) ?>" method="post"
                  enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $model->id ?>">
                <div style="padding: 10px;background-color: lightgrey;border-radius: 5px">
                    <div class="row">
                        <div class="col-lg-12">
                            <label for="">เอกสารแนบ</label>
                            <input type="file" name="file_doc" multiple>
                        </div>
                    </div>
                    <br/>
                    <div class="row">
                        <div class="col-lg-12">
                            <button class="btn btn-info">
                                <i class="fas fa-upload"></i> อัพโหลดเอกสารใบเสร็จค่าสินค้า
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            <form id="form-delete-doc-file" action="<?= Url::to(['petty-cash-voucher/delete-doc-file'], true) ?>"
                  method="post">
                <input type="hidden" name="id" value="<?= $model->id ?>">
                <input type="hidden" class="delete-doc-list" name="doc_delete_list" value="">
            </form>
        <?php endif; ?>
    </div>
<?php
$script = <<< JS
function delete_doc(e){
    var file_name = e.attr('data-var');
    if(file_name != null){
        $(".delete-doc-list").val(file_name);
        $("#form-delete-doc-file").submit();
    }
}
JS;
$this->registerJs($script, static::POS_END);
?>