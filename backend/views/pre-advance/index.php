<?php
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Pre-Advances';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pre-advance-index">

    <?php
    $currentUserId = Yii::$app->user->id;
    $isAdmin = \backend\models\User::isUserAdmin();
    $returnedPas = [];
    try {
        \backend\models\PreAdvance::ensureRejectColumns();
        $returnedPaQuery = \backend\models\PreAdvance::find()
            ->where(['status' => \backend\models\PreAdvance::STATUS_RETURNED]);
        if (!$isAdmin) {
            $returnedPaQuery->andWhere(['created_by' => $currentUserId]);
        }
        $returnedPas = $returnedPaQuery->orderBy(['id' => SORT_DESC])->all();
    } catch (\Exception $e) {
        \Yii::error('Error loading returned PAs: ' . $e->getMessage());
    }
    ?>
    <?php if (!empty($returnedPas)): ?>
    <div class="alert alert-warning border border-warning shadow-sm mb-4" role="alert" style="border-left: 5px solid #ffc107 !important; background-color: #fff9e6;">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
            <div class="d-flex align-items-center mb-1">
                <span class="badge badge-warning bg-warning text-dark p-2 me-2 mr-2" style="font-size: 0.95rem;">
                    <i class="fas fa-undo-alt"></i> งานตีกลับแก้ไข
                </span>
                <h5 class="alert-heading mb-0 font-weight-bold fw-bold text-dark">
                    <?= $isAdmin ? 'มี Payment Advance (PA) ที่ถูกส่งกลับแก้ไข' : 'คุณมี Payment Advance (PA) ที่ถูกส่งกลับแก้ไข' ?> 
                    <span class="badge badge-danger bg-danger text-white"><?= count($returnedPas) ?> รายการ</span>
                </h5>
            </div>
            <small class="text-muted"><i class="fas fa-info-circle"></i> กดปุ่ม "แก้ไขทันที" เพื่อเปิดฟอร์มแก้ไขและส่งตรวจสอบใหม่</small>
        </div>
        
        <div class="table-responsive bg-white rounded shadow-sm border mt-2">
            <table class="table table-hover table-striped mb-0 align-middle" style="font-size: 13px;">
                <thead class="thead-dark bg-dark text-white">
                    <tr>
                        <th style="width: 160px;">เลขที่เอกสาร (PA)</th>
                        <th style="width: 180px;">ผู้รับเงิน / Vendor</th>
                        <th>รายละเอียดที่ต้องปรับปรุง/แก้ไข</th>
                        <?php if ($isAdmin): ?>
                            <th style="width: 140px;">ผู้จัดทำ</th>
                        <?php endif; ?>
                        <th style="width: 140px;">ผู้ส่งกลับ</th>
                        <th style="width: 130px;">เวลาที่ส่งกลับ</th>
                        <th style="width: 160px; text-align: center;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($returnedPas as $rPa): ?>
                    <tr>
                        <td class="font-weight-bold fw-bold align-middle">
                            <span class="badge badge-primary bg-primary text-white me-1"><i class="fas fa-money-check-alt"></i></span>
                            <?= Html::a(Html::encode($rPa->pre_advance_no), ['view', 'id' => $rPa->id], ['class' => 'font-weight-bold fw-bold text-primary', 'target' => '_blank', 'title' => 'ดูรายละเอียด']) ?>
                        </td>
                        <td class="align-middle"><?= Html::encode($rPa->recipient_name ?: ($rPa->vendor ? $rPa->vendor->name : '-')) ?></td>
                        <td class="align-middle text-danger font-weight-bold" style="white-space: pre-wrap;">
                            <i class="fas fa-comment-dots text-warning"></i> <?= Html::encode($rPa->reject_reason ?: 'ไม่ระบุรายละเอียด') ?>
                        </td>
                        <?php if ($isAdmin): ?>
                            <td class="align-middle"><i class="fas fa-user-edit text-muted"></i> <?= Html::encode(\backend\models\User::findEmployeeNameByUserId($rPa->created_by) ?: '-') ?></td>
                        <?php endif; ?>
                        <td class="align-middle"><i class="fas fa-user-check text-muted"></i> <?= Html::encode(\backend\models\User::findEmployeeNameByUserId($rPa->rejected_by) ?: '-') ?></td>
                        <td class="align-middle text-muted"><?= $rPa->rejected_at ? date('d/m/Y H:i', strtotime($rPa->rejected_at)) : '-' ?></td>
                        <td class="align-middle text-center" style="white-space: nowrap;">
                            <a href="<?= \yii\helpers\Url::to(['update', 'id' => $rPa->id]) ?>" class="btn btn-sm btn-warning text-dark font-weight-bold shadow-sm">
                                <i class="fas fa-edit"></i> แก้ไขทันที
                            </a>
                            <a href="<?= \yii\helpers\Url::to(['view', 'id' => $rPa->id]) ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eye"></i> ดู
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <?= Html::a('Create Pre-Advance', ['create'], ['class' => 'btn btn-success']) ?>
        <form action="<?= \yii\helpers\Url::to(['index']) ?>" method="get" class="form-inline">
            <input type="hidden" name="r" value="pre-advance/index">
            <div class="input-group">
                <input type="text" name="globalSearch" class="form-control" placeholder="ค้นหา เลขที่ / ชื่อ Vendor..." value="<?= Html::encode($globalSearch ?? '') ?>" style="width: 250px;">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> ค้นหา</button>
                    <?php if(!empty($globalSearch)): ?>
                        <a href="<?= \yii\helpers\Url::to(['index']) ?>" class="btn btn-outline-secondary">ล้าง</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="table-responsive">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'trans_date',
            'pre_advance_no',
            [
                'attribute' => 'vendor_id',
                'value' => function ($model) {
                    return $model->vendor ? $model->vendor->name : '';
                }
            ],
            'recipient_name',
            [
                'attribute' => 'amount',
                'format' => ['decimal', 2],
            ],
            'remark',
            [
                'label' => 'การดำเนินงาน',
                'format' => 'raw',
                'headerOptions' => ['style' => 'width: 150px; text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center;'],
                'value' => function ($model) {
                    $seqData = $model->getDocumentSequenceData();
                    return '<div class="mb-1"><span class="badge badge-info">' . $seqData['latest'] . '</span></div>' .
                           '<button type="button" class="btn btn-xs btn-outline-secondary" onclick="showSeqModal(this)" data-html="' . htmlspecialchars($seqData['html'], ENT_QUOTES, 'UTF-8') . '"><i class="fas fa-list"></i> ลำดับ</button>';
                },
            ],
            [
                'label' => 'สถานะ',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->is_recall == 1) {
                        return '<span class="badge badge-warning bg-warning text-dark"><i class="fas fa-undo-alt"></i> ขอ Recall</span>';
                    } elseif ($model->status == \backend\models\PreAdvance::STATUS_REJECTED) {
                        return '<span class="badge badge-danger bg-danger"><i class="fas fa-ban"></i> ไม่อนุมัติ</span>';
                    } elseif ($model->status == \backend\models\PreAdvance::STATUS_RETURNED) {
                        return '<span class="badge badge-warning bg-warning text-dark"><i class="fas fa-undo"></i> ส่งกลับแก้ไข</span>';
                    } elseif (!empty($model->approved_by)) {
                        return '<span class="badge badge-success">อนุมัติแล้ว</span>';
                    } elseif (!empty($model->checked_by)) {
                        return '<span class="badge badge-info">ตรวจสอบแล้ว</span>';
                    } else {
                        return '<span class="badge badge-warning">รอตรวจสอบ</span>';
                    }
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {print} {delete}',
                'buttons' => [
                    'print' => function ($url, $model, $key) {
                        return Html::a('<span class="fas fa-print" aria-hidden="true"></span> Print', ['print', 'id' => $model->id], [
                            'title' => 'Print',
                            'class' => 'btn btn-sm btn-info',
                            'target' => '_blank',
                            'data-pjax' => '0'
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        if ($model->status == \backend\models\PreAdvance::STATUS_REJECTED && !\backend\models\User::isUserAdmin()) {
                            return '';
                        }
                        if (!empty($model->approved_by)) {
                            return ''; // Hide update if approved (optional but good practice)
                        }
                        return Html::a('<span class="fas fa-edit" aria-hidden="true"></span>', $url, [
                            'class' => 'btn btn-sm btn-primary'
                        ]);
                    },
                    'view' => function ($url, $model, $key) {
                        return Html::a('<span class="fas fa-eye" aria-hidden="true"></span>', $url, [
                            'class' => 'btn btn-sm btn-success'
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        if (!empty($model->approved_by)) {
                            return ''; // Prevent delete if approved
                        }
                        return Html::a('<span class="fas fa-trash-alt" aria-hidden="true"></span>', $url, [
                            'class' => 'btn btn-sm btn-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this item?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ]
            ],
        ],
    ]); ?>
    </div>
</div>

<style>
    .pre-advance-index .table {
        white-space: nowrap;
    }
</style>

<!-- Modal สำหรับแสดงลำดับการดำเนินงาน -->
<div class="modal fade" id="seqModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fs-6"><i class="fas fa-tasks"></i> ลำดับการดำเนินงาน</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body" id="seqModalBody">
      </div>
    </div>
  </div>
</div>
<script>
function showSeqModal(btn) {
    var html = $(btn).data('html');
    $('#seqModalBody').html(html);
    $('#seqModal').modal('show');
}
</script>
