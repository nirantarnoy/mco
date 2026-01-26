<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

$this->title = 'บันทึกรับเงิน: ' . $invoice->invoice_number;
?>
<div class="invoice-payment-form">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-dollar-sign"></i> <?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><th width="120">ลูกค้า:</th><td><?= Html::encode($invoice->customer_name) ?></td></tr>
                        <tr><th>เลขที่เอกสาร:</th><td><span class="badge badge-warning"><?= $invoice->invoice_number ?></span></td></tr>
                        <tr><th>ยอดเงินใบเสร็จ:</th><td><strong><?= number_format($invoice->total_amount, 2) ?></strong> บาท</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <?php
                    $total_paid = array_sum(array_column($history, 'amount'));
                    $remaining = $invoice->total_amount - $total_paid;
                    ?>
                    <div class="text-right p-3" style="background: #f8f9fa; border-radius: 8px;">
                        <span class="text-muted">ยอดรับแล้ว:</span> <span class="text-success h4 font-weight-bold"><?= number_format($total_paid, 2) ?></span><br>
                        <span class="text-muted">ยอดคงค้าง:</span> <span class="text-danger h4 font-weight-bold"><?= number_format($remaining, 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="card bg-light">
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($model, 'payment_date')->widget(DatePicker::classname(), [
                                'options' => ['placeholder' => 'เลือกวันที่', 'value' => date('Y-m-d')],
                                'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayHighlight' => true]
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'value' => $remaining > 0 ? $remaining : 0]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'payment_method')->dropDownList([
                                'เงินสด' => 'เงินสด',
                                'โอนเงินธนาคาร' => 'โอนเงินธนาคาร',
                                'เช็ค' => 'เช็ค',
                            ], ['prompt' => '-- เลือกช่องทาง --']) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'file')->fileInput(['class' => 'form-control-file mt-2']) ?>
                        </div>
                    </div>
                    <div class="text-right">
                        <?= Html::submitButton('<i class="fas fa-save"></i> บันทึกรายการ', ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <h5 class="mt-4 mb-3"><i class="fas fa-history"></i> ประวัติการรับเงิน</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark text-center">
                    <tr>
                        <th width="60">#</th>
                        <th>วันที่รับเงิน</th>
                        <th>ช่องทางการชำระ</th>
                        <th class="text-right">ยอดเงิน (บาท)</th>
                        <th width="150">ไฟล์แนบ</th>
                        <th width="100">จัดการ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($history): ?>
                        <?php foreach ($history as $index => $item): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td class="text-center"><?= Yii::$app->formatter->asDate($item->payment_date) ?></td>
                                <td class="text-center"><?= Html::encode($item->payment_method) ?></td>
                                <td class="text-right font-weight-bold text-primary"><?= number_format($item->amount, 2) ?></td>
                                <td class="text-center">
                                    <?php if ($item->attachment): ?>
                                        <?= Html::a('<i class="fas fa-paperclip"></i> ดูไฟล์', ['/' . $item->attachment], ['target' => '_blank', 'class' => 'btn btn-outline-info btn-xs']) ?>
                                    <?php else: ?>
                                        <span class="text-muted small">ไม่มีไฟล์</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= Html::a('<i class="fas fa-trash-alt"></i>', ['delete-payment', 'id' => $item->id], [
                                        'class' => 'btn btn-danger btn-sm',
                                        'data' => [
                                            'confirm' => 'คุณแน่ใจว่าต้องการลบรายการรับเงินนี้?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted p-4">ยังไม่มีข้อมูลการบันทึกรับเงิน</td></tr>
                    <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-light">
                    <tr style="font-size: 1.1em; font-weight: bold;">
                        <td colspan="3" class="text-right">ยอดรับรวมทั้งสิ้น:</td>
                        <td class="text-right text-success"><?= number_format($total_paid, 2) ?></td>
                        <td colspan="2"></td>
                    </tr>
                    <?php if ($remaining > 0): ?>
                        <tr class="text-danger">
                            <td colspan="3" class="text-right">คงเหลือค้างชำระ:</td>
                            <td class="text-right"><?= number_format($remaining, 2) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    <?php endif; ?>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <?= Html::a('<i class="fas fa-chevron-left"></i> กลับหน้ารายการ', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>

<style>
    .btn-xs { padding: 1px 5px; font-size: 12px; line-height: 1.5; border-radius: 3px; }
    .table td { vertical-align: middle !important; }
</style>