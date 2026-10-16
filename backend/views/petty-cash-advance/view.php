<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\PettyCashAdvance */

$this->title = 'ใบเบิกเงินทดแทน: ' . $model->advance_no;
$this->params['breadcrumbs'][] = ['label' => 'การจัดการเงินทดแทนสดย่อย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$statusLabels = [
    'pending' => ['label' => 'รอดำเนินการ', 'class' => 'warning'],
    'approved' => ['label' => 'อนุมัติแล้ว', 'class' => 'success'],
    'rejected' => ['label' => 'ปฏิเสธ', 'class' => 'danger'],
    'paid' => ['label' => 'จ่ายแล้ว', 'class' => 'info'],
];
$status = $statusLabels[$model->status] ?? ['label' => $model->status, 'class' => 'secondary'];
?>

<div class="petty-cash-advance-view">

<!--    <div class="row">-->
<!--        <div class="col-md-8">-->
<!--        </div>-->
<!--        <div class="col-md-4 text-right">-->
<!--            <span class="badge badge---><?php //= $status['class'] ?><!-- badge-lg" style="font-size: 1.2em; padding: 8px 16px;">-->
<!--                --><?php //= $status['label'] ?>
<!--            </span>-->
<!--        </div>-->
<!--    </div>-->

    <!-- ปุ่มจัดการ -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="btn-group mr-2" role="group">
                <?= Html::a('<i class="fas fa-list"></i> รายการทั้งหมด', ['index'], [
                    'class' => 'btn btn-outline-secondary'
                ]) ?>

                <?php if ( $model->status != 'cancelled' && (\Yii::$app->user->can('CanEditAccount') || \Yii::$app->user->can('approve-advance'))): ?>
                    <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], [
                        'class' => 'btn btn-outline-primary'
                    ]) ?>
                <?php endif; ?>
                <?php if ($model->status != 'cancelled' && (\Yii::$app->user->can('CanEditAccount') || \Yii::$app->user->can('approve-advance'))): ?>
                    <?= Html::a('<i class="fas fa-edit"></i> ยกเลิก', ['cancel', 'id' => $model->id], [
                        'class' => 'btn btn-outline-warning'
                    ]) ?>
                <?php endif; ?>

                <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                    'class' => 'btn btn-outline-info',
                    'target' => '_blank'
                ]) ?>

                <?php if ($model->status === 'approved'): ?>
                    <?php if ($model->is_recall != 1): ?>
                        <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#recall-modal" data-bs-toggle="modal" data-bs-target="#recall-modal">
                            <i class="fas fa-undo-alt"></i> ร้องขอแก้ไข (Recall)
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($model->is_recall == 1): ?>
                    <?php if (\Yii::$app->user->can('approve-advance') || \backend\models\User::isUserAdmin()): ?>
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
                        <span class="badge bg-warning text-dark p-2 align-self-center ml-2"><i class="fas fa-hourglass-half"></i> อยู่ระหว่างรออนุมัติ Recall จากผู้มีอำนาจ</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($model->is_recall == 1): ?>
        <div class="alert alert-warning border border-warning shadow-sm mb-3">
            <h5><i class="fas fa-exclamation-triangle"></i> เอกสารนี้มีคำร้องขอ Recall เพื่อแก้ไข</h5>
            <p class="mb-1"><strong>ผู้ร้องขอ:</strong> <?= \backend\models\User::findEmployeeNameByUserId($model->recall_by) ?> (วันที่: <?= $model->recall_at ? date('d/m/Y H:i:s', strtotime($model->recall_at)) : '-' ?>)</p>
            <p class="mb-0"><strong>เหตุผล:</strong> <?= Html::encode($model->recall_reason) ?></p>
        </div>
    <?php endif; ?>

<!--            --><?php //if ($model->status !== 'approved' && \Yii::$app->user->can('approve-advance')): ?>
<!--                <div class="btn-group" role="group">-->
<!--                    --><?php //= Html::a('<i class="fas fa-check"></i> อนุมัติ', ['approve', 'id' => $model->id], [
//                        'class' => 'btn btn-success',
//                        'data' => [
//                            'confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบเบิกนี้?',
//                            'method' => 'post',
//                        ],
//                    ]) ?>
<!---->
<!--                    --><?php //= Html::a('<i class="fas fa-times"></i> ปฏิเสธ', ['reject', 'id' => $model->id], [
//                        'class' => 'btn btn-danger',
//                        'data' => [
//                            'confirm' => 'คุณแน่ใจหรือไม่ที่จะปฏิเสธใบเบิกนี้?',
//                            'method' => 'post',
//                        ],
//                    ]) ?>
<!--                </div>-->
<!--            --><?php //endif; ?>
        </div>
    </div>

    <!-- แสดงข้อมูลหลัก -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> รายละเอียดใบเบิกเงินทดแทน
                    </h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                        'attributes' => [
                            [
                                'attribute' => 'advance_no',
                                'label' => 'เลขที่ใบเบิก',
                                'format' => 'raw',
                                'value' => '<strong>' . $model->advance_no . '</strong>'
                            ],
                            [
                                'attribute' => 'request_date',
                                'label' => 'วันที่เบิก',
                                'value'=>function($data){
                                    return date('m-d-Y',strtotime($data->request_date));
                                }
                            ],
                            [
                                'attribute' => 'employee_id',
                                'label' => 'พนักงานที่เบิก',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if ($model->employee) {
                                        return '<i class="fas fa-user"></i> ' . $model->employee->fname . ' ' . $model->employee->lname;
                                    }
                                    return '-';
                                }
                            ],
                            [
                                'attribute' => 'amount',
                                'label' => 'จำนวนเงิน',
                                'format' => 'raw',
                                'value' => '<h4 class="text-success mb-0">' . number_format($model->amount, 2) . ' บาท</h4>'
                            ],
                            [
                                'attribute' => 'purpose',
                                'label' => 'วัตถุประสงค์',
                                'format' => 'ntext'
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'สถานะ',
                                'format' => 'raw',
                                'value' => '<span class="badge badge-' . $status['class'] . ' badge-lg">' . $status['label'] . '</span>'
                            ],
                            [
                                'attribute' => 'approved_by',
                                'label' => 'ผู้อนุมัติ',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if ($model->approver) {
                                        return '<i class="fas fa-user-check"></i> ' . $model->approver->fname . ' ' . $model->approver->lname;
                                    }
                                    return '-';
                                }
                            ],
                            [
                                'attribute' => 'remarks',
                                'label' => 'หมายเหตุ',
                                'format' => 'ntext'
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- สถานะวงเงิน -->
            <?php
            $currentBalance = \backend\models\PettyCashAdvance::getCurrentBalance();
            $maxAmount = \backend\models\PettyCashAdvance::getMaxAmount();
            $minAmount = \backend\models\PettyCashAdvance::getMinAmount();
            $needsRefill = \backend\models\PettyCashAdvance::needsRefill();
            ?>

            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-wallet"></i> สถานะวงเงินปัจจุบัน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="<?= $needsRefill ? 'text-warning' : 'text-success' ?>">
                            <?= number_format($currentBalance, 2) ?> บาท
                        </h3>
                        <small class="text-muted">ยอดคงเหลือ</small>
                    </div>

                    <?php
                    $percentage = ($currentBalance / $maxAmount) * 100;
                    $progressClass = $percentage <= 10 ? 'danger' : ($percentage <= 30 ? 'warning' : 'success');
                    ?>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-<?= $progressClass ?>"
                             style="width: <?= $percentage ?>%"></div>
                    </div>

                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted">สูงสุด</small><br>
                            <strong><?= number_format($maxAmount, 2) ?></strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">ขั้นต่ำ</small><br>
                            <strong class="text-danger"><?= number_format($minAmount, 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ข้อมูลเพิ่มเติม -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-clock"></i> ข้อมูลการสร้าง
                    </h6>
                </div>
                <div class="card-body">
                    <p><strong>สร้างเมื่อ:</strong><br>
                        <?= date('m-d-Y H:i:s', $model->created_at) ?></p>

                    <p><strong>แก้ไขล่าสุด:</strong><br>
                        <?= date('m-d-Y H:i:s', $model->updated_at) ?></p>

                    <?php if ($model->created_by): ?>
                        <p><strong>ผู้สร้าง:</strong><br>
                            <?= \backend\models\User::findIdentity($model->created_by)->username ?? 'ไม่ระบุ' ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline สถานะ (ถ้ามีข้อมูล) -->
    <?php if (!$model->isNewRecord): ?>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history"></i> Timeline การดำเนินงาน
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6>สร้างใบเบิกเงินทดแทน</h6>
                                    <p class="text-muted mb-0">
                                        <?= \Yii::$app->formatter->asDatetime($model->created_at) ?>
                                    </p>
                                </div>
                            </div>

                            <?php if ($model->status !== 'pending'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-<?= $status['class'] ?>"></div>
                                    <div class="timeline-content">
                                        <h6><?= $status['label'] ?></h6>
                                        <p class="text-muted mb-0">
                                            <?= \Yii::$app->formatter->asDatetime($model->updated_at) ?>
                                            <?php if ($model->approver): ?>
                                                <br>โดย: <?= $model->approver->fname . ' ' . $model->approver->lname ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<style>
    .badge-lg {
        font-size: 1.1em;
        padding: 8px 16px;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }

    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 0;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .timeline-content h6 {
        margin-bottom: 5px;
        color: #333;
    }

    .progress {
        height: 8px;
    }

    .card {
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .detail-view th {
        background-color: #f8f9fa;
        width: 30%;
    }
</style>

<!-- Modal สำหรับ ร้องขอ Recall -->
<?php
\yii\bootstrap4\Modal::begin([
    'id' => 'recall-modal',
    'title' => '<h4><i class="fas fa-undo-alt text-warning"></i> ร้องขอแก้ไขใบเบิกเงินทดแทนสดย่อย (Recall)</h4>',
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
        <label for="pca-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ต้องการดึงเอกสารกลับมาแก้ไข <span class="text-danger">*</span></label>
        <textarea name="recall_reason" id="pca-recall-reason" class="form-control" rows="4" required placeholder="ตัวอย่าง: แก้ไขยอดเงินเบิกทดแทน, เปลี่ยนแปลงวัตถุประสงค์"></textarea>
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
        <label for="pca-reject-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ไม่อนุมัติคำร้องขอ Recall</label>
        <textarea name="reject_reason" id="pca-reject-recall-reason" class="form-control" rows="3" placeholder="ระบุเหตุผลที่ไม่อนุมัติ"></textarea>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
    <button type="submit" class="btn btn-danger"><i class="fas fa-check"></i> ยืนยันปฏิเสธ Recall</button>
</div>
<?php \yii\widgets\ActiveForm::end(); ?>
<?php \yii\bootstrap4\Modal::end(); ?>