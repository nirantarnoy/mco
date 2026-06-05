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
        body { font-family: 'Sarabun', 'Arial', sans-serif; font-size: 14px; margin: 0; padding: 20px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .pull-right { float: right; }
        .pull-left { float: left; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .content-table th, .content-table td { border: 1px solid #000; padding: 8px; }
        .content-table th { background-color: #f2f2f2; text-align: center; }
        .sign-table { width: 100%; margin-top: 50px; }
        .sign-table td { text-align: center; width: 33%; vertical-align: bottom; height: 100px; }
        .sign-line { border-bottom: 1px solid #000; display: inline-block; width: 80%; margin-bottom: 10px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print();" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Print</button>
    </div>

    <div class="text-center title">
        ใบขออนุมัติค่าใช้จ่าย/ตั้งเบิก (Pre-Advance)
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><b>Pre-Advance No:</b></td>
            <td width="35%"><?= Html::encode($model->pre_advance_no) ?></td>
            <td width="15%" class="text-right"><b>วันที่ (Date):</b></td>
            <td width="35%"><?= Html::encode($formatter->asDate($model->trans_date, 'php:d/m/Y')) ?></td>
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
                <th width="10%">ลำดับ<br>(No.)</th>
                <th width="15%">วันที่<br>(Date)</th>
                <th width="45%">รายละเอียดการขออนุมัติค่าใช้จ่าย/ตั้งเบิก<br>(Description)</th>
                <th width="15%">ยอดเบิก<br>(Amount)</th>
                <th width="15%">หมายเหตุ<br>(Remark)</th>
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
                    <td><?= Html::encode($line->description) ?></td>
                    <td class="text-right"><?= number_format($line->amount, 2) ?></td>
                    <td><?= Html::encode($line->remark) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php 
            // Add empty rows to fill up space if needed
            for ($j = $i; $j <= max(10, $i); $j++): 
            ?>
                <tr>
                    <td class="text-center">&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endfor; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><b>ยอดรวมทั้งหมด (Total Amount)</b></td>
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
