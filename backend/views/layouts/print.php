<?php
// backend/views/layouts/print.php
use yii\helpers\Html;

$this->beginPage();
?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <style>
            body {
                font-family: 'Sarabun', Arial, sans-serif;
                font-size: 14px;
                margin: 0;
                padding: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .company-info {
                text-align: center;
                margin-bottom: 20px;
            }
            .billing-info {
                margin-bottom: 20px;
            }
            .customer-info {
                margin-bottom: 20px;
            }
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            .items-table th,
            .items-table td {
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
            }
            .items-table th {
                background-color: #f5f5f5;
            }
            .text-right {
                text-align: right;
            }
            .text-center {
                text-align: center;
            }
            .summary {
                float: right;
                width: 300px;
                margin-top: 20px;
            }
            .summary table {
                width: 100%;
                border-collapse: collapse;
            }
            .summary td {
                padding: 5px;
                border-bottom: 1px solid #ddd;
            }
            .total-row {
                font-weight: bold;
                border-top: 2px solid #000;
            }
            .signature {
                margin-top: 50px;
                display: flex;
                justify-content: space-between;
            }
            .signature-box {
                width: 200px;
                text-align: center;
            }
            .signature-line {
                border-top: 1px solid #000;
                margin-top: 50px;
                padding-top: 5px;
            }
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
        </style>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" class="btn btn-primary">พิมพ์</button>
        <button onclick="window.close()" class="btn btn-default">ปิด</button>
    </div>

    <?= $content ?>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>