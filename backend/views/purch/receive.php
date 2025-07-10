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
                    <?= date('d/m/Y', strtotime($purchModel->purch_date)) ?>
                </div>
                <div class="col-md-3">
                    <strong>ผู้ขาย:</strong><br>
                    <?= Html::encode($purchModel->vendor_name) ?>
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
        'options' => ['class' => 'receive-form'],
    ]); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">รายการสินค้าที่รับเข้า</h5>
        </div>
        <div class="card-body">
            <!-- Warehouse Selection -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label"><strong>เลือกคลังสินค้า *</strong></label>
                    <?= Select2::widget([
                        'name' => 'warehouse_id',
                        'data' => $warehouses,
                        'options' => [
                            'placeholder' => 'เลือกคลังสินค้า...',
                            'required' => true,
                        ],
                        'pluginOptions' => [
                            'allowClear' => false,
                            'width' => '100%',
                        ],
                    ]) ?>
                </div>
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
                                ]) ?>
                            </td>
                            <td class="text-center">
                                <?= Html::encode($line['unit'] ?? 'ชิ้น') ?>
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
                        <td colspan="2"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <div class="d-flex justify-content-between">
            <?= Html::submitButton('<i class="fas fa-download"></i> รับสินค้าเข้าคลัง', [
                'class' => 'btn btn-success',
                'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะรับสินค้าเข้าคลัง?'
            ]) ?>
            <?= Html::a('ยกเลิก', ['view', 'id' => $purchModel->id], ['class' => 'btn btn-secondary']) ?>
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
</style>