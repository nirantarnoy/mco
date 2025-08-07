<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use backend\models\Quotation;
use yii\data\ActiveDataProvider;

/* @var $this yii\web\View */
/* @var $model backend\models\Quotation */

$this->title = 'ใบเสนอราคา: ' . $model->quotation_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบเสนอราคา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="quotation-view">
    <!-- Flash Messages -->
    <?php if (\Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= \Yii::$app->session->getFlash('warning') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('info') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'target' => '_blank'
            ]) ?>
            <?= Html::a('<i class="fas fa-file-pdf"></i> PDF', ['pdf', 'id' => $model->id], [
                'class' => 'btn btn-warning',
                'target' => '_blank'
            ]) ?>

            <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?php if ($model->approve_status == Quotation::APPROVE_STATUS_PENDING): ?>
                <?= Html::a('อนุมัติ', ['approve', 'id' => $model->id], [
                    'class' => 'btn btn-success',
                    'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบเสนอราคานี้?',
                    'data-method' => 'post',
                ]) ?>
                <?= Html::a('ไม่อนุมัติ', ['reject', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะไม่อนุมัติใบเสนอราคานี้?',
                    'data-method' => 'post',
                ]) ?>
            <?php endif; ?>
            <?php if ($model->approve_status != Quotation::APPROVE_STATUS_APPROVED && $model->approve_status != Quotation::APPROVE_STATUS_REJECTED && \Yii::$app->user->can('quotation/delete')): ?>
                <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบใบเสนอราคานี้?',
                    'data-method' => 'post',
                ]) ?>
            <?php endif; ?>
            <?= Html::a('กลับ', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">ข้อมูลใบเสนอราคา</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'quotation_no:text:เลขที่ใบเสนอราคา',
                            [
                                'attribute' => 'quotation_date',
                                'label' => 'วันที่',
                                'format' => ['date', 'php:d/m/Y'],
                            ],
                            'customer_name:text:ชื่อลูกค้า',
                            [
                                'attribute' => 'status',
                                'label' => 'สถานะเอกสาร',
                                'value' => $model->getStatusLabel(),
                            ],
                            [
                                'attribute' => 'approve_status',
                                'label' => 'สถานะอนุมัติ',
                                'format' => 'raw',
                                'value' => $model->getApproveStatusBadge(),
                            ],
                            [
                                'attribute' => 'total_amount',
                                'label' => 'ยอดรวม',
                                'format' => ['currency', 'THB'],
                            ],
                            [
                                'attribute' => 'payment_term_id',
                                'value' => function ($model) {
                                    return \backend\models\Paymentterm::findName($model->payment_term_id);
                                }
                            ],
                            'note:ntext:หมายเหตุ',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">ข้อมูลการสร้าง</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'created_at',
                                'label' => 'วันที่สร้าง',
                                'format' => ['datetime', 'php:d/m/Y H:i'],
                            ],
                            'created_by:text:สร้างโดย',
                            [
                                'attribute' => 'updated_at',
                                'label' => 'วันที่แก้ไข',
                                'format' => ['datetime', 'php:d/m/Y H:i'],
                            ],
                            'updated_by:text:แก้ไขโดย',
                            [
                                'attribute' => 'approve_by',
                                'label' => 'อนุมัติโดย',
                                'value' => $model->getApproveByName(),
                            ],
                        ],
                    ]) ?>
                </div>
            </div>

            <?php if (!empty($model->total_amount_text)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">จำนวนเงิน (ตัวอักษร)</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= Html::encode($model->total_amount_text) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">รายการสินค้า</h5>
        </div>
        <div class="card-body">
            <?php
            $quotationLineDataProvider = new ActiveDataProvider([
                'query' => $model->getQuotationLines(),
                'pagination' => false,
            ]);
            ?>

            <?= GridView::widget([
                'dataProvider' => $quotationLineDataProvider,
                'layout' => '{items}',
                'bordered' => true,
                'striped' => true,
                'condensed' => true,
                'responsive' => true,
                'hover' => true,
                'showPageSummary' => true,
                'pageSummaryRowOptions' => ['class' => 'kv-page-summary warning'],
                'columns' => [
                    [
                        'class' => 'kartik\grid\SerialColumn',
                        'header' => '#',
                        'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],
                    [
                        'attribute' => 'product_name',
                        'label' => 'รายการ/สินค้า',
                        'headerOptions' => ['style' => 'width: 300px;'],
                    ],
                    [
                        'attribute' => 'qty',
                        'label' => 'จำนวน',
                        'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'format' => ['decimal', 2],
                        'pageSummary' => true,
                        'pageSummaryFunc' => GridView::F_SUM,
                        'pageSummaryOptions' => ['style' => 'text-align: center;'],
                    ],
                    [
                        'attribute' => 'line_price',
                        'label' => 'ราคา/หน่วย',
                        'headerOptions' => ['style' => 'width: 120px; text-align: right;'],
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'format' => ['currency', 'THB'],

                    ],
                    [
                        'attribute' => 'discount_amount',
                        'label' => 'ส่วนลด',
                        'headerOptions' => ['style' => 'width: 100px; text-align: right;'],
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'format' => ['currency', 'THB'],
                        'pageSummary' => true,
                        'pageSummaryFunc' => GridView::F_SUM,
                        'pageSummaryOptions' => ['style' => 'text-align: right;'],
                    ],
                    [
                        'attribute' => 'line_total',
                        'label' => 'รวมเงิน',
                        'headerOptions' => ['style' => 'width: 120px; text-align: right;'],
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'format' => ['currency', 'THB'],
                        'pageSummary' => true,
                        'pageSummaryFunc' => GridView::F_SUM,
                        'pageSummaryOptions' => ['style' => 'text-align: right;'],
                    ],
                    [
                        'attribute' => 'note',
                        'label' => 'หมายเหตุ',
                        'headerOptions' => ['style' => 'width: 150px;'],
                    ],
                ],
            ]); ?>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">สรุปยอดเงิน</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-8">ยอดรวม:</div>
                                <div class="col-4 text-end">
                                    <span class="fw-bold"><?= Yii::$app->formatter->asCurrency($model->total_amount, 'THB') ?></span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-8">VAT (7%):</div>
                                <div class="col-4 text-end">
                                    <span class="fw-bold"><?= Yii::$app->formatter->asCurrency($model->total_amount * 0.07, 'THB') ?></span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-8"><strong>ยอดรวมทั้งสิ้น:</strong></div>
                                <div class="col-4 text-end">
                                    <span class="fw-bold text-primary h5"><?= Yii::$app->formatter->asCurrency($model->total_amount * 1.07, 'THB') ?></span>
                                </div>
                            </div>
                        </div>
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

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .detail-view th {
        background-color: #f8f9fa;
        width: 30%;
    }

    .badge {
        font-size: 12px;
        padding: 0.4em 0.8em;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>


<?php
$this->registerJs("
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
");
?>