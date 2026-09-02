<?php
use yii\helpers\Html;

$formatter = \Yii::$app->formatter;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Pre-Advance : <?= Html::encode($model->pre_advance_no) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 12pt; 
            margin: 0; 
            padding: 20px; 
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .title { font-size: 16pt; font-weight: bold; margin-bottom: 20px; }
        
        .header-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .header-table td { padding: 3px; vertical-align: top; }
        
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .content-table th, .content-table td { border: 1px solid #000; padding: 6px; }
        .content-table th { background-color: #f2f2f2; text-align: center; font-size: 11pt; font-weight: bold; vertical-align: middle; }
        .content-table td { font-size: 11pt; }
        
        .sign-table { width: 100%; margin-top: 40px; }
        .sign-table td { text-align: center; width: 33%; vertical-align: bottom; height: 80px; font-size: 11pt; }
        .sign-line { border-bottom: 1px solid #000; display: inline-block; width: 70%; margin-bottom: 10px; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print();" style="padding: 10px 20px; font-size: 14pt; cursor: pointer;">Print</button>
    </div>

    <div class="text-center title">
        ใบขออนุมัติค่าใช้จ่าย/ตั้งเบิก (Pre-Advance)
    </div>

    <table class="header-table">
        <tr>
            <td width="15%"><b>Pre-Advance No:</b></td>
            <td width="55%"><?= Html::encode($model->pre_advance_no) ?></td>
            <td width="15%" class="text-right"><b>วันที่ (Date):</b></td>
            <td width="15%"><?= Html::encode($formatter->asDate($model->trans_date, 'php:d/m/Y')) ?></td>
        </tr>
        <tr>
            <td><b>ชื่อผู้รับเงิน:</b></td>
            <td><?= Html::encode($model->recipient_name) ?></td>
            <td class="text-right"><b>Vendor:</b></td>
            <td><?= $model->vendor ? Html::encode($model->vendor->name) : '-' ?></td>
        </tr>
        <tr>
            <td><b>หมายเหตุ:</b></td>
            <td colspan="3"><?= Html::encode($model->remark) ?></td>
        </tr>
    </table>

    <table class="content-table">
        <thead>
            <tr>
                <th width="4%">ลำดับ<br>(No.)</th>
                <th width="8%">วันที่<br>(Date)</th>
                <th width="12%">เลขที่อ้างอิงเอกสาร<br>Ref. No.(PO / NON PR)</th>
                <th width="15%">ชื่อบริษัท ห้าง ร้าน<br>บุคคล ตามบิลเอกสาร<br>(Receipt Name)</th>
                <th width="21%">รายละเอียดการขออนุมัติ<br>ค่าใช้จ่าย/ตั้งเบิก<br>(Description)</th>
                <th width="9%">ยอดก่อน<br>ภาษีมูลค่าเพิ่ม<br>(Value Before Vat)</th>
                <th width="7%">ภาษีมูลค่าเพิ่ม<br>(Vat)</th>
                <th width="7%">ภาษีหัก ณ ที่จ่าย<br>(Tax Withholding)</th>
                <th width="9%">ยอดสุทธิ<br>(Total Amount)</th>
                <th width="8%">หมายเหตุ<br>(Remark)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Collect refs mapping
            $refMap = [];
            $refList = [];
            foreach ($model->preAdvanceRefs as $ref) {
                if ($ref->ref_type == \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR) {
                    $m = \backend\models\PurchaseMaster::findOne($ref->ref_id);
                    if ($m) {
                        $vName = !empty($m->supnam) ? $m->supnam : ($m->vendor ? $m->vendor->name : '');
                        $valBeforeVat = (float)($m->vatpr0 > 0 ? $m->vatpr0 : ($m->vat_amount > 0 ? ($m->total_amount - $m->vat_amount) : $m->total_amount));
                        $info = [
                            'type' => 'NONE_PR',
                            'docnum' => $m->docnum,
                            'vendor_name' => $vName,
                            'qt_no' => !empty($m->job_no) ? $m->job_no : $m->refnum,
                            'total_amount' => (float)$m->total_amount,
                            'value_before_vat' => $valBeforeVat,
                            'vat_amount' => (float)($m->vat_amount ?? 0),
                            'tax_amount' => (float)($m->tax_amount ?? 0),
                        ];
                        $refMap[$m->docnum] = $info;
                        $refList[] = $info;
                    }
                } elseif ($ref->ref_type == \backend\models\PreAdvanceRef::REF_TYPE_PO) {
                    $m = \backend\models\Purch::findOne($ref->ref_id);
                    if ($m) {
                        $vName = !empty($m->vendor_name) ? $m->vendor_name : ($m->vendor ? $m->vendor->name : '');
                        $whdTax = (float)($m->whd_tax_amount ?? 0);
                        $vatAmt = (float)($m->vat_amount ?? 0);
                        $netAmt = (float)$m->net_amount;
                        $totAmt = (float)($m->total_amount ?? 0);
                        $discAmt = (float)($m->discount_total_amount ?? 0);
                        $valBeforeVat = ($totAmt > 0) ? ($totAmt - $discAmt) : ($netAmt - $vatAmt + $whdTax);

                        $info = [
                            'type' => 'PO',
                            'docnum' => $m->purch_no,
                            'vendor_name' => $vName,
                            'qt_no' => $m->ref_no,
                            'total_amount' => $netAmt,
                            'value_before_vat' => $valBeforeVat,
                            'vat_amount' => $vatAmt,
                            'tax_amount' => $whdTax,
                        ];
                        $refMap[$m->purch_no] = $info;
                        $refList[] = $info;
                    }
                }
            }

            $i = 1;
            $linesList = $model->preAdvanceLines;
            $sumBeforeVat = 0;
            $sumVat = 0;
            $sumTax = 0;
            $sumTotal = 0;

            foreach ($linesList as $index => $line):
                $refNo = '';
                $qtNo = '';
                $rawRemark = trim($line->remark ?? '');
                $receiptName = (empty($rawRemark) || strtolower($rawRemark) === 'null' || $rawRemark === '-') ? '' : $rawRemark;
                $descText = $line->description;
                $totalAmount = (float)$line->amount;
                $vatAmount = 0;
                $taxAmount = 0;
                $valueBeforeVat = 0;

                // Extract Ref No (e.g. NPR202608170001 or PO-00293-QT26-00084.108)
                if (preg_match('/เลขที่:\s*([A-Za-z0-9-.\/_]+)/u', $descText, $matches)) {
                    $refNo = trim($matches[1]);
                }

                // Extract QT No from description
                if (preg_match('/อ้างอิง\s*QT:\s*([A-Za-z0-9-.\/_]+)/u', $descText, $qtMatches)) {
                    $qtNo = trim($qtMatches[1]);
                }

                // Find matching refInfo
                $refInfo = null;
                if (!empty($refNo) && isset($refMap[$refNo])) {
                    $refInfo = $refMap[$refNo];
                } elseif (isset($refList[$index])) {
                    $refInfo = $refList[$index];
                } else {
                    foreach ($refMap as $k => $info) {
                        if (!empty($refNo) && (strpos($k, $refNo) !== false || strpos($refNo, $k) !== false)) {
                            $refInfo = $info;
                            break;
                        }
                    }
                }

                if ($refInfo) {
                    if (empty($refNo)) {
                        $refNo = $refInfo['docnum'];
                    }
                    if (empty($receiptName)) {
                        $receiptName = $refInfo['vendor_name'];
                    }
                    if (empty($qtNo)) {
                        $qtNo = $refInfo['qt_no'];
                    }
                    $vatAmount = $refInfo['vat_amount'];
                    $taxAmount = $refInfo['tax_amount'];
                    $valueBeforeVat = $refInfo['value_before_vat'];
                }

                // Fallback for vendor name if still empty
                if (empty($receiptName) || strtolower($receiptName) === 'null') {
                    $receiptName = $model->vendor ? $model->vendor->name : '';
                }

                if ($valueBeforeVat == 0) {
                    $valueBeforeVat = $totalAmount;
                }

                // Build clean Description
                $displayDesc = $descText;

                $sumBeforeVat += $valueBeforeVat;
                $sumVat += $vatAmount;
                $sumTax += $taxAmount;
                $sumTotal += $totalAmount;
            ?>
                <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td class="text-center"><?= $line->line_date ? Html::encode($formatter->asDate($line->line_date, 'php:d/m/Y')) : '' ?></td>
                    <td class="text-center"><?= Html::encode($refNo ?: '-') ?></td>
                    <td><?= Html::encode($receiptName ?: '-') ?></td>
                    <td><?= Html::encode($displayDesc) ?></td>
                    <td class="text-right"><?= $valueBeforeVat > 0 ? number_format($valueBeforeVat, 2) : '-' ?></td>
                    <td class="text-right"><?= $vatAmount > 0 ? number_format($vatAmount, 2) : '-' ?></td>
                    <td class="text-right"><?= $taxAmount > 0 ? number_format($taxAmount, 2) : '-' ?></td>
                    <td class="text-right"><?= number_format($totalAmount, 2) ?></td>
                    <td contenteditable="true" style="outline: none; cursor: text;" title="พิมพ์หมายเหตุเพิ่มเติมตรงนี้ได้"></td>
                </tr>
            <?php endforeach; ?>
            <?php 
            // Add empty rows to fill up space if needed
            for ($j = $i; $j <= max(12, $i); $j++): 
            ?>
                <tr>
                    <td class="text-center">&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td contenteditable="true" style="outline: none; cursor: text;"></td>
                </tr>
            <?php endfor; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-center" style="font-weight: bold; font-size: 12pt;">Summary</td>
                <td class="text-right"><b><?= $sumBeforeVat > 0 ? number_format($sumBeforeVat, 2) : '-' ?></b></td>
                <td class="text-right"><b><?= $sumVat > 0 ? number_format($sumVat, 2) : '-' ?></b></td>
                <td class="text-right"><b><?= $sumTax > 0 ? number_format($sumTax, 2) : '-' ?></b></td>
                <td class="text-right"><b><?= number_format($sumTotal > 0 ? $sumTotal : $model->amount, 2) ?></b></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                <span class="sign-line"></span><br>
                ผู้ขอเบิก (Requested By)<br>
                วันที่ _______/_______/_______
            </td>
            <td>
                <span class="sign-line"></span><br>
                ผู้ตรวจสอบ (Checked By)<br>
                วันที่ _______/_______/_______
            </td>
            <td>
                <span class="sign-line"></span><br>
                ผู้อนุมัติ (Approved By)<br>
                วันที่ _______/_______/_______
            </td>
        </tr>
    </table>
</body>
</html>
