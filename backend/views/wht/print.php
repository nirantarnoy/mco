<?php
use yii\helpers\Html;
$formatter = \Yii::$app->formatter;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>หนังสือรับรองการหักภาษี ณ ที่จ่าย : <?= Html::encode($model->wht_no) ?></title>
    <style>
        body { font-family: 'Sarabun', 'Arial', sans-serif; font-size: 14px; margin: 0; padding: 20px; line-height: 1.4; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .pull-right { float: right; }
        .pull-left { float: left; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .wht-form { border: 1px solid #000; padding: 10px; position: relative; }
        .doc-no { position: absolute; top: 10px; left: 10px; font-size: 12px; }
        .doc-ref { position: absolute; top: 10px; right: 10px; font-size: 12px; }
        
        .section-box { border: 1px solid #000; padding: 10px; margin-bottom: 5px; }
        .grid-container { display: flex; }
        .col-left { width: 150px; font-weight: bold; }
        .col-right { flex-grow: 1; border-bottom: 1px dotted #000; }
        
        table.wht-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.wht-table th, table.wht-table td { border: 1px solid #000; padding: 5px; }
        table.wht-table th { text-align: center; }
        
        .check-box { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; margin-right: 5px; vertical-align: middle; text-align: center; line-height: 12px; font-size: 10px; }
        .checked::after { content: '✓'; }

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

    <div class="wht-form">
        <div class="doc-no">เล่มที่ / เลขที่ <b><?= Html::encode($model->wht_no) ?></b></div>
        
        <div class="text-center title" style="margin-top: 30px;">
            หนังสือรับรองการหักภาษี ณ ที่จ่าย<br>
            <span style="font-size: 14px; font-weight: normal;">ตามมาตรา 50 ทวิ แห่งประมวลรัษฎากร</span>
        </div>

        <div class="section-box" style="margin-top: 15px;">
            <b>ผู้มีหน้าที่หักภาษี ณ ที่จ่าย:</b><br>
            ชื่อ: <b>บริษัท เอ็ม.ซี.โอ. จำกัด</b><br>
            ที่อยู่: 228/15 ถ.พหลโยธิน แขวงสามเสนใน เขตพญาไท กรุงเทพมหานคร 10400<br>
            เลขประจำตัวผู้เสียภาษีอากร: <b>0105543000955</b>
        </div>

        <div class="section-box">
            <b>ผู้ถูกหักภาษี ณ ที่จ่าย:</b><br>
            ชื่อ: <b><?= $model->vendor ? Html::encode($model->vendor->name) : '-' ?></b><br>
            ที่อยู่: <b><?= $model->vendor ? Html::encode($model->vendor->address) : '-' ?></b><br>
            เลขประจำตัวผู้เสียภาษีอากร: <b><?= $model->vendor ? Html::encode($model->vendor->tax_id) : '-' ?></b>
        </div>

        <div style="margin-top: 10px;">
            <b>แบบที่ยื่น:</b>
            <span class="check-box <?= $model->wht_type == 3 ? 'checked' : '' ?>"></span> (1) ภ.ง.ด. 3 &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="check-box <?= $model->wht_type == 53 ? 'checked' : '' ?>"></span> (2) ภ.ง.ด. 53
        </div>

        <table class="wht-table">
            <thead>
                <tr>
                    <th width="45%">ประเภทเงินได้ที่จ่าย</th>
                    <th width="20%">วัน เดือน ปี ที่จ่าย</th>
                    <th width="20%">จำนวนเงินที่จ่าย</th>
                    <th width="15%">ภาษีที่หักและนำส่งไว้</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?= Html::encode($model->wht_desc) ?>
                        <?= $model->other_desc ? ' (' . Html::encode($model->other_desc) . ')' : '' ?>
                    </td>
                    <td class="text-center"><?= $model->trans_date ? $formatter->asDate($model->trans_date, 'php:d/m/Y') : '' ?></td>
                    <td class="text-right"><?= number_format($model->base_amount, 2) ?></td>
                    <td class="text-right"><?= number_format($model->wht_amount, 2) ?></td>
                </tr>
                <tr>
                    <td colspan="2" class="text-right"><b>รวมเงินภาษีที่หักนำส่ง</b></td>
                    <td class="text-right"><b><?= number_format($model->base_amount, 2) ?></b></td>
                    <td class="text-right"><b><?= number_format($model->wht_amount, 2) ?></b></td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 10px;">
            <b>ผู้จ่ายเงิน:</b>
            <span class="check-box <?= $model->pay_condition == 1 ? 'checked' : '' ?>"></span> (1) หัก ณ ที่จ่าย &nbsp;&nbsp;
            <span class="check-box <?= $model->pay_condition == 2 ? 'checked' : '' ?>"></span> (2) ออกภาษีให้ตลอดไป &nbsp;&nbsp;
            <span class="check-box <?= $model->pay_condition == 3 ? 'checked' : '' ?>"></span> (3) ออกภาษีให้ครั้งเดียว
        </div>

        <div style="margin-top: 40px; text-align: center;">
            <p>ขอรับรองว่าข้อความและตัวเลขดังกล่าวข้างต้นถูกต้องตรงกับความจริงทุกประการ</p>
            <br><br>
            ลงชื่อ ............................................................................ ผู้มีหน้าที่หักภาษี ณ ที่จ่าย<br>
            <span style="display: inline-block; margin-top: 10px;">
                วันที่ <?= $model->trans_date ? $formatter->asDate($model->trans_date, 'php:d/m/Y') : '......./......./.......' ?>
            </span>
        </div>
    </div>
</body>
</html>
