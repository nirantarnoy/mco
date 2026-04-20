<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model backend\models\OcrPattern */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ocr-pattern-form">

    <?php $form = ActiveForm::begin(['id' => 'ocr-pattern-form']); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">ข้อมูลรูปแบบ</h3>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'เช่น บริษัท เอบีซี จำกัด']) ?>
                    <?= $form->field($model, 'tax_id')->textInput(['maxlength' => true, 'placeholder' => 'เลขผู้เสียภาษี 13 หลัก']) ?>
                    <?= $form->field($model, 'status')->dropDownList([1 => 'ใช้งาน', 0 => 'ระงับการใช้งาน']) ?>
                </div>
            </div>

            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">สูตรการดึงข้อมูล (Regex Patterns)</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> คลิกที่ข้อความจากผลการสแกนด้านล่างเพื่อนำมาสร้าง Pattern อัตโนมัติ</small>
                    </div>
                    
                    <?= $form->field($model, 'regex_invoice_no')->textInput(['maxlength' => true, 'id' => 'regex-invoice-no']) ?>
                    <?= $form->field($model, 'regex_date')->textInput(['maxlength' => true, 'id' => 'regex-date']) ?>
                    <?= $form->field($model, 'regex_total')->textInput(['maxlength' => true, 'id' => 'regex-total']) ?>
                </div>
            </div>
            
            <div class="form-group">
                <?= Html::submitButton('บันทึกข้อมูล', ['class' => 'btn btn-success btn-lg btn-block']) ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-camera"></i> สแกนตัวอย่างเพื่อสร้าง Pattern</h3>
                </div>
                <div class="card-body">
                    <?= FileInput::widget([
                        'name' => 'ocr_file',
                        'options' => [
                            'accept' => 'image/*,application/pdf',
                            'id' => 'ocr-input-file'
                        ],
                        'pluginOptions' => [
                            'showPreview' => false,
                            'showCaption' => true,
                            'showRemove' => true,
                            'showUpload' => false,
                            'browseLabel' => 'เลือกไฟล์ตัวอย่าง',
                        ],
                    ]) ?>
                    
                    <button type="button" class="btn btn-info btn-block mt-2" id="btn-scan">
                        <i class="fas fa-search"></i> เริ่มสแกน OCR
                    </button>
                    
                    <div id="loading" class="text-center mt-3" style="display:none;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p>กำลังอ่านข้อมูล...</p>
                    </div>

                    <div id="ocr-result-container" class="mt-3" style="display:none;">
                        <label>ผลการสแกน (คลิกเลือกคำที่เป็นหัวข้อ):</label>
                        <div id="ocr-tokens" class="p-3 border rounded bg-light" style="max-height: 400px; overflow-y: auto; line-height: 2.5;">
                            <!-- Tokens will be injected here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<style>
    .ocr-token {
        display: inline-block;
        padding: 2px 5px;
        margin: 2px;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
    }
    .ocr-token:hover {
        background-color: #e2e6ea;
        border-color: #adb5bd;
    }
    .ocr-token.selected {
        background-color: #007bff;
        color: #fff;
        border-color: #007bff;
    }
</style>

<?php
$scanUrl = Url::to(['ocr/process']);
$js = <<<JS
var currentField = 'regex-invoice-no';

// เมื่อ focus ที่ช่อง regex ให้จำไว้ว่ากำลังตั้งค่าช่องไหน
$('#regex-invoice-no, #regex-date, #regex-total').on('focus', function() {
    currentField = $(this).attr('id');
    $('.card-success').addClass('border-primary');
    $('.card-success .card-title').html('สูตรการดึงข้อมูล (กำลังแากเลิก: ' + $(this).prev('label').text() + ')');
});

$('#btn-scan').on('click', function() {
    var formData = new FormData();
    var fileInput = $('#ocr-input-file')[0];
    if (fileInput.files.length === 0) {
        alert('กรุณาเลือกไฟล์ก่อน');
        return;
    }
    
    formData.append('ocr_file', fileInput.files[0]);
    $('#btn-scan').hide();
    $('#loading').show();
    $('#ocr-result-container').hide();

    $.ajax({
        url: '{$scanUrl}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#loading').hide();
            $('#btn-scan').show();
            if (response.success) {
                renderTokens(response.fullText);
                $('#ocr-result-container').fadeIn();
                
                // ถ้าสแกนเจอ Tax ID ให้เอามาใส่ในช่องโดยอัตโนมัติ
                if (response.fullText) {
                    var taxMatch = response.fullText.match(/\d{13}/);
                    if (taxMatch && !$('#ocrpattern-tax_id').val()) {
                        $('#ocrpattern-tax_id').val(taxMatch[0]);
                    }
                }
            } else {
                alert('เกิดข้อผิดพลาด: ' + response.message);
            }
        },
        error: function() {
            $('#loading').hide();
            $('#btn-scan').show();
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }
    });
});

function renderTokens(text) {
    var container = $('#ocr-tokens');
    container.empty();
    
    // แยกเป็นคำๆ โดยยังคงภาษาไทยไว้
    var words = text.split(/(\s+)/);
    
    words.forEach(function(word) {
        if (word.trim().length > 0) {
            var span = $('<span class="ocr-token"></span>').text(word);
            span.on('click', function() {
                var selectedText = $(this).text();
                var regex = '';
                
                // สร้าง Regex อัตโนมัติจากคำที่เลือก
                if (currentField === 'regex-total') {
                    // สำหรับยอดรวม มักจะตามด้วยตัวเลขทศนิยม
                    regex = '/(?:' + escapeRegExp(selectedText) + ')\\\\s*[:.]?\\\\s*([0-9,]+\\\\.[0-9]{2})/iu';
                } else if (currentField === 'regex-invoice-no') {
                    // สำหรับเลขที่ มักจะตามด้วยตัวอักษรและตัวเลข
                    regex = '/(?:' + escapeRegExp(selectedText) + ')\\\\s*[:.]?\\\\s*([A-Z0-9\\\\-\\\\/]+)/iu';
                } else {
                    // ทั่วไป
                    regex = '/(?:' + escapeRegExp(selectedText) + ')\\\\s*[:.]?\\\\s*(.+)/iu';
                }
                
                $('#' + currentField).val(regex);
                
                // Visual feedback
                $('.ocr-token').removeClass('selected');
                $(this).addClass('selected');
            });
            container.append(span);
        } else {
            container.append(word); // whitespace
        }
    });
}

function escapeRegExp(string) {
    return string.replace(/[.*+?^\${}()|[\]\\\/]/g, '\\\\$&');
}
JS;
$this->registerJs($js);
?>
