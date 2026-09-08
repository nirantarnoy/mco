<?php
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Pre-Advances';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pre-advance-index">
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
                'label' => 'สถานะ',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!empty($model->approved_by)) {
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
