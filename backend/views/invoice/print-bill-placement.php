<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Invoice */

$this->title = 'พิมพ์ใบวางบิล - ' . $model->invoice_number;

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
        font-size: 14px;
        color: #000;
    }
    .print-container { 
        max-width: 100%; 
        width: 100%;
        box-shadow: none;
        border: none;
    }
}

.print-container {
    max-width: 210mm;
    margin: 0 auto;
    background: white;
    padding: 20px;
    border: 1px solid #ddd;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

/* Header Section */
.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    border-bottom: 2px solid #000;
    padding-bottom: 15px;
}

.company-logo {
    display: flex;
    align-items: center;
    gap: 15px;
}

.logo {
    width: 80px;
    height: 80px;
    background: linear-gradient(45deg, #d32f2f, #f57c00);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: bold;
}

.company-info {
    flex: 1;
    margin-left: 15px;
}

.company-name {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 5px;
}

.company-address {
    font-size: 12px;
    line-height: 1.3;
    margin-bottom: 3px;
}

.company-contact {
    font-size: 12px;
    font-weight: bold;
}

.bill-info {
    text-align: right;
    min-width: 200px;
}

.bill-title {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 10px;
}

.bill-details {
    text-align: left;
}

.bill-detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    min-width: 250px;
}

.bill-detail-row strong {
    min-width: 120px;
}

/* Customer Section */
.customer-section {
    margin: 20px 0;
    border: 1px solid #000;
    padding: 15px;
}

.customer-title {
    font-weight: bold;
    margin-bottom: 10px;
}

.customer-details {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

.customer-left, .customer-right {
    width: 48%;
}

.customer-field {
    margin-bottom: 8px;
    display: flex;
}

.customer-field strong {
    min-width: 100px;
}

.customer-field span {
    border-bottom: 1px solid #000;
    flex: 1;
    min-height: 20px;
    padding-left: 5px;
}

/* Items Table */
.items-section {
    margin: 20px 0;
}

.items-title {
    font-weight: bold;
    margin-bottom: 10px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000;
}

.items-table th,
.items-table td {
    border: 1px solid #000;
    padding: 8px 5px;
    text-align: center;
    vertical-align: middle;
    font-size: 13px;
}

.items-table th {
    background-color: #f8f9fa;
    font-weight: bold;
    height: 40px;
}

.items-table td {
    height: 35px;
}

.items-table .text-left { text-align: left; }
.items-table .text-right { text-align: right; }

/* Empty rows for spacing */
.empty-row {
    height: 60px;
}

/* Total Section */
.total-section {
    margin-top: 0;
}

.total-row {
    background-color: #f0f0f0;
    font-weight: bold;
}

.total-amount {
    font-size: 16px;
    font-weight: bold;
}

/* Signature Section */
.signature-section {
    margin-top: 30px;
    display: flex;
    justify-content: space-between;
}

.signature-box {
    width: 45%;
    text-align: center;
}

.signature-label {
    font-weight: bold;
    margin-bottom: 20px;
}

.signature-line {
    border-bottom: 1px solid #000;
    height: 50px;
    margin-bottom: 10px;
    position: relative;
}

.signature-date {
    font-size: 12px;
}

/* Payment info */
.payment-info {
    margin: 20px 0;
    display: flex;
    justify-content: space-between;
}

.payment-field {
    display: flex;
    align-items: center;
    gap: 10px;
}

.payment-field strong {
    min-width: 120px;
}

.payment-field span {
    border-bottom: 1px solid #000;
    min-width: 150px;
    height: 20px;
}

/* Print buttons */
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

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn:hover {
    opacity: 0.8;
}
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
    <div class="btn-group">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> พิมพ์
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> ปิด
        </button>
        <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-success">
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
                <div class="company-name">บริษัท เอ็ม.ซี.โอ. จำกัด</div>
                <div class="company-address">
                    8/18 ถนนเกาะกลอย ตำบลเชิงเนิน อำเภอเมืองจังหวัดระยอง 21000
                </div>
                <div class="company-contact">
                    Tel : (038) 875258-9, Fax : (038) 619559
                </div>
            </div>
        </div>
        <div class="bill-info">
            <div class="bill-title">ใบวางบิล</div>
            <div class="bill-details">
                <div class="bill-detail-row">
                    <strong>เลขที่ใบวางบิล:</strong>
                    <span><?= Html::encode($model->invoice_number) ?></span>
                </div>
                <div class="bill-detail-row">
                    <strong>วันที่ใบวางบิล:</strong>
                    <span><?= Yii::$app->formatter->asDate($model->invoice_date, 'dd-MMM-yy') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="customer-section">
        <div class="customer-title">ชื่อลูกค้า</div>
        <div class="customer-details">
            <div class="customer-left">
                <div class="customer-field">
                    <strong>ชื่อลูกค้า:</strong>
                    <span><?= Html::encode($model->customer_name ?: 'บริษัท มาลาอากาโซโลจิสติกส์ จำกัด (มหาชน)') ?></span>
                </div>
                <div class="customer-field">
                    <strong>ที่อยู่:</strong>
                    <span><?= Html::encode($model->customer_address ?: 'เลขที่ 2098 อาคารชาติน ทาวเวอร์ ชั้น 8 ถนนบุญญากรรม แขวงบางจาก') ?></span>
                </div>
                <div class="customer-field">
                    <strong></strong>
                    <span><?= Html::encode($model->customer_address ? '' : 'เขตพระโขนง กรุงเทพมหานคร 10260 สาขา 00000') ?></span>
                </div>
                <div class="customer-field">
                    <strong>เลขประจำตัวผู้เสียภาษี:</strong>
                    <span><?= Html::encode($model->customer_tax_id ?: '0107536000269') ?></span>
                </div>
            </div>
            <div class="customer-right">
                <div class="customer-field">
                    <strong>นัดชำระเงินวันที่:</strong>
                    <span><?= $model->payment_due_date ? Yii::$app->formatter->asDate($model->payment_due_date, 'MM/dd/yyyy') : '' ?></span>
                </div>
                <div class="customer-field">
                    <strong>วันนัดรับเช็ค:</strong>
                    <span><?= $model->check_due_date ? Yii::$app->formatter->asDate($model->check_due_date, 'MM/dd/yyyy') : '' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Section -->
    <div class="items-section">
        <div class="items-title">ดังรายการต่อไปนี้</div>
        <table class="items-table">
            <thead>
            <tr>
                <th style="width: 8%;">ลำดับที่</th>
                <th style="width: 25%;">หมายเลขใบสั่งซื้อ</th>
                <th style="width: 20%;">เลขที่เอกสารตั้งหนี้</th>
                <th style="width: 15%;">ลงวันที่</th>
                <th style="width: 15%;">ชำระเงินวันที่</th>
                <th style="width: 17%;">จำนวนเงิน</th>
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
                        <td class="text-left"><?= Html::encode($model->po_number ?: 'RY-UJ24-0002634 Signed') ?></td>
                        <td><?= Html::encode($model->invoice_number) ?></td>
                        <td><?= Yii::$app->formatter->asDate($model->invoice_date, 'MM/dd/yyyy') ?></td>
                        <td><?= $model->due_date ? Yii::$app->formatter->asDate($model->due_date, 'MM/dd/yyyy') : '' ?></td>
                        <td class="text-right"><?= number_format($item->amount, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default sample data -->
                <tr>
                    <td>1</td>
                    <td class="text-left">RY-UJ24-0002634 Signed</td>
                    <td>260/11-003</td>
                    <td>20-Nov-24</td>
                    <td>20-Dec-24</td>
                    <td class="text-right">53,500.00</td>
                </tr>
            <?php endif; ?>

            <!-- Empty rows for spacing -->
            <?php for ($i = count($model->items); $i < 12; $i++): ?>
                <tr class="empty-row"><td colspan="6">&nbsp;</td></tr>
            <?php endfor; ?>
            </tbody>
            <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-left">
                    <strong>รวมเงินทั้งสิ้น หึ่งหลื่นสามพันห้าร้อยบาทถ้วน</strong>
                </td>
                <td class="text-right total-amount"><?= number_format($model->total_amount, 2) ?></td>
            </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">ผู้รับวางบิล</div>
            <div class="signature-line"></div>
            <div class="signature-date">
                วันที่ _____ / _____ / _______
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-label">ผู้วางบิล</div>
            <div class="signature-line"></div>
            <div class="signature-date">
                วันที่ _____ / _____ / _______
            </div>
        </div>
    </div>
</div>

<script>
    // Sample data for dynamic generation (can be used with backend)
    const sampleBillData = {
        billNumber: '<?= Html::encode($model->invoice_number) ?>',
        billDate: '<?= Yii::$app->formatter->asDate($model->invoice_date, 'MM/dd/yyyy') ?>',
        customer: {
            name: '<?= Html::encode($model->customer_name) ?>',
            address: '<?= Html::encode($model->customer_address) ?>',
            taxId: '<?= Html::encode($model->customer_tax_id) ?>'
        },
        items: [
            <?php if (!empty($model->items)): ?>
            <?php foreach ($model->items as $index => $item): ?>
            {
                seq: <?= $index + 1 ?>,
                orderNumber: '<?= Html::encode($model->po_number) ?>',
                documentNumber: '<?= Html::encode($model->invoice_number) ?>',
                documentDate: '<?= Yii::$app->formatter->asDate($model->invoice_date, 'MM/dd/yyyy') ?>',
                paymentDate: '<?= $model->due_date ? Yii::$app->formatter->asDate($model->due_date, 'MM/dd/yyyy') : '' ?>',
                amount: <?= $item->amount ?>
            }<?= $index < count($model->items) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
            <?php endif; ?>
        ],
        totalAmount: <?= $model->total_amount ?>,
        paymentDueDate: '<?= $model->payment_due_date ? Yii::$app->formatter->asDate($model->payment_due_date, 'MM/dd/yyyy') : '' ?>',
        checkDueDate: '<?= $model->check_due_date ? Yii::$app->formatter->asDate($model->check_due_date, 'MM/dd/yyyy') : '' ?>'
    };

    // Function to populate data dynamically (for backend integration)
    function populateData(data) {
        // This function can be used to populate data dynamically if needed
        console.log('Bill data:', data);
    }

    // Initialize with current data
    populateData(sampleBillData);
</script>