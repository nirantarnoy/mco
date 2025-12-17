<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\DeliveryNote */
/* @var $details backend\models\DeliveryNoteLine[] */
/* @var $form yii\widgets\ActiveForm */

$this->registerCss("
    .table-responsive { overflow: visible !important; }
    .btn-remove-row { margin-top: 5px; }
");

// Generate unit options for JS
$units = \backend\models\Unit::find()->all();
$unitOptions = "";
foreach ($units as $unit) {
    $unitOptions .= "<option value=\\\"" . $unit->id . "\\\">" . Html::encode($unit->name) . "</option>";
}

$this->registerJs("
    function addDetailRow() {
        var rowIndex = $('#details-table tbody tr').length;
        var newRowHtml = `
        <tr>
            <td>
                <input type=\"text\" name=\"DeliveryNoteLine[` + rowIndex + `][item_no]\" class=\"form-control form-control-sm\" value=\"` + (rowIndex + 1) + `\">
            </td>
            <td>
                <textarea name=\"DeliveryNoteLine[` + rowIndex + `][description]\" class=\"form-control form-control-sm\" rows=\"2\"></textarea>
            </td>
            <td>
                <input type=\"text\" name=\"DeliveryNoteLine[` + rowIndex + `][part_no]\" class=\"form-control form-control-sm\">
            </td>
            <td>
                <input type=\"number\" name=\"DeliveryNoteLine[` + rowIndex + `][qty]\" class=\"form-control form-control-sm text-right\" step=\"0.01\">
            </td>
            <td>
               <select name=\"DeliveryNoteLine[` + rowIndex + `][unit_id]\" class=\"form-control form-control-sm\">
                    <option value=\"\">-- หน่วย --</option>
                    $unitOptions
               </select>
            </td>
            <td class=\"text-center\">
                <button type=\"button\" class=\"btn btn-sm btn-danger btn-remove-row\">
                    <i class=\"fas fa-trash\"></i>
                </button>
            </td>
        </tr>`;
        $('#details-table tbody').append(newRowHtml);
    }

    $(document).on('click', '.btn-add-row', function() {
        addDetailRow();
    });

    $(document).on('click', '.btn-remove-row', function() {
        if ($('#details-table tbody tr').length > 1) {
            $(this).closest('tr').remove();
            // Re-index item numbers
            $('#details-table tbody tr').each(function(index) {
                $(this).find('input[name$=\'[item_no]\']').val(index + 1);
                // Fix names
                $(this).find('input, textarea, select').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        } else {
            alert('ต้องมีรายการอย่างน้อย 1 รายการ');
        }
    });
");

?>

<div class="delivery-note-form">

    <?php $form = ActiveForm::begin(['id' => 'delivery-note-form']); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">ข้อมูลใบส่งของ</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'dn_no')->textInput(['readonly' => true, 'placeholder' => 'สร้างอัตโนมัติ']) ?>

                    <?= $form->field($model, 'date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'เลือกวันที่'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ]
                    ]) ?>

                    <?= $form->field($model, 'job_id')->widget(\kartik\select2\Select2::className(), [
                        'data' => \yii\helpers\ArrayHelper::map(\backend\models\Job::find()->orderBy(['id' => SORT_DESC])->all(), 'id', 'job_no'),
                        'options' => ['placeholder' => 'เลือก Job No.'],
                        'pluginOptions' => ['allowClear' => true],
                        'pluginEvents' => [
                            "change" => "function() { 
                                var id = $(this).val();
                                if(id) {
                                    $.ajax({
                                        url: '" . \yii\helpers\Url::to(['get-job-details']) . "',
                                        data: {id: id},
                                        success: function(data) {
                                            if(data) {
                                                $('#deliverynote-customer_name').val(data.customer_name);
                                                $('#deliverynote-address').val(data.address);
                                                $('#deliverynote-tel').val(data.tel);
                                                $('#deliverynote-our_ref').val(data.our_ref);
                                                // $('#deliverynote-ref_no').val(data.ref_no);
                                                
                                                // Clear existing lines
                                                $('#details-table tbody').empty();
                                                
                                                // Add lines from job
                                                if(data.lines && data.lines.length > 0) {
                                                    data.lines.forEach(function(line, index) {
                                                        var newRowHtml = `
                                                        <tr>
                                                            <td>
                                                                <input type=\"text\" name=\"DeliveryNoteLine[` + index + `][item_no]\" class=\"form-control form-control-sm\" value=\"` + line.item_no + `\">
                                                            </td>
                                                            <td>
                                                                <textarea name=\"DeliveryNoteLine[` + index + `][description]\" class=\"form-control form-control-sm\" rows=\"2\">` + (line.description || '') + `</textarea>
                                                            </td>
                                                            <td>
                                                                <input type=\"text\" name=\"DeliveryNoteLine[` + index + `][part_no]\" class=\"form-control form-control-sm\" value=\"` + (line.part_no || '') + `\">
                                                            </td>
                                                            <td>
                                                                <input type=\"number\" name=\"DeliveryNoteLine[` + index + `][qty]\" class=\"form-control form-control-sm text-right\" step=\"0.01\" value=\"` + line.qty + `\">
                                                            </td>
                                                            <td>
                                                               <select name=\"DeliveryNoteLine[` + index + `][unit_id]\" class=\"form-control form-control-sm\">
                                                                    <option value=\"\">-- หน่วย --</option>
                                                                    $unitOptions
                                                               </select>
                                                            </td>
                                                            <td class=\"text-center\">
                                                                <button type=\"button\" class=\"btn btn-sm btn-danger btn-remove-row\">
                                                                    <i class=\"fas fa-trash\"></i>
                                                                </button>
                                                            </td>
                                                        </tr>`;
                                                        $('#details-table tbody').append(newRowHtml);
                                                        
                                                        // Set selected unit
                                                        $('select[name=\"DeliveryNoteLine[' + index + '][unit_id]\"]').val(line.unit_id);
                                                    });
                                                } else {
                                                    // Add empty row if no lines
                                                    $('.btn-add-row').click();
                                                }
                                            }
                                        }
                                    });
                                }
                            }",
                        ],
                    ]) ?>

                    <?= $form->field($model, 'customer_name')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'address')->textarea(['rows' => 3]) ?>
                    <?= $form->field($model, 'note')->textarea(['rows' => 3]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'attn')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'our_ref')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'from_name')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'tel')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'ref_no')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'page_no')->textInput(['maxlength' => true]) ?>
                </div>

            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">รายการสินค้า</h5>
                <button type="button" class="btn btn-sm btn-primary btn-add-row">
                    <i class="fas fa-plus"></i> เพิ่มรายการ
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="details-table" class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">ITEM</th>
                            <th width="40%">DESCRIPTION</th>
                            <th width="20%">P/N</th>
                            <th width="10%">Q'TY</th>
                            <th width="15%">UNIT</th>
                            <th width="5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($details as $index => $detail): ?>
                            <tr>
                                <td>
                                    <?= Html::textInput("DeliveryNoteLine[{$index}][item_no]", $detail->item_no ?: ($index + 1), ['class' => 'form-control form-control-sm']) ?>
                                </td>
                                <td>
                                    <?= Html::textarea("DeliveryNoteLine[{$index}][description]", $detail->description, ['class' => 'form-control form-control-sm', 'rows' => 2]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("DeliveryNoteLine[{$index}][part_no]", $detail->part_no, ['class' => 'form-control form-control-sm']) ?>
                                </td>
                                <td>
                                    <?= Html::input('number', "DeliveryNoteLine[{$index}][qty]", $detail->qty, ['class' => 'form-control form-control-sm text-right', 'step' => '0.01']) ?>
                                </td>
                                <td>
                                    <?= Html::dropDownList(
                                        "DeliveryNoteLine[{$index}][unit_id]",
                                        $detail->unit_id,
                                        \yii\helpers\ArrayHelper::map(\backend\models\Unit::find()->orderBy('name')->all(), 'id', 'name'),
                                        ['class' => 'form-control form-control-sm', 'prompt' => '-- หน่วย --']
                                    ) ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-row">
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

    <div class="form-group mt-3 text-center">
        <?= Html::submitButton('<i class="fas fa-save"></i> บันทึก', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>