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

<?php
$currencyCode = $model->currency ? $model->currency->code : 'THB';
$sub_total_amount = 0;
$modelline = \backend\models\QuotationLine::find()->where(['quotation_id' => $model->id])->all();
foreach ($modelline as $line) {
    $sub_total_amount += $line->line_total;
}
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
            <?= Html::a('<i class="fas fa-file-excel"></i> Export to Express', ['export-express', 'id' => $model->id], [
                'class' => 'btn btn-success',
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
            <?php if ($model->approve_status == Quotation::APPROVE_STATUS_APPROVED): ?>
                <?php if ($model->is_recall != 1): ?>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#recall-modal" data-bs-toggle="modal" data-bs-target="#recall-modal">
                        <i class="fas fa-undo-alt"></i> ร้องขอแก้ไข (Recall)
                    </button>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($model->is_recall == 1): ?>
                <?php if (\Yii::$app->user->can('quotation/approve') || \backend\models\User::isUserAdmin()): ?>
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

    <?php if ($model->is_recall == 1): ?>
        <div class="alert alert-warning border border-warning shadow-sm mb-3">
            <h5><i class="fas fa-exclamation-triangle"></i> เอกสารนี้มีคำร้องขอ Recall เพื่อแก้ไข</h5>
            <p class="mb-1"><strong>ผู้ร้องขอ:</strong> <?= \backend\models\User::findEmployeeNameByUserId($model->recall_by) ?> (วันที่: <?= $model->recall_at ? date('d/m/Y H:i:s', strtotime($model->recall_at)) : '-' ?>)</p>
            <p class="mb-0"><strong>เหตุผล:</strong> <?= Html::encode($model->recall_reason) ?></p>
        </div>
    <?php endif; ?>

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
                        'format' => ['currency', $currencyCode],

                    ],
                    [
                        'attribute' => 'discount_amount',
                        'label' => 'ส่วนลด',
                        'headerOptions' => ['style' => 'width: 100px; text-align: right;'],
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'format' => ['currency', $currencyCode],
                        'pageSummary' => true,
                        'pageSummaryFunc' => GridView::F_SUM,
                        'pageSummaryOptions' => ['style' => 'text-align: right;'],
                    ],
                    [
                        'attribute' => 'line_total',
                        'label' => 'รวมเงิน',
                        'headerOptions' => ['style' => 'width: 120px; text-align: right;'],
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'format' => ['currency', $currencyCode],
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
                                    <span class="fw-bold"><?= Yii::$app->formatter->asCurrency($sub_total_amount, $currencyCode) ?></span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-8">ส่วนลด:</div>
                                <div class="col-4 text-end">
                                    <span class="fw-bold"><?= Yii::$app->formatter->asCurrency($model->total_discount_amount, $currencyCode) ?></span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-8">VAT (7%):</div>
                                <div class="col-4 text-end">
                                    <span class="fw-bold"><?= Yii::$app->formatter->asCurrency($model->vat_total_amount, $currencyCode) ?></span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-8"><strong>ยอดรวมทั้งสิ้น:</strong></div>
                                <div class="col-4 text-end">
                                    <span class="fw-bold text-primary h5"><?= Yii::$app->formatter->asCurrency($model->total_amount, $currencyCode) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับ ร้องขอ Recall -->
    <?php
    \yii\bootstrap4\Modal::begin([
        'id' => 'recall-modal',
        'title' => '<h4><i class="fas fa-undo-alt text-warning"></i> ร้องขอแก้ไขใบเสนอราคา (Recall)</h4>',
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
        <p class="text-muted">เมื่อส่งคำร้องขอ ระบบจะแจ้งเตือนผู้อนุมัติเพื่อพิจารณาปลดล็อคเอกสารกลับมาให้แก้ไข</p>
        <div class="form-group mb-3">
            <label for="qt-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ต้องการดึงเอกสารกลับมาแก้ไข <span class="text-danger">*</span></label>
            <textarea name="recall_reason" id="qt-recall-reason" class="form-control" rows="4" required placeholder="ตัวอย่าง: แก้ไขส่วนลด, เปลี่ยนแปลงราคาต่อหน่วย, เพิ่มรายการสินค้า"></textarea>
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
            <label for="qt-reject-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ไม่อนุมัติคำร้องขอ Recall</label>
            <textarea name="reject_reason" id="qt-reject-recall-reason" class="form-control" rows="3" placeholder="ระบุเหตุผลที่ไม่อนุมัติ"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-danger"><i class="fas fa-check"></i> ยืนยันปฏิเสธ Recall</button>
    </div>
    <?php \yii\widgets\ActiveForm::end(); ?>
    <?php \yii\bootstrap4\Modal::end(); ?>

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