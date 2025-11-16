<?php
// แก้ไขไฟล์ views/petty-cash-report/index.php
// เพิ่ม use statements ที่ด้านบนของไฟล์

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'รายงานเงินสดย่อย';
?>

<div class="petty-cash-report-index">
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
                            <h4><?= number_format($totalAdvanced + $pendingAdvance, 2) ?></h4>
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

<!--        <div class="col-lg-3 col-md-6">-->
<!--            <div class="card bg---><?php //= $needsRefill ? 'warning' : 'secondary' ?><!-- text-white">-->
<!--                <div class="card-body">-->
<!--                    <div class="d-flex justify-content-between">-->
<!--                        <div>-->
<!--                            <h4>--><?php //= number_format($pendingAdvance, 2) ?><!--</h4>-->
<!--                            <p class="mb-0">รออนุมัติ</p>-->
<!--                        </div>-->
<!--                        <div class="align-self-center">-->
<!--                            <i class="fas fa-clock fa-2x"></i>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
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
<!--                                <th>สถานะ</th>-->
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recentAdvances as $advance): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($advance->request_date)) ?></td>
                                    <td class="text-right"><?= number_format($advance->amount, 2) ?></td>
<!--                                    <td>-->
<!--                                        --><?php
//                                        $statusLabels = [
//                                            'pending' => ['label' => 'รอ', 'class' => 'warning'],
//                                            'approved' => ['label' => 'อนุมัติ', 'class' => 'success'],
//                                            'rejected' => ['label' => 'ปฏิเสธ', 'class' => 'danger'],
//                                            'paid' => ['label' => 'จ่ายแล้ว', 'class' => 'info'],
//                                        ];
//                                        $status = $statusLabels[$advance->status] ?? ['label' => $advance->status, 'class' => 'secondary'];
//                                        ?>
<!--                                        <span class="badge badge---><?php //= $status['class'] ?><!--">--><?php //= $status['label'] ?><!--</span>-->
<!--                                    </td>-->
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
    const totalAdvanced = <?= $totalAdvanced + $pendingAdvance ?>;

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