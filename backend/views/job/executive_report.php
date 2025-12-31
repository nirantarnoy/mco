<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\models\Job;

/* @var $this yii\web\View */
/* @var $model Job */
/* @var $purchReqs array */
/* @var $purchases array */
/* @var $journalTrans array */
/* @var $invoices array */
/* @var $billingInvoices array */
/* @var $paymentReceipts array */
/* @var $vehicleExpense array */
/* @var $jobExpenses array */
/* @var $isPdf boolean */

$isPdf = isset($isPdf) ? $isPdf : false;

$this->title = 'รายงานสรุปผู้บริหาร: ' . $model->job_no;
$this->params['breadcrumbs'][] = ['label' => 'รายงานใบงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Register CSS for Executive Report
$this->registerCss('
    .executive-report {
        background-color: #f4f7f6;
        padding: 20px;
        font-family: "thsarabun", sans-serif;
    }
    .report-header {
        background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .summary-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        border-left: 5px solid #1a237e;
        transition: transform 0.3s ease;
    }
    .summary-card:hover {
        transform: translateY(-5px);
    }
    .summary-title {
        color: #666;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }
    .summary-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a237e;
    }
    .section-card {
        background: white;
        border-radius: 15px;
        margin-bottom: 30px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .section-header {
        background: #f8f9fa;
        padding: 15px 25px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .section-header h5 {
        margin: 0;
        font-weight: 600;
        color: #333;
    }
    .table-custom {
        margin-bottom: 0;
    }
    .table-custom thead th {
        background: #f8f9fa;
        border-top: none;
        color: #666;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 15px 25px;
    }
    .table-custom tbody td {
        padding: 15px 25px;
        vertical-align: middle;
        border-top: 1px solid #f1f1f1;
    }
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .btn-export {
        background: #2e7d32;
        color: white;
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-export:hover {
        background: #1b5e20;
        color: white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .btn-print {
        background: #0277bd;
        color: white;
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 600;
        border: none;
        margin-right: 10px;
    }
    .btn-print:hover {
        background: #01579b;
        color: white;
    }
    @media print {
        .no-print { display: none !important; }
        .executive-report { padding: 0; background: white; }
        .report-header { border-radius: 0; box-shadow: none; background: #1a237e !important; -webkit-print-color-adjust: exact; }
        .summary-card { border: 1px solid #ddd; box-shadow: none; break-inside: avoid; }
        .section-card { border: 1px solid #ddd; box-shadow: none; break-inside: avoid; }
    }
');

$total_purch_req = 0;
foreach($purchReqs as $req) $total_purch_req += $req['total_amount'];

$total_purch = 0;
foreach($purchases as $p) $total_purch += $p['net_amount'];

$total_petty = 0;
foreach($pettyCashVouchers as $v) $total_petty += $v['amount'];

$total_invoice = 0;
foreach($invoices as $i) $total_invoice += $i['total_amount'];

?>

<div class="executive-report">
    <!-- Header -->
    <div class="report-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h2 class="mb-1"><?= Html::encode($this->title) ?></h2>
                <p class="mb-0 opacity-75">สรุปข้อมูลความเคลื่อนไหวและสถานะทางการเงินของใบงาน</p>
            </div>
            <div class="col-md-5 text-right no-print">
                <?= Html::a('<i class="fas fa-print"></i> พิมพ์ PDF', ['executive-report-pdf', 'id' => $model->id], [
                    'class' => 'btn btn-print',
                    'target' => '_blank'
                ]) ?>
                <?= Html::a('<i class="fas fa-file-excel"></i> ส่งออก Excel', ['export-executive-report', 'id' => $model->id], [
                    'class' => 'btn btn-export'
                ]) ?>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="summary-card" style="border-left-color: #4caf50;">
                <div class="summary-title">มูลค่างานทั้งหมด</div>
                <div class="summary-value"><?= number_format($model->job_amount, 2) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card" style="border-left-color: #ff9800;">
                <div class="summary-title">ยอดสั่งซื้อ (PO)</div>
                <div class="summary-value"><?= number_format($total_purch, 2) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card" style="border-left-color: #f44336;">
                <div class="summary-title">ยอดเงินสดย่อย</div>
                <div class="summary-value"><?= number_format($total_petty, 2) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card" style="border-left-color: #2196f3;">
                <div class="summary-title">ยอดเปิดบิล (Invoice)</div>
                <div class="summary-value"><?= number_format($total_invoice, 2) ?></div>
            </div>
        </div>
    </div>

    <!-- Job Details -->
    <div class="section-card">
        <div class="section-header">
            <h5><i class="fas fa-info-circle mr-2"></i> ข้อมูลใบงาน</h5>
        </div>
        <div class="p-4">
            <div class="row">
                <div class="col-md-3">
                    <label class="text-muted small uppercase">เลขที่ใบงาน</label>
                    <div class="font-weight-bold"><?= Html::encode($model->job_no) ?></div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small uppercase">วันที่เริ่ม - สิ้นสุด</label>
                    <div><?= date('d/m/Y', strtotime($model->start_date)) ?> - <?= date('d/m/Y', strtotime($model->end_date)) ?></div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small uppercase">สถานะใบงาน</label>
                    <div>
                        <span class="status-badge badge-<?= $model->getStatusColor() ?>">
                            <?= $model->getStatusText() ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small uppercase">ลูกค้า</label>
                    <div class="font-weight-bold">
                        <?php
                        $customer_name = '';
                        if ($model->quotation) {
                            $customer_name = $model->quotation->customer_name;
                        } else {
                            $cus_data = \backend\models\Quotation::findCustomerData2($model->quotation_id);
                            if (!empty($cus_data)) {
                                $customer_name = $cus_data[0]['customer_name'];
                            }
                        }
                        echo Html::encode($customer_name);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Orders Section -->
    <div class="section-card">
        <div class="section-header">
            <h5><i class="fas fa-shopping-cart mr-2"></i> รายการสั่งซื้อ (Purchase Orders)</h5>
            <span class="badge badge-pill badge-light"><?= count($purchases) ?> รายการ</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>เลขที่ใบสั่งซื้อ</th>
                        <th>วันที่</th>
                        <th>ผู้จำหน่าย</th>
                        <th class="text-right">ยอดสุทธิ</th>
                        <th class="text-center">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($purchases)): ?>
                        <?php foreach ($purchases as $p): ?>
                            <tr>
                                <td class="font-weight-bold"><?= Html::encode($p['purch_no']) ?></td>
                                <td><?= date('d/m/Y', strtotime($p['purch_date'])) ?></td>
                                <td><?= Html::encode($p['vendor_name']) ?></td>
                                <td class="text-right font-weight-bold"><?= number_format($p['net_amount'], 2) ?></td>
                                <td class="text-center">
                                    <?php
                                    $status_text = $p['approve_status'] == 1 ? 'อนุมัติ' : ($p['approve_status'] == 2 ? 'ไม่อนุมัติ' : 'รอพิจารณา');
                                    $status_class = $p['approve_status'] == 1 ? 'success' : ($p['approve_status'] == 2 ? 'danger' : 'warning');
                                    ?>
                                    <span class="status-badge badge-<?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">ไม่มีข้อมูลรายการสั่งซื้อ</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Petty Cash Section -->
    <div class="section-card">
        <div class="section-header">
            <h5><i class="fas fa-wallet mr-2"></i> รายการเงินสดย่อย (Petty Cash)</h5>
            <span class="badge badge-pill badge-light"><?= count($pettyCashVouchers) ?> รายการ</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>เลขที่ใบเบิก</th>
                        <th>วันที่</th>
                        <th>ผู้เบิก</th>
                        <th>วัตถุประสงค์</th>
                        <th class="text-right">จำนวนเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pettyCashVouchers)): ?>
                        <?php foreach ($pettyCashVouchers as $v): ?>
                            <tr>
                                <td class="font-weight-bold"><?= Html::encode($v['pcv_no']) ?></td>
                                <td><?= date('d/m/Y', strtotime($v['pcv_date'])) ?></td>
                                <td><?= Html::encode($v['issued_by']) ?></td>
                                <td><?= Html::encode($v['paid_for']) ?></td>
                                <td class="text-right font-weight-bold text-danger"><?= number_format($v['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">ไม่มีข้อมูลรายการเงินสดย่อย</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Invoices Section -->
    <div class="section-card">
        <div class="section-header">
            <h5><i class="fas fa-file-invoice-dollar mr-2"></i> รายการเปิดบิล (Invoices)</h5>
            <span class="badge badge-pill badge-light"><?= count($invoices) ?> รายการ</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>เลขที่ใบกำกับ</th>
                        <th>วันที่</th>
                        <th>ลูกค้า</th>
                        <th class="text-right">ยอดสุทธิ</th>
                        <th class="text-center">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoices)): ?>
                        <?php foreach ($invoices as $i): ?>
                            <tr>
                                <td class="font-weight-bold"><?= Html::encode($i['invoice_number']) ?></td>
                                <td><?= date('d/m/Y', strtotime($i['invoice_date'])) ?></td>
                                <td><?= Html::encode($i['customer_name']) ?></td>
                                <td class="text-right font-weight-bold text-primary"><?= number_format($i['total_amount'], 2) ?></td>
                                <td class="text-center">
                                    <span class="status-badge badge-<?= $i['status'] == 1 ? 'success' : 'secondary' ?>">
                                        <?= $i['status'] == 1 ? 'ปกติ' : 'ยกเลิก' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">ไม่มีข้อมูลรายการเปิดบิล</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <!-- Billing Invoices Section -->
    <div class="section-card">
        <div class="section-header">
            <h5><i class="fas fa-file-invoice mr-2"></i> รายการวางบิล (Billing Invoices)</h5>
            <span class="badge badge-pill badge-light"><?= count($billingInvoices) ?> รายการ</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>เลขที่ใบวางบิล</th>
                        <th>วันที่</th>
                        <th class="text-right">ยอดรวม</th>
                        <th class="text-center">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($billingInvoices)): ?>
                        <?php foreach ($billingInvoices as $bi): ?>
                            <tr>
                                <td class="font-weight-bold"><?= Html::encode($bi['billing_number']) ?></td>
                                <td><?= date('d/m/Y', strtotime($bi['billing_date'])) ?></td>
                                <td class="text-right font-weight-bold"><?= number_format($bi['total_amount'], 2) ?></td>
                                <td class="text-center">
                                    <span class="status-badge badge-<?= $bi['status'] == 1 ? 'success' : 'secondary' ?>">
                                        <?= $bi['status'] == 1 ? 'ปกติ' : 'ยกเลิก' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">ไม่มีข้อมูลรายการวางบิล</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Receipts Section -->
    <div class="section-card">
        <div class="section-header">
            <h5><i class="fas fa-receipt mr-2"></i> รายการรับชำระเงิน (Payment Receipts)</h5>
            <span class="badge badge-pill badge-light"><?= count($paymentReceipts) ?> รายการ</span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>เลขที่ใบเสร็จ</th>
                        <th>วันที่</th>
                        <th>วิธีชำระเงิน</th>
                        <th class="text-right">ยอดรับเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($paymentReceipts)): ?>
                        <?php foreach ($paymentReceipts as $pr): ?>
                            <tr>
                                <td class="font-weight-bold"><?= Html::encode($pr['receipt_number']) ?></td>
                                <td><?= date('d/m/Y', strtotime($pr['payment_date'])) ?></td>
                                <td><?= Html::encode($pr['payment_method']) ?></td>
                                <td class="text-right font-weight-bold text-success"><?= number_format($pr['received_amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">ไม่มีข้อมูลรายการรับชำระเงิน</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Vehicle Expenses Section -->
            <div class="section-card">
                <div class="section-header">
                    <h5><i class="fas fa-car mr-2"></i> ค่าใช้จ่ายรถ (Vehicle Expenses)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>ทะเบียน</th>
                                <th class="text-right">จำนวนเงิน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($vehicleExpense)): ?>
                                <?php foreach ($vehicleExpense as $ve): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($ve['trans_date'])) ?></td>
                                        <td><?= Html::encode($ve['plate_no']) ?></td>
                                        <td class="text-right text-danger"><?= number_format($ve['amount'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted">ไม่มีข้อมูลค่าใช้จ่ายรถ</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <!-- Job Expenses Section -->
            <div class="section-card">
                <div class="section-header">
                    <h5><i class="fas fa-exclamation-circle mr-2"></i> ค่าใช้จ่ายอื่นๆ (Job Expenses)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>รายการ</th>
                                <th class="text-right">จำนวนเงิน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($jobExpenses)): ?>
                                <?php foreach ($jobExpenses as $je): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($je->trans_date)) ?></td>
                                        <td><?= Html::encode($je->description) ?></td>
                                        <td class="text-right text-danger"><?= number_format($je->line_amount, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted">ไม่มีข้อมูลค่าใช้จ่ายอื่นๆ</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
