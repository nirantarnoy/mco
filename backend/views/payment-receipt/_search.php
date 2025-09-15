<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use backend\models\PaymentReceipt;
use backend\models\BillingInvoice;

/* @var $this yii\web\View */
/* @var $model backend\models\PaymentReceiptSearch */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="payment-receipt-search">

        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => [
                'data-pjax' => 1,
                'class' => 'row g-3'
            ],
        ]); ?>

        <div class="col-md-3">
            <?= $form->field($model, 'receipt_number')->textInput([
                'placeholder' => 'เลขที่ใบเสร็จ...',
                'class' => 'form-control'
            ])->label('เลขที่ใบเสร็จ') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'billing_invoice_id')->widget(Select2::class, [
                'data' => ArrayHelper::map(
                    BillingInvoice::find()
                        ->with(['customer'])
                        ->orderBy(['created_at' => SORT_DESC])
                        ->limit(200)
                        ->all(),
                    'id',
                    function($model) {
                        return $model->billing_number . ' - ' . ($model->customer->name ?? 'N/A');
                    }
                ),
                'options' => [
                    'placeholder' => 'เลือกใบแจ้งหนี้...',
                    'encode' => false,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
                ],
            ])->label('ใบแจ้งหนี้') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'payment_method')->widget(Select2::class, [
                'data' => PaymentReceipt::getPaymentMethods(),
                'options' => [
                    'placeholder' => 'วิธีการชำระ...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label('วิธีการชำระ') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'payment_status')->widget(Select2::class, [
                'data' => PaymentReceipt::getPaymentStatuses(),
                'options' => [
                    'placeholder' => 'สถานะการชำระ...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label('สถานะการชำระ') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'payment_date_from')->widget(DatePicker::class, [
                'options' => [
                    'placeholder' => 'วันที่เริ่มต้น...',
                ],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                    'clearBtn' => true,
                ]
            ])->label('วันที่รับเงิน (จาก)') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'payment_date_to')->widget(DatePicker::class, [
                'options' => [
                    'placeholder' => 'วันที่สิ้นสุด...',
                ],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                    'clearBtn' => true,
                ]
            ])->label('วันที่รับเงิน (ถึง)') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'amount_from')->textInput([
                'type' => 'number',
                'step' => '0.01',
                'placeholder' => 'จำนวนเงินต่ำสุด...',
                'class' => 'form-control'
            ])->label('จำนวนเงิน (จาก)') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'amount_to')->textInput([
                'type' => 'number',
                'step' => '0.01',
                'placeholder' => 'จำนวนเงินสูงสุด...',
                'class' => 'form-control'
            ])->label('จำนวนเงิน (ถึง)') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'bank_name')->textInput([
                'placeholder' => 'ชื่อธนาคาร...',
                'class' => 'form-control'
            ])->label('ธนาคาร') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'cheque_number')->textInput([
                'placeholder' => 'เลขที่เช็ค...',
                'class' => 'form-control'
            ])->label('เลขที่เช็ค') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'job_id')->textInput([
                'placeholder' => 'รหัสงาน...',
                'class' => 'form-control'
            ])->label('รหัสงาน') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'has_attachment')->widget(Select2::class, [
                'data' => [
                    '' => 'ทั้งหมด',
                    '1' => 'มีไฟล์แนบ',
                    '0' => 'ไม่มีไฟล์แนบ',
                ],
                'options' => [
                    'placeholder' => 'ไฟล์แนบ...',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label('ไฟล์แนบ') ?>
        </div>

        <!-- Action Buttons -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group" role="group">
                    <?= Html::submitButton(
                        '<i class="fas fa-search"></i> ค้นหา',
                        ['class' => 'btn btn-primary']
                    ) ?>

                    <?= Html::a(
                        '<i class="fas fa-undo"></i> ล้างค่า',
                        ['index'],
                        ['class' => 'btn btn-outline-secondary']
                    ) ?>

                    <?= Html::button(
                        '<i class="fas fa-filter"></i> ตัวกรองขั้นสูง',
                        [
                            'class' => 'btn btn-outline-info',
                            'id' => 'advanced-filter-btn',
                            'data-bs-toggle' => 'collapse',
                            'data-bs-target' => '#advanced-filters'
                        ]
                    ) ?>
                </div>

                <div class="text-muted">
                    <small>
                        <i class="fas fa-info-circle"></i>
                        ใส่เงื่อนไขการค้นหาแล้วกดปุ่มค้นหา
                    </small>
                </div>
            </div>
        </div>

        <!-- Advanced Filters (Collapsible) -->
        <div class="col-12">
            <div class="collapse mt-3" id="advanced-filters">
                <div class="card bg-light">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cogs"></i> ตัวกรองขั้นสูง
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <?= $form->field($model, 'received_by')->widget(Select2::class, [
                                    'data' => ArrayHelper::map(
                                        \common\models\User::find()->all(),
                                        'id',
                                        'username'
                                    ),
                                    'options' => [
                                        'placeholder' => 'ผู้รับเงิน...',
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ])->label('ผู้รับเงิน') ?>
                            </div>

                            <div class="col-md-4">
                                <?= $form->field($model, 'created_date_from')->widget(DatePicker::class, [
                                    'options' => [
                                        'placeholder' => 'วันที่สร้างเริ่มต้น...',
                                    ],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                        'todayHighlight' => true,
                                        'clearBtn' => true,
                                    ]
                                ])->label('วันที่สร้าง (จาก)') ?>
                            </div>

                            <div class="col-md-4">
                                <?= $form->field($model, 'created_date_to')->widget(DatePicker::class, [
                                    'options' => [
                                        'placeholder' => 'วันที่สร้างสิ้นสุด...',
                                    ],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                        'todayHighlight' => true,
                                        'clearBtn' => true,
                                    ]
                                ])->label('วันที่สร้าง (ถึง)') ?>
                            </div>

                            <div class="col-md-6">
                                <?= $form->field($model, 'notes')->textInput([
                                    'placeholder' => 'ค้นหาในหมายเหตุ...',
                                    'class' => 'form-control'
                                ])->label('หมายเหตุ') ?>
                            </div>

                            <div class="col-md-6">
                                <?= $form->field($model, 'status')->widget(Select2::class, [
                                    'data' => [
                                        '' => 'ทั้งหมด',
                                        '1' => 'ใช้งาน',
                                        '0' => 'ไม่ใช้งาน',
                                    ],
                                    'options' => [
                                        'placeholder' => 'สถานะระบบ...',
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ])->label('สถานะระบบ') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Filter Buttons -->
        <div class="col-12">
            <div class="mt-3">
                <div class="card bg-info bg-opacity-10 border-info">
                    <div class="card-body py-2">
                        <h6 class="mb-2" style="color: black">
                            <i class="fas fa-bolt"></i> ตัวกรองด่วน
                        </h6>
                        <div class="btn-group flex-wrap" role="group">
                            <?= Html::a(
                                '<i class="fas fa-calendar-day"></i> วันนี้',
                                ['index', 'PaymentReceiptSearch[payment_date_from]' => date('Y-m-d'), 'PaymentReceiptSearch[payment_date_to]' => date('Y-m-d')],
                                ['class' => 'btn btn-sm btn-outline-primary me-2 mb-1']
                            ) ?>

                            <?= Html::a(
                                '<i class="fas fa-calendar-week"></i> สัปดาห์นี้',
                                ['index', 'PaymentReceiptSearch[payment_date_from]' => date('Y-m-d', strtotime('monday this week')), 'PaymentReceiptSearch[payment_date_to]' => date('Y-m-d')],
                                ['class' => 'btn btn-sm btn-outline-primary me-2 mb-1']
                            ) ?>

                            <?= Html::a(
                                '<i class="fas fa-calendar-alt"></i> เดือนนี้',
                                ['index', 'PaymentReceiptSearch[payment_date_from]' => date('Y-m-01'), 'PaymentReceiptSearch[payment_date_to]' => date('Y-m-t')],
                                ['class' => 'btn btn-sm btn-outline-primary me-2 mb-1']
                            ) ?>

                            <?= Html::a(
                                '<i class="fas fa-money-bill-wave"></i> เงินสด',
                                ['index', 'PaymentReceiptSearch[payment_method]' => 'cash'],
                                ['class' => 'btn btn-sm btn-outline-success me-2 mb-1']
                            ) ?>

                            <?= Html::a(
                                '<i class="fas fa-university"></i> โอนเงิน',
                                ['index', 'PaymentReceiptSearch[payment_method]' => 'bank_transfer'],
                                ['class' => 'btn btn-sm btn-outline-info me-2 mb-1']
                            ) ?>

                            <?= Html::a(
                                '<i class="fas fa-check-circle"></i> ชำระครบ',
                                ['index', 'PaymentReceiptSearch[payment_status]' => 'full'],
                                ['class' => 'btn btn-sm btn-outline-success me-2 mb-1']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

    <style>
        .payment-receipt-search .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
        }

        .payment-receipt-search .select2-container {
            width: 100% !important;
        }

        .btn-group .btn {
            white-space: nowrap;
        }

        .card.bg-info.bg-opacity-10 {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        .btn-group.flex-wrap {
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .payment-receipt-search .row.g-3 > .col-md-3,
            .payment-receipt-search .row.g-3 > .col-md-4,
            .payment-receipt-search .row.g-3 > .col-md-6 {
                margin-bottom: 1rem;
            }

            .btn-group {
                flex-direction: column;
                width: 100%;
            }

            .btn-group .btn {
                margin-bottom: 0.5rem;
                width: 100%;
            }
        }

        /* Animation for collapsible */
        .collapse {
            transition: height 0.35s ease;
        }

        /* Custom styling for advanced filter card */
        .card.bg-light .card-header {
            background-color: #e9ecef !important;
            border-bottom: 1px solid #dee2e6;
        }

        /* Focus states */
        .form-control:focus,
        .select2-container--default .select2-selection--single:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
    </style>

<?php
$this->registerJs("
// Handle advanced filter toggle
$('#advanced-filter-btn').on('click', function() {
    var icon = $(this).find('i');
    var isExpanded = $('#advanced-filters').hasClass('show');
    
    if (isExpanded) {
        icon.removeClass('fa-filter').addClass('fa-filter');
        $(this).html('<i class=\"fas fa-filter\"></i> ตัวกรองขั้นสูง');
    } else {
        icon.removeClass('fa-filter').addClass('fa-times');
        $(this).html('<i class=\"fas fa-times\"></i> ปิดตัวกรอง');
    }
});

// Auto-submit form on select changes (with debounce)
var searchTimeout;
$('.payment-receipt-search select, .payment-receipt-search input[type=\"date\"]').on('change', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        $('.payment-receipt-search form').submit();
    }, 500);
});

// Handle quick filter buttons
$('.btn-group a').on('click', function(e) {
    // Add loading state
    var btn = $(this);
    var originalText = btn.html();
    btn.html('<i class=\"fas fa-spinner fa-spin\"></i> กำลังโหลด...');
    btn.addClass('disabled');
    
    // Allow the link to proceed normally
    setTimeout(function() {
        btn.html(originalText);
        btn.removeClass('disabled');
    }, 2000);
});
");
?>