<?php

use yii\bootstrap4\Modal;
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
                    <?php $po_remain = \backend\models\Purch::checkPoremain($model->id); ?>
                    <?php if (!empty($po_remain)): ?>
                        <?= Html::a('<i class="fas fa-download"></i> รับสินค้าเข้าคลัง', ['receive', 'id' => $model->id], [
                            'class' => 'btn btn-success'
                        ]) ?>
                    <?php endif; ?>
                    <?php if ($model->status != Purch::STATUS_COMPLETED): ?>
                        <?= Html::a('<i class="fas fa-check-circle"></i> ยืนยันรับบริการ/ปิดจบ', ['confirm-service-receive', 'id' => $model->id], [
                            'class' => 'btn btn-primary',
                            'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะยืนยันการรับบริการ/ปิดจบใบสั่งซื้อนี้? สถานะจะถูกปรับเป็น Completed โดยไม่มีการบันทึกสต็อก',
                            'data-method' => 'post',
                        ]) ?>
                    <?php endif; ?>
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
                                    'attribute' => 'currency_id',
                                    'value' => function ($data) {
                                        return \backend\models\Currency::findCode($data->currency_id);
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
                                    'attribute' => 'vat_amount',
                                    'label' => 'VAT (จำนวนเงิน)',
                                    'value' => function ($model) {
                                        return $model->vat_amount;
                                    }
                                ],
                                [
                                    'attribute' => 'whd_tax_amount',
                                    'value' => function ($model) {
                                        return $model->whd_tax_amount;
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
                                //  return \backend\models\Product::findName($data->product_id);
                                return $data->product_name;
                            },
                        ],
                        'product_description',
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

    <div class="row">
        <div class="col-lg-6">
            <div class="label">
                <h4>เอกสารแนบ</h4>
            </div>
            <table class="table table-bordered table-striped" style="width: 100%">
                <thead>
                <tr>
                    <th style="width: 5%;text-align: center">#</th>
                    <th style="width: 50%;text-align: center">ชื่อไฟล์</th>
                    <th style="width: 20%;text-align: center">ประเภท</th>
                    <th style="width: 20%;text-align: center">ดูเอกสาร</th>
                    <th style="width: 5%;text-align: center">-</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($model_doc != null): ?>

                    <?php foreach ($model_doc as $key => $value): ?>
                        <tr>
                            <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                            <td><?= $value->doc_name ?></td>
                            <td><?= \backend\helpers\PurchDocType::getTypeById($value->doc_type_id) ?></td>
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
        <div class="col-lg-6">
            <div class="label">
                <h4>เอกสารแนบใบรับสินค้า</h4>
            </div>
            <table class="table table-bordered table-striped" style="width: 100%">
                <thead>
                <tr>
                    <th style="width: 5%;text-align: center">#</th>
                    <th style="width: 50%;text-align: center">ชื่อไฟล์</th>
                    <th style="width: 20%;text-align: center">ดูเอกสาร</th>
                    <th style="width: 5%;text-align: center">-</th>
                </tr>
                </thead>
                <tbody>
                <?php $model_rec_doc = findDoc($model->id); ?>
                <?php if ($model_rec_doc != null): ?>

                    <?php for ($i = 0; $i <= count($model_rec_doc) - 1; $i++): ?>
                        <tr>
                            <td style="width: 10px;text-align: center"><?= $i + 1 ?></td>
                            <td><?= $model_rec_doc[$i] ?></td>
                            <td style="text-align: center">
                                <a href="<?= Yii::$app->request->BaseUrl . '/uploads/purch_receive_doc/' . $model_rec_doc[$i] ?>"
                                   target="_blank">
                                    ดูเอกสาร
                                </a>
                            </td>
                            <td style="text-align: center">
                                <!--                                <div class="btn btn-danger" data-var="-->
                                <?php //= trim($value->doc_name) ?><!--" onclick="delete_doc($(this))">ลบ</div>-->
                            </td>
                        </tr>
                    <?php endfor; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <br/>
    <div class="payment-history-section">
        <h3 class="mb-3">
            <i class="fas fa-history"></i> ประวัติการโอนเงิน
        </h3>

        <?php if (empty($paymentLines)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> ยังไม่มีประวัติการโอนเงินสำหรับคำสั่งซื้อนี้
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">วันที่โอน</th>
                        <th style="width: 15%;">ธนาคาร</th>
                        <th style="width: 20%;">ชื่อบัญชี</th>
                        <th style="width: 15%;">วิธีการชำระ</th>
                        <th style="width: 15%;" class="text-right">จำนวนเงิน</th>
                        <th style="width: 10%;" class="text-center">Slip</th>
                        <th style="width: 5%;" class="text-center">หมายเหตุ</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $totalAmount = 0; ?>
                    <?php foreach ($paymentLines as $index => $line): ?>
                        <?php $totalAmount += $line->pay_amount; ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <?= date('d-m-Y',strtotime($payment_date))?>
                            </td>
                            <td>
                                <strong><?= Html::encode($line->bank_id) ?></strong>
                            </td>
                            <td><?= Html::encode($line->bank_name) ?></td>
                            <td>
                                    <span class="badge badge-primary">
                                        <?= Html::encode($line->payment_method_id) ?>
                                    </span>
                            </td>
                            <td class="text-right">
                                <strong class="text-success">
                                    <?= Yii::$app->formatter->asDecimal($line->pay_amount, 2) ?>
                                </strong> บาท
                            </td>
                            <td class="text-center">
                                <?php if (!empty($line->doc)): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-info btn-view-slip"
                                            data-id="<?= $line->id ?>"
                                            data-toggle="tooltip"
                                            title="คลิกเพื่อดู Slip">
                                        <i class="fas fa-receipt"></i> ดู Slip
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($line->note)): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            data-toggle="popover"
                                            data-trigger="focus"
                                            data-content="<?= Html::encode($line->note) ?>"
                                            title="หมายเหตุ">
                                        <i class="fas fa-sticky-note"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot class="thead-light">
                    <tr>
                        <th colspan="5" class="text-right">รวมยอดโอนทั้งหมด:</th>
                        <th class="text-right">
                            <strong class="text-success" style="font-size: 1.1em;">
                                <?= Yii::$app->formatter->asDecimal($totalAmount, 2) ?>
                            </strong> บาท
                        </th>
                        <th colspan="2"></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
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

    <!-- Modal สำหรับแสดง Slip -->
<?php
    Modal::begin([
        'id' => 'slip-modal',
        'title' => '<h4><i class="fas fa-receipt"></i> รายละเอียด Slip การโอนเงิน</h4>',
        'size' => Modal::SIZE_LARGE,
        'options' => ['tabindex' => false],
    ]);

    echo '<div id="slip-modal-content"></div>';

    Modal::end();
?>
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

        .payment-history-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .payment-history-section h3 {
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }

        .table thead th {
            vertical-align: middle;
        }

        .btn-view-slip {
            transition: all 0.3s ease;
        }

        .btn-view-slip:hover {
            transform: scale(1.05);
        }
    </style>

<?php
function findDoc($id)
{
    $data = [];
    if ($id) {
        $model = \backend\models\PurchReceiveDoc::find()->where(['purch_id' => $id])->all();
        if ($model) {
            foreach ($model as $value) {
                array_push($data, $value->doc_name);
            }
        }
    }
    return $data;
}

$this->registerJs("
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
        ");
?>

<?php
// JavaScript สำหรับจัดการ Modal และ Tooltip
$url_to_showslip = \yii\helpers\Url::to(['purch/view-slip'], true);
$this->registerJs(<<<JS
    // เปิด Modal แสดง Slip
    $(document).on('click', '.btn-view-slip', function() {
        var id = $(this).data('id');
        var modal = $('#slip-modal');
        
        modal.find('#slip-modal-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">กำลังโหลด...</p></div>');
        modal.modal('show');
        
        $.ajax({
            url: '$url_to_showslip',
            type: 'GET',
            data: {id: id},
            success: function(response) {
                modal.find('#slip-modal-content').html(response);
            },
            error: function() {
                modal.find('#slip-modal-content').html('<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>');
            }
        });
    });
    
    // เปิดใช้งาน Tooltip
    $('[data-toggle="tooltip"]').tooltip();
    
    // เปิดใช้งาน Popover
    $('[data-toggle="popover"]').popover();
JS
);
?>
