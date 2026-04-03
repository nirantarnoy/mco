<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\file\FileInput;

$this->title = 'อ่านไฟล์ OCR ด้วย Google Vision';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="ocr-index">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title"><i class="fas fa-file-invoice"></i> <?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <p class="text-muted">เลือกไฟล์รูปภาพ (JPG, PNG) หรือไฟล์ PDF เพื่ออ่านข้อความด้วยระบบ OCR จาก Google Vision</p>

            <div class="row">
                <div class="col-md-12">
                    <?= FileInput::widget([
                        'name' => 'ocr_file',
                        'options' => [
                            'accept' => 'image/*,application/pdf',
                            'id' => 'ocr-input-file'
                        ],
                        'pluginOptions' => [
                            'showPreview' => true,
                            'showCaption' => true,
                            'showRemove' => true,
                            'showUpload' => false,
                            'browseClass' => 'btn btn-primary btn-block',
                            'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                            'browseLabel' =>  'เลือกรูปภาพ'
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <button type="button" class="btn btn-lg btn-success px-5" id="btn-scan" disabled>
                        <i class="fas fa-search"></i> เริ่มการสแกน OCR
                    </button>
                    <div id="loading" class="mt-2" style="display:none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">กำลังประมวลผล...</span>
                        </div>
                        <p>กำลังส่งไฟล์ไปยัง Google Vision และประมวลผล...</p>
                    </div>
                </div>
            </div>

            <div id="result-area" class="mt-5" style="display:none;">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h5 class="card-title text-success">ผลการสแกน OCR</h5>
                        <div class="card-tools">
                             <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="full-text">ข้อความที่อ่านได้ทั้งหมด:</label>
                            <textarea id="full-text" class="form-control" rows="15" spellcheck="false" style="font-family: 'Courier New', Courier, monospace; font-size: 14px; background-color: #f8f9fa; border-radius: 8px;"></textarea>
                        </div>
                        <div class="mt-3 text-right">
                             <button class="btn btn-secondary btn-sm" id="btn-copy">
                                <i class="fas fa-copy"></i> คัดลอกข้อความ
                             </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .ocr-index .card {
        border-radius: 15px;
        overflow: hidden;
    }
    #full-text {
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    #full-text:focus {
        background-color: #fff !important;
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .spinner-border {
        width: 3rem;
        height: 3rem;
    }
</style>

<?php
$scanUrl = Url::to(['ocr/process']);
$js = <<<JS
$('#ocr-input-file').on('change', function() {
    if ($(this).val()) {
        $('#btn-scan').prop('disabled', false);
    } else {
        $('#btn-scan').prop('disabled', true);
    }
});

$('#btn-scan').on('click', function() {
    var formData = new FormData();
    var fileInput = $('#ocr-input-file')[0];
    
    if (fileInput.files.length === 0) return;
    
    formData.append('ocr_file', fileInput.files[0]);
    
    // UI Loading state
    $('#btn-scan').hide();
    $('#loading').show();
    $('#result-area').fadeOut();

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
                $('#full-text').val(response.fullText);
                $('#result-area').fadeIn();
                
                // Use SweetAlert if available
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สแกนสำเร็จ',
                        text: 'ระบบดึงข้อมูลจากรูปภาพเรียบร้อยแล้ว'
                    });
                } else {
                    alert('สแกนสำเร็จ!');
                }
            } else {
                alert('เกิดข้อผิดพลาด: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            $('#loading').hide();
            $('#btn-scan').show();
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + error);
        }
    });
});

$('#btn-copy').on('click', function() {
    var copyText = document.getElementById("full-text");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    
    if (window.Swal) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'คัดลอกรูปภาพแล้ว',
            showConfirmButton: false,
            timer: 1500
        });
    } else {
        alert('คัดลอกแล้ว');
    }
});
JS;

$this->registerJs($js);
?>
