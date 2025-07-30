<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dailyStats array */
/* @var $topActions array */
/* @var $topUsers array */
/* @var $statistics array */

$this->title = 'Action Logs Dashboard';
$this->params['breadcrumbs'][] = ['label' => 'Action Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Dashboard';

// Register Chart.js
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js');
?>

<div class="action-log-dashboard">

    <div class="row">
        <div class="col-md-12">
            <h1><?= Html::encode($this->title) ?></h1>
            <?= Html::a('Back to Logs', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="row" style="margin-top: 20px;">
        <div class="col-md-3">
            <div class="stats-card panel panel-primary">
                <div class="panel-body text-center">
                    <i class="glyphicon glyphicon-list-alt" style="font-size: 3em; margin-bottom: 10px;"></i>
                    <h2><?= number_format($statistics['total_logs']) ?></h2>
                    <p>Total Logs (30 days)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card panel panel-success">
                <div class="panel-body text-center">
                    <i class="glyphicon glyphicon-ok-circle" style="font-size: 3em; margin-bottom: 10px;"></i>
                    <h2><?= number_format($statistics['success_logs']) ?></h2>
                    <p>Success Logs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card panel panel-danger">
                <div class="panel-body text-center">
                    <i class="glyphicon glyphicon-remove-circle" style="font-size: 3em; margin-bottom: 10px;"></i>
                    <h2><?= number_format($statistics['failed_logs']) ?></h2>
                    <p>Failed Logs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card panel panel-info">
                <div class="panel-body text-center">
                    <i class="glyphicon glyphicon-user" style="font-size: 3em; margin-bottom: 10px;"></i>
                    <h2><?= number_format($statistics['unique_users']) ?></h2>
                    <p>Unique Users</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Daily Activity Chart -->
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Daily Activity (Last 7 Days)</h4>
                </div>
                <div class="panel-body">
                    <canvas id="dailyChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Status Distribution</h4>
                </div>
                <div class="panel-body">
                    <canvas id="statusChart" width="200" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row">
        <!-- Top Actions -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Top Actions (Last 30 Days)</h4>
                </div>
                <div class="panel-body">
                    <?php if ($topActions): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Action</th>
                                    <th class="text-right">Count</th>
                                    <th class="text-right">%</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $totalActions = array_sum(array_column($topActions, 'count'));
                                foreach ($topActions as $action):
                                    $percentage = $totalActions > 0 ? round(($action['count'] / $totalActions) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <?= Html::a(Html::encode($action['action']), ['index', 'ActionLogSearch[action]' => $action['action']], [
                                                'class' => 'text-primary'
                                            ]) ?>
                                        </td>
                                        <td class="text-right"><?= number_format($action['count']) ?></td>
                                        <td class="text-right"><?= $percentage ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No action data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Users -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Most Active Users (Last 30 Days)</h4>
                </div>
                <div class="panel-body">
                    <?php if ($topUsers): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Username</th>
                                    <th class="text-right">Actions</th>
                                    <th class="text-right">%</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $totalUserActions = array_sum(array_column($topUsers, 'count'));
                                foreach ($topUsers as $user):
                                    $percentage = $totalUserActions > 0 ? round(($user['count'] / $totalUserActions) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <?= Html::a(Html::encode($user['username']), ['index', 'ActionLogSearch[user_search]' => $user['username']], [
                                                'class' => 'text-primary'
                                            ]) ?>
                                        </td>
                                        <td class="text-right"><?= number_format($user['count']) ?></td>
                                        <td class="text-right"><?= $percentage ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No user data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .stats-card {
        margin-bottom: 20px;
    }

    .stats-card .panel-body {
        padding: 30px 15px;
    }

    .stats-card h2 {
        margin: 0;
        color: #fff;
        font-size: 2.5em;
        font-weight: bold;
    }

    .stats-card p {
        margin: 5px 0 0 0;
        color: #fff;
        font-size: 1.1em;
    }

    .stats-card i {
        color: rgba(255, 255, 255, 0.8);
    }

    .panel-primary .stats-card h2,
    .panel-primary .stats-card p,
    .panel-primary .stats-card i {
        color: #fff;
    }

    .table th {
        border-top: none;
    }

    .text-primary {
        text-decoration: none;
    }

    .text-primary:hover {
        text-decoration: underline;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Daily Activity Chart
        const dailyData = <?= json_encode($dailyStats) ?>;

        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [
                    {
                        label: 'Total',
                        data: dailyData.map(item => item.total),
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.1,
                        fill: true
                    },
                    {
                        label: 'Success',
                        data: dailyData.map(item => item.success),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.1
                    },
                    {
                        label: 'Failed',
                        data: dailyData.map(item => item.failed),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                }
            }
        });

        // Status Distribution Chart
        const statusData = [
            <?= $statistics['success_logs'] ?>,
            <?= $statistics['failed_logs'] ?>,
            <?= $statistics['warning_logs'] ?>
        ];

        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Success', 'Failed', 'Warning'],
                datasets: [{
                    data: statusData,
                    backgroundColor: [
                        '#28a745',
                        '#dc3545',
                        '#ffc107'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: false
                    }
                }
            }
        });
    });
</script>