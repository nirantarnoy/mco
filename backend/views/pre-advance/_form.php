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

$selectedNonePrIds = \backend\models\PreAdvanceRef::find()
    ->where(['pre_advance_id' => $model->id, 'ref_type' => \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR])
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
                    <label class="form-label">เลือกใบสั่งซื้อ (None PR) - ยังไม่สามารถกดเลือกข้อมูลได้</label>
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
                <div class="col-md-8">
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
            <h5 class="mb-0">ไฟล์แนบเอกสาร</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">แนบไฟล์เพิ่มเติม (เลือกได้หลายไฟล์)</label>
                        <?= Html::fileInput('upload_files[]', null, ['multiple' => true, 'class' => 'form-control', 'accept' => 'image/*,application/pdf,.doc,.docx,.xls,.xlsx']) ?>
                    </div>
                </div>
            </div>
            
            <?php if (!$model->isNewRecord && !empty($model->preAdvanceDocs)): ?>
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
                                <?php foreach ($model->preAdvanceDocs as $doc): ?>
                                    <tr id="doc-row-<?= $doc->id ?>">
                                        <td>
                                            <?= Html::a($doc->file_name, Yii::getAlias('@web/uploads/pre_advance/') . $doc->file_path, ['target' => '_blank']) ?>
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
