<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Invoice */

$this->title = 'พิมพ์ใบกำกับภาษี - ' . $model->invoice_number;

// Add print styles that match the original form exactly
$this->registerCss("
@page {
    size: A4;
    margin: 10mm;
}

@media print {
    .no-print { display: none !important; }
    body { 
        margin: 0; 
        padding: 0; 
        font-family: 'Sarabun', 'TH SarabunPSK', Arial, sans-serif; 
        font-size: 13px;
        color: #000;
    }
    .print-container { 
        max-width: 100%; 
        box-shadow: none; 
        border: none; 
    }
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Sarabun', 'TH SarabunPSK', Arial, sans-serif;
    font-size: 13px;
    line-height: 1.3;
    color: #000;
    background: #fff;
}

.print-container {
    max-width: 210mm;
    margin: 0 auto;
    background: white;
    padding: 15px;
    border: 1px solid #ddd;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

/* Header Section */
.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    border-bottom: 2px solid #000;
    padding-bottom: 10px;
}

.company-logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo {
    width: 70px;
    height: 70px;
    background: linear-gradient(45deg, #d32f2f, #f57c00);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    font-weight: bold;
}

.company-info {
    flex: 1;
    margin-left: 12px;
}

.company-name-thai {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 2px;
}

.company-name-eng {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 3px;
}

.company-address {
    font-size: 11px;
    line-height: 1.2;
    margin-bottom: 2px;
}

.invoice-info {
    text-align: center;
    min-width: 180px;
}

.invoice-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 5px;
}

.invoice-subtitle {
    font-size: 14px;
    margin-bottom: 10px;
}

/* Customer Section */
.customer-section {
    display: flex;
    justify-content: space-between;
    margin: 15px 0;
    gap: 20px;
}

.customer-left, .customer-right {
    flex: 1;
}

.field-group {
    margin-bottom: 6px;
    display: flex;
    align-items: center;
}

.field-label {
    min-width: 120px;
    font-weight: bold;
    font-size: 12px;
}

.field-value {
    border-bottom: 1px solid #000;
    flex: 1;
    padding: 2px 5px;
    min-height: 18px;
    font-size: 12px;
}

/* Items Table */
.items-section {
    margin: 20px 0;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
    font-size: 12px;
}

.items-table th,
.items-table td {
    border: 1px solid #000;
    padding: 6px 4px;
    text-align: center;
    vertical-align: middle;
}

.items-table th {
    background-color: #f8f9fa;
    font-weight: bold;
    height: 35px;
    font-size: 11px;
}

.items-table td {
    height: 30px;
}

.items-table .text-left { text-align: left; padding-left: 8px; }
.items-table .text-right { text-align: right; padding-right: 8px; }

/* Empty rows */
.empty-row {
    height: 40px;
}

/* Summary Section */
.summary-section {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.summary-left {
    flex: 1;
    border: 1px solid #000;
    padding: 10px;
    height: fit-content;
}

.summary-right {
    width: 300px;
    border: 1px solid #000;
    padding: 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 10px;
    border-bottom: 1px solid #000;
    font-size: 12px;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row.total {
    font-weight: bold;
    background-color: #f0f0f0;
}

.amount-text {
    margin-top: 5px;
    font-size: 11px;
}

/* Notes Section */
.notes-section {
    margin: 15px 0;
    font-size: 11px;
    line-height: 1.4;
}

.notes-title {
    font-weight: bold;
    margin-bottom: 5px;
}

.note-item {
    margin-bottom: 3px;
    text-indent: 15px;
}

/* Signature Section */
.signature-section {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.signature-box {
    flex: 1;
    border: 1px solid #000;
    padding: 15px 10px;
    text-align: center;
    height: 120px;
    position: relative;
}

.signature-title {
    font-weight: bold;
    margin-bottom: 10px;
    font-size: 12px;
}

.signature-line {
    border-bottom: 1px solid #000;
    margin: 40px 10px 10px;
}

.signature-date {
    font-size: 11px;
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
}

/* Print controls */
.print-controls {
    margin-bottom: 20px;
    text-align: center;
}

.btn {
    padding: 10px 20px;
    margin: 0 5px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
}

.btn-primary { background-color: #007bff; color: white; }
.btn-success { background-color: #28a745; color: white; }
.btn-secondary { background-color: #6c757d; color: white; }
.btn:hover { opacity: 0.8; }

/* Utilities */
.text-center { text-align: center; }
.text-right { text-align: right; }
.font-bold { font-weight: bold; }
");

// Auto print when page loads
$this->registerJs("
window.onload = function() {
    setTimeout(function() {
        window.print();
    }, 1000);
};
");
?>

<div class="print-controls no-print">
    <button onclick="window.print()" class="btn btn-primary">
        🖨️ พิมพ์
    </button>
    <button onclick="window.close()" class="btn btn-secondary">
        ❌ ปิด
    </button>
    <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-success">
        👁️ ดูรายละเอียด
    </a>
</div>

<div class="print-container">
    <!-- Header -->
    <div class="header">
        <div class="company-logo">
            <div class="logo">
                <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png',['style' => 'max-width: 120px;']) ?>
            </div>
            <div class="company-info">
                <div class="company-name-thai">บริษัท เอ็ม. ซี. โอ. จำกัด (สำนักงานใหญ่)</div>
                <div class="company-name-eng">M. C. O. COMPANY LIMITED</div>
                <div class="company-address">
                    8/18 ถ.เกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000 โทร 66-(0)-38875258-59 แฟ๊กซ์ 66-(0)-3861-9559
                </div>
                <div class="company-address">
                    8/18 Koh-Kloy-Rd., Cherngnoen, Muang, Rayong 21000 Tel. 66-(0)3887-5258-59 Fax. 66-(0)3861-9559
                </div>
            </div>
        </div>
        <div class="invoice-info">
            <div class="invoice-title">ใบกำกับภาษี</div>
            <div class="invoice-subtitle">Tax Invoice</div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-section">
        <div class="customer-left">
            <div class="field-group">
                <span class="field-label">เลขประจำตัวผู้เสียภาษี:</span>
                <span class="field-value">0215543000985</span>
            </div>
            <div class="field-group">
                <span class="field-label">รหัสลูกค้า / Code:</span>
                <span class="field-value"><?= Html::encode($model->quotation->customer->code ?: '') ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">ขายให้ / Sold To:</span>
                <span class="field-value"><?= Html::encode($model->customer_name ?: '') ?></span>
            </div>
            <div class="field-group">
                <span class="field-label"></span>
                <span class="field-value"><?= Html::encode($model->customer_address ?: '') ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">TAXID:</span>
                <span class="field-value"><?= Html::encode($model->customer_tax_id ?: '') ?></span>
            </div>
        </div>
        <div class="customer-right">
            <div class="field-group">
                <span class="field-label">วันที่ / Date:</span>
                <span class="field-value"><?= Yii::$app->formatter->asDate($model->invoice_date, 'MM/dd/yyyy') ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">เลขที่ / Inv.No.:</span>
                <span class="field-value"><?= Html::encode($model->invoice_number) ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">ใบสั่งซื้อเลขที่ / P/O No.:</span>
                <span class="field-value"><?= Html::encode($model->po_number ?: '') ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">วันที่สั่งซื้อ / P/O Date:</span>
                <span class="field-value"><?= $model->po_date ? Yii::$app->formatter->asDate($model->po_date, 'MM/dd/yyyy') : '' ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">เงื่อนไข / กำหนดชำระ / Credit, Due:</span>
                <span class="field-value"><?= Html::encode($model->paymentTerm->name ?: '') ?> <?= $model->due_date ? Yii::$app->formatter->asDate($model->due_date, 'd/M/yy') : '' ?></span>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="items-section">
        <table class="items-table">
            <thead>
            <tr>
                <th style="width: 8%;">ลำดับ<br>Item</th>
                <th style="width: 40%;">รายการ<br>Description</th>
                <th style="width: 12%;">จำนวน<br>Quantity</th>
                <th style="width: 15%;">ราคาต่อหน่วย<br>Unit/Price</th>
                <th style="width: 15%;">จำนวนเงินรวม<br>Amount</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $model_line = \backend\models\InvoiceItem::find()->where(['invoice_id' => $model->id])->all();
            ?>
            <?php if (!empty($model_line)): ?>
                <?php foreach ($model_line as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td class="text-left"><?= nl2br(Html::encode($item->item_description)) ?></td>
                        <td><?= number_format($item->quantity, 0) ?> <?= Html::encode($item->unit) ?></td>
                        <td class="text-right"><?= number_format($item->unit_price, 3) ?></td>
                        <td class="text-right"><?= number_format($item->amount, 3) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default sample data -->
                <tr>
                    <td>1</td>
                    <td class="text-left">Service Charge of Observer Golden Spatula on Songkran Festival (15/04/2025)</td>
                    <td>1 JOB</td>
                    <td class="text-right">15,000.000</td>
                    <td class="text-right">15,000.000</td>
                </tr>
            <?php endif; ?>

            <!-- Empty rows for spacing -->
            <?php for ($i = count($model_line); $i < 10; $i++): ?>
                <tr class="empty-row">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-left">
            <div class="font-bold">ตัวอักษร</div>
            <div class="amount-text"><?= $model->total_amount_text ?: '' ?></div>

            <div style="margin-top: 30px; font-size: 12px; text-align: center;">
                <strong>ได้ตรวจรับสินค้าตามรายการข้างต้นถูกต้อง</strong>
            </div>
        </div>
        <div class="summary-right">
            <div class="summary-row">
                <span>ส่วนลด / Discount</span>
                <span><?= number_format($model->discount_amount, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>รวมเงิน / Total</span>
                <span><?= number_format($model->subtotal, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>ภาษีมูลค่าเพิ่ม / VAT <?= $model->vat_percent ?>%</span>
                <span><?= number_format($model->vat_amount, 2) ?></span>
            </div>
            <div class="summary-row total">
                <span>รวมเงินทั้งสิ้น / TOTAL</span>
                <span><?= number_format($model->total_amount, 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="notes-section">
        <div class="notes-title">หมายเหตุ :</div>
        <div class="note-item">1. ตามรายการข้างต้น แม้จะได้ส่งมอบสินค้าแก่ผู้ซื้อแล้วก็ยังเป็นทรัพย์สินของบริษัทฯ จนกว่าจะได้รับชำระเงินครบถ้วน</div>
        <div class="note-item">2. สินค้าที่ซื้อไปเกินกว่า 7 วัน ทางบริษัทฯ ใคร่ขอสงวนสิทธิ์ไม่รับคืนสินค้า และคิดดอกเบี้ยร้อยละ 1.5 ต่อเดือน</div>
        <div class="note-item">3. สามารถชำระผ่านช่องทางธนาคารกรุงเทพจำกัด (มหาชน) สาขาระยอง ชื่อบัญชี บจ.เอ็ม.ซี.โอ. เลขบัญชี 277-3-02318-5 บัญชีกระแสรายวัน</div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-title">ได้ตรวจรับสินค้าตามรายการข้างต้นถูกต้อง</div>
            <div class="signature-line"></div>
            <div class="signature-title">ผู้รับสินค้า / Received By</div>
            <div class="signature-date">วันที่ / Date ___/____/____</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">&nbsp;</div>
            <div class="signature-line"></div>
            <div class="signature-title">ผู้ส่งสินค้า / Delivery By</div>
            <div class="signature-date">วันที่ / Date ___/____/____</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">&nbsp;</div>
            <div class="signature-line"></div>
            <div class="signature-title">ผู้มีอำนาจลงนาม / Authorized Signature</div>
            <div class="signature-date">วันที่ / Date ___/____/____</div>
        </div>
    </div>
</div>