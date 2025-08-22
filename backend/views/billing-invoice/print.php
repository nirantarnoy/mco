<?php
// backend/views/billing-invoice/print.php
use yii\helpers\Html;

$this->title = 'ใบวางบิล - ' . $model->billing_number;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }

        body {
            font-family: 'Sarabun', 'THSarabunNew', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 15px;
        }

        .mco-logo {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            font-family: 'Arial Black', Arial, sans-serif;
            letter-spacing: 2px;
        }

        .company-details {
            text-align: right;
            font-size: 11px;
            line-height: 1.3;
        }

        .company-details h1 {
            font-size: 16px;
            margin: 0 0 8px 0;
            font-weight: bold;
            color: #333;
        }

        .invoice-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 25px 0;
            color: #333;
        }

        .billing-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .customer-details {
            width: 65%;
        }

        .billing-numbers {
            width: 30%;
        }

        .customer-details table {
            width: 100%;
            font-size: 12px;
        }

        .customer-details td {
            padding: 3px 0;
            vertical-align: top;
        }

        .customer-details td:first-child {
            width: 80px;
            font-weight: bold;
        }

        .number-box {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 8px;
            text-align: center;
            border-radius: 3px;
        }

        .number-box .label {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
        }

        .number-box .value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .section-header {
            background-color: #e8e8e8;
            border: 1px solid #999;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 25px 0 0 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #e8e8e8;
            border: 1px solid #333;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            line-height: 1.2;
        }

        .items-table td {
            border: 1px solid #333;
            padding: 8px 4px;
            text-align: center;
            vertical-align: middle;
        }

        .items-table .text-left {
            text-align: left;
        }

        .items-table .text-right {
            text-align: right;
        }

        .items-table .big-number {
            font-size: 28px;
            font-weight: bold;
            color: #666;
            padding: 20px 4px;
        }

        .total-section {
            margin: 25px 0;
            text-align: right;
        }

        .total-box {
            display: inline-block;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            min-width: 200px;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }

        .signature-block {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 60px;
            margin-bottom: 8px;
            position: relative;
        }

        .signature-name {
            position: absolute;
            bottom: 10px;
            right: 10px;
            font-style: italic;
            font-size: 14px;
            color: #666;
        }

        .signature-label {
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .signature-date {
            margin-top: 15px;
            font-size: 11px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 20px; text-align: center; padding: 10px; background: #f0f0f0;">
    <button onclick="window.print()" style="padding: 10px 25px; font-size: 14px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">🖨️ พิมพ์</button>
    <button onclick="window.close()" style="padding: 10px 25px; font-size: 14px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">❌ ปิด</button>
</div>

<div class="header-section">
    <div class="mco-logo">
        <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png',['style' => 'max-width: 120px;']) ?>
    </div>
    <div class="company-details">
        <h1>บริษัท เอ็ม.ซี.โอ. จำกัด</h1>
        <p>8/18 ถนนเกาะกลอย ตำบลเชิงเนิน อำเภอเมือง จังหวัดระยอง 21000</p>
        <p><strong>Tel :</strong> (038) 875258-9, &nbsp; <strong>Fax :</strong> (038) 619559</p>
    </div>
</div>

<div class="invoice-title">ใบวางบิล</div>

<div class="billing-info">
    <div class="customer-details">
        <table>
            <tr>
                <td><strong>ชื่อลูกค้า</strong></td>
                <td><strong><?= Html::encode($model->customer->name ?? 'บริษัท ส.สิริเสถ จำกัด (สำนักงานใหญ่)') ?></strong></td>
            </tr>
            <tr>
                <td><strong>ที่อยู่</strong></td>
                <td>
                    <?= Html::encode($model->customer->address ?? '140 ถ.วิภาวดีรังสิต แขวงดินแดง เขตดินแดง') ?><br>
                    <?= Html::encode($model->customer->tax_id ?? 'กรุงเทพมหานคร 10400 TAXID 0105520017611.') ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="billing-numbers">
        <div class="number-box">
            <div class="label">เลขที่ใบวางบิล</div>
            <div class="value"><?= Html::encode($model->billing_number) ?></div>
        </div>
        <div class="number-box">
            <div class="label">วันที่ใบวางบิล</div>
            <div class="value"><?= Yii::$app->formatter->asDate($model->billing_date, 'php:j/n/y') ?></div>
        </div>
    </div>
</div>
<table class="items-table">
    <thead>
    <tr>
        <th style="width: 8%;">ลำดับที่</th>
        <th style="width: 18%;">หมายเลขใบสั่งซื้อ</th>
        <th style="width: 18%;">เลขที่เอกสารตั้งหนี้</th>
        <th style="width: 12%;">ลงวันที่</th>
        <th style="width: 12%;">วันที่ครบกำหนดชำระ</th>
        <th style="width: 15%;">จำนวนเงิน</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $totalAmount = 0;
    $itemCount = 0;

    // แสดงรายการจริงจากฐานข้อมูล
    foreach ($model->billingInvoiceItems as $index => $item):
        $itemCount++;
        $totalAmount += $item->amount;
        $invoice = $item->invoice; // ดึงข้อมูล invoice ที่เกี่ยวข้อง
        ?>
        <tr>
            <td><?= $itemCount ?></td>
            <td class="text-left"><?= Html::encode($invoice->invoice_number ?? '-') ?></td>
            <td><?= Html::encode($invoice->invoice_number ?? '-') ?></td>
            <td><?= Yii::$app->formatter->asDate($invoice->invoice_date ?? $model->billing_date, 'php:j/n/y') ?></td>
            <td><?= Yii::$app->formatter->asDate($invoice->payment_due_date ?? $model->payment_due_date ?? date('Y-m-d', strtotime($model->billing_date . ' +30 days')), 'php:j/n/y') ?></td>
            <td class="text-right"><?= number_format($item->amount, 2) ?></td>
        </tr>
    <?php endforeach; ?>

    <?php
    // ถ้าไม่มีข้อมูลให้แสดงข้อความว่าง
    if ($itemCount == 0):
        $totalAmount = $model->total_amount;
        ?>
        <tr>
            <td colspan="7" style="text-align: center; padding: 20px; color: #999; font-style: italic;">
                ไม่มีรายการใบกำกับภาษี
            </td>
        </tr>
    <?php endif; ?>

    <?php
    // เติมแถวว่าง (ไม่มีตัวเลขใหญ่)
    $emptyRows = 15 - $itemCount;
    if ($itemCount == 0) $emptyRows = 14; // ถ้าไม่มีข้อมูลให้เหลือ 14 แถว

    for ($i = 0; $i < $emptyRows; $i++):
        ?>
        <tr style="height: 35px;">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>

<div class="total-section">

      <table style="width: 100%">
          <tr>
              <td style="text-align: left;font-weight: bold;font-size: 16px">
                  <u>รวมเงินทั้งสิ้น</u> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?=\backend\models\PurchReq::numtothai($model->total_amount)?>
              </td>
              <td style="text-align: right">
                  <div class="total-box">
                  <?= number_format($totalAmount > 0 ? $totalAmount : $model->total_amount, 2) ?>
                  </div>
              </td>
          </tr>
      </table>


</div>

<div class="signature-section">

     <table style="width: 100%">
         <tr>
             <td style="width: 50%">
                 <table style="width: 100%">
                     <tr>
                         <td style="width: 20%;font-size: 14px;padding:10px;text-align: left"><u>ผู้รับวางบิล</u></td>
                         <td style="padding:10px;text-align: left;width: 80%">_____________________________</td>
                     </tr>
                     <tr>
                         <td style="width: 80%;font-size: 14px;text-align: left"><u>วัดนัดรับเช็ค</u></td>
                         <td style="padding:10px;text-align: left">_____________________________</td>
                     </tr>
                 </table>
             </td>
             <td style="width: 50%">
                 <table style="width: 100%">
                     <tr>
                         <td style="width: 20%;font-size: 14px"><u>ผู้วางบิล</u></td>
                         <td style="padding:10px;text-align: left">_____________________________</td>
                     </tr>
                 </table>
             </td>
         </tr>
     </table>

</div>

</body>
</html>