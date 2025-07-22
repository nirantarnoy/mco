<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use backend\models\PurchReq;
use yii\data\ActiveDataProvider;

/* @var $this yii\web\View */
/* @var $model backend\models\PurchReq */

$this->title = 'ใบขอซื้อ: ' . $model->purch_req_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบขอซื้อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
    <div class="purch-req-view">
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
                <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print-pr', 'id' => $model->id], [
                    'class' => 'btn btn-info',
                    'target' => '_blank'
                ]) ?>
                <?= Html::a('<i class="fas fa-file-pdf"></i> PDF', ['pdf', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'target' => '_blank'
                ]) ?>

                <?php if ($model->approve_status == PurchReq::APPROVE_STATUS_APPROVED && !$model->purch_id): ?>
                    <?php if (\Yii::$app->user->can('CanConvertPo')): ?>
                        <?= Html::a('<i class="fas fa-exchange-alt"></i> แปลงเป็นใบสั่งซื้อ', ['convert-to-purchase-order', 'id' => $model->id], [
                            'class' => 'btn btn-success',
                            'data-confirm' => 'คุณต้องการแปลงใบขอซื้อนี้เป็นใบสั่งซื้อหรือไม่?',
                            'data-method' => 'post',
                        ]) ?>
                    <?php endif; ?>
                <?php elseif ($model->purch_id): ?>
                    <?= Html::a('<i class="fas fa-external-link-alt"></i> ดูใบสั่งซื้อ', ['/purch/view', 'id' => $model->purch_id], [
                        'class' => 'btn btn-success',
                        'target' => '_blank'
                    ]) ?>
                <?php endif; ?>

                <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?php if ($model->approve_status == PurchReq::APPROVE_STATUS_PENDING): ?>
                    <?php if (\Yii::$app->user->can('CanApprovePr')): ?>
                        <?= Html::a('อนุมัติ', ['approve', 'id' => $model->id], [
                            'class' => 'btn btn-success',
                            'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบขอซื้อนี้?',
                            'data-method' => 'post',
                        ]) ?>
                        <?= Html::a('ไม่อนุมัติ', ['reject', 'id' => $model->id], [
                            'class' => 'btn btn-warning',
                            'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะไม่อนุมัติใบขอซื้อนี้?',
                            'data-method' => 'post',
                        ]) ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($model->purch_id == null || $model->purch_id == ''): ?>
                    <?php if (\Yii::$app->user->can('purchreq/delete')): ?>
                        <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบใบขอซื้อนี้?',
                            'data-method' => 'post',
                        ]) ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?= Html::a('กลับ', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">ข้อมูลใบขอซื้อ</h5>
                    </div>
                    <div class="card-body">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'purch_req_no:text:เลขที่ใบขอซื้อ',
                                [
                                    'attribute' => 'purch_req_date',
                                    'label' => 'วันที่',
                                    'format' => ['date', 'php:m/d/Y'],
                                ],
                                'vendor_name:text:ชื่อผู้ขาย',
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
                                    'attribute' => 'approve_by',
                                    'value' => function ($model) {
                                        return \backend\models\User::findEmployeeNameByUserId($model->approve_by);
                                    }
                                ],
                                [
                                    'attribute' => 'total_amount',
                                    'label' => 'ยอดรวม',
                                    'format' => ['currency', 'THB'],
                                ],
                                [
                                    'attribute' => 'discount_amount',
                                    'label' => 'ส่วนลด',
                                    'format' => ['currency', 'THB'],
                                ],
                                [
                                    'attribute' => 'vat_amount',
                                    'label' => 'VAT',
                                    'format' => ['currency', 'THB'],
                                ],
                                [
                                    'attribute' => 'net_amount',
                                    'label' => 'ยอดรวมสุทธิ',
                                    'format' => ['currency', 'THB'],
                                ],
                                'note:ntext:หมายเหตุ',
                                'purch_id:text:รหัสใบสั่งซื้อ',
                                [
                                    'attribute' => 'purch_id',
                                    'label' => 'สถานะใบสั่งซื้อ',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        if ($model->purch_id) {
                                            return '<span class="badge bg-success">แปลงแล้ว</span> ' .
                                                Html::a('ดูใบสั่งซื้อ', ['/purch/view', 'id' => $model->purch_id], [
                                                    'class' => 'btn btn-xs btn-outline-primary',
                                                    'target' => '_blank'
                                                ]);
                                        } else {
                                            return '<span class="badge bg-secondary">ยังไม่แปลง</span>';
                                        }
                                    },
                                ],
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
                                    'format' => ['datetime', 'php:m/d/Y H:i'],
                                ],
                                [
                                        'attribute' => 'created_by',
                                    'label' => 'สร้างโดย',
                                    'value' => function ($model) {
                                       return \backend\models\User::findEmployeeNameByUserId($model->created_by);
                                    }
                                ],
                                [
                                    'attribute' => 'updated_at',
                                    'label' => 'วันที่แก้ไข',
                                    'format' => ['datetime', 'php:m/d/Y H:i'],
                                ],
                                [
                                    'attribute' => 'updated_by',
                                    'label' => 'แก้ไขโดย',
                                    'value' => function ($model) {
                                        return \backend\models\User::findEmployeeNameByUserId($model->updated_by);
                                    }
                                ],
                            ],
                        ]) ?>
                    </div>
                </div>

                <?php if (!empty($model->total_text)): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">จำนวนเงิน (ตัวอักษร)</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= Html::encode($model->total_text) ?></p>
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
                $purchReqLineDataProvider = new ActiveDataProvider([
                    'query' => $model->getPurchReqLines(),
                    'pagination' => false,
                ]);
                ?>

                <?= GridView::widget([
                    'dataProvider' => $purchReqLineDataProvider,
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
                            'label' => 'ชื่อสินค้า',
                            'headerOptions' => ['style' => 'width: 200px;'],
                        ],
                        [
                            'attribute' => 'product_description',
                            'label' => 'รายละเอียด',
                            'headerOptions' => ['style' => 'width: 200px;'],
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
                            'attribute' => 'unit',
                            'label' => 'หน่วยนับ',
                            'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                        ],
                        [
                            'attribute' => 'line_total',
                            'label' => 'ราคารวม',
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
                                    <div class="col-8">ส่วนลด:</div>
                                    <div class="col-4 text-end">
                                        <span class="fw-bold"><?= Yii::$app->formatter->asCurrency($model->discount_amount, 'THB') ?></span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-8">VAT (7%):</div>
                                    <div class="col-4 text-end">
                                        <span class="fw-bold"><?= Yii::$app->formatter->asCurrency($model->vat_amount, 'THB') ?></span>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-8"><strong>ยอดรวมสุทธิ:</strong></div>
                                    <div class="col-4 text-end">
                                        <span class="fw-bold text-primary h5"><?= Yii::$app->formatter->asCurrency($model->net_amount, 'THB') ?></span>
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