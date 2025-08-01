<?php
use yii\helpers\Html;

$company = [
    'name' => 'บริษัท เอ็ม.ซี.โอ. จำกัด',
    'name_en' => 'M.C.O. CO.,LTD.',
    'address' => '8/18 ถนนเกาะกลอย ตำบลเชิงเนิน อำเภอเมือง จังหวัดระยอง 21000',
    'address_en' => '8/18 Koh-Kloy Rd., Tambon Cherngnoeh, Amphur Muang, Rayong 21000',
    'phone' => 'Tel : (038) 875258-9',
    'fax' => 'Fax : (038) 619559',
    'email' => 'e-mail: info@thai-mco.com',
    'website' => 'www.thai-mco.com',
    'tax_id' => '0215543000985'
];

function convertToThaiText($number) {
    // Function to convert number to Thai text
    return 'หนึ่งแสนแปดพันเจ็ดร้อยห้าสิบเอ็ดบาทสิบหกสตางค์'; // Placeholder
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ใบลดหนี้ <?= $model->credit_note_no ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0.5in;
            }
            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: 'Prompt', sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            background: white;
        }

        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: white;
            min-height: 297mm;
        }

        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-logo {
            width: 100px;
            height: 80px;
            background: #ff6b35;
            color: white;
            text-align: center;
            line-height: 80px;
            font-weight: bold;
            font-size: 24px;
            flex-shrink: 0;
        }

        .company-details {
            flex: 1;
            margin-left: 20px;
        }

        .company-details h2,
        .company-details h3 {
            margin: 5px 0;
            font-weight: bold;
        }

        .company-details p {
            margin: 2px 0;
            font-size: 12px;
        }

        .document-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }

        .copy-label {
            text-align: right;
            font-weight: bold;
            margin-bottom: 10px;
            color: #666;
        }

        .document-info {
            margin-bottom: 20px;
        }

        .document-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .document-info td {
            padding: 5px 0;
            vertical-align: top;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .items-table th {
            background-color: #f5f5f5;
            text-align: center;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary-box {
            border: 1px solid #000;
            padding: 10px;
            margin-top: 20px;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .dotted-line {
            border-bottom: 1px dotted #000;
            height: 30px;
            margin: 10px 0;
        }

        .discount-row {
            color: red;
        }

        .print-controls {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
<div class="print-container">
    <!-- Print Controls -->
    <div class="print-controls no-print">
        <h4>ใบลดหนี้ <?= $model->credit_note_no ?></h4>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> พิมพ์เอกสาร
        </button>
        <a href="javascript:window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> ปิดหน้าต่าง
        </a>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <div class="company-logo">MCO</div>
            <div class="company-details">
                <h2><?= $company['name'] ?></h2>
                <h3><?= $company['name_en'] ?></h3>
                <p><?= $company['phone'] ?> <?= $company['fax'] ?></p>
                <p><?= $company['email'] ?> <?= $company['website'] ?></p>
                <p><?= $company['address'] ?></p>
                <p><?= $company['address_en'] ?></p>
            </div>
        </div>
    </div>

    <!-- Copy Label -->
    <div class="copy-label">Copy</div>

    <!-- Document Title -->
    <div class="document-title">
        ใบลดหนี้ / ใบกำกับภาษี<br>
        CREDIT NOTE / TAX INVOICE
    </div>

    <!-- Document Information -->
    <div class="document-info">
        <table>
            <tr>
                <td width="50%">
                    <strong>เลขประจำตัวผู้เสียภาษี:</strong> <?= $company['tax_id'] ?><br>
                    <strong>รหัสลูกค้า:</strong> <?= $model->customer->customer_code ?><br>
                    <strong>ชื่อลูกค้า:</strong> <?= $model->customer->customer_name ?><br>
                    <strong>ที่อยู่:</strong> <?= $model->customer->address ?><br>
                    <strong>เลขประจำตัวผู้เสียภาษี:</strong> <?= $model->customer->tax_id ?>
                </td>
                <td width="50%" class="text-right">
                    <strong>เลขที่:</strong> <?= $model->credit_note_no ?><br>
                    <strong>วันที่:</strong> <?= date('d/m/Y', strtotime($model->issue_date)) ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
        <tr>
            <th width="8%">ลำดับ</th>
            <th width="32%">รายการ</th>
            <th width="12%">จำนวน</th>
            <th width="12%">ราคา</th>
            <th width="15%">ราคารวม</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($model->items as $item): ?>
            <tr>
                <td class="text-center"><?= $item->item_no ?></td>
                <td><?= $item->product_code ? $item->product_code . '<br>' : '' ?><?= Html::encode($item->description) ?></td>
                <td class="text-center"><?= $item->quantity ?> <?= $item->unit ?></td>
                <td class="text-right"><?= number_format($item->unit_price, 2) ?></td>
                <td class="text-right"><?= number_format($item->total_price, 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <!-- Discount Row -->
        <?php
        $totalDiscount = 0;
        foreach ($model->items as $item) {
            $totalDiscount += $item->discount;
        }
        if ($totalDiscount > 0):
            ?>
            <tr class="discount-row">
                <td></td>
                <td><strong>Discount</strong></td>
                <td></td>
                <td></td>
                <td class="text-right"><strong>-<?= number_format($totalDiscount, 2) ?></strong></td>
            </tr>
        <?php endif; ?>

        <!-- Empty rows for spacing -->
        <?php for ($i = count($model->items); $i < 4; $i++): ?>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-box">
        <table width="100%">
            <tr>
                <td width="70%">
                    <strong>ใบกำกับภาษีเดิมเลขที่:</strong> <?= $model->original_invoice_no ?><br>
                    <strong>ลงวันที่:</strong> <?= $model->original_invoice_date ? date('d/m/Y', strtotime($model->original_invoice_date)) : '' ?><br>
                    <strong>เหตุผลที่ต้องลดหนี้:</strong><br>
                    <?= Html::encode($model->reason) ?>
                </td>
                <td width="30%" class="text-right">
                    <strong>มูลค่าสินค้าตามใบกำกับเดิม:</strong> <?= number_format($model->original_amount, 2) ?><br>
                    <strong>มูลค่าสินค้าตามจริง:</strong> -<br>
                    <strong>รวมมูลค่าลดหนี้:</strong> <?= number_format($model->credit_amount, 2) ?><br>
                    <strong>ภาษีมูลค่าเพิ่ม:</strong> <?= number_format($model->vat_amount, 2) ?><br>
                    <strong>รวมเป็นเงินทั้งสิ้น:</strong> <?= number_format($model->total_amount, 2) ?>
                </td>
            </tr>
        </table>

        <div style="margin-top: 10px;">
            <strong>(ตัวอักษร)</strong> <?= convertToThaiText($model->total_amount) ?>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div><?= $company['name'] ?></div>
            <div class="dotted-line"></div>
            <div>ผู้มีอำนาจลงนาม / ผู้รับมอบอำนาจ</div>
            <div>_____/_____/_____</div>
        </div>
        <div class="signature-box">
            <div class="dotted-line"></div>
            <div>ลายเซ็นผู้รับเอกสาร</div>
            <div>_____/_____/_____</div>
        </div>
    </div>
</div>

<script>
    // Auto print on load (optional)
    // window.onload = function() { window.print(); }

    // Close window after printing
    window.onafterprint = function() {
        // Optional: close window after printing
        // window.close();
    }
</script>
</body>
</html>