<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\TempInvoice $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Temp Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="temp-invoice-view">

<?php
    // Document type detection logic from raw text
    $rawText = mb_strtolower($model->raw_text, 'UTF-8');
    $detectedType = null;
    
    // Pattern matching for document type
    if (strpos($rawText, 'ใบกำกับภาษี') !== false || strpos($rawText, 'tax invoice') !== false) {
        $detectedType = 'tax_invoice';
    } elseif (strpos($rawText, 'ใบเสร็จ') !== false || strpos($rawText, 'receipt') !== false) {
        $detectedType = 'receipt';
    } elseif (strpos($rawText, 'ใบวางบิล') !== false || strpos($rawText, 'billing note') !== false) {
        $detectedType = 'bill_placement';
    } elseif (strpos($rawText, 'ใบแจ้งหนี้') !== false || strpos($rawText, 'invoice') !== false || strpos($rawText, 'quotation') !== false) {
        $detectedType = 'quotation';
    }

    $getBtnClass = function($type) use ($detectedType) {
        return $detectedType === $type ? 'btn btn-success shadow-sm font-weight-bold' : 'btn btn-outline-primary';
    };
    ?>

    <?php if ($detectedType): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> ระบบตรวจพบว่าเอกสารนี้อาจเป็น: <strong><?= \backend\models\Invoice::getTypeOptions()[$detectedType] ?? 'ไม่ระบุ' ?></strong> (แนะนำให้เลือกสร้างเอกสารประเภทนี้)
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <div class="btn-group" role="group">
            <?= Html::a('<i class="fas fa-file-invoice-dollar"></i> สร้างใบสั่งซื้อ (PO)', ['purch/create', 'ocr_id' => $model->id], ['class' => 'btn btn-outline-success']) ?>
        </div>
        
        <div class="btn-group ml-2" role="group">
            <?= Html::a('<i class="fas fa-file-alt"></i> สร้างใบแจ้งหนี้', ['invoice/create', 'type' => 'quotation', 'ocr_id' => $model->id], ['class' => $getBtnClass('quotation')]) ?>
            <?= Html::a('<i class="fas fa-file-invoice"></i> สร้างใบวางบิล', ['invoice/create', 'type' => 'bill_placement', 'ocr_id' => $model->id], ['class' => $getBtnClass('bill_placement')]) ?>
            <?= Html::a('<i class="fas fa-file-signature"></i> สร้างใบกำกับภาษี', ['invoice/create', 'type' => 'tax_invoice', 'ocr_id' => $model->id], ['class' => $getBtnClass('tax_invoice')]) ?>
            <?= Html::a('<i class="fas fa-receipt"></i> สร้างใบเสร็จ', ['invoice/create', 'type' => 'receipt', 'ocr_id' => $model->id], ['class' => $getBtnClass('receipt')]) ?>
        </div>
        
        <div class="float-right">
            <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
        <div class="clearfix"></div>
    </div>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'invoice_number',
            'invoice_date:date',
            'vendor_name',
            'customer_name',
            'customer_tax_id',
            [
                'attribute' => 'total_amount',
                'value' => number_format($model->total_amount, 2),
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function($model) {
                    if ($model->status == 0) return '<span class="badge badge-warning">รอยืนยัน</span>';
                    if ($model->status == 1) return '<span class="badge badge-success">ยืนยันแล้ว</span>';
                    return '<span class="badge badge-danger">ยกเลิก</span>';
                }
            ],
        ],
    ]) ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">รายการสินค้า</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr class="bg-light">
                                <th style="width: 50px">#</th>
                                <th>รหัสสินค้า</th>
                                <th>รายละเอียด</th>
                                <th class="text-right">จำนวน</th>
                                <th>หน่วย</th>
                                <th class="text-right">ราคา/หน่วย</th>
                                <th class="text-right">จำนวนเงิน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($model->tempInvoiceLines as $index => $line): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= Html::encode($line->product_code) ?></td>
                                    <td><?= Html::encode($line->description) ?></td>
                                    <td class="text-right"><?= number_format($line->quantity, 2) ?></td>
                                    <td><?= Html::encode($line->unit) ?></td>
                                    <td class="text-right"><?= number_format($line->unit_price, 2) ?></td>
                                    <td class="text-right"><?= number_format($line->amount, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($model->tempInvoiceLines)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">ไม่พบรายการสินค้า</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                             <tr class="bg-light">
                                <th colspan="6" class="text-right">รวมเงิน (Subtotal)</th>
                                <th class="text-right"><?= number_format($model->subtotal, 2) ?></th>
                            </tr>
                            <tr class="bg-light">
                                <th colspan="6" class="text-right">ภาษีมูลค่าเพิ่ม (VAT 7%)</th>
                                <th class="text-right"><?= number_format($model->vat_amount, 2) ?></th>
                            </tr>
                            <tr class="bg-primary text-white">
                                <th colspan="6" class="text-right">ยอดเงินรวมสุทธิ (Total)</th>
                                <th class="text-right"><?= number_format($model->total_amount, 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-secondary text-white">
            <h3 class="card-title">ข้อความดิบจากการ OCR</h3>
        </div>
        <div class="card-body">
            <pre class="bg-light p-3" style="white-space: pre-wrap; font-size: 12px;"><?= Html::encode($model->raw_text) ?></pre>
        </div>
    </div>
</div>
