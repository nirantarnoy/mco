<?php
/**
 * @var $this yii\web\View
 */

use yii\helpers\Html;

// Mock data
$receiptDate = date('m/d/Y');
$orderDate = date('m/d/Y',strtotime($model->purch_date));
$supplierName = \backend\models\Purch::findVendorName($model->vendor_id);
$supplierAddress = \backend\models\Purch::findVendorAddress($model->vendor_id);
$taxId = \backend\models\Purch::findVendorTaxID($model->vendor_id);
$poNumber = $model->purch_no;

// Items for inspection
//$items = [
//    [
//        'description' => 'กระดาษ A4 80 แกรม Double A',
//        'qty' => 50,
//        'unit' => 'รีม',
//        'inspection_result' => 'ผ่าน'
//    ],
//    [
//        'description' => 'หมึกพิมพ์ Canon Black (PG-740)',
//        'qty' => 10,
//        'unit' => 'ขวด',
//        'inspection_result' => 'ผ่าน'
//    ],
//    [
//        'description' => 'แฟ้มเอกสาร 2 นิ้ว สีน้ำเงิน',
//        'qty' => 30,
//        'unit' => 'แฟ้ม',
//        'inspection_result' => 'ผ่าน'
//    ],
//    [
//        'description' => 'ปากกาลูกลื่น 0.5 มม. สีน้ำเงิน',
//        'qty' => 100,
//        'unit' => 'ด้าม',
//        'inspection_result' => 'ผ่าน'
//    ],
//    [
//        'description' => 'กรรไกร 8 นิ้ว สแตนเลส',
//        'qty' => 5,
//        'unit' => 'อัน',
//        'inspection_result' => 'ผ่าน'
//    ],
//];

$items = [];
foreach($model_line as $line){
    $item = [
        'description' => \backend\models\Product::findProductName($line->product_id),
        'qty' => $line->qty,
        'unit' => $line->unit_id,
        'inspection_result' => '',
    ];
    $items[] = $item;
}

// Inspection details - array of booleans for each item (15 items x 15 checkpoints)
$inspectionMatrix = [];
for ($i = 0; $i < 11; $i++) {
    $inspectionMatrix[$i] = [];
    for ($j = 0; $j < 15; $j++) {
        // Mock data: most items pass inspection
        $inspectionMatrix[$i][$j] = ($i < count($items)) ? true : false;
    }
}

// Overall inspection results
$overallResult = 'accept_all'; // accept_all, accept_partial, reject_all
$hasCorrectBrand = true;
$hasCorrectSize = true;
$hasCorrectQty = true;
$hasCertificate = true;
$hasManual = false;

// Signatures
$purchasingRep = \backend\models\User::findEmployeeNameByUserId($model->created_by);
$requestor = getEmpRequestor($model->id);
$requestorRep = 'นายสมศักดิ์ ขอสินค้า';

function getEmpRequestor($id){
    $name = '';
    $modelx = \backend\models\PurchReq::find()->where(['purch_id'=>$id])->one();
    if($modelx){
        $name = \backend\models\User::findEmployeeNameByUserId($modelx->created_by);
    }
    return $name;
}
?>

<style>
    @media print {
        body {
            margin: 0;
            padding: 0;
        }
        .print-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0;
            padding: 10mm;
        }
    }

    .print-container {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        padding: 10mm;
        background: white;
        font-family: 'Sarabun', Arial, sans-serif;
        font-size: 14px;
        line-height: 1.3;
        color: #000;
    }

    .header-section {
        text-align: center;
        margin-bottom: 20px;
    }

    .company-header {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }

    .logo {
        max-width: 120px;
        margin-right: 20px;
    }

    .company-info {
        text-align: left;
    }

    .company-info h3 {
        margin: 0;
        font-size: 16px;
        font-weight: bold;
    }

    .company-info p {
        margin: 2px 0;
        font-size: 13px;
    }

    .form-title {
        font-size: 20px;
        font-weight: bold;
        margin: 15px 0;
    }

    .form-field {
        display: inline-block;
        margin-bottom: 8px;
    }

    .form-label {
        font-weight: normal;
        margin-right: 5px;
    }

    .form-value {
        border-bottom: 1px dotted #999;
        display: inline-block;
        min-width: 150px;
        padding: 0 5px;
        font-weight: normal;
    }

    .supplier-section {
        margin-bottom: 15px;
        text-align: left;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
        font-size: 13px;
    }

    .items-table th {
        background-color: #f0f0f0;
        font-weight: bold;
    }

    .items-table .description {
        text-align: left;
        padding-left: 8px;
    }

    .items-table .narrow {
        width: 30px;
    }

    .inspection-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .inspection-table td {
        border: 1px solid #000;
        padding: 3px;
        text-align: center;
        font-size: 12px;
        height: 20px;
    }

    .inspection-table .row-header {
        text-align: left;
        padding-left: 8px;
        font-weight: bold;
        width: 200px;
    }

    .inspection-table .check-cell {
        width: 25px;
    }

    .checkbox-group {
        display: inline-block;
        margin-right: 15px;
        margin-bottom: 5px;
    }

    .checkbox-group input[type="checkbox"] {
        margin-right: 5px;
    }

    .result-section {
        margin: 20px 0;
        padding: 10px;
        border: 1px solid #000;
    }

    .result-section h4 {
        margin: 0 0 10px 0;
        font-size: 14px;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        margin-top: 40px;
    }

    .signature-box {
        width: 30%;
        text-align: center;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        margin: 40px 10px 5px 10px;
    }

    .form-number {
        text-align: right;
        font-size: 12px;
        margin-top: 20px;
    }

    .notes-section {
        margin-top: 15px;
        font-size: 13px;
    }

    .date-field {
        text-align: right;
        margin-bottom: 15px;
    }
</style>

<div class="print-container">
    <!-- Header Section -->
    <div class="header-section">
        <div class="company-header">
            <img src="../../backend/web/uploads/logo/mco_logo.png" class="logo" alt="MCO Logo">
            <div class="company-info">
                <h3>บริษัท เอ็ม.ซี.โอ. จำกัด.</h3>
                <p>8/18 ถนนเกาะกลอย ต.เชิงเนิน อ.เมือง ระยอง 21000</p>
            </div>
        </div>
        <h2 class="form-title">ใบตรวจรับสินค้า/วัสดุ</h2>
    </div>

    <!-- Date and Supplier Info -->
    <div class="date-field">
        <span class="form-label">วันที่</span>
        <span class="form-value"><?= Html::encode($receiptDate) ?></span>
    </div>

    <div class="supplier-section">
        <div class="form-field" style="display: block; margin-bottom: 5px;">
            <span class="form-label">สินค้ามาจากบริษัท</span>
            <span class="form-value" style="min-width: 400px;"><?= Html::encode($supplierName) ?></span>
        </div>
        <div class="form-field" style="display: block; margin-bottom: 5px;">
            <span class="form-label">ที่อยู่</span>
            <span class="form-value" style="min-width: 500px;"><?= Html::encode($supplierAddress) ?></span>
        </div>
        <div class="form-field" style="display: block; margin-bottom: 5px;">
            <span class="form-value" style="min-width: 500px; margin-left: 30px;"></span>
        </div>
        <div class="form-field" style="display: block; margin-bottom: 5px;">
            <span class="form-label">เลขประจำตัวผู้เสียภาษี</span>
            <span class="form-value" style="min-width: 300px;"><?= Html::encode($taxId) ?></span>
        </div>
        <div class="form-field" style="display: block; margin-bottom: 5px;">
            <span class="form-label">ตามใบสั่งซื้อเลขที่ PO.NO.</span>
            <span class="form-value" style="min-width: 200px;"><?= Html::encode($poNumber) ?></span>
            <span class="form-label" style="margin-left: 50px;">สั่งซื้อวันที่</span>
            <span class="form-value" style="min-width: 150px;"><?= Html::encode($orderDate) ?></span>
        </div>
    </div>

    <!-- Items Table -->
    <p style="margin-bottom: 10px;"><strong>รายการสินค้าที่ส่ง ณ จุดรับ/ส่ง มีดังต่อไปนี้</strong></p>
    <table class="items-table">
        <thead>
        <tr>
            <th class="narrow">ลำดับที่</th>
            <th>รายละเอียด</th>
            <th style="width: 80px;">จำนวน</th>
            <th style="width: 100px;">ผลการตรวจ</th>
        </tr>
        </thead>
        <tbody>
        <?php for ($i = 0; $i < 11; $i++): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td class="description">
                    <?= isset($items[$i]) ? Html::encode($items[$i]['description']) : '&nbsp;' ?>
                </td>
                <td>
                    <?= isset($items[$i]) ? $items[$i]['qty'] . ' ' . $items[$i]['unit'] : '&nbsp;' ?>
                </td>
                <td>
                    <?= isset($items[$i]) ? Html::encode($items[$i]['inspection_result']) : '&nbsp;' ?>
                </td>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>

    <!-- Inspection Matrix Table -->
    <table class="inspection-table">
        <tr>
            <td class="row-header">&nbsp;</td>
            <?php for ($j = 1; $j <= 15; $j++): ?>
                <td class="check-cell"><?= $j ?></td>
            <?php endfor; ?>
            <td style="width: 80px;">บันทึก</td>
        </tr>
        <tr>
            <td class="row-header">1. สภาพทั่วไปของสินค้า/</td>
            <?php for ($j = 0; $j < 15; $j++): ?>
<!--                <td class="check-cell">--><?php //= isset($inspectionMatrix[0][$j]) && $inspectionMatrix[0][$j] ? '✓' : '' ?><!--</td>-->
                <td class="check-cell"><?= isset($inspectionMatrix[0][$j]) && $inspectionMatrix[0][$j] ? '' : '' ?></td>
            <?php endfor; ?>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="row-header">2. สินค้าถูกต้องตามใบสั่งซื้อ</td>
            <?php for ($j = 0; $j < 15; $j++): ?>
                <td class="check-cell">&nbsp;</td>
            <?php endfor; ?>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="row-header" style="padding-left: 20px;">2.1 ยี่ห้อและรุ่น</td>
            <?php for ($j = 0; $j < 15; $j++): ?>
                <td class="check-cell"><?= $hasCorrectBrand && $j < count($items) ? '' : '' ?></td>
            <?php endfor; ?>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="row-header" style="padding-left: 20px;">2.2 รูปร่างและขนาด</td>
            <?php for ($j = 0; $j < 15; $j++): ?>
                <td class="check-cell"><?= $hasCorrectSize && $j < count($items) ? '' : '' ?></td>
            <?php endfor; ?>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="row-header" style="padding-left: 20px;">2.3 จำนวนที่ส่ง</td>
            <?php for ($j = 0; $j < 15; $j++): ?>
                <td class="check-cell"><?= $hasCorrectQty && $j < count($items) ? '' : '' ?></td>
            <?php endfor; ?>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="row-header">3. เอกสารที่จัดส่งมาพร้อม</td>
            <?php for ($j = 0; $j < 15; $j++): ?>
                <td class="check-cell">&nbsp;</td>
            <?php endfor; ?>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="row-header" style="padding-left: 20px;">3.1 ใบ certificate</td>
            <?php for ($j = 0; $j < 15; $j++): ?>
                <td class="check-cell"><?= $hasCertificate && $j < 3 ? '' : '' ?></td>
            <?php endfor; ?>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td class="row-header" style="padding-left: 20px;">3.2 คู่มือการใช้งาน</td>
            <?php for ($j = 0; $j < 15; $j++): ?>
                <td class="check-cell"><?= $hasManual && $j < 2 ? '' : '' ?></td>
            <?php endfor; ?>
            <td>&nbsp;</td>
        </tr>
    </table>

    <!-- Inspection Result -->
    <div class="result-section">
        <h4>ผลการตรวจรับสินค้า</h4>
        <div>
            <div class="checkbox-group">
                <input type="checkbox" <?= $overallResult == 'accept_all' ? 'checked' : '' ?>>
                <label>ถูกต้องและยอมรับสินค้า</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" <?= $overallResult == 'accept_partial' ? 'checked' : '' ?>>
                <label>ไม่ถูกต้องและยอมรับสินค้าบางรายการ</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" <?= $overallResult == 'reject_all' ? 'checked' : '' ?>>
                <label>ไม่ถูกต้องและส่งคืนสินค้าทั้งหมด</label>
            </div>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div>ลงชื่อ</div>
            <div class="signature-line"></div>
            <div>(<?= Html::encode($purchasingRep) ?>)</div>
            <div>ตัวแทนหน่วยงานจัดซื้อ</div>
        </div>

        <div class="signature-box">
            <div>ลงชื่อ</div>
            <div class="signature-line"></div>
            <div>(<?= Html::encode($requestor) ?>)</div>
            <div>ตัวแทนผู้ขอซื้อ</div>
        </div>
        <div class="signature-box">
            <div>ลงชื่อ</div>
            <div class="signature-line"></div>
            <div>(........................................)</div>
            <div>&nbsp;</div>
        </div>
    </div>

    <!-- Form Number -->
    <div class="form-number">
        F-WP-FMA-002-009 Rev.1
    </div>
</div>

<script>
    // Auto print when page loads (optional)
    // window.onload = function() {
    //     window.print();
    // };
</script>