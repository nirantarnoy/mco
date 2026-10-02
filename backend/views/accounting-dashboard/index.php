<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'แดชบอร์ดภาพรวมบัญชี (Accounting Dashboard)';
$this->params['breadcrumbs'][] = $this->title;

$today = date('Y-m-d');
?>

<?php
$percentReceived = $totalApproved > 0 ? round(($totalReceived / $totalApproved) * 100) : 0;
// Load Chart.js CDN explicitly
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_HEAD]);
?>

<div class="accounting-dashboard-index">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-sm h-100">
                <span class="info-box-icon bg-info"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">PO อนุมัติแล้ว</span>
                    <span class="info-box-number" style="font-size: 1.5rem;"><?= $totalApproved ?> <small class="font-weight-normal">ใบ</small></span>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-sm h-100">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">รับเข้าครบแล้ว</span>
                    <span class="info-box-number" style="font-size: 1.5rem;"><?= $totalReceived ?> <small class="font-weight-normal">ใบ</small></span>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-sm h-100">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted font-weight-bold">รอรับสินค้า</span>
                    <span class="info-box-number" style="font-size: 1.5rem;"><?= $totalPending ?> <small class="font-weight-normal">ใบ</small></span>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-sm h-100">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-danger font-weight-bold">เกินกำหนดรับ</span>
                    <span class="info-box-number text-danger" style="font-size: 1.5rem;"><?= $totalOverdue ?> <small class="font-weight-normal">ใบ</small></span>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6 mb-2">
            <div class="info-box shadow-sm h-100">
                <span class="info-box-icon bg-primary"><i class="fas fa-wallet"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-primary font-weight-bold">ทำบัญชี (PV) แล้ว</span>
                    <span class="info-box-number text-primary" style="font-size: 1.5rem;"><?= $totalAccounted ?> <small class="font-weight-normal">ใบ</small></span>
                </div>
            </div>
        </div>
    </div>
    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Bar Chart for Comparison -->
        <div class="col-md-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="text-uppercase text-muted font-weight-bold mb-0">เปรียบเทียบใบสั่งซื้อ</h6>
                    <h4 class="font-weight-bold text-dark">ยอด PO อนุมัติแล้ว VS รับเข้าแล้ว</h4>
                </div>
                <div class="card-body">
                    <canvas id="poBarChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Donut Chart for Percentage -->
        <div class="col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="text-uppercase text-muted font-weight-bold mb-0">สัดส่วนการรับเข้า</h6>
                    <h4 class="font-weight-bold text-dark">เปอร์เซ็นต์การรับสินค้า</h4>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <div style="position: relative; width: 100%; max-width: 200px; height: 200px;">
                        <canvas id="poDonutChart"></canvas>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                            <h3 class="font-weight-bold mb-0 text-success" id="donutPercent"><?= $percentReceived ?>%</h3>
                            <small class="text-muted">รับเข้าแล้ว</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> ภาพรวมใบสั่งซื้อ (PO) และการติดตามเอกสาร</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> หน้านี้แสดงภาพรวมของ PO ที่ผ่านการอนุมัติแล้ว เพื่อติดตามวันที่รับสินค้าและเอกสารที่เกี่ยวข้อง
            </div>
            
            <?php Pjax::begin(['id' => 'accounting-dashboard-pjax', 'timeout' => 5000]); ?>
            
            <div class="mb-4 d-flex flex-wrap gap-2">
                <a href="<?= Url::to(['index', 'filter' => 'all']) ?>" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?> shadow-sm" data-pjax="1">
                    ทั้งหมด
                </a>
                <a href="<?= Url::to(['index', 'filter' => '3days']) ?>" class="btn <?= $filter === '3days' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-warning text-dark' ?> shadow-sm" data-pjax="1">
                    จะรับสินค้าใน 3 วัน
                </a>
                <a href="<?= Url::to(['index', 'filter' => '5days']) ?>" class="btn <?= $filter === '5days' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-warning text-dark' ?> shadow-sm" data-pjax="1">
                    จะรับสินค้าใน 5 วัน
                </a>
                <a href="<?= Url::to(['index', 'filter' => '7days']) ?>" class="btn <?= $filter === '7days' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-warning text-dark' ?> shadow-sm" data-pjax="1">
                    จะรับสินค้าใน 7 วัน
                </a>
                <a href="<?= Url::to(['index', 'filter' => 'overdue']) ?>" class="btn <?= $filter === 'overdue' ? 'btn-danger font-weight-bold' : 'btn-outline-danger' ?> shadow-sm" data-pjax="1">
                    เกินกำหนด
                </a>
            </div>

            <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover table-bordered table-striped mb-0'],
                'layout' => "{summary}\n{items}\n{pager}",
                'pager' => [
                    'options' => ['class' => 'pagination justify-content-center mt-4 mb-2'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['class' => 'page-link text-muted'],
                    'prevPageCssClass' => 'page-item',
                    'nextPageCssClass' => 'page-item',
                    'disabledPageCssClass' => 'disabled',
                    'activePageCssClass' => 'active',
                ],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    
                    [
                        'attribute' => 'purch_no',
                        'label' => 'เลขที่ PO',
                        'format' => 'raw',
                        'value' => function($model) {
                            return Html::a($model->purch_no, ['purch/view', 'id' => $model->id], [
                                'target' => '_blank',
                                'class' => 'fw-bold text-primary',
                                'data-pjax' => '0'
                            ]);
                        }
                    ],
                    [
                        'attribute' => 'vendor_name',
                        'label' => 'ผู้ขาย',
                    ],
                    [
                        'attribute' => 'target_shipment_date',
                        'label' => 'กำหนดรับสินค้า',
                        'format' => 'raw',
                        'value' => function($model) use ($today) {
                            if (empty($model->target_shipment_date)) {
                                return '<span class="text-muted">ไม่ได้ระบุ</span>';
                            }
                            
                            $targetDate = $model->target_shipment_date;
                            $diff = (strtotime($targetDate) - strtotime($today)) / (60 * 60 * 24);
                            
                            $dateStr = Yii::$app->formatter->asDate($targetDate, 'php:d/m/Y');
                            
                            // Check if received completely
                            if ($model->status == \backend\models\Purch::STATUS_COMPLETED) {
                                return '<span class="text-success"><i class="fas fa-check-circle"></i> ' . $dateStr . '</span>';
                            }
                            
                            if ($diff < 0) {
                                return '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> เลยกำหนด: ' . $dateStr . '</span>';
                            } elseif ($diff <= 3) {
                                return '<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> ใกล้ถึงกำหนด: ' . $dateStr . '</span>';
                            } else {
                                return $dateStr;
                            }
                        }
                    ],
                    [
                        'label' => 'สถานะรับของ',
                        'format' => 'raw',
                        'value' => function($model) {
                            if ($model->status == \backend\models\Purch::STATUS_COMPLETED) {
                                return '<span class="badge bg-success">รับครบแล้ว</span>';
                            }
                            $po_remain = \backend\models\Purch::checkPoremain($model->id);
                            if (empty($po_remain)) {
                                return '<span class="badge bg-success">รับครบแล้ว</span>';
                            }
                            return '<span class="badge bg-warning text-dark">รอรับสินค้า</span>';
                        }
                    ],
                    [
                        'label' => 'เวลา (ล่าช้า/คงเหลือ)',
                        'format' => 'raw',
                        'value' => function($model) use ($today) {
                            if (empty($model->target_shipment_date)) return '<span class="text-muted">-</span>';
                            if ($model->status == \backend\models\Purch::STATUS_COMPLETED) {
                                return '<span class="text-success"><i class="fas fa-check"></i> เรียบร้อย</span>';
                            }
                            
                            $diff = floor((strtotime($model->target_shipment_date) - strtotime($today)) / (60 * 60 * 24));
                            if ($diff < 0) {
                                return '<span class="text-danger fw-bold"><i class="fas fa-exclamation-circle"></i> ล่าช้า ' . abs($diff) . ' วัน</span>';
                            } elseif ($diff == 0) {
                                return '<span class="text-warning fw-bold text-dark">กำหนดส่งวันนี้!</span>';
                            } else {
                                return '<span class="text-info">อีก ' . $diff . ' วัน</span>';
                            }
                        }
                    ],
                    [
                        'label' => 'การติดตาม',
                        'format' => 'raw',
                        'value' => function($model) {
                            if ($model->status == \backend\models\Purch::STATUS_COMPLETED) {
                                return '<span class="text-muted">-</span>';
                            }
                            // Placeholder button for follow up since no DB column yet
                            return '<button class="btn btn-xs btn-outline-secondary" onclick="alert(\'ระบบบันทึกการติดตามกำลังอยู่ในระหว่างการพัฒนา\')" title="บันทึกการติดตาม"><i class="fas fa-comment-dots"></i> ยังไม่ติดตาม</button>';
                        }
                    ],
                    [
                        'label' => 'เอกสารแนบ',
                        'format' => 'raw',
                        'value' => function($model) {
                            $docs = $model->getAttachedDocuments();
                            $html = '<div class="d-flex flex-wrap gap-1">';
                            
                            if ($docs['acknowledge']) {
                                $html .= '<span class="badge bg-info" title="มีเอกสารตอบรับ">ตอบรับ</span>';
                            } else {
                                $html .= '<span class="badge border border-info text-info" title="รอเอกสารตอบรับ">ตอบรับ</span>';
                            }
                            
                            if ($docs['invoice']) {
                                $html .= '<span class="badge bg-primary" title="มี Invoice/ใบเสร็จ">IV</span>';
                            } else {
                                $html .= '<span class="badge border border-primary text-primary" title="รอ Invoice/ใบเสร็จ">IV</span>';
                            }
                            
                            if ($docs['vendor_bill']) {
                                $html .= '<span class="badge bg-secondary" title="มีการวางบิล">วางบิล</span>';
                            } else {
                                $html .= '<span class="badge border border-secondary text-secondary" title="รอวางบิล">วางบิล</span>';
                            }
                            
                            $html .= '</div>';
                            return $html;
                        }
                    ],
                    [
                        'label' => 'สถานะจ่ายเงิน',
                        'format' => 'raw',
                        'value' => function($model) {
                            $pvText = $model->getPvStatusText();
                            if (!empty($pvText)) {
                                return $pvText;
                            }
                            
                            $docs = $model->getAttachedDocuments();
                            if ($docs['slip']) {
                                return '<span class="badge bg-success">มี Slip</span>';
                            }
                            
                            return '<span class="badge bg-light text-dark border">ยังไม่จ่าย</span>';
                        }
                    ],
                ],
            ]); ?>
            </div>
            
            <?php Pjax::end(); ?>
        </div>
    </div>
    
    <!-- พื้นที่สำหรับ Dashboard อื่นๆ ของบัญชีในอนาคต -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">กิจกรรมอื่นๆ (รอดำเนินการในอนาคต)</h5>
                </div>
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-tools fa-3x mb-3"></i>
                    <p>พื้นที่สำหรับแสดงผลข้อมูลบัญชีอื่นๆ เช่น การวางบิล, ลูกหนี้, ภาษี ฯลฯ</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.accounting-dashboard-index .badge {
    font-weight: 500;
    padding: 0.4em 0.6em;
    font-size: 11px;
}
.accounting-dashboard-index .gap-1 {
    gap: 4px !important;
}
</style>

<?php
$js = <<<JS
$(function () {
    // === Bar Chart ===
    var barChartCanvas = $('#poBarChart').get(0).getContext('2d');
    var barChartData = {
      labels  : ['ภาพรวมใบสั่งซื้อ (PO)'],
      datasets: [
        {
          label               : 'PO อนุมัติแล้วทั้งหมด',
          backgroundColor     : 'rgba(54, 162, 235, 0.8)',
          borderColor         : 'rgba(54, 162, 235, 1)',
          borderWidth         : 1,
          data                : [{$totalApproved}]
        },
        {
          label               : 'รับเข้าครบแล้ว',
          backgroundColor     : 'rgba(75, 192, 192, 0.8)',
          borderColor         : 'rgba(75, 192, 192, 1)',
          borderWidth         : 1,
          data                : [{$totalReceived}]
        },
        {
          label               : 'รอรับสินค้า (ค้างรับ)',
          backgroundColor     : 'rgba(255, 159, 64, 0.8)',
          borderColor         : 'rgba(255, 159, 64, 1)',
          borderWidth         : 1,
          data                : [{$totalPending}]
        }
      ]
    };
    
    // Check if Chart.js is v2 or v3+
    var isChartJsV3 = typeof Chart.defaults.plugins !== 'undefined';
    
    var barChartOptions = {
      responsive              : true,
      maintainAspectRatio     : false,
      datasetFill             : false
    };
    
    if (isChartJsV3) {
        barChartOptions.scales = {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        };
        barChartOptions.plugins = {
            legend: { position: 'top' }
        };
    } else {
        barChartOptions.scales = {
            yAxes: [{
                ticks: { beginAtZero: true, precision: 0 }
            }]
        };
        barChartOptions.legend = { position: 'top' };
    }

    new Chart(barChartCanvas, {
      type: 'bar',
      data: barChartData,
      options: barChartOptions
    });

    // === Donut Chart ===
    var donutChartCanvas = $('#poDonutChart').get(0).getContext('2d');
    var donutData = {
      labels: ['รับเข้าครบแล้ว', 'รอรับสินค้า'],
      datasets: [
        {
          data: [{$totalReceived}, {$totalPending}],
          backgroundColor : ['#20c997', '#ffc107'],
          hoverBackgroundColor: ['#1aa179', '#d39e00'],
          borderWidth: 0
        }
      ]
    };
    
    var donutOptions = {
      maintainAspectRatio : false,
      responsive : true,
      cutout: isChartJsV3 ? '75%' : 75
    };
    
    if (isChartJsV3) {
        donutOptions.plugins = {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        var label = context.label || '';
                        if (label) { label += ': '; }
                        var value = context.raw;
                        var total = context.chart._metasets[context.datasetIndex].total;
                        var percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                        return label + value + ' ใบ (' + percentage + '%)';
                    }
                }
            }
        };
    } else {
        donutOptions.legend = { position: 'bottom' };
        donutOptions.tooltips = {
            callbacks: {
                label: function(tooltipItem, data) {
                    var label = data.labels[tooltipItem.index] || '';
                    if (label) { label += ': '; }
                    var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                    var total = data.datasets[tooltipItem.datasetIndex].data.reduce((a, b) => a + b, 0);
                    var percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                    return label + value + ' ใบ (' + percentage + '%)';
                }
            }
        };
    }
    
    new Chart(donutChartCanvas, {
      type: 'doughnut',
      data: donutData,
      options: donutOptions
    });
});
JS;
$this->registerJs($js);
?>
