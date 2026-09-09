<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $models array */
/* @var $month string */
/* @var $year string */

$this->title = 'รายงานภาษีซื้อ';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        body {
            font-family: 'Sarabun', 'Tahoma', sans-serif;
            font-size: 13px;
        }
        @media print {
            @page {
                size: landscape;
                margin: 1cm;
            }
        }
    </style>
</head>
<body onload="window.print();">

    <?= $this->render('_report', [
        'models' => $models,
        'month' => $month,
        'year' => $year,
    ]) ?>

</body>
</html>
