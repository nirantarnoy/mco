<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $model backend\models\PaymentVoucher */

$options = \backend\models\PaymentVoucher::getPaymentMethodOptions();
$payment_method_label = $options[$model->payment_method] ?? '';

// Find logo if exists
$logo = '/img/logo_mco.png'; // Update with actual path if known
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Voucher - <?= $model->voucher_no ?></title>
    <style>
        body { font-family: 'sarabun', 'garuda', sans-serif; font-size: 14px; margin: 0; padding: 20px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table td { vertical-align: top; }
        .logo { width: 180px; }
        .title { font-size: 20px; font-weight: bold; text-align: center; }
        .info-box { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-box td { padding: 5px 0; }
        .label { font-weight: bold; }
        .underline { border-bottom: 1px solid #000; display: inline-block; min-width: 150px; padding: 0 5px; }
        
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .main-table th { border: 2px solid #000; padding: 8px; text-align: center; background-color: #eee; }
        .main-table td { border: 1px solid #000; padding: 8px; vertical-align: top; height: 25px; }
        .main-table .border-bold { border: 2px solid #000; }
        
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { border: 1px solid #000; width: 25%; height: 120px; padding: 5px; text-align: center; vertical-align: top; }
        .signature-img { max-height: 50px; max-width: 140px; display: block; margin: 0 auto; }
        .sig-container { height: 60px; display: flex; align-items: flex-end; justify-content: center; margin-bottom: 5px; border-bottom: 1px dotted #ccc; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
        
        .checkbox-container { display: inline-block; margin-right: 15px; }
        .checkbox-box { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; margin-right: 5px; position: relative; top: 2px; }
        .checkbox-box.checked { background-color: #000; }
        .checkbox-box.circle { border-radius: 50%; }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 20px; text-align: right;">
    <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print</button>
</div>

<table class="header-table">
    <tr>
        <td style="width: 30%;">
            <img src="<?= Yii::$app->request->baseUrl ?>/uploads/logo/mco_logo.png" class="logo" onerror="this.src='https://via.placeholder.com/180x60?text=MCO+GROUP'">
        </td>
        <td style="width: 40%;" class="title">PAYMENT VOUCHER</td>
        <td style="width: 30%; text-align: right;">
            <div style="margin-bottom: 10px;">PV No. <span class="underline" style="min-width: 120px;"><?= $model->voucher_no ?></span></div>
            <div>Date <span class="underline" style="min-width: 120px;"><?= date('d/m/Y', strtotime($model->trans_date)) ?></span></div>
        </td>
    </tr>
</table>

<table class="info-box">
    <tr>
        <td style="width: 10%;"><span class="label">Name</span></td>
        <td colspan="3"><span class="underline" style="width: 90%;"><?= Html::encode($model->recipient_name) ?></span></td>
    </tr>
    <tr>
        <td><span class="label">By</span></td>
        <td colspan="3">
            <div class="checkbox-container">
                <div class="checkbox-box circle <?= $model->payment_method == \backend\models\PaymentVoucher::PAY_METHOD_CASH ? 'checked' : '' ?>"></div> Cash/TR/TT
            </div>
            <div class="checkbox-container">
                <div class="checkbox-box circle <?= $model->payment_method == \backend\models\PaymentVoucher::PAY_METHOD_CHEQUE ? 'checked' : '' ?>"></div> Bank of Cheque
            </div>
        </td>
    </tr>
    <tr>
        <td></td>
        <td style="width: 30%;"><span class="label">Cheque No.</span> <span class="underline" style="min-width: 150px;"><?= $model->cheque_no ?></span></td>
        <td style="width: 40%; text-align: center;"><span class="label">วันที่หน้าเช็ค</span> <span class="underline" style="min-width: 150px;"><?= $model->cheque_date ? date('d/m/Y', strtotime($model->cheque_date)) : '' ?></span></td>
        <td></td>
    </tr>
    <tr>
        <td><span class="label">Amount</span></td>
        <td><span class="underline" style="min-width: 150px;"><?= number_format($model->amount, 2) ?></span></td>
        <td><span class="label">Paid For/PR/PO/QT</span></td>
        <td><span class="underline" style="min-width: 250px;"><?= Html::encode($model->paid_for) ?></span></td>
    </tr>
</table>

<table class="main-table">
    <thead>
        <tr>
            <th style="width: 10%;">Code Acc.</th>
            <th style="width: 15%;">Code Bill</th>
            <th>Description</th>
            <th style="width: 15%;">Debit</th>
            <th style="width: 15%;">Credit</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $lines = $model->paymentVoucherLines;
        $total_debit = 0;
        $total_credit = 0;
        for($i = 0; $i < 10; $i++): 
            $line = $lines[$i] ?? null;
            if($line) {
                $total_debit += $line->debit;
                $total_credit += $line->credit;
            }
        ?>
        <tr>
            <td style="text-align: center;"><?= $line ? Html::encode($line->account_code) : '' ?></td>
            <td style="text-align: center;"><?= $line ? Html::encode($line->bill_code) : '' ?></td>
            <td><?= $line ? Html::encode($line->description) : '' ?></td>
            <td style="text-align: right;"><?= ($line && $line->debit > 0) ? number_format($line->debit, 2) : '' ?></td>
            <td style="text-align: right;"><?= ($line && $line->credit > 0) ? number_format($line->credit, 2) : '' ?></td>
        </tr>
        <?php endfor; ?>
        <tr style="font-weight: bold; background-color: #eee;">
            <td colspan="3" style="text-align: right;">Total</td>
            <td style="text-align: right;"><?= number_format($total_debit, 2) ?></td>
            <td style="text-align: right;"><?= number_format($total_credit, 2) ?></td>
        </tr>
    </tbody>
</table>

<table class="footer-table">
    <tr>
        <td>
            <div style="height: 90px;"></div>
            Received by
        </td>
        <td>
            <div class="sig-container">
                <?php
                $prepared_sig = \backend\models\User::findEmployeeSignature($model->created_by);
                if ($prepared_sig): ?>
                    <img src="<?= Yii::$app->request->baseUrl ?>/uploads/employee_signature/<?= $prepared_sig ?>" class="signature-img">
                <?php endif; ?>
            </div>
            Prepared by
            <div style="font-size: 11px; margin-top: 5px;"><?= \backend\models\User::findEmployeeNameByUserId($model->created_by) ?></div>
        </td>
        <td>
            <div class="sig-container"></div>
            Checker by
        </td>
        <td>
            <div class="sig-container"></div>
            Authorized by
        </td>
    </tr>
</table>

</body>
</html>
