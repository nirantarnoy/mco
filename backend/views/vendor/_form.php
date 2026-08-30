<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var backend\models\Customer $model */
/** @var yii\widgets\ActiveForm $form */

$district_data = \backend\models\District::find()->all();
$city_data = \backend\models\Amphur::find()->all();
$province_data = \backend\models\Province::find()->all();
$district_chk = \backend\models\AddressInfo::findDistrictId($model->id, 1); // 1 = vendor 2 = customer
$city_chk = \backend\models\AddressInfo::findAmphurId($model->id, 1);
$province_chk = \backend\models\AddressInfo::findProvinceId($model->id, 1);

$address_chk = \backend\models\AddressInfo::find()->where(['party_id' => $model->id, 'party_type_id' => 1])->one();
$model_doc = \common\models\VendorDoc::find()->where(['vendor_id' => $model->id])->all();

$x_address = $address_chk == null ? '' : $address_chk->address;
$x_street = $address_chk == null ? '' : $address_chk->street;
$x_zipcode = $address_chk == null ? '' : $address_chk->zip_code;

$new_code = '';
if($model->isNewRecord) {
    $new_code = $model::getLastno();
}else{
    $new_code = $model->code;
}
?>
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
<div class="customer-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-10">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true,'value' => $new_code,'readonly'=>'readonly']) ?>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-10">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-10">
            <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-4">
            <?= $form->field($model, 'taxid')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-3">
            <?= $form->field($model, 'is_vat')->dropDownList([
                '1' => 'คิด VAT',
                '2' => 'ไม่คิด VAT',
            ], ['prompt' => 'เลือกระบบ VAT']) ?>
        </div>
        <div class="col-lg-2">
            <div style="height: 35px"></div>
            <?= $form->field($model, 'status')->widget(\toxor88\switchery\Switchery::className())->label(false) ?>
        </div>
        <div class="col-lg-1"></div>
    </div>

    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-10">
            <div class="row">
                <div class="col-lg-3">
                    <div style="height: 35px"></div>
                    <?= $form->field($model, 'is_head')->widget(\toxor88\switchery\Switchery::className())->label(false) ?>
                </div>
                <div class="col-lg-3">
                    <?= $form->field($model, 'branch_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-lg-3">
                    <?= $form->field($model, 'contact_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-lg-3">
                    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </div>
        <div class="col-lg-1"></div>
    </div>


    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-10">
            <div class="row">
                <div class="col-lg-3">
                    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="col-lg-1"></div>
        </div>
        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-10">
                <div class="row">
                    <div class="col-lg-2">
                        <?= $form->field($model, 'home_number')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-lg-6">
                        <?= $form->field($model, 'street')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-lg-4">
                        <?= $form->field($model, 'aisle')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
                <div class="col-lg-1"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-10">
                <div class="row">
                    <div class="col-lg-3"> <?= $form->field($model, 'district_name')->textInput(['maxlength' => true]) ?></div>
                    <div class="col-lg-3"><?= $form->field($model, 'city_name')->textInput(['maxlength' => true]) ?></div>
                    <div class="col-lg-3"><?= $form->field($model, 'province_name')->textInput(['maxlength' => true]) ?></div>
                </div>
                <div class="col-lg-1"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-10">
                <div class="card card-outline card-info" style="border: 1px solid #17a2b8; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-top: 15px; margin-bottom: 20px;">
                    <div class="card-header" style="background-color: #e9f7f9; border-bottom: 1px solid #17a2b8; padding: 12px 20px;">
                        <h5 class="card-title" style="margin: 0; color: #117a8b; font-weight: bold;">
                            <i class="fas fa-university me-2"></i> ข้อมูลบัญชีธนาคาร (Bank Account Information)
                        </h5>
                    </div>
                    <div class="card-body" style="padding: 20px;">
                        <div class="row">
                            <div class="col-lg-4">
                                <?= $form->field($model, 'bank_name')->textInput(['maxlength' => true, 'id' => 'vendor-bank_name', 'placeholder' => 'เช่น กสิกรไทย, ไทยพาณิชย์']) ?>
                            </div>
                            <div class="col-lg-4">
                                <?= $form->field($model, 'account_name')->textInput(['maxlength' => true, 'id' => 'vendor-account_name', 'placeholder' => 'ชื่อบัญชี']) ?>
                            </div>
                            <div class="col-lg-4">
                                <?= $form->field($model, 'account_num')->textInput(['maxlength' => true, 'id' => 'vendor-account_num', 'placeholder' => 'เลขที่บัญชีธนาคาร']) ?>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="col-lg-7">
                                <?= $form->field($model, 'bank_account_file')->fileInput(['id' => 'bank-account-file-input', 'accept' => 'image/*'])->label('แนบรูปภาพหน้าบัญชีธนาคาร (สมุดบัญชี/Passbook)') ?>
                                <?php if (!empty($model->bank_account_file)): ?>
                                    <div class="mt-1">
                                        <span class="text-muted"><i class="fas fa-paperclip"></i> ไฟล์ปัจจุบัน: </span>
                                        <a href="<?= Yii::$app->request->baseUrl . '/uploads/vendor_doc/' . $model->bank_account_file ?>" target="_blank" class="btn btn-sm btn-outline-info ms-1">
                                            <i class="fas fa-eye"></i> ดูหน้าบัญชีที่แนบไว้
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-lg-5 align-self-center text-lg-end mt-2 mt-lg-0">
                                <button type="button" class="btn btn-info text-white" id="btn-scan-bank-book" style="font-weight: 500;">
                                    <i class="fas fa-robot me-1"></i> สแกนด้วย Gemini AI
                                </button>
                                <div id="scan-loading" style="display: none;" class="mt-2 text-info">
                                    <i class="fas fa-spinner fa-spin me-1"></i> กำลังอ่านข้อมูลด้วย Gemini AI...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-1"></div>
        </div>

        <div class="row" style="display: none;">
            <div class="col-lg-6">
                <br/>
                <div class="row">
                    <div class="co-lg-6" style="text-align: center;">
                        <label for="">ที่อยู่ผู้ขาย</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <label for="">ที่อยู่</label>
                        <input type="text" class="form-control cus-address" id="cus-address"
                               value="<?= $model->isNewRecord ? '' : $x_address ?>" name="cus_address">
                    </div>

                </div>
                <div class="row">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <label for="">ถนน</label>
                        <input type="text" class="form-control cus-street" id="cus-street"
                               value="<?= $model->isNewRecord ? '' : $x_street ?>" name="cus_street">
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <label for="">ตำบล/แขวง</label>
                        <select name="district_id" class="form-control district-id" id="district"
                                onchange="">
                            <option value="0">--ตำบล/แขวง--</option>
                            <?php foreach ($district_data as $val): ?>
                                <?php
                                $selected = '';
                                if ($val->DISTRICT_ID == $district_chk)
                                    $selected = 'selected';
//                    ?>
                                <option value="<?= $val->DISTRICT_ID ?>" <?= $selected ?>><?= $val->DISTRICT_NAME ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <label for="">อำเภอ/เขต</label>
                        <select name="city_id" class="form-control city-id" id="city"
                                onchange="getDistrict($(this))">
                            <option value="0">--อำเภอ/เขต--</option>
                            <?php foreach ($city_data as $val2): ?>
                                <?php
                                $selected = '';
                                if ($val2->AMPHUR_ID == $city_chk)
                                    $selected = 'selected';
//                    ?>
                                <option value="<?= $val2->AMPHUR_ID ?>" <?= $selected ?>><?= $val2->AMPHUR_NAME ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <label for="">จังหวัด</label>
                        <select name="province_id" class="form-control province-id" id=""
                                onchange="getCity($(this))">
                            <option value="0">--จังหวัด--</option>
                            <?php foreach ($province_data as $val3): ?>
                                <?php
                                $selected = '';
                                if ($val3->PROVINCE_ID == $province_chk)
                                    $selected = 'selected';
//                    ?>
                                <option value="<?= $val3->PROVINCE_ID ?>" <?= $selected ?>><?= $val3->PROVINCE_NAME ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-8">
                        <label for="">รหัสไปรษณีย์</label>
                        <input type="text" class="form-control zipcode" id="zipcode"
                               value="<?= $model->isNewRecord ? '' : $x_zipcode ?>" name="zipcode" readonly>
                    </div>
                </div>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-10">
                <div class="form-group">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                </div>

            </div>
            <div class="col-lg-1"></div>
        </div>

        <?php ActiveForm::end(); ?>
        
        <hr>
        <br/>
        <div class="label">
            <h4>เอกสารแนบ</h4>
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
                    <?php if ($model_doc != null): ?>

                        <?php foreach ($model_doc as $key => $value): ?>
                            <tr>
                                <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                                <td><?= $value->doc_name ?></td>
                                <td style="text-align: center">
                                    <a href="<?= Yii::$app->request->BaseUrl . '/uploads/vendor_doc/' . $value->doc_name ?>"
                                       target="_blank">
                                        ดูเอกสาร
                                    </a>
                                </td>
                                <td style="text-align: center">
                                    <div class="btn btn-danger" data-var="<?= trim($value->doc_name) ?>"
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
        <?php if (!$model->isNewRecord): ?>
            <form action="<?= Url::to(['vendor/add-doc-file'], true) ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $model->id ?>">
                <div style="padding: 10px;background-color: lightgrey;border-radius: 5px">
                    <div class="row">
                        <div class="col-lg-12">
                            <label for="">เอกสารแนบ</label>
                            <input type="file" name="file_doc[]" multiple>
                        </div>
                    </div>
                    <br/>
                    <div class="row">
                        <div class="col-lg-12">
                            <button class="btn btn-info">
                                <i class="fas fa-upload"></i> อัพโหลดเอกสารแนบ
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
        <form id="form-delete-doc-file" action="<?= Url::to(['vendor/delete-doc-file'], true) ?>" method="post">
            <input type="hidden" name="id" value="<?= $model->id ?>">
            <input type="hidden" class="delete-doc-list" name="doc_delete_list" value="">
        </form>

    </div>

    <?php
    $url_to_getcity = \yii\helpers\Url::to(['vendor/showcity'], true);
    $url_to_getdistrict = \yii\helpers\Url::to(['vendor/showdistrict'], true);
    $url_to_getzipcode = \yii\helpers\Url::to(['vendor/showzipcode'], true);
    $url_to_getAddress = \yii\helpers\Url::to(['vendor/showaddress'], true);
    $url_scan_bank_book = \yii\helpers\Url::to(['vendor/scan-bank-book'], true);


    $js = <<<JS
$(function () {
    $('#btn-scan-bank-book').on('click', function() {
        var fileInput = $('#bank-account-file-input')[0];
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            alert('กรุณาแนบหรือเลือกไฟล์รูปภาพหน้าบัญชีธนาคารก่อนทำการสแกน');
            return;
        }

        var formData = new FormData();
        formData.append('bank_book_file', fileInput.files[0]);

        $('#btn-scan-bank-book').prop('disabled', true);
        $('#scan-loading').show();

        $.ajax({
            url: "{$url_scan_bank_book}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                $('#btn-scan-bank-book').prop('disabled', false);
                $('#scan-loading').hide();

                if (res.success) {
                    if (res.account_number) {
                        $('#vendor-account_num').val(res.account_number);
                    }
                    if (res.bank_name) {
                        $('#vendor-bank_name').val(res.bank_name);
                    }
                    if (res.account_name) {
                        $('#vendor-account_name').val(res.account_name);
                    }
                    var msg = 'อ่านข้อมูลสำเร็จด้วย Gemini AI!\\nเลขที่บัญชี: ' + (res.account_number || '-') + '\\nธนาคาร: ' + (res.bank_name || '-') + '\\nชื่อบัญชี: ' + (res.account_name || '-');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'สแกนสำเร็จด้วย Gemini AI',
                            html: '<b>เลขที่บัญชี:</b> ' + (res.account_number || '-') + '<br>' +
                                  '<b>ธนาคาร:</b> ' + (res.bank_name || '-') + '<br>' +
                                  '<b>ชื่อบัญชี:</b> ' + (res.account_name || '-'),
                            confirmButtonText: 'ตกลง'
                        });
                    } else {
                        alert(msg);
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: res.message || 'ไม่สามารถประมวลผลได้'
                        });
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + (res.message || 'ไม่สามารถประมวลผลได้'));
                    }
                }
            },
            error: function(xhr, status, error) {
                $('#btn-scan-bank-book').prop('disabled', false);
                $('#scan-loading').hide();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาดในการเชื่อมต่อ',
                        text: error || 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
                    });
                } else {
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + error);
                }
            }
        });
    });
});
function getCity(e){
    $.post("$url_to_getcity"+"&id="+e.val(),function(data){
        $("select#city").html(data);
        $("select#city").prop("disabled","");
    });
}

function getDistrict(e){
    $.post("$url_to_getdistrict"+"&id="+e.val(),function(data){
                                          $("select#district").html(data);
                                          $("select#district").prop("disabled","");

                                        });
                                           $.post("$url_to_getzipcode"+"&id="+e.val(),function(data){
                                                $("#zipcode").val(data);
                                              });
}

function getAddres(e){
    $.post("$url_to_getAddress"+"&id="+e.val(),function(data){
        $("#city").html(data);
        $("select#city").prop("disabled","");
    });
}
function delete_doc(e){
    var file_name = e.attr('data-var');
    if(file_name != null){
        $(".delete-doc-list").val(file_name);
        $("#form-delete-doc-file").submit();
    }
}
JS;

    $this->registerJs($js, static::POS_END);
    ?>

