<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\grid\ActionColumn;
use yii\widgets\Pjax;
use backend\models\TransactionSearch;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\TransactionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการ Transaction สต๊อก';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transaction-index">

    <!-- Flash Messages -->
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= Yii::$app->session->getFlash('warning') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
<!--        <h1>--><?php //= Html::encode($this->title) ?><!--</h1>-->
        <div class="btn-group">
            <?= Html::a('<i class="fas fa-arrow-up"></i> เบิกสินค้า', ['issue-stock'], [
                'class' => 'btn btn-danger'
            ]) ?>
            <?= Html::a('<i class="fas fa-arrow-down"></i> คืนเบิก', ['return-issue'], [
                'class' => 'btn btn-success'
            ]) ?>
            <?= Html::a('<i class="fas fa-hand-holding"></i> ยืมสินค้า', ['issue-borrow'], [
                'class' => 'btn btn-warning'
            ]) ?>
            <?= Html::a('<i class="fas fa-undo"></i> คืนยืม', ['return-borrow'], [
                'class' => 'btn btn-info'
            ]) ?>
        </div>
    </div>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{items}{pager}',
        'pjax' => true,
        'bordered' => true,
        'striped' => false,
        'condensed' => false,
        'responsive' => true,
        'hover' => true,
        'floatHeader' => true,
        'showPageSummary' => false,
        'toolbar' => [
            '{export}',
            '{toggleData}',
        ],
        'export' => [
            'fontAwesome' => true,
            'showConfirmAlert' => false,
            'target' => GridView::TARGET_BLANK
        ],
        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'header' => '#',
                'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'journal_no',
                'label' => 'เลขที่เอกสาร',
                'headerOptions' => ['style' => 'width: 150px;'],
                'value' => function ($model) {
                    return $model->journal_no ?: 'ยังไม่ได้กำหนด';
                },
            ],
            [
                'attribute' => 'trans_date',
                'label' => 'วันที่',
                'headerOptions' => ['style' => 'width: 120px;'],
                'format' => ['datetime', 'php:d/m/Y H:i'],
                'filter' => kartik\date\DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'trans_date',
                    'options' => ['placeholder' => 'เลือกวันที่'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ]
                ]),
            ],
            [
                'attribute' => 'trans_type_id',
                'label' => 'ประเภท',
                'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center;'],
                'filter' => TransactionSearch::getTransactionTypeOptions(),
                'value' => function ($model) {
                    $types = TransactionSearch::getTransactionTypeOptions();
                    $type = $types[$model->trans_type_id] ?? 'ไม่ระบุ';
                    $badges = [
                        3 => 'danger',   // เบิกสินค้า
                        4 => 'success',  // คืนเบิก
                        5 => 'warning',  // ยืมสินค้า
                        6 => 'info',     // คืนยืม
                    ];
                    $badge = $badges[$model->trans_type_id] ?? 'secondary';
                    return '<span class="badge bg-' . $badge . '">' . $type . '</span>';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'customer_name',
                'label' => 'ผู้เบิก/ผู้ยืม',
                'headerOptions' => ['style' => 'width: 150px;'],
                'value' => function ($model) {
                    return $model->customer_name ?: 'ไม่ระบุ';
                },
            ],
            [
                'attribute' => 'warehouse_id',
                'label' => 'คลัง',
                'headerOptions' => ['style' => 'width: 120px;'],
                'filter' => \backend\models\Warehouse::getWarehouseList(),
                'value' => function ($model) {
                    return $model->warehouse->name ?? 'ไม่ระบุ';
                },
            ],
            [
                'attribute' => 'qty',
                'label' => 'จำนวนรวม',
                'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center;'],
                'format' => ['decimal', 2],
            ],
            [
                'attribute' => 'status',
                'label' => 'สถานะ',
                'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center;'],
                'filter' => TransactionSearch::getStatusOptions(),
                'value' => function ($model) {
                    $statuses = [
                        0 => '<span class="badge bg-secondary">รอดำเนินการ</span>',
                        1 => '<span class="badge bg-success">อนุมัติ</span>',
                        2 => '<span class="badge bg-danger">ไม่อนุมัติ</span>',
                    ];
                    return $statuses[$model->status] ?? '<span class="badge bg-secondary">ไม่ระบุ</span>';
                },
                'format' => 'raw',
            ],
            [
                'class' => ActionColumn::class,
                'header' => 'ตัวเลือก',
                'headerOptions' => ['style' => 'width: 150px; text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center;'],
                'template' => '{view} {approve} {reject}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'title' => 'ดูรายละเอียด',
                            'class' => 'btn btn-sm btn-outline-info me-1',
                            'data-pjax' => '0'
                        ]);
                    },
                    'approve' => function ($url, $model, $key) {
                        if ($model->status == 0) {
                            return Html::a('<i class="fas fa-check"></i>', ['approve', 'id' => $model->id], [
                                'title' => 'อนุมัติ',
                                'class' => 'btn btn-sm btn-outline-success me-1',
                                'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติรายการนี้?',
                                'data-method' => 'post',
                                'data-pjax' => '0'
                            ]);
                        }
                        return '';
                    },
                    'reject' => function ($url, $model, $key) {
                        if ($model->status == 0) {
                            return Html::a('<i class="fas fa-times"></i>', ['reject', 'id' => $model->id], [
                                'title' => 'ไม่อนุมัติ',
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะไม่อนุมัติรายการนี้?',
                                'data-method' => 'post',
                                'data-pjax' => '0'
                            ]);
                        }
                        return '';
                    },
                ],
                'urlCreator' => function ($action, $model, $key, $index) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

    <div class="mt-3">
        <?php
        $totalCount = $dataProvider->totalCount;
        $currentPage = $dataProvider->pagination->page + 1;
        $pageSize = $dataProvider->pagination->pageSize;
        $startRecord = ($currentPage - 1) * $pageSize + 1;
        $endRecord = min($currentPage * $pageSize, $totalCount);
        ?>
        <small class="text-muted">
            แสดง <?= $startRecord ?> - <?= $endRecord ?> ของทั้งหมด <?= $totalCount ?> รายการ
        </small>
    </div>

</div>

<script>
    // Auto hide alerts after 5 seconds
    $(document).ready(function() {
        setTimeout(function() {
            $('.alert').each(function(index) {
                const $alert = $(this);
                setTimeout(function() {
                    $alert.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, index * 200);
            });
        }, 5000);

        // Handle manual close
        $('.alert .btn-close').click(function() {
            $(this).closest('.alert').fadeOut(300, function() {
                $(this).remove();
            });
        });
    });
</script>

<style>
    .kv-grid-table {
        font-size: 14px;
    }

    .grid-view .summary {
        display: none;
    }

    .badge {
        font-size: 12px;
        padding: 0.4em 0.8em;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .alert {
        margin-bottom: 1rem;
        border: 0;
        border-radius: 0.5rem;
        padding: 1rem 1.25rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .alert-success {
        background-color: #d1edff;
        border-left: 4px solid #28a745;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
        color: #721c24;
    }

    .alert-warning {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        color: #856404;
    }

    .alert i {
        font-size: 1.1em;
    }

    .alert .btn-close {
        opacity: 0.5;
    }

    .alert .btn-close:hover {
        opacity: 1;
    }
</style>