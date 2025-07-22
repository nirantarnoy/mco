<?php
/**
 * @var $this yii\web\View
 */

use yii\helpers\Html;

// Mock data for labels
$labels = [
    [
        'product_code' => 'ST-001',
        'product_name' => 'กระดาษ A4 80 แกรม',
        'description' => 'Double A Premium',
        'unit' => 'รีม',
        'location' => 'A1-B2',
        'barcode' => '8851234567890',
        'qty' => '50',
        'price' => '120.00'
    ],
    [
        'product_code' => 'ST-002',
        'product_name' => 'หมึกพิมพ์ Canon',
        'description' => 'PG-740 Black',
        'unit' => 'ขวด',
        'location' => 'B3-C1',
        'barcode' => '8851234567891',
        'qty' => '10',
        'price' => '450.00'
    ],
    [
        'product_code' => 'ST-003',
        'product_name' => 'แฟ้มเอกสาร 2 นิ้ว',
        'description' => 'สีน้ำเงิน',
        'unit' => 'แฟ้ม',
        'location' => 'C2-D3',
        'barcode' => '8851234567892',
        'qty' => '30',
        'price' => '65.00'
    ],
    [
        'product_code' => 'ST-004',
        'product_name' => 'ปากกาลูกลื่น',
        'description' => 'Pilot 0.5mm Blue',
        'unit' => 'ด้าม',
        'location' => 'D1-E2',
        'barcode' => '8851234567893',
        'qty' => '100',
        'price' => '15.00'
    ],
    [
        'product_code' => 'ST-005',
        'product_name' => 'กรรไกร 8 นิ้ว',
        'description' => 'สแตนเลส',
        'unit' => 'อัน',
        'location' => 'E3-F1',
        'barcode' => '8851234567894',
        'qty' => '5',
        'price' => '85.00'
    ],
    [
        'product_code' => 'ST-006',
        'product_name' => 'เทปใส 3/4 นิ้ว',
        'description' => '3M Scotch',
        'unit' => 'ม้วน',
        'location' => 'F2-G1',
        'barcode' => '8851234567895',
        'qty' => '20',
        'price' => '25.00'
    ],
];

$products = [
    [
        'ref_po' => 'PO-2024-001',
        'descrip' => 'เครื่องปิดผนึกสำหรับบรรจุภัณฑ์',
        'model' => 'MCO-SEAL-300',
        'brand' => 'MCO Industrial',
        'qty' => '2 Units'
    ],
    [
        'ref_po' => 'PO-2024-002',
        'descrip' => 'เครื่องตัดวัสดุอัตโนมัติ',
        'model' => 'MCO-CUT-500',
        'brand' => 'MCO Automation',
        'qty' => '1 Set'
    ],
    [
        'ref_po' => 'PO-2024-003',
        'descrip' => 'ระบบลำเลียงสายพาน',
        'model' => 'MCO-CONV-750',
        'brand' => 'MCO Systems',
        'qty' => '3 Meters'
    ],
    [
        'ref_po' => 'PO-2024-004',
        'descrip' => 'เครื่องบรรจุผงอัตโนมัติ',
        'model' => 'MCO-FILL-250',
        'brand' => 'MCO Packaging',
        'qty' => '1 Unit'
    ],
    [
        'ref_po' => 'PO-2024-005',
        'descrip' => 'เครื่องควบคุมอุณหภูมิดิจิตอล',
        'model' => 'MCO-TEMP-100',
        'brand' => 'MCO Control',
        'qty' => '5 Pieces'
    ],
    [
        'ref_po' => 'PO-2024-006',
        'descrip' => 'ปั๊มลมสำหรับงานอุตสาหกรรม',
        'model' => 'MCO-PUMP-400',
        'brand' => 'MCO Equipment',
        'qty' => '2 Units'
    ]
];

// Company info
$companyName = 'M.C.O. COMPANY LIMITED';
$printDate = date('d/m/Y H:i');

?>

<style>
    /*body {*/
    /*    font-family: Arial, sans-serif;*/
    /*    margin: 10px;*/
    /*    background-color: #f5f5f5;*/
    /*    font-size: 10px;*/
    /*}*/

    .page {
        background-color: white;
        padding: 10px;
        margin-bottom: 20px;
    }

    .labels-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 5mm;
        width: 210mm; /* A4 width */
    }

    .label {
        width: 70mm; /* 4 cm */
        height: 40mm; /* 7 cm */
        border: 2px solid #2563eb;
        border-radius: 3px;
        overflow: hidden;
        background-color: white;
        page-break-inside: avoid;
        display: flex;
        flex-direction: column;
    }

    .header {
        background-color: white;
        padding: 3mm;
        border-bottom: 1px solid #000;
        text-align: center;
        flex-shrink: 0;
    }

    .company-name {
        font-size: 20px;
        font-weight: bold;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .logo {
        margin-bottom: 12px;
    }

    .logo-text {
        background: linear-gradient(45deg, #dc2626, #eab308, #16a34a, #2563eb);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 25px;
        font-weight: bold;
    }

    .certification {
        font-size: 15px;
        color: #666;
        line-height: 1.2;
    }

    .cert-badge {
        display: inline-block;
        background-color: #dc2626;
        color: white;
        padding: 1px 2px;
        border-radius: 1px;
        font-size: 4px;
        margin-right: 2px;
        vertical-align: middle;
    }

    .contact {
        font-size: 5px;
        font-weight: bold;
        margin-top: 1mm;
        color: #000;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
        flex: 1;
        font-size: 6px;
    }

    .data-table td {
        border: 1px solid #000;
        padding: 1mm;
        vertical-align: top;
        line-height: 1.1;
    }

    .data-table .label-col {
        background-color: #f8f9fa;
        width: 12mm;
        text-align: center;
        font-weight: bold;
        font-size: 5px;
    }

    .data-table .content-col {
        background-color: white;
        font-size: 5px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    @media print {
        body { margin: 0; }
        .page { margin: 0; padding: 5mm; }
        .label { page-break-inside: avoid; }
    }

    @page {
        size: A4;
        margin: 10mm;
    }
</style>

<!-- Include Google Fonts for barcode if needed -->
<link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">

<div class="no-print">
    <button class="print-button" onclick="window.print()">พิมพ์ป้ายสินค้า</button>
</div>

<div class="page">
    <div class="labels-container">
        <?php foreach($products as $product): ?>
            <div class="label">
                <div class="header">
                    <div class="logo">
                        <span class="logo-text">MCO</span>
                    </div>
                    <div class="company-name">M.C.O.CO.,LTD</div>

                    <div class="certification">
                        <span class="cert-badge">ISO</span>
                        <strong>ISO 9001:2015</strong><br>
                        <strong>No: TH020629</strong><br>
                        <strong>Bureau Veritas (Thailand)</strong>
                    </div>

                    <div class="contact">
                        038-875258<br>www.thai-mco.com
                    </div>
                </div>

                <table class="data-table">
                    <tr>
                        <td class="label-col">Ref.Po</td>
                        <td class="content-col"><?php echo htmlspecialchars($product['ref_po']); ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Descrip.</td>
                        <td class="content-col"><?php echo htmlspecialchars($product['descrip']); ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Model</td>
                        <td class="content-col"><?php echo htmlspecialchars($product['model']); ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Brand</td>
                        <td class="content-col"><?php echo htmlspecialchars($product['brand']); ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Q'ty</td>
                        <td class="content-col"><?php echo htmlspecialchars($product['qty']); ?></td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="print-info no-print">
        <p>พิมพ์วันที่: <?= $printDate ?> | <?= $companyName ?></p>
    </div>
</div>

<script>
    // Optional: Auto-generate more realistic barcode pattern
    document.addEventListener('DOMContentLoaded', function() {
        const barcodeBars = document.querySelectorAll('.barcode-bars');
        barcodeBars.forEach(function(bar) {
            // Generate random barcode pattern
            let pattern = 'repeating-linear-gradient(to right, ';
            let position = 0;
            for (let i = 0; i < 30; i++) {
                let barWidth = Math.random() * 3 + 1;
                let spaceWidth = Math.random() * 2 + 1;
                pattern += `#000 ${position}px, #000 ${position + barWidth}px, `;
                position += barWidth;
                pattern += `#fff ${position}px, #fff ${position + spaceWidth}px`;
                position += spaceWidth;
                if (i < 29) pattern += ', ';
            }
            pattern += ')';
            bar.style.background = pattern;
        });
    });

    // Alternative label layout for thermal printers (comment out if not needed)
    function setThermalPrinterLayout() {
        const labels = document.querySelectorAll('.label');
        labels.forEach(label => {
            label.style.width = '40mm';
            label.style.height = '70mm';
            label.style.pageBreakAfter = 'always';
        });
        document.querySelector('.labels-grid').style.gridTemplateColumns = '1fr';
    }
</script>