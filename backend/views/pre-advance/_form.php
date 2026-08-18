<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use backend\models\PreAdvance;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model backend\models\PreAdvance */
/* @var $form yii\widgets\ActiveForm */

$getNonePrByVendorUrl = Url::to(['get-none-pr-by-vendor']);
$pullMultipleUrl = Url::to(['pull-multiple']);
$removeAttachmentUrl = Url::to(['remove-attachment']);

$selectedRefRecords = \backend\models\PreAdvanceRef::find()
    ->where(['pre_advance_id' => $model->id])
    ->all();

$selectedNonePrList = [];
$selectedNonePrIds = [];
foreach ($selectedRefRecords as $ref) {
    if ($ref->ref_type == \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR) {
        $m = \backend\models\PurchaseMaster::findOne($ref->ref_id);
        if ($m) {
            $key = 'none_pr_' . $m->id;
            $selectedNonePrList[$key] = '[None PR] ' . $m->docnum . ' (ยอดรวม: ' . number_format($m->total_amount, 2) . (!empty($m->supnam) ? ' - ' . $m->supnam : '') . ')';
            $selectedNonePrIds[] = $key;
        }
    } elseif ($ref->ref_type == \backend\models\PreAdvanceRef::REF_TYPE_PO) {
        $m = \backend\models\Purch::findOne($ref->ref_id);
        if ($m) {
            $key = 'po_' . $m->id;
            $selectedNonePrList[$key] = '[PO] ' . $m->purch_no . ' (ยอดรวม: ' . number_format($m->net_amount, 2) . (!empty($m->vendor_name) ? ' - ' . $m->vendor_name : '') . ')';
            $selectedNonePrIds[] = $key;
        }
    }
}

$existingDocsGrouped = [];
if (!$model->isNewRecord && !empty($model->preAdvanceDocs)) {
    foreach ($model->preAdvanceDocs as $doc) {
        $key = 'general';
        if ($doc->ref_type == \backend\models\PreAdvanceRef::REF_TYPE_PO && $doc->ref_id) {
            $key = 'po_' . $doc->ref_id;
        } elseif ($doc->ref_type == \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR && $doc->ref_id) {
            $key = 'none_pr_' . $doc->ref_id;
        }
        $existingDocsGrouped[$key][] = [
            'id' => $doc->id,
            'file_name' => $doc->file_name,
            'file_path' => $doc->file_path,
            'file_size' => round($doc->file_size / 1024, 2),
            'uploaded_by' => $doc->uploaded_by ? \common\models\User::findOne($doc->uploaded_by)->username : '-',
            'uploaded_at' => date('Y-m-d H:i', $doc->uploaded_at),
        ];
    }
}
$existingDocsJson = json_encode($existingDocsGrouped);
$webUploadPath = Yii::getAlias('@web/uploads/pre_advance/');
?>

<?php
$js = <<<JS
var existingDocs = {$existingDocsJson};
var webUploadPath = '{$webUploadPath}';

function renderDynamicAttachments() {
    var container = $('#dynamic-attachment-container');
    container.empty();

    var selectedData = $('#none-pr-select').select2('data') || [];
    
    if (selectedData.length === 0) {
        container.html('<div class="alert alert-light text-muted border mb-0"><i class="fa fa-info-circle"></i> โปรดเลือกรายการใบสั่งซื้อ / None PR ด้านบนเพื่อแสดงช่องแนบไฟล์แยกตามรายการ</div>');
    } else {
        $.each(selectedData, function(i, item) {
            var key = item.id;
            var text = item.text || item.id;
            
            var cardHtml = '<div class="card border mb-3 shadow-xs">' +
                '<div class="card-header bg-light d-flex justify-content-between align-items-center py-2">' +
                    '<div><span class="badge bg-primary text-white me-2">รายการ: ' + key + '</span> <strong>' + text + '</strong></div>' +
                '</div>' +
                '<div class="card-body py-3">' +
                    '<div class="form-group mb-2">' +
                        '<label class="form-label text-dark small fw-bold">เลือกไฟล์แนบสำหรับรายการนี้ (แนบได้หลายไฟล์):</label>' +
                        '<input type="file" name="upload_files[' + key + '][]" class="form-control" multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx">' +
                    '</div>';

            if (existingDocs[key] && existingDocs[key].length > 0) {
                cardHtml += '<div class="mt-3"><h6>รายการไฟล์แนบเดิมของรายการนี้:</h6><div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead class="bg-light"><tr><th>ชื่อไฟล์</th><th style="width: 10rem;">ผู้อัปโหลด</th><th style="width: 10rem;">วันที่อัปโหลด</th><th class="text-center" style="width: 6rem;">Action</th></tr></thead><tbody>';
                $.each(existingDocs[key], function(dIdx, doc) {
                    cardHtml += '<tr id="doc-row-' + doc.id + '">' +
                        '<td><a href="' + webUploadPath + doc.file_path + '" target="_blank">' + doc.file_name + '</a> <small class="text-muted">(' + doc.file_size + ' KB)</small></td>' +
                        '<td>' + doc.uploaded_by + '</td>' +
                        '<td>' + doc.uploaded_at + '</td>' +
                        '<td class="text-center"><button type="button" class="btn btn-danger btn-xs btn-remove-doc" data-id="' + doc.id + '"><i class="fa fa-trash"></i> ลบ</button></td>' +
                    '</tr>';
                });
                cardHtml += '</tbody></table></div></div>';
            }

            cardHtml += '</div></div>';
            container.append(cardHtml);
        });
    }

    var genContainer = $('#existing-docs-general');
    genContainer.empty();
    if (existingDocs['general'] && existingDocs['general'].length > 0) {
        var genHtml = '<div class="mt-2"><h6>รายการไฟล์แนบเดิมทั่วไป:</h6><div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead class="bg-light"><tr><th>ชื่อไฟล์</th><th style="width: 10rem;">ผู้อัปโหลด</th><th style="width: 10rem;">วันที่อัปโหลด</th><th class="text-center" style="width: 6rem;">Action</th></tr></thead><tbody>';
        $.each(existingDocs['general'], function(dIdx, doc) {
            genHtml += '<tr id="doc-row-' + doc.id + '">' +
                '<td><a href="' + webUploadPath + doc.file_path + '" target="_blank">' + doc.file_name + '</a> <small class="text-muted">(' + doc.file_size + ' KB)</small></td>' +
                '<td>' + doc.uploaded_by + '</td>' +
                '<td>' + doc.uploaded_at + '</td>' +
                '<td class="text-center"><button type="button" class="btn btn-danger btn-xs btn-remove-doc" data-id="' + doc.id + '"><i class="fa fa-trash"></i> ลบ</button></td>' +
            '</tr>';
        });
        genHtml += '</tbody></table></div></div>';
        genContainer.html(genHtml);
    }
}

function addLine(data = null) {
    var tr = $('<tr class="line-item">');
    
    var date_val = data && data.line_date ? data.line_date : '';
    tr.append('<td><input type="date" name="line_date[]" class="form-control" value="' + date_val + '"></td>');
    
    tr.append('<td><input type="text" name="line_description[]" class="form-control" value="' + (data ? data.description : '') + '"></td>');
    tr.append('<td><input type="number" name="line_amount[]" class="form-control line-amount" step="0.01" value="' + (data ? data.amount : '0.00') + '"></td>');
    tr.append('<td><input type="text" name="line_remark[]" class="form-control" value="' + (data ? data.remark : '') + '"></td>');
    
    tr.append('<td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-line"><i class="fa fa-trash"></i></button></td>');
    $('#voucher-lines tbody').append(tr);
}

function calculateTotal() {
    var total_amount = 0;
    $('.line-amount').each(function() {
        total_amount += parseFloat($(this).val()) || 0;
    });
    $('#total-amount').text(total_amount.toFixed(2));
    $('#preadvance-amount').val(total_amount);
}

function loadNonePrByVendor(vendorId) {
    var prevVal = $('#vendor-select').data('prev-val');
    if (prevVal !== vendorId) {
        $('#none-pr-select').val(null).trigger('change');
    }
    $('#vendor-select').data('prev-val', vendorId);
}

function pullMultipleData() {
    var none_pr_ids = $('#none-pr-select').val() || [];
    
    if (none_pr_ids.length === 0) {
        return;
    }
    
    $.ajax({
        url: '{$pullMultipleUrl}',
        type: 'POST',
        data: {none_pr_ids: none_pr_ids},
        success: function(res) {
            if(res.success) {
                $('#hidden-none-pr-ids').val(JSON.stringify(res.none_pr_ids));
            }
        }
    });
}

$('#none-pr-select').on('change', function() {
    var none_pr_ids = $(this).val() || [];
    $('#hidden-none-pr-ids').val(JSON.stringify(none_pr_ids));
    pullMultipleData();
    renderDynamicAttachments();
});

$(document).ready(function() {
    calculateTotal();
    
    $('#vendor-select').data('prev-val', $('#vendor-select').val());
    
    $('#btn-add-line').on('click', function() {
        addLine();
    });
    
    $('#voucher-lines tbody').on('input', '.line-amount', function() {
        calculateTotal();
    });
    
    $('#voucher-lines tbody').on('click', '.btn-remove-line', function() {
        $(this).closest('tr').remove();
        calculateTotal();
    });

    $(document).on('click', '.btn-remove-doc', function() {
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

    setTimeout(function() {
        renderDynamicAttachments();
    }, 300);
});
JS;
$this->registerJs($js);
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
?>

<div class="pre-advance-form">

    <?php $form = ActiveForm::begin([
        'id' => 'pre-advance-form',
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">ข้อมูล Pre-Advance</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <?= $form->field($model, 'vendor_id')->widget(Select2::className(), [
                        'data' => ArrayHelper::map(\backend\models\Vendor::find()->all(), 'id', 'name'),
                        'options' => ['placeholder' => 'เลือก Vendor...', 'id' => 'vendor-select'],
                        'pluginOptions' => ['allowClear' => true],
                        'pluginEvents' => [
                            "change" => "function() { loadNonePrByVendor($(this).val()); }",
                        ]
                    ])->label('Vendor') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'pre_advance_no')->textInput(['maxlength' => true, 'placeholder' => 'ระบุเลขที่ หรือปล่อยว่างเพื่อสร้างอัตโนมัติ']) ?>
                </div>
                <div class="col-md-4">
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
                <div class="col-md-6">
                    <label class="form-label">เลือกใบสั่งซื้อ / None PR</label>
                    <?= Select2::widget([
                        'name' => 'none_pr_ids[]',
                        'data' => $selectedNonePrList,
                        'value' => $selectedNonePrIds,
                        'options' => [
                            'placeholder' => 'ค้นหา/เลือก PO หรือ None PR...',
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
                                'data' => new JsExpression('function(params) { return {q:params.term, vendor_id: $("#vendor-select").val(), pre_advance_id: "' . ($model->id ?: '') . '"}; }'),
                                'processResults' => new JsExpression('function(data) { return {results: data.results}; }'),
                                'cache' => true
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'recipient_name')->widget(Select2::className(), [
                        'data' => PreAdvance::getRecipientNameOptions(),
                        'options' => ['placeholder' => 'เลือกชื่อผู้รับเงิน...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'tags' => true, // Allows user to type custom name if not in list
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'readonly' => true]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'status')->dropDownList([
                        \backend\models\PreAdvance::STATUS_ACTIVE => 'ใช้งาน (Active)',
                        \backend\models\PreAdvance::STATUS_DRAFT => 'ร่าง (Draft)',
                    ])->label('สถานะ') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'remark')->textInput(['placeholder' => 'หมายเหตุ']) ?>
                </div>
            </div>

            <input type="hidden" id="hidden-none-pr-ids" name="none_pr_ids" value='<?= json_encode($selectedNonePrIds) ?>'>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">บันทึกรายการตั้งเบิก</h5>
            <button type="button" class="btn btn-primary btn-sm" id="btn-add-line">
                <i class="fa fa-plus"></i> เพิ่มแถว
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="voucher-lines">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%">วันที่</th>
                            <th style="width: 40%">รายละเอียดการขออนุมัติค่าใช้จ่าย/ตั้งเบิก</th>
                            <th style="width: 15%">ยอดเบิก</th>
                            <th style="width: 25%">หมายเหตุ</th>
                            <th style="width: 5%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($model->isNewRecord): ?>
                            <!-- Empty row for new record -->
                        <?php else: ?>
                            <?php foreach ($model->preAdvanceLines as $line): ?>
                                <tr class="line-item">
                                    <td><input type="date" name="line_date[]" class="form-control" value="<?= Html::encode($line->line_date) ?>"></td>
                                    <td><input type="text" name="line_description[]" class="form-control" value="<?= Html::encode($line->description) ?>"></td>
                                    <td><input type="number" name="line_amount[]" class="form-control line-amount" step="0.01" value="<?= $line->amount ?>"></td>
                                    <td><input type="text" name="line_remark[]" class="form-control" value="<?= Html::encode($line->remark) ?>"></td>
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
                            <th colspan="2" class="text-end">ยอดรวมทั้งหมด</th>
                            <th id="total-amount" class="text-start">0.00</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fa fa-paperclip me-1"></i> ไฟล์แนบเอกสาร (จำแนกตามรายการที่เลือก)</h5>
        </div>
        <div class="card-body">
            <div id="dynamic-attachment-container"></div>

            <div class="card border border-secondary mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><strong>แนบไฟล์เอกสารเพิ่มเติม / ทั่วไป (General Pre-Advance Attachments)</strong></h6>
                </div>
                <div class="card-body">
                    <div class="form-group mb-2">
                        <input type="file" name="upload_files[general][]" class="form-control" multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx">
                    </div>
                    <div id="existing-docs-general"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group text-end mt-4">
        <?= Html::submitButton($model->isNewRecord ? 'บันทึกรายการ' : 'อัพเดทรายการ', ['class' => 'btn btn-success btn-lg px-5']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<style>
.card { border-radius: 8px; overflow: hidden; }
.card-header { font-weight: 600; }
.form-label { font-weight: 500; }
#voucher-lines th { text-align: center; }
#voucher-lines td { padding: 8px; }
.btn-lg { border-radius: 30px; }
</style>
