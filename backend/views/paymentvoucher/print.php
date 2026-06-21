<?php
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $model backend\models\PaymentVoucher */
/* @var $step int */

$options = \backend\models\PaymentVoucher::getPaymentMethodOptions();
$payment_method_label = $options[$model->payment_method] ?? '';

$step = isset($step) ? (int)$step : 1;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Voucher - <?= $model->voucher_no ?> (Step <?= $step ?>)</title>
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
        
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .main-table th { border: 2px solid #000; padding: 8px; text-align: center; background-color: #eee; }
        .main-table td { border: 1px solid #000; padding: 8px; vertical-align: middle; height: 25px; }
        .main-table .border-bold { border: 2px solid #000; }
        
        .footer-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .footer-table td { border: 1px solid #000; width: 25%; height: 120px; padding: 5px; text-align: center; vertical-align: top; }
        .signature-img { max-height: 50px; max-width: 140px; display: block; margin: 0 auto; }
        .sig-container { height: 60px; display: flex; align-items: flex-end; justify-content: center; margin-bottom: 5px; border-bottom: 1px dotted #ccc; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
        
        .checkbox-container { display: inline-block; margin-right: 15px; }
        .checkbox-box { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; margin-right: 5px; position: relative; top: 2px; }
        .checkbox-box.checked { background-color: #000 !important; }
        .checkbox-box.circle { border-radius: 50%; }
        
        .section-title { font-weight: bold; font-size: 15px; margin-top: 15px; margin-bottom: 5px; color: #333; border-left: 3px solid #007bff; padding-left: 8px; }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 20px; text-align: right;">
    <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 4px; font-weight: bold;">Print</button>
</div>

<table class="header-table">
    <tr>
        <td style="width: 30%;">
            <img src="<?= Yii::$app->request->baseUrl ?>/uploads/logo/mco_logo.png" class="logo" onerror="this.src='https://via.placeholder.com/180x60?text=MCO+GROUP'">
        </td>
        <td style="width: 40%;" class="title">
            PAYMENT VOUCHER<br>
            <span style="font-size: 14px; font-weight: normal; color: #555;">
                <?php if ($step == 1): ?>
                    (ใบรวมบิลที่จะต้องจ่าย - COMBINED BILLS SUMMARY)
                <?php else: ?>
                    (รายละเอียดการจ่ายเงินและการลงบัญชี - PAYMENT & ACCOUNTING DETAILS)
                <?php endif; ?>
            </span>
        </td>
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

<?php if ($step == 1): ?>
    <!-- STEP 1: รวมบิลที่จะต้องจ่าย (Summary of Combined Bills) -->
    <div class="section-title">บิลและเอกสารสั่งซื้อที่นำมารวมจ่าย (Combined Documents)</div>
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 20%;">ประเภทเอกสาร</th>
                <th style="width: 25%;">เลขที่เอกสาร</th>
                <th style="width: 15%;">วันที่เอกสาร</th>
                <th style="width: 20%;">ชื่อผู้ขาย/ผู้จำหน่าย</th>
                <th style="width: 15%; text-align: right;">ยอดรวมเงินจ่าย</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $refs = $model->paymentVoucherRefs;
            $total_ref_amount = 0;
            if (!empty($refs)):
                foreach ($refs as $index => $ref):
                    $total_ref_amount += $ref->amount;
                    
                    $type_label = '';
                    $doc_no = $ref->ref_no;
                    $doc_date = '-';
                    $vendor_name = $model->recipient_name;

                    if ($ref->ref_type == \backend\models\PaymentVoucherRef::REF_TYPE_PR) {
                        $type_label = 'ใบขอซื้อ (PR)';
                        $pr = \backend\models\PurchReq::findOne($ref->ref_id);
                        if ($pr) {
                            $doc_date = $pr->purch_req_date ? date('d/m/Y', strtotime($pr->purch_req_date)) : '-';
                            $vendor_name = $pr->vendor_name ?: $vendor_name;
                        }
                    } elseif ($ref->ref_type == \backend\models\PaymentVoucherRef::REF_TYPE_PO) {
                        $type_label = 'ใบสั่งซื้อ (PO)';
                        $po = \backend\models\Purch::findOne($ref->ref_id);
                        if ($po) {
                            $doc_date = $po->purch_date ? date('d/m/Y', strtotime($po->purch_date)) : '-';
                            $vendor_name = $po->vendor_name ?: $vendor_name;
                        }
                    } elseif ($ref->ref_type == \backend\models\PaymentVoucherRef::REF_TYPE_NONE_PR) {
                        $type_label = 'ใบสั่งซื้อ (None PR)';
                        $none_pr = \backend\models\PurchaseMaster::findOne($ref->ref_id);
                        if ($none_pr) {
                            $doc_date = $none_pr->docdat ? date('d/m/Y', strtotime($none_pr->docdat)) : '-';
                            $vendor_name = $none_pr->supnam ?: $vendor_name;
                        }
                    }
            ?>
                <tr>
                    <td style="text-align: center;"><?= $index + 1 ?></td>
                    <td style="text-align: center;"><?= $type_label ?></td>
                    <td style="text-align: center; font-weight: bold;"><?= Html::encode($doc_no) ?></td>
                    <td style="text-align: center;"><?= $doc_date ?></td>
                    <td><?= Html::encode($vendor_name) ?></td>
                    <td style="text-align: right; font-weight: bold;"><?= number_format($ref->amount, 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="font-weight: bold; background-color: #eee;">
                    <td colspan="5" style="text-align: right;">รวมทั้งสิ้น</td>
                    <td style="text-align: right;"><?= number_format($total_ref_amount, 2) ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #888;">ไม่มีบิลที่นำมารวมจ่าย</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

<?php else: ?>
    <!-- STEP 2: รายละเอียดจ่ายเงินและการลงบัญชี (Payment details and accounting entries) -->
    <div class="section-title">รายละเอียดภาษีและมูลค่าสินค้าแยกตามเอกสาร</div>
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">วันที่เอกสาร</th>
                <th style="width: 25%;">เลขที่เอกสาร</th>
                <th style="width: 15%; text-align: right;">มูลค่าก่อนภาษี</th>
                <th style="width: 12%; text-align: right;">VAT</th>
                <th style="width: 13%; text-align: right;">หัก ณ ที่จ่าย (WHT)</th>
                <th style="width: 15%; text-align: right;">รวมเงินจ่าย</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $refs = $model->paymentVoucherRefs;
            $sum_before_vat = 0;
            $sum_vat = 0;
            $sum_wth = 0;
            $sum_total = 0;
            
            if (!empty($refs)):
                foreach ($refs as $index => $ref):
                    $doc_date = '-';
                    $doc_no = $ref->ref_no;
                    $before_vat = 0;
                    $vat = 0;
                    $wth = 0;
                    $total = $ref->amount;

                    if ($ref->ref_type == \backend\models\PaymentVoucherRef::REF_TYPE_PO) {
                        $po = \backend\models\Purch::findOne($ref->ref_id);
                        if ($po) {
                            $doc_date = $po->purch_date ? date('d/m/Y', strtotime($po->purch_date)) : '-';
                            $ratio = ($po->net_amount > 0) ? ($ref->amount / $po->net_amount) : 1;
                            $before_vat = ($po->total_amount - $po->discount_amount) * $ratio;
                            $vat = $po->vat_amount * $ratio;
                            $wth = $po->whd_tax_amount * $ratio;
                        }
                    } elseif ($ref->ref_type == \backend\models\PaymentVoucherRef::REF_TYPE_NONE_PR) {
                        $none_pr = \backend\models\PurchaseMaster::findOne($ref->ref_id);
                        if ($none_pr) {
                            $doc_date = $none_pr->docdat ? date('d/m/Y', strtotime($none_pr->docdat)) : '-';
                            $ratio = ($none_pr->total_amount > 0) ? ($ref->amount / $none_pr->total_amount) : 1;
                            $before_vat = $none_pr->vatpr0 * $ratio;
                            $vat = $none_pr->vat_amount * $ratio;
                            $wth = $none_pr->tax_amount * $ratio;
                        }
                    } elseif ($ref->ref_type == \backend\models\PaymentVoucherRef::REF_TYPE_PR) {
                        $pr = \backend\models\PurchReq::findOne($ref->ref_id);
                        if ($pr) {
                            $doc_date = $pr->purch_req_date ? date('d/m/Y', strtotime($pr->purch_req_date)) : '-';
                            $ratio = ($pr->net_amount > 0) ? ($ref->amount / $pr->net_amount) : 1;
                            $before_vat = ($pr->total_amount - $pr->discount_amount) * $ratio;
                            $vat = $pr->vat_amount * $ratio;
                            $wth = 0;
                        }
                    }

                    $total = $before_vat + $vat - $wth;
                    
                    $sum_before_vat += $before_vat;
                    $sum_vat += $vat;
                    $sum_wth += $wth;
                    $sum_total += $total;
            ?>
                <tr>
                    <td style="text-align: center;"><?= $index + 1 ?></td>
                    <td style="text-align: center;"><?= $doc_date ?></td>
                    <td style="text-align: center; font-weight: bold;"><?= Html::encode($doc_no) ?></td>
                    <td style="text-align: right;"><?= number_format($before_vat, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($vat, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($wth, 2) ?></td>
                    <td style="text-align: right; font-weight: bold;"><?= number_format($total, 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="font-weight: bold; background-color: #eee;">
                    <td colspan="3" style="text-align: right;">รวมทั้งสิ้น</td>
                    <td style="text-align: right;"><?= number_format($sum_before_vat, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($sum_vat, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($sum_wth, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($sum_total, 2) ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #888;">ไม่มีข้อมูลภาษีแยกตามเอกสาร</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">การลงบันทึกบัญชี (Accounting Posting)</div>
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 10%;">Code Acc.</th>
                <th style="width: 12%;">Code Bill</th>
                <th style="width: 25%;">Description</th>
                <th style="width: 25%;"></th>
                <th style="width: 14%;">Debit</th>
                <th style="width: 14%;">Credit</th>
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
                    // แยก description ออกเป็น 2 ช่อง
                    $descriptions = explode('|||', $line->description);
                    $desc1 = $descriptions[0] ?? '';
                    $desc2 = $descriptions[1] ?? '';
                } else {
                    $desc1 = '';
                    $desc2 = '';
                }
            ?>
            <tr>
                <td style="text-align: center;"><?= $line ? Html::encode($line->account_code) : '' ?></td>
                <td style="text-align: center;"><?= $line ? Html::encode($line->bill_code) : '' ?></td>
                <td><?= Html::encode($desc1) ?></td>
                <td><?= Html::encode($desc2) ?></td>
                <td style="text-align: right;"><?= ($line && $line->debit > 0) ? number_format($line->debit, 2) : '' ?></td>
                <td style="text-align: right;"><?= ($line && $line->credit > 0) ? number_format($line->credit, 2) : '' ?></td>
            </tr>
            <?php endfor; ?>
            <tr style="font-weight: bold; background-color: #eee;">
                <td colspan="4" style="text-align: right;">Total</td>
                <td style="text-align: right;"><?= number_format($total_debit, 2) ?></td>
                <td style="text-align: right;"><?= number_format($total_credit, 2) ?></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>

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
            <div class="no-print" style="margin-top: 10px;">
                <a href="<?= \yii\helpers\Url::to(['paymentvoucher/view', 'id' => $model->id]) ?>" target="_blank" style="display: inline-block; padding: 4px 8px; font-size: 11px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; border: 1px solid #0056b3;">Electronic sign</a>
            </div>
        </td>
        <td>
            <div class="sig-container"></div>
            Checker by
            <div class="no-print" style="margin-top: 10px;">
                <a href="<?= \yii\helpers\Url::to(['paymentvoucher/view', 'id' => $model->id]) ?>" target="_blank" style="display: inline-block; padding: 4px 8px; font-size: 11px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; border: 1px solid #0056b3;">Electronic sign</a>
            </div>
        </td>
        <td>
            <div class="sig-container"></div>
            Authorized by
            <div class="no-print" style="margin-top: 10px;">
                <a href="<?= \yii\helpers\Url::to(['paymentvoucher/view', 'id' => $model->id]) ?>" target="_blank" style="display: inline-block; padding: 4px 8px; font-size: 11px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; border: 1px solid #0056b3;">Electronic sign</a>
            </div>
        </td>
    </tr>
</table>

</body>
</html>
