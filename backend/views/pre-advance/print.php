<?php
use yii\helpers\Html;

$formatter = \Yii::$app->formatter;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Pre-Advance : <?= Html::encode($model->pre_advance_no) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 12pt; 
            margin: 0; 
            padding: 20px; 
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .title { font-size: 16pt; font-weight: bold; margin-bottom: 20px; }
        
        .header-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .header-table td { padding: 3px; vertical-align: top; }
        
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .content-table th, .content-table td { border: 1px solid #000; padding: 6px; }
        .content-table th { background-color: #f2f2f2; text-align: center; font-size: 11pt; font-weight: bold; vertical-align: middle; }
        .content-table td { font-size: 11pt; }
        
        .sign-table { width: 100%; margin-top: 40px; }
        .sign-table td { text-align: center; width: 33%; vertical-align: bottom; height: 80px; font-size: 11pt; }
        .sign-line { border-bottom: 1px solid #000; display: inline-block; width: 70%; margin-bottom: 10px; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print();" style="padding: 10px 20px; font-size: 14pt; cursor: pointer;">Print</button>
    </div>

    <div class="text-center title">
        ใบขออนุมัติค่าใช้จ่าย/ตั้งเบิก (Pre-Advance)
    </div>

    <table class="header-table">
        <tr>
            <td width="15%"><b>Pre-Advance No:</b></td>
            <td width="55%"><?= Html::encode($model->pre_advance_no) ?></td>
            <td width="15%" class="text-right"><b>วันที่ (Date):</b></td>
            <td width="15%"><?= Html::encode($formatter->asDate($model->trans_date, 'php:d/m/Y')) ?></td>
        </tr>
        <tr>
            <td><b>ชื่อผู้รับเงิน:</b></td>
            <td><?= Html::encode($model->recipient_name) ?></td>
            <td class="text-right"><b>Vendor:</b></td>
            <td><?= $model->vendor ? Html::encode($model->vendor->name) : '-' ?></td>
        </tr>
        <tr>
            <td><b>หมายเหตุ:</b></td>
            <td colspan="3"><?= Html::encode($model->remark) ?></td>
        </tr>
    </table>

    <table class="content-table">
        <thead>
            <tr>
                <th width="4%">ลำดับ<br>(No.)</th>
                <th width="8%">วันที่<br>(Date)</th>
                <th width="12%">เลขที่อ้างอิงเอกสาร<br>Ref. No.(PO / NON PR)</th>
                <th width="15%">ชื่อบริษัท ห้าง ร้าน<br>บุคคล ตามบิลเอกสาร<br>(Receipt Name)</th>
                <th width="21%">รายละเอียดการขออนุมัติ<br>ค่าใช้จ่าย/ตั้งเบิก<br>(Description)</th>
                <th width="9%">ยอดก่อน<br>ภาษีมูลค่าเพิ่ม<br>(Value Before Vat)</th>
                <th width="7%">ภาษีมูลค่าเพิ่ม<br>(Vat)</th>
                <th width="7%">ภาษีหัก ณ ที่จ่าย<br>(Tax Withholding)</th>
                <th width="9%">ยอดสุทธิ<br>(Total Amount)</th>
                <th width="8%">หมายเหตุ<br>(Remark)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            foreach ($model->preAdvanceLines as $line): 
            ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td class="text-center"><?= $line->line_date ? Html::encode($formatter->asDate($line->line_date, 'php:d/m/Y')) : '' ?></td>
                    <td></td>
                    <td></td>
                    <td><?= Html::encode($line->description) ?></td>
                    <td class="text-right"></td>
                    <td class="text-right"></td>
                    <td class="text-right"></td>
                    <td class="text-right"><?= number_format($line->amount, 2) ?></td>
                    <td><?= Html::encode($line->remark) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php 
            // Add empty rows to fill up space if needed
            for ($j = $i; $j <= max(15, $i); $j++): 
            ?>
                <tr>
                    <td class="text-center">&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endfor; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-center" style="font-weight: bold; font-size: 12pt;">Summary</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right"><b><?= number_format($model->amount, 2) ?></b></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                <span class="sign-line"></span><br>
                ผู้ขอเบิก (Requested By)<br>
                วันที่ _______/_______/_______
            </td>
            <td>
                <span class="sign-line"></span><br>
                ผู้ตรวจสอบ (Checked By)<br>
                วันที่ _______/_______/_______
            </td>
            <td>
                <span class="sign-line"></span><br>
                ผู้อนุมัติ (Approved By)<br>
                วันที่ _______/_______/_______
            </td>
        </tr>
    </table>
</body>
</html>
