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

    <div class="row">
        <div class="col-md-8">
        </div>
        <div class="col-md-4 text-right">
            <span class="badge badge-<?= $status['class'] ?> badge-lg" style="font-size: 1.2em; padding: 8px 16px;">
                <?= $status['label'] ?>
            </span>
        </div>
    </div>

    <!-- ปุ่มจัดการ -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="btn-group mr-2" role="group">
                <?= Html::a('<i class="fas fa-list"></i> รายการทั้งหมด', ['index'], [
                    'class' => 'btn btn-outline-secondary'
                ]) ?>

                <?php if ($model->status === 'pending' || \Yii::$app->user->can('CanEditAccount') || \Yii::$app->user->can('approve-advance')): ?>
                    <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], [
                        'class' => 'btn btn-outline-primary'
                    ]) ?>
                <?php endif; ?>

                <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                    'class' => 'btn btn-outline-info',
                    'target' => '_blank'
                ]) ?>
            </div>

            <?php if ($model->status !== 'approved' && \Yii::$app->user->can('approve-advance')): ?>
                <div class="btn-group" role="group">
                    <?= Html::a('<i class="fas fa-check"></i> อนุมัติ', ['approve', 'id' => $model->id], [
                        'class' => 'btn btn-success',
                        'data' => [
                            'confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบเบิกนี้?',
                            'method' => 'post',
                        ],
                    ]) ?>

                    <?= Html::a('<i class="fas fa-times"></i> ปฏิเสธ', ['reject', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'คุณแน่ใจหรือไม่ที่จะปฏิเสธใบเบิกนี้?',
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            <?php endif; ?>
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
            $maxAmount = \backend\models\PettyCashAdvance::MAX_AMOUNT;
            $minAmount = \backend\models\PettyCashAdvance::MIN_AMOUNT;
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