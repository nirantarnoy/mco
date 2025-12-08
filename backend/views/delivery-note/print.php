<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\DeliveryNote */

$this->title = 'Delivery Note ' . $model->dn_no;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
            line-height: 1.4;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 8px;
        }
        .items-table th {
            text-align: center;
            background-color: #f0f0f0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer-signatures {
            width: 100%;
            margin-top: 50px;
        }
        .signature-box {
            width: 45%;
            display: inline-block;
            text-align: center;
            vertical-align: top;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 30px auto 10px;
        }
        .company-logo {
            background-color: #fff;
            padding: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            display: inline-block;
        }
        @media print {
            @page {
                margin: 1cm;
            }
            body {
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>ใบตรวจรับ / Delivery note</h2>
        </div>

        <div class="company-info">
            <div class="company-logo">
                <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png',['style' => 'max-width: 150px;']) ?>
            </div><br>
            <strong>M.C.O. COMPANY LIMITED</strong><br>
            8/18 Koh-Kloy Rd., T. Cherngnoen,<br>
            A. Muang, Rayong 21000 Thailand.<br>
            ID.NO. 0215543000985<br>
            Tel : (038)-875258-9 , 094-6984555
        </div>

        <table class="info-table">
            <tr>
                <td width="60%">
                    <strong>To :</strong> <?= Html::encode($model->customer_name) ?><br>
                    <?= nl2br(Html::encode($model->address)) ?>
                </td>
                <td width="40%">
                    <table width="100%">
                        <tr>
                            <td><strong>Date :</strong></td>
                            <td><?= Yii::$app->formatter->asDate($model->date, 'php:d/m/Y') ?></td>
                        </tr>
                        <tr>
                            <td><strong>OUR REF :</strong></td>
                            <td><?= Html::encode($model->our_ref) ?></td>
                        </tr>
                        <tr>
                            <td><strong>FROM :</strong></td>
                            <td><?= Html::encode($model->from_name) ?></td>
                        </tr>
                        <tr>
                            <td><strong>TEL :</strong></td>
                            <td><?= Html::encode($model->tel) ?></td>
                        </tr>
                        <tr>
                            <td><strong>REF.NO. :</strong></td>
                            <td><?= Html::encode($model->ref_no) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Page No. :</strong></td>
                            <td><?= Html::encode($model->page_no) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Attn :</strong> <?= Html::encode($model->attn) ?>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="10%">ITEM</th>
                    <th width="40%">DESCRIPTION</th>
                    <th width="25%">P/N</th>
                    <th width="10%">Q'TY</th>
                    <th width="15%">UNIT</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($model->deliveryNoteLines as $line): ?>
                <tr>
                    <td class="text-center"><?= Html::encode($line->item_no) ?></td>
                    <td><?= nl2br(Html::encode($line->description)) ?></td>
                    <td class="text-center"><?= Html::encode($line->part_no) ?></td>
                    <td class="text-right"><?= Html::encode($line->qty) ?></td>
                    <td class="text-center"><?= $line->unit ? Html::encode($line->unit->name) : '' ?></td>
                </tr>
                <?php endforeach; ?>
                <!-- Fill empty rows if needed -->
                <?php for($i = count($model->deliveryNoteLines); $i < 10; $i++): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <div class="footer-signatures">
            <div class="signature-box">
                Recipient _____________________
                <div class="signature-line"></div>
                (_____________________)<br>
                Date _____________________
            </div>
            <div class="signature-box" style="float: right;">
                Sender _____________________
                <div class="signature-line"></div>
                (_____________________)<br>
                Date _____________________
            </div>
        </div>
    </div>
</body>
</html>
