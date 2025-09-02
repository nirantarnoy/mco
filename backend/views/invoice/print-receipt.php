<?php

use yii\helpers\Html; ?>
<style>
    @page {
        size: A5;
        margin: 1.5cm;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .main-footer,
        .main-header,
        .main-sidebar,
        .content-wrapper .content-header {
            display: none !important;
        }

        .receipt-container {
            max-width: none !important;
            margin: 1.5cm !important;
            width: calc(100% - 3cm) !important;
            box-shadow: none !important;
            border: none !important;
            min-height: calc(210mm - 3cm) !important;
            padding: 0 !important;
        }

        body {
            font-family: 'Sarabun', 'TH SarabunPSK', Arial, sans-serif !important;
            font-size: 11px !important;
            color: #000 !important;
            line-height: 1.1 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        * {
            box-sizing: border-box !important;
        }
    }

    .receipt-container {
        max-width: calc(148mm - 3cm);
        margin: 0 auto;
        background: white;
        padding: 15px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        min-height: calc(210mm - 3cm);
        font-family: 'Sarabun', 'TH SarabunPSK', Arial, sans-serif;
        font-size: 11px;
        line-height: 1.1;
    }

    /* Print Controls */
    .receipt-print-controls {
        margin-bottom: 20px;
        text-align: center;
    }

    .receipt-btn-group {
        display: inline-flex;
        gap: 10px;
    }

    .receipt-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 11px;
        text-decoration: none;
        display: inline-block;
    }

    .receipt-btn-primary {
        background-color: #007bff;
        color: white;
    }

    .receipt-btn-success {
        background-color: #28a745;
        color: white;
    }

    .receipt-btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .receipt-btn:hover {
        opacity: 0.8;
    }

    /* Header Section */
    .receipt-header-section {
        display: flex;
        margin-bottom: 10px;
    }

    .receipt-header-left {
        flex: 1;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .receipt-logo-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
    }

    .receipt-company-info {
        flex: 1;
        margin-left: 10px;
    }

    .receipt-company-name-thai {
        font-size: 14px;
        font-weight: bold;
        color: #0066CC;
        margin-bottom: 1px;
    }

    .receipt-company-name-eng {
        font-size: 12px;
        font-weight: bold;
        color: #0066CC;
        margin-bottom: 5px;
    }

    .receipt-company-address {
        font-size: 8px;
        line-height: 1.2;
    }

    .receipt-row {
        display: flex;
        margin-bottom: 8px;
    }

    .receipt-col {
        flex: 1;
        padding: 2px;
    }

    .receipt-title {
        font-size: 14px;
        font-weight: bold;
        text-align: center;
    }

    /* Customer and Details Table */
    .receipt-details-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
        font-size: 9px;
    }

    .receipt-details-table td {
        border: 1px solid #000;
        padding: 2px 4px;
        vertical-align: middle;
    }

    .receipt-label-cell {
        background-color: #f0f0f0;
        font-weight: bold;
        white-space: nowrap;
    }

    .receipt-data-cell {
        background-color: #e8e8e8;
    }

    /* Items Table */
    .receipt-items-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000;
        margin-bottom: 8px;
        font-size: 9px;
    }

    .receipt-items-table th,
    .receipt-items-table td {
        border: 1px solid #000;
        padding: 2px 3px;
        text-align: center;
        vertical-align: middle;
    }

    .receipt-items-table th {
        background-color: #f0f0f0;
        font-weight: bold;
        height: 25px;
        font-size: 8px;
    }

    .receipt-items-table td {
        height: 18px;
    }

    .receipt-text-left {
        text-align: left !important;
    }

    .receipt-text-right {
        text-align: right !important;
    }

    /* Footer Note */
    .receipt-footer-note {
        font-size: 7px;
        text-align: center;
        margin: 5px 0;
    }

    /* Payment and Signature Section */
    .receipt-payment-signature-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000;
        font-size: 8px;
    }

    .receipt-payment-signature-table td {
        border: 1px solid #000;
        padding: 3px;
        vertical-align: top;
    }

    .receipt-checkbox {
        width: 10px;
        height: 10px;
        border: 1px solid #000;
        display: inline-block;
        margin-right: 3px;
    }

    .receipt-signature-line {
        border-bottom: 1px solid #000;
        height: 20px;
        margin-bottom: 3px;
    }

    /* Form Code */
    .receipt-form-code {
        text-align: right;
        font-size: 7px;
        margin-top: 8px;
    }

    /* Compact spacing */
    .receipt-compact-row {
        margin-bottom: 3px;
    }
</style>

<div class="receipt-print-controls no-print">
    <div class="receipt-btn-group">
        <button onclick="printReceipt()" class="receipt-btn receipt-btn-primary">
            <i class="fas fa-print"></i> พิมพ์
        </button>
        <button onclick="generateNewReceipt()" class="receipt-btn receipt-btn-success">
            <i class="fas fa-plus"></i> สร้างใหม่
        </button>
    </div>
</div>

<div class="receipt-container">
    <!-- Header Section -->
    <div class="receipt-header-section">
        <div class="receipt-header-left">
            <div class="receipt-logo-section">
                <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png', ['style' => 'max-width: 120px;']) ?>
            </div>
            <div class="receipt-company-info">
                <div class="receipt-company-name-thai">บริษัท เอ็ม.ซี.โอ. จำกัด</div>
                <div class="receipt-company-name-eng">M.C.O. COMPANY LIMITED</div>
                <div class="receipt-company-address">
                    8/18 ถ.เกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000<br>
                    โทร. 66-(0) 3887-5258-59 แฟกซ์ 66-(0)3861-9559<br>
                    เลขประจำตัวผู้เสียภาษี 0215543000985
                </div>
            </div>
        </div>
    </div>

    <!-- Title and Receipt Info -->
    <div class="receipt-row receipt-compact-row">
        <div class="receipt-col"></div>
        <div class="receipt-col">
            <div class="receipt-title">ใบเสร็จรับเงิน<br>RECEIPT</div>
        </div>
        <div class="receipt-col"></div>
    </div>

    <!-- Customer Details Table -->
    <table class="receipt-details-table">
        <tr>
            <td class="receipt-label-cell" style="width: 12%;">รหัสลูกค้า<br>CODE</td>
            <td class="receipt-data-cell" style="width: 22%;"><?= Html::encode(\backend\models\Customer::findCode($model->customer_id) ?: '') ?></td>
            <td class="receipt-label-cell" style="width: 12%;">วันที่<br>DATE</td>
            <td class="receipt-data-cell" style="width: 22%;"><?= date('m/d/Y', strtotime($model->invoice_date)) ?></td>
            <td class="receipt-label-cell" style="width: 10%;">เลขที่<br>NO</td>
            <td class="receipt-data-cell" style="width: 22%;"><?= Html::encode($model->invoice_number) ?></td>
        </tr>
        <tr>
            <td class="receipt-label-cell" rowspan="2">ขายให้<br>SALE TO</td>
            <td class="receipt-data-cell" style="border-bottom: none;"><?= Html::encode($model->customer_name ?: '') ?></td>
            <td class="receipt-label-cell">ใบสั่งซื้อเลขที่<br>PO NO</td>
            <td class="receipt-data-cell"><?= Html::encode($model->po_number ?: '') ?></td>
            <td class="receipt-label-cell">วันที่สั่งซื้อ<br>PO DATE</td>
            <td class="receipt-data-cell"><?= date('m/d/Y', strtotime($model->po_date)) ?></td>
        </tr>
        <tr>
            <td class="receipt-data-cell" style="border-top: none;">
                <?= Html::encode($model->customer_address ?: '') ?><br>
                TAXID: <?= Html::encode($model->customer_tax_id ?: '') ?>
            </td>
            <td class="receipt-label-cell">อ้างถึงเลขที่ใบแจ้งหนี้<br>RFQ.IV</td>
            <td class="receipt-data-cell"></td>
            <td class="receipt-label-cell">อ้างถึงวันที่ใบแจ้งหนี้<br>RFQ.DATE.IV</td>
            <td class="receipt-data-cell"></td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="receipt-items-table">
        <thead>
        <tr>
            <th style="width: 8%;">ลำดับ<br>ITEM</th>
            <th style="width: 50%;">รายการ<br>DESCRIPTION</th>
            <th style="width: 12%;">จำนวน<br>QUANTITY</th>
            <th style="width: 15%;">ราคาต่อหน่วย<br>UNIT PRICE</th>
            <th style="width: 15%;">จำนวนเงิน<br>AMOUNT</th>
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
                    <td class="receipt-text-left"><?= nl2br(Html::encode($item->item_description)) ?></td>
                    <td><?= number_format($item->quantity, 0) ?> <?= Html::encode($item->unit) ?></td>
                    <td class="receipt-text-right"><?= number_format($item->unit_price, 2) ?></td>
                    <td class="receipt-text-right"><?= number_format($item->amount, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php for ($i = 0; $i < 3; $i++): ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        <?php endfor; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="3" rowspan="2" style="border-bottom: none; font-size: 7px;">
                <div class="receipt-footer-note">
                    <strong>ผิด ตก ยกเว็น E.&O.E.</strong><br>
                    <strong>หมายเหตุ</strong> ใบเสร็จรับเงินฉบับนี้จะสมบูรณ์ต่อเมื่อเก็บเงินตามเช็คได้เรียบร้อยแล้ว<br>
                    <strong>This receipt will be valued only when the cheque is cleared with the Bank</strong>
                </div>
            </td>
            <td class="receipt-text-left">รวมเงิน<br>TOTAL</td>
            <td class="receipt-text-right"><?= number_format($model->subtotal, 2) ?></td>
        </tr>
        <tr>
            <td class="receipt-text-left">ภาษีมูลค่าเพิ่ม<br>VAT 7%</td>
            <td class="receipt-text-right"><?= number_format($model->vat_amount, 2) ?></td>
        </tr>
        <tr>
            <td colspan="3" style="border-bottom: none; border-top: none; border-left: none; background-color: #e8e8e8; padding: 5px; font-size: 8px;">
                <strong><?= $model->total_amount_text ?: '' ?></strong>
            </td>
            <td class="receipt-text-left">รวมเงินทั้งสิ้น<br>TOTAL AMOUNT</td>
            <td class="receipt-text-right"><strong><?= number_format($model->total_amount, 2) ?></strong></td>
        </tr>
        </tfoot>
    </table>

    <!-- Payment and Signature Section -->
    <table class="receipt-payment-signature-table">
        <tr>
            <td style="width: 30%;">
                <strong>ชำระโดย PAID BY</strong><br>
                <span class="receipt-checkbox"></span> เงินสด CASH<br>
                <span class="receipt-checkbox"></span> เช็ค CHEQUE
            </td>
            <td colspan="2">
                ธนาคาร BANK: _________________ เลขที่เช็ค CHEQUE NO.: _________________<br>
                ลงวันที่ DATE: _________________
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">
                <div class="receipt-signature-line"></div>
                <strong>จำนวนเงิน / AMOUNT</strong>
            </td>
            <td style="text-align: center; width: 35%;">
                <div class="receipt-signature-line"></div>
                <strong>ผู้เก็บเงิน / COLLECTOR</strong><br>
                <strong>วันที่ / DATE</strong>
            </td>
            <td style="text-align: center; width: 35%;">
                <div class="receipt-signature-line"></div>
                <strong>ผู้จัดการ / ผู้มีอำนาจลงนาม</strong><br>
                <strong>MANAGER / AUTHORIZED SIGNATURE</strong>
            </td>
        </tr>
    </table>

    <!-- Form Code -->
    <div class="receipt-form-code">
        F-WP-FMA-006-002Rev.N
    </div>
</div>

<script>
    function printReceipt() {
        window.print();
    }

    function generateNewReceipt() {
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
        console.log('Generated new receipt:', randomReceipt);
    }

    window.addEventListener('beforeprint', function () {
        document.body.style.zoom = '1';
    });
</script>