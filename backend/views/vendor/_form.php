<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var backend\models\Customer $model */
/** @var yii\widgets\ActiveForm $form */

$district_chk = \backend\models\AddressInfo::findDistrictId($model->id, 1); // 1 = vendor 2 = customer
$city_chk = \backend\models\AddressInfo::findAmphurId($model->id, 1);
$province_chk = \backend\models\AddressInfo::findProvinceId($model->id, 1);

// ถ้าใน AddressInfo ไม่มี ID แต่โมเดลมีชื่อ ให้ค้นหา ID ย้อนกลับ
if (empty($province_chk) && !empty($model->province_name)) {
    $p_find = \backend\models\Province::find()->where(['like', 'PROVINCE_NAME', trim($model->province_name)])->one();
    if ($p_find) $province_chk = $p_find->PROVINCE_ID;
}
if (empty($city_chk) && !empty($model->city_name)) {
    $c_find = \backend\models\Amphur::find()->where(['like', 'AMPHUR_NAME', trim($model->city_name)])->one();
    if ($c_find) $city_chk = $c_find->AMPHUR_ID;
}
if (empty($district_chk) && !empty($model->district_name)) {
    $d_find = \backend\models\District::find()->where(['like', 'DISTRICT_NAME', trim($model->district_name)])->one();
    if ($d_find) $district_chk = $d_find->DISTRICT_ID;
}

$province_data = \backend\models\Province::find()->orderBy(['PROVINCE_NAME' => SORT_ASC])->all();
$city_data = !empty($province_chk) ? \backend\models\Amphur::find()->where(['PROVINCE_ID' => $province_chk])->orderBy(['AMPHUR_NAME' => SORT_ASC])->all() : [];
$district_data = !empty($city_chk) ? \backend\models\District::find()->where(['AMPHUR_ID' => $city_chk])->orderBy(['DISTRICT_NAME' => SORT_ASC])->all() : [];

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
        </div>
        <div class="col-lg-1"></div>
    </div>

        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-10">
                <div class="card card-outline card-primary" style="border: 1px solid #007bff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-top: 5px; margin-bottom: 20px;">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #eaf4ff; border-bottom: 1px solid #007bff; padding: 12px 20px;">
                        <h5 class="card-title mb-0" style="color: #0056b3; font-weight: bold;">
                            <i class="fas fa-map-marker-alt me-2"></i> ข้อมูลที่อยู่ผู้ขาย (Address Information)
                        </h5>
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i> เลือกจังหวัด / อำเภอ / ตำบล</small>
                    </div>
                    <div class="card-body" style="padding: 20px;">


                        <!-- ซ่อน input เดิมของ Model เพื่อ sync ค่า -->
                        <input type="hidden" name="district_id" id="district-id-hidden" value="<?= $district_chk ?>">
                        <input type="hidden" name="city_id" id="city-id-hidden" value="<?= $city_chk ?>">
                        <input type="hidden" name="province_id" id="province-id-hidden" value="<?= $province_chk ?>">
                        <input type="hidden" name="cus_address" id="cus-address-hidden" value="<?= Html::encode($model->home_number ?: $x_address) ?>">
                        <input type="hidden" name="cus_street" id="cus-street-hidden" value="<?= Html::encode($model->street ?: $x_street) ?>">

                        <!-- แถวที่ 1: บ้านเลขที่, ถนน, ซอย -->
                        <div class="row">
                            <div class="col-lg-3">
                                <?= $form->field($model, 'home_number')->textInput(['maxlength' => true, 'id' => 'vendor-home_number', 'placeholder' => 'เช่น 123/45']) ?>
                            </div>
                            <div class="col-lg-5">
                                <?= $form->field($model, 'street')->textInput(['maxlength' => true, 'id' => 'vendor-street', 'placeholder' => 'เช่น สุขุมวิท']) ?>
                            </div>
                            <div class="col-lg-4">
                                <?= $form->field($model, 'aisle')->textInput(['maxlength' => true, 'id' => 'vendor-aisle', 'placeholder' => 'เช่น ซอย 5']) ?>
                            </div>
                        </div>

                        <!-- แถวที่ 2: Cascade Dropdown (จังหวัด -> อำเภอ -> ตำบล -> รหัสไปรษณีย์) -->
                        <div class="row mt-2">
                            <div class="col-lg-3">
                                <label class="form-label" style="font-weight: 500;">จังหวัด <span class="text-danger">*</span></label>
                                <select id="select-province" class="form-control form-select">
                                    <option value="">-- เลือกจังหวัด --</option>
                                    <?php foreach ($province_data as $val3): ?>
                                        <?php
                                        $selected = ($val3->PROVINCE_ID == $province_chk || trim($val3->PROVINCE_NAME) === trim($model->province_name)) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $val3->PROVINCE_ID ?>" <?= $selected ?>><?= trim($val3->PROVINCE_NAME) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?= $form->field($model, 'province_name')->hiddenInput(['id' => 'vendor-province_name'])->label(false) ?>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label" style="font-weight: 500;">อำเภอ/เขต <span class="text-danger">*</span></label>
                                <select id="select-city" class="form-control form-select" <?= empty($city_chk) && empty($model->city_name) ? 'disabled' : '' ?>>
                                    <option value="">-- เลือกอำเภอ/เขต --</option>
                                    <?php if (!empty($city_data)): ?>
                                        <?php foreach ($city_data as $val2): ?>
                                            <?php
                                            $selected = ($val2->AMPHUR_ID == $city_chk || trim($val2->AMPHUR_NAME) === trim($model->city_name)) ? 'selected' : '';
                                            ?>
                                            <option value="<?= $val2->AMPHUR_ID ?>" data-postcode="<?= trim($val2->POSTCODE) ?>" <?= $selected ?>><?= trim($val2->AMPHUR_NAME) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?= $form->field($model, 'city_name')->hiddenInput(['id' => 'vendor-city_name'])->label(false) ?>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label" style="font-weight: 500;">ตำบล/แขวง <span class="text-danger">*</span></label>
                                <select id="select-district" class="form-control form-select" <?= empty($district_chk) && empty($model->district_name) ? 'disabled' : '' ?>>
                                    <option value="">-- เลือกตำบล/แขวง --</option>
                                    <?php if (!empty($district_data)): ?>
                                        <?php foreach ($district_data as $val): ?>
                                            <?php
                                            $selected = ($val->DISTRICT_ID == $district_chk || trim($val->DISTRICT_NAME) === trim($model->district_name)) ? 'selected' : '';
                                            ?>
                                            <option value="<?= $val->DISTRICT_ID ?>" data-zipcode="<?= trim($val->ZIPCODE) ?>" <?= $selected ?>><?= trim($val->DISTRICT_NAME) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?= $form->field($model, 'district_name')->hiddenInput(['id' => 'vendor-district_name'])->label(false) ?>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label" style="font-weight: 500;">รหัสไปรษณีย์ <span class="text-danger">*</span></label>
                                <?= $form->field($model, 'zipcode')->textInput(['maxlength' => true, 'id' => 'vendor-zipcode', 'placeholder' => 'รหัสไปรษณีย์'])->label(false) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-1"></div>
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
                                       target="_blank" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-search"></i> ดูเอกสาร
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
    $url_lookup_zipcode = \yii\helpers\Url::to(['vendor/lookup-zipcode'], true);
    $url_scan_bank_book = \yii\helpers\Url::to(['vendor/scan-bank-book'], true);

    $js = <<<JS
$(function () {
    // 1. จัดการเมื่อเลือกจังหวัด (Cascade Dropdown)
    $('#select-province').on('change', function() {
        var provId = $(this).val();
        var provName = $(this).find('option:selected').text();
        if (provId) {
            $('#vendor-province_name').val(provName.trim());
            $('#province-id-hidden').val(provId);
        } else {
            $('#vendor-province_name').val('');
            $('#province-id-hidden').val('');
        }

        // รีเซ็ตอำเภอ ตำบล รหัสไปรษณีย์
        $('#select-city').html('<option value="">-- กำลังโหลดอำเภอ/เขต... --</option>').prop('disabled', true);
        $('#select-district').html('<option value="">-- เลือกตำบล/แขวง --</option>').prop('disabled', true);
        $('#vendor-city_name').val('');
        $('#city-id-hidden').val('');
        $('#vendor-district_name').val('');
        $('#district-id-hidden').val('');
        $('#vendor-zipcode').val('');

        if (provId) {
            $.get("{$url_to_getcity}", { id: provId, format: 'json' }, function(data) {
                var html = '<option value="">-- เลือกอำเภอ/เขต --</option>';
                if (data && data.length > 0) {
                    $.each(data, function(i, item) {
                        html += '<option value="' + item.id + '" data-postcode="' + (item.postcode || '') + '">' + item.name + '</option>';
                    });
                    $('#select-city').html(html).prop('disabled', false);
                } else {
                    $('#select-city').html('<option value="">- ไม่พบข้อมูลอำเภอ -</option>');
                }
            }, 'json');
        } else {
            $('#select-city').html('<option value="">-- เลือกอำเภอ/เขต --</option>');
        }
    });

    // 2. จัดการเมื่อเลือกอำเภอ (Cascade Dropdown)
    $('#select-city').on('change', function() {
        var cityId = $(this).val();
        var cityName = $(this).find('option:selected').text();
        var postcode = $(this).find('option:selected').data('postcode') || '';

        if (cityId) {
            $('#vendor-city_name').val(cityName.trim());
            $('#city-id-hidden').val(cityId);
        } else {
            $('#vendor-city_name').val('');
            $('#city-id-hidden').val('');
        }

        $('#select-district').html('<option value="">-- กำลังโหลดตำบล/แขวง... --</option>').prop('disabled', true);
        $('#vendor-district_name').val('');
        $('#district-id-hidden').val('');
        if (postcode) {
            $('#vendor-zipcode').val(postcode);
        }

        if (cityId) {
            $.get("{$url_to_getdistrict}", { id: cityId, format: 'json' }, function(data) {
                var html = '<option value="">-- เลือกตำบล/แขวง --</option>';
                if (data && data.length > 0) {
                    $.each(data, function(i, item) {
                        html += '<option value="' + item.id + '" data-zipcode="' + (item.zipcode || '') + '">' + item.name + '</option>';
                    });
                    $('#select-district').html(html).prop('disabled', false);
                } else {
                    $('#select-district').html('<option value="">- ไม่พบข้อมูลตำบล -</option>');
                }
            }, 'json');
        } else {
            $('#select-district').html('<option value="">-- เลือกตำบล/แขวง --</option>');
        }
    });

    // 3. จัดการเมื่อเลือกตำบล
    $('#select-district').on('change', function() {
        var distId = $(this).val();
        var distName = $(this).find('option:selected').text();
        var zipcode = $(this).find('option:selected').data('zipcode') || '';

        if (distId) {
            $('#vendor-district_name').val(distName.trim());
            $('#district-id-hidden').val(distId);
            if (zipcode) {
                $('#vendor-zipcode').val(zipcode);
            }
        } else {
            $('#vendor-district_name').val('');
            $('#district-id-hidden').val('');
        }
    });

    // 4. สแกนสมุดบัญชีด้วย AI
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

