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

$model_doc = \common\models\JournalTransDoc::find()->where(['journal_trans_id' => $model->id])->all();
?>
    <div class="journal-trans-view">
        <!-- Flash Messages -->
        <?php if (\Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= \Yii::$app->session->getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (\Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= \Yii::$app->session->getFlash('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (\Yii::$app->session->hasFlash('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= \Yii::$app->session->getFlash('warning') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (\Yii::$app->session->hasFlash('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?= \Yii::$app->session->getFlash('info') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-8">
                <!--            <h1>--><?php //= Html::encode($this->title) ?><!--</h1>-->
            </div>
            <div class="col-md-4 text-right">
                <div class="btn-group">
                    <?php if ($model->status === JournalTrans::STATUS_DRAFT): ?>
                        <?= Html::a('<i class="fa fa-edit"></i> Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?php if (\Yii::$app->user->can('approveJournalTrans')): ?>
                            <?= Html::a('<i class="fa fa-check"></i> Approve', ['approve', 'id' => $model->id], [
                                'class' => 'btn btn-success',
                                'data' => [
                                    'confirm' => 'Are you sure you want to approve this transaction?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
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
        <br/>

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
                                    'attribute' => 'stock_type_id',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        $icon = $model->stock_type_id == JournalTrans::STOCK_TYPE_IN ?
                                            '<i class="fa fa-arrow-up text-success"></i>' :
                                            '<i class="fa fa-arrow-down text-danger"></i>';
                                        $types = JournalTrans::getStockTypeOptions();
                                        return $icon . ' ' . ($types[$model->stock_type_id] ?? 'Unknown');
                                    },
                                ],
                                'customer_name',
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
                                //'created_by',
                                [
                                    'attribute' => 'emp_trans_id',
                                    'value' => function ($model) {
                                        return \backend\models\User::findEmployeeNameByUserId($model->emp_trans_id);
                                    }
                                ],
                                [
                                    'attribute' => 'approve_by',
                                    'value' => function ($model) {
                                        return \backend\models\User::findEmployeeNameByUserId($model->approve_by);
                                    }
                                ],
                                [
                                    'attribute' => 'approve_date',
                                    'value' => function ($model) {
                                        return date('m-d-Y H:i:s', strtotime($model->approve_date));
                                    }
                                ],
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
                <h3 class="panel-title">รายการสินค้า</h3>
            </div>
            <div class="panel-body">
                <?php
                $dataProvider = new ArrayDataProvider([
                    'allModels' => $model->journalTransLines,
                    'pagination' => false,
                ]);
                ?>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'showHeader' => true,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn', 'headerOptions' => ['style' => 'width: 50px;', 'text-align' => 'center;']],

                        [
                            'label' => 'Product',
                            'format' => 'raw',
                            'value' => function ($line) {
                                if ($line->product) {
                                    return '<strong>' . Html::encode($line->product->code) . '</strong><br>' .
                                        Html::encode($line->product->name);
                                }
                                return '<span class="text-danger">Product not found</span>';
                            },
                        ],

                        [
                            'attribute' => 'qty',
                            'format' => 'decimal',
                            'contentOptions' => ['class' => 'text-right'],
                            'headerOptions' => ['style' => 'width: 100px;'],
                        ],

//                    [
//                        'attribute' => 'sale_price',
//                        'format' => ['currency', 'THB'],
//                        'contentOptions' => ['class' => 'text-right'],
//                        'headerOptions' => ['style' => 'width: 120px;'],
//                    ],

                        [
                            'attribute' => 'line_price',
                            'format' => ['currency', 'THB'],
                            'contentOptions' => ['class' => 'text-right'],
                            'headerOptions' => ['style' => 'width: 130px;'],
                        ],

                        [
                            'label' => 'Return Info',
                            'format' => 'raw',
                            'value' => function ($line) {
                                if ($line->journalTrans->trans_type_id == JournalTrans::TRANS_TYPE_RETURN_BORROW) {
                                    $html = '';
                                    if ($line->return_to_type) {
                                        $types = \common\models\JournalTransLineX::getReturnTypeOptions();
                                        $class = '';
                                        switch ($line->return_to_type) {
                                            case 'complete':
                                                $class = 'label-success';
                                                break;
                                            case 'damaged':
                                                $class = 'label-danger';
                                                break;
                                            case 'incomplete':
                                                $class = 'label-warning';
                                                break;
                                        }
                                        $html .= '<span class="label ' . $class . '">' .
                                            ($types[$line->return_to_type] ?? $line->return_to_type) .
                                            '</span>';
                                    }
                                    if ($line->return_note) {
                                        $html .= '<br><small>' . Html::encode($line->return_note) . '</small>';
                                    }
                                    return $html;
                                }
                                return '-';
                            },
                            'visible' => $model->trans_type_id == JournalTrans::TRANS_TYPE_RETURN_BORROW,
                        ],

                        [
                            'attribute' => 'remark',
                            'format' => 'ntext',
                            'value' => function ($line) {
                                return $line->remark ?: '-';
                            },
                        ],
                    ],
                    'summary' => false,
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                ]); ?>

                <!-- Totals -->
                <div class="row" style="margin-top: 15px;">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <table class="table table-condensed">
                            <tr>
                                <td><strong>Total Quantity:</strong></td>
                                <td class="text-right">
                                    <strong><?php echo number_format(array_sum(array_column($model->journalTransLines, 'qty')), 2) ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total Amount:</strong></td>
                                <td class="text-right">
                                    <strong><?php echo Yii::$app->formatter->asCurrency(array_sum(array_column($model->journalTransLines, 'line_price')), 'THB') ?></strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Movements (if approved) -->
        <?php if ($model->status === JournalTrans::STATUS_APPROVED && !empty($model->stockTrans)): ?>
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">Stock Movements</h3>
                </div>
                <div class="panel-body">
                    <?php
                    $stockDataProvider = new ArrayDataProvider([
                        'allModels' => $model->stockTrans,
                        'pagination' => false,
                    ]);
                    ?>

                    <?= GridView::widget([
                        'dataProvider' => $stockDataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            [
                                'label' => 'Product',
                                'value' => function ($stock) {
                                    return $stock->product->code . ' - ' . $stock->product->name;
                                },
                            ],

                            [
                                'attribute' => 'qty',
                                'format' => 'decimal',
                                'contentOptions' => ['class' => 'text-right'],
                            ],

                            [
                                'label' => 'Movement Type',
                                'format' => 'raw',
                                'value' => function ($stock) {
                                    $icon = $stock->stock_type_id == JournalTrans::STOCK_TYPE_IN ?
                                        '<i class="fa fa-arrow-up text-success"></i> IN' :
                                        '<i class="fa fa-arrow-down text-danger"></i> OUT';
                                    return $icon;
                                },
                            ],

                            [
                                'attribute' => 'line_price',
                                'format' => ['currency', 'THB'],
                                'contentOptions' => ['class' => 'text-right'],
                            ],

                            [
                                'attribute' => 'created_at',
//                            'format' => 'datetime',
                                'value' => function ($stock) {
                                    return date('m-d-Y', strtotime($stock->created_at));
                                }
                            ],

                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => function ($stock) {
                                    return '<span class="label label-success">Completed</span>';
                                },
                            ],
                        ],
                        'summary' => false,
                        'tableOptions' => ['class' => 'table table-striped table-bordered'],
                    ]); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Current Stock Status -->
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">สถานะสต็อกปัจจุบัน</h3>
            </div>
            <div class="panel-body">
                <?php
                $stockStatusData = [];
                foreach ($model->journalTransLines as $line) {
                    // Check if product exists to avoid null errors
                    if ($line->product !== null) {
                        $stockStatusData[] = [
                            'product' => $line->product,
                            'current_stock' => $line->product->getStockInWarehouse($line->warehouse_id),
                            'available_stock' => $line->product->getAvailableStockInWarehouse($line->warehouse_id),
                            'total_stock' => $line->product->stock_qty,
                            'minimum_stock' => $line->product->minimum_stock,
                        ];
                    }
                }

                $stockStatusProvider = new ArrayDataProvider([
                    'allModels' => $stockStatusData,
                    'pagination' => false,
                ]);
                ?>

                <?= GridView::widget([
                    'dataProvider' => $stockStatusProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        [
                            'label' => 'Product',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return '<strong>' . Html::encode($data['product']->code) . '</strong><br>' .
                                    Html::encode($data['product']->name);
                            },
                        ],

                        [
                            'label' => 'Current Stock (This Warehouse)',
                            'format' => 'decimal',
                            'value' => function ($data) {
                                return $data['current_stock'];
                            },
                            'contentOptions' => ['class' => 'text-right'],
                        ],

                        [
                            'label' => 'Available Stock',
                            'format' => 'decimal',
                            'value' => function ($data) {
                                return $data['available_stock'];
                            },
                            'contentOptions' => ['class' => 'text-right'],
                        ],

                        [
                            'label' => 'Total Stock (All Warehouses)',
                            'format' => 'decimal',
                            'value' => function ($data) {
                                return $data['total_stock'];
                            },
                            'contentOptions' => ['class' => 'text-right'],
                        ],

                        [
                            'label' => 'Minimum Stock',
                            'format' => 'decimal',
                            'value' => function ($data) {
                                return $data['minimum_stock'];
                            },
                            'contentOptions' => ['class' => 'text-right'],
                        ],

                        [
                            'label' => 'Status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if ($data['total_stock'] <= $data['minimum_stock']) {
                                    return '<span class="label label-danger"><i class="fa fa-warning"></i> Low Stock</span>';
                                } elseif ($data['total_stock'] <= $data['minimum_stock'] * 1.5) {
                                    return '<span class="label label-warning"><i class="fa fa-exclamation"></i> Warning</span>';
                                } else {
                                    return '<span class="label label-success"><i class="fa fa-check"></i> OK</span>';
                                }
                            },
                        ],
                    ],
                    'summary' => false,
                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                ]); ?>
            </div>
        </div>

        <br/>
        <div class="label">
            <h4>เอกสารแนบ</h4>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <table class="table table-bordered table-striped" style="width: 100%">
                    <thead>
                    <tr>
                        <th style="width: 5%;text-align: center">#</th>
                        <th style="width: 50%;text-align: center">ชื่อไฟล์</th>
                        <th style="width: 10%;text-align: center">ดูเอกสาร</th>
                        <th style="width: 5%;text-align: center">-</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($model_doc != null): ?>

                        <?php foreach ($model_doc as $key => $value): ?>
                            <tr>
                                <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                                <td><?= $value->doc_name ?></td>
                                <td style="text-align: center">
                                    <a href="<?= Yii::$app->request->BaseUrl . '/uploads/journal_trans_doc/' . $value->doc_name ?>"
                                       target="_blank">
                                        ดูเอกสาร
                                    </a>
                                </td>
                                <td style="text-align: center">
                                    <!--                                <div class="btn btn-danger" data-var="-->
                                    <?php //= trim($value->doc_name) ?><!--" onclick="delete_doc($(this))">ลบ</div>-->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-lg-1"></div>
        </div>
        <br/>

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
                                    Date: <?= date('m-d-Y H:i:s', strtotime($model->updated_at)) ?>
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

        .bg-blue {
            background-color: #3c8dbc;
        }

        .bg-yellow {
            background-color: #f39c12;
        }

        .bg-green {
            background-color: #00a65a;
        }

        .bg-red {
            background-color: #dd4b39;
        }
    </style>
<?php
$this->registerJs("
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
");
?>