<?php

use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $model backend\models\DebitNote */

$company = '';
$formatter = Yii::$app->formatter;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>ใบเพิ่มหนี้ <?= $model->document_no ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 5mm;
        }

        @font-face {
            font-family: 'THSarabunPSK';
            src: url('../../backend/web/fonts/thsarabun/THSarabunPSK.ttf') format('truetype');
            font-weight: normal;
        }

        @font-face {
            font-family: 'THSarabunPSK';
            src: url('../../backend/web/fonts/thsarabun/THSarabunPSK-Bold.ttf') format('truetype');
            font-weight: bold;
        }

        @font-face {
            font-family: 'THSarabunPSK';
            src: url('../../backend/web/fonts/thsarabun/THSarabunPSK-Italic.ttf') format('truetype');
            font-style: italic;
        }

        @media print {

            .no-print,
            .main-footer {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 0;
                /* font-family: 'THSarabunPSK' !important; */
                font-size: 13px;
            }

            .container {
                width: 100%;
                max-width: none;
                padding: 0 !important;
                margin: 0 !important;
            }
        }

        body {
            font-family: 'promt', 'sans-serif' !important;
            font-size: 14px;
            color: #000;
        }


        .container {
            font-family: 'THSarabunPSK' !important;
            max-width: 210mm;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 20px;
        }

        .company-info {
            display: block;
        }

        .company-name {
            font-size: 35px;
            font-weight: 600;
            color: #0000ff;
            line-height: 1;
        }

        .company-details {
            font-size: 20px;
            line-height: 1.1;
        }

        .document-info {
            text-align: center;
        }

        .document-title {
            font-size: 28px;
            font-weight: bold;
        }

        .document-title-en {
            font-size: 28px;
            font-weight: bold;
        }

        .title-row {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }


        .document-copy {
            font-size: 22px;
            position: absolute;
            right: 0;
            font-weight: bold;
            /* margin-bottom: 15px; */
            margin-right: 30px;
        }

        .customer-section {
            padding: 0px;
        }

        .section-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 22px;
        }

        .label {
            width: 150px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .label-right {
            width: 120px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .value {
            flex: 1;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 22px;
        }

        .items-table th {
            background: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: bold;
        }

        .items-table td {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            padding: 8px;
        }

        /* Summary table */
        .summary-table td {
            padding: 6px;
        }

        .total-row {
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 10px;
        }

        .amount-text {
            background: #f9f9f9;
            text-align: center;
            font-size: 16px;
            padding: 10px;
            margin: 20px 0;
        }

        /* Signature */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px dotted #000;
            margin: 50px 20px 10px;
        }

        .letter-text {
            font-size: 22px;
        }
    </style>
</head>

<body>

    <!-- PRINT BUTTONS -->
    <div class="no-print" style="text-align:center; margin:20px;">
        <button onclick="window.printMultipleCopies()" class="btn btn-primary btn-print">
            <i class="fas fa-print"></i> พิมพ์
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            ปิด
        </button>
    </div>

    <div class="container">

        <!-- HEADER -->
        <div class="header">
            <div class="company-info">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 40%; vertical-align: top;">
                            <div class="logo" style="margin-bottom: 10px;">
                                <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png', ['style' => 'max-width: 200px;']) ?>
                            </div>
                            <div class="company-details">
                                Tel : (038) 875258-9 Fax : (038) 619559<br>
                                e-mail: info@thai-mco.com www.thai-mco.com
                            </div>
                        </td>
                        <td style="width: 60%; vertical-align: top; text-align: right; padding-top: 50px;">
                            <div class="company-name">
                                บริษัท เอ็ม.ซี.โอ. จำกัด
                            </div>
                            <div class="company-name">
                                M.C.O. CO.,LTD.
                            </div>
                            <div class="company-details" style="margin-top: 5px;">
                                8/18 Koh-Kloy Rd., Tambon Cherngnoen, Amphur Muang, Rayong 21000<br>
                                8/18 ถนนเกาะกลอย ตำบลเชิงเนิน อำเภอเมือง จังหวัดระยอง 21000
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <br>

        <!-- DOCUMENT TITLE -->
        <div class="document-info" style="line-height: 0.9">

            <div class="document-title">ใบเพิ่มหนี้ / ใบกำกับภาษี</div>

            <div class="title-row">
                <div class="document-title-en">DEDIT NOTE / TAX INVOICE</div>
                <div class="document-copy">Copy</div>
            </div>
        </div>
        <br>
        <!-- CUSTOMER SECTION -->
        <div class="customer-section">
            <table>
                <tr>
                    <td style="width:50%;">
                        <div class="section-row">
                            <div class="label">ทะเบียนเลขที่</div>
                            <div class="value"><?= $company ? $company->tax_id : '0215543000985' ?></div>
                        </div>

                        <div class="section-row">
                            <div class="label">รหัสลูกค้า</div>
                            <div class="value">
                                <?= $model->customer_id ?
                                    Html::encode(\backend\models\Customer::findCode($model->customer_id)) :
                                    Html::encode(\backend\models\Vendor::findCode($model->vendor_id)) ?>
                            </div>
                        </div>

                        <div class="section-row">
                            <div class="label">ชื่อลูกค้า</div>
                            <div class="value">
                                <?= $model->customer_id ?
                                    Html::encode(\backend\models\Customer::findName($model->customer_id)) :
                                    Html::encode(\backend\models\Vendor::findName($model->vendor_id)) ?>
                            </div>
                        </div>

                        <div class="section-row">
                            <div class="label">ที่อยู่</div>
                            <div class="value">
                                <?= $model->customer_id ?
                                    Html::encode(\backend\models\Customer::findFullAddress($model->customer_id)) :
                                    Html::encode(\backend\models\Vendor::findFullAddress($model->vendor_id)) ?>
                            </div>
                        </div>
                    </td>

                    <td style="width:50%;">
                        <div class="info-right" style="margin-top:-50px;">
                            <div class="section-row" style="margin-left:150px;">
                                <div class="label-right">เลขที่</div>
                                <div class="value"><?= Html::encode($model->document_no) ?></div>
                            </div>

                            <div class="section-row" style="margin-left:150px;">
                                <div class="label-right">วันที่</div>
                                <div class="value"><?= $formatter->asDate($model->document_date, 'php:m/d/Y') ?></div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ITEMS TABLE -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:60px;">ลำดับ</th>
                    <th>รายการ</th>
                    <th style="width:100px;">จำนวน</th>
                    <th style="width:100px;">ราคา</th>
                    <th style="width:120px;">ราคารวม</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($model->debitNoteItems as $index => $item): ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td><?= nl2br(Html::encode($item->description)) ?></td>
                        <td class="text-center"><?= $formatter->asDecimal($item->quantity, 0) ?> <?= Html::encode($item->unit) ?></td>
                        <td class="text-right"><?= $formatter->asDecimal($item->unit_price, 2) ?></td>
                        <td class="text-right"><?= $formatter->asDecimal($item->amount, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

            <!-- SUMMARY -->
            <tfoot>
                <tr>
                    <!-- LEFT BLOCK -->
                    <td colspan="2" rowspan="4" style="border:1px solid #000; padding:8px; vertical-align: top;">
                        <div>
                            <div class="label">ใบกำกับภาษีเดิมเลขที่</div>
                            <div><?= Html::encode($model->original_invoice_no) ?></div>
                        </div>

                        <div style="margin-top:5px;">
                            <div class="label">ลงวันที่</div>
                            <div><?= $model->original_invoice_date ? $formatter->asDate($model->original_invoice_date, 'php:d/m/Y') : '' ?></div>
                        </div>

                        <div style="margin-top:10px;">
                            <div style="font-weight:bold;">เหตุผลที่ต้องเพิ่มหนี้:</div>
                            <?= nl2br(Html::encode($model->reason)) ?>
                        </div>
                    </td>

                    <!-- Row 1 -->
                    <td colspan="2" style="border:1px solid #000;">มูลค่าสินค้าตามใบกำกับฯเดิม</td>
                    <td style="border:1px solid #000; text-align:right;"><?= $formatter->asDecimal($model->original_amount, 2) ?></td>
                </tr>
                <tr>
                    <!-- Row 2 -->
                    <td colspan="2" style="border:1px solid #000;">รวมมูลค่าสินค้า</td>
                    <td style="border:1px solid #000; text-align:right;"><?= $formatter->asDecimal($model->adjust_amount, 2) ?></td>
                </tr>
                <tr>
                    <!-- Row 3 -->
                    <td colspan="2" style="border:1px solid #000;">ภาษีมูลค่าเพิ่ม <?= $formatter->asDecimal($model->vat_percent, 0) ?>%</td>
                    <td style="border:1px solid #000; text-align:right;"><?= $formatter->asDecimal($model->vat_amount, 2) ?></td>
                </tr>
                <tr>
                    <!-- Row 4 -->
                    <td colspan="2" style="border:1px solid #000; font-weight:bold;">รวมเป็นเงินทั้งสิ้น</td>
                    <td style="border:1px solid #000; font-weight:bold; text-align:right;"><?= $formatter->asDecimal($model->total_amount, 2) ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- AMOUNT TEXT -->
        <table width="100%">
            <tr>
                <td class="letter-text" width="20%">(ตัวอักษร)</td>
                <td class="letter-text" width="80%">
                    <div class="amount-text letter-text">
                        <?= nl2br(Html::encode($model->amount_text)) ?>
                    </div>
                </td>
            </tr>
        </table>

        <!-- SIGNATURE -->
        <div class="signature-section letter-text">
            <div class="signature-box">
                <div>บริษัท เอ็ม.ซี.โอ. จำกัด</div>
                <div class="signature-line"></div>
                <div>ผู้มีอำนาจลงนาม / ผู้รับมอบอำนาจ</div>
                <div style="margin-top: 10px;">_____/_____/_____</div>
            </div>

            <div class="signature-box">
                <div>&nbsp;</div>
                <div class="signature-line"></div>
                <div>ลายเซ็นผู้รับเอกสาร</div>
                <div style="margin-top: 10px;">_____/_____/_____</div>
            </div>
        </div>

    </div>

    <?php
    $js = <<<JS
let printing = false;

window.printMultipleCopies = function() {
    if (printing) return;
    printing = true;

    const btn = document.querySelector('.btn-print');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังพิมพ์...';
    }

    setTimeout(() => window.print(), 300);
};

window.addEventListener('afterprint', function () {
    printing = false;
    const btn = document.querySelector('.btn-print');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-print"></i> พิมพ์';
    }
});
JS;

    $this->registerJs($js);
    ?>

</body>

</html>