<?php
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Pre-Advances';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pre-advance-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('Create Pre-Advance', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

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
