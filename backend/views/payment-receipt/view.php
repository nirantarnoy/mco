<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\PaymentReceipt;

/* @var $this yii\web\View */
/* @var $model backend\models\PaymentReceipt */

$this->title = 'ใบเสร็จรับเงิน: ' . $model->receipt_number;
$this->params['breadcrumbs'][] = ['label' => 'บันทึกการรับเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="payment-receipt-view">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-receipt text-success"></i>
                <?= Html::encode($this->title) ?>
            </h1>
            <p class="text-muted mb-0">
                รายละเอียดการรับเงินจากลูกค้า
                <?php if ($model->billingInvoice && $model->billingInvoice->customer): ?>
                    <strong><?= Html::encode($model->billingInvoice->customer->name) ?></strong>
                <?php endif; ?>
            </p>
        </div>
        <div class="btn-group">
            <?= Html::a(
                '<i class="fas fa-edit"></i> แก้ไข',
                ['update', 'id' => $model->id],
                ['class' => 'btn btn-warning']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-print"></i> พิมพ์',
                ['print', 'id' => $model->id],
                ['class' => 'btn btn-info', 'target' => '_blank']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-list"></i> กลับ',
                ['index'],
                ['class' => 'btn btn-secondary']
            ) ?>
        </div>
    </div>

    <!-- Status Alert -->
    <div class="row mb-4">
        <div class="col-12">
            <?php
            $statusClass = 'info';
            $statusIcon = 'info-circle';
            $statusText = PaymentReceipt::getPaymentStatuses()[$model->payment_status] ?? $model->payment_status;

            if ($model->payment_status === 'full') {
                $statusClass = 'success';
                $statusIcon = 'check-circle';
            } elseif ($model->payment_status === 'partial') {
                $statusClass = 'warning';
                $statusIcon = 'exclamation-triangle';
            } elseif ($model->payment_status === 'overpaid') {
                $statusClass = 'info';
                $statusIcon = 'info-circle';
            }
            ?>

            <div class="alert alert-<?= $statusClass ?> alert-dismissible">
                <h6><i class="fas fa-<?= $statusIcon ?>"></i> สถานะการชำระ: <?= $statusText ?></h6>
                <?php if ($model->remaining_balance > 0): ?>
                    <p class="mb-0">ยอดคงเหลือ: <strong><?= number_format($model->remaining_balance, 2) ?> บาท</strong></p>
                <?php elseif ($model->remaining_balance < 0): ?>
                    <p class="mb-0">รับเงินเกิน: <strong><?= number_format(abs($model->remaining_balance), 2) ?> บาท</strong></p>
                <?php else: ?>
                    <p class="mb-0">ชำระเงินครบถ้วนแล้ว</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Payment Details -->
        <div class="col-lg-8">
            <!-- Payment Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> ข้อมูลการรับเงิน
                    </h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                        'attributes' => [
                            'receipt_number:text:เลขที่ใบเสร็จ',
                            [
                                'attribute' => 'billing_invoice_id',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if ($model->billingInvoice) {
                                        return Html::a(
                                            Html::encode($model->billingInvoice->billing_number),
                                            ['/billing-invoice/view', 'id' => $model->billing_invoice_id],
                                            ['class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank']
                                        );
                                    }
                                    return '-';
                                },
                                'label' => 'ใบแจ้งหนี้',
                            ],
                            [
                                'attribute' => 'job_id',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if ($model->job_id && $model->job) {
                                        return Html::a(
                                            Html::encode($model->job->job_number ?? $model->job_id),
                                            ['/job/view', 'id' => $model->job_id],
                                            ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']
                                        );
                                    }
                                    return $model->job_id ?: '-';
                                },
                                'label' => 'รหัสงาน',
                            ],
                            'payment_date:date:วันที่รับเงิน',
                            [
                                'attribute' => 'payment_method',
                                'format' => 'raw',
                                'value' => function($model) {
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

                                    return '<span class="badge bg-' . $badgeClass . ' badge-lg">' . Html::encode($method) . '</span>';
                                },
                                'label' => 'วิธีการชำระ',
                            ],
                            [
                                'attribute' => 'received_by',
                                'value' => function($model) {
                                    return $model->receivedBy->username ?? $model->received_by;
                                },
                                'label' => 'ผู้รับเงิน',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>

            <!-- Payment Method Details -->
            <?php if ($model->payment_method === 'bank_transfer' || $model->payment_method === 'cheque'): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-credit-card"></i> รายละเอียดการชำระ
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($model->payment_method === 'bank_transfer'): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>ชื่อธนาคาร:</strong><br>
                                    <?= Html::encode($model->bank_name ?: '-') ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>เลขที่บัญชี:</strong><br>
                                    <?= Html::encode($model->account_number ?: '-') ?>
                                </div>
                            </div>
                        <?php elseif ($model->payment_method === 'cheque'): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>เลขที่เช็ค:</strong><br>
                                    <?= Html::encode($model->cheque_number ?: '-') ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>วันที่เช็ค:</strong><br>
                                    <?= $model->cheque_date ? Yii::$app->formatter->asDate($model->cheque_date) : '-' ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Notes -->
            <?php if ($model->notes): ?>
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-sticky-note"></i> หมายเหตุ
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= Html::encode($model->notes) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Attachments -->
            <?php if ($model->attachment_path || $model->paymentAttachments): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-paperclip"></i> ไฟล์แนบ
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($model->attachment_path): ?>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-file text-primary me-2"></i>
                                <?= Html::a(
                                    Html::encode($model->attachment_name ?: basename($model->attachment_path)),
                                    ['download', 'id' => $model->id],
                                    [
                                        'class' => 'text-decoration-none',
                                        'target' => '_blank',
                                        'title' => 'ดาวน์โหลดไฟล์'
                                    ]
                                ) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($model->paymentAttachments): ?>
                            <?php foreach ($model->paymentAttachments as $attachment): ?>
                                <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="<?= $attachment->getFileIcon() ?> me-2"></i>
                                        <div>
                                            <strong><?= Html::encode($attachment->original_name) ?></strong><br>
                                            <small class="text-muted">
                                                <?= $attachment->getFormattedFileSize() ?> •
                                                อัพโหลดโดย <?= Html::encode($attachment->uploadedBy->username ?? 'N/A') ?> •
                                                <?= Yii::$app->formatter->asDatetime($attachment->created_at) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div>
                                        <?= Html::a(
                                            '<i class="fas fa-download"></i>',
                                            $attachment->getDownloadUrl(),
                                            [
                                                'class' => 'btn btn-sm btn-outline-primary',
                                                'title' => 'ดาวน์โหลด',
                                                'target' => '_blank'
                                            ]
                                        ) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column - Amount Summary -->
        <div class="col-lg-4">
            <!-- Amount Summary Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator"></i> สรุปยอดเงิน
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td><strong>จำนวนเงินที่รับ:</strong></td>
                            <td class="text-end">
                                <span class="h6 text-primary"><?= number_format($model->received_amount, 2) ?></span>
                            </td>
                        </tr>
                        <?php if ($model->discount_amount > 0): ?>
                            <tr>
                                <td>ส่วนลด:</td>
                                <td class="text-end text-danger">
                                    -<?= number_format($model->discount_amount, 2) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($model->vat_amount > 0): ?>
                            <tr>
                                <td>ภาษีมูลค่าเพิ่ม:</td>
                                <td class="text-end">
                                    <?= number_format($model->vat_amount, 2) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($model->withholding_tax > 0): ?>
                            <tr>
                                <td>ภาษีหัก ณ ที่จ่าย:</td>
                                <td class="text-end text-warning">
                                    -<?= number_format($model->withholding_tax, 2) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr class="border-top">
                            <td><strong>ยอดสุทธิ:</strong></td>
                            <td class="text-end">
                                <span class="h5 text-success"><?= number_format($model->net_amount, 2) ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Invoice Summary -->
            <?php if ($model->billingInvoice): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-file-invoice"></i> สรุปใบแจ้งหนี้
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td>ยอดรวมใบแจ้งหนี้:</td>
                                <td class="text-end">
                                    <?= number_format($model->billingInvoice->total_amount, 2) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>ยอดที่ชำระแล้ว:</td>
                                <td class="text-end text-success">
                                    <?php
                                    $totalPaid = \backend\models\PaymentReceipt::find()
                                        ->where(['billing_invoice_id' => $model->billing_invoice_id])
                                        ->sum('net_amount') ?? 0;
                                    echo number_format($totalPaid, 2);
                                    ?>
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>ยอดคงเหลือ:</strong></td>
                                <td class="text-end">
                                    <strong class="<?= $model->remaining_balance > 0 ? 'text-warning' : 'text-success' ?>">
                                        <?= number_format($model->remaining_balance, 2) ?>
                                    </strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs"></i> การจัดการ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?= Html::a(
                            '<i class="fas fa-edit"></i> แก้ไขข้อมูล',
                            ['update', 'id' => $model->id],
                            ['class' => 'btn btn-warning']
                        ) ?>

                        <?= Html::a(
                            '<i class="fas fa-print"></i> พิมพ์ใบเสร็จ',
                            ['print', 'id' => $model->id],
                            ['class' => 'btn btn-info', 'target' => '_blank']
                        ) ?>

                        <?= Html::a(
                            '<i class="fas fa-plus"></i> บันทึกการรับเงินใหม่',
                            ['create', 'invoice_id' => $model->billing_invoice_id],
                            ['class' => 'btn btn-success']
                        ) ?>

                        <?= Html::a(
                            '<i class="fas fa-trash"></i> ลบรายการ',
                            ['delete', 'id' => $model->id],
                            [
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                                    'method' => 'post',
                                ],
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-info"></i> ข้อมูลระบบ
                    </h6>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-sm table-bordered'],
                        'attributes' => [
                            [
                                'attribute' => 'created_by',
                                'value' => function($model) {
                                    return $model->createdBy->username ?? $model->created_by;
                                },
                                'label' => 'สร้างโดย',
                            ],
                            'created_at:datetime:วันที่สร้าง',
                            [
                                'attribute' => 'updated_by',
                                'value' => function($model) {
                                    return $model->updatedBy->username ?? ($model->updated_by ?: '-');
                                },
                                'label' => 'แก้ไขโดย',
                            ],
                            'updated_at:datetime:วันที่แก้ไข',
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return $model->status
                                        ? '<span class="badge bg-success">ใช้งาน</span>'
                                        : '<span class="badge bg-secondary">ไม่ใช้งาน</span>';
                                },
                                'label' => 'สถานะระบบ',
                            ],
                        ],
                    ]) ?>
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

    .detail-view th {
        background-color: #f8f9fa;
        font-weight: 600;
        width: 30%;
    }

    .badge-lg {
        font-size: 0.9em;
        padding: 0.5em 0.75em;
    }

    .table-borderless td {
        border: none !important;
        padding: 0.25rem 0.5rem;
    }

    @media (max-width: 768px) {
        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            margin-bottom: 0.5rem;
        }
    }
</style>