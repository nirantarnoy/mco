<?php
use yii\helpers\Html;

$assetDir = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <style>
            @font-face {
                font-family: 'Kanit-Regular';
                /*font-family: 'TH-Sarabun-New';*/
                /*src: url('fonts/THSarabunNew.ttf') format('truetype');*/
                src: url('fonts/Kanit-Regular.ttf') format('truetype');
                /*src: url('../../backend/web/fonts/Kanit-Regular.ttf') format('truetype');*/
                /* src: url('../fonts/thsarabunnew-webfont.eot?#iefix') format('embedded-opentype'),
                      url('../fonts/thsarabunnew-webfont.woff') format('woff'),
                      url('../fonts/EkkamaiStandard-Light.ttf') format('truetype');*/
                font-weight: normal;
                font-style: normal;
            }
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
            body {
                font-family: Arial, sans-serif;
                font-size: 14px;
                line-height: 1.4;
            }
        </style>
    </head>
    <body>
    <?php $this->beginBody() ?>
    <?= $content ?>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>