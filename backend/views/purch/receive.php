<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $purchModel backend\models\Purch */
/* @var $poLines array */
/* @var $warehouses array */

$this->title = 'รับสินค้าเข้าคลัง: ' . $purchModel->purch_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบสั่งซื้อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $purchModel->purch_no, 'url' => ['view', 'id' => $purchModel->id]];
$this->params['breadcrumbs'][] = 'รับสินค้าเข้าคลัง';

$warehouse_data = \backend\models\Warehouse::find()->where(['status' => 1])->all();

// Register JS for calculations
$this->registerJs("
function calculateTotal() {
    var total = 0;
    $('.receive-qty').each(function() {
        var qty = parseFloat($(this).val()) || 0;
        if (qty > 0) {
            total += qty;
        }
    });
    $('#total-qty').text(total.toFixed(2));
}

$(document).on('keyup change', '.receive-qty', function() {
    var qty = parseFloat($(this).val()) || 0;
    var maxQty = parseFloat($(this).attr('max')) || 0;
    
    if (qty > maxQty) {
        $(this).val(maxQty);
        alert('จำนวนที่รับเข้าไม่สามารถเกินจำนวนคงเหลือได้');
    }
    
    calculateTotal();
});

$('.receive-all').click(function() {
    var productId = $(this).data('product-id');
    var maxQty = $(this).data('max-qty');
    $('#receive-' + productId).val(maxQty);
    calculateTotal();
});

$(document).ready(function() {
    calculateTotal();
});

// Toggle checklist section
$('#toggle-checklist').click(function() {
    $('#checklist-section').slideToggle();
    var icon = $(this).find('i');
    if (icon.hasClass('fa-chevron-down')) {
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
    } else {
        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
    }
});
");
?>

<div class="purch-receive">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <?= Html::a('ประวัติการรับสินค้า', ['receive-history', 'id' => $purchModel->id], [
                'class' => 'btn btn-info'
            ]) ?>
            <?= Html::a('กลับ', ['view', 'id' => $purchModel->id], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

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

    <!-- PO Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">ข้อมูลใบสั่งซื้อ</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>เลขที่ใบสั่งซื้อ:</strong><br>
                    <?= Html::encode($purchModel->purch_no) ?>
                </div>
                <div class="col-md-3">
                    <strong>วันที่:</strong><br>
                    <?= date('m/d/Y', strtotime($purchModel->purch_date)) ?>
                </div>
                <div class="col-md-3">
                    <strong>ผู้ขาย:</strong><br>
                    <?= Html::encode(\backend\models\Vendor::findName($purchModel->vendor_id)) ?>
                </div>
                <div class="col-md-3">
                    <strong>ยอดรวม:</strong><br>
                    <?= Yii::$app->formatter->asCurrency($purchModel->net_amount, 'THB') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Receive Form -->
    <?php $form = ActiveForm::begin([
        'method' => 'post',
        'options' => ['class' => 'receive-form','enctype'=>'multipart/form-data'],
    ]); ?>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">รายการสินค้าที่รับเข้า</h5>
        </div>
        <div class="card-body">
            <!-- Warehouse Selection -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label"><strong>หมายเหตุ</strong></label>
                    <?= Html::textArea('remark', '', [
                        'class' => 'form-control',
                        'placeholder' => 'หมายเหตุการรับสินค้า',
                        'rows' => 3,
                    ]) ?>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>ชื่อสินค้า</th>
                        <th style="width: 100px;">สั่งซื้อ</th>
                        <th style="width: 100px;">รับแล้ว</th>
                        <th style="width: 100px;">คงเหลือ</th>
                        <th style="width: 120px;">รับเข้าครั้งนี้</th>
                        <th style="width: 80px;">หน่วย</th>
                        <th style="width: 120px;">เข้าคลัง</th>
                        <th style="width: 80px;">ทั้งหมด</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $rowNum = 1; ?>
                    <?php foreach ($poLines as $line): ?>
                        <tr>
                            <td class="text-center"><?= $rowNum++ ?></td>
                            <td>
                                <strong><?= Html::encode($line['product_name']) ?></strong>
                                <?php if (!empty($line['product_description'])): ?>
                                    <br><small class="text-muted"><?= Html::encode($line['product_description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?= number_format($line['qty'], 2) ?>
                            </td>
                            <td class="text-center">
                                <?= number_format($line['total_received'], 2) ?>
                            </td>
                            <td class="text-center">
                                <strong class="text-primary"><?= number_format($line['remaining_qty'], 2) ?></strong>
                            </td>
                            <td>
                                <?= Html::textInput("receive[{$line['product_id']}]", '', [
                                    'class' => 'form-control receive-qty',
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'min' => '0',
                                    'max' => $line['remaining_qty'],
                                    'placeholder' => '0',
                                    'id' => 'receive-' . $line['product_id'],
                                    'required' => true,
                                ]) ?>
                            </td>
                            <td class="text-center">
                                <?= Html::encode($line['unit'] ?? 'ชิ้น') ?>
                            </td>
                            <td>
                                <select name="line_warehouse_id[]" class="form-control line-warehouse-select" required>
                                    <option value="">เลือกคลังสินค้า...</option>
                                    <?php foreach ($warehouse_data as $value): ?>
                                        <option value="<?= $value->id ?>"><?= $value->name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary receive-all"
                                        data-product-id="<?= $line['product_id'] ?>"
                                        data-max-qty="<?= $line['remaining_qty'] ?>"
                                        title="รับทั้งหมด">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-secondary">
                    <tr>
                        <td colspan="5" class="text-end"><strong>รวมจำนวนที่รับเข้า:</strong></td>
                        <td class="text-center">
                            <strong><span id="total-qty">0.00</span></strong>
                        </td>
                        <td colspan="3"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Checklist Section -->
    <div class="card mb-3">
        <div class="card-header" style="cursor: pointer;" id="toggle-checklist">
            <h5 class="card-title mb-0">
                <i class="fas fa-clipboard-check"></i> Checklist การตรวจรับสินค้า
                <i class="fas fa-chevron-down float-right"></i>
            </h5>
        </div>
        <div class="card-body" id="checklist-section">

            <!-- Checker Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label"><strong>ชื่อผู้ตรวจสอบ *</strong></label>
                    <?= Html::textInput('checklist[checker_name]', '', [
                        'class' => 'form-control',
                        'placeholder' => 'ระบุชื่อผู้ตรวจสอบ',
                        'required' => false,
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><strong>วันที่ตรวจสอบ *</strong></label>
                    <?= Html::input('date', 'checklist[check_date]', date('Y-m-d'), [
                        'class' => 'form-control',
                        'required' => false,
                    ]) ?>
                </div>
            </div>

            <!-- 1. สภาพทั่วไปของสินค้า -->
            <div class="mb-4">
                <h6 class="border-bottom pb-2"><strong>1. สภาพทั่วไปของสินค้า</strong></h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                        <tr>
                            <th width="50%">รายการ</th>
                            <?php for($x=1;$x<=count($poLines);$x++): ?>
                            <th width="5%" class="text-center"><?=$x?></th>
                            <?php endfor; ?>
<!--                            <th width="5%" class="text-center">2</th>-->
<!--                            <th width="5%" class="text-center">3</th>-->
<!--                            <th width="5%" class="text-center">4</th>-->
<!--                            <th width="5%" class="text-center">5</th>-->
<!--                            <th width="5%" class="text-center">6</th>-->
<!--                            <th width="5%" class="text-center">7</th>-->
<!--                            <th width="5%" class="text-center">8</th>-->
<!--                            <th width="5%" class="text-center">9</th>-->
<!--                            <th width="5%" class="text-center">10</th>-->
<!--                            <th width="5%" class="text-center">11</th>-->
<!--                            <th width="5%" class="text-center">12</th>-->
<!--                            <th width="5%" class="text-center">13</th>-->
<!--                            <th width="5%" class="text-center">14</th>-->
<!--                            <th width="5%" class="text-center">15</th>-->
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><strong>สภาพทั่วไปของสินค้า</strong></td>
                            <?php for ($i = 1; $i <= count($poLines); $i++): ?>
                                <td class="text-center">
                                    <?= Html::checkbox("checklist[general_condition][{$i}]", false, [
                                        'value' => 1,
                                        'class' => 'form-check-input',
                                        'style' => 'transform: scale(1.3); cursor: pointer;'
                                    ]) ?>
                                </td>
                            <?php endfor; ?>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. สิ่งที่ถูกต้องตามใบสั่งซื้อ -->
            <div class="mb-4">
                <h6 class="border-bottom pb-2"><strong>2. สิ่งที่ถูกต้องตามใบสั่งซื้อ</strong></h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                        <tr>
                            <th width="80%">รายการ</th>
                            <th width="20%" class="text-center">ตรวจสอบ</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>2.1 สินค้าตรงตาม</td>
                            <td class="text-center">
                                <?= Html::checkbox('checklist[correct_items]', false, [
                                    'value' => 1,
                                    'class' => 'form-check-input',
                                    'style' => 'transform: scale(1.3); cursor: pointer;'
                                ]) ?>
                            </td>
                        </tr>
                        <tr>
                            <td>2.2 จำนวนและขนาด</td>
                            <td class="text-center">
                                <?= Html::checkbox('checklist[correct_quantity]', false, [
                                    'value' => 1,
                                    'class' => 'form-check-input',
                                    'style' => 'transform: scale(1.3); cursor: pointer;'
                                ]) ?>
                            </td>
                        </tr>
                        <tr>
                            <td>2.3 จำนวนที่สั่ง</td>
                            <td class="text-center">
                                <?= Html::checkbox('checklist[correct_spec]', false, [
                                    'value' => 1,
                                    'class' => 'form-check-input',
                                    'style' => 'transform: scale(1.3); cursor: pointer;'
                                ]) ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. เอกสารที่จัดส่งมาพร้อม -->
            <div class="mb-4">
                <h6 class="border-bottom pb-2"><strong>3. เอกสารที่จัดส่งมาพร้อม</strong></h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                        <tr>
                            <th width="80%">รายการ</th>
                            <th width="20%" class="text-center">มี/ไม่มี</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>3.1 ใบ certificate</td>
                            <td class="text-center">
                                <?= Html::checkbox('checklist[has_certificate]', false, [
                                    'value' => 1,
                                    'class' => 'form-check-input',
                                    'style' => 'transform: scale(1.3); cursor: pointer;'
                                ]) ?>
                            </td>
                        </tr>
                        <tr>
                            <td>3.2 คู่มือการใช้งาน</td>
                            <td class="text-center">
                                <?= Html::checkbox('checklist[has_manual]', false, [
                                    'value' => 1,
                                    'class' => 'form-check-input',
                                    'style' => 'transform: scale(1.3); cursor: pointer;'
                                ]) ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Checklist Notes -->
            <div class="row">
                <div class="col-md-12">
                    <label class="form-label"><strong>หมายเหตุเพิ่มเติม</strong></label>
                    <?= Html::textarea('checklist[notes]', '', [
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => 'ระบุหมายเหตุเพิ่มเติมจากการตรวจสอบ...'
                    ]) ?>
                </div>
            </div>

        </div>
    </div>

    <!-- File Upload -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">เอกสารแนบ</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <label for="">แนบเอกสาร (ถ้าต้องการ)</label>
                    <input type="file" name="file_doc[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <small class="text-muted">รองรับไฟล์: PDF, JPG, PNG, DOC, DOCX</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="form-group mt-3">
        <div class="d-flex justify-content-between">
            <?= Html::submitButton('<i class="fas fa-download"></i> รับสินค้าเข้าคลัง', [
                'class' => 'btn btn-success btn-lg',
                'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะรับสินค้าเข้าคลัง?'
            ]) ?>
            <?= Html::a('ยกเลิก', ['view', 'id' => $purchModel->id], ['class' => 'btn btn-secondary btn-lg']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .receive-qty {
        text-align: center;
    }

    .receive-all {
        margin: 0;
    }

    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .form-check-input {
        margin: 0 auto;
    }

    #toggle-checklist:hover {
        background-color: #e9ecef;
    }
</style>
