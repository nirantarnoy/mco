<?php

use yii\helpers\Html;

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเสร็จรับเงิน - MCO-25-000042</title>
    <style>
        @page {
            size: A4;
            margin: 0.4in 0.5in;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .main-footer {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 0;
                font-family: 'Sarabun', 'TH SarabunPSK', Arial, sans-serif;
                font-size: 13px;
                color: #000;
                line-height: 1.2;
            }

            .print-container {
                max-width: 0;
                margin: 0 auto;
                width: 100%;
                box-shadow: none;
                border: none;
                min-height: 280mm;
                padding: 10px;
            }
        }

        body {
            font-family: 'Sarabun', 'TH SarabunPSK', Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 13px;
        }

        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            min-height: 280mm;
        }

        /* Print Controls */
        .print-controls {
            margin-bottom: 20px;
            text-align: center;
        }

        .btn-group {
            display: inline-flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
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

        /* Top Border with Tax ID */
        .top-info {
            text-align: center;
            font-size: 11px;
            margin-bottom: 10px;
            padding: 5px;
            border: 1px solid #000;
        }

        /* Header Section - แบ่งเป็น 2 ส่วน */
        .header-section {
            display: flex;
            margin-bottom: 15px;
        }

        .header-left {
            flex: 1;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .logo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .iso-badge {
            background-color: #8B0000;
            color: white;
            padding: 2px 4px;
            font-size: 8px;
            text-align: center;
            border-radius: 2px;
            width: 80px;
        }

        .logo-container {
            width: 100px;
            height: 60px;
            background: linear-gradient(45deg, #c41e3a, #f4c430, #2c5aa0);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            position: relative;
        }

        .logo-text {
            color: white;
            font-weight: bold;
            font-size: 24px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .company-info {
            flex: 1;
            margin-left: 15px;
        }

        .company-name-thai {
            font-size: 20px;
            font-weight: bold;
            color: #0066CC;
            margin-bottom: 2px;
        }

        .company-name-eng {
            font-size: 18px;
            font-weight: bold;
            color: #0066CC;
            margin-bottom: 8px;
        }

        .company-address {
            font-size: 10px;
            line-height: 1.3;
        }

        .header-right {
            width: 300px;
            text-align: center;
            border: 2px solid #000;
            padding: 10px;
        }

        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .receipt-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .receipt-details strong {
            min-width: 60px;
        }

        /* Customer Section */
        .customer-section {
            border: 2px solid #000;
            margin-bottom: 15px;
        }

        .customer-top {
            display: flex;
            border-bottom: 1px solid #000;
        }

        .customer-left-section {
            flex: 1;
            padding: 8px;
            border-right: 1px solid #000;
        }

        .customer-right-section {
            width: 280px;
            padding: 8px;
        }

        .customer-field {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .customer-field strong {
            min-width: 80px;
            font-weight: bold;
        }

        .customer-field span {
            flex: 1;
            border-bottom: 1px dotted #333;
            padding-bottom: 1px;
            margin-left: 5px;
        }

        .req-section {
            padding: 8px;
            border-top: 1px solid #000;
            font-size: 11px;
            display: flex;
            justify-content: space-between;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
        }

        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            height: 35px;
            font-size: 10px;
        }

        .items-table td {
            height: 25px;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }

        /* Note and Total Section */
        .note-total-section {
            margin-bottom: 15px;
        }

        .note-box {
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 10px;
            text-align: center;
            font-size: 11px;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            font-size: 12px;
        }

        .total-row.grand-total {
            border-top: 1px solid #000;
            font-weight: bold;
            padding-top: 5px;
        }

        /* Payment Section */
        .payment-section {
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }

        .payment-text {
            font-size: 11px;
            margin-bottom: 10px;
            text-align: center;
        }

        .payment-methods {
            display: flex;
            gap: 30px;
            margin-bottom: 10px;
            font-size: 11px;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            display: inline-block;
            margin-right: 5px;
        }

        .checkbox.checked::after {
            content: "✓";
            font-size: 10px;
            font-weight: bold;
        }

        .payment-details {
            display: flex;
            gap: 20px;
            font-size: 11px;
        }

        .payment-detail {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .payment-detail-label {
            font-weight: bold;
        }

        .payment-detail-value {
            border-bottom: 1px solid #000;
            min-width: 100px;
            height: 15px;
        }

        /* Signature Section */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-label {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 35px;
            margin-bottom: 8px;
        }

        .signature-note {
            font-size: 10px;
        }

        /* Form Code */
        .form-code {
            text-align: right;
            font-size: 9px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="print-controls no-print">
    <div class="btn-group">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> พิมพ์
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> ปิด
        </button>
        <button onclick="generateNew()" class="btn btn-success">
            <i class="fas fa-plus"></i> สร้างใหม่
        </button>
    </div>
</div>

<div class="print-container">
    <!-- Top Tax Info -->

    <!-- Header Section -->
    <div class="header-section">
        <div class="header-left">
            <div class="logo-section">
                <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png', ['style' => 'max-width: 190px;']) ?>
            </div>
            <div class="company-info">
                <div class="company-name-thai">บริษัท เอ็ม.ซี.โอ. จำกัด</div>
                <div class="company-name-eng">M.C.O. COMPANY LIMITED</div>
                <div class="company-address">
                    8/18 ถ.เกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000 โทร. 66-(0) 3887-5258-59 แฟกซ์ 66-(0)3861-9559<br>
                    8/18 Koh-Kloy Rd., Cherngnoen, Muang, Rayong 21000 Tel : 66-(0)3887-5258-59 Fax : 66-(0)3861-9559
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <div style="vertical-align: bottom;">
                เลขประจำตัวผู้เสียภาษี 0215543000985
            </div>
        </div>
        <div class="col-lg-4" style="text-align: center;">
            <div class="receipt-title">ใบเสร็จรับเงิน<br>RECEIPT</div>
        </div>
        <div class="col-lg-4"></div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <table style="width: 100%;collapse: collapse;">
                <tr>
                    <td style="width: 10%;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        รหัสลูกค้า <br>CODE
                    </td>
                    <td style="width: 20%;background-color: lightslategray;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        <span style="padding-top: 5px;padding-bottom: 5px;"><?= Html::encode(\backend\models\Customer::findCode($model->customer_id) ?: '') ?></span>
                    </td>
                    <td style="width: 15%;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        วันที่ <br>DATE
                    </td>
                    <td style="width: 20%;background-color: lightslategray;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        <span style="padding-top: 5px;padding-bottom: 5px;"><?= date('m/d/Y', strtotime($model->invoice_date)) ?></span>
                    </td>
                    <td style="width: 10%;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        เลขที่ <br>NO
                    </td>
                    <td style="width: 15%;background-color: lightslategray;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        <span style="padding-top: 5px;padding-bottom: 5px;"><?= Html::encode($model->invoice_number) ?></span>
                    </td>
                </tr>
                <tr>
                    <td style="width: 10%;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: top;"
                        rowspan="2">ขายให้ <br>SALE TO
                    </td>
                    <td style="width: 20%;background-color: lightslategray;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;border-bottom: none;">
                        <span style="padding-top: 5px;padding-bottom: 5px;"><?= Html::encode($model->customer_name ?: '') ?></span>
                    </td>
                    <td style="width: 15%;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        ใบสั่งซื้อเลขที่ <br>PO NO
                    </td>
                    <td style="width: 20%;background-color: lightslategray;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        <span style="padding-top: 5px;padding-bottom: 5px;"><?= Html::encode($model->po_number ?: '') ?></span>
                    </td>
                    <td style="width: 10%;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        วันที่สั่งซื้อ <br>PO DATE
                    </td>
                    <td style="width: 15%;background-color: lightslategray;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        <span style="padding-top: 5px;padding-bottom: 5px;"><?= date('m/d/Y', strtotime($model->po_date)) ?></span>
                    </td>
                </tr>
                <tr>

                    <td style="width: 20%;background-color: lightslategray;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;border-top: none;">
                        <span style="padding-top: 5px;padding-bottom: 5px;"><?= Html::encode($model->customer_address ?: '') ?></span>
                    </td>
                    <td style="width: 15%;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        อ้างถึงเลขที่ใบแจ้งหนี้ <br>RFQ.IV
                    </td>
                    <td style="width: 20%;background-color: lightslategray;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        <span style="padding-top: 5px;padding-bottom: 5px;"></span></td>
                    <td style="width: 10%;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        อ้างถึงวันที่ใบแจ้งหนี้ <br>RFQ.DATE.IV
                    </td>
                    <td style="width: 15%;background-color: lightslategray;border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                        <span style="padding-top: 5px;padding-bottom: 5px;"></span></td>
                </tr>
            </table>
        </div>
    </div>
    <div style="height: 10px"></div>
    <!-- Items Table -->
    <table class="items-tablex">
        <tbody>
        <tr>
            <td style="border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;">
                <span><strong>ลำดับ<br/>ITEM</strong></span></td>
            <td style="border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;">
                <span><strong>รายการ<br/>DESCRIPTION</strong></span></td>
            <td style="border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;">
                <span><strong>จำนวน<br/>QUANTITY</strong></span></td>
            <td style="border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;">
                <span><strong>ราคาต่อหน่วย<br/>UNIT PRICE</strong></span></td>
            <td style="border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;">
                <span><strong>จำนวนเงิน<br/>AMOUNT</strong></span></td>
        </tr>
        <?php
        $model_line = \backend\models\InvoiceItem::find()->where(['invoice_id' => $model->id])->all();
        ?>
        <?php if (!empty($model_line)): ?>
            <?php foreach ($model_line as $index => $item): ?>
                <tr>
                    <td style="width: 8%;border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;">
                        <?= $index + 1 ?>
                    </td>
                    <td style="width: 52%;border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;"
                        class="text-left"><?= nl2br(Html::encode($item->item_description)) ?>
                    </td>
                    <td style="width: 10%;border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;">
                        <?= number_format($item->quantity, 0) ?> <?= Html::encode($item->unit) ?>
                    </td>
                    <td style="width: 15%;border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;"
                        class="text-right"><?= number_format($item->unit_price, 3) ?>
                    </td>
                    <td style="width: 15%;border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;"
                        class="text-right"><?= number_format($item->amount, 3) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php for ($i = 0; $i < 2; $i++): ?>
            <tr>
                <td style="border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;"> &nbsp;
                </td>
                <td style="border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;"> &nbsp;
                </td>
                <td style="border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;"> &nbsp;
                </td>
                <td style="border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;"> &nbsp;
                </td>
                <td style="border: 1px solid #000;padding: 4px 6px;text-align: center;vertical-align: middle;"> &nbsp;
                </td>
            </tr>
        <?php endfor; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3" rowspan="2" style="border-bottom: none;">
                <div style="text-align: left">
                    <div style="text-align: center;width: 100%">
                        <strong>ผิด ตก ยกเว็น E.&O.E.</strong><br>
                    </div>

                    <strong>หมายเหตุ</strong> ใบเสร็จรับเงินฉบับนี้จะสมบูรณ์ต่อเมื่อเก็บเงินตามเช็คได้เรียบร้อยแล้ว<br>
                    <strong>This receipt will be valued only when the cheque is cleared with the Bank</strong>
                </div>
            </td>
            <td style="border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;">
                <span>รวมเงิน<br/>TOTAL</span></td>
            <td style="border: 1px solid #000;padding: 4px 6px;text-align: right;vertical-align: middle;"><span><?= number_format($model->subtotal, 2) ?></span>
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;"><span>ภาษีมูลค่าเพิ่ม<br/>VAT 7%</span>
            </td>
            <td style="border: 1px solid #000;padding: 4px 6px;text-align: right;vertical-align: middle;">
                <span><?= number_format($model->vat_amount, 2) ?></span></td>
        </tr>
        <tr>
            <td colspan="3" style="border-bottom: none;border-top: none;border-left: none;">
                <div style="text-align: left;height: 100%;width: 100%;background-color: lightslategray;border-radius: 5px;padding: 10px;">
                    <strong><?= $model->total_amount_text ?: '' ?></strong>
                </div>
            </td>
            <td style="border: 1px solid #000;padding: 4px 6px;text-align: left;vertical-align: middle;"><span>รวมเงินทั้งสิ้น<br/>TOTAL AMOUNT</span>
            </td>
            <td style="border: 1px solid #000;padding: 4px 6px;text-align: right;vertical-align: middle;"><span><?= number_format($model->total_amount, 2) ?></span>
            </td>
        </tr>
        </tfoot>
    </table>
    <div style="height: 10px"></div>
    <table style="width: 100%;collapse: collapse;border: 1px solid #000">
        <tr>
            <td style="width: 33%;border: 1px solid #000;">
                <table style="width: 100%;collapse: collapse;">
                    <tr>
                        <td><span>ชำระโดย<br/>PAID BY</span></td>
                        <td><span class="checkbox"></span>
                            <span>เงินสด<br/>CASH</span></td>
                        <td>
                            <span class="checkbox"></span>
                            <span>เช็ค<br/>CHEQUE</span>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr></tr>
                </table>
            </td>
            <td colspan="2">
                <div class="payment-details">
                    <div class="payment-detail">
                        <span class="payment-detail-label">ธนาคาร BANK</span>
                        <span class="payment-detail-value"></span>
                    </div>
                    <div class="payment-detail">
                        <span class="payment-detail-label">เลขที่เช็ค CHEQUE NO.</span>
                        <span class="payment-detail-value"></span>
                    </div>
                    <div class="payment-detail">
                        <span class="payment-detail-label">ลงวันที่ DATE</span>
                        <span class="payment-detail-value"></span>
                    </div>
                </div>
            </td>

        </tr>
        <tr>
            <td style="width: 33%;border: 1px solid #000;text-align: center">
                <div class="signature-line"></div>
                <div class="signature-label">จำนวนเงิน / AMOUNT</div>

            </td>
            <td style="border: 1px solid #000;text-align: center">
                <div class="signature-line"></div>
                <div class="signature-label">ผู้เก็บเงิน / COLLECTOR / วันที่ / DATE</div>

            </td>
            <td style="border: 1px solid #000;text-align: center">
                <div class="signature-line"></div>
                <div class="signature-note">ผู้จัดการ / ผู้มีอำนาจลงนาม<br>MANAGER / AUTHORIZED SIGNATURE</div>
            </td>
        </tr>

    </table>


    <!-- Form Code -->
    <div class="form-code">
        F-WP-FMA-006-002Rev.N
    </div>
</div>

<script>
    function generateNew() {
        const receipts = [
            {
                number: 'MCO-25-000043',
                date: '9/5/2025',
                customer: 'บริษัท ไทยออยล์ จำกัด (มหาชน)',
                amount: 125000.00
            },
            {
                number: 'MCO-25-000044',
                date: '10/5/2025',
                customer: 'บริษัท ปตท. สำรวจและผลิตปิโตรเลียม จำกัด (มหาชน)',
                amount: 89500.00
            }
        ];

        const randomReceipt = receipts[Math.floor(Math.random() * receipts.length)];

        // Update receipt data
        document.querySelectorAll('.receipt-details span')[1].textContent = randomReceipt.number;
        document.querySelectorAll('.receipt-details span')[0].textContent = randomReceipt.date;

        const vat = randomReceipt.amount * 0.07;
        const total = randomReceipt.amount + vat;

        // Update amounts in table and totals
        document.querySelector('.items-table tbody tr td:last-child').textContent = randomReceipt.amount.toLocaleString('th-TH', {minimumFractionDigits: 2});
        document.querySelector('.items-table tbody tr td:nth-child(4)').textContent = randomReceipt.amount.toLocaleString('th-TH', {minimumFractionDigits: 2});

        document.querySelectorAll('.total-row span:last-child')[0].textContent = randomReceipt.amount.toLocaleString('th-TH', {minimumFractionDigits: 2});
        document.querySelectorAll('.total-row span:last-child')[1].textContent = vat.toLocaleString('th-TH', {minimumFractionDigits: 2});
        document.querySelectorAll('.total-row span:last-child')[2].textContent = total.toLocaleString('th-TH', {minimumFractionDigits: 2});
    }

    window.addEventListener('beforeprint', function () {
        document.body.style.zoom = '1';
    });
</script>
</body>
</html>