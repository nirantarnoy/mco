<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use backend\models\Purch;
use yii\data\ActiveDataProvider;

/* @var $this yii\web\View */
/* @var $model backend\models\Purch */

$this->title = 'ใบสั่งซื้อ: ' . $model->purch_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบสั่งซื้อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="purch-view">

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
            <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?php if ($model->approve_status == Purch::APPROVE_STATUS_APPROVED): ?>
                <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                    'class' => 'btn btn-info',
                    'target' => '_blank'
                ]) ?>
                <?= Html::a('<i class="fas fa-file-pdf"></i> PDF', ['pdf', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'target' => '_blank'
                ]) ?>

                    <?= Html::a('<i class="fas fa-download"></i> รับสินค้าเข้าคลัง', ['receive', 'id' => $model->id], [
                        'class' => 'btn btn-success'
                    ]) ?>
                    <?= Html::a('<i class="fas fa-history"></i> ประวัติการรับสินค้า', ['receive-history', 'id' => $model->id], [
                        'class' => 'btn btn-info'
                    ]) ?>

                <?= Html::a('อนุมัติ', ['approve', 'id' => $model->id], [
                    'class' => 'btn btn-success',
                    'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบสั่งซื้อนี้?',
                    'data-method' => 'post',
                ]) ?>
                <?= Html::a('ไม่อนุมัติ', ['reject', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะไม่อนุมัติใบสั่งซื้อนี้?',
                    'data-method' => 'post',
                ]) ?>
            <?php endif; ?>
            <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบใบสั่งซื้อนี้?',
                'data-method' => 'post',
            ]) ?>
            <?= Html::a('กลับ', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">ข้อมูลใบสั่งซื้อ</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'purch_no:text:เลขที่ใบสั่งซื้อ',
                            [
                                'attribute' => 'purch_date',
                                'label' => 'วันที่',
                                'format' => ['date', 'php:d/m/Y'],
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
                                'attribute' => 'total_amount',
                                'label' => 'ยอดรวม',
                                'format' => ['currency', 'THB'],
                            ],
                            'note:ntext:หมายเหตุ',
                            'ref_text:text:อ้างอิง',
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
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">รายการสินค้า</h5>
        </div>
        <div class="card-body">
            <?php
            $purchLineDataProvider = new ActiveDataProvider([
                'query' => $model->getPurchLines(),
                'pagination' => false,
            ]);
            ?>

            <?= GridView::widget([
                'dataProvider' => $purchLineDataProvider,
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
                        'headerOptions' => ['style' => 'width: 250px;'],
                        'contentOptions' => ['style' => 'text-align: left;'],
                        'value' => function ($data) {
                            return \backend\models\Product::findName($data->product_id);
                        },
                    ],
                    [
                        'attribute' => 'qty',
                        'label' => 'จำนวน',
                        'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'format' => ['decimal', 2],
                        'pageSummary' => true,
                        'pageSummaryFunc' => GridView::F_SUM,
                    ],
                    [
                        'attribute' => 'line_price',
                        'label' => 'ราคา/หน่วย',
                        'headerOptions' => ['style' => 'width: 120px; text-align: right;'],
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'format' => ['currency', 'THB'],
                    ],
                    [
                        'attribute' => 'line_total',
                        'label' => 'ราคารวม',
                        'headerOptions' => ['style' => 'width: 120px; text-align: right;'],
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'format' => ['currency', 'THB'],
                        'pageSummary' => true,
                        'pageSummaryFunc' => GridView::F_SUM,
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
</style>