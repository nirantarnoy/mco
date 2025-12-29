<?php
use yii\helpers\Html;

/* @var $models backend\models\PurchPaymentLine[] */
/* @var $from_date string */
/* @var $to_date string */

$total = 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>รายงานสรุปการจ่ายเงิน</title>
    <style>
        body {
            font-family: "thsarabun";
            font-size: 14pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }
        th {
            background-color: #f0f0f0;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>รายงานสรุปการจ่ายเงิน</h3>
        <p>ประจำวันที่ <?= Yii::$app->formatter->asDate($from_date, 'php:d/m/Y') ?> ถึง <?= Yii::$app->formatter->asDate($to_date, 'php:d/m/Y') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50px;">ลำดับ</th>
                <th style="width: 100px;">วันที่โอน</th>
                <th style="width: 120px;">เลขที่ใบสั่งซื้อ</th>
                <th>ผู้ขาย</th>
                <th style="width: 150px;">ธนาคาร</th>
                <th style="width: 100px;">จำนวนเงิน</th>
                <th>หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($models as $index => $model): ?>
                <?php $total += $model->pay_amount; ?>
                <tr>
                    <td class="text-center"><?= $index + 1 ?></td>
                    <td class="text-center"><?= Yii::$app->formatter->asDate($model->trans_date, 'php:d/m/Y') ?></td>
                    <td class="text-center"><?= $model->purchPayment->purch->purch_no ?? '-' ?></td>
                    <td><?= $model->purchPayment->purch->vendor_name ?? '-' ?></td>
                    <td><?= $model->bank_name ?></td>
                    <td class="text-right"><?= number_format($model->pay_amount, 2) ?></td>
                    <td><?= $model->nodet ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="5" class="text-right">รวมทั้งสิ้น</td>
                <td class="text-right"><?= number_format($total, 2) ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
