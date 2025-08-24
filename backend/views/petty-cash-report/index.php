<?php
use yii\helpers\Url;
?>
<div class="petty-cash-report-index">
    <h1><i class="fas fa-chart-bar"></i> รายงานเงินสดย่อย</h1>

    <!-- Dashboard Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= number_format($currentBalance, 2) ?></h4>
                            <p class="mb-0">ยอดคงเหลือปัจจุบัน</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= number_format($totalAdvanced, 2) ?></h4>
                            <p class="mb-0">เบิกทดแทนทั้งหมด</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-hand-holding-usd fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= number_format($totalUsed, 2) ?></h4>
                            <p class="mb-0">ใช้จ่ายทั้งหมด</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-receipt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-<?= $needsRefill ? 'warning' : 'secondary' ?> text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= number_format($pendingAdvance, 2) ?></h4>
                            <p class="mb-0">รออนุมัติ</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> แนวโน้มรายเดือน (12 เดือนที่ผ่านมา)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-pie-chart"></i> สัดส่วนการใช้งาน</h5>
                </div>
                <div class="card-body">
                    <canvas id="usageChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history"></i> การเบิกทดแทนล่าสุด</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>จำนวน</th>
                                <th>สถานะ</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recentAdvances as $advance): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($advance->request_date)) ?></td>
                                    <td class="text-right"><?= number_format($advance->amount, 2) ?></td>
                                    <td>
                                        <?php
                                        $statusLabels = [
                                            'pending' => ['label' => 'รอ', 'class' => 'warning'],
                                            'approved' => ['label' => 'อนุมัติ', 'class' => 'success'],
                                            'rejected' => ['label' => 'ปฏิเสธ', 'class' => 'danger'],
                                            'paid' => ['label' => 'จ่ายแล้ว', 'class' => 'info'],
                                        ];
                                        $status = $statusLabels[$advance->status] ?? ['label' => $advance->status, 'class' => 'secondary'];
                                        ?>
                                        <span class="badge badge-<?= $status['class'] ?>"><?= $status['label'] ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history"></i> การจ่ายเงินล่าสุด</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                            <tr>
                                <th>วันที่</th>
                                <th>รายการ</th>
                                <th>จำนวน</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recentVouchers as $voucher): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($voucher->date)) ?></td>
                                    <td>
                                        <?php
                                        $purpose = $voucher->paid_for ?? '-';
                                        echo strlen($purpose) > 30 ? substr($purpose, 0, 30) . '...' : $purpose;
                                        ?>
                                    </td>
                                    <td class="text-right"><?= number_format($voucher->amount, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-tools"></i> เครื่องมือ</h5>
                </div>
                <div class="card-body">
                    <div class="btn-group mr-2">
                        <?= Html::a('<i class="fas fa-plus"></i> เบิกเงินทดแทน',
                            ['/petty-cash-advance/create'],
                            ['class' => 'btn btn-success']
                        ) ?>
                        <?= Html::a('<i class="fas fa-receipt"></i> จ่ายเงินสดย่อย',
                            ['/petty-cash-voucher/create'],
                            ['class' => 'btn btn-primary']
                        ) ?>
                    </div>

                    <div class="btn-group mr-2">
                        <?= Html::a('<i class="fas fa-file-alt"></i> รายงานสรุป',
                            ['/petty-cash-report/summary'],
                            ['class' => 'btn btn-info']
                        ) ?>
                        <?= Html::a('<i class="fas fa-download"></i> Export Excel',
                            ['/petty-cash-report/export'],
                            ['class' => 'btn btn-secondary']
                        ) ?>
                    </div>

                    <?php if ($needsRefill): ?>
                        <div class="btn-group">
                            <?= Html::a('<i class="fas fa-exclamation-triangle"></i> เติมเงินด่วน',
                                ['/petty-cash-advance/create', 'amount' => $maxAmount - $currentBalance],
                                ['class' => 'btn btn-warning pulse-btn']
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyData = <?= json_encode($monthlyData) ?>;

    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.monthName),
            datasets: [
                {
                    label: 'เบิกทดแทน',
                    data: monthlyData.map(item => item.advanced),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'ใช้จ่าย',
                    data: monthlyData.map(item => item.used),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'ยอดคงเหลือ',
                    data: monthlyData.map(item => item.balance),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'แนวโน้มการใช้งานเงินสดย่อย'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' บาท';
                        }
                    }
                }
            }
        }
    });

    // Usage Pie Chart
    const usageCtx = document.getElementById('usageChart').getContext('2d');
    const currentBalance = <?= $currentBalance ?>;
    const totalUsed = <?= $totalUsed ?>;
    const totalAdvanced = <?= $totalAdvanced ?>;

    new Chart(usageCtx, {
        type: 'doughnut',
        data: {
            labels: ['ยอดคงเหลือ', 'ใช้จ่ายแล้ว'],
            datasets: [{
                data: [currentBalance, totalUsed],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'สัดส่วนการใช้เงิน'
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Auto refresh every 60 seconds
    setInterval(function() {
        location.reload();
    }, 60000);
</script>

<style>
    .pulse-btn {
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .card {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border: none;
        border-radius: 8px;
    }

    .card-header {
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
    }

    .bg-primary { background-color: #007bff !important; }
    .bg-success { background-color: #28a745 !important; }
    .bg-info { background-color: #17a2b8 !important; }
    .bg-warning { background-color: #ffc107 !important; }

    .table td {
        padding: 0.5rem;
    }

    .badge {
        font-size: 0.75em;
    }
</style>

<?php
// views/petty-cash-report/summary.php
use yii\helpers\Html;
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
                    <!-- อาจเพิ่มกราฟหรือข้อมูลเพิ่มเติม -->
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
                                    <td><?= $advance->advance_no ?></td>
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
                                    <td><?= $voucher->pcv_no ?></td>
                                    <td class="text-right"><?= number_format($voucher->amount, 2) ?></td>
                                    <td>
                                        <?php
                                        $purpose = $voucher->paid_for ?? '-';
                                        echo strlen($purpose) > 20 ? substr($purpose, 0, 20) . '...' : $purpose;
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