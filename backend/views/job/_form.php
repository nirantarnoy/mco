<?php

use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\Job $model */
/** @var yii\widgets\ActiveForm $form */

$model_purch_job = \backend\models\Purch::find()->where(['job_id' => $model->id])->all();
$customer_name = '';
if(!$model->isNewRecord){
    $data = \backend\models\Quotation::findCustomerData2($model->quotation_id);
    if(!empty($data)){
        $customer_name = $data[0]['customer_name'];
    }
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

    <div class="job-form">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
        <input type="hidden" class="removelist" name="removelist" value="">
        <input type="hidden" class="expense_removelist" name="expense_removelist" value="">

        <!-- ส่วนฟอร์มหลัก -->
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
                <?php if(!$model->isNewRecord): ?>
                    <div class="btn btn-sm btn-info" onclick="pullQuotationDetails($(this))"><i class="fas fa-sync"></i> ดึงรายละเอียดจากใบเสนอราคา</div>
                <?php endif; ?>
            </div>
            <div class="col-lg-3">
                <label for="">ลูกค้า</label>
                <input type="text" class="form-control customer-name" name="customer_name" value="<?=$customer_name?>" readonly>
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
                    ])->label() ?>
            </div>
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
                    ])->label() ?>
            </div>
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
                <?= $form->field($model, 'cus_po_doc')->fileInput() ?>
                <?php if ($model->cus_po_doc): ?>
                    <div class="alert alert-info">
                        <strong>ไฟล์ปัจจุบัน:</strong><br>
                        <?php
                        $fileUrl = Yii::getAlias('@web/uploads/job/' . $model->cus_po_doc);
                        ?>
                        <?= Html::a('📂 เปิดไฟล์', $fileUrl, [
                            'class' => 'btn btn-sm btn-outline-primary mt-2',
                            'target' => '_blank',
                            'data-pjax' => '0'
                        ]) ?>
                        <?= Html::a('🗑️ ลบไฟล์', ['delete-file', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-danger mt-2',
                            'data' => [
                                'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์นี้?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-3">
                <?= $form->field($model, 'jsa_doc')->fileInput() ?>
                <?php if ($model->jsa_doc): ?>
                    <div class="alert alert-info">
                        <strong>ไฟล์ปัจจุบัน:</strong><br>
                        <?php
                        $fileUrl = Yii::getAlias('@web/uploads/job/' . $model->jsa_doc);
                        ?>
                        <?= Html::a('📂 เปิดไฟล์', $fileUrl, [
                            'class' => 'btn btn-sm btn-outline-primary mt-2',
                            'target' => '_blank',
                            'data-pjax' => '0'
                        ]) ?>
                        <?= Html::a('🗑️ ลบไฟล์', ['delete-file', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-danger mt-2',
                            'data' => [
                                'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์นี้?',
                                'method' => 'post',
                            ],
                        ]) ?>
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

        <!-- ส่วนรายการค่าใช้จ่าย (แบบ Manual) -->
        <br/>
        <div class="row">
            <div class="col-lg-12">
                <label for="">รายการค่าใช้จ่ายของใบงาน</label>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-bordered table-striped" id="expense-table">
                    <thead>
                    <tr>
                        <th style="width: 5%;text-align: center;">#</th>
                        <th style="width: 15%">วันที่</th>
                        <th style="width: 35%">รายการค่าใช้จ่าย</th>
                        <th style="width: 15%">จำนวนเงิน</th>
                        <th style="width: 25%">เอกสารแนบ</th>
                        <th style="width: 5%;text-align: center;">-</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($model_expenses)): ?>
                        <?php foreach ($model_expenses as $idx => $expense): ?>
                            <tr data-val="<?= $expense->id ?>">
                                <td style="text-align: center;">
                                    <input type="hidden" class="expense-id" name="expense_id[]"
                                           value="<?= $expense->id ?>">
                                    <input type="text" class="form-control" value="<?= ($idx + 1) ?>" readonly>
                                </td>
                                <td>
                                    <input type="date" class="form-control expense-date" name="expense_date[]"
                                           value="<?= date('Y-m-d',strtotime($expense->trans_date)) ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control expense-desc" name="expense_desc[]"
                                           value="<?= $expense->description ?>">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control expense-amount"
                                           name="expense_amount[]" value="<?= $expense->line_amount ?>"
                                           onchange="calculateTotal()">
                                </td>
                                <td>
                                    <input type="file" class="form-control expense-file" name="expense_file[]">
                                    <?php if ($expense->line_doc): ?>
                                        <div class="file-info mt-1">
                                            <a href="<?= Yii::getAlias('@web/uploads/expense/' . $expense->line_doc) ?>"
                                               target="_blank" class="btn btn-link btn-sm p-0">
                                                📎 <?= $expense->line_doc ?>
                                            </a>
                                            <?= Html::a('ลบไฟล์', ['delete-expense-file', 'id' => $expense->id], [
                                                'class' => 'btn btn-danger btn-xs ms-2',
                                                'data' => [
                                                    'confirm' => 'ต้องการลบไฟล์นี้หรือไม่?',
                                                    'method' => 'post',
                                                ],
                                            ]) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <div class="btn btn-sm btn-danger" onclick="removeExpenseLine($(this))">ลบ</div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr data-val="">
                            <td style="text-align: center;">
                                <input type="hidden" class="expense-id" name="expense_id[]" value="">
                                <input type="text" class="form-control" value="1" readonly>
                            </td>
                            <td>
                                <input type="date" class="form-control expense-date" name="expense_date[]" value="">
                            </td>
                            <td>
                                <input type="text" class="form-control expense-desc" name="expense_desc[]" value=""
                                       placeholder="ระบุรายการค่าใช้จ่าย">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control expense-amount"
                                       name="expense_amount[]" value="" placeholder="0.00"
                                       onchange="calculateTotal()">
                            </td>
                            <td>
                                <input type="file" class="form-control expense-file" name="expense_file[]">
                            </td>
                            <td style="text-align: center;">
                                <div class="btn btn-sm btn-danger" onclick="removeExpenseLine($(this))">ลบ</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td>
                            <div class="btn btn-sm btn-primary" onclick="addExpenseLine()">เพิ่มรายการ</div>
                        </td>
                        <td colspan="2" style="text-align: right;"><strong>รวมค่าใช้จ่าย:</strong></td>
                        <td><strong><span id="total-expense">0.00</span> บาท</strong></td>
                        <td colspan="2"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

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
                                <td style="text-align: center">
                                    <input type="hidden" name="job_line_id[]" value="<?= $line->id ?>">
                                    <?= $key + 1 ?>
                                </td>
                                <td><?= \backend\models\Product::findName($line->product_id) ?></td>
                                <td>
                                    <input type="number" class="form-control line-qty" name="line_qty[]" value="<?= $line->qty ?>" onchange="lineCalculate($(this))">
                                </td>
                                <td><?= \backend\models\Product::findUnitName($line->product_id) ?></td>
                                <td>
                                    <input type="number" step="0.01" class="form-control line-price" name="line_unit_price[]" value="<?= $line->line_price ?>" onchange="lineCalculate($(this))">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control line-item-total" name="line_item_total[]" value="<?= $line->line_total ?>" readonly>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
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
                            <tr data-val="<?= $value->id ?>">
                                <td style="text-align: center;">
                                    <input type="hidden" class="rec-id" name="rec_id[]" value="<?= $value->id ?>">
                                    <input type="text" class="form-control" value="<?= ($inx + 1) ?>" readonly>
                                </td>
                                <td>
                                    <input type="text" class="form-control line-name" name="line_name[]"
                                           value="<?= $value->name ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control line-phone" name="line_phone[]"
                                           value="<?= $value->phone ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control line-email" name="line_email[]"
                                           value="<?= $value->email ?>">
                                </td>
                                <td style="text-align: center;">
                                    <div class="btn btn-sm btn-danger" onclick="removeLine($(this))">ลบ</div>
                                </td>
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
$url_to_pull_quotation_details = \yii\helpers\Url::to(['job/pull-quotation-details'], true);
$js = <<<JS
var removelist = [];
var removeExpenseList = [];

$(function(){
    calculateTotal();
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

// ฟังก์ชันสำหรับ Contact
function addline(){
    var lastRow = $("#table-list tbody tr:last");
    var input = lastRow.find('.line-name');

    if (input.val().trim() === '') {
        input.focus();
        input.css("border", "1px solid red");
        if (lastRow.find('.error-required').length === 0) {
            input.after('<div class="error-required" style="color:red;font-size:12px;">* Required</div>');
        }
        return false;
    } else {
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
        $(".removelist").val(removelist.join(","));
    }
    
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

// ฟังก์ชันสำหรับ Expense
function addExpenseLine(){
    var lastRow = $("#expense-table tbody tr:last");
    var rowCount = $("#expense-table tbody tr").length;
    
    var clone = lastRow.clone();
    clone.find(".expense-date").val("");
    clone.find(".expense-desc").val("");
    clone.find(".expense-amount").val("");
    clone.find(".expense-file").val("");
    clone.find(".file-info").remove();
    clone.attr("data-val", "");
    clone.find('.expense-id').val("");
    clone.find('input[readonly]').val(rowCount + 1);
    
    lastRow.after(clone);
    calculateTotal();
}

function removeExpenseLine(e){
    var id = e.closest("tr").find(".expense-id").val();
    if(id && id != ""){
        removeExpenseList.push(id);
        $(".expense_removelist").val(removeExpenseList.join(","));
    }
    
    if($("#expense-table tbody tr").length == 1){
        $("#expense-table tbody tr").find(".expense-date").val("");
        $("#expense-table tbody tr").find(".expense-desc").val("");
        $("#expense-table tbody tr").find(".expense-amount").val("");
        $("#expense-table tbody tr").find(".expense-file").val("");
        $("#expense-table tbody tr").find(".expense-id").val("");
        $("#expense-table tbody tr").find(".file-info").remove();
        $("#expense-table tbody tr").attr("data-val", "");
    }else{
        e.parent().parent().remove();
        updateExpenseNumbers();
    }
    calculateTotal();
}

function updateExpenseNumbers(){
    $("#expense-table tbody tr").each(function(index){
        $(this).find("input[readonly]").val(index + 1);
    });
}

function calculateTotal(){
    var total = 0;
    $(".expense-amount").each(function(){
        var val = parseFloat($(this).val()) || 0;
        total += val;
    });
    $("#total-expense").text(total.toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ","));
}

function lineCalculate(e){
    var row = e.closest("tr");
    var qty = parseFloat(row.find(".line-qty").val()) || 0;
    var price = parseFloat(row.find(".line-price").val()) || 0;
    var total = qty * price;
    row.find(".line-item-total").val(total.toFixed(2));
}

function pullQuotationDetails(e){
    var id = '$model->id';
    var quotation_id = $("#job-quotation_id").val();
    if(id && quotation_id){
        if(confirm('คุณต้องการดึงรายละเอียดจากใบเสนอราคาใช่หรือไม่? (รายการที่ซ้ำจะถูกข้าม)')){
            $.ajax({
                url: '$url_to_pull_quotation_details',
                type: 'POST',
                data: {'id':id, 'quotation_id':quotation_id},
                success: function(data) {
                   location.reload();
                },
                error: function() {
                    alert('Error pulling quotation details');
                }
            });
        }
    }else{
        alert('กรุณาเลือกใบเสนอราคาก่อน');
    }
}

JS;
$this->registerJs($js, static::POS_END);
?>