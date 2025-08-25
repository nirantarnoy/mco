<?php
use yii\helpers\Html;

$this->title = 'รายงานเงินสดย่อย M.C.O.CO.,LTD';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        @media print {
            body {
                font-size: 12px;
                font-family: 'Sarabun', sans-serif;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }

            .mco-table {
                font-size: 10px;
            }

            .mco-table th, .mco-table td {
                padding: 2px !important;
                border: 1px solid #000 !important;
            }

            @page {
                margin: 1cm;
                size: A4 landscape;
            }
        }

        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
        }

        .mco-table {
            width: 100%;
            border-collapse: collapse;
        }

        .mco-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 8px 4px;
            border: 1px solid #000;
        }

        .mco-table td {
            padding: 6px 4px;
            border: 1px solid #000;
            vertical-align: middle;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-header h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .report-header h5 {
            color: #34495e;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .summary-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .summary-box {
            width: 48%;
            border: 1px solid #000;
            padding: 10px;
        }

        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            width: 200px;
            height: 40px;
            margin: 10px auto;
        }
    </style>
</head>
<body>
<!-- Print Button -->
<div class="no-print text-center mb-3">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print"></i> พิมพ์รายงาน
    </button>
    <button onclick="window.close()" class="btn btn-secondary">
        <i class="fas fa-times"></i> ปิด
    </button>
</div>

<!-- Report Content -->
<div class="container-fluid">
    <div class="report-header">
        <h3><strong>รายงานเงินสดย่อย M.C.O.CO.,LTD</strong></h3>
        <h5>ประจำวันที่ <?= date('d/m/Y', strtotime($from_date)) ?> ถึง <?= date('d/m/Y', strtotime($to_date)) ?></h5>
        <p style="margin: 5px 0; color: #666;">F-WP-FMA-004-003 Rev.N</p>
    </div>

    <!-- Opening Balance -->
    <div style="margin-bottom: 15px;">
        <strong>ยอดยกมา: <?= number_format($reportData['opening_balance'], 2) ?> บาท</strong>
    </div>

    <!-- Main Report Table -->
    <table class="mco-table">
        <thead>
        <tr>
            <th rowspan="2" style="width: 8%;">วันที่</th>
            <th rowspan="2" style="width: 25%;">รายการ</th>
            <th rowspan="2" style="width: 10%;">รายรับ</th>
            <th colspan="6">รายจ่าย</th>
            <th rowspan="2" style="width: 10%;">คงเหลือ</th>
            <th rowspan="2" style="width: 12%;">เลขที่เอกสาร</th>
        </tr>
        <tr>
            <th style="width: 8%;">ค่าใช้จ่าย</th>
            <th style="width: 5%;">VAT</th>
            <th style="width: 8%;">VAT จำนวน</th>
            <th style="width: 6%;">W/H</th>
            <th style="width: 6%;">อื่น ๆ</th>
            <th style="width: 8%;">ทั้งหมด</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($reportData['transactions'] as $transaction): ?>
            <tr>
                <td class="text-center"><?= date('d/m/Y', strtotime($transaction['date'])) ?></td>
                <td><?= Html::encode($transaction['description']) ?></td>
                <td class="text-right">
                    <?= $transaction['income'] > 0 ? number_format($transaction['income'], 2) : '-' ?>
                </td>
                <td class="text-right">
                    <?= $transaction['expense_detail']['amount'] > 0 ? number_format($transaction['expense_detail']['amount'], 2) : '-' ?>
                </td>
                <td class="text-right">
                    <?= $transaction['expense_detail']['vat'] > 0 ? number_format($transaction['expense_detail']['vat'], 2) : '-' ?>
                </td>
                <td class="text-right">
                    <?= $transaction['expense_detail']['vat_amount'] > 0 ? number_format($transaction['expense_detail']['vat_amount'], 2) : '-' ?>
                </td>
                <td class="text-right">
                    <?= $transaction['expense_detail']['wht'] > 0 ? number_format($transaction['expense_detail']['wht'], 2) : '-' ?>
                </td>
                <td class="text-right">
                    <?= $transaction['expense_detail']['other'] > 0 ? number_format($transaction['expense_detail']['other'], 2) : '-' ?>
                </td>
                <td class="text-right">
                    <?= $transaction['expense_detail']['total'] > 0 ? number_format($transaction['expense_detail']['total'], 2) : '-' ?>
                </td>
                <td class="text-right font-weight-bold">
                    <?= number_format($transaction['balance'], 2) ?>
                </td>
                <td class="text-center"><?= Html::encode($transaction['doc_no']) ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($reportData['transactions'])): ?>
            <tr>
                <td colspan="11" class="text-center" style="padding: 20px; color: #666;">
                    ไม่มีรายการในช่วงเวลาที่เลือก
                </td>
            </tr>
        <?php endif; ?>
        </tbody>

        <!-- Summary Row -->
        <tfoot>
        <tr class="font-weight-bold" style="background-color: #f8f9fa;">
            <td colspan="2" class="text-center">รวม</td>
            <td class="text-right"><?= number_format($reportData['total_income'], 2) ?></td>
            <td class="text-right"><?= number_format($reportData['expense_summary']['amount'], 2) ?></td>
            <td class="text-right"><?= number_format($reportData['expense_summary']['vat'], 2) ?></td>
            <td class="text-right"><?= number_format($reportData['expense_summary']['vat_amount'], 2) ?></td>
            <td class="text-right"><?= number_format($reportData['expense_summary']['wht'], 2) ?></td>
            <td class="text-right"><?= number_format($reportData['expense_summary']['other'], 2) ?></td>
            <td class="text-right"><?= number_format($reportData['expense_summary']['total'], 2) ?></td>
            <td class="text-right"><?= number_format($reportData['closing_balance'], 2) ?></td>
            <td></td>
        </tr>
        </tfoot>
    </table>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-box">
            <h6><strong>สรุปรายการ</strong></h6>
            <table style="width: 100%; border: none;">
                <tr>
                    <td>ยอดยกมา:</td>
                    <td class="text-right"><?= number_format($reportData['opening_balance'], 2) ?> บาท</td>
                </tr>
                <tr>
                    <td>รายรับรวม:</td>
                    <td class="text-right"><?= number_format($reportData['total_income'], 2) ?> บาท</td>
                </tr>
                <tr>
                    <td>รายจ่ายรวม:</td>
                    <td class="text-right"><?= number_format($reportData['total_expense'], 2) ?> บาท</td>
                </tr>
                <tr style="border-top: 1px solid #000;">
                    <td class="font-weight-bold">ยอดคงเหลือ:</td>
                    <td class="text-right font-weight-bold"><?= number_format($reportData['closing_balance'], 2) ?> บาท</td>
                </tr>
            </table>
        </div>

        <div class="summary-box">
            <h6><strong>รวมรายจ่าย</strong></h6>
            <table style="width: 100%; border: none;">
                <tr>
                    <td>ค่าใช้จ่าย:</td>
                    <td class="text-right"><?= number_format($reportData['expense_summary']['amount'], 2) ?></td>
                </tr>
                <tr>
                    <td>VAT:</td>
                    <td class="text-right"><?= number_format($reportData['expense_summary']['vat_amount'], 2) ?></td>
                </tr>
                <tr>
                    <td>หัก ณ ที่จ่าย:</td>
                    <td class="text-right"><?= number_format($reportData['expense_summary']['wht'], 2) ?></td>
                </tr>
                <tr>
                    <td>อื่น ๆ:</td>
                    <td class="text-right"><?= number_format($reportData['expense_summary']['other'], 2) ?></td>
                </tr>
                <tr style="border-top: 1px solid #000;">
                    <td class="font-weight-bold">รวมจ่ายจริง:</td>
                    <td class="text-right font-weight-bold"><?= number_format($reportData['expense_summary']['total'], 2) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <p><strong>ผู้รักษาเงินสดย่อย</strong></p>
            <div class="signature-line"></div>
            <p style="margin-top: 5px;">(...........................)</p>
        </div>
        <div class="signature-box">
            <p><strong>ผู้อนุมัติ</strong></p>
            <div class="signature-line"></div>
            <p style="margin-top: 5px;">(...........................)</p>
        </div>
    </div>

    <!-- Report Footer -->
    <div class="text-center" style="margin-top: 30px; font-size: 12px; color: #666;">
        รายงานสร้างเมื่อ: <?= date('d/m/Y H:i:s') ?> |
        ระบบจัดการเงินสดย่อย M.C.O.CO.,LTD
    </div>
</div>

<script>
    // Auto print when page loads (optional)
    // window.onload = function() { window.print(); }

    // Auto close after print (optional)
    window.onafterprint = function() {
        // window.close();
    }
</script>
</body>
</html>
