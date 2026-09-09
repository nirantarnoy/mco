<?php

use backend\models\Company;

/* @var $models array */
/* @var $month string */
/* @var $year string */

$months = [
    '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
    '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
    '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
];
$monthName = isset($months[$month]) ? $months[$month] : '';
$thaiYear = (int)$year + 543;

$companyId = Yii::$app->session->get('company_id');
$companyName = '';
$companyAddress = '';
if ($companyId) {
    $company = Company::findOne($companyId);
    if ($company) {
        $companyName = $company->name;
        $companyAddress = $company->description;
    }
}
?>

<style>
    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .report-table th, .report-table td {
        border: 1px solid #000;
        padding: 5px;
    }
    .report-table th {
        text-align: center;
        vertical-align: middle;
        background-color: #f9f9f9;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .no-border { border: none !important; }
    .header-table { width: 100%; margin-bottom: 20px; font-size: 14px; font-weight: bold; }
    .header-table td { padding: 3px; }
    .page-break { page-break-after: always; }
</style>

<div style="text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px;">
    รายงานภาษีซื้อ (สำนักงานใหญ่)
</div>

<table class="header-table">
    <tr>
        <td style="width: 15%;">ชื่อผู้ประกอบการ</td>
        <td style="width: 40%;"><?= $companyName ?></td>
        <td style="width: 20%;">สำนักงานใหญ่</td>
        <td style="width: 25%;">เดือน <?= $monthName ?> <?= $thaiYear ?></td>
    </tr>
    <tr>
        <td>ชื่อสถานประกอบการ</td>
        <td><?= $companyName ?></td>
        <td>สำนักงานใหญ่</td>
        <td></td>
    </tr>
    <tr>
        <td>ที่อยู่ผู้ประกอบการ</td>
        <td colspan="3"><?= $companyAddress ?></td>
    </tr>
</table>

<table class="report-table">
    <thead>
        <tr>
            <th rowspan="2" style="width: 5%;">ลำดับที่</th>
            <th colspan="2">ใบกำกับภาษี</th>
            <th rowspan="2" style="width: 10%;">เลขประจำตัว<br>ผู้เสียภาษีอากร</th>
            <th colspan="2">สถานประกอบการ</th>
            <th rowspan="2" style="width: 20%;">ชื่อผู้ขายสินค้า/ผู้ให้บริการ</th>
            <th rowspan="2" style="width: 8%;">มูลค่าสินค้า<br>NON-VAT</th>
            <th rowspan="2" style="width: 8%;">มูลค่าสินค้า<br>หรือบริการ</th>
            <th rowspan="2" style="width: 8%;">จำนวนเงิน<br>ภาษีมูลค่าเพิ่ม</th>
            <th rowspan="2" style="width: 8%;">หมายเหตุ</th>
        </tr>
        <tr>
            <th style="width: 8%;">วัน.เดือน/ปี</th>
            <th style="width: 10%;">เลขที่ เล่มที่</th>
            <th style="width: 5%;">สนง.<br>ใหญ่</th>
            <th style="width: 5%;">สาขา</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        $totalBaseHq = 0;
        $totalVatHq = 0;
        $totalBaseBr = 0;
        $totalVatBr = 0;

        if (!empty($models)) {
            foreach ($models as $m) {
                $vatDate = $m->vatdat ? date('d/m/Y', strtotime($m->vatdat)) : ($m->docdat ? date('d/m/Y', strtotime($m->docdat)) : '');
                $isHq = ($m->orgnum == '00000' || empty($m->orgnum));
                
                $base = (float)$m->vatpr0;
                $vat = (float)$m->vat_amount;
                
                if ($isHq) {
                    $totalBaseHq += $base;
                    $totalVatHq += $vat;
                } else {
                    $totalBaseBr += $base;
                    $totalVatBr += $vat;
                }
                ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td class="text-center"><?= $vatDate ?></td>
                    <td class="text-center"><?= $m->invoice_no ? $m->invoice_no : $m->refnum ?></td>
                    <td class="text-center"><?= $m->taxid ?></td>
                    <td class="text-center"><?= $isHq ? '/' : '' ?></td>
                    <td class="text-center"><?= !$isHq ? $m->orgnum : '' ?></td>
                    <td><?= $m->supnam ?></td>
                    <td class="text-right"></td>
                    <td class="text-right"><?= number_format($base, 2) ?></td>
                    <td class="text-right"><?= number_format($vat, 2) ?></td>
                    <td><?= $m->remark ?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="11" class="text-center">ไม่พบข้อมูล</td>
            </tr>
            <?php
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="text-right no-border" style="font-weight: bold; padding-right: 15px;">รวมยอดรายงานภาษีซื้อ (สำนักงานใหญ่)</td>
            <td class="text-right no-border" style="font-weight: bold;"></td>
            <td class="text-right" style="font-weight: bold;"><?= number_format($totalBaseHq, 2) ?></td>
            <td class="text-right" style="font-weight: bold;"><?= number_format($totalVatHq, 2) ?></td>
            <td class="no-border"></td>
        </tr>
    </tfoot>
</table>

<div style="margin-top: 40px; margin-bottom: 20px; font-weight: bold; text-align: center; font-size: 16px;">
    รายงานภาษีซื้อ (สาขา)
</div>

<table class="report-table">
    <tr>
        <td colspan="7" class="text-right no-border" style="font-weight: bold; padding-right: 15px; width: 71%;">รวมยอดจำนวนภาษีซื้อ (สาขา)</td>
        <td class="text-right no-border" style="font-weight: bold; width: 8%;"></td>
        <td class="text-right" style="font-weight: bold; width: 8%;"><?= number_format($totalBaseBr, 2) ?></td>
        <td class="text-right" style="font-weight: bold; width: 8%;"><?= number_format($totalVatBr, 2) ?></td>
        <td class="no-border" style="width: 5%;"></td>
    </tr>
    <tr>
        <td colspan="7" class="text-right no-border" style="font-weight: bold; padding-right: 15px;">รวมยอดจำนวนภาษีซื้อทั้งสิ้น</td>
        <td class="text-right no-border" style="font-weight: bold;"></td>
        <td class="text-right" style="font-weight: bold;"><?= number_format($totalBaseHq + $totalBaseBr, 2) ?></td>
        <td class="text-right" style="font-weight: bold;"><?= number_format($totalVatHq + $totalVatBr, 2) ?></td>
        <td class="no-border"></td>
    </tr>
</table>
