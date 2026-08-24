<?php
use yii\helpers\Html;

$this->title = 'None PR - ' . $model->docnum;

// Fetch vendor for additional info if needed
$vendor = \backend\models\Vendor::findOne(['code' => $model->supcod]);
$vendor_email = $vendor ? $vendor->email : '';
$is_head = $vendor ? $vendor->is_head : 0;
$branch_name = $vendor ? $vendor->branch_name : '';

$fmt = Yii::$app->formatter;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            color: #000;
            line-height: 1.5;
            position: relative;
        }
        .header-box {
            border: 1px solid #000;
            padding: 15px;
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        .header-logo {
            width: 200px;
        }
        .header-text {
            flex: 1;
            padding-left: 20px;
        }
        .header-text h2 {
            margin: 0 0 10px 0;
            font-size: 20px;
            font-weight: bold;
        }
        .header-text p {
            margin: 3px 0;
            font-size: 12px;
        }
        .row-group {
            display: flex;
            margin-bottom: 10px;
            align-items: flex-end;
        }
        .field-label {
            font-weight: bold;
            margin-right: 10px;
            white-space: nowrap;
        }
        .field-value {
            border-bottom: 1px solid #000;
            flex: 1;
            min-height: 20px;
            padding-left: 10px;
        }
        .field-value-inline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            padding: 0 10px;
            text-align: center;
        }
        
        .radio-box {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            border-radius: 50%;
            margin-right: 5px;
            position: relative;
            top: 3px;
        }
        .radio-box.checked {
            background-color: #000;
        }
        
        .items-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .items-table th {
            text-align: center;
            font-weight: bold;
            padding: 10px;
            border-bottom: 2px solid transparent;
        }
        .items-table td {
            padding: 8px 10px;
            text-align: center;
        }
        .items-table td:first-child {
            text-align: left;
        }
        
        .totals-section {
            width: 300px;
            float: right;
            margin-top: 50px;
            margin-bottom: 30px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        
        .sign-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .sign-box {
            width: 45%;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: -1;
            width: 80%;
        }
        
        .footer-logos {
            text-align: center;
            margin-top: 50px;
        }
        .footer-logos img {
            max-height: 60px;
            margin: 0 10px;
        }
        
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Print</button>
    </div>

    <!-- Watermark -->
    <img src="<?= Yii::getAlias('@web') ?>/uploads/logo/mco_logo_2.png" class="watermark" alt="Watermark">

    <!-- Header Box -->
    <div class="header-box">
        <div class="header-logo">
            <img src="<?= Yii::getAlias('@web') ?>/uploads/logo/mco_logo_2.png" style="width: 100%;" alt="MCO Logo">
        </div>
        <div class="header-text">
            <h2>M.C.O. COMPANY LIMITED</h2>
            <p>8/18 KOH-KLOY, TAMBON CHERNGNOEN, MUANG, RAYONG 21000 THAILAND</p>
            <p>
                <a href="#" style="color: #0000EE; text-decoration: underline;">Tel :</a> (6638) 875258, <u>875259 FAX</u> : (6638) 619559<br>
                <a href="http://www.thai-mco.com" style="color: #0000EE; text-decoration: underline;">http://www.thai-mco.com</a> 
                e-mail: <a href="mailto:info@thai-mco.com" style="color: #0000EE; text-decoration: underline;">info@thai-mco.com</a>
            </p>
        </div>
    </div>

    <!-- Doc Info -->
    <div class="row-group">
        <div class="field-label">Date:</div>
        <div class="field-value" style="flex: 0 0 200px; margin-right: 50px;">
            <?= $fmt->asDate($model->docdat, 'php:d/m/Y') ?>
        </div>
        <div class="field-label" style="margin-left: auto;">NPR No. :</div>
        <div class="field-value" style="flex: 0 0 250px;">
            <?= Html::encode($model->docnum) ?>
        </div>
    </div>

    <div class="row-group">
        <div class="field-label">Payment to (Company Name / Contact Name) :</div>
        <div class="field-value">
            <?= Html::encode($model->supnam) ?>
        </div>
    </div>

    <div class="row-group">
        <div class="field-label">Branch :</div>
        <div style="margin-right: 20px;">
            <div class="radio-box <?= $is_head == 1 ? 'checked' : '' ?>"></div> สำนักงานใหญ่
        </div>
        <div>
            <div class="radio-box <?= $is_head != 1 && !empty($branch_name) ? 'checked' : '' ?>"></div> สำนักงานสาขาที่
            <div class="field-value-inline" style="min-width: 100px;"><?= Html::encode($is_head != 1 ? $branch_name : '') ?></div>
        </div>
        <div class="field-label" style="margin-left: auto;">Tax ID :</div>
        <div class="field-value" style="flex: 0 0 200px;">
            <?= Html::encode($model->taxid) ?>
        </div>
    </div>

    <div class="row-group">
        <div class="field-label">Address:</div>
        <div class="field-value">
            <?= Html::encode(trim($model->addr01 . ' ' . $model->addr02 . ' ' . $model->addr03)) ?>
        </div>
    </div>

    <div class="row-group">
        <div class="field-label">Telephone :</div>
        <div class="field-value" style="margin-right: 20px;">
            <?= Html::encode($model->telnum) ?>
        </div>
        <div class="field-label">E-mail Address :</div>
        <div class="field-value">
            <?= Html::encode($vendor_email) ?>
        </div>
    </div>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Description</th>
                <th style="width: 15%;">Qt'y</th>
                <th style="width: 15%;">Unit</th>
                <th style="width: 25%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal = 0;
            foreach ($model->purchaseDetails as $detail): 
                $subtotal += (float)$detail->amount;
            ?>
            <tr>
                <td><?= Html::encode($detail->stkdes) ?></td>
                <td><?= number_format($detail->uqnty, 1) ?></td>
                <td><?= Html::encode($detail->unit ?? '') ?></td>
                <td><?= number_format($detail->amount, 2) ?></td>
            </tr>
            <?php endforeach; ?>
            
            <?php 
            // Add some empty rows to keep the layout nice
            $emptyRows = 5 - count($model->purchaseDetails);
            for ($i = 0; $i < $emptyRows; $i++): 
            ?>
            <tr>
                <td><br></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals-section">
            <?php 
            $discount = floatval($model->disc);
            $afterDiscount = $subtotal - $discount;
            ?>
            <div class="totals-row">
                <div>Total</div>
                <div><?= number_format($subtotal, 2) ?></div>
            </div>
            <div class="totals-row">
                <div>Discount</div>
                <div><?= number_format($discount, 2) ?></div>
            </div>
            <div class="totals-row">
                <div>Amount</div>
                <div><?= number_format($afterDiscount, 2) ?></div>
            </div>
            <div class="totals-row">
                <div>Vat</div>
                <div><?= number_format($model->vat_amount, 2) ?></div>
            </div>
            <div class="totals-row">
                <div>WHT</div>
                <div><?= number_format($model->tax_amount, 2) ?></div>
            </div>
            <div class="totals-row">
                <div>Total Amount</div>
                <div><?= number_format($model->total_amount, 2) ?></div>
            </div>
        </div>
    </div>

    <!-- Bottom Fields -->
    <div class="row-group" style="margin-top: 20px;">
        <div class="field-label">Term Payment :</div>
        <div style="margin-right: 15px;">
            <div class="radio-box <?= $model->paytrm == 'Advance Payment' ? 'checked' : '' ?>"></div> Advance Payment
        </div>
        <div style="margin-right: 15px;">
            <div class="radio-box <?= $model->paytrm == 'Immediately' ? 'checked' : '' ?>"></div> Immediately
        </div>
        <div style="margin-right: 15px;">
            <div class="radio-box <?= $model->paytrm == '30 Days' ? 'checked' : '' ?>"></div> 30 Days
        </div>
        <div>
            <div class="radio-box <?= !in_array($model->paytrm, ['Advance Payment', 'Immediately', '30 Days']) && !empty($model->paytrm) ? 'checked' : '' ?>"></div> Other
            <div class="field-value-inline" style="min-width: 80px;">
                <?= Html::encode(!in_array($model->paytrm, ['Advance Payment', 'Immediately', '30 Days']) ? $model->paytrm : '') ?>
            </div>
        </div>
    </div>

    <div class="row-group">
        <div class="field-label">Account Name :</div>
        <div class="field-value" style="margin-right: 10px;"></div>
        
        <div class="field-label">Account No.</div>
        <div class="field-value" style="margin-right: 10px;"></div>
        
        <div class="field-label">Bank Name :</div>
        <div class="field-value" style="margin-right: 10px;"></div>
        
        <div class="field-label">Branch :</div>
        <div class="field-value"></div>
    </div>

    <!-- Signatures -->
    <div class="sign-section">
        <div class="sign-box">
            <div class="row-group">
                <div class="field-label">Request / Prepare By :</div>
                <div class="field-value">
                    <?php 
                    if ($model->created_by) {
                        $user = \common\models\User::findOne($model->created_by);
                        echo $user ? Html::encode($user->username) : '';
                    }
                    ?>
                </div>
            </div>
            <div class="row-group" style="margin-top: 20px;">
                <div class="field-label">Date :</div>
                <div class="field-value">
                    <?= $model->created_at ? $fmt->asDate($model->created_at, 'php:d/m/Y') : '' ?>
                </div>
            </div>
        </div>
        <div class="sign-box">
            <div class="row-group">
                <div class="field-label">Authorized By :</div>
                <div class="field-value"></div>
            </div>
            <div class="row-group" style="margin-top: 20px;">
                <div class="field-label">Date :</div>
                <div class="field-value"></div>
            </div>
        </div>
    </div>

    <!-- Footer Logos -->
    <div class="footer-logos">
        <img src="<?= Yii::getAlias('@web') ?>/uploads/Picture1.jpg" alt="MCO Stamp">
    </div>

</body>
</html>
