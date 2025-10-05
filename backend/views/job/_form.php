<?php

use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Job $model */
/** @var yii\widgets\ActiveForm $form */

$model_purch_job = \backend\models\Purch::find()->where(['job_id' => $model->id])->all();
?>

    <div class="job-form">

        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
        <input type="hidden" class="removelist" name="removelist" value="">
        <div class="row">
            <div class="col-lg-3">
                <?= $form->field($model, 'job_no')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-lg-3">
                <?= $form->field($model, 'quotation_id')->widget(Select2::className(),
                    [
                        'data' => \yii\helpers\ArrayHelper::map(\backend\models\Quotation::find()->all(), 'id', 'quotation_no'),
                        'language' => 'th',
                        'options' => ['placeholder' => 'เลือกใบเสนอราคา', 'onchange' => 'getCustomerinfo($(this))'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ]
                    ])->label('เลขที่ใบเสนอราคา') ?>
            </div>
            <div class="col-lg-3">
                <label for="">ลูกค้า</label>
                <input type="text" class="form-control customer-name" name="customer_name" readonly>
            </div>
            <div class="col-lg-3">
                <?php $model->job_date = $model->job_date ? date('m/d/Y', strtotime($model->job_date)) : ''; ?>
                <?= $form->field($model, 'job_date')->widget(DatePicker::className(),
                    ['options' =>
                        [
                            'placeholder' => 'เลือกวันที่',
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'mm/dd/yyyy',
                        ]
                    ])->label('วันที่') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <?php $model->start_date = $model->start_date ? date('m/d/Y', strtotime($model->start_date)) : ''; ?>
                <?= $form->field($model, 'start_date')->widget(DatePicker::className(),
                    ['options' =>
                        [
                            'placeholder' => 'เลือกวันที่',
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'mm/dd/yyyy',
                        ]
                    ])->label() ?>
            </div>
            <div class="col-lg-3">
                <?php $model->end_date = $model->end_date ? date('m/d/Y', strtotime($model->end_date)) : ''; ?>
                <?= $form->field($model, 'end_date')->widget(DatePicker::className(),
                    ['options' =>
                        [
                            'placeholder' => 'เลือกวันที่',
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'mm/dd/yyyy',
                        ]
                    ])->label() ?></div>
            <div class="col-lg-3">
                <?= $form->field($model, 'cus_po_no')->textInput() ?>
            </div>
            <div class="col-lg-3">
                <?php $model->cus_po_date = $model->cus_po_date ? date('m/d/Y', strtotime($model->cus_po_date)) : ''; ?>
                <?= $form->field($model, 'cus_po_date')->widget(DatePicker::className(),
                    ['options' =>
                        [
                            'placeholder' => 'เลือกวันที่',
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'mm/dd/yyyy',
                        ]
                    ])->label() ?></div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <label for="">สถานะ</label>
                <?= \backend\models\Job::getJobStatusBadge($model->status) ?>
            </div>
            <div class="col-lg-3">
                <?= $form->field($model, 'job_amount')->textInput() ?>
            </div>
            <div class="col-lg-3">
                <?= $form->field($model, 'jsa_doc')->fileInput() ?>

                <?php if ($model->jsa_doc): ?>
                    <div class="alert alert-info">
                        <strong>ไฟล์ปัจจุบัน:</strong><br>

                        <?php
                        // สร้าง URL ของไฟล์ (ปรับ path ให้ตรงกับของโปรเจกต์คุณ)
                        $fileUrl = Yii::getAlias('@web/uploads/job/' . $model->jsa_doc);
                        $ext = pathinfo($model->jsa_doc, PATHINFO_EXTENSION);
                        ?>

                        <!-- เปิดไฟล์ในแท็บใหม่ -->
                        <?= Html::a('📂 เปิดไฟล์', $fileUrl, [
                            'class' => 'btn btn-sm btn-outline-primary mt-2',
                            'target' => '_blank',
                            'data-pjax' => '0'
                        ]) ?>

                        <!-- ปุ่มลบไฟล์ -->
                        <?= Html::a('🗑️ ลบไฟล์', ['delete-file', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-danger mt-2',
                            'data' => [
                                'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์นี้?',
                                'method' => 'post',
                            ],
                        ]) ?>

                        <!-- แสดงภาพตัวอย่างถ้าเป็นไฟล์รูป -->
<!--                        --><?php //if (in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp'])): ?>
<!--                            <div class="mt-2">-->
<!--                                <img src="--><?php //= $fileUrl ?><!--" alt="preview" style="max-width: 100%; height: auto; border: 1px solid #ccc; padding: 3px;">-->
<!--                            </div>-->
<!--                        --><?php //endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-3">
                <?= $form->field($model, 'report_doc')->fileInput() ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($model, 'summary_note')->textarea() ?>
            </div>
        </div>

        <?= $form->field($model, 'status')->hiddenInput()->label(false) ?>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        </div>
        <br/>
        <div class="row">
            <div class="col-lg-12">
                <label for="">รายละเอียดสินค้า</label>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 5%;text-align: center">#</th>
                        <th style="width: 20%">รายการ</th>
                        <th style="width: 15%">จํานวน</th>
                        <th style="width: 10%">หน่วย</th>
                        <th style="width: 10%">ราคาต่อหน่วย</th>
                        <th style="width: 10%">ราคารวม</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($model_line != null): ?>
                        <?php foreach ($model_line as $key => $line): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td><?= \backend\models\Product::findName($line->product_id) ?></td>
                                <td><?= $line->qty ?></td>
                                <td><?= \backend\models\Product::findUnitName($line->product_id) ?></td>
                                <td><?= $line->line_price ?></td>
                                <td><?= $line->line_total ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="col-lg-12">
                <label for="">รายชื่อผู้ติดต่อ</label>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-bordered table-striped" id="table-list">
                    <thead>
                    <tr>
                        <th style="width: 5%;text-align: center;">#</th>
                        <th style="width: 30%">ชื่อผู้ติดต่อ</th>
                        <th>เบอร์โทร</th>
                        <th>Email</th>
                        <th style="width:5%;text-align: center;">-</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($model_contact != null): ?>
                        <?php foreach ($model_contact as $inx => $value): ?>
                            <tr data-val="<?=$value->id?>">
                                <td style="text-align: center;">
                                    <input type="hidden" class="rec-id" name="rec_id[]" value="<?=$value->id?>">
                                    <input type="text" class="form-control" value="<?=($inx +1)?>" readonly>
                                </td>
                                <td>
                                    <input type="text" class="form-control line-name" name="line_name[]" value="<?=$value->name?>" >
                                </td>
                                <td>
                                    <input type="text" class="form-control line-phone" name="line_phone[]" value="<?=$value->phone?>" >
                                </td>
                                <td>
                                    <input type="text" class="form-control line-email" name="line_email[]" value="<?=$value->email?>" >
                                </td>
                                <td style="text-align: center;"><div class="btn btn-sm btn-danger" onclick="removeLine($(this))">ลบ</div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr data-val="">
                            <td style="text-align: center;">
                                <input type="text" class="form-control" value="" readonly>
                            </td>
                            <td>
                                <input type="hidden" class="rec-id" name="rec_id[]" value="">
                                <input type="text" class="form-control line-name" name="line_name[]" value="">
                            </td>
                            <td>
                                <input type="text" class="form-control line-phone" name="line_phone[]" value="">
                            </td>
                            <td>
                                <input type="text" class="form-control line-email" name="line_email[]" value="">
                            </td>
                            <td style="text-align: center;">
                                <div class="btn btn-sm btn-danger" onclick="removeLine($(this))">ลบ</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td>
                            <div class="btn btn-sm btn-primary" onclick="addline()">เพิ่มรายการ</div>
                        </td>
                        <td colspan="4"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <br/>
        <div class="row">
            <div class="col-lg-12">
                <label for="">รายการสั่งซื้อสำหรับใบงานนี้</label>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 5%;text-align: center">#</th>
                        <th style="width: 20%">เลขที่ใบสั่งซื้อ</th>
                        <th style="width: 15%">วันที่</th>
                        <th style="width: 10%">สถานะ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($model_purch_job != null && !$model->isNewRecord): ?>
                        <?php foreach ($model_purch_job as $key => $purch): ?>
                            <?php
                            $puch_x = new \backend\models\Purch();
                            ?>
                            <tr>
                                <td style="text-align: center"><?= $key + 1 ?></td>
                                <td>
                                    <a href="<?= \yii\helpers\Url::to(['purch/view', 'id' => $purch->id]) ?>"><?= $purch->purch_no ?></a>
                                </td>
                                <td><?= $purch->purch_date ? date('m/d/Y', strtotime($purch->purch_date)) : '' ?></td>
                                <td><?= $puch_x->getApproveStatusBadge($purch->status) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td style="text-align: center"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$url_to_getcustomerinfo = \yii\helpers\Url::to(['job/getcustomerinfo'], true);
$js = <<<JS
var removelist = [];
$(function(){
    
});
function getCustomerinfo(e){
    var id = e.val();
    if(id){
        $.ajax({
        url: '$url_to_getcustomerinfo',
        type: 'POST',
        data: {'id':id},
        dataType: 'html',
        success: function(data) {
           $(".customer-name").val(data);
        },
        error: function() {
            console.log('Error loading customer info data');
        }
    });
    }
    
}

function addline(){
    var lastRow = $("#table-list tbody tr:last");
    var input = lastRow.find('.line-name');

    // trim() เอาช่องว่างออกก่อนเช็ค
    if (input.val().trim() === '') {
        // ใส่ focus
        input.focus();

        // เพิ่มสไตล์ขอบแดง
        input.css("border", "1px solid red");

        // ถ้ายังไม่มีข้อความ required ให้เพิ่ม
        if (lastRow.find('.error-required').length === 0) {
            input.after('<div class="error-required" style="color:red;font-size:12px;">* Required</div>');
        }

        // หยุดไม่ให้ทำงานต่อ
        return false;
    } else {
        // ถ้าไม่ว่าง ล้างสไตล์/ข้อความที่เคยใส่ไว้
        input.css("border", "");
        lastRow.find('.error-required').remove();
    }
     var clone = lastRow.clone();
          clone.find(".line-name").val("");
          clone.find(".line-phone").val("");
          clone.find(".line-email").val("");
          clone.attr("data-var", "");
          clone.find('.rec-id').val("0");
          lastRow.after(clone);
    
}
function removeLine(e){
    var id = e.closest("tr").find(".rec-id").val();
    if(id){
        removelist.push(id);
        $(".removelist").val(removelist);
        if($("#table-list tbody tr").length == 1){
           $("#table-list tbody tr").find(".line-name").val("");
           $("#table-list tbody tr").find(".line-phone").val("");
           $("#table-list tbody tr").find(".line-email").val("");
           $("#table-list tbody tr").attr("data-var", "");
           $("#table-list tbody tr").find(".rec-id").val("0");
        }else{
           e.parent().parent().remove(); 
        }
    }else{
       if($("#table-list tbody tr").length == 1){
           $("#table-list tbody tr").find(".line-name").val("");
           $("#table-list tbody tr").find(".line-phone").val("");
           $("#table-list tbody tr").find(".line-email").val("");
           $("#table-list tbody tr").attr("data-var", "");
           $("#table-list tbody tr").find(".rec-id").val("0");
        }else{
           e.parent().parent().remove(); 
        }
    }
}
JS;
$this->registerJs($js, static::POS_END);
?>