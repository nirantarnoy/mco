<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'รายการรอตั้งหนี้ (AP Worklist)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-payable-worklist">

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <span class="badge bg-danger">เกินกำหนด SLA (<?= $max_sla_days ?> วัน)</span>
                <span class="badge bg-warning text-dark">สร้าง PV แล้วแต่รอจ่าย</span>
                <?= Html::a('<i class="fas fa-cog"></i> ตั้งค่า SLA', ['setting'], ['class' => 'btn btn-tool btn-sm text-primary']) ?>
            </div>
        </div>
        <div class="card-body">
            
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info"></i> คำแนะนำ!</h5>
                รายการด้านล่างคือใบสั่งซื้อ (PO) และ (None PR) ที่ <b>รับสินค้าเรียบร้อยแล้ว</b> แต่ยังไม่ได้ทำจ่ายเงินหรือยังไม่ได้ตั้งหนี้ผ่าน Payment Voucher แบบเสร็จสมบูรณ์<br/>
                กรุณาคลิกที่ "เลขที่เอกสาร" เพื่อเข้าไปดูรายละเอียดและสร้าง Payment Voucher ต่อไป
            </div>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{items}\n{pager}",
                'options' => ['class' => 'table-responsive'],
                'tableOptions' => ['class' => 'table table-bordered table-striped table-hover'],
                'rowOptions' => function ($model, $key, $index, $grid) {
                    if ($model['status_color'] == 'warning') {
                        return ['class' => 'table-warning'];
                    }
                    if ($model['is_overdue']) {
                        return ['class' => 'table-danger'];
                    }
                    return [];
                },
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'type',
                        'label' => 'ประเภท',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $badge = $model['type'] == 'PO' ? 'primary' : 'info';
                            return "<span class='badge bg-{$badge}'>{$model['type']}</span>";
                        }
                    ],
                    [
                        'attribute' => 'doc_no',
                        'label' => 'เลขที่เอกสาร',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a($model['doc_no'], Url::to($model['url']), [
                                'class' => 'fw-bold',
                                'target' => '_blank',
                                'data-pjax' => 0
                            ]);
                        }
                    ],
                    [
                        'attribute' => 'date',
                        'label' => 'วันที่เอกสาร',
                        'format' => ['date', 'php:d/m/Y']
                    ],
                    [
                        'attribute' => 'vendor_name',
                        'label' => 'ผู้จำหน่าย/Supplier',
                    ],
                    [
                        'attribute' => 'received_date',
                        'label' => 'วันที่รับของ/รับทราบ',
                        'format' => ['date', 'php:d/m/Y']
                    ],
                    [
                        'attribute' => 'overdue_days',
                        'label' => 'เวลาที่ผ่านไป (วัน)',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $days = floor($model['overdue_days']);
                            if ($model['is_overdue']) {
                                return "<span class='text-danger fw-bold'>{$days} วัน (เกินกำหนด)</span>";
                            }
                            return "{$days} วัน";
                        }
                    ],
                    [
                        'attribute' => 'amount',
                        'label' => 'จำนวนเงินสุทธิ',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-end'],
                        'headerOptions' => ['class' => 'text-end']
                    ],
                    [
                        'attribute' => 'status_text',
                        'label' => 'สถานะปัจจุบัน',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return "<span class='badge bg-{$model['status_color']}'>{$model['status_text']}</span>";
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-eye"></i>', Url::to($model['url']), [
                                    'title' => 'ดูรายละเอียด',
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'target' => '_blank',
                                    'data-pjax' => 0
                                ]);
                            }
                        ]
                    ],
                ],
            ]); ?>

        </div>
    </div>
</div>
