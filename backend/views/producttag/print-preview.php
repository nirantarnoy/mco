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
            size: A4;
            margin: 10mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .print-container {
            width: 100%;
            display: table;
            border-collapse: collapse;
        }

        .tag-row {
            display: table-row;
            page-break-inside: avoid;
        }

        .tag-cell {
            display: table-cell;
            width: 7cm;
            height: 4cm;
            border: 2px solid #0066CC;
            padding: 5px;
            vertical-align: top;
            position: relative;
            box-sizing: border-box;
            background-color: white;
        }

        .tag-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }

        .company-logo {
            display: flex;
            align-items: center;
        }

        .logo-text {
            font-size: 18px;
            font-weight: bold;
            color: #FF6600;
            margin-right: 5px;
        }

        .logo-subtext {
            font-size: 10px;
            color: #666;
        }

        .company-info {
            text-align: right;
            font-size: 9px;
            color: #666;
        }

        .iso-info {
            background-color: #FF6600;
            color: white;
            padding: 2px 5px;
            font-size: 8px;
            margin-bottom: 2px;
        }

        .tag-content {
            font-size: 11px;
            line-height: 1.4;
        }

        .tag-field {
            margin-bottom: 3px;
            display: flex;
            border-bottom: 1px solid #ddd;
            padding-bottom: 2px;
        }

        .field-label {
            font-weight: bold;
            width: 60px;
            flex-shrink: 0;
        }

        .field-value {
            flex: 1;
            padding-left: 5px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none;
            }

            .tag-cell {
                page-break-inside: avoid;
            }
        }

        .print-actions {
            margin: 20px;
            text-align: center;
        }

        .print-actions button {
            margin: 0 5px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="print-actions no-print">
    <button onclick="window.print();">พิมพ์</button>
    <button onclick="window.close();">ปิดหน้าต่าง</button>
</div>

<div class="print-container">
    <?php
    $count = 0;
    foreach ($printData as $index => $product):
        if ($count % 3 == 0): // Start new row every 3 tags
            ?>
            <div class="tag-row">
        <?php endif; ?>

        <div class="tag-cell">
            <div class="tag-header">
                <div class="company-logo">
                    <span class="logo-text">MCO</span>
                    <div>
                        <div class="logo-subtext">M.C.O.CO.,LTD</div>
                    </div>
                </div>
                <div class="company-info">
                    <div class="iso-info">Certified ISO 9001:2015</div>
                    <div>Certificate No: TH020629</div>
                    <div>Issued by Bureau Veritas</div>
                </div>
            </div>

            <div class="tag-content">
                <div class="tag-field">
                    <span class="field-label">Ref.Po</span>
                    <span class="field-value"><?= htmlspecialchars($product['ref_po']) ?></span>
                </div>
                <div class="tag-field">
                    <span class="field-label">Descrip.</span>
                    <span class="field-value"><?= htmlspecialchars($product['description']) ?></span>
                </div>
                <div class="tag-field">
                    <span class="field-label">Model</span>
                    <span class="field-value"><?= htmlspecialchars($product['model']) ?></span>
                </div>
                <div class="tag-field">
                    <span class="field-label">Brand</span>
                    <span class="field-value"><?= htmlspecialchars($product['brand']) ?></span>
                </div>
                <div class="tag-field">
                    <span class="field-label">Q'ty</span>
                    <span class="field-value"><?= htmlspecialchars($product['quantity']) ?></span>
                </div>
            </div>

            <div style="position: absolute; bottom: 2px; left: 5px; right: 5px; text-align: center; font-size: 9px; color: #666;">
                Tel : 038-875258 www.thai-mco.com
            </div>
        </div>

        <?php
        $count++;
        if ($count % 3 == 0 || $index == count($printData) - 1): // End row
            // Fill empty cells if this is the last row and it's not complete
            if ($index == count($printData) - 1 && $count % 3 != 0):
                for ($i = $count % 3; $i < 3; $i++):
                    ?>
                    <div class="tag-cell" style="visibility: hidden;"></div>
                <?php
                endfor;
            endif;
            ?>
            </div>
        <?php
        endif;
    endforeach;
    ?>
</div>
</body>
</html>