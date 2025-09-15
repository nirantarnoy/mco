<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\export\ExportMenu;
use backend\models\PaymentReceipt;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PaymentReceiptSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'บันทึกการรับเงิน';
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="payment-receipt-index">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">
                    <i class="fas fa-receipt text-primary"></i>
                    <?= Html::encode($this->title) ?>
                </h1>
                <p class="text-muted mb-0">จัดการข้อมูลการรับเงินจากลูกค้า</p>
            </div>
            <div>
                <?= Html::a(
                    '<i class="fas fa-plus"></i> บันทึกการรับเงิน',
                    ['create'],
                    ['class' => 'btn btn-success btn-lg']
                ) ?>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-1">รับเงินวันนี้</h6>
                                <h4 class="mb-0">
                                    <?php
                                    $todayAmount = PaymentReceipt::find()
                                        ->where(['payment_date' => date('Y-m-d')])
                                        ->sum('net_amount') ?? 0;
                                    echo number_format($todayAmount, 2);
                                    ?>
                                </h4>
                                <small>บาท</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-1">รับเงินเดือนนี้</h6>
                                <h4 class="mb-0">
                                    <?php
                                    $monthAmount = PaymentReceipt::find()
                                        ->where(['>=', 'payment_date', date('Y-m-01')])
                                        ->where(['<=', 'payment_date', date('Y-m-t')])
                                        ->sum('net_amount') ?? 0;
                                    echo number_format($monthAmount, 2);
                                    ?>
                                </h4>
                                <small>บาท</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-1">จำนวนรายการ</h6>
                                <h4 class="mb-0">
                                    <?= PaymentReceipt::find()->count() ?>
                                </h4>
                                <small>รายการ</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-list fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title mb-1">ค้างรับ</h6>
                                <h4 class="mb-0">
                                    <?php
                                    $pendingAmount = \backend\models\BillingInvoice::find()
                                        ->where(['status' => ['pending', 'partial_paid']])
                                        ->sum('total_amount') ?? 0;
                                    echo number_format($pendingAmount, 2);
                                    ?>
                                </h4>
                                <small>บาท</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-search"></i> ค้นหาและกรอง
                </h6>
            </div>
            <div class="card-body">
                <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            </div>
        </div>

        <!-- Data Grid -->
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-table"></i> รายการบันทึกการรับเงิน
                    </h6>
                    <div>
                        <?= ExportMenu::widget([
                            'dataProvider' => $dataProvider,
                            'columns' => [
                                'receipt_number',
                                'payment_date',
                                [
                                    'attribute' => 'billing_invoice_id',
                                    'value' => 'billingInvoice.billing_number',
                                ],
                                [
                                    'attribute' => 'payment_method',
                                    'value' => function($model) {
                                        return PaymentReceipt::getPaymentMethods()[$model->payment_method] ?? $model->payment_method;
                                    }
                                ],
                                'received_amount',
                                'net_amount',
                                [
                                    'attribute' => 'payment_status',
                                    'value' => function($model) {
                                        return PaymentReceipt::getPaymentStatuses()[$model->payment_status] ?? $model->payment_status;
                                    }
                                ],
                            ],
                            'fontAwesome' => true,
                            'dropdownOptions' => [
                                'label' => 'ส่งออก',
                                'class' => 'btn btn-outline-secondary btn-sm'
                            ],
                            'exportConfig' => [
                                ExportMenu::FORMAT_TEXT => false,
                                ExportMenu::FORMAT_PDF => false,
                                ExportMenu::FORMAT_HTML => false,
                            ],
                        ]); ?>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <?php Pjax::begin(['id' => 'payment-receipt-pjax']); ?>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'tableOptions' => ['class' => 'table table-striped table-hover mb-0'],
                    'headerRowOptions' => ['class' => 'bg-light'],
                    'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center p-3'>{summary}{pager}</div>",
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'headerOptions' => ['style' => 'width: 50px;'],
                        ],

                        [
                            'attribute' => 'receipt_number',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return Html::a(
                                    '<strong>' . Html::encode($model->receipt_number) . '</strong>',
                                    ['view', 'id' => $model->id],
                                    ['class' => 'text-decoration-none']
                                );
                            },
                            'headerOptions' => ['style' => 'width: 140px;'],
                        ],

                        [
                            'attribute' => 'payment_date',
                            'format' => 'date',
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],

                        [
                            'attribute' => 'billing_invoice_id',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if ($model->billingInvoice) {
                                    return Html::a(
                                        Html::encode($model->billingInvoice->billing_number),
                                        ['/billing-invoice/view', 'id' => $model->billing_invoice_id],
                                        ['class' => 'text-primary', 'target' => '_blank']
                                    );
                                }
                                return '-';
                            },
                            'label' => 'ใบแจ้งหนี้',
                            'headerOptions' => ['style' => 'width: 140px;'],
                        ],

                        [
                            'attribute' => 'payment_method',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $methods = PaymentReceipt::getPaymentMethods();
                                $method = $methods[$model->payment_method] ?? $model->payment_method;

                                $badges = [
                                    'cash' => 'success',
                                    'bank_transfer' => 'primary',
                                    'cheque' => 'warning',
                                    'credit_card' => 'info',
                                    'other' => 'secondary',
                                ];

                                $badgeClass = $badges[$model->payment_method] ?? 'secondary';

                                return '<span class="badge bg-' . $badgeClass . '">' . Html::encode($method) . '</span>';
                            },
                            'filter' => PaymentReceipt::getPaymentMethods(),
                            'headerOptions' => ['style' => 'width: 120px;'],
                        ],

                        [
                            'attribute' => 'received_amount',
                            'format' => ['decimal', 2],
                            'headerOptions' => ['style' => 'width: 120px; text-align: right;'],
                            'contentOptions' => ['style' => 'text-align: right;'],
                        ],

                        [
                            'attribute' => 'net_amount',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return '<strong class="text-success">' . number_format($model->net_amount, 2) . '</strong>';
                            },
                            'headerOptions' => ['style' => 'width: 120px; text-align: right;'],
                            'contentOptions' => ['style' => 'text-align: right;'],
                        ],

                        [
                            'attribute' => 'payment_status',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $statuses = PaymentReceipt::getPaymentStatuses();
                                $status = $statuses[$model->payment_status] ?? $model->payment_status;

                                $badges = [
                                    'partial' => 'warning',
                                    'full' => 'success',
                                    'overpaid' => 'info',
                                ];

                                $badgeClass = $badges[$model->payment_status] ?? 'secondary';

                                return '<span class="badge bg-' . $badgeClass . '">' . Html::encode($status) . '</span>';
                            },
                            'filter' => PaymentReceipt::getPaymentStatuses(),
                            'headerOptions' => ['style' => 'width: 100px;'],
                        ],

                        [
                            'attribute' => 'attachment_path',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if ($model->attachment_path) {
                                    return Html::a(
                                        '<i class="fas fa-paperclip text-primary"></i>',
                                        ['download', 'id' => $model->id],
                                        [
                                            'title' => 'ดาวน์โหลดไฟล์แนบ',
                                            'target' => '_blank'
                                        ]
                                    );
                                }
                                return '-';
                            },
                            'label' => 'ไฟล์แนบ',
                            'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                            'filter' => false,
                        ],

                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view} {update} {print} {delete}',
                            'buttons' => [
                                'view' => function ($url, $model, $key) {
                                    return Html::a(
                                        '<i class="fas fa-eye"></i>',
                                        $url,
                                        [
                                            'title' => 'ดูรายละเอียด',
                                            'class' => 'btn btn-sm btn-outline-info me-1',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) {
                                    return Html::a(
                                        '<i class="fas fa-edit"></i>',
                                        $url,
                                        [
                                            'title' => 'แก้ไข',
                                            'class' => 'btn btn-sm btn-outline-warning me-1',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    );
                                },
                                'print' => function ($url, $model, $key) {
                                    return Html::a(
                                        '<i class="fas fa-print"></i>',
                                        ['print', 'id' => $model->id],
                                        [
                                            'title' => 'พิมพ์ใบเสร็จ',
                                            'class' => 'btn btn-sm btn-outline-secondary me-1',
                                            'target' => '_blank',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    );
                                },
                                'delete' => function ($url, $model, $key) {
                                    return Html::a(
                                        '<i class="fas fa-trash"></i>',
                                        $url,
                                        [
                                            'title' => 'ลบ',
                                            'class' => 'btn btn-sm btn-outline-danger',
                                            'data-bs-toggle' => 'tooltip',
                                            'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                                            'data-method' => 'post'
                                        ]
                                    );
                                },
                            ],
                            'headerOptions' => ['style' => 'width: 160px; text-align: center;'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                        ],
                    ],
                    'pager' => [
                        'class' => 'yii\bootstrap4\LinkPager',
                        'options' => ['class' => 'pagination justify-content-center mb-0'],
                    ],
                ]); ?>

                <?php Pjax::end(); ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-bolt"></i> การดำเนินการด่วน
                        </h6>
                        <div class="btn-group" role="group">
                            <?= Html::a(
                                '<i class="fas fa-file-invoice"></i> ใบแจ้งหนี้ค้างชำระ',
                                ['/billing-invoice/index', 'BillingInvoiceSearch[status]' => 'pending'],
                                ['class' => 'btn btn-outline-warning']
                            ) ?>

                            <?= Html::a(
                                '<i class="fas fa-chart-line"></i> รายงานการรับเงิน',
                                ['/reports/payment-summary'],
                                ['class' => 'btn btn-outline-info']
                            ) ?>

                            <?= Html::a(
                                '<i class="fas fa-download"></i> ส่งออกข้อมูล',
                                ['export'],
                                ['class' => 'btn btn-outline-success']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .opacity-75 {
            opacity: 0.75;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .badge {
            font-size: 0.75em;
            font-weight: 500;
        }

        .btn-group .btn {
            margin-right: 0.5rem;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }

            .btn-lg {
                width: 100%;
            }

            .card-body .btn-group {
                flex-direction: column;
            }

            .card-body .btn-group .btn {
                margin-bottom: 0.5rem;
                margin-right: 0;
            }
        }

        /* Custom scrollbar for table */
        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: #6c757d #f8f9fa;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f8f9fa;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background-color: #6c757d;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background-color: #495057;
        }

        /* Animation for cards */
        .card {
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        /* Tooltip styling */
        .tooltip {
            font-size: 0.875rem;
        }

        /* Loading state */
        .grid-view-loading {
            position: relative;
        }

        .grid-view-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            z-index: 1000;
        }
    </style>

<?php
$this->registerJs("
// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle=\"tooltip\"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Auto refresh every 5 minutes
setInterval(function() {
    $.pjax.reload({container: '#payment-receipt-pjax'});
}, 300000);

// Handle filter changes
$('.grid-view').on('change', 'select, input', function() {
    var form = $(this).closest('form');
    setTimeout(function() {
        form.submit();
    }, 500);
});
");
?>