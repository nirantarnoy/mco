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

$this->title = 'Timeline ใบงาน: ' . $model->job_no;
$this->params['breadcrumbs'][] = ['label' => 'รายงานใบงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Register CSS
$this->registerCss('
.timeline-container {
    position: relative;
    padding-left: 40px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
    padding-top: 20px;
    padding-bottom: 20px;
}

.timeline-container::before {
    content: "";
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(to bottom, #007bff 0%, #17a2b8 25%, #ffc107 50%, #28a745 75%, #343a40 100%);
    border-radius: 2px;
    box-shadow: 0 0 10px rgba(0,123,255,0.3);
}

.timeline-section {
    position: relative;
    margin-bottom: 40px;
    opacity: 1;
    visibility: visible;
    display: block;
}

.timeline-section::before {
    content: "";
    position: absolute;
    left: -32px;
    top: 25px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    border: 4px solid #007bff;
    z-index: 10;
    box-shadow: 0 0 0 4px rgba(255,255,255,0.8);
}

.timeline-section:nth-child(2)::before {
    border-color: #ffc107;
    box-shadow: 0 0 0 4px rgba(255,255,255,0.8);
}

.timeline-section:nth-child(3)::before {
    border-color: #6c757d;
    box-shadow: 0 0 0 4px rgba(255,255,255,0.8);
}

.timeline-section:nth-child(4)::before {
    border-color: #28a745;
    box-shadow: 0 0 0 4px rgba(255,255,255,0.8);
}

.timeline-section:nth-child(5)::before {
    border-color: #343a40;
    box-shadow: 0 0 0 4px rgba(255,255,255,0.8);
}

.timeline-section .card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    overflow: hidden;
    background: rgba(255,255,255,0.95);
}

.timeline-section .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.timeline-section .card-header {
    border: none;
    padding: 20px 25px;
    position: relative;
    overflow: hidden;
}

.timeline-section .card-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 1.1rem;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.timeline-section .card-body {
    padding: 25px;
    background: rgba(255,255,255,0.98);
}

.timeline-section .table {
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 0;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.timeline-section .table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    color: #495057;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    padding: 15px 12px;
    text-align: center;
}

.timeline-section .table tbody tr:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
    transition: all 0.2s ease;
}

.timeline-section .table td {
    border: none;
    border-bottom: 1px solid #f1f3f4;
    padding: 12px;
    vertical-align: middle;
}

.badge {
    font-size: 0.75em;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.alert {
    border: none;
    border-radius: 12px;
    padding: 20px 25px;
    margin-bottom: 0;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.financial-summary .card {
    border-radius: 20px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.financial-summary .card-body {
    padding: 25px;
    text-align: center;
}

.financial-summary .card h5 {
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 15px;
    opacity: 0.8;
}

.financial-summary .card h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.progress-timeline {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 20px;
    padding: 30px;
    border: 1px solid rgba(0,0,0,0.05);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.progress-timeline .progress {
    height: 35px;
    border-radius: 20px;
    background: rgba(0,0,0,0.05);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.progress-timeline .progress-bar {
    border-radius: 20px;
    position: relative;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .timeline-container {
        padding-left: 25px;
    }
    
    .timeline-container::before {
        left: 12px;
        width: 2px;
    }
    
    .timeline-section::before {
        left: 4px;
        width: 10px;
        height: 10px;
        border-width: 2px;
    }
    
    .financial-summary .card h3 {
        font-size: 1.5rem;
    }
    
    .timeline-section .card-header {
        padding: 15px 20px;
    }
    
    .timeline-section .card-body {
        padding: 20px 15px;
    }
}

@media print {
    .timeline-container {
        background: white !important;
        padding-left: 0 !important;
    }
    
    .timeline-container::before,
    .timeline-section::before {
        display: none !important;
    }
    
    .timeline-section .card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        margin-bottom: 20px;
    }
    
    .btn {
        display: none !important;
    }
}
');
?>

    <div class="job-timeline-view">

        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="mb-0">
                                    <i class="fas fa-project-diagram"></i>
                                    <?= Html::encode($this->title) ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <?= Html::a('<i class="fas fa-arrow-left"></i> กลับ', ['index'], [
                                    'class' => 'btn btn-light btn-sm'
                                ]) ?>
                                <?= Html::a('<i class="fas fa-print"></i> พิมพ์', '#', [
                                    'class' => 'btn btn-info btn-sm',
                                    'onclick' => 'window.print(); return false;'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>รหัสใบงาน:</strong><br>
                                <span class="text-primary h5"><?= Html::encode($model->job_no) ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong>วันที่เริ่ม:</strong><br>
                                <span class="text-info"><?= $model->start_date ? date('d/m/Y', strtotime($model->start_date)) : '-' ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong>สถานะ:</strong><br>
                                <?= Html::tag('span', $model->getStatusText(), [
                                    'class' => 'badge badge-' . $model->getStatusColor() . ' p-2'
                                ]) ?>
                            </div>
                            <div class="col-md-3">
                                <strong>มูลค่างาน:</strong><br>
                                <span class="text-success h5"><?= number_format($model->job_amount, 2) ?> บาท</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Container -->
        <div class="timeline-container">

            <!-- Purchase Request Section -->
            <div class="timeline-section">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt"></i>
                            ใบขอซื้อ (Purchase Request)
                            <span class="badge badge-light text-dark ml-2"><?= count($purchReqs) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($purchReqs)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขใบขอซื้อ</th>
                                        <th>วันที่</th>
                                        <th>ผู้ขอ</th>
                                        <th>สถานะ</th>
                                        <th>มูลค่า</th>
                                        <th>หมายเหตุ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($purchReqs as $req): ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($req['purch_req_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($req['purch_req_date'])) ?></td>
                                            <td style="text-align: center;"><?= Html::encode($req['fname'] . ' ' . $req['lname']) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $req['status'], [
                                                    'class' => 'badge badge-' . ($req['status'] == 'approved' ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                            <td class="text-right"><?= number_format($req['total_amount'], 2) ?></td>
                                            <td><?= Html::encode($req['note']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลใบขอซื้อสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Purchase Order Section -->
            <div class="timeline-section">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart"></i>
                            ใบสั่งซื้อ (Purchase Order)
                            <span class="badge badge-dark ml-2"><?= count($purchases) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($purchases)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขใบสั่งซื้อ</th>
                                        <th>วันที่</th>
                                        <th>ผู้จำหน่าย</th>
                                        <th>สถานะ</th>
                                        <th>มูลค่า</th>
                                        <th>ส่วนลด</th>
                                        <th>VAT</th>
                                        <th>สุทธิ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($purchases as $purchase): ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($purchase['purch_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($purchase['purch_date'])) ?></td>
                                            <td style="text-align: center;"><?= Html::encode($purchase['vendor_name']) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $purchase['status'], [
                                                    'class' => 'badge badge-' . ($purchase['status'] == 'approved' ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                            <td class="text-right"><?= number_format($purchase['total_amount'], 2) ?></td>
                                            <td class="text-right"><?= number_format($purchase['discount_amount'], 2) ?></td>
                                            <td class="text-right"><?= number_format($purchase['vat_amount'], 2) ?></td>
                                            <td class="text-right font-weight-bold"><?= number_format($purchase['net_amount'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                ไม่มีข้อมูลใบสั่งซื้อสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Journal Transaction Section -->
            <div class="timeline-section">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-warehouse"></i>
                            รายการรับ-เบิกของ (Journal Transactions)
                            <span class="badge badge-light text-dark ml-2"><?= count($journalTrans) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($journalTrans)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขเอกสาร</th>
                                        <th>วันที่</th>
                                        <th>ประเภท</th>
                                        <th>ลูกค้า</th>
                                        <th>จำนวน</th>
                                        <th>สถานะ</th>
                                        <th>หมายเหตุ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($journalTrans as $trans): ?>
                                        <tr>
                                            <td><?= Html::encode($trans['journal_no']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($trans['trans_date'])) ?></td>
                                            <td>
                                                <?= Html::tag('span', $trans['trans_type_id'], [
                                                    'class' => 'badge badge-' . ($trans['trans_type_id'] == 'IN' ? 'success' : 'danger')
                                                ]) ?>
                                            </td>
                                            <td><?= Html::encode($trans['customer_name']) ?></td>
                                            <td class="text-center"><?= number_format($trans['qty'], 0) ?></td>
                                            <td>
                                                <?= Html::tag('span', $trans['status'], [
                                                    'class' => 'badge badge-' . ($trans['status'] == 'approved' ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                            <td><?= Html::encode($trans['remark']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลรายการรับ-เบิกของสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Invoice Section -->
            <div class="timeline-section">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-invoice-dollar"></i>
                            ใบกำกับภาษี/ใบเสร็จ (Invoices)
                            <span class="badge badge-light text-dark ml-2"><?= count($invoices) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($invoices)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขใบกำกับ</th>
                                        <th>ประเภท</th>
                                        <th>วันที่</th>
                                        <th>ลูกค้า</th>
                                        <th>รหัสลูกค้า</th>
                                        <th>ยอดก่อนภาษี</th>
                                        <th>ส่วนลด</th>
                                        <th>VAT</th>
                                        <th>ยอดสุทธิ</th>
                                        <th>สถานะ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($invoices as $invoice): ?>
                                        <tr>
                                            <td><?= Html::encode($invoice['invoice_number']) ?></td>
                                            <td>
                                                <?= Html::tag('span', $invoice['invoice_type'], [
                                                    'class' => 'badge badge-' . ($invoice['invoice_type'] == 'TAX' ? 'primary' : 'info')
                                                ]) ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></td>
                                            <td><?= Html::encode($invoice['customer_name']) ?></td>
                                            <td><?= Html::encode($invoice['customer_code']) ?></td>
                                            <td class="text-right"><?= number_format($invoice['subtotal'], 2) ?></td>
                                            <td class="text-right"><?= number_format($invoice['discount_amount'], 2) ?></td>
                                            <td class="text-right"><?= number_format($invoice['vat_amount'], 2) ?></td>
                                            <td class="text-right font-weight-bold"><?= number_format($invoice['total_amount'], 2) ?></td>
                                            <td>
                                                <?= Html::tag('span', $invoice['status'], [
                                                    'class' => 'badge badge-' . ($invoice['status'] == 'paid' ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลใบกำกับภาษี/ใบเสร็จสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Section -->
            <div class="timeline-section financial-summary">
                <div class="card border-dark">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie"></i>
                            สรุปการเงิน (Financial Summary)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // คำนวณสรุปทางการเงิน
                        $totalPurchaseAmount = array_sum(array_column($purchases, 'net_amount'));
                        $totalInvoiceAmount = array_sum(array_column($invoices, 'total_amount'));
                        $profitLoss = $model->job_amount - $totalPurchaseAmount;
                        $profitLossPercentage = $model->job_amount > 0 ? ($profitLoss / $model->job_amount) * 100 : 0;
                        ?>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <h5>มูลค่างานทั้งหมด</h5>
                                        <h3><?= number_format($model->job_amount, 2) ?></h3>
                                        <small>บาท</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <h5>ค่าใช้จ่ายรวม</h5>
                                        <h3><?= number_format($totalPurchaseAmount, 2) ?></h3>
                                        <small>บาท</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <h5>ยอดขายรวม</h5>
                                        <h3><?= number_format($totalInvoiceAmount, 2) ?></h3>
                                        <small>บาท</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card <?= $profitLoss >= 0 ? 'bg-warning' : 'bg-warning' ?> text-dark">
                                    <div class="card-body text-center">
                                        <h5><?= $profitLoss >= 0 ? 'กำไร' : 'ขาดทุน' ?></h5>
                                        <h3><?= number_format(abs($profitLoss), 2) ?></h3>
                                        <small>บาท (<?= number_format($profitLossPercentage, 2) ?>%)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Chart -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h6>ความคืบหน้าของงาน</h6>
                                <div class="progress-timeline">
                                    <div class="progress mb-3">
                                        <?php
                                        $steps = [
                                            'ใบขอซื้อ' => !empty($purchReqs),
                                            'ใบสั่งซื้อ' => !empty($purchases),
                                            'รับ-เบิกของ' => !empty($journalTrans),
                                            'ใบกำกับ/ใบเสร็จ' => !empty($invoices)
                                        ];
                                        $completedSteps = array_sum($steps);
                                        $totalSteps = count($steps);
                                        $progressPercentage = ($completedSteps / $totalSteps) * 100;
                                        ?>
                                        <div class="progress-bar <?= $progressPercentage == 100 ? 'bg-success' : 'bg-warning' ?>"
                                             role="progressbar"
                                             style="width: <?= $progressPercentage ?>%"
                                             aria-valuenow="<?= $progressPercentage ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            <?= number_format($progressPercentage, 1) ?>% เสร็จสิ้น
                                        </div>
                                    </div>

                                    <div class="row">
                                        <?php foreach ($steps as $stepName => $isCompleted): ?>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <i class="fas <?= $isCompleted ? 'fa-check-circle text-success' : 'fa-clock text-muted' ?> fa-2x"></i>
                                                    <br>
                                                    <small class="<?= $isCompleted ? 'text-success font-weight-bold' : 'text-muted' ?>">
                                                        <?= $stepName ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php
// Register Simple JavaScript
$this->registerJs('
$(document).ready(function() {
    console.log("Timeline loaded successfully - no animations");
    
    // Basic hover effects only
    $(".table tbody tr").hover(
        function() {
            $(this).css("background-color", "#e3f2fd");
        },
        function() {
            $(this).css("background-color", "");
        }
    );
    
    // Simple tooltips if available
    if (typeof $.fn.tooltip !== "undefined") {
        $("[title]").tooltip();
    }
});
');
?>