<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

$this->title = 'รายงานเงินสดย่อย';
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="petty-cash-report">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <?= Html::encode($this->title) ?>
                </h3>
            </div>

            <div class="card-body">
                <!-- Filter Form -->
                <div class="filter-form mb-4">
                    <form id="form-search" method="post" class="form-inline"
                          action="<?= Url::to(['petty-cash-advance/print-petty'],true) ?>">
                        <div class="form-group mr-3 mb-2">
                            <label class="mr-2">จากวันที่:</label>
                            <?= DatePicker::widget([
                                'name' => 'date_from',
                                'value' => $dateFrom,
                                'options' => ['placeholder' => 'เลือกวันที่...', 'class' => 'form-control'],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                    'todayHighlight' => true,
                                ]
                            ]); ?>
                        </div>

                        <div class="form-group mr-3 mb-2">
                            <label class="mr-2">ถึงวันที่:</label>
                            <?= DatePicker::widget([
                                'name' => 'date_to',
                                'value' => $dateTo,
                                'options' => ['placeholder' => 'เลือกวันที่...', 'class' => 'form-control'],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                    'todayHighlight' => true,
                                ]
                            ]); ?>
                        </div>

                        <div class="form-group mr-3 mb-2">
                            <label class="mr-2">เลขที่เอกสาร:</label>
                            <input type="text" name="document_no" value="<?= Html::encode($documentNo) ?>"
                                   class="form-control" placeholder="ค้นหาเลขที่เอกสาร...">
                        </div>

                        <div class="form-group mb-2">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                            <a href="<?= Url::current(['date_from' => null, 'date_to' => null, 'document_no' => null]) ?>"
                               class="btn btn-secondary mr-2">
                                <i class="fas fa-redo"></i> ล้างค่า
                            </a>
                            <a href="<?= Url::current(array_merge(Yii::$app->request->queryParams, ['export' => 'excel'])) ?>"
                               class="btn btn-success mr-2">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                            <button type="button" id="print-report" class="btn btn-info">
                                <i class="fas fa-print"></i> พิมพ์รายงาน
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Report Header -->
                <div class="text-center mb-3">
                    <h4 class="font-weight-bold">รายงานเงินสดย่อย M.C.O.CO.,LTD</h4>
                    <?php if (!empty($dateFrom) || !empty($dateTo)): ?>
                        <p class="text-muted">
                            ระหว่างวันที่
                            <?php if (!empty($dateFrom)): ?>
                                <?= date('d/m/Y', strtotime($dateFrom)) ?>
                            <?php endif; ?>
                            <?php if (!empty($dateTo)): ?>
                                ถึง <?= date('d/m/Y', strtotime($dateTo)) ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Report Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead class="thead-dark">
                        <tr class="text-center">
                            <th style="width: 100px;">วันที่</th>
                            <th style="width: 120px;">เลขที่เอกสาร</th>
                            <th>รายการ</th>
                            <th style="width: 100px;">รายรับ</th>
                            <th colspan="6" class="text-center">รายจ่าย</th>
                            <th style="width: 100px;">คงเหลือ</th>
                        </tr>
                        <tr class="text-center">
                            <th colspan="4"></th>
                            <th style="width: 100px;">คชจ.</th>
                            <th style="width: 80px;">VAT</th>
                            <th style="width: 80px;">VAT<br>ต้องหาม</th>
                            <th style="width: 80px;">W/H</th>
                            <th style="width: 80px;">อื่นๆ</th>
                            <th style="width: 100px;">ทั้งหมด</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted">
                                    <i class="fas fa-info-circle"></i> ไม่พบข้อมูล
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td class="text-center">
                                        <?= date('d/m/Y', strtotime($transaction['date'])) ?>
                                    </td>
                                    <td><?= Html::encode($transaction['document_no']) ?></td>
                                    <td><?= Html::encode($transaction['description']) ?></td>
                                    <td class="text-right">
                                        <?= $transaction['income'] > 0 ? number_format($transaction['income'], 2) : '-' ?>
                                    </td>
                                    <td class="text-right">
                                        <?= $transaction['expense'] > 0 ? number_format($transaction['expense'], 2) : '-' ?>
                                    </td>
                                    <td class="text-right">
                                        <?= $transaction['vat_amount'] > 0 ? number_format($transaction['vat_amount'], 2) : '-' ?>
                                    </td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">
                                        <?= $transaction['wht'] > 0 ? number_format($transaction['wht'], 2) : '-' ?>
                                    </td>
                                    <td class="text-right">
                                        <?= $transaction['other'] > 0 ? number_format($transaction['other'], 2) : '-' ?>
                                    </td>
                                    <td class="text-right">
                                        <?= $transaction['total_expense'] > 0 ? number_format($transaction['total_expense'], 2) : '-' ?>
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        <?= number_format($transaction['balance'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                        <?php if (!empty($transactions)): ?>
                            <tfoot>
                            <tr class="table-info font-weight-bold">
                                <td colspan="3" class="text-center">รวม</td>
                                <td class="text-right">
                                    <?= number_format($totalIncome, 2) ?>
                                </td>
                                <td class="text-right">
                                    <?= number_format($totalExpense, 2) ?>
                                </td>
                                <td class="text-right">
                                    <?= number_format($totalVat, 2) ?>
                                </td>
                                <td class="text-right">-</td>
                                <td class="text-right">
                                    <?= number_format($totalWht, 2) ?>
                                </td>
                                <td class="text-right">
                                    <?= number_format($totalOther, 2) ?>
                                </td>
                                <td class="text-right">
                                    <?= number_format($totalAllExpenses, 2) ?>
                                </td>
                                <td class="text-right text-primary">
                                    <?= number_format($finalBalance, 2) ?>
                                </td>
                            </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Summary Cards -->
                <?php if (!empty($transactions)): ?>
                    <div class="row mt-4 summary-cards">
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-arrow-up"></i> รายรับทั้งหมด
                                    </h5>
                                    <h3 class="mb-0"><?= number_format($totalIncome, 2) ?> บาท</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-arrow-down"></i> รายจ่ายทั้งหมด
                                    </h5>
                                    <h3 class="mb-0"><?= number_format($totalAllExpenses, 2) ?> บาท</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-wallet"></i> คงเหลือ
                                    </h5>
                                    <h3 class="mb-0"><?= number_format($finalBalance, 2) ?> บาท</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <style>
        .petty-cash-report .table th {
            font-size: 0.9rem;
            vertical-align: middle;
        }

        .petty-cash-report .table td {
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .petty-cash-report .table-responsive {
            overflow-x: auto;
        }

        /* Print Styles for A4 with minimal margins */
        @media print {
            /* Hide unnecessary elements */
            .filter-form,
            .card-header,
            .btn,
            .breadcrumb,
            .navbar,
            .sidebar,
            .main-header,
            .main-footer,
            nav,
            .summary-cards {
                display: none !important;
            }

            /* Reset page styling */
            body {
                margin: 2px;
                padding: 2px;
                background: white !important;
            }

            .content-wrapper,
            .content {
                margin: 0 !important;
                padding: 0 !important;
            }

            /* A4 page setup with minimal margins */
            @page {
                size: A4 landscape;
                margin: 5mm 5mm 5mm 5mm; /* Minimal margins: top right bottom left */
            }

            /* Card styling for print */
            .card {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                page-break-inside: avoid;
            }

            .card-body {
                padding: 0 !important;
            }

            /* Report header */
            .text-center.mb-3 {
                margin-bottom: 10px !important;
            }

            .text-center.mb-3 h4 {
                font-size: 16pt !important;
                margin: 5px 0 !important;
                font-weight: bold;
            }

            .text-center.mb-3 p {
                font-size: 11pt !important;
                margin: 3px 0 !important;
            }

            /* Table styling for print */
            .table-responsive {
                overflow: visible !important;
            }

            .table {
                width: 100% !important;
                font-size: 9pt !important;
                border-collapse: collapse !important;
                margin: 0 !important;
            }

            .table th {
                font-size: 9pt !important;
                padding: 4px 3px !important;
                background-color: #f8f9fa !important;
                border: 1px solid #000 !important;
                font-weight: bold !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table td {
                font-size: 8.5pt !important;
                padding: 3px 3px !important;
                border: 1px solid #000 !important;
            }

            .table thead {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table tfoot {
                background-color: #e9ecef !important;
                font-weight: bold !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .table tfoot td {
                font-weight: bold !important;
                font-size: 9pt !important;
            }

            /* Prevent page breaks inside table rows */
            .table tr {
                page-break-inside: avoid;
            }

            /* Text alignment */
            .text-right {
                text-align: right !important;
            }

            .text-center {
                text-align: center !important;
            }

            /* Ensure colors print correctly */
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Additional styles for better on-screen display */
        .summary-cards {
            margin-top: 1.5rem;
        }
    </style>

<?php
// Register print button handler
$this->registerJs("
    $('#print-report').on('click', function() {
        window.print();
    });
");
?>