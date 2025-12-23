<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\Invoice */

$this->title = 'สร้างใบเสร็จรับเงิน';
?>

<div class="invoice-create-receipt">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-invoice"></i> สร้างใบเสร็จรับเงิน</h5>
            </div>
            
            <?php $form = ActiveForm::begin(['id' => 'receipt-form']); ?>
            
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'invoice_number')->textInput([
                            'maxlength' => true, 
                            'placeholder' => 'เว้นว่างเพื่อสร้างอัตโนมัติ'
                        ])->label('เลขที่ใบเสร็จรับเงิน') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'invoice_date')->widget(DatePicker::class, [
                            'options' => ['placeholder' => 'เลือกวันที่'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ]
                        ]) ?>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> รายการสินค้าและข้อมูลอื่นๆ จะถูกคัดลอกมาจากต้นฉบับโดยอัตโนมัติ
                </div>
            </div>
            
            <div class="modal-footer">
                <?= Html::a('ยกเลิก', ['view', 'id' => Yii::$app->request->get('copy_from')], ['class' => 'btn btn-secondary']) ?>
                <?= Html::submitButton('<i class="fas fa-save"></i> บันทึก', ['class' => 'btn btn-success']) ?>
            </div>
            
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
