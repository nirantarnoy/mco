<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\PettyCashAdvance */
/* @var $form yii\widgets\ActiveForm */

$currentBalance = \backend\models\PettyCashAdvance::getCurrentBalance();
$maxAmount = \backend\models\PettyCashAdvance::MAX_AMOUNT;
$minAmount = \backend\models\PettyCashAdvance::MIN_AMOUNT;
$needsRefill = \backend\models\PettyCashAdvance::needsRefill();
$maxRequestAmount = $maxAmount - $currentBalance;

$this->registerJs("
// ตรวจสอบยอดเงินแบบ Real-time
function checkBalance() {
    var requestAmount = parseFloat($('#pettycashadvance-amount').val()) || 0;
    var currentBalance = {$currentBalance};
    var maxAmount = {$maxAmount};
    var totalAfterRequest = currentBalance + requestAmount;
    
    $('#current-balance').text(currentBalance.toLocaleString('th-TH', {minimumFractionDigits: 2}));
    $('#total-after-request').text(totalAfterRequest.toLocaleString('th-TH', {minimumFractionDigits: 2}));
    
    if (totalAfterRequest > maxAmount) {
        $('#balance-warning').show();
       // $('#submit-btn').prop('disabled', true);
    } else {
        $('#balance-warning').hide();
       // $('#submit-btn').prop('disabled', false);
    }
}

// Event handlers
$(document).on('input', '#pettycashadvance-amount', function() {
    checkBalance();
});

// Initialize
$(document).ready(function() {
    checkBalance();
});
");
?>

<div class="petty-cash-advance-form">

    <!-- แสดงสถานะวงเงิน -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card <?= $needsRefill ? 'border-warning' : 'border-info' ?>">
                <div class="card-header <?= $needsRefill ? 'bg-warning text-dark' : 'bg-info text-white' ?>">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-wallet"></i> สถานะวงเงินสดย่อย
                        <?php if ($needsRefill): ?>
                            <span class="badge badge-danger ml-2">ต้องเบิกเติม</span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6>วงเงินสูงสุด</h6>
                                <h4 class="text-primary"><?= number_format($maxAmount, 2) ?> บาท</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6>ยอดคงเหลือปัจจุบัน</h6>
                                <h4 class="<?= $needsRefill ? 'text-warning' : 'text-success' ?>">
                                    <span id="current-balance"><?= number_format($currentBalance, 2) ?></span> บาท
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6>วงเงินขั้นต่ำ</h6>
                                <h4 class="text-info"><?= number_format($minAmount, 2) ?> บาท</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6>เบิกได้สูงสุด</h6>
                                <h4 class="text-success"><?= number_format($maxRequestAmount, 2) ?> บาท</h4>
                            </div>
                        </div>
                    </div>

                    <?php if ($needsRefill): ?>
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>แจ้งเตือน:</strong> ยอดเงินสดย่อยเหลือน้อย (<?= number_format($currentBalance, 2) ?> บาท)
                            ควรเบิกเงินทดแทนเพื่อเติมวงเงินให้ครบ <?= number_format($maxAmount, 2) ?> บาท
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (\Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'petty-cash-advance-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => '<div class="col-sm-3">{label}</div><div class="col-sm-9">{input}{error}</div>',
            'labelOptions' => ['class' => 'control-label'],
        ]
    ]); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-hand-holding-usd"></i> ข้อมูลการเบิกเงินทดแทน
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'advance_no')->textInput([
                        'maxlength' => true,
                        'readonly' => true,
                        'placeholder' => 'จะสร้างอัตโนมัติ',
                        'style' => 'background-color: #f8f9fa;'
                    ]) ?>

                    <?= $form->field($model, 'request_date')->widget(DatePicker::class, [
                        'options' => ['placeholder' => 'เลือกวันที่เบิก'],
                        'pluginOptions' => [
                            'autoclose' => true,
                        ],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '1',
                        'class' => 'form-control text-right',
                        'placeholder' => '0.00'
                    ])->hint("จำนวนเงินที่เบิกได้สูงสุด: " . number_format($maxRequestAmount, 2) . " บาท") ?>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">ยอดหลังเบิก</label>
                        <div class="col-sm-9">
                            <div class="form-control-static">
                                <h5 class="text-info">
                                    <span id="total-after-request"><?= number_format($currentBalance, 2) ?></span> บาท
                                </h5>
                            </div>
                        </div>
                    </div>

                    <?php if ($model->status): ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">สถานะ</label>
                            <div class="col-sm-9">
                                <div class="form-control-static">
                                    <?php
                                    $statusLabels = [
                                        'pending' => ['label' => 'รอดำเนินการ', 'class' => 'warning'],
                                        'approved' => ['label' => 'อนุมัติแล้ว', 'class' => 'success'],
                                        'rejected' => ['label' => 'ปฏิเสธ', 'class' => 'danger'],
                                        'paid' => ['label' => 'จ่ายแล้ว', 'class' => 'info'],
                                    ];
                                    $status = $statusLabels[$model->status] ?? ['label' => $model->status, 'class' => 'secondary'];
                                    ?>
                                    <span class="badge badge-<?= $status['class'] ?>"><?= $status['label'] ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?= $form->field($model, 'purpose')->textarea([
                'rows' => 4,
                'placeholder' => 'ระบุวัตถุประสงค์ในการเบิกเงินทดแทน เช่น เติมวงเงินสดย่อย เพื่อใช้จ่ายในกิจกรรม...'
            ])->label('วัตถุประสงค์') ?>

            <?= $form->field($model, 'remarks')->textarea([
                'rows' => 2,
                'placeholder' => 'หมายเหตุเพิ่มเติม (ถ้ามี)'
            ])->label('หมายเหตุ') ?>
        </div>
    </div>

    <!-- คำเตือนเมื่อเกินวงเงิน -->
    <div id="balance-warning" class="alert alert-danger" style="display: none;">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>แจ้งเตือน:</strong>
        จำนวนเงินที่เบิกจะทำให้เกินวงเงินสูงสุด <?= number_format($maxAmount, 2) ?> บาท
    </div>

    <div class="form-group mt-4">
        <div class="text-center">
            <?= Html::submitButton(
                $model->isNewRecord ? '<i class="fas fa-save"></i> บันทึกใบเบิก' : '<i class="fas fa-save"></i> แก้ไข',
                [
                    'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
                    'id' => 'submit-btn'
                ]
            ) ?>
            <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['index'], ['class' => 'btn btn-secondary']) ?>

            <?php if (!$model->isNewRecord && $model->status === 'pending' && \Yii::$app->user->can('approve-advance')): ?>
<!--                --><?php //= Html::a('<i class="fas fa-check"></i> อนุมัติ', ['approve', 'id' => $model->id], [
//                    'class' => 'btn btn-success',
//                    'data' => [
//                        'confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบเบิกนี้?',
//                        'method' => 'post',
//                    ],
//                ]) ?>
<!--                --><?php //= Html::a('<i class="fas fa-times"></i> ปฏิเสธ', ['reject', 'id' => $model->id], [
//                    'class' => 'btn btn-danger',
//                    'data' => [
//                        'confirm' => 'คุณแน่ใจหรือไม่ที่จะปฏิเสธใบเบิกนี้?',
//                        'method' => 'post',
//                    ],
//                ]) ?>
            <?php endif; ?>

            <?php if (!$model->isNewRecord): ?>
                <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                    'class' => 'btn btn-info',
                    'target' => '_blank'
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>