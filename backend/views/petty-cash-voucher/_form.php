<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\PettyCashVoucher */
/* @var $details backend\models\PettyCashDetail[] */
/* @var $form yii\widgets\ActiveForm */
// CSS สำหรับ autocomplete
$autocompleteCSS = <<<CSS
.autocomplete-dropdown {
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.autocomplete-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.autocomplete-item:hover {
    background-color: #f5f5f5;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item.highlighted {
    background-color: #007bff;
    color: white;
}

.product-code {
    color: #666;
    font-size: 12px;
}

.product-field-container {
    position: relative;
}

.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;
    background: white;
    border: 1px solid #ccc;
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    display: none;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.form-group {
    margin-bottom: 1rem;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.item-number {
    font-weight: bold;
    color: #6c757d;
}

.dynamicform_wrapper .btn-success {
    margin-right: 5px;
}

.table-responsive {
    overflow: visible !important; /* แทน auto */
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.table-responsive .table {
    overflow: visible !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}
CSS;

$this->registerCss($autocompleteCSS);

// Register Font Awesome
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
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

//// Add new row
//function addDetailRow() {
//    var rowIndex = $('#details-table tbody tr').length;
//    
//    var newRowHtml = `
//    <tr>
//        <td>
//            <input type=\"text\" name=\"PettyCashDetail[` + rowIndex + `][ac_code]\" class=\"form-control form-control-sm\" placeholder=\"รหัสบัญชี\" maxlength=\"50\">
//        </td>
//        <td>
//            <input type=\"date\" name=\"PettyCashDetail[` + rowIndex + `][detail_date]\" class=\"form-control form-control-sm\">
//        </td>
//        <td>
//            <textarea name=\"PettyCashDetail[` + rowIndex + `][detail]\" class=\"form-control form-control-sm\" rows=\"2\" placeholder=\"รายละเอียดการจ่าย\"></textarea>
//        </td>
//        <td>
//            <input type=\"number\" name=\"PettyCashDetail[` + rowIndex + `][amount]\" class=\"form-control form-control-sm amount-input text-right\" step=\"0.01\" min=\"0\" placeholder=\"0.00\" value=\"0.00\">
//        </td>
//        <td>
//            <input type=\"number\" name=\"PettyCashDetail[` + rowIndex + `][vat]\" class=\"form-control form-control-sm text-right\" step=\"0.01\" min=\"0\" placeholder=\"0.00\" value=\"0.00\">
//        </td>
//        <td>
//            <input type=\"number\" name=\"PettyCashDetail[` + rowIndex + `][vat_amount]\" class=\"form-control form-control-sm vat-amount-input text-right\" step=\"0.01\" min=\"0\" placeholder=\"0.00\" value=\"0.00\">
//        </td>
//        <td>
//            <input type=\"number\" name=\"PettyCashDetail[` + rowIndex + `][wht]\" class=\"form-control form-control-sm wht-input text-right\" step=\"0.01\" min=\"0\" placeholder=\"0.00\" value=\"0.00\">
//        </td>
//        <td>
//            <input type=\"number\" name=\"PettyCashDetail[` + rowIndex + `][other]\" class=\"form-control form-control-sm other-input text-right\" step=\"0.01\" min=\"0\" placeholder=\"0.00\" value=\"0.00\">
//        </td>
//        <td>
//            <input type=\"text\" name=\"PettyCashDetail[` + rowIndex + `][total]\" class=\"form-control form-control-sm total-input text-right\" readonly style=\"background-color: #f8f9fa;\" value=\"0.00\">
//        </td>
//        <td class=\"text-center\">
//            <button type=\"button\" class=\"btn btn-sm btn-danger btn-remove-row\" title=\"ลบรายการ\">
//                <i class=\"fas fa-trash\"></i>
//            </button>
//        </td>
//    </tr>`;
//    
//    $('#details-table tbody').append(newRowHtml);
//}

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

        <?php if (\Yii::$app->session->hasFlash('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= \Yii::$app->session->getFlash('warning') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (\Yii::$app->session->hasFlash('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?= \Yii::$app->session->getFlash('info') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php $form = ActiveForm::begin([
            'id' => 'petty-cash-form',
            'options' => ['class' => 'form-horizontal','enctype'=>'multipart/form-data',],
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

                        <?= $form->field($model, 'pay_for_emp_id')->widget(\kartik\select2\Select2::className(), [
                            'data' => \yii\helpers\ArrayHelper::map(\backend\models\Employee::find()->all(), 'id', function ($data) {
                                return $data->fname . ' ' . $data->lname;
                            }),
                            'options' => [
                                'placeholder' => 'เลือกพนักงาน',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]) ?>
                        <?= $form->field($model, 'vendor_id')->widget(\kartik\select2\Select2::className(), [
                            'data' => \yii\helpers\ArrayHelper::map(\backend\models\Vendor::find()->all(), 'id', function ($data) {
                                return $data->code . ' ' . $data->name;
                            }),
                            'options' => [
                                'placeholder' => 'เลือกผู้จำหน่าย',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'amount')->textInput([
                            'type' => 'number',
                            'step' => '0.01',
                            'readonly' => true,
                            'class' => 'form-control text-right'
                        ]) ?>
                        <?= $form->field($model, 'quotation_id')->widget(\kartik\select2\Select2::className(), [
                            'data' => \yii\helpers\ArrayHelper::map(\backend\models\Quotation::find()->all(), 'id', function ($data) {
                                return $data->quotation_no;
                            }),
                            'options' => [
                                'placeholder' => '--เลือกใบเสนอราคา--',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]) ?>
                        <?= $form->field($model, 'job_id')->widget(\kartik\select2\Select2::className(), [
                            'data' => \yii\helpers\ArrayHelper::map(\backend\models\Job::find()->all(), 'id', function ($data) {
                                return $data->job_no;
                            }),
                            'options' => [
                                'placeholder' => '--เลือกใบงาน--',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]) ?>

                        <?= $form->field($model, 'issued_by')->textInput(['maxlength' => true, 'placeholder' => 'ผู้จัดทำ', 'readonly' => 'readonly', 'value' => $model->isNewRecord ? \backend\models\User::findEmployeeNameByUserId(\Yii::$app->user->id) : $model->issued_by]) ?>

                        <?= $form->field($model, 'approved_by')->textInput(['maxlength' => true, 'placeholder' => 'ผู้อนุมัติ', 'readonly' => 'readonly','value'=>$model->approved_by?\backend\models\User::findEmployeeNameByUserId($model->approved_by):'']) ?>
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
                            <th width="10%">สำหรับเลขที่บิล</th>
                            <th width="10%">DATE</th>
                            <th width="25%">DETAIL</th>
                            <th width="10%">ใบงาน</th>
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
                                    <?= Html::textInput("PettyCashDetail[{$index}][job_ref_detail]", \backend\models\Job::findJobNo($detail->job_ref_id), [
                                        'class' => 'form-control form-control-sm job-autocomplete',
                                        'placeholder' => 'ใบงาน',
                                        'data-index' => $index,
                                        'autocomplete'=>'off',
                                    ]) ?>
                                    <?= Html::hiddenInput("PettyCashDetail[{$index}][job_ref_id]", $detail->job_ref_id, [
                                        'class' => 'job-id-hidden',
                                        'data-index' => $index
                                    ]) ?>
                                    <div class="autocomplete-dropdown" data-index="<?= $index ?>"></div>
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
        <?php if ($model->isNewRecord): ?>
            <div style="padding: 10px;background-color: lightgrey;border-radius: 5px">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">อัพโหลดเอกสารแนบสลิป</label>
                        <input type="file" name="file_doc_slip" multiple>
                    </div>
                </div>
            </div>
            <br/>
            <div style="padding: 10px;background-color: lightgrey;border-radius: 5px">
                <div class="row">
                    <div class="col-lg-12">
                        <label for="">อัพโหลดเอกสารค่าสินค้า</label>
                        <input type="file" name="file_doc_bill" multiple>
                    </div>
                </div>

            </div>
        <?php endif; ?>

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
                                             onclick="delete_doc($(this),1)">ลบ
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
<!--            <form id="form-delete-doc-file" action="--><?php //= Url::to(['petty-cash-voucher/delete-doc-file'], true) ?><!--"-->
<!--                  method="post">-->
<!--                <input type="hidden" name="id" value="--><?php //= $model->id ?><!--">-->
<!--                <input type="hidden" class="delete-doc-list" name="doc_delete_list" value="">-->
<!--            </form>-->

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
                                             onclick="delete_doc($(this),2)">ลบ
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
                <input type="hidden" class="delete-doc-type" name="doc_delete_type" value="">
            </form>
        <?php endif; ?>
    </div>
<?php
// URL สำหรับ AJAX
$ajax_url = Url::to(['get-job-info']);
$script = <<< JS
// ตัวแปรเก็บข้อมูลสินค้า
var productsData = [];
var isProductsLoaded = false;

// Function to add new row (แก้ไขในส่วนนี้)
function addDetailRow() {
    var rowIndex = $('#details-table tbody tr').length;
    
    var newRowHtml = `
    <tr>
        <td>
            <input type="text" name="PettyCashDetail[` + rowIndex + `][ac_code]" class="form-control form-control-sm" placeholder="รหัสบัญชี" maxlength="50">
        </td>
        <td>
            <input type="date" name="PettyCashDetail[` + rowIndex + `][detail_date]" class="form-control form-control-sm">
        </td>
        <td>
            <textarea name="PettyCashDetail[` + rowIndex + `][detail]" class="form-control form-control-sm" rows="2" placeholder="รายละเอียดการจ่าย"></textarea>
        </td>
        <td>
            <div class="product-field-container">
                <input type="text" name="PettyCashDetail[` + rowIndex + `][job_ref_id]" class="form-control form-control-sm job-autocomplete" data-index="` + rowIndex + `" placeholder="ใบงาน">
                <div class="autocomplete-dropdown" data-index="` + rowIndex + `"></div>
            </div>
        </td>
        <td>
            <input type="number" name="PettyCashDetail[` + rowIndex + `][amount]" class="form-control form-control-sm amount-input text-right" step="0.01" min="0" placeholder="0.00" value="0.00">
        </td>
        <td>
            <input type="number" name="PettyCashDetail[` + rowIndex + `][vat]" class="form-control form-control-sm text-right" step="0.01" min="0" placeholder="0.00" value="0.00">
        </td>
        <td>
            <input type="number" name="PettyCashDetail[` + rowIndex + `][vat_amount]" class="form-control form-control-sm vat-amount-input text-right" step="0.01" min="0" placeholder="0.00" value="0.00">
        </td>
        <td>
            <input type="number" name="PettyCashDetail[` + rowIndex + `][wht]" class="form-control form-control-sm wht-input text-right" step="0.01" min="0" placeholder="0.00" value="0.00">
        </td>
        <td>
            <input type="number" name="PettyCashDetail[` + rowIndex + `][other]" class="form-control form-control-sm other-input text-right" step="0.01" min="0" placeholder="0.00" value="0.00">
        </td>
        <td>
            <input type="text" name="PettyCashDetail[` + rowIndex + `][total]" class="form-control form-control-sm total-input text-right" readonly style="background-color: #f8f9fa;" value="0.00">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btn-remove-row" title="ลบรายการ">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`;
    
    $('#details-table tbody').append(newRowHtml);
}
// ฟังก์ชันโหลดข้อมูลสินค้า
function loadProductsData() {
    if (isProductsLoaded) return;
    $.ajax({
        url: '$ajax_url',
        type: 'GET',
        data: { action: 'get-all-jobs' },
        dataType: 'json',
        success: function(data) {
            productsData = data;
            isProductsLoaded = true;
            console.log('Loading data is ',productsData);
        },
        error: function() {
            console.log('Error loading jobs data');
            productsData = [];
        }
    });
}

// ฟังก์ชันค้นหาสินค้า
function searchProducts(query) {
    if (!query || query.length < 1) return [];
    
    query = query.toLowerCase();
    return productsData.filter(function(product) {
        return product.job_no.toLowerCase().includes(query) || 
               product.display.toLowerCase().includes(query);
    }).slice(0, 10);
}

// ฟังก์ชันแสดงผลลัพธ์ (แก้ไขเล็กน้อย)
function showAutocompleteResults(input, results) {
    var index = input.attr('data-index') || input.closest('tr').index();
    var dropdown = input.siblings('.autocomplete-dropdown');
    
    // ถ้าไม่เจอ dropdown ที่เป็น sibling ให้หาจาก data-index
    if (dropdown.length === 0) {
        dropdown = $('.autocomplete-dropdown[data-index="' + index + '"]');
    }
    
    dropdown.empty();
    
    if (results.length === 0) {
        dropdown.hide();
        return;
    }
    
    results.forEach(function(product) {
        var item = $('<div class="autocomplete-item">')
            .html('<div>' + product.job_no + '</div><div class="product-code">' + product.display + '</div>')
            .data('product', product);
        dropdown.append(item);
    });
    
    dropdown.show();
    console.log('Showing dropdown with', results.length, 'results'); // Debug log
}

// ฟังก์ชันซ่อน dropdown
function hideAutocomplete(index) {
    setTimeout(function() {
        $('.autocomplete-dropdown[data-index="' + index + '"]').hide();
    }, 200);
}

// แก้ไขส่วนของ existing rows เพื่อให้มี data-index
$(document).ready(function() {
    // เพิ่ม data-index ให้กับแถวที่มีอยู่แล้ว
    $('#details-table tbody tr').each(function(index) {
        $(this).find('.job-autocomplete').attr('data-index', index);
        $(this).find('.autocomplete-dropdown').attr('data-index', index);
    });
    
    // โหลดข้อมูลสินค้าตอนเริ่มต้น
    loadProductsData();
    
    // Event สำหรับ autocomplete
    $(document).on('input', '.job-autocomplete', function() {
        var input = $(this);
        var query = input.val();
        
        console.log('Input query:', query); // Debug log
        
        if (!isProductsLoaded) {
            console.log('Products not loaded yet'); // Debug log
            loadProductsData();
            return;
        }
        
        var results = searchProducts(query);
        console.log('Search results:', results); // Debug log
        showAutocompleteResults(input, results);
    });
    
    $(document).on('focus', '.job-autocomplete', function() {
        var input = $(this);
        var query = input.val();
       
        if (!isProductsLoaded) {
            loadProductsData();
            return;
        }
       
        if (query) {
            var results = searchProducts(query);
            showAutocompleteResults(input, results);
        }
    });
    
    $(document).on('blur', '.job-autocomplete', function() {
        var input = $(this);
        setTimeout(function() {
            input.siblings('.autocomplete-dropdown').hide();
        }, 200);
    });
    
    $(document).on('click', '.autocomplete-item', function() {
        var product = $(this).data('product');
        var dropdown = $(this).closest('.autocomplete-dropdown');
        var input = dropdown.siblings('.job-autocomplete');
        selectProduct(input, product);
    });
    
    // Event navigation ด้วย keyboard
    $(document).on('keydown', '.job-autocomplete', function(e) {
        var input = $(this);
        var dropdown = input.siblings('.autocomplete-dropdown');
        var items = dropdown.find('.autocomplete-item');
        var highlighted = items.filter('.highlighted');
        
        if (e.keyCode === 40) { // Arrow Down
            e.preventDefault();
            if (highlighted.length === 0) {
                items.first().addClass('highlighted');
            } else {
                highlighted.removeClass('highlighted');
                var next = highlighted.next('.autocomplete-item');
                if (next.length) {
                    next.addClass('highlighted');
                } else {
                    items.first().addClass('highlighted');
                }
            }
        } else if (e.keyCode === 38) { // Arrow Up
            e.preventDefault();
            if (highlighted.length === 0) {
                items.last().addClass('highlighted');
            } else {
                highlighted.removeClass('highlighted');
                var prev = highlighted.prev('.autocomplete-item');
                if (prev.length) {
                    prev.addClass('highlighted');
                } else {
                    items.last().addClass('highlighted');
                }
            }
        } else if (e.keyCode === 13) { // Enter
            e.preventDefault();
            if (highlighted.length) {
                var product = highlighted.data('product');
                selectProduct(input, product);
            }
        } else if (e.keyCode === 27) { // Escape
            dropdown.hide();
        }
    });
});

// // แก้ไขฟังก์ชันเลือกสินค้า
// function selectProduct(input, product) {
//     // อัพเดตค่า
//     input.val(product.display);
//    
//     // ซ่อน dropdown
//     input.siblings('.autocomplete-dropdown').hide();
//    
//     // คำนวณยอดรวม (ถ้ามี)
//     // calculateLineTotal(index);
// }
// ฟังก์ชันเลือกสินค้า - แสดง job_no แต่เก็บ id
function selectProduct(input, product) {
    var index = input.attr('data-index');
    
    // แสดง job_no ใน input (แทนที่จะเป็น display)
    input.val(product.job_no);
    
    // บันทึก product.id ใน hidden input
    var hiddenInput = input.siblings('.job-id-hidden');
    if (hiddenInput.length === 0) {
        hiddenInput = $('.job-id-hidden[data-index="' + index + '"]');
    }
    hiddenInput.val(product.id);
    
    // อัพเดตจำนวนเงิน (ถ้ามีข้อมูล)
    if (product.job_amount) {
        var amountInput = input.closest('tr').find('.amount-input');
        amountInput.val(parseFloat(product.job_amount).toFixed(2));
        
        // คำนวณยอดรวมใหม่
      //  calculateRowTotal(input.closest('tr'));
    }
    
    // ซ่อน dropdown
    input.siblings('.autocomplete-dropdown').hide();
    
    console.log('Selected job:', product.job_no, 'ID:', product.id, 'Display in input:', product.job_no);
}

// ฟังก์ชันตรวจสอบการเลือก job โดยใช้ job_no
function validateJobSelection(input) {
    var hiddenInput = input.siblings('.job-id-hidden');
    if (hiddenInput.length === 0) {
        var index = input.attr('data-index');
        hiddenInput = $('.job-id-hidden[data-index="' + index + '"]');
    }
    
    var displayValue = input.val(); // ตอนนี้เป็น job_no
    var jobId = hiddenInput.val();
    
    // ถ้ามี job_no แต่ไม่มี job_id แสดงว่าพิมพ์เอง ไม่ได้เลือกจาก dropdown
    if (displayValue && !jobId) {
        // ลองหา job ที่ตรงกับ job_no ที่พิมพ์
        var matchedJob = productsData.find(function(product) {
            return product.job_no.toLowerCase() === displayValue.toLowerCase();
        });
        
        if (matchedJob) {
            // ถ้าเจอ job ที่ตรงกัน ให้ set id อัตโนมัติ
            hiddenInput.val(matchedJob.id);
            console.log('Auto-matched job:', matchedJob.job_no, 'ID:', matchedJob.id);
            return true;
        } else {
            // ถ้าไม่เจอ ให้แสดงเตือน
            console.warn('Job "' + displayValue + '" not found. Please select from dropdown.');
            
            // อาจจะเปลี่ยนสีกรอบเป็นแดงเพื่อแสดงข้อผิดพลาด
            input.addClass('is-invalid');
            
            // หรือล้างค่า
            // input.val('');
            // hiddenInput.val('');
            
            return false;
        }
    }
    
    // ถ้าทั้งคู่ว่าง หรือทั้งคู่มีค่า ถือว่าถูกต้อง
    input.removeClass('is-invalid');
    return true;
}
function delete_doc(e,typeid){
    var file_name = e.attr('data-var');
    if(file_name != null){
       // alert();
        $(".delete-doc-list").val(file_name);
        $(".delete-doc-type").val(typeid);
        $("#form-delete-doc-file").submit();
    }
}
JS;
$this->registerJs($script, static::POS_END);
?>