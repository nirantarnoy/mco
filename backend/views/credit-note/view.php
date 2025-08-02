<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\CreditNote */

$this->title = $model->document_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบลดหนี้', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');

    .credit-note-view {
        font-family: 'Prompt', sans-serif;
    }

    .action-buttons {
        margin-bottom: 20px;
    }

    .status-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
    }

    .status-draft {
        background-color: #ffc107;
        color: #000;
    }

    .status-approved {
        background-color: #28a745;
        color: #fff;
    }

    .status-cancelled {
        background-color: #dc3545;
        color: #fff;
    }

    .items-section {
        margin-top: 30px;
    }

    .detail-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
</style>

<div class="credit-note-view">

    <div class="d-flex justify-content-between align-items-center mb-3">

    </div>

    <div class="action-buttons">
        <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>

        <?php if ($model->status == $model::STATUS_DRAFT): ?>
            <?= Html::a('<i class="fas fa-check"></i> อนุมัติ', ['approve', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'ต้องการอนุมัติเอกสารนี้หรือไม่?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>

        <?php if ($model->status != $model::STATUS_CANCELLED): ?>
            <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['cancel', 'id' => $model->id], [
                'class' => 'btn btn-warning',
                'data' => [
                    'confirm' => 'ต้องการยกเลิกเอกสารนี้หรือไม่?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>

        <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
            'class' => 'btn btn-info',
            'target' => '_blank',
        ]) ?>

        <?= Html::a('<i class="fas fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'ต้องการลบเอกสารนี้หรือไม่?',
                'method' => 'post',
            ],
        ]) ?>
    </div>

    <div class="card">
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'document_no',
                    [
                        'attribute' => 'document_date',
                        'format' => ['date', 'php:d/m/Y']
                    ],
                    [
                        'attribute' => 'customer_id',
                        'value' => function($model) {
                            return $model->customer->customer_code . ' - ' . $model->customer->customer_name_th;
                        }
                    ],
                    'original_invoice_no',
                    [
                        'attribute' => 'original_invoice_date',
                        'format' => ['date', 'php:d/m/Y']
                    ],
                    'original_amount:decimal',
                    'actual_amount:decimal',
                    'reason:ntext',
                    'adjust_amount:decimal',
                    'vat_amount:decimal',
                    'total_amount:decimal',
                    'amount_text',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function($model) {
                            return Html::tag('span', $model->getStatusLabel(), [
                                'class' => 'status-badge status-' . $model->status
                            ]);
                        }
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => ['datetime', 'php:d/m/Y H:i:s']
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => ['datetime', 'php:d/m/Y H:i:s']
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <div class="items-section">
        <h3>รายการสินค้า</h3>
        <table class="table table-bordered detail-table">
            <thead>
            <tr>
                <th style="width: 60px;">ลำดับ</th>
                <th>รายการ</th>
                <th style="width: 100px;">จำนวน</th>
                <th style="width: 80px;">หน่วย</th>
                <th style="width: 120px;">ราคาต่อหน่วย</th>
                <th style="width: 120px;">ส่วนลด</th>
                <th style="width: 120px;">จำนวนเงิน</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($model->creditNoteItems as $index => $item): ?>
                <tr>
                    <td class="text-center"><?= $index + 1 ?></td>
                    <td><?= nl2br(Html::encode($item->description)) ?></td>
                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($item->quantity, 2) ?></td>
                    <td class="text-center"><?= Html::encode($item->unit) ?></td>
                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($item->unit_price, 2) ?></td>
                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($item->discount_amount, 2) ?></td>
                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($item->amount, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong>รวม</strong></td>
                <td class="text-right"><strong><?= Yii::$app->formatter->asDecimal($model->adjust_amount, 2) ?></strong></td>
            </tr>
            </tfoot>
        </table>
    </div>

</div>