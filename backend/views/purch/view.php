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

$model_doc = \common\models\PurchDoc::find()->where(['purch_id' => $model->id])->all();
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

                <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                    'class' => 'btn btn-info',
                    'target' => '_blank'
                ]) ?>
                <?= Html::a('<i class="fas fa-print"></i> พิมพ์ ตปท.', ['print-for-export', 'id' => $model->id], [
                    'class' => 'btn btn-info',
                    'target' => '_blank'
                ]) ?>
                <?php if (\Yii::$app->user->can('CanApprovePo') && $model->approve_status == Purch::APPROVE_STATUS_PENDING): ?>
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
                <?php if ($model->status != Purch::STATUS_CANCELLED): ?>
                    <?= Html::a('ยกเลิกใบสั่งซื้อ', ['cancel', 'id' => $model->id], [
                        'class' => 'btn btn-warning',
                        'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะยกเลิกใบสั่งซื้อนี้?',
                        'data-method' => 'post',
                    ]) ?>
                <?php endif; ?>
                <?php if ($model->approve_status == Purch::APPROVE_STATUS_APPROVED): ?>
                    <?= Html::a('<i class="fas fa-file-pdf"></i> PDF', ['pdf', 'id' => $model->id], [
                        'class' => 'btn btn-warning',
                        'target' => '_blank'
                    ]) ?>

                    <?= Html::a('<i class="fas fa-list-alt"></i> พิมพ์ใบรับสินค้า', ['printreceipt', 'id' => $model->id], [
                        'class' => 'btn btn-success',
                        'target' => '_blank'
                    ]) ?>
                    <?= Html::a('<i class="fas fa-download"></i> รับสินค้าเข้าคลัง', ['receive', 'id' => $model->id], [
                        'class' => 'btn btn-success'
                    ]) ?>
                    <?= Html::a('<i class="fas fa-history"></i> ประวัติการรับสินค้า', ['receive-history', 'id' => $model->id], [
                        'class' => 'btn btn-info'
                    ]) ?>


                <?php endif; ?>
                <?php if (\Yii::$app->user->can('purch/delete')): ?>
                    <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบใบสั่งซื้อนี้?',
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
                                    'attribute' => 'status',
                                    'label' => 'สถานะใบสั่งซื้อ',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        $po_remain = \backend\models\Purch::checkPoremain($model->id);
                                        if ($model->approve_status == Purch::APPROVE_STATUS_APPROVED && !empty($po_remain)) {
                                            return '<span class="badge bg-info">อนุมัติ</span>';
                                        } elseif ($model->approve_status == Purch::APPROVE_STATUS_PENDING && !empty($po_remain)) {
                                            return '<span class="badge bg-warning">รอพิจารณา</span>';
                                        } elseif (empty($po_remain)) {
                                            return '<span class="badge bg-success">สำเร็จ</span>';
                                        }
                                    }
                                ],
                                [
                                    'attribute' => 'total_amount',
                                    'label' => 'ยอดรวม',
                                    'format' => ['currency', 'THB'],
                                ],
                                [
                                    'attribute' => 'discount_per',
                                    'value' => function ($model) {
                                        return $model->discount_per . '%';
                                    }
                                ],
                                [
                                    'attribute' => 'discount_amount',
                                    'label' => 'ส่วนลด (จำนวนเงิน)',
                                    'value' => function ($model) {
                                        return $model->discount_amount;
                                    }
                                ],
                                [
                                    'attribute' => 'net_amount',
                                    'label' => 'ยอดรวมทั้งสิ้น',
                                    'format' => ['currency', 'THB'],
                                ],
                                [
                                    'attribute' => 'total_text',
                                    'label' => 'ยอดรวมตัวอักษร',
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
                                'note:ntext:หมายเหตุ',
                                'ref_text:text:อ้างอิง',
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
                            'attribute' => 'unit_id',
                            'value' => function ($model) {
                                return \backend\models\Unit::findName($model->unit_id);
                            }
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

    </div>
    <br/>
    <div class="label">
        <h4>เอกสารแนบ</h4>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <table class="table table-bordered table-striped" style="width: 100%">
                <thead>
                <tr>
                    <th style="width: 5%;text-align: center">#</th>
                    <th style="width: 50%;text-align: center">ชื่อไฟล์</th>
                    <th style="width: 10%;text-align: center">ดูเอกสาร</th>
                    <th style="width: 5%;text-align: center">-</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($model_doc != null): ?>

                    <?php foreach ($model_doc as $key => $value): ?>
                        <tr>
                            <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                            <td><?= $value->doc_name ?></td>
                            <td style="text-align: center">
                                <a href="<?= Yii::$app->request->BaseUrl . '/uploads/purch_doc/' . $value->doc_name ?>"
                                   target="_blank">
                                    ดูเอกสาร
                                </a>
                            </td>
                            <td style="text-align: center">
                                <!--                                <div class="btn btn-danger" data-var="-->
                                <?php //= trim($value->doc_name) ?><!--" onclick="delete_doc($(this))">ลบ</div>-->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <br/>
    <br/>
    <div class="row">
        <?php if ($model->approve_status == 1): ?>
            <div class="col-lg-3">
                <form action="<?= \yii\helpers\Url::to(['purch/print-tags'], true) ?>" method="post" target="_blank">
                    <input type="hidden" name="purch_id" value="<?= $model->id ?>">
                    <button type="submit" class="btn btn-primary" style="width: 100%">
                        <i class="fa fa-print"></i> พิมพ์บาร์โค้ด
                    </button>
                </form>
            </div>
        <?php endif; ?>
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

<?php
$this->registerJs("
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
        ");
?>