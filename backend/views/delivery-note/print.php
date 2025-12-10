<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\DeliveryNote */

$this->title = 'Delivery Note ' . $model->dn_no;

$this->registerCss("
    @font-face {
        font-family: 'THSarabunPSK';
        src: url('../../backend/web/fonts/thsarabun/THSarabunPSK.ttf') format('truetype');
        font-weight: normal;
    }
    @font-face {
        font-family: 'THSarabunPSK';
        src: url('../../backend/web/fonts/thsarabun/THSarabunPSK-Bold.ttf') format('truetype');
        font-weight: bold;
    }
    @font-face {
        font-family: 'THSarabunPSK';
        src: url('../../backend/web/fonts/thsarabun/THSarabunPSK-Italic.ttf') format('truetype');
        font-style: italic;
    }

    /* Standard View Styles */
    .delivery-note-view {
        font-family: 'THSarabunPSK', sans-serif;
        font-size: 16px;
        line-height: 1.4;
        background: #fff;
        padding: 20px;
    }

    .container {
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background: white;
        border: 1px solid #ddd;
        position: relative; /* For absolute positioning of label */
        margin-bottom: 20px;
    }

    /* Screen UI Controls */
    .btn-group {
        text-align: center;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap; 
    }
    
    .control-group {
        display: flex; 
        align-items: center; 
        gap: 5px; 
        margin-right: 10px;
    }
    
    .form-control {
        display: inline-block;
        width: auto;
        height: 34px; 
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        color: #555;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    /* Document Layout Styles */
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
        display: inline-block;
    }

    .copy-label {
        position: absolute;
        top: 20px;
        right: 20px;
        border: 1px solid #000;
        padding: 5px 15px;
        font-weight: bold;
        font-size: 18px;
        /* display: none; */ /* Initially shown or managed by JS? Better to always show in print */
    }

    /* Print Specifics */
    @media print {
        .no-print, .main-sidebar, .main-header, .content-header, .main-footer, .btn-group { display: none !important; }
        body, .content-wrapper { 
            margin: 0 !important; 
            padding: 0 !important; 
            background-color: white !important;
        }
        .container {
            border: none !important;
            width: 100% !important;
            max-width: none !important;
            padding: 0;
            margin: 0;
            margin-bottom: 0 !important;
            page-break-after: always;
        }
        .container:last-child {
            page-break-after: auto;
        }
        @page {
            margin: 1cm;
        }
    }
");
?>
<script>
    window.changeHeader = function() {
        var headerSelect = document.getElementById('headerSelect');
        var selectedValue = headerSelect.value;
        var companyNames = document.querySelectorAll('.companyName');

        companyNames.forEach(function(el) {
            if (selectedValue === 'mco') {
                el.innerHTML = '<strong>M.C.O. COMPANY LIMITED</strong>';
            } else {
                el.innerHTML = '<strong>' + selectedValue + '</strong>';
            }
        });
        // Note: Address and Logo remain static M.C.O. as per requirement.
    };

    window.changeLanguage = function() {
        var lang = document.getElementById('languageSelect').value;

        // Helper to safely set innerHTML by class
        var safeSetHtmlClass = function(className, html) {
            var els = document.querySelectorAll('.' + className);
            els.forEach(function(el) {
                el.innerHTML = html;
            });
        };

        // Title
        safeSetHtmlClass('docTitle', (lang === 'th') ? 'ใบตรวจรับ / Delivery note' : 'Delivery Note');

        // Labels with Strong tags
        safeSetHtmlClass('labelDate', '<strong>' + ((lang === 'th') ? 'วันที่ / Date' : 'Date') + ' :</strong>');
        safeSetHtmlClass('labelTo', '<strong>' + ((lang === 'th') ? 'ถึง / To' : 'To') + ' :</strong>');
        safeSetHtmlClass('labelOurRef', '<strong>' + ((lang === 'th') ? 'อ้างอิงเอกสารเรา / OUR REF' : 'OUR REF') + ' :</strong>');
        safeSetHtmlClass('labelFrom', '<strong>' + ((lang === 'th') ? 'จาก / FROM' : 'FROM') + ' :</strong>');
        safeSetHtmlClass('labelTel', '<strong>' + ((lang === 'th') ? 'โทร / TEL' : 'TEL') + ' :</strong>');
        safeSetHtmlClass('labelRefNo', '<strong>' + ((lang === 'th') ? 'เลขที่อ้างอิง / REF.NO.' : 'REF.NO.') + ' :</strong>');
        safeSetHtmlClass('labelPageNo', '<strong>' + ((lang === 'th') ? 'หน้า / Page No.' : 'Page No.') + ' :</strong>');
        safeSetHtmlClass('labelAttn', '<strong>' + ((lang === 'th') ? 'เรียน / Attn' : 'Attn') + ' :</strong>');

        // Copy Labels (Original/Copy)
        var copyLabels = document.querySelectorAll('.copy-text');
        copyLabels.forEach(function(el) {
            var type = el.getAttribute('data-type');
            if (lang === 'th') {
                el.innerHTML = (type === 'original') ? 'ต้นฉบับ' : 'สำเนา';
            } else {
                el.innerHTML = (type === 'original') ? 'Original' : 'Copy';
            }
        });

        // Table Headers
        var tables = document.querySelectorAll('.items-table');
        tables.forEach(function(table) {
            var th = table.querySelectorAll('th');
            if (th.length >= 5) {
                if (lang === 'th') {
                    th[0].innerHTML = 'ลำดับ<br>ITEM';
                    th[1].innerHTML = 'รายการ<br>DESCRIPTION';
                    th[2].innerHTML = 'หมายเลขสินค้า<br>P/N';
                    th[3].innerHTML = 'จำนวน<br>Q\'TY';
                    th[4].innerHTML = 'หน่วย<br>UNIT';
                } else {
                    th[0].innerHTML = 'ITEM';
                    th[1].innerHTML = 'DESCRIPTION';
                    th[2].innerHTML = 'P/N';
                    th[3].innerHTML = 'Q\'TY';
                    th[4].innerHTML = 'UNIT';
                }
            }
        });

        // Footer Signatures
        if (lang === 'th') {
            safeSetHtmlClass('sigRecipient', 'ผู้รับของ / Recipient _____________________');
            safeSetHtmlClass('sigSender', 'ผู้ส่งของ / Sender _____________________');
        } else {
            safeSetHtmlClass('sigRecipient', 'Recipient _____________________');
            safeSetHtmlClass('sigSender', 'Sender _____________________');
        }
    };
</script>

<div class="delivery-note-view">
    <!-- Buttons & Controls -->
    <div class="btn-group no-print">
        <div class="control-group">
            <span style="font-weight: bold;">เลือกบริษัท:</span>
            <select id="headerSelect" class="form-control" onchange="changeHeader()">
                <option value="mco">M.C.O. COMPANY LIMITED (Default)</option>
                <?php
                $companies = \backend\models\Company::find()->all();
                foreach ($companies as $comp) {
                    if (strtoupper($comp->name) !== 'M.C.O. COMPANY LIMITED') {
                        echo '<option value="' . Html::encode($comp->name) . '">' . Html::encode($comp->name) . '</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="control-group">
            <span style="font-weight: bold;">เลือกภาษา:</span>
            <select id="languageSelect" class="form-control" onchange="changeLanguage()">
                <option value="en">English Only (ENG)</option>
                <option value="th" selected>Thai & English (TH/ENG)</option>
            </select>
        </div>

        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> พิมพ์
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> ปิด
        </button>
        <a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-info">
            <i class="fas fa-eye"></i> ดูรายละเอียด
        </a>
    </div>

    <?php
    // Define copies: 1 Original + 2 Copies
    $copies = [
        ['type' => 'original', 'text' => 'ต้นฉบับ'],
        ['type' => 'copy', 'text' => 'สำเนา'],
        ['type' => 'copy', 'text' => 'สำเนา'],
    ];
    ?>

    <?php foreach ($copies as $copy): ?>
        <!-- Page Container -->
        <div class="container">

            <!-- Copy Label (Top Right) -->
            <div class="copy-label">
                <span class="copy-text" data-type="<?= $copy['type'] ?>"><?= $copy['text'] ?></span>
            </div>

            <div class="header">
                <h2 class="docTitle">ใบตรวจรับ / Delivery note</h2>
            </div>

            <div class="company-info">
                <div class="company-logo">
                    <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png', ['style' => 'max-width: 150px;', 'class' => 'companyLogoImg']) ?>
                </div><br>

                <div class="companyName" style="margin-bottom: 5px;">
                    <strong>M.C.O. COMPANY LIMITED</strong>
                </div>
                <div class="companyAddress">
                    8/18 Koh-Kloy Rd., T. Cherngnoen,<br>
                    A. Muang, Rayong 21000 Thailand.<br>
                    ID.NO. 0215543000985<br>
                    Tel : (038)-875258-9 , 094-6984555
                </div>

                <div style="text-align: right; margin-top: -60px;">
                    <span class="labelDate"><strong>วันที่ / Date :</strong></span> <?= Yii::$app->formatter->asDate($model->date, 'php:d/m/Y') ?>
                </div>
            </div>

            <table class="info-table" style="margin-top: 40px;">
                <tr>
                    <td width="60%">
                        <span class="labelTo"><strong>ถึง / To :</strong></span> <?= Html::encode($model->customer_name) ?><br>
                        <?= nl2br(Html::encode($model->address)) ?>
                    </td>
                    <td width="40%">
                        <table width="100%">

                            <tr>
                                <td><span class="labelOurRef"><strong>อ้างอิงเอกสารเรา / OUR REF :</strong></span></td>
                                <td style="border-bottom: 1px dotted #000;"><?= Html::encode($model->our_ref) ?></td>
                            </tr>
                            <tr>
                                <td><span class="labelFrom"><strong>จาก / FROM :</strong></span></td>
                                <td style="border-bottom: 1px dotted #000;"><?= Html::encode($model->from_name) ?></td>
                            </tr>
                            <tr>
                                <td><span class="labelTel"><strong>โทร / TEL :</strong></span></td>
                                <td style="border-bottom: 1px dotted #000;"><?= Html::encode($model->tel) ?></td>
                            </tr>
                            <tr>
                                <td><span class="labelRefNo"><strong>เลขที่อ้างอิง / REF.NO. :</strong></span></td>
                                <td style="border-bottom: 1px dotted #000;"><?= Html::encode($model->ref_no) ?></td>
                            </tr>
                            <tr>
                                <td><span class="labelPageNo"><strong>หน้า / Page No. :</strong></span></td>
                                <td style="border-bottom: 1px dotted #000;"><?= Html::encode($model->page_no) ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span class="labelAttn"><strong>เรียน / Attn :</strong></span> <?= Html::encode($model->attn) ?>
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
                    <?php for ($i = count($model->deliveryNoteLines); $i < 10; $i++): ?>
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
                    <span class="sigRecipient">ผู้รับของ / Recipient _____________________</span>
                    <div class="signature-line"></div>
                    (_____________________)<br>
                    Date _____________________
                </div>
                <div class="signature-box" style="float: right;">
                    <span class="sigSender">ผู้ส่งของ / Sender _____________________</span>
                    <div class="signature-line"></div>
                    (_____________________)<br>
                    Date _____________________
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>