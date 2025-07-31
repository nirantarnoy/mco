<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Invoice */

$this->title = 'พิมพ์ใบแจ้งหนี้ - ' . $model->invoice_number;

// Add print styles that match the original form exactly
$this->registerCss("
@page {
    size: A4;
    margin: 0.5in;
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
        width: 100%;
    }
}

.print-container {
    max-width: 210mm;
    margin: 0 auto;
    background: white;
    padding: 15mm;
    border: 1px solid #ddd;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

/* Header Section */
.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
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
    min-width: 200px;
}

.invoice-title {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 5px;
}

.invoice-subtitle {
    font-size: 12px;
    margin-bottom: 5px;
    color: #666;
}

.not-tax-invoice {
    font-size: 12px;
    color: #666;
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
    align-items: flex-start;
}

.field-label {
    min-width: 100px;
    font-weight: bold;
    font-size: 12px;
}

.field-value {
    border-bottom: 1px solid #000;
    flex: 1;
    padding: 2px 5px;
    min-height: 18px;
    font-size: 12px;
    line-height: 1.3;
}

/* Items Table */
.items-section {
    margin: 20px 0;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    border: 2px solid #000;
    font-size: 12px;
}

.items-table th,
.items-table td {
    border: 1px solid #000;
    padding: 8px 4px;
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
    height: 35px;
}

/* Summary Section */
.summary-section {
    margin-top: 15px;
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.summary-left {
    flex: 1;
    padding: 10px;
}

.summary-right {
    width: 250px;
    border: 1px solid #000;
    padding: 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 10px;
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
    font-size: 12px;
    margin-bottom: 10px;
    font-weight: bold;
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
    gap: 10px;
}

.signature-box {
    flex: 1;
    border: 1px solid #000;
    padding: 10px;
    text-align: center;
    height: 100px;
    position: relative;
}

.signature-title {
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 11px;
}

.signature-line {
    border-bottom: 1px solid #000;
    margin: 25px 5px 10px;
}

.signature-date {
    font-size: 10px;
    position: absolute;
    bottom: 5px;
    left: 50%;
    transform: translateX(-50%);
}

/* Utilities */
.text-center { text-align: center; }
.text-right { text-align: right; }
.text-left { text-align: left; }
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

<div class="no-print text-center mb-4">
    <div class="btn-group">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> พิมพ์
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> ปิด
        </button>
        <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-info">
            <i class="fas fa-eye"></i> ดูรายละเอียด
        </a>
    </div>
</div>

<div class="print-container">
    <!-- Header -->
    <div class="header">
        <div class="company-logo">
            <div class="logo">
                <?= Html::img('../../backend/web/uploads/logo/mco_logo.png',['style' => 'max-width: 120px;']) ?>
            </div>
            <div class="company-info">
                <div class="company-name-thai">บริษัท เอ็ม. ซี. โอ. จำกัด (สำนักงานใหญ่)</div>
                <div class="company-name-eng">M.C.O. COMPANY LIMITED</div>
                <div style="font-weight: bold; margin: 3px 0;">TAXID: 0215543000985</div>
                <div class="company-address">
                    8/18 ถ.เกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000 โทร 66-(0)-38875258-59 แฟ๊กซ์66-(0)-3861-9559
                </div>
                <div class="company-address">
                    8/18 Koh-Kloy-Rd., Cherngnoen, Muang, Rayong 21000 Tel. 66-(0)3887-5258-59 Fax. 66-(0)3861-9559
                </div>
            </div>
        </div>
        <div class="invoice-info">
            <div class="invoice-title">ใบแจ้งหนี้/ใบส่งสินค้า-บริการ</div>
            <div class="not-tax-invoice">(ไม่ใช่ใบกำกับภาษี)</div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-section">
        <div class="customer-left">
            <div class="field-group">
                <span class="field-label">รหัสลูกค้า / Code:</span>
                <span class="field-value"><?= Html::encode($model->quotation->customer->code ?: '') ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">ขายให้ / Sold To:</span>
                <span class="field-value"><?= Html::encode($model->customer_name ?: 'Glow Energy Public Company Limited (Head Office)') ?></span>
            </div>
            <div class="field-group">
                <span class="field-label"></span>
                <span class="field-value"><?= Html::encode($model->customer_address ?: '555/2 Energy Complex Building B, 5th Floor, Vibhavadi-Rangsit Road, Chatuchak, Chatuchak, Bangkok 10900') ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">เลขประจำตัวผู้เสียภาษี:</span>
                <span class="field-value"><?= Html::encode($model->customer_tax_id ?: '0107538000461') ?></span>
            </div>
        </div>
        <div class="customer-right">
            <div class="field-group">
                <span class="field-label">วันที่ / Date:</span>
                <span class="field-value"><?= Yii::$app->formatter->asDate($model->invoice_date, 'MM/dd/yyyy') ?></span>
            </div>
            <div class="field-group">
                <span class="field-label">เลขที่ / In.No.:</span>
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
                <span class="field-value"><?= Html::encode($model->paymentTerm->name ?: '') ?></span>
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
            <?php if (!empty($model->items)): ?>
                <?php foreach ($model->items as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td class="text-left"><?= nl2br(Html::encode($item->item_description)) ?></td>
                        <td><?= number_format($item->quantity, 0) ?> <?= Html::encode($item->unit) ?></td>
                        <td class="text-right"><?= number_format($item->unit_price, 2) ?></td>
                        <td class="text-right"><?= number_format($item->amount, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default sample data -->
                <tr>
                    <td>1</td>
                    <td class="text-left">PM O2 Analyzer CFB3 Feb 2025</td>
                    <td>3 JOB</td>
                    <td class="text-right">6,400.00</td>
                    <td class="text-right">19,200.00</td>
                </tr>
            <?php endif; ?>

            <!-- Empty rows for spacing -->
            <?php for ($i = count($model->items); $i < 10; $i++): ?>
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
            <div class="amount-text">(ตัวอักษร)</div>
            <div style="font-size: 14px; font-weight: bold;">
                <?= $model->total_amount_text ?: 'สองหมื่นห้าร้อยสี่สิบสี่บาทถ้วน' ?>
            </div>

            <div style="margin-top: 20px; font-size: 12px; text-align: center;">
                <strong>ได้ตรวจรับสินค้าตามรายการข้างต้นถูกต้อง</strong>
            </div>
        </div>
        <div class="summary-right">
            <div class="summary-row">
                <span>รวมเงิน / Total</span>
                <span><?= number_format($model->subtotal, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>ภาษีมูลค่าเพิ่ม / Vat <?= $model->vat_percent ?>%</span>
                <span><?= number_format($model->vat_amount, 2) ?></span>
            </div>
            <div class="summary-row total">
                <span>รวมเงินทั้งสิ้น / Grand Total</span>
                <span><?= number_format($model->total_amount, 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="notes-section">
        <div class="notes-title">หมายเหตุ :</div>
        <div class="note-item">1. ตามรายการข้างต้น แม้จะได้ส่งมอบสินค้าแก่ผู้ซื้อแล้วก็ยังเป็นทรัพย์สินของผู้ขายจนกว่าผู้ซื้อจะได้รับชำระเงิน</div>
        <div class="note-item">2. สินค้าที่ซื้อไปเกินกว่า 7 วัน ทางบริษัทฯใคร่ขอสงวนสิทธิ์ไม่รับคืนสินค้าและคิดดอกเบี้ยร้อยละ1.5 ต่อเดือน</div>
        <div class="note-item">3. สามารถชำระผ่านช่องทางธนาคารกรุงเทพจำกัด (มหาชน) สาขาระยอง ชื่อบัญชีบจ.เอ็ม.ซี.โอ. เลขบัญชี277-3-02318-5 บัญชีกระแสรายวัน</div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-title">ผู้รับสินค้า / Received By</div>
            <div class="signature-line"></div>
            <div class="signature-date">วันที่ / Date______/_________/________</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">ผู้ส่งสินค้า / Send By</div>
            <div class="signature-line"></div>
            <div class="signature-date">วันที่ / Date______/_________/________</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">ผู้มีอำนาจลงนาม / Authorized Signature</div>
            <div class="signature-line"></div>
            <div class="signature-date">วันที่ / Date______/_________/________</div>
        </div>
    </div>
</div>