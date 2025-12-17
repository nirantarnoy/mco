<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\DeliveryNote */

$this->title = $model->dn_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบตรวจรับ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="delivery-note-view">

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], ['class' => 'btn btn-info', 'target' => '_blank']) ?>
        <?= Html::a('<i class="fas fa-file-excel"></i> Export Excel', ['export-excel', 'id' => $model->id], ['class' => 'btn btn-success', 'target' => '_blank']) ?>
        <?= Html::a('<i class="fas fa-file-pdf"></i> Export PDF', ['export-pdf', 'id' => $model->id], ['class' => 'btn btn-danger', 'target' => '_blank']) ?>
    </p>

    <div class="row">
        <div class="col-md-6">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'dn_no',
                    'date',
                    [
                        'attribute' => 'job_id',
                        'value' => $model->job ? $model->job->job_no : '-',
                    ],
                    'customer_name',
                    'address:ntext',
                    'note:ntext',
                ],
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'attn',
                    'our_ref',
                    'from_name',
                    'tel',
                    'ref_no',
                    'page_no',
                ],
            ]) ?>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title mb-0">รายการสินค้า</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>ITEM</th>
                        <th>DESCRIPTION</th>
                        <th>P/N</th>
                        <th class="text-right">Q'TY</th>
                        <th>UNIT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->deliveryNoteLines as $line): ?>
                        <tr>
                            <td><?= Html::encode($line->item_no) ?></td>
                            <td><?= nl2br(Html::encode($line->description)) ?></td>
                            <td><?= Html::encode($line->part_no) ?></td>
                            <td class="text-right"><?= Html::encode($line->qty) ?></td>
                            <td><?= $line->unit ? Html::encode($line->unit->name) : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>