<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\PettyCashVoucher */

$this->title = $model->pcv_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบสำคัญจ่ายเงินสดย่อย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="petty-cash-voucher-view">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'target' => '_blank'
            ]) ?>
            <?= Html::a('<i class="fas fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                'data-method' => 'post',
            ]) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> กลับไปรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> ข้อมูลทั่วไป</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-bordered detail-view'],
                        'attributes' => [
                            'pcv_no',
                            [
                                'attribute' => 'date',
                                'value' => Yii::$app->formatter->asDate($model->date, 'dd/MM/yyyy'),
                            ],
                            'name',
                            [
                                'attribute' => 'amount',
                                'value' => number_format($model->amount, 2) . ' บาท',
                            ],
                            [
                                'attribute' => 'paid_for',
                                'value' => $model->paid_for ?: '-',
                                'format' => 'ntext',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-users"></i> ผู้ดำเนินการ</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-bordered detail-view'],
                        'attributes' => [
                            [
                                'attribute' => 'issued_by',
                                'value' => $model->issued_by ?: '-',
                            ],
                            [
                                'attribute' => 'issued_date',
                                'value' => $model->issued_date ? Yii::$app->formatter->asDate($model->issued_date, 'dd/MM/yyyy') : '-',
                            ],
                            [
                                'attribute' => 'approved_by',
                                'value' => $model->approved_by ?: '-',
                            ],
                            [
                                'attribute' => 'approved_date',
                                'value' => $model->approved_date ? Yii::$app->formatter->asDate($model->approved_date, 'dd/MM/yyyy') : '-',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-list"></i> รายละเอียดการจ่าย</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($model->details)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">A/C CODE</th>
                            <th width="10%">DATE</th>
                            <th width="30%">DETAIL</th>
                            <th width="12%">AMOUNT</th>
                            <th width="8%">VAT</th>
                            <th width="10%">VAT จำนวน</th>
                            <th width="8%">W/H</th>
                            <th width="8%">อื่นๆ</th>
                            <th width="12%">TOTAL</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $totalAmount = 0; ?>
                        <?php foreach ($model->details as $index => $detail): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= Html::encode($detail->ac_code) ?></td>
                                <td class="text-center">
                                    <?= $detail->detail_date ? Yii::$app->formatter->asDate($detail->detail_date, 'dd/MM/yyyy') : '-' ?>
                                </td>
                                <td><?= Html::encode($detail->detail) ?></td>
                                <td class="text-right"><?= number_format($detail->amount, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->vat, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->vat_amount, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->wht, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->other, 2) ?></td>
                                <td class="text-right font-weight-bold"><?= number_format($detail->total, 2) ?></td>
                            </tr>
                            <?php $totalAmount += $detail->total; ?>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                        <tr>
                            <th colspan="9" class="text-right">รวมทั้งหมด:</th>
                            <th class="text-right"><?= number_format($totalAmount, 2) ?> บาท</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted p-4">
                    <i class="fas fa-inbox fa-2x"></i><br>
                    ไม่มีรายละเอียดการจ่าย
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>