<?php

use toxor88\switchery\Switchery;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Worker $model */
/** @var yii\widgets\ActiveForm $form */
?>

    <div class="worker-form">


        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-5">
                <?= $form->field($model, 'fnam')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-lg-5">
                <?= $form->field($model, 'lname')->textInput(['maxlength' => true]) ?>
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
            <div class="col-lg-3">
                <?= $form->field($model, 'idcard_no')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-lg-3">
                <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-lg-3">
                <?= $form->field($model, 'status')->widget(Switchery::className())->label(false) ?>
            </div>
            <div class="col-lg-1"></div>
        </div>
        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-10 mb-3">
                <div class="card border-primary shadow-sm">
                    <div class="card-header bg-primary text-white py-2">
                        <strong style="font-family: 'Prompt', sans-serif;"><i class="fas fa-passport me-2"></i> รายการเอกสารประกอบลูกจ้างและราชการ (ARICAT 9 รายการ)</strong>
                    </div>
                    <div class="card-body bg-light py-2">
                        <ol class="mb-0 small text-dark" style="columns: 2; -webkit-columns: 2; -moz-columns: 2; line-height: 1.8;">
                            <li>บันทึกข้อตกลง MOU ระหว่างประเทศ (ไทย-กัมพูชา/เมียนมา/ลาว/เวียดนาม)</li>
                            <li>หนังสืออนุญาตให้นำแรงงานต่างด้าวเข้ามาทำงาน (กรมจัดหางาน)</li>
                            <li>แบบ ตท.2 และ ตท.3 (เอกสารตอบกลับจากกรมแรงงาน)</li>
                            <li>ทะเบียนแรงงานเข้าเมือง (ตามระบบของรัฐ)</li>
                            <li>ใบตรวจโรคจากโรงพยาบาลที่กำหนด (แบบฟอร์ม ตม.1)</li>
                            <li>ใบอนุญาตทำงาน (Work Permit)</li>
                            <li>วีซ่าประเภท Non-LA (เพื่อทำงานในประเทศไทย)</li>
                            <li>หนังสือแจ้งการจัดส่งแรงงานจากรัฐต้นทาง</li>
                            <li>เอกสารการรับแรงงาน ณ จุดผ่านแดนที่ได้รับอนุญาต</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="col-lg-1"></div>
        </div>

        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-10">
                <input type="hidden" name="old_doc" value="<?= $model->doc ?>">
                <?= $form->field($model, 'doc[]')->fileInput(['maxlength' => true,'multiple' => true,])->label('แนบไฟล์เอกสารประกอบลูกจ้าง/ราชการ (สามารถเลือกได้หลายไฟล์พร้อมกัน)') ?>
            </div>
            <div class="col-lg-1"></div>
        </div>
        <div class="row">
            <div class="col-lg-1"></div>
            <div class="col-lg-8">
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
                                <td><?= $value->doc ?></td>
                                <td style="text-align: center">
                                    <a href="<?= Yii::$app->request->BaseUrl . '/uploads/aricat/' . $value->doc ?>"
                                       target="_blank">
                                        ดูเอกสาร
                                    </a>
                                </td>
                                <td style="text-align: center">
                                    <div class="btn btn-danger" data-var="<?= trim($value->doc) ?>" onclick="delete_doc($(this))">ลบ</div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
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
        <input type="hidden" class="delete-doc-list" name="doc_delete_list" value="">
        <?php ActiveForm::end(); ?>
    </div>

<?php
$script = <<< JS
var delete_doc_list = [];
    function delete_doc(e){
        var id = e.attr('data-var');
        delete_doc_list.push(id);
        if(id != null){
            e.closest('tr').remove();
        }
        console.log(delete_doc_list);
        $(".delete-doc-list").val(delete_doc_list);
    }
JS;
$this->registerJs($script, static::POS_END);
?>