<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $purchModel backend\models\PurchaseMaster */
/* @var $detailsWithRemaining array */
/* @var $warehouses array */

$this->title = 'รับสินค้าเข้าคลัง (None PR): ' . $purchModel->docnum;
$this->params['breadcrumbs'][] = ['label' => 'บันทึกการซื้อ (None PR)', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $purchModel->docnum, 'url' => ['view', 'id' => $purchModel->id]];
$this->params['breadcrumbs'][] = 'รับสินค้าเข้าคลัง';

$itemCount = count($detailsWithRemaining);

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
    var detailId = $(this).data('detail-id');
    var maxQty = $(this).data('max-qty');
    $('#receive-' + detailId).val(maxQty);
    calculateTotal();
});

$(document).ready(function() {
    calculateTotal();
});
");
?>

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

<div class="purchasemaster-receive">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <?= Html::a('<i class="fas fa-history mr-1"></i> ประวัติการรับสินค้า', ['receive-history', 'id' => $purchModel->id], [
                'class' => 'btn btn-info'
            ]) ?>
            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> กลับ', ['view', 'id' => $purchModel->id], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <!-- None PR Information -->
    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title">ข้อมูลใบซื้อ None PR</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <strong>เลขที่เอกสาร:</strong><br>
                    <?= Html::encode($purchModel->docnum) ?>
                </div>
                <div class="col-md-3 mb-2">
                    <strong>วันที่เอกสาร:</strong><br>
                    <?= date('m/d/Y', strtotime($purchModel->docdat)) ?>
                </div>
                <div class="col-md-3 mb-2">
                    <strong>ผู้จำหน่าย:</strong><br>
                    <?= Html::encode($purchModel->supnam) ?>
                </div>
                <div class="col-md-3 mb-2">
                    <strong>ยอดรวมสุทธิ:</strong><br>
                    <?= Yii::$app->formatter->asDecimal($purchModel->total_amount, 2) ?> บาท
                </div>
            </div>
        </div>
    </div>

    <!-- Receive Form -->
    <?php $form = ActiveForm::begin([
        'method' => 'post',
        'options' => ['class' => 'receive-form', 'enctype' => 'multipart/form-data'],
    ]); ?>

    <div class="card card-outline card-success mb-3">
        <div class="card-header">
            <h3 class="card-title">รายการสินค้าที่รับเข้าคลัง</h3>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6 mb-2">
                    <label class="form-label"><strong>คลังสินค้าหลักที่จะรับเข้า</strong></label>
                    <?= Html::dropDownList('line_warehouse_id[]', null, $warehouses, [
                        'class' => 'form-control select2',
                        'prompt' => '-- เลือกคลังสินค้า --',
                        'required' => true,
                    ]) ?>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label"><strong>หมายเหตุการรับสินค้า</strong></label>
                    <?= Html::textArea('remark', '', [
                        'class' => 'form-control',
                        'placeholder' => 'ระบุหมายเหตุเพิ่มเติม (ถ้ามี)',
                        'rows' => 2,
                    ]) ?>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th style="width: 140px;">รหัสสินค้า</th>
                            <th>รายละเอียดสินค้า</th>
                            <th class="text-right" style="width: 120px;">จำนวนสั่งซื้อ</th>
                            <th class="text-right" style="width: 120px;">รับแล้ว</th>
                            <th class="text-right" style="width: 120px;">ค้างรับ</th>
                            <th class="text-right" style="width: 160px;">จำนวนที่รับครั้งนี้</th>
                            <th class="text-center" style="width: 100px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detailsWithRemaining as $index => $item): 
                            $detail = $item['detail'];
                            $productId = $item['product_id'];
                            $ordered = $item['ordered_qty'];
                            $received = $item['received_qty'];
                            $remaining = $item['remaining_qty'];
                        ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td>
                                    <?= Html::encode($detail->stkcod) ?>
                                    <?php if (!$productId): ?>
                                        <br><span class="badge badge-danger" title="ไม่พบสินค้านี้ใน Product Master">ไม่พบในคลัง</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= Html::encode($detail->stkdes) ?></td>
                                <td class="text-right"><?= number_format($ordered, 2) ?></td>
                                <td class="text-right text-success"><?= number_format($received, 2) ?></td>
                                <td class="text-right text-danger"><strong><?= number_format($remaining, 2) ?></strong></td>
                                <td>
                                    <?php if ($remaining > 0 && $productId): ?>
                                        <?= Html::textInput("receive[{$detail->id}]", 0, [
                                            'type' => 'number',
                                            'step' => '0.01',
                                            'min' => '0',
                                            'max' => $remaining,
                                            'class' => 'form-control text-right receive-qty',
                                            'id' => 'receive-' . $detail->id,
                                        ]) ?>
                                    <?php else: ?>
                                        <span class="text-muted text-center d-block">
                                            <?= !$productId ? 'ไม่พบรหัสสินค้า' : 'รับครบแล้ว' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($remaining > 0 && $productId): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary receive-all" 
                                                data-detail-id="<?= $detail->id ?>" 
                                                data-max-qty="<?= $remaining ?>">
                                            รับทั้งหมด
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">รวมจำนวนรับครั้งนี้:</th>
                            <th class="text-right text-primary"><span id="total-qty">0.00</span></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Upload Documents -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <label class="form-label"><strong>เอกสารแนบการรับสินค้า (ใบส่งของ / ใบรับสินค้า)</strong></label>
                    <input type="file" name="file_doc[]" multiple class="form-control-file">
                    <small class="form-text text-muted">สามารถแนบไฟล์ภาพหรือ PDF เพิ่มเติมได้</small>
                </div>
            </div>

            <div class="text-right mt-4">
                <?= Html::submitButton('<i class="fas fa-save mr-1"></i> บันทึกรับสินค้าเข้าคลัง', [
                    'class' => 'btn btn-success btn-lg'
                ]) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
