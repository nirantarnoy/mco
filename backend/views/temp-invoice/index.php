<?php

use backend\models\TempInvoice;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var backend\models\TempInvoiceSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'รายการสแกนใบแจ้งหนี้ (OCR)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="temp-invoice-index">

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus"></i> สแกนใหม่', ['ocr/index'], ['class' => 'btn btn-success btn-sm']) ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'tableOptions' => ['class' => 'table table-hover table-striped mb-0'],
                        'layout' => "{items}\n{summary}\n{pager}",
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            
                            [
                                'attribute' => 'invoice_number',
                                'label' => 'เลขที่เอกสาร',
                            ],
                            [
                                'attribute' => 'invoice_date',
                                'label' => 'วันที่',
                                'format' => 'date',
                            ],
                            [
                                'attribute' => 'vendor_name',
                                'label' => 'ผู้ขาย / แบรนด์',
                            ],
                            [
                                'attribute' => 'total_amount',
                                'label' => 'ยอดเงินรวม',
                                'headerOptions' => ['class' => 'text-right'],
                                'contentOptions' => ['class' => 'text-right'],
                                'value' => function($model) {
                                    return number_format($model->total_amount, 2);
                                }
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'สถานะ',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if ($model->status == 0) return '<span class="badge badge-warning">รอยืนยัน</span>';
                                    if ($model->status == 1) return '<span class="badge badge-success">ยืนยันแล้ว</span>';
                                    return '<span class="badge badge-danger">ยกเลิก</span>';
                                }
                            ],
                            [
                                'class' => ActionColumn::className(),
                                'template' => '{view} {delete}',
                                'urlCreator' => function ($action, TempInvoice $model, $key, $index, $column) {
                                    return Url::toRoute([$action, 'id' => $model->id]);
                                 }
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
