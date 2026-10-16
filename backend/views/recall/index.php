<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\bootstrap4\Modal;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $type string */
/* @var $countTotal int */
/* @var $countPurch int */
/* @var $countPurchReq int */
/* @var $countPurchaseMaster int */
/* @var $countQuotation int */
/* @var $countPreAdvance int */
/* @var $countPettyCashVoucher int */
/* @var $countPettyCashAdvance int */

$this->title = 'จัดการคำร้องขอ Recall เอกสาร';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="recall-index">
    <!-- Flash Messages -->
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i> <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('info')): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle me-2"></i> <?= Yii::$app->session->getFlash('info') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card card-outline card-warning shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title mb-0">
                <i class="fas fa-undo-alt text-warning mr-2"></i> <?= Html::encode($this->title) ?>
            </h3>
            <div class="card-tools">
                <span class="badge badge-warning text-dark px-3 py-2" style="font-size: 14px;">
                    <i class="fas fa-hourglass-half"></i> รอพิจารณาทั้งหมด: <strong><?= number_format($countTotal) ?></strong> รายการ
                </span>
            </div>
        </div>

        <div class="card-body">
            <!-- Filter Tabs -->
            <ul class="nav nav-pills mb-3 flex-wrap">
                <li class="nav-item mb-2 mr-2">
                    <a class="nav-link <?= $type === 'all' ? 'active bg-warning text-dark font-weight-bold' : '' ?>" href="<?= Url::to(['index', 'type' => 'all']) ?>">
                        <i class="fas fa-list-ul mr-1"></i> ทั้งหมด (<?= $countTotal ?>)
                    </a>
                </li>
                <li class="nav-item mb-2 mr-2">
                    <a class="nav-link <?= $type === 'purch' ? 'active bg-primary font-weight-bold' : '' ?>" href="<?= Url::to(['index', 'type' => 'purch']) ?>">
                        <i class="fas fa-shopping-cart mr-1"></i> ใบสั่งซื้อ PO (<?= $countPurch ?>)
                    </a>
                </li>
                <li class="nav-item mb-2 mr-2">
                    <a class="nav-link <?= $type === 'purchreq' ? 'active bg-info font-weight-bold' : '' ?>" href="<?= Url::to(['index', 'type' => 'purchreq']) ?>">
                        <i class="fas fa-file-invoice mr-1"></i> ใบขอซื้อ PR (<?= $countPurchReq ?>)
                    </a>
                </li>
                <li class="nav-item mb-2 mr-2">
                    <a class="nav-link <?= $type === 'purchasemaster' ? 'active bg-secondary font-weight-bold' : '' ?>" href="<?= Url::to(['index', 'type' => 'purchasemaster']) ?>">
                        <i class="fas fa-shopping-bag mr-1"></i> ซื้อ None PR (<?= $countPurchaseMaster ?>)
                    </a>
                </li>
                <li class="nav-item mb-2 mr-2">
                    <a class="nav-link <?= $type === 'quotation' ? 'active bg-success font-weight-bold' : '' ?>" href="<?= Url::to(['index', 'type' => 'quotation']) ?>">
                        <i class="fas fa-file-contract mr-1"></i> ใบเสนอราคา (<?= $countQuotation ?>)
                    </a>
                </li>
                <li class="nav-item mb-2 mr-2">
                    <a class="nav-link <?= $type === 'preadvance' ? 'active bg-warning text-dark font-weight-bold' : '' ?>" href="<?= Url::to(['index', 'type' => 'preadvance']) ?>">
                        <i class="fas fa-money-check-alt mr-1"></i> Payment Advance (<?= $countPreAdvance ?>)
                    </a>
                </li>
                <li class="nav-item mb-2 mr-2">
                    <a class="nav-link <?= $type === 'pettycashvoucher' ? 'active bg-dark font-weight-bold' : '' ?>" href="<?= Url::to(['index', 'type' => 'pettycashvoucher']) ?>">
                        <i class="fas fa-receipt mr-1"></i> เงินสดย่อย PCV (<?= $countPettyCashVoucher ?>)
                    </a>
                </li>
                <li class="nav-item mb-2 mr-2">
                    <a class="nav-link <?= $type === 'pettycashadvance' ? 'active bg-info font-weight-bold' : '' ?>" href="<?= Url::to(['index', 'type' => 'pettycashadvance']) ?>">
                        <i class="fas fa-hand-holding-usd mr-1"></i> เบิกเงินทดแทน (<?= $countPettyCashAdvance ?>)
                    </a>
                </li>
            </ul>

            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-bordered table-hover table-striped align-middle'],
                    'emptyText' => '<div class="text-center py-5 text-muted"><i class="fas fa-check-circle fa-3x text-success mb-3"></i><p class="h5">ไม่มีรายการคำร้องขอ Recall ค้างอยู่ในระบบ</p></div>',
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'header' => '#',
                            'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                        ],
                        [
                            'attribute' => 'type_label',
                            'header' => 'ประเภทเอกสาร',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                            'value' => function($model) {
                                return '<span class="badge ' . $model['type_badge'] . ' px-2 py-1">' . Html::encode($model['type_label']) . '</span>';
                            }
                        ],
                        [
                            'attribute' => 'doc_no',
                            'header' => 'เลขที่เอกสาร',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 160px;'],
                            'value' => function($model) {
                                return Html::a('<strong>' . Html::encode($model['doc_no']) . '</strong>', $model['view_url'], [
                                    'target' => '_blank',
                                    'title' => 'คลิกเพื่อเปิดหน้าเอกสารเต็ม',
                                    'class' => 'text-primary'
                                ]);
                            }
                        ],
                        [
                            'attribute' => 'doc_date',
                            'header' => 'วันที่เอกสาร',
                            'headerOptions' => ['style' => 'width: 110px; text-align: center;'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                            'value' => function($model) {
                                return $model['doc_date'] ? date('d/m/Y', strtotime($model['doc_date'])) : '-';
                            }
                        ],
                        [
                            'attribute' => 'party_name',
                            'header' => 'ผู้ขาย / ผู้รับเงิน',
                            'value' => function($model) {
                                return $model['party_name'] ?: '-';
                            }
                        ],
                        [
                            'attribute' => 'amount',
                            'header' => 'ยอดเงิน (บาท)',
                            'headerOptions' => ['style' => 'width: 130px; text-align: right;'],
                            'contentOptions' => ['style' => 'text-align: right; font-weight: 600;'],
                            'value' => function($model) {
                                return number_format($model['amount'], 2);
                            }
                        ],
                        [
                            'attribute' => 'recall_reason',
                            'header' => 'เหตุผลการขอ Recall',
                            'format' => 'raw',
                            'value' => function($model) {
                                return '<div class="alert alert-warning mb-0 p-2" style="font-size: 13px;">' . nl2br(Html::encode($model['recall_reason'])) . '</div>';
                            }
                        ],
                        [
                            'attribute' => 'recall_by_name',
                            'header' => 'ผู้ขอ Recall',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 160px;'],
                            'value' => function($model) {
                                $html = '<div><i class="fas fa-user-circle text-muted mr-1"></i>' . Html::encode($model['recall_by_name'] ?: 'ไม่ระบุ') . '</div>';
                                if ($model['recall_at']) {
                                    $html .= '<div class="text-muted" style="font-size: 12px;"><i class="far fa-clock mr-1"></i>' . date('d/m/Y H:i', strtotime($model['recall_at'])) . '</div>';
                                }
                                return $html;
                            }
                        ],
                        [
                            'header' => 'การจัดการคำร้อง',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 220px; text-align: center;'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                            'value' => function($model) {
                                $btnView = '<button type="button" class="btn btn-sm btn-info mr-1 btn-quick-view" data-type="' . $model['type'] . '" data-id="' . $model['id'] . '" title="ดูสรุปรายละเอียดแบบ Popup"><i class="fas fa-eye"></i> รายละเอียด</button>';
                                $btnApprove = Html::a('<i class="fas fa-check"></i> อนุมัติ', ['approve', 'type' => $model['type'], 'id' => $model['id']], [
                                    'class' => 'btn btn-sm btn-success mr-1',
                                    'data' => [
                                        'confirm' => 'ยืนยันอนุมัติ Recall สำหรับ ' . $model['doc_no'] . '? เอกสารจะถูกปลดล็อคให้แก้ไขได้',
                                        'method' => 'post',
                                    ],
                                    'title' => 'อนุมัติการ Recall'
                                ]);
                                $btnReject = '<button type="button" class="btn btn-sm btn-danger btn-reject-modal" data-type="' . $model['type'] . '" data-id="' . $model['id'] . '" data-doc="' . Html::encode($model['doc_no']) . '" title="ปฏิเสธการ Recall"><i class="fas fa-times"></i></button>';

                                return '<div class="btn-group">' . $btnView . $btnApprove . $btnReject . '</div>';
                            }
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal สำหรับ Quick View -->
<?php
Modal::begin([
    'id' => 'quick-view-modal',
    'title' => '<h4><i class="fas fa-file-alt text-primary mr-2"></i> รายละเอียดเอกสาร Recall</h4>',
    'size' => Modal::SIZE_LARGE,
    'options' => ['tabindex' => false],
    'closeButton' => [
        'class' => 'close',
        'data-dismiss' => 'modal',
        'aria-label' => 'Close',
    ],
]);
echo '<div id="quick-view-content"><div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-3x text-muted"></i><p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p></div></div>';
Modal::end();
?>

<!-- Modal สำหรับ Reject -->
<?php
Modal::begin([
    'id' => 'reject-modal',
    'title' => '<h4><i class="fas fa-times-circle text-danger mr-2"></i> ปฏิเสธคำร้องขอ Recall</h4>',
    'size' => Modal::SIZE_DEFAULT,
    'options' => ['tabindex' => false],
    'closeButton' => [
        'class' => 'close',
        'data-dismiss' => 'modal',
        'aria-label' => 'Close',
    ],
]);
?>
<form id="reject-form" method="post" action="">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
    <div class="modal-body">
        <p>เอกสาร: <strong id="reject-doc-no" class="text-danger"></strong></p>
        <div class="form-group mb-3">
            <label class="font-weight-bold">ระบุเหตุผลการไม่อนุมัติคำร้องขอ Recall</label>
            <textarea name="reject_reason" class="form-control" rows="3" placeholder="ระบุเหตุผลที่ปฏิเสธคำร้องขอ"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> ยืนยันปฏิเสธ Recall</button>
    </div>
</form>
<?php
Modal::end();
?>

<?php
$urlViewAjax = Url::to(['recall/view-ajax']);
$urlReject = Url::to(['recall/reject']);

$js = <<<JS
// Quick view modal
$(document).on('click', '.btn-quick-view', function() {
    var type = $(this).data('type');
    var id = $(this).data('id');
    var modal = $('#quick-view-modal');
    
    modal.find('#quick-view-content').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-3x text-muted"></i><p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p></div>');
    modal.modal('show');
    
    $.ajax({
        url: '$urlViewAjax',
        type: 'GET',
        data: {type: type, id: id},
        success: function(response) {
            modal.find('#quick-view-content').html(response);
        },
        error: function() {
            modal.find('#quick-view-content').html('<div class="alert alert-danger">ไม่สามารถโหลดข้อมูลเอกสารได้</div>');
        }
    });
});

// Reject modal
$(document).on('click', '.btn-reject-modal', function() {
    var type = $(this).data('type');
    var id = $(this).data('id');
    var doc = $(this).data('doc');
    var modal = $('#reject-modal');
    
    modal.find('#reject-doc-no').text(doc);
    modal.find('#reject-form').attr('action', '$urlReject' + '?type=' + type + '&id=' + id);
    modal.modal('show');
});
JS;
$this->registerJs($js);
?>
