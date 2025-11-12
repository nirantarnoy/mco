<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\Purchase */
/* @var $purchases array */
/* @var $isUpdate bool */

$this->title = 'บันทึกข้อมูลซื้อ';
$this->params['breadcrumbs'][] = $this->title;

$isUpdate = isset($isUpdate) ? $isUpdate : false;
?>

    <div class="purchase-index">
        
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">
                    <i class="fas fa-edit"></i> <?= $isUpdate ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล' ?>
                </h3>
                <?php if ($isUpdate): ?>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus"></i> เพิ่มรายการใหม่', ['index'], ['class' => 'btn btn-success btn-sm']) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'purchase-form',
                    'action' => $isUpdate ? ['update', 'id' => $model->id] : ['index'],
                ]); ?>

                <div class="row">
                    <!-- คอลัมน์ที่ 1 -->
                    <div class="col-md-3">
                        <?= $form->field($model, 'dedcod')->widget(\kartik\select2\Select2::className(),[
                                'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Department::find()->all(),'id','name'),
                                'options'=>[
                                        'placeholder'=>'--เลือกแผนก--'
                                ],
                                'pluginOptions'=>[
                                        'allowClear'=>true,
                                ]
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'docnum')->textInput(['maxlength' => true, 'placeholder' => 'เลขที่เอกสาร']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'docdat')->widget(DatePicker::classname(), [
                            'options' => ['placeholder' => 'เลือกวันที่...'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true,
                            ]
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'supcod')->widget(\kartik\select2\Select2::className(),[
                            'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Vendor::find()->all(),'id','name'),
                            'options'=>[
                                'placeholder'=>'--เลือกรหัสผู้จำหน่าย--'
                            ],
                            'pluginOptions'=>[
                                'allowClear'=>true,
                            ]
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <!-- คอลัมน์ที่ 2 -->
                    <div class="col-md-6">
                        <?= $form->field($model, 'supnam')->textInput(['maxlength' => true, 'placeholder' => 'ชื่อผู้จำหน่าย']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'stkcod')->widget(\kartik\select2\Select2::className(),[
                            'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Vendor::find()->all(),'id','name'),
                            'options'=>[
                                'placeholder'=>'--เลือกรหัสสินค้า--'
                            ],
                            'pluginOptions'=>[
                                'allowClear'=>true,
                            ]
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'stkdes')->textInput(['maxlength' => true, 'placeholder' => 'รายละเอียดสินค้า']) ?>
                    </div>
                </div>

                <div class="row">
                    <!-- คอลัมน์ที่ 3 -->
                    <div class="col-md-2">
                        <?= $form->field($model, 'trnqty')->textInput(['type' => 'number', 'step' => '0.01', 'placeholder' => 'จำนวน']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'untpri')->textInput(['maxlength' => true, 'placeholder' => 'ราคาต่อหน่วย']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'disc')->textInput(['type' => 'number', 'step' => '0.01', 'placeholder' => 'ส่วนลด']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'placeholder' => 'จำนวนเงิน']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'payfrm')->widget(\kartik\select2\Select2::className(),[
                            'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Vendor::find()->all(),'id','name'),
                            'options'=>[
                                'placeholder'=>'--เลือกวิธีชำระ--'
                            ],
                            'pluginOptions'=>[
                                'allowClear'=>true,
                            ]
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'duedat')->widget(DatePicker::classname(), [
                            'options' => ['placeholder' => 'วันครบกำหนด'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                            ]
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <!-- คอลัมน์ที่ 4 -->
                    <div class="col-md-3">
                        <?= $form->field($model, 'taxid')->textInput(['maxlength' => true, 'placeholder' => 'เลขประจำตัวผู้เสียภาษี']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'discod')->textInput(['maxlength' => true, 'placeholder' => 'ส่วนลดทั่วไป']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'addr01')->textInput(['placeholder' => 'ที่อยู่ 1']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'addr02')->textInput(['placeholder' => 'ที่อยู่ 2']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'addr03')->textInput(['placeholder' => 'ที่อยู่ 3']) ?>
                    </div>
                </div>

                <div class="row">
                    <!-- คอลัมน์ที่ 5 -->
                    <div class="col-md-2">
                        <?= $form->field($model, 'zipcod')->textInput(['maxlength' => true, 'placeholder' => 'รหัสไปรษณีย์']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'telnum')->textInput(['maxlength' => true, 'placeholder' => 'เบอร์โทร']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'orgnum')->textInput(['maxlength' => true, 'placeholder' => 'ลำดับเรียง']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'refnum')->textInput(['maxlength' => true, 'placeholder' => 'เลขที่ใบกำกับ']) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'vatdat')->widget(DatePicker::classname(), [
                            'options' => ['placeholder' => 'วันที่ภาษี'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                            ]
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'vatpr0')->textInput(['type' => 'number', 'step' => '0.01', 'placeholder' => 'มูลค่าภาษี']) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'late')->textInput(['maxlength' => true, 'placeholder' => 'ยังไม่ได้เอกสาร']) ?>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <?= Html::submitButton('<i class="fas fa-save"></i> ' . ($isUpdate ? 'บันทึกการแก้ไข' : 'บันทึก'), ['class' => 'btn btn-success btn-lg']) ?>
                    <?php if ($isUpdate): ?>
                        <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['index'], ['class' => 'btn btn-secondary btn-lg']) ?>
                    <?php endif; ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <!-- ตารางแสดงข้อมูล -->
        <div class="card mt-4">
            <div class="card-header bg-success">
                <h3 class="card-title"><i class="fas fa-list"></i> รายการข้อมูลที่บันทึก</h3>
                <div class="card-tools">
                    <?php if (!empty($purchases)): ?>
                        <?= Html::a('<i class="fas fa-file-excel"></i> Export Excel', ['export'], [
                            'class' => 'btn btn-success btn-sm',
                            'title' => 'ส่งออกเป็น Excel'
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover mb-0" id="purchase-table">
                        <thead class="thead-dark">
                        <tr>
                            <th width="3%">#</th>
                            <th width="5%">รหัสแผนก</th>
                            <th width="7%">เลขที่เอกสาร</th>
                            <th width="6%">วันที่</th>
                            <th width="7%">รหัสผู้จำหน่าย</th>
                            <th width="10%">ชื่อผู้จำหน่าย</th>
                            <th width="7%">รหัสสินค้า</th>
                            <th width="10%">รายละเอียด</th>
                            <th width="5%" class="text-right">จำนวน</th>
                            <th width="7%">ราคา/หน่วย</th>
                            <th width="5%" class="text-right">ส่วนลด</th>
                            <th width="7%" class="text-right">จำนวนเงิน</th>
                            <th width="6%">วิธีชำระ</th>
                            <th width="6%">ครบกำหนด</th>
                            <th width="5%" class="text-center">จัดการ</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($purchases)): ?>
                            <tr>
                                <td colspan="15" class="text-center text-muted">ยังไม่มีข้อมูล</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($purchases as $index => $purchase): ?>
                                <tr data-id="<?= $purchase['id'] ?>">
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td><?= Html::encode($purchase['dep_name'] ?? '') ?></td>
                                    <td><?= Html::encode($purchase['docnum'] ?? '') ?></td>
                                    <td><?= !empty($purchase['docdat']) ? Yii::$app->formatter->asDate($purchase['docdat'], 'php:d/m/Y') : '-' ?></td>
                                    <td><?= Html::encode($purchase['vendor_name'] ?? '') ?></td>
                                    <td><?= Html::encode($purchase['supnam'] ?? '') ?></td>
                                    <td><?= Html::encode($purchase['product_name'] ?? '') ?></td>
                                    <td><?= Html::encode($purchase['stkdes'] ?? '') ?></td>
                                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($purchase['trnqty'] ?? 0, 2) ?></td>
                                    <td><?= Html::encode($purchase['untpri'] ?? '') ?></td>
                                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($purchase['disc'] ?? 0, 2) ?></td>
                                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($purchase['amount'] ?? 0, 2) ?></td>
                                    <td><?= Html::encode($purchase['payment_method_name'] ?? '') ?></td>
                                    <td><?= !empty($purchase['duedat']) ? Yii::$app->formatter->asDate($purchase['duedat'], 'php:d/m/Y') : '-' ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-warning btn-sm btn-edit" data-id="<?= $purchase['id'] ?>" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="<?= $purchase['id'] ?>" title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

<?php
$deleteUrl = Url::to(['delete']);
$loadUrl = Url::to(['load-model']);
$updateUrl = Url::to(['update']);

$this->registerJs("
// ฟังก์ชันลบข้อมูล
$(document).on('click', '.btn-delete', function() {
    var id = $(this).data('id');
    var row = $(this).closest('tr');
    
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบรายการนี้?')) {
        $.ajax({
            url: '$deleteUrl',
            type: 'POST',
            data: {
                id: id,
                _csrf: yii.getCsrfToken()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    row.fadeOut(300, function() {
                        $(this).remove();
                        updateRowNumbers();
                        if ($('#purchase-table tbody tr').length === 0) {
                            $('#purchase-table tbody').html('<tr><td colspan=\"15\" class=\"text-center text-muted\">ยังไม่มีข้อมูล</td></tr>');
                        }
                    });
                    alert(response.message);
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการลบข้อมูล');
            }
        });
    }
});

// ฟังก์ชันแก้ไขข้อมูล
$(document).on('click', '.btn-edit', function() {
    var id = $(this).data('id');
    window.location.href = '$updateUrl?id=' + id;
});

// อัพเดตเลขที่แถว
function updateRowNumbers() {
    $('#purchase-table tbody tr').each(function(index) {
        if ($(this).find('td').length > 1) {
            $(this).find('td:first').text(index + 1);
        }
    });
}
");
?>