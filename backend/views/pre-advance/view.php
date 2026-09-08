<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->pre_advance_no;
$this->params['breadcrumbs'][] = ['label' => 'Pre-Advances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="pre-advance-view">

    

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
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

        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

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

</div>
