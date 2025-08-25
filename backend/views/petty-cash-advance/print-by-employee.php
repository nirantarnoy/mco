use yii\helpers\Html;

$this->title = 'รายการเบิกเงินทดแทนของ ' . $employee->fname . ' ' . $employee->lname;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>

    <style>
        @page {
            size: A4 portrait;
            margin: 2cm 1.5cm;
        }

        body {
            font-family: 'Sarabun', 'TH Sarabun New', sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 16px;
            font-weight: bold;
        }

        .header h3 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: bold;
        }

        .employee-info {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            font-size: 13px;
        }

        .employee-info table {
            width: 100%;
        }

        .employee-info td {
            padding: 3px 0;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }

        .main-table th {
            background-color: #f5f5f5;
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .summary-box {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
            background-color: #f9f9f9;
        }

        .no-print {
            display: block;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                font-size: 12px;
            }

            .main-table {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
<!-- Print Controls -->
<div class="no-print" style="text-align: center; margin-bottom: 20px;">
    <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-right: 10px;">
        พิมพ์เอกสาร
    </button>
    <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
        ปิดหน้าต่าง
    </button>
</div>

<!-- Document Header -->
<div class="header">
    <h2>บริษัท เอ็ม.ซี.โอ. จำกัด</h2>
    <h3>รายการเบิกเงินทดแทนสดย่อย</h3>
    <div>ประจำวันที่ <?= date('d/m/', strtotime($from_date)) . (date('Y', strtotime($from_date)) + 543) ?>
        ถึง <?= date('d/m/', strtotime($to_date)) . (date('Y', strtotime($to_date)) + 543) ?></div>
</div>

<!-- Employee Information -->
<div class="employee-info">
    <strong>ข้อมูลพนักงาน:</strong>
    <table>
        <tr>
            <td style="width: 120px;">ชื่อ-นามสกุล:</td>
            <td><?= Html::encode($employee->fname . ' ' . $employee->lname) ?></td>
            <td style="width: 120px;">รหัสพนักงาน:</td>
            <td><?= Html::encode($employee->employee_code ?? '-') ?></td>
        </tr>
        <tr>
            <td>แผนก:</td>
            <td><?= Html::encode($employee->department->name ?? '-') ?></td>
            <td>ตำแหน่ง:</td>
            <td><?= Html::encode($employee->position ?? '-') ?></td>
        </tr>
    </table>
</div>

<!-- Main Table -->
<table class="main-table">
    <thead>
    <tr>
        <th style="width: 8%;">ลำดับ</th>
        <th style="width: 15%;">วันที่เบิก</th>
        <th style="width: 15%;">เลขที่ใบเบิก</th>
        <th style="width: 40%;">วัตถุประสงค์</th>
        <th style="width: 12%;">จำนวนเงิน</th>
        <th style="width: 10%;">สถานะ</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($advances)): ?>
        <?php foreach ($advances as $index => $advance): ?>
            <tr>
                <td class="text-center"><?= $index + 1 ?></td>
                <td class="text-center">
                    <?= date('d/m/', strtotime($advance->request_date)) . (date('Y', strtotime($advance->request_date)) + 543) ?>
                </td>
                <td class="text-center"><?= Html::encode($advance->advance_no) ?></td>
                <td><?= Html::encode($advance->purpose) ?></td>
                <td class="text-right"><?= number_format($advance->amount, 2) ?></td>
                <td class="text-center">
                    <?php
                    $statusText = [
                        'pending' => 'รออนุมัติ',
                        'approved' => 'อนุมัติ',
                        'rejected' => 'ปฏิเสธ',
                        'paid' => 'จ่ายแล้ว',
                    ];
                    echo $statusText[$advance->status] ?? $advance->status;
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>

        <!-- Fill remaining rows -->
        <?php for ($i = count($advances); $i < 10; $i++): ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        <?php endfor; ?>

    <?php else: ?>
        <?php for ($i = 0; $i < 10; $i++): ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        <?php endfor; ?>
    <?php endif; ?>

    <!-- Total Row -->
    <tr class="total-row">
        <td colspan="4" class="text-center">รวมทั้งหมด</td>
        <td class="text-right"><?= number_format($totalAmount, 2) ?></td>
        <td>-</td>
    </tr>
    </tbody>
</table>

<!-- Summary -->
<div class="summary-box">
    <strong>สรุป:</strong><br>
    จำนวนครั้งที่เบิก: <?= count($advances) ?> ครั้ง<br>
    จำนวนเงินรวม: <?= number_format($totalAmount, 2) ?> บาท<br>
    ค่าเฉลี่ยต่อครั้ง: <?= count($advances) > 0 ? number_format($totalAmount / count($advances), 2) : '0.00' ?> บาท
</div>

<!-- Footer -->
<div style="text-align: center; margin-top: 30px; font-size: 12px;">
    พิมพ์เมื่อ: <?= date('d/m/') . (date('Y') + 543) . ' ' . date('H:i') ?> น.<br>
    F-WP-FMA-004-002 Rev.N
</div>

</body>
</html>