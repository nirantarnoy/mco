<?php
use yii\helpers\Html;

$this->title = 'Purchase Order - ' . $purchase->purch_no;

// Calculate totals
$subtotal = 0;
$discount = 0;
$vat = 0;
$total = 0;

if ($purchaseLines) {
    foreach ($purchaseLines as $line) {
        $subtotal += $line->line_total;
    }
}

if($purchase->discount_per > 0){
    $discount = $subtotal * ($purchase->discount_per / 100);
}
if($purchase->discount_amount > 0){
    $discount += $purchase->discount_amount;
}

// Calculate VAT 7%
$netAmount = $subtotal - $discount;
$vat = $netAmount * 0.07;
$total = $netAmount + $vat;

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

        /* Fix for print layout */
        .row {
            display: table !important;
            width: 100% !important;
            table-layout: fixed !important;
        }

        .col-lg-7,
        .col-lg-5 {
            display: table-cell !important;
            vertical-align: top !important;
            float: none !important;
        }

        .col-lg-7 {
            width: 58% !important;
            padding-right: 15px !important;
        }

        .col-lg-5 {
            width: 42% !important;
            padding-left: 15px !important;
        }

        .main-content {
            display: table !important;
            width: 100% !important;
            table-layout: fixed !important;
        }

        .left-section {
            display: table-cell !important;
            width: 100% !important;
            vertical-align: top !important;
        }

        .contact-info {
            display: table !important;
            width: 100% !important;
            table-layout: fixed !important;
        }

        .contact-column {
            display: table-cell !important;
            width: 50% !important;
            vertical-align: top !important;
            padding-right: 10px !important;
        }

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

        img {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }

        .po-container {
            font-size: 12px !important;
            line-height: 1.3 !important;
        }

        .header {
            margin-bottom: 10px !important;
            display: table !important;
            width: 100% !important;
        }

        .header > div {
            display: table-cell !important;
            vertical-align: top !important;
        }

        .header > div:first-child {
            width: 60% !important;
        }

        .header > div:last-child {
            width: 40% !important;
            text-align: right !important;
        }

        .po-title {
            font-size: 20px !important;
        }

        .company-info {
            margin-bottom: 10px !important;
            line-height: 1.2 !important;
        }

        .company-name {
            font-size: 16px !important;
        }

        .info-section {
            margin-bottom: 8px !important;
        }

        .info-row {
            margin-bottom: 3px !important;
            display: table !important;
            width: 100% !important;
        }

        .info-label {
            font-size: 11px !important;
            display: table-cell !important;
            width: 80px !important;
        }

        .info-value {
            display: table-cell !important;
            font-size: 11px !important;
        }

        .items {
            margin: 10px 0 !important;
            font-size: 11px !important;
        }

        .items th {
            padding: 4px !important;
            font-size: 10px !important;
        }

        .items td {
            padding: 3px !important;
            height: 20px !important;
            font-size: 10px !important;
        }

        .summary-section {
            margin-top: 12px !important;
            display: table !important;
            width: 100% !important;
        }

        .summary-left {
            display: table-cell !important;
            width: 65% !important;
            vertical-align: top !important;
        }

        .summary-right {
            display: table-cell !important;
            width: 35% !important;
            vertical-align: top !important;
        }

        .summary-table td {
            padding: 3px !important;
            font-size: 11px !important;
        }

        .acknowledgment {
            text-align: center;
            margin: 15px 0;
            font-weight: bold;
            font-size: 12px;
            border-bottom: 1px solid #000;
            padding-bottom: 80px !important;
        }

        .signature-section {
            margin-top: 25px !important;
            display: table !important;
            width: 100% !important;
            table-layout: fixed !important;
        }

        .signature-box {
            display: table-cell !important;
            width: 33.33% !important;
            text-align: center !important;
            vertical-align: top !important;
            padding: 0 15px !important;
        }

        .signature-line {
            margin: 25px 0 8px 0 !important;
            min-height: 40px !important;
            border-bottom: 1px solid #000 !important;
        }

        .signature-line img {
            max-width: 120px !important;
            max-height: 35px !important;
            object-fit: contain;
        }

        .terms {
            margin-top: 12px !important;
            font-size: 10px !important;
            line-height: 1.3 !important;
        }
    }

    .po-container {
        width: 210mm;
        margin: 0 auto;
        padding: 15mm;
        background: white;
        font-family: Arial, sans-serif;
        font-size: 12px;
        line-height: 1.3;
        color: #000;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .logo-section {
        display: flex;
        align-items: center;
    }

    .logo-img {
        max-width: 120px;
        margin-bottom: 5px;
    }

    .po-title {
        font-size: 20px;
        font-weight: bold;
        text-align: right;
    }

    .company-info {
        margin-bottom: 15px;
        line-height: 1.3;
    }

    .company-name {
        font-weight: bold;
        font-size: 14px;
    }

    .row {
        display: flex;
        margin-bottom: 15px;
    }

    .col-lg-7 {
        flex: 0 0 58.33%;
        padding-right: 15px;
    }

    .col-lg-5 {
        flex: 0 0 41.67%;
        padding-left: 15px;
    }

    .main-content {
        display: flex;
        gap: 30px;
        margin-bottom: 15px;
    }

    .left-section {
        flex: 1;
    }

    .right-section {
        width: 300px;
        border: 1px solid #000;
        padding: 10px;
    }

    .info-row {
        display: flex;
        margin-bottom: 5px;
        align-items: center;
    }

    .info-label {
        font-weight: bold;
        min-width: 80px;
        font-size: 11px;
    }

    .info-value {
        border-bottom: 1px solid #000;
        flex: 1;
        padding: 2px 5px;
        font-size: 11px;
        min-height: 16px;
    }

    .supplier-section {
        margin-bottom: 15px;
    }

    .section-title {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .address-box {
        border: 1px solid #000;
        padding: 8px;
        min-height: 80px;
    }

    .contact-info {
        display: flex;
        gap: 30px;
        margin-bottom: 10px;
    }

    .contact-column {
        flex: 1;
    }

    table.items {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
    }

    table.items th {
        background: #e6f2ff;
        border: 1px solid #000;
        padding: 5px;
        text-align: center;
        font-weight: bold;
        font-size: 11px;
    }

    table.items td {
        border: 1px solid #000;
        padding: 4px;
        text-align: center;
        height: 20px;
        font-size: 11px;
    }

    .description-cell {
        text-align: left !important;
        padding-left: 5px !important;
    }

    .number-cell {
        text-align: right !important;
        padding-right: 5px !important;
    }

    .summary-section {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
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
        padding: 4px;
        border: 1px solid #000;
        font-size: 12px;
    }

    .summary-label {
        text-align: center;
        font-weight: bold;
        background: #e6f2ff;
        width: 60%;
    }

    .acknowledgment {
        text-align: center;
        margin: 15px 0;
        font-weight: bold;
        font-size: 12px;
        border-bottom: 1px solid #000;
        padding-bottom: 30px;
    }

    .signature-section {
        display: flex;
        justify-content: space-around;
        margin-top: 30px;
        text-align: center;
    }

    .signature-box {
        width: 30%;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        margin: 30px 0 8px 0;
        min-height: 50px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        position: relative;
    }

    .signature-line img {
        max-width: 120px;
        max-height: 45px;
        object-fit: contain;
        position: absolute;
        bottom: 5px;
        left: 50%;
        transform: translateX(-50%);
    }

    .terms {
        margin-top: 15px;
        font-size: 10px;
        line-height: 1.3;
    }

    .reference-code {
        text-align: right;
        font-size: 10px;
        margin-top: 5px;
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
        <div>
            <div class="po-title">PURCHASE ORDER</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <!-- Company Info -->
            <div class="company-info">
                <div class="company-name">M.C.O. COMPANY LIMITED</div>
                <div>8/18 Koh-Kloy Road. ,Tambon Cherngnoen,</div>
                <div>Amphur Muang , Rayong 21000 Thailand.</div>
                <div>ID.NO. 0215543000985</div>
                <div>Tel : (038)-875258-9 , 094-6984555</div>
                <div>e-mail : info@thai-mco.com</div>
            </div>
            <!-- Supplier Section -->
            <div class="supplier-section">
                <div class="section-title">SUPPLIER :</div>
                <div class="address-boxx">
                    <?= Html::encode($purchase->vendor->name ?? '') ?><br>
                    <?= Html::encode($vendor_address) ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="info-row">
                <span class="info-label">PO.NO. :</span>
                <span class="info-value"><?= Html::encode($purchase->purch_no) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">DATE :</span>
                <span class="info-value"><?= Yii::$app->formatter->asDate($purchase->purch_date, 'php:d/m/Y') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">PAGE :</span>
                <span class="info-value">1</span>
            </div>
            <br>
            <div style="font-weight: bold;">Buyer (if other then consignee)</div>
            <div class="info-row">
                <span class="info-label">Contact :</span>
                <span class="info-value"></span>
            </div>
            <br>
            <div style="font-weight: bold;">Consignee :</div>
            <div>M.C.O.Co., Ltd.</div>
            <div>8/18 Koh-Kloy Road. ,Tambon Cherngnoen,</div>
            <div>Amphur Muang , Rayong 21000 Thailand.</div>
            <div>Tel : (038)-875258-9 , 094-6984555</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="left-section">
            <!-- Contact Info -->
            <div class="contact-info">
                <div class="contact-column">
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
                    <div class="info-row">
                        <span class="info-label">REF. NO. :</span>
                        <span class="info-value"><?= Html::encode($purchase->ref_no ?? '') ?></span>
                    </div>
                </div>
                <div class="contact-column">
                    <div class="info-row">
                        <span class="info-label">DELIVERY :</span>
                        <span class="info-value"><?= Html::encode($purchase->delivery_note ?? '') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">PAYMENT :</span>
                        <span class="info-value"><?= Html::encode($purchase->payment_note ?? '') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">CURRENCY :</span>
                        <span class="info-value">USD</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items">
        <thead>
        <tr>
            <th style="width: 5%;">ITEM</th>
            <th style="width: 12%;">CODE NO.</th>
            <th style="width: 35%;">DESCRIPTION</th>
            <th style="width: 10%;">P/N.</th>
            <th style="width: 8%;">Q'TY</th>
            <th style="width: 8%;">UNIT</th>
            <th style="width: 12%;">UNIT PRICE</th>
            <th style="width: 12%;">AMOUNT</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($purchaseLines): ?>
            <?php $itemNo = 1; ?>
            <?php foreach ($purchaseLines as $line): ?>
                <tr>
                    <td><?= $itemNo++ ?></td>
                    <td><?= Html::encode($line->product->code ?? '') ?></td>
                    <td class="description-cell"><?= Html::encode($line->product->name ?? $line->product_name) ?></td>
                    <td></td>
                    <td><?= number_format($line->qty, 0) ?></td>
                    <td><?= Html::encode(\backend\models\Unit::findName($line->unit_id)) ?></td>
                    <td class="number-cell"><?= number_format($line->line_price, 2) ?></td>
                    <td class="number-cell"><?= number_format($line->line_total, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Empty rows -->
        <?php
        $emptyRows = 20 - (isset($purchaseLines) ? count($purchaseLines) : 0);
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
            <div class="acknowledgment">ACKNOWLEDGMENT BY : <div style="height: 50px;"></div> </div>
        </div>
        <div class="summary-right">
            <table class="summary-table">
                <tr>
                    <td class="summary-label">SUB TOAL</td>
                    <td class="number-cell"><?= number_format($subtotal, 2) ?></td>
                </tr>
                <tr>
                    <td class="summary-label">GRAND TOTAL</td>
                    <td class="number-cell" style="font-weight: bold;">
                        <?= number_format($subtotal, 2) ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Terms -->
    <div class="terms">
        <div><strong>NOTE 1 :</strong> 1. This number must appear on all package, D/OS, Invoices.</div>
        <div style="margin-left: 60px;">2. Please acknowledge after receipt of this purchase order.</div>
        <div style="margin-left: 60px;">3. For the late delivery the penalty will be 0.5% of the Total Amount per day.</div>
        <div><strong>NOTE 2 :</strong></div>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <?php
                $created_by = \backend\models\Purch::findPrcreatedBy($purchase->id);
                $requestor_signature = \backend\models\User::findEmployeeSignature($created_by);
                if(!empty($requestor_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?=$requestor_signature?>" alt="Request By Signature">
                <?php endif; ?>
            </div>
            <div>REQUEST BY</div>
        </div>
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
                $approve_signature = \backend\models\User::findEmployeeSignature($purchase->approve_by);
                if(!empty($approve_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?=$approve_signature?>" alt="Authorized Signature">
                <?php endif; ?>
            </div>
            <div>AUTHORIZED SINGNATURE</div>
        </div>
    </div>

    <div class="reference-code">F-WP-FMA-002-005 R.1</div>
</div>

<!-- Print Buttons -->
<?php if (isset($showButtons) && $showButtons): ?>
    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()" class="btn btn-primary" style="font-size: 20px;font-weight: bold;">
            <i class="glyphicon glyphicon-print"></i> Print
        </button>
        <button onclick="printToPDF()" class="btn btn-success" style="font-size: 20px;font-weight: bold;">
            <i class="glyphicon glyphicon-download-alt"></i> Print to PDF
        </button>
        <button onclick="window.close()" class="btn btn-default" style="font-size: 20px;font-weight: bold;">Close</button>
    </div>
<?php endif; ?>

<script>
    function printToPDF() {
        setTimeout(() => {
            window.print();
        }, 100);
    }

    window.addEventListener('beforeprint', function() {
        document.body.classList.add('printing');
    });

    window.addEventListener('afterprint', function() {
        document.body.classList.remove('printing');
    });
</script>