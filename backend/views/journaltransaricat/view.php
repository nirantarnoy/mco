<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use backend\models\JournalTrans;

/* @var $this yii\web\View */
/* @var $model common\models\JournalTrans */

$this->title = 'Transaction: ' . $model->journal_no;
$this->params['breadcrumbs'][] = ['label' => 'Transactions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="journal-trans-view">

        <div class="row">
            <div class="col-md-8">
                <!--            <h1>--><?php //= Html::encode($this->title) ?><!--</h1>-->
            </div>
            <div class="col-md-4 text-right">
                <div class="btn-group">
                    <?php if ($model->status === JournalTrans::STATUS_DRAFT): ?>
                        <?= Html::a('<i class="fa fa-edit"></i> Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
<!--                        --><?php //= Html::a('<i class="fa fa-check"></i> Approve', ['approve', 'id' => $model->id], [
//                            'class' => 'btn btn-success',
//                            'data' => [
//                                'confirm' => 'Are you sure you want to approve this transaction?',
//                                'method' => 'post',
//                            ],
//                        ]) ?>
                        <?= Html::a('<i class="fa fa-trash"></i> Delete', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this transaction?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    <?php endif; ?>
                    <?= Html::a('<i class="fa fa-print"></i> Print', ['print', 'id' => $model->id], [
                        'class' => 'btn btn-info',
                        'target' => '_blank'
                    ]) ?>
                    <?= Html::a('<i class="fa fa-list"></i> Back to List', ['index'], ['class' => 'btn btn-default']) ?>
                </div>
            </div>
        </div>
        <br />

        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'journal_no',
                                [
                                    'attribute' => 'trans_date',
                                    'value' => function ($model) {
                                        return date('m-d-Y', strtotime($model->trans_date));
                                    }
                                ],
                                [
                                    'attribute' => 'trans_type_id',
                                    'value' => function ($model) {
                                        $types = JournalTrans::getTransTypeOptions();
                                        return $types[$model->trans_type_id] ?? 'Unknown';
                                    },
                                ],
                                [
                                    'attribute' => 'agency_id',
                                    'value' => function ($model) {
                                        return \backend\models\Agency::findName($model->agency_id);
                                    }
                                ],
                                [
                                    'attribute' => 'employer_id',
                                    'value' => function ($model) {
                                        return \backend\models\Employer::findName($model->employer_id);
                                    }
                                ],
                                'remark:ntext',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-info">
                    <div class="panel-body">
                        <?= DetailView::widget([
                            'model' => $model,
                            'attributes' => [
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
                                ],
                                [
                                    'attribute' => 'created_at',
                                    'value' => function ($model) {
                                        return date('m-d-Y H:i:s', strtotime($model->created_at));
                                    },
                                ],
                                'created_by',
                                [
                                    'attribute' => 'updated_at',
                                    'value' => function ($model) {
                                        return date('m-d-Y H:i:s', strtotime($model->updated_at));
                                    },
                                ],
                                'updated_by',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Lines -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">รายละเอียด</h3>
            </div>
            <div class="panel-body">
                <?php
                $dataProvider = new ArrayDataProvider([
                    'allModels' => $model->journalTransAricat,
                    'pagination' => false,
                ]);
                ?>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'showHeader' => true,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn','headerOptions' => ['style' => 'width: 50px;','text-align' => 'center;']],

                        [
                            'label' => 'พนักงาน',
                            'format' => 'raw',
                            'value' => function ($line) {
                                if ($line->worker) {
                                    return '<strong>' . Html::encode($line->worker->fnam.' '.$line->worker->lname) . '</strong><br>';
                                }
                                return '<span class="text-danger">Worker not found</span>';
                            },
                        ],

                        [
                            'attribute' => 'note',
                            'format' => 'text',
                            'value' => function ($line) {
                                return $line->note ?: '-';
                            },
                        ],
                    ],
                    'summary' => false,
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                ]); ?>

            </div>
        </div>



        <!-- Action Timeline -->
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">Action Timeline</h3>
            </div>
            <div class="panel-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <i class="fa fa-plus bg-blue"></i>
                        <div class="timeline-content">
                            <h4>Transaction Created</h4>
                            <p>
                                Created by: <strong><?= Html::encode($model->created_by) ?></strong><br>
                                Date: <?= date('m-d-Y H:i:s', strtotime($model->created_at)) ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($model->updated_at != $model->created_at): ?>
                        <div class="timeline-item">
                            <i class="fa fa-edit bg-yellow"></i>
                            <div class="timeline-content">
                                <h4>Transaction Updated</h4>
                                <p>
                                    Updated by: <strong><?= Html::encode($model->updated_by) ?></strong><br>
                                    Date: <?=  date('m-d-Y H:i:s', strtotime($model->updated_at)) ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($model->status === JournalTrans::STATUS_APPROVED): ?>
                        <div class="timeline-item">
                            <i class="fa fa-check bg-green"></i>
                            <div class="timeline-content">
                                <h4>Transaction Approved</h4>
                                <p>
                                    Stock movements have been processed and inventory updated.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <style>
        .timeline {
            position: relative;
            margin: 20px 0;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 60px;
        }

        .timeline-item i {
            position: absolute;
            left: 0;
            top: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            color: white;
            font-size: 16px;
        }

        .timeline-content {
            background: #f4f4f4;
            border-radius: 4px;
            padding: 15px;
            border-left: 3px solid #ddd;
        }

        .timeline-content h4 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }

        .timeline-content p {
            margin: 0;
            color: #666;
        }

        .bg-blue { background-color: #3c8dbc; }
        .bg-yellow { background-color: #f39c12; }
        .bg-green { background-color: #00a65a; }
        .bg-red { background-color: #dd4b39; }
    </style>
<?php
$this->registerJs("
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
");
?>