<?php
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

$this->title = $model->pre_advance_no;
$this->params['breadcrumbs'][] = ['label' => 'Pre-Advances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="pre-advance-view">

    

    <p>
        <?php if (empty($model->approved_by)): ?>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
        <?= Html::a('Print', ['print', 'id' => $model->id], ['class' => 'btn btn-info', 'target' => '_blank']) ?>
        <?= Html::a('สร้างใบหัก ณ ที่จ่าย (WHT)', ['/wht/create', 'ref_type' => 'PRE-ADVANCE', 'ref_id' => $model->id], ['class' => 'btn btn-warning', 'target' => '_blank']) ?>
        
        <?php 
        $isChecker = \backend\models\User::isUserAdmin() || Yii::$app->user->can('pre_advance_checker') || Yii::$app->user->can('checker');
        $isApprover = \backend\models\User::isUserAdmin() || Yii::$app->user->can('pre_advance_approver') || Yii::$app->user->can('approver');
        ?>
        
        <?php if ($isChecker): ?>
            <?php if (empty($model->checked_by)): ?>
                <?= Html::a('ตรวจสอบรายการ', ['check', 'id' => $model->id], [
                    'class' => 'btn btn-success',
                    'data' => [
                        'confirm' => 'ยืนยันการตรวจสอบรายการนี้?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php else: ?>
                <?= Html::a('ยกเลิกตรวจสอบ', ['uncheck', 'id' => $model->id], [
                    'class' => 'btn btn-secondary',
                    'data' => [
                        'confirm' => 'ยืนยันการยกเลิกตรวจสอบรายการนี้?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($isApprover): ?>
            <?php if (empty($model->approved_by)): ?>
                <?= Html::a('อนุมัติรายการ', ['approve', 'id' => $model->id], [
                    'class' => 'btn btn-success',
                    'data' => [
                        'confirm' => 'ยืนยันการอนุมัติรายการนี้?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php else: ?>
                <?= Html::a('ยกเลิกอนุมัติ', ['unapprove', 'id' => $model->id], [
                    'class' => 'btn btn-secondary',
                    'data' => [
                        'confirm' => 'ยืนยันการยกเลิกอนุมัติรายการนี้?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($model->approved_by) && $model->is_recall != 1): ?>
            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#recall-modal" data-bs-toggle="modal" data-bs-target="#recall-modal">
                <i class="fas fa-undo-alt"></i> ร้องขอแก้ไข (Recall)
            </button>
        <?php endif; ?>

        <?php if ($model->is_recall == 1): ?>
            <?php if ($isApprover): ?>
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
                <span class="badge badge-warning bg-warning text-dark p-2 align-self-center"><i class="fas fa-hourglass-half"></i> อยู่ระหว่างรออนุมัติ Recall จากผู้มีอำนาจ</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (empty($model->approved_by)): ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
    </p>

    <?php if ($model->is_recall == 1): ?>
        <div class="alert alert-warning border border-warning shadow-sm">
            <h5><i class="fas fa-exclamation-triangle"></i> เอกสารนี้มีคำร้องขอ Recall เพื่อแก้ไข</h5>
            <p class="mb-1"><strong>ผู้ร้องขอ:</strong> <?= \backend\models\User::findEmployeeNameByUserId($model->recall_by) ?> (วันที่: <?= $model->recall_at ? date('d/m/Y H:i:s', strtotime($model->recall_at)) : '-' ?>)</p>
            <p class="mb-0"><strong>เหตุผล:</strong> <?= Html::encode($model->recall_reason) ?></p>
        </div>
    <?php endif; ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'pre_advance_no',
            'trans_date',
            [
                'attribute' => 'vendor_id',
                'value' => $model->vendor ? $model->vendor->name : '-',
            ],
            [
                'label' => 'ใบสั่งซื้อ / None PR อ้างอิง',
                'value' => function($model) {
                    $refs = $model->preAdvanceRefs;
                    if (empty($refs)) return '-';
                    $labels = [];
                    foreach ($refs as $ref) {
                        if ($ref->ref_type == \backend\models\PreAdvanceRef::REF_TYPE_NONE_PR) {
                            $m = \backend\models\PurchaseMaster::findOne($ref->ref_id);
                            if ($m) $labels[] = '[None PR] ' . $m->docnum;
                        } elseif ($ref->ref_type == \backend\models\PreAdvanceRef::REF_TYPE_PO) {
                            $m = \backend\models\Purch::findOne($ref->ref_id);
                            if ($m) $labels[] = '[PO] ' . $m->purch_no;
                        }
                    }
                    return !empty($labels) ? implode(', ', $labels) : '-';
                }
            ],
            'amount:decimal',
            'remark:ntext',
            [
                'attribute' => 'checked_by',
                'value' => function($model) {
                    if (empty($model->checked_by)) return '-';
                    $empName = \backend\models\User::findEmployeeNameByUserId($model->checked_by);
                    return !empty($empName) ? $empName : \backend\models\User::findName($model->checked_by);
                }
            ],
            'checked_at:datetime',
            [
                'attribute' => 'approved_by',
                'value' => function($model) {
                    if (empty($model->approved_by)) return '-';
                    $empName = \backend\models\User::findEmployeeNameByUserId($model->approved_by);
                    return !empty($empName) ? $empName : \backend\models\User::findName($model->approved_by);
                }
            ],
            'approved_at:datetime',
            [
                'attribute' => 'recall_reason',
                'label' => 'เหตุผลการขอ Recall',
                'format' => 'ntext',
                'visible' => $model->is_recall == 1 || !empty($model->recall_reason),
            ],
            [
                'attribute' => 'recall_by',
                'label' => 'ผู้ขอ Recall',
                'value' => function ($model) {
                    return \backend\models\User::findEmployeeNameByUserId($model->recall_by);
                },
                'visible' => $model->is_recall == 1 || !empty($model->recall_by),
            ],
            [
                'attribute' => 'recall_at',
                'label' => 'เวลาที่ขอ Recall',
                'format' => 'datetime',
                'visible' => $model->is_recall == 1 || !empty($model->recall_at),
            ],
        ],
    ]) ?>

    <h3>บันทึกรายการตั้งเบิก</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ลำดับ</th>
                <th>วันที่</th>
                <th>รายละเอียดการขออนุมัติค่าใช้จ่าย/ตั้งเบิก</th>
                <th class="text-right">ยอดเบิก</th>
                <th>หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model->preAdvanceLines as $index => $line): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= Html::encode($line->line_date) ?></td>
                    <td><?= Html::encode($line->description) ?></td>
                    <td class="text-right"><?= number_format($line->amount, 2) ?></td>
                    <td><?= Html::encode($line->remark) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">ยอดรวมทั้งหมด</th>
                <th class="text-right"><?= number_format($model->amount, 2) ?></th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <!-- Action Log History Section -->
    <?php 
    $userId = \Yii::$app->user->id;
    $isAdmin = \backend\models\User::isUserAdmin();
    $isMcoAdmin = \backend\models\User::checkhasrole($userId, 'mcoadmin');
    $isExactAdmin = \backend\models\User::checkhasrole($userId, 'admin');
    if ($isAdmin || $isMcoAdmin || $isExactAdmin): 
    ?>
    <div class="card mt-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="card-title mb-0"><i class="fas fa-history"></i> ประวัติการทำรายการ Payment Advance</h5>
            <div class="card-tools d-flex align-items-center gap-2">
                <a href="<?= \yii\helpers\Url::to(['action-log/index', 'ActionLogSearchModel[model_class]' => get_class($model), 'ActionLogSearchModel[model_id]' => $model->id]) ?>" class="btn btn-xs btn-outline-light btn-sm text-white" target="_blank" title="ดูบันทึกเหตุการณ์ทั้งหมดของระบบ">
                    <i class="fas fa-external-link-alt"></i> เปิดหน้า Action Log กลาง
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php
            $rawLogs = \backend\models\ActionLogModel::find()
                ->where(['model_class' => get_class($model), 'model_id' => $model->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();

            // รายการที่มีการเปลี่ยนแปลงข้อมูลเนื้อหาจริงๆ
            $filteredLogs = array_filter($rawLogs, function($log) {
                if ($log->action === 'CONTROLLER_ACTION') {
                    $actionId = $log->action_method ?? '';
                    if (empty($actionId) && !empty($log->data)) {
                        $data = $log->getFormattedData();
                        $actionId = $data['action_id'] ?? '';
                    }
                    if (in_array($actionId, ['view', 'print', 'update'])) {
                        return false;
                    }
                }

                if ($log->action === 'MODEL_UPDATE') {
                    $data = $log->getFormattedData();
                    $changes = $data['changed_attributes'] ?? [];
                    $meaningfulChanges = array_filter($changes, function($val, $key) {
                        return !in_array($key, ['updated_at', 'updated_by']);
                    }, ARRAY_FILTER_USE_BOTH);

                    if (empty($meaningfulChanges)) {
                        return false;
                    }
                }

                return true;
            });
            ?>

            <!-- แท็บสลับการดู: เฉพาะที่มีการเปลี่ยนค่า (Default) vs ดูประวัติทั้งหมดอย่างละเอียด -->
            <ul class="nav nav-pills mb-3" id="preAdvanceLogTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active btn-sm" id="filtered-tab" data-bs-toggle="pill" data-bs-target="#filtered-log-pane" data-toggle="pill" href="#filtered-log-pane" role="tab" type="button">
                        <i class="fas fa-edit"></i> รายการที่มีการเปลี่ยนแปลงค่า (<?= count($filteredLogs) ?>)
                    </button>
                </li>
                <li class="nav-item ms-1">
                    <button class="nav-link btn-sm" id="all-tab" data-bs-toggle="pill" data-bs-target="#all-log-pane" data-toggle="pill" href="#all-log-pane" role="tab" type="button">
                        <i class="fas fa-list-ul"></i> ประวัติทั้งหมดอย่างละเอียดสำหรับ Admin (<?= count($rawLogs) ?>)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="preAdvanceLogTabContent">
                <!-- Tab 1: รายการที่มีการเปลี่ยนค่าจริงๆ (Default) -->
                <div class="tab-pane fade show active" id="filtered-log-pane" role="tabpanel">
                    <?php if (!empty($filteredLogs)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" style="font-size: 13px; white-space: nowrap;">
                                <thead>
                                    <tr>
                                        <th>เวลา</th>
                                        <th>ผู้ใช้งาน</th>
                                        <th>การกระทำ</th>
                                        <th>รายละเอียดเพิ่มเติม</th>
                                        <th>Diff (เปรียบเทียบ)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filteredLogs as $log): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?></td>
                                            <td><?= Html::encode($log->username) ?></td>
                                            <td>
                                                <?php
                                                    if ($log->action == 'MODEL_CREATE') echo '<span class="badge bg-success">สร้าง</span>';
                                                    elseif ($log->action == 'MODEL_UPDATE') echo '<span class="badge bg-warning">แก้ไข</span>';
                                                    elseif ($log->action == 'MODEL_DELETE') echo '<span class="badge bg-danger">ลบ</span>';
                                                    elseif ($log->action == 'REQUEST_RECALL') echo '<span class="badge bg-warning text-dark"><i class="fas fa-undo-alt"></i> ร้องขอ Recall</span>';
                                                    elseif ($log->action == 'APPROVE_RECALL') echo '<span class="badge bg-success"><i class="fas fa-check"></i> อนุมัติ Recall</span>';
                                                    elseif ($log->action == 'REJECT_RECALL') echo '<span class="badge bg-danger"><i class="fas fa-times"></i> ปฏิเสธ Recall</span>';
                                                    else echo '<span class="badge bg-info">'.Html::encode($log->action).'</span>';
                                                ?>
                                            </td>
                                            <td><?= Html::encode($log->message) ?></td>
                                            <td>
                                                <?php if ($log->action == 'MODEL_UPDATE'): ?>
                                                    <button type="button" class="btn btn-sm btn-info btn-view-diff" data-id="<?= $log->id ?>">
                                                        <i class="fas fa-exchange-alt"></i> ดูส่วนที่ถูกแก้
                                                    </button>
                                                <?php endif; ?>
                                                <a href="<?= \yii\helpers\Url::to(['action-log/view', 'id' => $log->id]) ?>" class="btn btn-sm btn-secondary" target="_blank">
                                                    <i class="fas fa-eye"></i> รายละเอียดแบบเต็ม
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">ยังไม่มีประวัติการแก้ไขเนื้อหา</div>
                    <?php endif; ?>
                </div>

                <!-- Tab 2: ประวัติทั้งหมดอย่างละเอียดสำหรับ Admin (รวมเข้าดู, กดพิมพ์, controller action) -->
                <div class="tab-pane fade" id="all-log-pane" role="tabpanel">
                    <?php if (!empty($rawLogs)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" style="font-size: 13px; white-space: nowrap;">
                                <thead>
                                    <tr>
                                        <th>เวลา</th>
                                        <th>ผู้ใช้งาน</th>
                                        <th>Action Type</th>
                                        <th>รายละเอียด / ข้อความ</th>
                                        <th>IP Address</th>
                                        <th>Diff / รายละเอียด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rawLogs as $log): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?></td>
                                            <td><?= Html::encode($log->username) ?></td>
                                            <td>
                                                <?php
                                                    if ($log->action == 'MODEL_CREATE') echo '<span class="badge bg-success">สร้าง (Model)</span>';
                                                    elseif ($log->action == 'MODEL_UPDATE') echo '<span class="badge bg-warning">แก้ไข (Model)</span>';
                                                    elseif ($log->action == 'MODEL_DELETE') echo '<span class="badge bg-danger">ลบ (Model)</span>';
                                                    elseif ($log->action == 'REQUEST_RECALL') echo '<span class="badge bg-warning text-dark"><i class="fas fa-undo-alt"></i> ร้องขอ Recall</span>';
                                                    elseif ($log->action == 'APPROVE_RECALL') echo '<span class="badge bg-success"><i class="fas fa-check"></i> อนุมัติ Recall</span>';
                                                    elseif ($log->action == 'REJECT_RECALL') echo '<span class="badge bg-danger"><i class="fas fa-times"></i> ปฏิเสธ Recall</span>';
                                                    elseif ($log->action == 'CONTROLLER_ACTION') echo '<span class="badge bg-primary">เข้าใช้งาน (' . Html::encode($log->action_method) . ')</span>';
                                                    else echo '<span class="badge bg-secondary">'.Html::encode($log->action).'</span>';
                                                ?>
                                            </td>
                                            <td><?= Html::encode($log->message) ?></td>
                                            <td><code><?= Html::encode($log->ip_address) ?></code></td>
                                            <td>
                                                <?php if ($log->action == 'MODEL_UPDATE'): ?>
                                                    <button type="button" class="btn btn-sm btn-info btn-view-diff" data-id="<?= $log->id ?>">
                                                        <i class="fas fa-exchange-alt"></i> ดูส่วนที่ถูกแก้
                                                    </button>
                                                <?php endif; ?>
                                                <a href="<?= \yii\helpers\Url::to(['action-log/view', 'id' => $log->id]) ?>" class="btn btn-sm btn-secondary" target="_blank">
                                                    <i class="fas fa-eye"></i> รายละเอียดแบบเต็ม
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">ไม่มีประวัติในระบบ</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Modal สำหรับแสดง Diff -->
<?php
Modal::begin([
    'id' => 'diff-modal',
    'title' => '<h4><i class="fas fa-exchange-alt"></i> รายละเอียดการเปลี่ยนแปลง</h4>',
    'size' => Modal::SIZE_LARGE,
    'options' => ['tabindex' => false],
    'closeButton' => [
        'class' => 'btn-close',
        'data-bs-dismiss' => 'modal',
        'aria-label' => 'Close',
    ],
]);

echo '<div id="diff-modal-content"></div>';

Modal::end();
?>

<!-- Modal สำหรับ ร้องขอ Recall -->
<?php
Modal::begin([
    'id' => 'recall-modal',
    'title' => '<h4><i class="fas fa-undo-alt text-warning"></i> ร้องขอแก้ไขเอกสาร Pre-Advance (Recall)</h4>',
    'size' => Modal::SIZE_DEFAULT,
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
        <label for="preadvance-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ต้องการดึงเอกสารกลับมาแก้ไข <span class="text-danger">*</span></label>
        <textarea name="recall_reason" id="preadvance-recall-reason" class="form-control" rows="4" required placeholder="ตัวอย่าง: มีการเปลี่ยนแปลงยอดเงิน, แก้ไขรายการขออนุมัติค่าใช้จ่าย"></textarea>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
    <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane"></i> ยืนยันส่งคำร้องขอ Recall</button>
</div>
<?php \yii\widgets\ActiveForm::end(); ?>
<?php
Modal::end();
?>

<!-- Modal สำหรับ ปฏิเสธ Recall -->
<?php
Modal::begin([
    'id' => 'reject-recall-modal',
    'title' => '<h4><i class="fas fa-times text-danger"></i> ปฏิเสธคำร้องขอ Recall</h4>',
    'size' => Modal::SIZE_DEFAULT,
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
        <label for="preadvance-reject-recall-reason" class="form-label fw-bold">ระบุเหตุผลที่ไม่อนุมัติคำร้องขอ Recall</label>
        <textarea name="reject_reason" id="preadvance-reject-recall-reason" class="form-control" rows="3" placeholder="ระบุเหตุผลที่ไม่อนุมัติ"></textarea>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">ยกเลิก</button>
    <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> ยืนยันปฏิเสธ Recall</button>
</div>
<?php \yii\widgets\ActiveForm::end(); ?>
<?php
Modal::end();
?>

<?php
$url_to_showdiff = \yii\helpers\Url::to(['pre-advance/view-diff'], true);
$this->registerJs(<<<JS
    // เปิด Modal แสดง Diff
    $(document).on('click', '.btn-view-diff', function() {
        var id = $(this).data('id');
        var modal = $('#diff-modal');
        
        modal.find('#diff-modal-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">กำลังโหลด...</p></div>');
        modal.modal('show');
        
        $.ajax({
            url: '$url_to_showdiff',
            type: 'GET',
            data: {id: id},
            success: function(response) {
                modal.find('#diff-modal-content').html(response);
            },
            error: function() {
                modal.find('#diff-modal-content').html('<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>');
            }
        });
    });
JS
);
?>
