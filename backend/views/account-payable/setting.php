<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'ตั้งค่า SLA สำหรับฝ่ายบัญชี';
$this->params['breadcrumbs'][] = ['label' => 'รายการรอตั้งหนี้', 'url' => ['worklist']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="account-payable-setting">
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cog"></i> <?= Html::encode($this->title) ?></h3>
                </div>
                
                <?php $form = ActiveForm::begin(); ?>
                <div class="card-body">
                    <p class="text-muted">กำหนดจำนวนวันทำงาน (SLA) ที่ฝ่ายบัญชีต้องดำเนินการเปิดบิลตั้งหนี้ (PV) หลังจากที่ได้รับสินค้าเรียบร้อยแล้ว ถ้ารายการใดเลยกำหนดเวลาที่ตั้งไว้ ระบบจะแสดงแถบแจ้งเตือนสีแดงในหน้า Worklist</p>

                    <div class="form-group">
                        <label class="control-label" for="ap_sla_days"><?= $slaDays->description ?></label>
                        <div class="input-group">
                            <?= Html::textInput('ap_sla_days', $slaDays->setting_value, [
                                'class' => 'form-control',
                                'id' => 'ap_sla_days',
                                'type' => 'number',
                                'min' => 1,
                                'max' => 365,
                                'required' => true
                            ]) ?>
                            <div class="input-group-append">
                                <span class="input-group-text">วัน</span>
                            </div>
                        </div>
                        <div class="help-block text-muted mt-1">ตัวอย่าง: ใส่ 3 หมายความว่าต้องจัดการภายใน 3 วันหลังจากรับสินค้า</div>
                    </div>
                </div>

                <div class="card-footer">
                    <?= Html::submitButton('<i class="fas fa-save"></i> บันทึกการตั้งค่า', ['class' => 'btn btn-success']) ?>
                    <?= Html::a('ยกเลิก', ['worklist'], ['class' => 'btn btn-default float-right']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
