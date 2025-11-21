<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use backend\models\JournalTrans;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Stock Transactions';
$this->params['breadcrumbs'][] = $this->title;

//echo \Yii::$app->session->get('company_id');
?>
<div class="journal-trans-index">
    <p>
        <!--        --><?php //= Html::a('<i class="fa fa-plus"></i> PO Receive', ['create', 'type' => JournalTransX::TRANS_TYPE_PO_RECEIVE], ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fa fa-minus"></i> เบิกสินค้า', ['createorigin', 'type' => JournalTrans::TRANS_TYPE_ISSUE_STOCK], ['class' => 'btn btn-danger']) ?>
        <?= Html::a('<i class="fa fa-undo"></i> คืนเบิกสินค้า', ['create', 'type' => JournalTrans::TRANS_TYPE_RETURN_ISSUE], ['class' => 'btn btn-info']) ?>
        <?= Html::a('<i class="fa fa-share"></i> ยืมสินค้า', ['createorigin', 'type' => JournalTrans::TRANS_TYPE_ISSUE_BORROW], ['class' => 'btn btn-warning']) ?>
        <?= Html::a('<i class="fa fa-reply"></i> คืนยืมสินค้า', ['create', 'type' => JournalTrans::TRANS_TYPE_RETURN_BORROW], ['class' => 'btn btn-primary']) ?>
    </p>

    <div class="row">
        <div class="col-md-12">
            <?php Pjax::begin(); ?>
            <?php echo $this->render('_search', ['model' => $searchModel,'viewstatus'=>null]); ?>
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
                        'attribute' => 'trans_type_id',
                        'value' => function ($model) {
                            $types = JournalTrans::getTransTypeOptions();
                            return $types[$model->trans_type_id] ?? 'Unknown';
                        },
                        'headerOptions' => ['style' => 'width: 150px;text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],

                    [
                        'attribute' => 'stock_type_id',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $icon = $model->stock_type_id == JournalTrans::STOCK_TYPE_IN ?
                                '<i class="fa fa-arrow-up text-success"></i>' :
                                '<i class="fa fa-arrow-down text-danger"></i>';
                            $types = JournalTrans::getStockTypeOptions();
                            return $icon . ' ' . ($types[$model->stock_type_id] ?? 'Unknown');
                        },
                        'headerOptions' => ['style' => 'width: 100px;text-align: center;'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                    ],

                    [
                        'attribute' => 'customer_name',
                        'headerOptions' => ['style' => 'width: 150px;'],
                    ],

                    [
                        'attribute' => 'qty',
                        'format' => 'decimal',
                        'headerOptions' => ['style' => 'width: 80px;text-align: right;'],
                        'contentOptions' => ['class' => 'text-right'],
                    ],

                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            if ($model->status == JournalTrans::STATUS_DRAFT) {
                                return '<div class="badge badge-pill badge-warning" style="padding: 10px;">' . 'Pending' . '</div>';
                            }else if($model->status == JournalTrans::STATUS_APPROVED){
                                return '<div class="badge badge-pill badge-success" style="padding: 10px;">' . 'Complete' . '</div>';
                            }
                            else{
                                return '<div class="badge badge-pill badge-danger" style="padding: 10px;">' . 'Cancel' . '</div>';
                            }
                           // return '<div class="badge badge-pill badge-success" style="padding: 10px;">' . 'Complete' . '</div>';
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
                        'template' => '{view} {update} {approve} {delete}',
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
                            'approve' => function ($url, $model, $key) {
                                if ($model->status === JournalTrans::STATUS_DRAFT && \Yii::$app->user->can('approveJournalTrans')) {
                                    return Html::a('<i class="fa fa-check"></i>', ['approve', 'id' => $model->id], [
                                        'class' => 'btn btn-xs btn-success',
                                        'title' => 'Approve',
                                        'data' => [
                                            'confirm' => 'Are you sure you want to approve this transaction?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                }
                                return '';
                            },
                            'delete' => function ($url, $model, $key) {
                                if ($model->status === JournalTrans::STATUS_DRAFT &&  \Yii::$app->user->can('approveJournalTrans')) {
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