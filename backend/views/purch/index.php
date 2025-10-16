<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\grid\ActionColumn;
use yii\widgets\Pjax;
use backend\models\Purch;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PurchSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'คำสั่งซื้อ';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purch-index">

    <div class="d-flex justify-content-between align-items-center mb-3">
<!--        <h1>--><?php //= Html::encode($this->title) ?><!--</h1>-->
        <h1></h1>
        <div class="d-flex align-items-center">
            <span class="me-2">แสดง</span>
            <?= Html::dropDownList('per-page', $dataProvider->pagination->pageSize, [
                10 => '10',
                20 => '20',
                50 => '50',
                100 => '100'
            ], [
                'class' => 'form-select form-select-sm',
                'style' => 'width: auto;',
                'onchange' => 'changePageSize(this.value)',
                'id' => 'per-page-select'
            ]) ?>
            <span class="ms-2">รายการ</span>
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
        'panel' => [
            'type' => GridView::TYPE_DEFAULT,
            'heading' => false,
            'before' => Html::a('<i class="fas fa-plus"></i> สร้างใหม่', ['create'], ['class' => 'btn btn-success']),
            'after' => false,
        ],
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
                'attribute' => 'purch_no',
                'label' => 'ชื่อ',
                'headerOptions' => ['style' => 'width: 150px;'],
                'value' => function ($model) {
                    return $model->purch_no ?: 'ยังไม่ได้กำหนด';
                },
            ],
            [
                'attribute' => 'purch_date',
                'label' => 'วันที่',
                'headerOptions' => ['style' => 'width: 120px;'],
                'format' => ['date', 'php:m/d/Y'],
                'filter' => kartik\date\DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'purch_date',
                    'options' => ['placeholder' => 'เลือกวันที่'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ]
                ]),
            ],
//            [
//                'attribute' => 'vendor_id',
//                'label' => 'ผู้ขาย',
//                'headerOptions' => ['style' => 'width: 200px;'],
//                'value' => function ($model) {
//                    return $model->vendor_id ? \backend\models\Vendor::findName($model->vendor_id) : 'ไม่ระบุ';
//                },
//            ],
            [
                'attribute' => 'vendor_name',
                'label' => 'ผู้ขาย',
                'headerOptions' => ['style' => 'width: 200px;'],
                'value' => 'vendor.name', // ✅ แสดงชื่อ vendor
            ],
            [
                'attribute' => 'approve_status',
                'label' => 'สถานะ',
                'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center;'],
                'filter' => [
                    Purch::APPROVE_STATUS_PENDING => 'รอพิจารณา',
                    Purch::APPROVE_STATUS_APPROVED => 'อนุมัติ',
                    Purch::APPROVE_STATUS_REJECTED => 'ไม่อนุมัติ',
                    Purch::STATUS_CANCELLED => 'ไม่อนุมัติ',
                ],
                'value' => function ($model) {
                    $po_remain = \backend\models\Purch::checkPoremain($model->id);
                    if ($model->approve_status == Purch::APPROVE_STATUS_APPROVED && !empty($po_remain)) {
                        return '<span class="badge bg-info">อนุมัติ</span>';
                    } elseif($model->approve_status == Purch::APPROVE_STATUS_PENDING && !empty($po_remain)) {
                        return '<span class="badge bg-warning">รอพิจารณา</span>';
                    }elseif($model->approve_status == Purch::STATUS_CANCELLED) {
                        return '<span class="badge bg-danger">ยกเลิก</span>';
                    } elseif(empty($po_remain)) {
                        return '<span class="badge bg-success">สำเร็จ</span>';
                    }
                },
                'format' => 'raw',
            ],
            [
                'class' => ActionColumn::class,
                'header' => 'ตัวเลือก',
                'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center;'],
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'title' => 'ดูรายละเอียด',
                            'class' => 'btn btn-sm btn-outline-info me-1',
                            'data-pjax' => '0'
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                            'title' => 'แก้ไข',
                            'class' => 'btn btn-sm btn-outline-primary me-1',
                            'data-pjax' => '0'
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return $model->approve_status == Purch::APPROVE_STATUS_PENDING ? Html::a('<i class="fas fa-trash"></i>', $url, [
                            'title' => 'ลบ',
                            'class' => 'btn btn-sm btn-outline-danger',
                            'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                            'data-method' => 'post',
                            'data-pjax' => '0'
                        ]):'';
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
    function changePageSize(pageSize) {
        var url = new URL(window.location);
        url.searchParams.set('per-page', pageSize);
        url.searchParams.delete('page'); // Reset to first page
        window.location = url.toString();
    }

    // Handle page size from URL parameter
    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        var perPage = urlParams.get('per-page');
        if (perPage) {
            $('#per-page-select').val(perPage);
        }
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
</style>