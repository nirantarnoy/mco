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

$model_doc = \common\models\PurchReqDoc::find()->where(['purch_req_id' => $model->id])->all();
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
                    <?php if (\Yii::$app->user->can('purchreq/converttopurchaseorder')): ?>
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

                <?php if (\Yii::$app->user->can('purchreq/update')): ?>
                    <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#upload-doc-modal" data-bs-toggle="modal" data-bs-target="#upload-doc-modal">
                    <i class="fas fa-paperclip"></i> แนบไฟล์เพิ่มเติม
                </button>
                <?php if ($model->approve_status == PurchReq::APPROVE_STATUS_PENDING): ?>
                    <?php if (\Yii::$app->user->can('purchreq/reject')): ?>
                        <?= Html::a('ไม่อนุมัติ', ['reject', 'id' => $model->id], [
                            'class' => 'btn btn-warning',
                            'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะไม่อนุมัติใบขอซื้อนี้?',
                            'data-method' => 'post',
                        ]) ?>
                    <?php endif; ?>
                    <?php if (\Yii::$app->user->can('purchreq/approve')): ?>
                        <?= Html::a('อนุมัติ', ['approve', 'id' => $model->id], [
                            'class' => 'btn btn-success',
                            'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบขอซื้อนี้?',
                            'data-method' => 'post',
                        ]) ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($model->status != PurchReq::STATUS_CANCELLED): ?>
                    <?php if (\Yii::$app->user->can('purchreq/cancel')): ?>
                        <?= Html::a('ยกเลิกใบขอซื้อ', ['cancel', 'id' => $model->id], [
                            'class' => 'btn btn-warning',
                            'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะยกเลิกใบสั่งซื้อนี้?',
                            'data-method' => 'post',
                        ]) ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php /* if ($model->purch_id == null || $model->purch_id == ''): ?>
                    <?php if (\Yii::$app->user->can('purchreq/delete')): ?>
                        <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบใบขอซื้อนี้?',
                            'data-method' => 'post',
                        ]) ?>
                    <?php endif; ?>
                <?php endif; */ ?>
                <?php if ($model->approve_status == PurchReq::APPROVE_STATUS_APPROVED && !$model->purch_id): ?>
                    <?php if ($model->is_recall != 1): ?>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#recall-modal" data-bs-toggle="modal" data-bs-target="#recall-modal">
                            <i class="fas fa-undo-alt"></i> ร้องขอแก้ไข (Recall)
                        </button>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($model->is_recall == 1): ?>
                    <?php if (\Yii::$app->user->can('purchreq/approve') || \backend\models\User::isUserAdmin()): ?>
                        <?= Html::a('<i class="fas fa-check"></i> อนุมัติ Recall (ดึงกลับมาแก้ไข)', ['approve-recall', 'id' => $model->id], [
                            'class' => 'btn btn-success',
                            'data' => [
                                'confirm' => 'ยืนยันการอนุมัติ Recall? เอกสารนี้จะถูกปลดสถานะอนุมัติและสามารถแก้ไขได้',
                                'method' => 'post',
                            ],
                        ]) ?>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#reject-recall-modal" data-bs-toggle="modal" data-bs-target="#reject-recall-modal">
                            <i class="fas fa-times"></i> ปฏิเสธ Recall
                        </button>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark p-2 align-self-center"><i class="fas fa-hourglass-half"></i> อยู่ระหว่างรออนุมัติ Recall จากผู้มีอำนาจ</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?= Html::a('กลับ', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>

        <?php if ($model->is_recall == 1): ?>
            <div class="alert alert-warning border border-warning shadow-sm">
                <h5><i class="fas fa-exclamation-triangle"></i> เอกสารนี้มีคำร้องขอ Recall เพื่อแก้ไข</h5>
                <p class="mb-1"><strong>ผู้ร้องขอ:</strong> <?= \backend\models\User::findEmployeeNameByUserId($model->recall_by) ?> (วันที่: <?= $model->recall_at ? date('d/m/Y H:i:s', strtotime($model->recall_at)) : '-' ?>)</p>
                <p class="mb-0"><strong>เหตุผล:</strong> <?= Html::encode($model->recall_reason) ?></p>
            </div>
        <?php endif; ?>

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
                                [
                                    'attribute' => 'required_date',
                                    'label' => 'วันที่ต้องการใช้งาน',
                                    'format' => ['date', 'php:m/d/Y'],
                                ],
                                'vendor_name:text:ชื่อผู้ขาย',
                                [
                                    'attribute' => 'status',
                                    'label' => 'สถานะเอกสาร',
                                    'format' => 'raw',
                                    'value' => $model->getApproveStatusBadge(),
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
                                [
                                    'attribute' => 'purch_id',
                                    'value' => function ($model) {
                                        return \backend\models\Purch::findNo($model->purch_id);
                                    }
                                ],
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
                            'attribute' => 'doc_ref_no',
                            'label' => 'อ้างอิง',
                            'headerOptions' => ['style' => 'width: 100px;'],
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
                            'format' => ['decimal', 2],
                        ],
                        [
                            'attribute' => 'unit_id',
                            'label' => 'หน่วยนับ',
                            'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                            'value' => function ($model) {
                                return \backend\models\Unit::findName($model->unit_id);
                            }
                        ],
                        [
                            'attribute' => 'line_total',
                            'label' => 'ราคารวม',
                            'headerOptions' => ['style' => 'width: 120px; text-align: right;'],
                            'contentOptions' => ['style' => 'text-align: right;'],
                            'format' => ['decimal', 2],
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

        <?php
        // ดึง Payment Vouchers ที่เกี่ยวข้อง
        $pvRefs = \backend\models\PaymentVoucherRef::find()
            ->where(['ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PR, 'ref_id' => $model->id])
            ->all();

        if (!empty($pvRefs)):
        ?>
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-money-check-alt"></i> Payment Vouchers ที่เกี่ยวข้อง</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%; text-align: center;">#</th>
                            <th style="width: 20%;">PV No.</th>
                            <th style="width: 15%;">วันที่</th>
                            <th style="width: 20%; text-align: right;">จำนวนเงิน</th>
                            <th style="width: 20%;">ผู้รับเงิน</th>
                            <th style="width: 20%; text-align: center;">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalPaid = 0;
                        foreach ($pvRefs as $i => $ref): 
                            $totalPaid += $ref->amount;
                        ?>
                            <tr>
                                <td style="text-align: center;"><?= $i + 1 ?></td>
                                <td>
                                    <?= Html::a($ref->paymentVoucher->voucher_no, ['/paymentvoucher/view', 'id' => $ref->payment_voucher_id], [
                                        'class' => 'text-primary fw-bold',
                                        'target' => '_blank'
                                    ]) ?>
                                </td>
                                <td><?= Yii::$app->formatter->asDate($ref->paymentVoucher->trans_date, 'php:d/m/Y') ?></td>
                                <td style="text-align: right;"><?= Yii::$app->formatter->asCurrency($ref->amount, 'THB') ?></td>
                                <td><?= Html::encode($ref->paymentVoucher->recipient_name) ?></td>
                                <td style="text-align: center;">
                                    <?= Html::a('<i class="fas fa-eye"></i> ดูรายละเอียด', ['/paymentvoucher/view', 'id' => $ref->payment_voucher_id], [
                                        'class' => 'btn btn-sm btn-info',
                                        'target' => '_blank'
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" style="text-align: right;">รวมจำนวนเงินที่จ่ายแล้ว:</th>
                            <th style="text-align: right;"><?= Yii::$app->formatter->asCurrency($totalPaid, 'THB') ?></th>
                            <th colspan="2"></th>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align: right;">ยอดคงเหลือ:</th>
                            <th style="text-align: right;" class="<?= ($model->net_amount - $totalPaid) > 0 ? 'text-danger' : 'text-success' ?>">
                                <?= Yii::$app->formatter->asCurrency($model->net_amount - $totalPaid, 'THB') ?>
                            </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <br/>
    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="mb-0">เอกสารแนบ</h4>
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#upload-doc-modal" data-bs-toggle="modal" data-bs-target="#upload-doc-modal">
                    <i class="fas fa-paperclip"></i> แนบไฟล์เพิ่มเติม
                </button>
            </div>
            <table class="table table-bordered table-striped" style="width: 100%">
                <thead>
                <tr>
                    <th style="width: 5%;text-align: center">#</th>
                    <th style="width: 60%;text-align: center">ชื่อไฟล์</th>
                    <th style="width: 20%;text-align: center">ดูเอกสาร</th>
                    <th style="width: 15%;text-align: center">ลบ</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($model_doc != null): ?>

                    <?php foreach ($model_doc as $key => $value): ?>
                        <tr>
                            <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                            <td><?= $value->doc_name ?></td>
                            <td style="text-align: center">
                                <a href="<?= Yii::$app->request->BaseUrl . '/uploads/purch_req_doc/' . $value->doc_name ?>"
                                   target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-search"></i> ดูเอกสาร
                                </a>
                            </td>
                            <td style="text-align: center">
                                <button type="button" class="btn btn-outline-danger btn-sm" data-value="<?= $model->id ?>"
                                     data-var="<?= trim($value->doc_name) ?>"
                                     onclick="delete_doc_view($(this))">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">ไม่มีเอกสารแนบ</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <br/>

    <!-- Modal สำหรับ แนบไฟล์เพิ่มเติม -->
    <?php
    \yii\bootstrap4\Modal::begin([
        'id' => 'upload-doc-modal',
        'title' => '<h4><i class="fas fa-paperclip text-primary"></i> แนบเอกสารเพิ่มเติม</h4>',
        'size' => \yii\bootstrap4\Modal::SIZE_DEFAULT,
        'options' => ['tabindex' => false],
        'closeButton' => [
            'class' => 'btn-close',
            'data-bs-dismiss' => 'modal',
            'aria-label' => 'Close',
        ],
    ]);
    ?>
    <form action="<?= \yii\helpers\Url::to(['purchreq/add-doc-file'], true) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
        <input type="hidden" name="id" value="<?= $model->id ?>">
        <input type="hidden" name="redirect_to" value="view">
        <div class="modal-body">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">เลือกไฟล์เอกสาร <span class="text-danger">*</span></label>
                <input type="file" name="file_doc[]" class="form-control" multiple required>
                <small class="text-muted">สามารถเลือกได้หลายไฟล์พร้อมกัน (PDF, รูปภาพ, เอกสารอื่นๆ)</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> อัพโหลดไฟล์</button>
        </div>
    </form>
    <?php \yii\bootstrap4\Modal::end(); ?>

    <form id="form-delete-doc-view" action="<?= \yii\helpers\Url::to(['purchreq/delete-doc-file'], true) ?>" method="post">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
        <input type="hidden" name="id" value="<?= $model->id ?>">
        <input type="hidden" name="redirect_to" value="view">
        <input type="hidden" class="delete-doc-list" name="doc_delete_list" value="">
    </form>

    <!-- Modal สำหรับ ร้องขอ Recall -->
    <?php
    \yii\bootstrap4\Modal::begin([
        'id' => 'recall-modal',
        'title' => '<h4><i class="fas fa-undo-alt text-warning"></i> ร้องขอแก้ไขใบขอซื้อ (Recall)</h4>',
        'size' => \yii\bootstrap4\Modal::SIZE_DEFAULT,
        'options' => ['tabindex' => false],
        'closeButton' => [
            'class' => 'btn-close',
            'data-bs-dismiss' => 'modal',
            'aria-label' => 'Close',
        ],
    ]);
    ?>
    <?php $form = \yii\widgets\ActiveForm::begin([
        'action' => ['request-recall', 'id' => $model->id],
        'method' => 'post',
    ]); ?>
    <div class="modal-body">
        <p class="text-muted">เมื่อส่งคำร้องขอ ระบบจะแจ้งเตือนผู้อนุมัติเพื่อพิจารณาปลดล็อคใบขอซื้อกลับมาให้แก้ไข</p>
        <div class="form-group mb-3">
            <label for="pr-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ต้องการดึงเอกสารกลับมาแก้ไข <span class="text-danger">*</span></label>
            <textarea name="recall_reason" id="pr-recall-reason" class="form-control" rows="4" required placeholder="ตัวอย่าง: ขอแก้ไขจำนวนสินค้า, แก้ไขรายการสินค้า, เปลี่ยนแปลงงบประมาณ"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane"></i> ยืนยันส่งคำร้องขอ Recall</button>
    </div>
    <?php \yii\widgets\ActiveForm::end(); ?>
    <?php \yii\bootstrap4\Modal::end(); ?>

    <!-- Modal สำหรับ ปฏิเสธ Recall -->
    <?php
    \yii\bootstrap4\Modal::begin([
        'id' => 'reject-recall-modal',
        'title' => '<h4><i class="fas fa-times text-danger"></i> ปฏิเสธคำร้องขอ Recall</h4>',
        'size' => \yii\bootstrap4\Modal::SIZE_DEFAULT,
        'options' => ['tabindex' => false],
        'closeButton' => [
            'class' => 'btn-close',
            'data-bs-dismiss' => 'modal',
            'aria-label' => 'Close',
        ],
    ]);
    ?>
    <?php $form = \yii\widgets\ActiveForm::begin([
        'action' => ['reject-recall', 'id' => $model->id],
        'method' => 'post',
    ]); ?>
    <div class="modal-body">
        <div class="form-group mb-3">
            <label for="pr-reject-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ไม่อนุมัติคำร้องขอ Recall</label>
            <textarea name="reject_reason" id="pr-reject-recall-reason" class="form-control" rows="3" placeholder="ระบุเหตุผลที่ไม่อนุมัติ"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-danger"><i class="fas fa-check"></i> ยืนยันปฏิเสธ Recall</button>
    </div>
    <?php \yii\widgets\ActiveForm::end(); ?>
    <?php \yii\bootstrap4\Modal::end(); ?>

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

<script>
function delete_doc_view(e) {
    var id = e.attr('data-value');
    var fileName = e.attr('data-var');
    if (id > 0 && fileName && confirm('ต้องการลบไฟล์แนบ \"' + fileName + '\" ใช่หรือไม่?')) {
        $('.delete-doc-list').val(fileName);
        $('#form-delete-doc-view').submit();
    }
}
</script>