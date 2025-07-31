<?php

use yii\helpers\Html; ?>
<style>
    @page {
        size: A4;
        margin: 10mm;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Sarabun', 'TH SarabunPSK', Arial, sans-serif;
        font-size: 12px;
        line-height: 1.2;
        color: #000;
        background: white;
    }

    .invoice-container {
        width: 100%;
        max-width: 210mm;
        margin: 0 auto;
        background: white;
        padding: 5mm;
    }

    .header {
        /*border: 2px solid #000;*/
        padding: 8px;
        margin-bottom: 10px;
    }

    .company-info {
        text-align: center;
        margin-bottom: 8px;
    }

    .company-name-th {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .company-name-en {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 4px;
    }

    .company-address {
        font-size: 10px;
        line-height: 1.3;
    }

    .document-title {
        text-align: center;
        font-size: 16px;
        font-weight: bold;
        margin: 10px 0;
        border-bottom: 1px solid #000;
        padding-bottom: 5px;
    }

    .invoice-details {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .left-details, .right-details {
        width: 48%;
    }

    .detail-row {
        display: flex;
        margin-bottom: 3px;
        font-size: 11px;
    }

    .detail-label {
        min-width: 120px;
        font-weight: bold;
    }

    .customer-info {
        border: 1px solid #000;
        padding: 8px;
        margin-bottom: 10px;
    }

    .customer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000;
        margin-bottom: 10px;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #000;
        padding: 5px;
        text-align: center;
        font-size: 11px;
    }

    .items-table th {
        background-color: #f0f0f0;
        font-weight: bold;
    }

    .items-table .desc-col {
        text-align: left;
    }

    .items-table .amount-col {
        text-align: right;
    }

    .total-section {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .total-text {
        width: 50%;
        border: 1px solid #000;
        padding: 5px;
        font-size: 11px;
    }

    .total-amounts {
        width: 45%;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        border: 1px solid #000;
        padding: 3px 8px;
        font-size: 11px;
    }

    .total-row.grand-total {
        font-weight: bold;
        background-color: #f0f0f0;
    }

    .notes {
        font-size: 10px;
        line-height: 1.3;
        margin-bottom: 15px;
    }

    .signatures {
        display: flex;
        justify-content: space-between;
    }

    .signature-box {
        width: 30%;
        border: 1px solid #000;
        padding: 5px;
        text-align: center;
        height: 80px;
    }

    .signature-title {
        font-size: 10px;
        font-weight: bold;
        margin-bottom: 35px;
    }

    .signature-line {
        border-top: 1px solid #000;
        margin-top: 20px;
        padding-top: 3px;
        font-size: 9px;
    }

    .not-tax-invoice {
        text-align: center;
        font-size: 10px;
        font-style: italic;
        margin-bottom: 5px;
    }

    @media print {
        body {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }

        .invoice-container {
            padding: 0;
            margin: 0;
            max-width: none;
        }
    }
</style>

<div class="invoice-container">
    <!-- Header with Company Info -->
    <div class="header">
        <div class="row">
            <div class="col-lg-4">
                <?= Html::img('../../backend/web/uploads/logo/mco_logo.png', ['style' => 'max-width: 150px;']) ?>
            </div>
            <div class="col-lg-8">
                <div class="company-info">
                    <div class="company-name-th" style="text-align: left;">บริษัท เอ็ม. ซี. โอ. จำกัด (สำนักงานใหญ่)
                    </div>
                    <div class="company-name-en" style="text-align: left;">M.C.O. COMPANY LIMITED</div>
                    <div style="text-align: right; font-size: 10px; margin-top: -15px;">TAXID: 0215543000985</div>
                    <div class="company-address" style="text-align: left;">
                        8/18 ถ.เกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000 โทร 66-(0)-38875258-59
                        แฟ๊กซ์66-(0)-3861-9559<br>
                        8/18 Koh-Kloy-Rd., Cherngnoen, Muang, Rayong 21000 Tel. 66-(0)3887-5258-59 Fax. 66-(0)3861-9559
                    </div>
                </div>
            </div>
            <!--            <div class="col-lg-4"></div>-->
        </div>

        <div class="not-tax-invoice">(ไม่ใช่ใบกำกับภาษี)</div>
    </div>

    <!-- Document Title -->
    <div class="document-title">ใบแจ้งหนี้/ใบส่งสินค้า-บริการ</div>
    <div style="text-align: right; font-size: 10px; margin-top: -10px;">(ไม่ใช่ใบกำกับภาษี)</div>

    <!-- Invoice Details -->
    <div class="invoice-details">
        <div class="left-details">
            <div class="detail-row">
                <span class="detail-label">รหัสลูกค้า</span>
                <span><?= $model->quotation->customer->code ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Code</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ขายให้</span>
                <span>Glow Energy Public Company Limited (Head Office)</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Sold To</span>
                <span>555/2 Energy Complex Building B, 5th Floor, Vibhavadi-Rangsit Road, Chatuchak, Chatuchak, Bangkok 10900</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">เลขประจำตัวผู้เสียภาษี</span>
                <span>0107538000461</span>
            </div>
        </div>
        <div class="right-details">
            <div class="detail-row">
                <span class="detail-label">วันที่/ Date</span>
                <span><?= date('m/d/Y', strtotime($model->quotation->quotation_date)) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">เลขที่ / In.No.</span>
                <span>2568-03-002</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ใบสั่งซื้อเลขที่ / P/O No.</span>
                <span>3100018046</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">วันที่สั่งซื้อ / P/O Date</span>
                <span>04/02/2025</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">เงื่อนไข / กำหนดชำระ/ Credit , Due</span>
                <span>30 Days,</span>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
        <tr>
            <th style="width: 8%;">ลำดับ<br>Item</th>
            <th style="width: 35%;">รายการ<br>Description</th>
            <th style="width: 12%;">จำนวน<br>Quantity</th>
            <th style="width: 15%;">ราคาต่อหน่วย<br>Unit/Price</th>
            <th style="width: 15%;">จำนวนเงินรวม<br>Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($model_line != null): ?>
        <?php foreach ($model_line

        as $key => $line): ?>
        <tr>
            <td><?= $key + 1 ?></td>
            <td class="desc-col"><?= \backend\models\Product::findName($line->product_id) ?></td>
            <td><?= $line->qty ?></td>
            <td class="amount-col"><?= number_format($line->line_price, 2) ?></td>
            <td class="amount-col"><?= number_format($line->line_total, 2) ?></td>
        </tr>
        <tr>
            <?php endforeach; ?>
            <?php endif; ?>
            <!--            <td>1</td>-->
            <!--            <td class="desc-col">PM O2 Analyzer CFB3 Feb 2025</td>-->
            <!--            <td>3 JOB</td>-->
            <!--            <td class="amount-col">6,400.00</td>-->
            <!--            <td class="amount-col">19,200.00</td>-->
            <!--        </tr>-->
            <!-- Empty rows for spacing -->
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        </tbody>
    </table>

    <!-- Total Section -->
    <div class="total-section">
        <div class="total-text">
            <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">(ตัวอักษร)</div>
            <div style="text-align: center; font-size: 14px; font-weight: bold;">สองหมื่นห้าร้อยสี่สิบสี่บาทถ้วน</div>
        </div>
        <div class="total-amounts">
            <div class="total-row">
                <span>รวมเงิน<br>Total</span>
                <span>19,200.00</span>
            </div>
            <div class="total-row">
                <span>ภาษีมูลค่าเพิ่ม<br>Vat 7 %</span>
                <span>1,344.00</span>
            </div>
            <div class="total-row grand-total">
                <span>รวมเงินทั้งสิ้น<br>Grand Total</span>
                <span>20,544.00</span>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <div class="notes">
        <strong>หมายเหตุ :</strong><br>
        1. ตามรายการข้างต้น
        แม้จะได้ส่งมอบสินค้าแก่ผู้ซื้อแล้วก็ยังเป็นทรัพย์สินของผู้ขายจนกว่าผู้ซื้อจะได้รับชำระเงิน<br>
        2. สินค้าที่ซื้อไปเกินกว่า 7 วัน ทางบริษัทฯใคร่ขอสงวนสิทธิ์ไม่รับคืนสินค้าและคิดดอกเบี้ยร้อยละ1.5 ต่อเดือน<br>
        3. สามารถชำระผ่านช่องทางธนาคารกรุงเทพจำกัด (มหาชน) สาขาระยอง ชื่อบัญชีบจ.เอ็ม .ซี.โอ. เลขบัญชี277-3-02318-5
        บัญชีกระแสรายวัน
    </div>

    <div style="margin-bottom: 10px; font-size: 11px;">
        ได้ตรวจรับสินค้าตามรายการข้างต้นถูกต้อง
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-title">ผู้รับสินค้า / Received By</div>
            <div class="signature-line">วันที่ / Date______/_________/________</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">ผู้ส่งสินค้า / Send By</div>
            <div class="signature-line">วันที่ / Date______/_________/________</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">ผู้มีอำนาจลงนาม /Authorized Signature</div>
            <div class="signature-line">วันที่ / Date______/_________/________</div>
        </div>
    </div>
</div>

<script>
    // Sample data array structure for Yii2 integration
    const invoiceData = {
        company: {
            name_th: "บริษัท เอ็ม. ซี. โอ. จำกัด (สำนักงานใหญ่)",
            name_en: "M.C.O. COMPANY LIMITED",
            tax_id: "0215543000985",
            address_th: "8/18 ถ.เกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000",
            address_en: "8/18 Koh-Kloy-Rd., Cherngnoen, Muang, Rayong 21000",
            phone: "66-(0)-38875258-59",
            fax: "66-(0)-3861-9559"
        },
        invoice: {
            customer_code: "C-000551",
            date: "10/03/2025",
            invoice_no: "2568-03-002",
            po_no: "3100018046",
            po_date: "04/02/2025",
            credit_days: "30 Days"
        },
        customer: {
            name: "Glow Energy Public Company Limited (Head Office)",
            address: "555/2 Energy Complex Building B, 5th Floor, Vibhavadi-Rangsit Road, Chatuchak, Chatuchak, Bangkok 10900",
            tax_id: "0107538000461"
        },
        items: [
            {
                no: 1,
                description: "PM O2 Analyzer CFB3 Feb 2025",
                quantity: "3 JOB",
                unit_price: 6400.00,
                amount: 19200.00
            }
        ],
        totals: {
            subtotal: 19200.00,
            vat_rate: 7,
            vat_amount: 1344.00,
            grand_total: 20544.00,
            amount_text: "สองหมื่นห้าร้อยสี่สิบสี่บาทถ้วน"
        },
        bank_info: {
            bank: "ธนาคารกรุงเทพจำกัด (มหาชน)",
            branch: "สาขาระยอง",
            account_name: "บจ.เอ็ม .ซี.โอ.",
            account_no: "277-3-02318-5",
            account_type: "บัญชีกระแสรายวัน"
        }
    };

    // Function to print invoice
    function printInvoice() {
        window.print();
    }

    // Function to format number with commas
    function formatNumber(num) {
        return new Intl.NumberFormat('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(num);
    }

    // Add print button for testing
    document.addEventListener('DOMContentLoaded', function () {
        const printBtn = document.createElement('button');
        printBtn.innerHTML = 'พิมพ์ใบแจ้งหนี้';
        printBtn.style.cssText = 'position: fixed; top: 10px; right: 10px; z-index: 1000; padding: 10px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;';
        printBtn.onclick = printInvoice;
        document.body.appendChild(printBtn);

        // Hide print button when printing
        window.addEventListener('beforeprint', () => {
            printBtn.style.display = 'none';
        });

        window.addEventListener('afterprint', () => {
            printBtn.style.display = 'block';
        });
    });
</script>
