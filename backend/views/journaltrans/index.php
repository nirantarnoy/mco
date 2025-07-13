<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\JournalTrans;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Stock Transactions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="journal-trans-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create New Transaction', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Stock Summary Report', ['stock-summary'], ['class' => 'btn btn-info']) ?>
        <?= Html::a('Transaction Report', ['transaction-report'], ['class' => 'btn btn-warning']) ?>
    </p>

    <div class="row">
        <div class="col-md-3">
            <div class="panel panel-primary">
                <div class="panel-heading">Quick Actions</div>
                <div class="panel-body">
                    <div class="list-group">
                        <?= Html::a('<i class="fa fa-plus"></i> PO Receive', ['create', 'type' => JournalTrans::TRANS_TYPE_PO_RECEIVE], ['class' => 'list-group-item']) ?>
                        <?= Html::a('<i class="fa fa-minus"></i> Issue Stock', ['create', 'type' => JournalTrans::TRANS_TYPE_ISSUE_STOCK], ['class' => 'list-group-item']) ?>
                        <?= Html::a('<i class="fa fa-undo"></i> Return Issue', ['create', 'type' => JournalTrans::TRANS_TYPE_RETURN_ISSUE], ['class' => 'list-group-item']) ?>
                        <?= Html::a('<i class="fa fa-share"></i> Issue Borrow', ['create', 'type' => JournalTrans::TRANS_TYPE_ISSUE_BORROW], ['class' => 'list-group-item']) ?>
                        <?= Html::a('<i class="fa fa-reply"></i> Return Borrow', ['create', 'type' => JournalTrans::TRANS_TYPE_RETURN_BORROW], ['class' => 'list-group-item']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <?php Pjax::begin(); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'journal_no',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a($model->journal_no, ['view', 'id' => $model->id], ['class' => 'btn btn-link btn-sm']);
                        }
                    ],

                    [
                        'attribute' => 'trans_date',
                        'format' => 'date',
                        'headerOptions' => ['style' => 'width: 120px;'],
                    ],

                    [
                        'attribute' => 'trans_type_id',
                        'value' => function ($model) {
                            $types = JournalTrans::getTransTypeOptions();
                            return $types[$model->trans_type_id] ?? 'Unknown';
                        },
                        'headerOptions' => ['style' => 'width: 150px;'],
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
                        'headerOptions' => ['style' => 'width: 100px;'],
                    ],

                    [
                        'attribute' => 'customer_name',
                        'headerOptions' => ['style' => 'width: 150px;'],
                    ],

                    [
                        'attribute' => 'qty',
                        'format' => 'decimal',
                        'headerOptions' => ['style' => 'width: 80px;'],
                        'contentOptions' => ['class' => 'text-right'],
                    ],

                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $class = '';
                            switch ($model->status) {
                                case JournalTrans::STATUS_DRAFT:
                                    $class = 'label-default';
                                    break;
                                case JournalTrans::STATUS_PENDING:
                                    $class = 'label-warning';
                                    break;
                                case JournalTrans::STATUS_APPROVED:
                                    $class = 'label-success';
                                    break;
                                case JournalTrans::STATUS_CANCELLED:
                                    $class = 'label-danger';
                                    break;
                            }
                            return '<span class="label ' . $class . '">' . ucfirst($model->status) . '</span>';
                        },
                        'headerOptions' => ['style' => 'width: 100px;'],
                    ],

                    [
                        'attribute' => 'created_at',
                        'format' => 'datetime',
                        'headerOptions' => ['style' => 'width: 150px;'],
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {approve} {delete}',
                        'headerOptions' => ['style' => 'width: 120px;'],
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
                                if ($model->status === JournalTrans::STATUS_DRAFT) {
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
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
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
        background: rgba(0,0,0,0.2);
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

    .bg-aqua { background-color: #00c0ef !important; color: #fff; }
    .bg-red { background-color: #dd4b39 !important; color: #fff; }
    .bg-yellow { background-color: #f39c12 !important; color: #fff; }
    .bg-green { background-color: #00a65a !important; color: #fff; }
</style>