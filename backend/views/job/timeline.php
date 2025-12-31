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

.bg-warning-light {
    background-color: rgba(255, 193, 7, 0.4) !important; /* 0.4 = โปร่งใส 40% */
}

.alert-warning-light{
 background-color: rgba(255, 193, 7, 0.4) !important; /* 0.4 = โปร่งใส 40% */
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

$start = new DateTime($model->start_date);
$end   = new DateTime($model->end_date);

$today = new DateTime("now");
$is_over = 0;
$diff = $start->diff($end);
if($today > $end){
    $is_over = 1;
  $diff = $today->diff($end);
}
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
                                <?= Html::a('<i class="fas fa-arrow-left"></i> กลับ', ['job-report/index'], [
                                    'class' => 'btn btn-light btn-sm'
                                ]) ?>
                                <?= Html::a('<i class="fas fa-chart-line"></i> รายงานผู้บริหาร', ['executive-report', 'id' => $model->id], [
                                    'class' => 'btn btn-success btn-sm'
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
                            <div class="col-md-2">
                                <strong>รหัสใบงาน:</strong><br>
                                <span class="text-primary h5"><?= Html::encode($model->job_no) ?></span>
                            </div>
                            <div class="col-md-2">
                                <strong>วันที่เริ่ม:</strong><br>
                                <span class="text-info"><?= $model->start_date ? date('d/m/Y', strtotime($model->start_date)) : '-' ?></span>
                            </div>
                            <div class="col-md-2">
                                <strong>ถึงวันที่:</strong><br>
                                <span class="text-info"><?= $model->end_date ? date('d/m/Y', strtotime($model->end_date)) : '-' ?></span>
                            </div>
                            <div class="col-md-2">
                                <strong>ครบกำหนดในอีก:</strong><br>
                                <span class="<?= $is_over==1? 'text-danger' : 'text-success' ?>"><?= $is_over==1? 'เกินกำหนด '. $diff->format("%a").' วัน' : $diff->format("%a").' วัน' ?></span>
                            </div>
                            <div class="col-md-2">
                                <strong>สถานะ:</strong><br>
                                <?= Html::tag('span', $model->getStatusText(), [
                                    'class' => 'badge badge-' . $model->getStatusColor() . ' p-2'
                                ]) ?>
                            </div>
                            <div class="col-md-2">
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
                                        <th style="text-align: right;">มูลค่า</th>
                                        <th>หมายเหตุ</th>
                                        <th>เอกสาร</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($purchReqs as $req): ?>
                                        <?php
                                        $line_status = '';
                                        if ($req['approve_status'] == 0) {
                                            $line_status = 'รอพิจารณา';
                                        } else if ($req['approve_status'] == 1) {
                                            $line_status = 'อนุมัติ';
                                        } else if ($req['approve_status'] == 2) {
                                            $line_status = 'ไม่อนุมัติ';
                                        } else if ($req['approve_status'] == 3) {
                                            $line_status = 'ยกเลิก';
                                        }
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($req['purch_req_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($req['purch_req_date'])) ?></td>
                                            <td style="text-align: center;"><?= Html::encode($req['fname'] . ' ' . $req['lname']) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $line_status, [
                                                    'class' => 'badge badge-' . ($line_status == 'อนุมัติ' ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                            <td class="text-right"><?= number_format($req['total_amount'], 2) ?></td>
                                            <td><?= Html::encode($req['note']) ?></td>
                                            <td style="text-align: center;"><a class="badge badge-info" href="<?=Url::to(['job/documents','id'=>$model->id,'type'=>'purch_req','activityId'=>$req['id']],true)?>"><i class="fa fa-eye"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
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
                                        <th style="text-align: right;">มูลค่า</th>
                                        <th style="text-align: right;">ส่วนลด</th>
                                        <th style="text-align: right;">VAT</th>
                                        <th style="text-align: right;">สุทธิ</th>
                                        <th>เอกสาร</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($purchases as $purchase): ?>
                                        <?php
                                        $line_status = '';
                                        if ($purchase['approve_status'] == 0) {
                                            $line_status = 'รอพิจารณา';
                                        } else if ($purchase['approve_status'] == 1) {
                                            $line_status = 'อนุมัติ';
                                        } else if ($purchase['approve_status'] == 2) {
                                            $line_status = 'ไม่อนุมัติ';
                                        } else if ($purchase['approve_status'] == 3) {
                                            $line_status = 'ยกเลิก';
                                        }
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($purchase['purch_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($purchase['purch_date'])) ?></td>
                                            <td style="text-align: center;"><?= Html::encode($purchase['vendor_name']) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $line_status, [
                                                    'class' => 'badge badge-' . ($line_status == 'อนุมัติ' ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                            <td class="text-right"><?= number_format($purchase['total_amount'], 2) ?></td>
                                            <td class="text-right"><?= number_format($purchase['discount_amount'], 2) ?></td>
                                            <td class="text-right"><?= number_format($purchase['vat_amount'], 2) ?></td>
                                            <td class="text-right font-weight-bold"><?= number_format($purchase['net_amount'], 2) ?></td>
                                            <td style="text-align: center;"><a class="badge badge-info" href="<?=Url::to(['job/documents','id'=>$model->id,'type'=>'purch','activityId'=>$purchase['id']],true)?>"><i class="fa fa-eye"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                ไม่มีข้อมูลใบสั่งซื้อสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Purchase Order None PR Section -->
            <div class="timeline-section">
                <div class="card border-warning-light">
                    <div class="card-header bg-warning-light text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart"></i>
                            ใบสั่งซื้อ (Purchase None PR)
                            <span class="badge badge-dark ml-2"><?= count($purchasesnonepr) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($purchasesnonepr)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขใบสั่งซื้อ</th>
                                        <th>วันที่</th>
                                        <th>ผู้จำหน่าย</th>
                                        <th style="text-align: right;">มูลค่า</th>
                                        <th>เอกสาร</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($purchasesnonepr as $purchase): ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($purchase['purch_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($purchase['purch_date'])) ?></td>
                                            <td style="text-align: center;"><?= Html::encode($purchase['vendor_name']) ?></td>
                                            <td class="text-right"><?= number_format($purchase['total_amount'], 2) ?></td>
                                            <td style="text-align: center;"><a class="badge badge-info" href="<?=Url::to(['job/documents','id'=>$model->id,'type'=>'purch','activityId'=>$purchase['id']],true)?>"><i class="fa fa-eye"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
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
                                        <th style="text-align: right;">จำนวน</th>
                                        <th style="text-align: center;">สถานะ</th>
                                        <th>หมายเหตุ</th>
                                        <th>เอกสาร</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($journalTrans as $trans): ?>
                                        <?php
                                           $line_type_name = '';
                                           if($trans['trans_type_id']==3){
                                               $line_type_name ='เบิกสินค้า';
                                           }else if($trans['trans_type_id']==4){
                                               $line_type_name ='คืนสินค้า';
                                           }else if($trans['trans_type_id']==5){
                                               $line_type_name ='ยืมสินค้า';
                                           }else if($trans['trans_type_id']==6){
                                               $line_type_name ='คืนยืมสินค้า';
                                           }
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($trans['journal_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($trans['trans_date'])) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $line_type_name, [
                                                    'class' => 'badge badge-' . (in_array((int)$trans['trans_type_id'],[4,6] )  ? 'success' : 'danger')
                                                ]) ?>
                                            </td>
                                            <td style="text-align: left;"><?= Html::encode($trans['customer_name']) ?></td>
                                            <td style="text-align: right;"><?= number_format($trans['qty'], 0) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', 'completed', [
                                                    'class' => 'badge badge-' . ($trans['status'] == 0 ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                            <td><?= Html::encode($trans['remark']) ?></td>
                                            <td style="text-align: center;"><a class="badge badge-info" href="<?=Url::to(['job/documents','id'=>$model->id,'type'=>'journal_trans','activityId'=>$trans['id']],true)?>"><i class="fa fa-eye"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลรายการรับ-เบิกของสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Petty Cash Voucher Section -->
            <div class="timeline-section">
                <div class="card border-dark">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cash-register"></i>
                            ใบเบิกเงินสดย่อย (Petty Cash Voucher)
                            <span class="badge badge-light text-dark ml-2"><?= count($pettyCashVouchers) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($pettyCashVouchers)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="text-align: center;">เลขที่ใบเบิก</th>
                                        <th style="text-align: center;">วันที่</th>
                                        <th style="text-align: center;">ผู้เบิก</th>
                                        <th style="text-align: center;">เบิกให้กับ</th>
                                        <th style="text-align: center;">วัตถุประสงค์</th>
                                        <th style="text-align: right;">จำนวนเงิน</th>
                                        <th style="text-align: center;">สถานะ</th>
                                        <th style="text-align: center;">ผู้อนุมัติ</th>
                                        <th style="text-align: center;">เอกสาร</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $totalPettyCash = 0;
                                    foreach ($pettyCashVouchers as $voucher):
                                        $totalPettyCash += $voucher['amount'];

                                        $approve_status_text = '';
                                        $approve_status_color = '';
                                        if ($voucher['approve_status'] == 0) {
                                            $approve_status_text = 'รอพิจารณา';
                                            $approve_status_color = 'warning';
                                        } else if ($voucher['approve_status'] == 1) {
                                            $approve_status_text = 'อนุมัติ';
                                            $approve_status_color = 'success';
                                        } else if ($voucher['approve_status'] == 2) {
                                            $approve_status_text = 'ไม่อนุมัติ';
                                            $approve_status_color = 'danger';
                                        } else if ($voucher['approve_status'] == 3) {
                                            $approve_status_text = 'ยกเลิก';
                                            $approve_status_color = 'secondary';
                                        }

                                        // กำหนดชื่อผู้รับเงิน
                                        $recipient_name = '';
                                        if (!empty($voucher['employee_name'])) {
                                            $recipient_name = $voucher['employee_name'] . ' (พนักงาน)';
                                        } else if (!empty($voucher['customer_name'])) {
                                            $recipient_name = $voucher['customer_name'] . ' (ลูกค้า)';
                                        } else if (!empty($voucher['vendor_name'])) {
                                            $recipient_name = $voucher['vendor_name'] . ' (ผู้จำหน่าย)';
                                        } else {
                                            $recipient_name = Html::encode($voucher['name']);
                                        }
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($voucher['pcv_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($voucher['pcv_date'])) ?></td>
                                            <td style="text-align: center;"><?= Html::encode($voucher['issued_by']) ?></td>
                                            <td style="text-align: center;"><?= $recipient_name ?></td>
                                            <td><?= Html::encode($voucher['paid_for']) ?></td>
                                            <td style="text-align: right;" class="font-weight-bold"><?= number_format($voucher['amount'], 2) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $approve_status_text, [
                                                    'class' => 'badge badge-' . $approve_status_color
                                                ]) ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <?php if (!empty($voucher['approved_by'])): ?>
                                                    <?= Html::encode($voucher['approved_by']) ?><br>
                                                    <small class="text-muted"><?= date('d/m/Y', strtotime($voucher['approved_date'])) ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <a class="badge badge-info" href="<?=Url::to(['job/documents','id'=>$model->id,'type'=>'petty_cash_voucher','activityId'=>$voucher['id']],true)?>">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <?php if (!empty($voucher['details'])): ?>
                                        <tr class="bg-light">
                                            <td colspan="9" style="padding-left: 50px;">
                                                <small>
                                                    <strong>รายละเอียด:</strong>
                                                    <?php foreach ($voucher['details'] as $detail): ?>
                                                        <br>• <?= Html::encode($detail['detail']) ?>
                                                        (<?= number_format($detail['amount'], 2) ?> บาท)
                                                    <?php endforeach; ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="5" class="text-right font-weight-bold">รวมเงินสดย่อยทั้งหมด:</td>
                                        <td class="text-right font-weight-bold text-danger"><?= number_format($totalPettyCash, 2) ?></td>
                                        <td colspan="3"></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลใบเบิกเงินสดย่อยสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Vehicle Expense Section -->
            <div class="timeline-section">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-truck"></i>
                            ค่าใช้จ่ายรถ
                            <span class="badge badge-light text-dark ml-2"><?= count($vehicleExpense) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($vehicleExpense)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th style="text-align: center;">ทะเบียนรถ</th>
                                        <th style="text-align: center;">วันที่</th>
                                        <th style="text-align: right;">ระยะทาง</th>
                                        <th style="text-align: right;">ค่าใช้จ่ายรถ</th>
                                        <th style="text-align: right;">จำนวนคน</th>
                                        <th style="text-align: right;">ค่าจ้างรวม</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($vehicleExpense as $expanse): ?>
                                        <tr>
                                            <td style="text-align: center;"><?= Html::encode($expanse['vehicle_no']) ?></td>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($expanse['expense_date'])) ?></td>
                                            <td style="text-align: right;"><?= number_format($expanse['total_distance'],0) ?></td>
                                            <td style="text-align: right;"><?= number_format($expanse['vehicle_cost'], 0) ?></td>
                                            <td style="text-align: right;"><?= number_format($expanse['passenger_count'], 0) ?></td>
                                            <td style="text-align: right;"><?= number_format($expanse['total_wage'], 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลรายการค่าใช้จ่ายรถสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Invoice Section -->
            <div class="timeline-section">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
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
                                        <th style="text-align: right;">ยอดก่อนภาษี</th>
                                        <th style="text-align: right;">ส่วนลด</th>
                                        <th style="text-align: right;">VAT</th>
                                        <th style="text-align: right;">ยอดสุทธิ</th>
                                        <th style="text-align: center;">สถานะ</th>
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
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', 'completed', [
                                                    'class' => 'badge badge-' . ($invoice['status'] == 1 ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลใบกำกับภาษี/ใบเสร็จสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Billing Invoice Placement Section -->
            <div class="timeline-section">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-invoice-dollar"></i>
                            ใบวางบิล (Bill Placement)
                            <span class="badge badge-light text-dark ml-2"><?= count($billingInvoices) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($billingInvoices)): ?>
                            <?php foreach ($billingInvoices as $billing): ?>
                                <div class="billing-group mb-4">
                                    <!-- หัวข้อใบวางบิล -->
                                    <div class="billing-header bg-light p-3 rounded mb-2">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>เลขใบวางบิล:</strong>
                                                <span class="text-primary"><?= Html::encode($billing['billing_number']) ?></span>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>วันที่:</strong>
                                                <?= date('d/m/Y', strtotime($billing['billing_date'])) ?>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>ลูกค้า:</strong>
                                                <?= Html::encode($billing['customer_name']) ?>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>ยอดรวม:</strong>
                                                <span class="text-success font-weight-bold"><?= number_format($billing['total_amount'], 2) ?></span>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>สถานะ:</strong>
                                                <?= Html::tag('span', $billing['status'], [
                                                    'class' => 'badge badge-' . ($billing['status'] == 'issued' ? 'success' : 'warning')
                                                ]) ?>
                                            </div>
                                        </div>

                                        <!-- ข้อมูลเพิ่มเติม -->
                                        <div class="row mt-2">
                                            <div class="col-md-3">
                                                <small class="text-muted">ยอดก่อนภาษี: <?= number_format($billing['subtotal'], 2) ?></small>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">ส่วนลด: <?= number_format($billing['discount_amount'], 2) ?> (<?= $billing['discount_percent'] ?>%)</small>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">VAT: <?= number_format($billing['vat_amount'], 2) ?> (<?= $billing['vat_percent'] ?>%)</small>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">กำหนดชำระ: <?= $billing['payment_due_date'] ? date('d/m/Y', strtotime($billing['payment_due_date'])) : '-' ?></small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- รายการ Invoice ในใบวางบิล -->
                                    <div class="table-responsive pl-4">
                                        <table class="table table-sm table-hover border-left">
                                            <thead class="thead-light">
                                            <tr>
                                                <th>เลขใบกำกับ</th>
                                                <th>วันที่</th>
                                                <th style="text-align: right;">ยอดก่อนภาษี</th>
                                                <th style="text-align: right;">VAT</th>
                                                <th style="text-align: right;">ยอดสุทธิ</th>
                                                <th style="text-align: right;">ยอดค้างชำระ</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($billing['items'] as $item): ?>
                                                <tr>
                                                    <td><?= Html::encode($item['invoice_number']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($item['invoice_date'])) ?></td>
                                                    <td class="text-right"><?= number_format($item['subtotal'], 2) ?></td>
                                                    <td class="text-right"><?= number_format($item['vat_amount'], 2) ?></td>
                                                    <td class="text-right"><?= number_format($item['total_amount'], 2) ?></td>
                                                    <td class="text-right text-danger"><?= number_format($item['remaining_balance'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลใบวางบิลสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Payment Receipt Section -->
            <div class="timeline-section">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-receipt"></i>
                            ใบเสร็จรับเงิน (Payment Receipts)
                            <span class="badge badge-light text-dark ml-2"><?= count($paymentReceipts) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($paymentReceipts)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>เลขที่ใบเสร็จ</th>
                                        <th>วันที่รับชำระ</th>
                                        <th>ลูกค้า</th>
                                        <th>วิธีชำระ</th>
                                        <th style="text-align: right;">ยอดรับจริง</th>
                                        <th style="text-align: right;">VAT</th>
                                        <th style="text-align: right;">WHT</th>
                                        <th style="text-align: right;">ยอดสุทธิ</th>
                                        <th style="text-align: center;">สถานะ</th>
                                        <th>ผู้รับเงิน</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($paymentReceipts as $receipt): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?= Html::encode($receipt['receipt_number']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($receipt['payment_date'])) ?></td>
                                            <td><?= Html::encode($receipt['customer_name']) ?></td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?= Html::encode($receipt['payment_method']) ?>
                                                </span>
                                                <?php if ($receipt['payment_method'] == 'Cheque'): ?>
                                                    <br><small>No: <?= Html::encode($receipt['cheque_number']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right"><?= number_format($receipt['received_amount'], 2) ?></td>
                                            <td class="text-right"><?= number_format($receipt['vat_amount'], 2) ?></td>
                                            <td class="text-right text-danger"><?= number_format($receipt['withholding_tax'], 2) ?></td>
                                            <td class="text-right font-weight-bold text-success"><?= number_format($receipt['net_amount'], 2) ?></td>
                                            <td style="text-align: center;">
                                                <?= Html::tag('span', $receipt['payment_status'], [
                                                    'class' => 'badge badge-' . ($receipt['payment_status'] == 'completed' ? 'success' : 'warning')
                                                ]) ?>
                                            </td>
                                            <td><?= Html::encode($receipt['receiver_name']) ?></td>
                                        </tr>
                                        <?php if (!empty($receipt['details'])): ?>
                                            <tr class="bg-light">
                                                <td colspan="10" style="padding-left: 40px;">
                                                    <small>
                                                        <strong>รายการที่ชำระ:</strong>
                                                        <?php foreach ($receipt['details'] as $detail): ?>
                                                            <span class="badge badge-outline-secondary ml-2">
                                                                Invoice: <?= Html::encode($detail['invoice_number']) ?>
                                                                (<?= number_format($detail['amount'], 2) ?>)
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลใบเสร็จรับเงินสำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Job Expense Section -->
            <div class="timeline-section">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-coins"></i>
                            ค่าใช้จ่ายอื่นๆ (Job Expenses)
                            <span class="badge badge-light text-dark ml-2"><?= count($jobExpenses) ?> รายการ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($jobExpenses)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                    <tr>
                                        <th>วันที่</th>
                                        <th>รายการ</th>
                                        <th style="text-align: right;">จำนวนเงิน</th>
                                        <th>หมายเหตุ</th>
                                        <th>ผู้บันทึก</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $totalOtherExpense = 0;
                                    foreach ($jobExpenses as $expense):
                                        $totalOtherExpense += $expense->amount;
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($expense->expense_date)) ?></td>
                                            <td><?= Html::encode($expense->description) ?></td>
                                            <td style="text-align: right;" class="font-weight-bold"><?= number_format($expense->amount, 2) ?></td>
                                            <td><?= Html::encode($expense->remark) ?></td>
                                            <td style="text-align: center;"><?= $expense->createdBy ? Html::encode($expense->createdBy->username) : '-' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="2" class="text-right font-weight-bold">รวมค่าใช้จ่ายอื่นๆ:</td>
                                        <td class="text-right font-weight-bold text-danger"><?= number_format($totalOtherExpense, 2) ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-teal mb-0">
                                <i class="fas fa-info-circle"></i>
                                ไม่มีข้อมูลค่าใช้จ่ายอื่นๆ สำหรับใบงานนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div> <!-- End Timeline Container -->

    </div> <!-- End Job Timeline View -->