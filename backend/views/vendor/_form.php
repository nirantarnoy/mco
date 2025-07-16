<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

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

$x_address = $address_chk == null ? '' : $address_chk->address;
$x_street = $address_chk == null ? '' : $address_chk->street;
$x_zipcode = $address_chk == null ? '' : $address_chk->zip_code;
?>

<div class="customer-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-10">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
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
        <div class="col-lg-5">
            <?= $form->field($model, 'taxid')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-5">
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
                    <div class="col-lg-3"><?= $form->field($model, 'province_name')->textInput(['maxlength' => true]) ?></div>
                </div>
                <div class="col-lg-1"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-10">
                <div class="row">
                    <div class="col-lg-3"> <?= $form->field($model, 'zipcode')->textInput(['maxlength' => true]) ?></div>
                </div>
                <div class="col-lg-1"></div>
            </div>
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

    </div>

    <?php
    $url_to_getcity = \yii\helpers\Url::to(['vendor/showcity'], true);
    $url_to_getdistrict = \yii\helpers\Url::to(['vendor/showdistrict'], true);
    $url_to_getzipcode = \yii\helpers\Url::to(['vendor/showzipcode'], true);
    $url_to_getAddress = \yii\helpers\Url::to(['vendor/showaddress'], true);


    $js = <<<JS
$(function () {
    
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
JS;

    $this->registerJs($js, static::POS_END);
    ?>

