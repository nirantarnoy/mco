<?php

use yii\helpers\Html;
use backend\models\PettyCashAdvance;

/* @var $this yii\web\View */
/* @var $model backend\models\PettyCashAdvance */
/* @var $currentBalance float */
/* @var $pettyCashLimit float */
/* @var $advances array */
/* @var $from_date string */
/* @var $to_date string */

$this->title = 'ใบสรุปการเบิกชดเชยเงินสดย่อย';

// ฟังก์ชันแปลงวันที่เป็นรูปแบบไทย
function thaiDate($date)
{
    if (!$date) return '';
    $thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    $day = date('d', $timestamp);
    $month = $thaiMonths[(int)date('m', $timestamp)];
    $year = date('Y', $timestamp) + 543;
    return "$day $month พ.ศ. $year";
}

// ฟังก์ชันแปลงวันที่เป็น ว.ด.ป.
function shortThaiDate($date)
{
    if (!$date) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    $day = date('d', $timestamp);
    $month = date('m', $timestamp);
    $year = (date('Y', $timestamp) + 543) - 2500; // แสดงแค่ 2 หลัก
    return "$day.$month.$year";
}

// คำนวณยอดรวม
$totalAmount = 0;
$overAdvance = 0;

if (isset($advances) && is_array($advances)) {
    $totalAmount = array_sum(array_map(function ($adv) {
        return $adv->amount ?? 0;
    }, $advances));
}

// คำนวณเงินสดย่อยเบิกเกิน
if ($currentBalance < 0) {
    $overAdvance = abs($currentBalance);
}

// วันที่ช่วง
$dateFrom = $from_date ? thaiDate($from_date) : thaiDate(date('Y-m-01'));
$dateTo = $to_date ? thaiDate($to_date) : thaiDate(date('Y-m-t'));
$dateMonth = $from_date ? date('m', strtotime($from_date)) : date('m');
$dateYear = $from_date ? (date('Y', strtotime($from_date)) + 543) : (date('Y') + 543);

// ข้อมูลเดือน
$thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
$monthName = $thaiMonths[(int)$dateMonth];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 10mm;
            }
        }

        body {
            font-family: 'Sarabun', 'THSarabunNew', 'Angsana New', sans-serif;
            font-size: 14pt;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 15mm;
            box-sizing: border-box;
        }

        .header {
            margin-bottom: 12px !important;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .company-logo {
            width: 80px;
            height: auto;
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }

        .form-title {
            font-size: 15pt;
            font-weight: bold;
            margin: 5px 0;
        }

        .form-period {
            font-size: 14pt;
            margin: 5px 0;
        }

        .form-code {
            font-size: 11pt;
            text-align: right;
            margin: 5px 0;
        }

        .info-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .info-table td {
            border: 1px solid #000;
            padding: 8px 10px;
        }

        .info-label {
            font-weight: bold;
            width: 250px;
        }

        .data-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
        }

        .data-table th {
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .data-table .col-no {
            width: 50px;
        }

        .data-table .col-date {
            width: 80px;
        }

        .data-table .col-report-date {
            width: 90px;
        }

        .data-table .col-advance-no {
            width: 120px;
        }

        .data-table .col-description {
            text-align: left;
        }

        .data-table .col-amount {
            width: 100px;
            text-align: right;
        }

        .data-table .col-remark {
            width: 120px;
        }

        .data-table .total-row {
            font-weight: bold;
        }

        .signature-section {
            display: flex;
            justify-content: space-around;
            margin-top: 40px;
            text-align: center;
        }

        .signature-box {
            width: 30%;
        }

        /*.signature-line {*/
        /*    border-bottom: 1px solid #000;*/
        /*    margin: 40px 0 10px 0;*/
        /*    min-height: 60px;*/
        /*    display: flex;*/
        /*    align-items: flex-end;*/
        /*    justify-content: center;*/
        /*    position: relative;*/
        /*}*/
        .signature-line {
            border-bottom: 1px solid #000 !important;
        }

        .signature-line {
            margin: 1px 0 1px 0 !important;
            min-height: 5px !important;
        }

        .signature-line img {
            max-width: 140px;
            max-height: 55px;
            object-fit: contain;
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
        }

        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .btn-print {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14pt;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }

        .btn-print:hover {
            background-color: #0056b3;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14pt;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back:hover {
            background-color: #545b62;
        }

        .logo-section {
            display: flex;
            align-items: flex-start;
        }

        .logo {
            font-size: 30px;
            font-weight: bold;
            margin-right: 10px;
        }

        .logo .m {
            color: #FFA500;
        }

        .logo .c {
            color: #000080;
        }

        .logo .o {
            color: #008000;
        }

        .logo-img {
            max-width: 180px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()" class="btn-print">🖨️ พิมพ์</button>
    <a href="<?= \yii\helpers\Url::to(['index']) ?>" class="btn-back">← กลับ</a>
</div>

<div class="print-container">
    <!-- Header -->

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="logo-section">
                        <div class="logo">
                            <img src="../../backend/web/uploads/logo/mco_logo_2.png" class="logo-img" alt="">
                        </div>
                    </div>
                </td>
                <td>
                    <div style="text-align: center;">
                        <div class="company-name">บริษัท เอ็ม.ซี.โอ. จำกัด</div>
                        <div class="form-title">ใบสรุปการเบิกชดเชยเงินสดย่อย</div>
                        <div class="form-period">
                            ประจำวันที่ <?= $dateFrom ?> ถึง <?= $dateTo ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

    </div>



    <!-- Info Section -->
    <table class="info-table">
        <tr>
            <td class="info-label">วงเงินสดย่อย :</td>
            <td><?= number_format($pettyCashLimit, 2) ?> บาท</td>
        </tr>
        <tr>
            <td class="info-label">เงินสดย่อยคงเหลือ :</td>
            <td><?= number_format($currentBalance, 2) ?> บาท</td>
        </tr>
        <tr>
            <td class="info-label">เงินสดย่อยเบิกเกิน</td>
            <td><?= $overAdvance > 0 ? number_format($overAdvance, 2) : '-' ?> <?= $overAdvance > 0 ? 'บาท' : '' ?></td>
        </tr>
        <tr>
            <td class="info-label">เบิกชดเชยเงินสดย่อย:</td>
            <td><?= number_format($totalAmount, 2) ?> บาท</td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
        <tr>
            <th rowspan="2" class="col-no">ลำดับ</th>
            <th rowspan="2" class="col-date">ว.ด.ป.</th>
            <th rowspan="2" class="col-description">รายการ</th>
            <th rowspan="2" class="col-amount">จำนวนเงิน</th>
            <th rowspan="2" class="col-remark">หมายเหตุ</th>
        </tr>
        <!--        <tr>-->
        <!--            <th colspan="2" style="border-top: none; font-size: 9pt; padding: 2px;">เลขที่บิล</th>-->
        <!--        </tr>-->
        </thead>
        <tbody>
        <?php if (!empty($advances) && is_array($advances)): ?>
            <?php foreach ($advances as $index => $advance): ?>
                <tr>
                    <td class="col-advance-no">
                        <?= Html::encode($advance->advance_no) ?>
                    </td>
                    <td class="col-date"><?= shortThaiDate($advance->request_date) ?></td>
                    <td class="col-description"><?= Html::encode($advance->purpose) ?></td>
                    <td class="col-amount"><?= number_format($advance->amount, 2) ?></td>
                    <td class="col-remark">
                        <?php
                        if ($advance->remarks) {
                            echo Html::encode($advance->remarks);
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- เติมแถวว่างถ้ามีข้อมูลน้อย -->
            <?php for ($i = count($advances); $i < 15; $i++): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php endfor; ?>
        <?php else: ?>
            <!-- ถ้าไม่มีข้อมูล แสดงแถวว่าง 15 แถว -->
            <?php for ($i = 0; $i < 15; $i++): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <?php endfor; ?>
        <?php endif; ?>

        <!-- Total Row -->
        <tr class="total-row">
            <td colspan="3" style="text-align: center;">รวม</td>
            <td class="col-amount"><?= number_format($totalAmount, 2) ?></td>
            <td>&nbsp;</td>
        </tr>
        </tbody>
    </table>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <?php
            $requestor_signature = \backend\models\User::findEmployeeSignature($model->created_by);
            if(!empty($requestor_signature)): ?>
                <img src="../../backend/web/uploads/employee_signature/<?=$requestor_signature?>" style="max-width: 140px;max-height: 55px;" alt="Request By Signature">
            <?php endif; ?>
            <div class="signature-line"></div>
            <div>ผู้รักษาเงินสดย่อย</div>
        </div>
        <div class="signature-box">
            <?php
            $approval_signature = '';
            if (!empty($model->approve_status) && $model->approve_status == 1 && !empty($model->approved_by)) {
                $approverUserId = is_numeric($model->approved_by) ? (int)$model->approved_by : null;
                if (!$approverUserId) {
                    $userObj = \backend\models\User::find()->where(['username' => $model->approved_by])->one();
                    if ($userObj) {
                        $approverUserId = $userObj->id;
                    } else {
                        $empObj = \backend\models\Employee::find()->where(['CONCAT(fname, " ", lname)' => trim($model->approved_by)])->one();
                        if (!$empObj) {
                            $empObj = \backend\models\Employee::find()->where(['fname' => trim($model->approved_by)])->one();
                        }
                        if ($empObj) {
                            $userMatch = \backend\models\User::find()->where(['emp_ref_id' => $empObj->id])->one();
                            if ($userMatch) {
                                $approverUserId = $userMatch->id;
                            }
                        }
                    }
                }
                $approval_signature = $approverUserId ? \backend\models\User::findEmployeeSignature($approverUserId) : '';
            }
            if(!empty($approval_signature)): ?>
                <img src="../../backend/web/uploads/employee_signature/<?=$approval_signature?>" alt="Approved Signature">
            <?php else: ?>
                <div style="height: 55px;"></div>
            <?php endif; ?>
            <div class="signature-line"></div>
            <div>ผู้อนุมัติ</div>
        </div>
    </div>
    <div class="form-code">F-WP-FMA-004-002 Rev.N</div>
</div>

<script>
    // Auto print on load (optional)
    // window.onload = function() { window.print(); }
</script>
</body>
</html>