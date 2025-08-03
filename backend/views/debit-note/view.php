<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'ใบเพิ่มหนี้ ' . $model->document_no;
$this->params['breadcrumbs'][] = ['label' => 'จัดการใบเพิ่มหนี้', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$model_line = \backend\models\DebitNoteItem::find()->where(['debit_note_id' => $model->id])->all();
?>

<div class="debit-note-view">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><?= Html::encode($this->title) ?></h4>
                    <div class="btn-group">
                        <?= Html::a('<i class="fas fa-edit"></i> แก้ไข',
                            ['update', 'id' => $model->id],
                            ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('<i class="fas fa-print"></i> Print',
                            ['print', 'id' => $model->id],
                            ['class' => 'btn btn-info', 'target' => '_blank']) ?>
                        <?= Html::a('<i class="fas fa-file-pdf"></i> PDF',
                            ['pdf', 'id' => $model->id],
                            ['class' => 'btn btn-danger', 'target' => '_blank']) ?>
                        <?= Html::a('<i class="fas fa-file-excel"></i> Excel',
                            ['excel', 'id' => $model->id],
                            ['class' => 'btn btn-success']) ?>
                        <?= Html::a('<i class="fas fa-list"></i> กลับ',
                            ['index'],
                            ['class' => 'btn btn-secondary']) ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-striped table-bordered'],
                                'attributes' => [
                                    'document_no:text:เลขที่ใบเพิ่มหนี้',
                                    [
                                        'attribute' => 'customer_id',
                                        'value' => function ($model) {
                                            return \backend\models\Customer::findName($model->customer_id);
                                        }
                                    ],
                                    [
                                        'attribute' => 'document_date',
                                        'value' => function ($model) {
                                            return \Yii::$app->formatter->asDate($model->document_date, 'php:m/d/Y');
                                        }
                                    ],
                                    'original_invoice_no:text:เลขที่ใบกำกับเดิม',
                                    'original_invoice_date:date:วันที่ใบกำกับเดิม',
                                    'reason:ntext:เหตุผลที่ต้องเพิ่มหนี้',
                                ],
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <h5>รายการสินค้า</h5>
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>รายการ</th>
                                    <th class="text-center">จำนวน</th>
                                    <th class="text-right">ราคา</th>
                                    <th class="text-right">รวม</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($model_line as $item): ?>
                                    <tr>
                                        <td><?= $item->item_no ?></td>
                                        <td><?= Html::encode($item->description) ?></td>
                                        <td class="text-center"><?= $item->quantity ?></td>
                                        <td class="text-right"><?= number_format($item->unit_price, 2) ?></td>
                                        <td class="text-right"><?= number_format($item->amount, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">รวมมูลค่าสินค้า:</th>
                                    <th class="text-right"><?= number_format($model->total_amount - $model->vat_amount, 2) ?></th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-right">ภาษีมูลค่าเพิ่ม 7%:</th>
                                    <th class="text-right"><?= number_format($model->vat_amount, 2) ?></th>
                                </tr>
                                <tr class="table-info">
                                    <th colspan="4" class="text-right">รวมเป็นเงินทั้งสิ้น:</th>
                                    <th class="text-right"><?= number_format($model->total_amount, 2) ?></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>