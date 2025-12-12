<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $vouchers backend\models\PettyCashVoucher[] */
/* @var $from_date string */
/* @var $to_date string */
/* @var $totalAmount float */
/* @var $pettyCashLimit float */
/* @var $currentBalance float */

$companyName = "บริษัท เอ็ม.ซี.โอ. จำกัด"; // ควรดึงจาก Config หรือ Model
$reportDate = date('d/m/Y'); // วันที่พิมพ์รายงาน
$reimbursementNo = date('Ym') . "/001"; // เลขที่เบิก (สมมติ)

// แปลงวันที่เป็นไทย
function dateToThai($date) {
    if (!$date) return '-';
    $months = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
        7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    $timestamp = strtotime($date);
    $d = date('j', $timestamp);
    $m = (int)date('n', $timestamp);
    $y = date('Y', $timestamp) + 543;
    return "$d {$months[$m]} $y";
}

function dateToThaiShort($date) {
    if (!$date) return '-';
    $timestamp = strtotime($date);
    $d = date('j', $timestamp);
    $m = date('m', $timestamp);
    $y = date('Y', $timestamp) + 543;
    return "$d/$m/$y";
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบสรุปการเบิกชดเชยเงินสดย่อย</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
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

        body {
            font-family: "THSarabunPSK", "Sarabun", sans-serif;
            font-size: 15px;
            line-height: 1.1;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }
        .logo {
            position: absolute;
            left: 0;
            top: -10px; /* Adjust vertical position */
        }
        .logo-img {
            width: 120px; /* Adjust width as needed */
            height: auto;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
        }
        .subtitle {
            font-size: 14px;
        }
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .summary-box td {
            border: 1px solid #000;
            padding: 5px;
        }
        .summary-label {
            font-weight: bold;
            width: 200px;
        }
        .summary-value {
            text-align: right;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border: 1px solid #000;
            padding: 5px;
        }
        .info-item {
            flex: 1;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }
        .data-table th {
            text-align: center;
            background-color: #f0f0f0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        
        /* Utility for print */
        @media print {
            .no-print { display: none; }
        }

        .footer-box {
            border: 1px solid #000;
            border-top: none;
            padding: 10px;
            margin-top: -1px; /* Connect with table border */
        }
        .note-section {
            margin-bottom: 50px;
        }
        .signature-section {
            display: flex;
            justify-content: space-around;
            padding-bottom: 20px;
        }
        .signature-block {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
         <div class="logo">
            <img src="../../backend/web/uploads/logo/mco_logo.png" class="logo-img" alt="">
        </div>
        <div class="title"><?= $companyName ?></div>
        <div class="title">ใบสรุปการเบิกชดเชยเงินสดย่อย</div>
        <div class="subtitle">ประจำวันที่ <?= dateToThai($from_date) ?> ถึง <?= dateToThai($to_date) ?></div>
        <div style="clear: both;"></div>
    </div>

    <table class="summary-box">
        <tr>
            <td class="summary-label">วงเงินสดย่อย :</td>
            <td class="summary-value"><?= number_format($pettyCashLimit, 2) ?></td>
        </tr>
        <tr>
            <td class="summary-label">วงเงินสำรองอื่นๆ :</td>
            <td class="summary-value">-</td>
        </tr>
        <tr>
            <td class="summary-label">เงินสดย่อยเบิกเกิน :</td>
            <td class="summary-value">-</td>
        </tr>
        <tr>
            <td class="summary-label">เงินสดย่อยคงเหลือ :</td>
            <td class="summary-value"><?= number_format($currentBalance, 2) ?></td>
        </tr>
        <tr>
            <td class="summary-label">เบิกชดเชยเงินสดย่อย :</td>
            <td class="summary-value"><?= number_format($totalAmount, 2) ?></td>
        </tr>
    </table>

    <table class="summary-box" style="margin-top: -1px;">
        <tr>
            <td width="15%"><strong>วันที่รายงาน</strong></td>
            <td width="35%"><?= dateToThai(date('Y-m-d')) ?></td>
            <td width="15%"><strong>เลขที่เบิก :</strong></td>
            <td width="35%"><?= $reimbursementNo ?></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="50">ลำดับ</th>
                <th width="100">ว.ด.ป.</th>
                <th>รายการ</th>
                <th width="100">จำนวนเงิน</th>
                <th width="120">หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vouchers as $index => $voucher): ?>
            <tr>
                <td class="text-center"><?= $index + 1 ?></td>
                <td class="text-center"><?= dateToThaiShort($voucher->date) ?></td>
                <td><?= Html::encode($voucher->paid_for) ?></td>
                <td class="text-right"><?= number_format($voucher->amount, 2) ?></td>
                <td class="text-center"><?= $voucher->pcv_no ?></td>
            </tr>
            <?php endforeach; ?>
            
            <!-- Fill empty rows if needed to look like the image -->
            <?php for($i = count($vouchers); $i < 15; $i++): ?>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php endfor; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-center text-bold">รวม</td>
                <td class="text-right text-bold"><?= number_format($totalAmount, 2) ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-box">
        <div class="note-section">
            <strong>หมายเหตุ :</strong>
        </div>
        <div class="signature-section">
            <div class="signature-block">
                ผู้รักษาเงินสดย่อย......................................................
            </div>
            <div class="signature-block">
                ผู้อนุมัติ......................................................
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
