<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\JournalTrans;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Aricat Transaction';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="journal-trans-index">
    <p>
        <!--        --><?php //= Html::a('<i class="fa fa-plus"></i> PO Receive', ['create', 'type' => JournalTransX::TRANS_TYPE_PO_RECEIVE], ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fa fa-plus"></i> สร้างรายการใหม่', ['create', 'type' => JournalTrans::TRANS_TYPE_ARICAT_NEW], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="row">
        <div class="col-md-12">
            <?php Pjax::begin(); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn', 'headerOptions' => ['style' => 'width: 30px;']],

                    [
                        'attribute' => 'journal_no',
                        'headerOptions' => ['style' => 'width: 100px;text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a($model->journal_no, ['view', 'id' => $model->id], ['class' => 'btn btn-link btn-sm']);
                        }
                    ],

                    [
                        'attribute' => 'trans_date',
                        // 'format' => 'date',
                        'headerOptions' => ['style' => 'width: 120px;text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'value' => function ($model) {
                            return date('d-m-Y', strtotime($model->trans_date));
                        }
                    ],
                    [
                        'attribute' => 'agency_id',
                        'headerOptions' => ['style' => 'width: 150px;text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'value' => function ($model) {
                            return \backend\models\Agency::findName($model->agency_id);
                        }
                    ],

                    [
                        'attribute' => 'employer_id',
                        'headerOptions' => ['style' => 'width: 80px;text-align: center;'],
                        'contentOptions' => ['class' => 'text-center'],
                        'value' => function ($model) {
                            return \backend\models\Employer::findName($model->employer_id);
                        }
                    ],

                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return '<div class="badge badge-pill badge-success" style="padding: 10px;">' . 'Complete' . '</div>';
                        },
                        'headerOptions' => ['style' => 'width: 100px;text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],

//                    [
//                        'attribute' => 'created_at',
//                        'format' => 'datetime',
//                        'headerOptions' => ['style' => 'width: 150px;'],
//                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                     //   'template' => '{view} {update} {approve} {delete}',
                        'template' => '{view} {update} {delete}',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fa fa-eye"></i>', ['view', 'id' => $model->id], [
                                    'class' => 'btn btn-xs btn-info',
                                    'title' => 'View',
                                ]);
                            },
                            'update' => function ($url, $model, $key) {
                                if ($model->status === JournalTrans::STATUS_DRAFT) {
                                    return Html::a('<i class="fa fa-edit"></i>', ['update', 'id' => $model->id], [
                                        'class' => 'btn btn-xs btn-primary',
                                        'title' => 'Update',
                                    ]);
                                }
                                return '';
                            },
//                            'approve' => function ($url, $model, $key) {
//                                if ($model->status === JournalTrans::STATUS_DRAFT) {
//                                    return Html::a('<i class="fa fa-check"></i>', ['approve', 'id' => $model->id], [
//                                        'class' => 'btn btn-xs btn-success',
//                                        'title' => 'Approve',
//                                        'data' => [
//                                            'confirm' => 'Are you sure you want to approve this transaction?',
//                                            'method' => 'post',
//                                        ],
//                                    ]);
//                                }
//                                return '';
//                            },
                            'delete' => function ($url, $model, $key) {
                                if ($model->status === JournalTrans::STATUS_DRAFT) {
                                    return Html::a('<i class="fa fa-trash"></i>', ['delete', 'id' => $model->id], [
                                        'class' => 'btn btn-xs btn-danger',
                                        'title' => 'Delete',
                                        'data' => [
                                            'confirm' => 'Are you sure you want to delete this transaction?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                }
                                return '';
                            },
                        ],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>

    <!-- Statistics Cards -->
</div>

<style>
    .info-box {
        display: block;
        min-height: 90px;
        background: #fff;
        width: 100%;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        border-radius: 2px;
        margin-bottom: 15px;
    }

    .info-box-icon {
        border-top-left-radius: 2px;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 2px;
        display: block;
        float: left;
        height: 90px;
        width: 90px;
        text-align: center;
        font-size: 45px;
        line-height: 90px;
        background: rgba(0, 0, 0, 0.2);
    }

    .info-box-content {
        padding: 5px 10px;
        margin-left: 90px;
    }

    .info-box-text {
        text-transform: uppercase;
        font-weight: bold;
        font-size: 13px;
        display: block;
    }

    .info-box-number {
        display: block;
        font-weight: bold;
        font-size: 18px;
    }

    .bg-aqua {
        background-color: #00c0ef !important;
        color: #fff;
    }

    .bg-red {
        background-color: #dd4b39 !important;
        color: #fff;
    }

    .bg-yellow {
        background-color: #f39c12 !important;
        color: #fff;
    }

    .bg-green {
        background-color: #00a65a !important;
        color: #fff;
    }
</style>