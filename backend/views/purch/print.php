<?php
use yii\helpers\Html;

$this->title = 'Purchase Order - ' . $purchase->purch_no;

// คำนวณราคารวม
$subtotal = 0;
$discount = 0;
$tax_amount = 0;



$vat = 0;
$total = 0;

//if ($purchaseLines) {
//    foreach ($purchaseLines as $line) {
//        $subtotal += $line->line_total;
//    }
//}
//
//if($purchase->discount_per > 0){
//    $discount = $subtotal * ($purchase->discount_per / 100);
//}
//if($purchase->discount_amount > 0){
//    $discount += $purchase->discount_amount;
//}
//
//if($purchase->whd_tax_per > 0){
//    $tax_amount = ($subtotal - $discount) * ($purchase->whd_tax_per / 100);
//}
//
//// คำนวณ VAT 7%
//$netAmount = $subtotal - $discount;
//$vat = $netAmount * 0.07;
//$total = $netAmount + $vat - $tax_amount;

$vendor_info = \backend\models\Vendor::findVendorInfo($purchase->vendor_id);
$vendor_address = $vendor_info !== null ? $vendor_info['home_number'].' '.$vendor_info['street'].' '.$vendor_info['aisle'].', '.$vendor_info['district_name'].', '.$vendor_info['city_name'].', '.$vendor_info['province_name'].', '.$vendor_info['zipcode'] : '';
$phone = $vendor_info !== null ? $vendor_info['phone'] : '';
$email = $vendor_info !== null ? $vendor_info['email'] : '';
?>

<style>
    @media print {
        @page {
            size: A4;
            margin: 0.15in;
        }

        * {
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html, body {
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }

        .po-container {
            width: 100% !important;
            height: auto !important;
            min-height: auto !important;
            margin: 0 !important;
            padding: 8mm !important;
            transform: scale(0.95);
            transform-origin: top left;
            page-break-inside: avoid;
            page-break-before: auto;
            page-break-after: auto;
            position: relative;
            background: white !important;
        }

        .no-print {
            display: none !important;
        }

        /* เพิ่มคุณภาพสำหรับ PDF */
        .items {
            border-collapse: collapse !important;
        }

        .items,
        .items th,
        .items td {
            border: 1px solid #000 !important;
            border-collapse: collapse !important;
        }

        .summary-table,
        .summary-table td {
            border: 1px solid #000 !important;
        }

        .signature-line {
            border-bottom: 1px solid #000 !important;
        }

        /* รักษาคุณภาพของรูปภาพ */
        img {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }

        /* ปรับขนาดฟอนต์และระยะห่าง */
        .po-container {
            font-size: 14px !important;
            line-height: 1.2 !important;
        }

        .header {
            margin-bottom: 12px !important;
        }

        .po-title {
            font-size: 20px !important;
        }

        .thai-title {
            font-size: 16px !important;
            margin-top: 3px !important;
        }

        .company-info {
            margin-bottom: 12px !important;
            line-height: 1.2 !important;
        }

        .company-name {
            font-size: 16px !important;
        }

        .info-section {
            margin-bottom: 12px !important;
        }

        .info-row {
            margin-bottom: 3px !important;
        }

        .info-label {
            min-width: 100px !important;
            font-size: 12px !important;
        }

        .info-value {
            font-size: 12px !important;
        }

        .items {
            margin: 12px 0 !important;
            font-size: 12px !important;
        }

        .items th {
            padding: 4px !important;
            font-size: 11px !important;
        }

        .items td {
            padding: 3px !important;
            height: 20px !important;
            font-size: 11px !important;
        }

        .summary-section {
            margin-top: 15px !important;
        }

        .summary-table td {
            padding: 3px !important;
            font-size: 12px !important;
        }

        .acknowledgment {
            margin: 8px 0 !important;
            font-size: 14px !important;
        }

        .signature-section {
            margin-top: 30px !important;
        }

        .signature-line {
            margin: 30px 0 8px 0 !important;
            min-height: 50px !important;
        }

        .signature-line img {
            max-width: 120px !important;
            max-height: 45px !important;
            object-fit: contain;
        }

        .terms {
            margin-top: 15px !important;
            font-size: 12px !important;
            line-height: 1.3 !important;
        }

        .two-column {
            gap: 30px !important;
        }
    }

    .po-container {
        width: 210mm;
        margin: 0 auto;
        padding: 15mm;
        background: white;
        font-family: 'Sarabun', Arial, sans-serif;
        font-size: 16px;
        line-height: 1.4;
        color: #000;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .logo-section {
        display: flex;
        align-items: center;
    }

    .logo {
        font-size: 30px;
        font-weight: bold;
        margin-right: 10px;
    }

    .logo .m { color: #FFA500; }
    .logo .c { color: #000080; }
    .logo .o { color: #008000; }

    .th-flag {
        background: linear-gradient(to bottom, #FF0000 33%, #FFFFFF 33% 66%, #000080 66%);
        width: 30px;
        height: 20px;
        display: inline-block;
        margin-left: 5px;
    }

    .po-title {
        font-size: 24px;
        font-weight: bold;
    }

    .thai-title {
        font-size: 20px;
        margin-top: 5px;
    }

    .company-info {
        margin-bottom: 20px;
        line-height: 1.4;
    }

    .company-name {
        font-weight: bold;
        font-size: 18px;
    }

    .info-section {
        margin-bottom: 20px;
    }

    .info-row {
        display: flex;
        margin-bottom: 8px;
    }

    .info-label {
        font-weight: bold;
        min-width: 120px;
        font-size: 14px;
    }

    .info-value {
        border-bottom: 1px solid #ccc;
        flex: 1;
        padding-left: 5px;
        font-size: 14px;
    }

    .two-column {
        display: flex;
        gap: 50px;
    }

    .column {
        flex: 1;
    }

    table.items {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    table.items th {
        background: #87CEEB;
        border: 1px solid #000;
        padding: 6px;
        text-align: center;
        font-weight: bold;
        font-size: 13px;
    }

    table.items td {
        border: 1px solid #000;
        padding: 5px;
        text-align: center;
        height: 25px;
        font-size: 13px;
    }

    .description-cell {
        text-align: left !important;
    }

    .number-cell {
        text-align: right !important;
    }

    .summary-section {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }

    .summary-left {
        width: 60%;
    }

    .summary-right {
        width: 35%;
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
    }

    .summary-table td {
        padding: 5px;
        border: 1px solid #000;
        font-size: 14px;
    }

    .summary-label {
        text-align: right;
        font-weight: bold;
        background: #87CEEB;
    }

    .acknowledgment {
        text-align: center;
        margin: 10px 0;
        font-weight: bold;
        font-size: 16px;
    }

    .signature-section {
        display: flex;
        justify-content: space-around;
        margin-top: 40px;
        text-align: center;
    }

    .signature-box {
        width: 30%;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        margin: 40px 0 10px 0;
        min-height: 60px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        position: relative;
    }

    .signature-line img {
        max-width: 140px;
        max-height: 55px;
        object-fit: contain;
        position: absolute;
        bottom: 5px;
        left: 50%;
        transform: translateX(-50%);
    }

    .terms {
        margin-top: 20px;
        font-size: 14px;
        line-height: 1.4;
    }

    .logo-img {
        max-width: 180px;
        margin-bottom: 10px;
    }
</style>

<div class="po-container">
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <div class="logo">
                <img src="../../backend/web/uploads/logo/mco_logo_2.png" class="logo-img" alt="">
            </div>
        </div>
        <div style="text-align: right;">
            <div class="po-title">PURCHASE ORDER</div>
            <div class="thai-title">ใบสั่งซื้อ</div>
        </div>
    </div>

    <div style="display: flex; gap: 30px;">
        <div style="flex: 1;">
            <!-- Company Info -->
            <div class="company-info">
                <div class="company-name">M.C.O. COMPANY LIMITED</div>
                <div>บริษัท เอ็ม.ซี.โอ. จำกัด</div>
                <div>8/18 ถนนเกาะกลอย ตำบลเชิงเนิน อำเภอเมือง จ.ระยอง 21000</div>
                <div>เลขประจำตัวผู้เสียภาษี  0215543000985  </div>
                <div>Tel : (038) 875258-9,0946984555</div>
                <div>e-mail : info@thai-mco.com</div>
                <div><strong>SUPPLIER : <?= Html::encode($purchase->vendor->name ?? '') ?></strong><br /><?= Html::encode($vendor_address) ?></div>
            </div>
        </div>
        <div style="flex: 1;">
            <!-- PO Details -->
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">PO.NO. :</span>
                    <span class="info-value"><?= Html::encode($purchase->purch_no) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">PR.NO. :</span>
                    <span class="info-value"><?= Html::encode(\backend\models\Purch::findPrNo($purchase->id)) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">DATE :</span>
                    <span class="info-value"><?= Yii::$app->formatter->asDate($purchase->purch_date, 'php:d/m/Y') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">PAGE :</span>
                    <span class="info-value">1</span>
                </div>
                <div class="info-row">
                    <span class="info-label">EXPENSE JOB NO. :</span>
                    <span class="info-value"><?= Html::encode(\backend\models\Job::findJobNo($purchase->job_id) ?? '') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="two-column">
            <div class="column">
                <div class="info-row">
                    <span class="info-label">TEL :</span>
                    <span class="info-value"><?= Html::encode($phone) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">FAX :</span>
                    <span class="info-value"></span>
                </div>
                <div class="info-row">
                    <span class="info-label">CONTACT :</span>
                    <span class="info-value"></span>
                </div>
            </div>
            <div class="column">
                <div class="info-row">
                    <span class="info-label">CURRENCY :</span>
                    <span class="info-value"><?=\backend\models\Currency::findName($purchase->currency_id);?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">REF. NO. :</span>
                    <span class="info-value"><?= Html::encode($purchase->ref_no ?? '') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items">
        <thead>
        <tr>
            <th style="width: 5%;">ITEM</th>
            <th style="width: 15%;">CODE NO.</th>
            <th style="width: 35%;">DESCRIPTION</th>
            <th style="width: 10%;">P/N</th>
            <th style="width: 8%;">QTY</th>
            <th style="width: 8%;">UNIT</th>
            <th style="width: 12%;">UNIT PRICE</th>
            <th style="width: 12%;">AMOUNT</th>
        </tr>
        </thead>
        <tbody>
        <?php
         $total = 0;
        ?>
        <?php if ($purchaseLines): ?>
            <?php $itemNo = 1; ?>
            <?php foreach ($purchaseLines as $line): ?>
                <?php
                   $total += $line->line_total;
                ?>
                <tr>
                    <td><?= $itemNo++ ?></td>
                    <td><?= Html::encode($line->product->code ?? '') ?></td>
                    <td class="description-cell"><?= Html::encode($line->product_name).'<br />'.Html::encode($line['product_description']) ?></td>
                    <td></td>
                    <td><?= number_format($line->qty, 1) ?></td>
                    <td><?= Html::encode(\backend\models\Unit::findName($line->unit_id)) ?></td>
                    <td class="number-cell"><?= number_format($line->line_price, 2) ?></td>
                    <td class="number-cell"><?= number_format($line->line_total, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Empty rows -->
        <?php
        $emptyRows = 8 - (isset($purchaseLines) ? count($purchaseLines) : 0);
        for ($i = 0; $i < $emptyRows; $i++):
            ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary-section">
        <div class="summary-left">
            <div class="acknowledgment">ACKNOWLEDGMENT BY :</div>
            <div style="text-align: center;">
                <div class="signature-line"></div>
            </div>
        </div>
        <div class="summary-right">
            <table class="summary-table">
                <tr>
                    <td class="summary-label">TOTAL</td>
                    <td class="number-cell"><?= number_format($total, 2) ?></td>
                </tr>
                <tr>
                    <td class="summary-label">DISCOUNT</td>
                    <td class="number-cell"><?= number_format($purchase->discount_total_amount == 0 ?$purchase->discount_amount : $purchase->discount_total_amount, 2) ?></td>
                </tr>
                <tr>
                    <td class="summary-label">NET AMOUNT</td>
                    <td class="number-cell"><?= number_format(($total - $purchase->discount_amount), 2) ?></td>
                </tr>
                <tr>
                    <td class="summary-label">VAT 7%</td>
                    <td class="number-cell"><?= number_format($purchase->vat_amount, 2) ?></td>
                </tr>
                <tr>
                    <td class="summary-label">TAX <?=$purchase->whd_tax_per?> %</td>
                    <td class="number-cell"><?= number_format($purchase->whd_tax_amount, 2) ?></td>
                </tr>
                <tr>
                    <td class="summary-label" style="font-size: 16px;">TOTAL</td>
                    <td class="number-cell" style="font-size: 16px; font-weight: bold;">
                        <?= number_format($purchase->net_amount, 2) ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Terms -->
    <div class="terms">
        <div><strong>Delivery :</strong><span> <?= Html::encode($purchase->delivery_note ?? '') ?></span></div>
        <div><strong>Payment :</strong><span> <?= Html::encode($purchase->payment_note ?? '') ?></span></div>
        <div><strong>Note 1.</strong> กรณีส่งสินค้าล่าช้ากว่ากำหนด ผู้ขาย/ผู้รับจ้าง ยอมให้ปรับเป็นรายวันในอัตรา 0.5% ของราคาทั้งหมด ทั้งนี้สูงสุดไม่เกินร้อยละ 10 ของราคาดังกล่าว</div>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <?php
                $requestor_signature = \backend\models\User::findEmployeeSignature($purchase->created_by);
                if(!empty($requestor_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?=$requestor_signature?>" alt="Purchasing Signature">
                <?php endif; ?>
            </div>
            <div>PURCHASING</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                <?php
                $emp_id = getEmpRequestor($purchase->id);
                $requestor_signature = \backend\models\User::findEmployeeSignature($emp_id);
                if(!empty($requestor_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?=$requestor_signature?>" alt="Request By Signature">
                <?php endif; ?>
            </div>
            <div>REQUEST BY</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                <?php
                $approve_signature = \backend\models\User::findEmployeeSignature($purchase->approve_by);
                if(!empty($approve_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?=$approve_signature?>" alt="Authorized Signature">
                <?php endif; ?>
            </div>
            <div>AUTHORIZED SIGNATURE</div>
            <div>F-WP-FMA-002-002 R.1</div>
        </div>
    </div>
</div>
<?php
function getEmpRequestor($purch_id) {
    $emp_id = 0;
    if($purch_id > 0) {
        $model = \backend\models\PurchReq::find()->where(['purch_id'=>$purch_id])->one();
        if($model) {
            $emp_id = $model->created_by;
        }
    }
    return $emp_id;
}
?>

<!-- Print Buttons -->
<?php if (isset($showButtons) && $showButtons): ?>
    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()" class="btn btn-primary" style="font-size: 20px;font-weight: bold;">
            <i class="glyphicon glyphicon-print"></i> Print
        </button>
<!--        <a href="--><?php //= \yii\helpers\Url::to(['print', 'id' => $purchase->id, 'format' => 'pdf']) ?><!--"-->
<!--           class="btn btn-danger" target="_blank" style="font-size: 20px;font-weight: bold;">-->
<!--            <i class="glyphicon glyphicon-file"></i> Download PDF-->
<!--        </a>-->
        <button onclick="printToPDF()" class="btn btn-success" style="font-size: 20px;font-weight: bold;">
            <i class="glyphicon glyphicon-download-alt"></i> Print to PDF
        </button>
        <button onclick="window.close()" class="btn btn-default" style="font-size: 20px;font-weight: bold;">Close</button>
    </div>
<?php endif; ?>

<script>
    // ฟังก์ชันสำหรับ Print to PDF
    function printToPDF() {
        // alert('เคล็ดลับสำหรับ PDF คุณภาพสูง:\n\n' +
        //     '1. กด Ctrl+P (Windows) หรือ Cmd+P (Mac)\n' +
        //     '2. เลือก "Save as PDF" หรือ "Microsoft Print to PDF"\n' +
        //     '3. ใน More settings:\n' +
        //     '   - Paper size: A4\n' +
        //     '   - Margins: Minimum\n' +
        //     '   - Scale: Custom 100%\n' +
        //     '   - Options: ✓ Background graphics\n' +
        //     '4. กด Save');

        setTimeout(() => {
            window.print();
        }, 100);
    }

    // เพิ่มการตั้งค่าสำหรับ PDF generation
    window.addEventListener('beforeprint', function() {
        document.body.classList.add('printing');
    });

    window.addEventListener('afterprint', function() {
        document.body.classList.remove('printing');
    });
</script>