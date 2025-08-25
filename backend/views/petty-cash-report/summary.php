<?php
// แก้ไขส่วนท้ายของไฟล์ - views/petty-cash-report/summary.php
use yii\helpers\Html;

$this->title = 'รายงานสรุปเงินสดย่อย';
?>

<div class="petty-cash-report-summary">
    <h1><i class="fas fa-file-alt"></i> รายงานสรุปเงินสดย่อย</h1>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-end">
                <div class="col-md-4">
                    <label>จากวันที่</label>
                    <?= Html::input('date', 'from_date', $from_date, ['class' => 'form-control']) ?>
                </div>
                <div class="col-md-4">
                    <label>ถึงวันที่</label>
                    <?= Html::input('date', 'to_date', $to_date, ['class' => 'form-control']) ?>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                    <?= Html::a('<i class="fas fa-print"></i> พิมพ์',
                        ['print-summary', 'from_date' => $from_date, 'to_date' => $to_date],
                        ['class' => 'btn btn-info', 'target' => '_blank']
                    ) ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Report -->
    <div class="card">
        <div class="card-header">
            <h5>รายงานสรุปประจำงวด <?= $summary['period'] ?></h5>
        </div>
        <div class="card-body">
            <!-- Summary Table -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr class="table-info">
                            <td><strong>ยอดยกมา</strong></td>
                            <td class="text-right"><strong><?= number_format($summary['openingBalance'], 2) ?> บาท</strong></td>
                        </tr>
                        <tr class="table-success">
                            <td>เบิกทดแทนในงวด</td>
                            <td class="text-right"><?= number_format($summary['totalAdvanced'], 2) ?> บาท</td>
                        </tr>
                        <tr class="table-warning">
                            <td>ใช้จ่ายในงวด</td>
                            <td class="text-right"><?= number_format($summary['totalUsed'], 2) ?> บาท</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>ยอดยกไป</strong></td>
                            <td class="text-right"><strong><?= number_format($summary['closingBalance'], 2) ?> บาท</strong></td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> สถิติในงวด</h6>
                        <ul class="mb-0">
                            <li>จำนวนครั้งการเบิกทดแทน: <?= count($summary['advances']) ?> ครั้ง</li>
                            <li>จำนวนครั้งการจ่ายเงิน: <?= count($summary['vouchers']) ?> ครั้ง</li>
                            <li>การเบิกเฉลี่ยต่อครั้ง: <?= count($summary['advances']) > 0 ? number_format($summary['totalAdvanced'] / count($summary['advances']), 2) : '0.00' ?> บาท</li>
                            <li>การจ่ายเฉลี่ยต่อครั้ง: <?= count($summary['vouchers']) > 0 ? number_format($summary['totalUsed'] / count($summary['vouchers']), 2) : '0.00' ?> บาท</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Detailed Lists -->
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-hand-holding-usd"></i> รายการเบิกทดแทนในงวด</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>เลขที่</th>
                                <th>จำนวน</th>
                                <th>สถานะ</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($summary['advances'] as $advance): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($advance->request_date)) ?></td>
                                    <td><?= Html::encode($advance->advance_no) ?></td>
                                    <td class="text-right"><?= number_format($advance->amount, 2) ?></td>
                                    <td>
                                        <?php
                                        $statusLabels = [
                                            'approved' => ['label' => 'อนุมัติ', 'class' => 'success'],
                                            'paid' => ['label' => 'จ่ายแล้ว', 'class' => 'info'],
                                        ];
                                        $status = $statusLabels[$advance->status] ?? ['label' => $advance->status, 'class' => 'secondary'];
                                        ?>
                                        <span class="badge badge-<?= $status['class'] ?>"><?= $status['label'] ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($summary['advances'])): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">ไม่มีรายการ</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-6">
                    <h6><i class="fas fa-receipt"></i> รายการจ่ายเงินในงวด</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>เลขที่</th>
                                <th>จำนวน</th>
                                <th>รายการ</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($summary['vouchers'] as $voucher): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($voucher->date)) ?></td>
                                    <td><?= Html::encode($voucher->pcv_no) ?></td>
                                    <td class="text-right"><?= number_format($voucher->amount, 2) ?></td>
                                    <td>
                                        <?php
                                        $purpose = $voucher->paid_for ?? '-';
                                        echo Html::encode(strlen($purpose) > 20 ? substr($purpose, 0, 20) . '...' : $purpose);
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($summary['vouchers'])): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">ไม่มีรายการ</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>