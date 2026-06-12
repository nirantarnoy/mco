<?php
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'รายการหัก ณ ที่จ่าย (WHT)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wht-index">
    <p>
        <?= Html::a('สร้างรายการ', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'trans_date',
            'wht_no',
            [
                'attribute' => 'vendor_id',
                'value' => function ($model) {
                    return $model->vendor ? $model->vendor->name : '-';
                }
            ],
            [
                'attribute' => 'wht_type',
                'value' => function ($model) {
                    return $model->wht_type == 3 ? 'ภ.ง.ด. 3' : 'ภ.ง.ด. 53';
                }
            ],
            'wht_desc',
            [
                'attribute' => 'base_amount',
                'format' => ['decimal', 2],
            ],
            [
                'attribute' => 'wht_percent',
                'value' => function ($model) {
                    return $model->wht_percent . '%';
                }
            ],
            [
                'attribute' => 'wht_amount',
                'format' => ['decimal', 2],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {print} {delete}',
                'buttons' => [
                    'print' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-print" aria-hidden="true"></span> Print', ['print', 'id' => $model->id], [
                            'title' => 'Print',
                            'class' => 'btn btn-sm btn-info',
                            'target' => '_blank',
                            'data-pjax' => '0'
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>', $url, [
                            'class' => 'btn btn-sm btn-primary'
                        ]);
                    },
                    'view' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>', $url, [
                            'class' => 'btn btn-sm btn-success'
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>', $url, [
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
