<?php
/* @var $this yii\web\View */
/* @var $printData array */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Product Tags - M.C.O.CO.,LTD</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            background: white;
        }

        .print-container {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 3mm;
            justify-content: flex-start;
            align-content: flex-start;
        }

        .tag-cell {
            width: 7cm;
            height: 4cm;
            border: 2px solid #0066CC;
            padding: 2mm;
            position: relative;
            background-color: white;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .tag-header {
            height: 1.2cm;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 2mm;
            padding-bottom: 1mm;
            border-bottom: 1px solid #ddd;
            flex-shrink: 0;
        }

        .company-logo {
            flex: 0 0 40%;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .logo-img {
            height: 8mm;
        }

        .company-info {
            flex: 0 0 58%;
            text-align: left;
        }

        .company-info h3 {
            font-size: 10px;
            font-weight: bold;
            margin: 0 0 1px 0;
            color: #333;
        }

        .iso-info {
            font-size: 7px;
            line-height: 1.1;
            color: #666;
            font-weight: bold;
        }

        .tag-content {
            flex: 1;
            overflow: hidden;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            height: 100%;
        }

        .content-table td {
            border: 1px solid #999;
            padding: 1mm;
            vertical-align: top;
            word-wrap: break-word;
            overflow: hidden;
        }

        .content-table .label-col {
            width: 18%;
            text-align: center;
            font-weight: bold;
            background-color: #f8f9fa;
            font-size: 8px;
        }

        .content-table .value-col {
            width: 82%;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            word-break: break-word;
            hyphens: auto;
        }

        .content-table tr {
            height: 4mm;
        }

        /* Print-specific styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                gap: 2mm;
            }

            .tag-cell {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* Force exact dimensions in print */
            .tag-cell {
                min-width: 7cm;
                max-width: 7cm;
                min-height: 5cm;
                max-height: 5cm;
            }
        }

        .print-actions {
            margin: 20px;
            text-align: center;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 5px;
        }

        .print-actions button {
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background: #007bff;
            color: white;
        }

        .print-actions button:hover {
            background: #0056b3;
        }

        .print-actions button:last-child {
            background: #6c757d;
        }

        .print-actions button:last-child:hover {
            background: #545b62;
        }

        /* Responsive grid for screen view */
        @media screen and (max-width: 1200px) {
            .print-container {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="print-actions no-print">
    <button onclick="window.print();">🖨️ พิมพ์</button>
    <button onclick="window.close();">❌ ปิดหน้าต่าง</button>
</div>

<div class="print-container">
    <?php foreach ($printData as $index => $product): ?>
        <div class="tag-cell">
            <div class="tag-header">
                <div class="company-logo">
                    <img src="../../backend/web/uploads/logo/mco_logo.png" class="logo-img" alt="MCO Logo">
                    <img src="../../backend/web/uploads/logo/verity.jpg" class="logo-img" alt="Verity Logo">
                </div>
                <div class="company-info">
                    <h3>M.C.O.CO.,LTD</h3>
                    <div class="iso-info">
                        <div>Certified ISO 9001:2015</div>
                        <div>Certificate No: TH020629</div>
                        <div>Issued by Bureau Veritas</div>
                        <div>Tel: 038-875258</div>
                        <div>www.thai-mco.com</div>
                    </div>
                </div>
            </div>

            <div class="tag-content">
                <table class="content-table">
                    <tr>
                        <td class="label-col">Ref.Po</td>
                        <td class="value-col"><?= htmlspecialchars($product['ref_po'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Descrip.</td>
                        <td class="value-col"><?= htmlspecialchars($product['description'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Model</td>
                        <td class="value-col"><?= htmlspecialchars($product['model'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Brand</td>
                        <td class="value-col"><?= htmlspecialchars($product['brand'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <td class="label-col">Q'ty</td>
                        <td class="value-col"><?= htmlspecialchars($product['quantity'] ?? '') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    // Auto-adjust layout for optimal printing
    document.addEventListener('DOMContentLoaded', function() {
        // Calculate optimal spacing for print
        const container = document.querySelector('.print-container');
        const tags = document.querySelectorAll('.tag-cell');

        console.log('Total tags:', tags.length);

        // Ensure consistent sizing
        tags.forEach((tag, index) => {
            tag.style.minWidth = '7cm';
            tag.style.maxWidth = '7cm';
            tag.style.minHeight = '4cm';
            tag.style.maxHeight = '4cm';
        });
    });

    // Print event handlers
    window.addEventListener('beforeprint', function() {
        document.body.style.zoom = '1';
    });

    window.addEventListener('afterprint', function() {
        console.log('Print completed');
    });
</script>
</body>
</html>