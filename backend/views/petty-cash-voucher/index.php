<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'ใบสำคัญจ่ายเงินสดย่อย (Petty Cash Voucher)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="petty-cash-voucher-index">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">
                <i class="fas fa-money-bill-wave"></i> <?= Html::encode($this->title) ?>
            </h4>
            <div>
                <?= Html::a('<i class="fas fa-plus"></i> สร้างใหม่', ['create'], [
                    'class' => 'btn btn-success'
                ]) ?>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-striped table-bordered'],
                'summary' => 'แสดง {begin} - {end} จากทั้งหมด {totalCount} รายการ',
                'emptyText' => '<div class="text-center text-muted"><i class="fas fa-inbox fa-3x"></i><br>ไม่พบข้อมูล</div>',
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'pcv_no',
                        'headerOptions' => ['width' => '150px'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'date',
                        'headerOptions' => ['width' => '120px'],
                        'contentOptions' => ['class' => 'text-center'],
                        'value' => function($model) {
                            return Yii::$app->formatter->asDate($model->date, 'dd/MM/yyyy');
                        }
                    ],
                    [
                        'attribute' => 'name',
                        'headerOptions' => ['width' => '200px'],
                    ],
                    [
                        'attribute' => 'paid_for',
                        'value' => function($model) {
                            return $model->paid_for ? Html::encode(mb_substr($model->paid_for, 0, 50)) . (mb_strlen($model->paid_for) > 50 ? '...' : '') : '-';
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'amount',
                        'headerOptions' => ['width' => '120px'],
                        'contentOptions' => ['class' => 'text-right'],
                        'value' => function($model) {
                            return number_format($model->amount, 2);
                        }
                    ],
                    [
                        'attribute' => 'issued_by',
                        'headerOptions' => ['width' => '150px'],
                    ],
                    [
                        'attribute' => 'approved_by',
                        'headerOptions' => ['width' => '150px'],
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'headerOptions' => ['width' => '120px'],
                        'contentOptions' => ['class' => 'text-center'],
                        'template' => '{view} {update} {print} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-eye"></i>', $url, [
                                    'title' => 'ดูรายละเอียด',
                                    'class' => 'btn btn-sm btn-info',
                                    'data-pjax' => '0'
                                ]);
                            },
                            'update' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-edit"></i>', $url, [
                                    'title' => 'แก้ไข',
                                    'class' => 'btn btn-sm btn-primary',
                                    'data-pjax' => '0'
                                ]);
                            },
                            'print' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-print"></i>', ['print', 'id' => $model->id], [
                                    'title' => 'พิมพ์',
                                    'class' => 'btn btn-sm btn-secondary',
                                    'target' => '_blank',
                                    'data-pjax' => '0'
                                ]);
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-trash"></i>', $url, [
                                    'title' => 'ลบ',
                                    'class' => 'btn btn-sm btn-danger',
                                    'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                                    'data-method' => 'post',
                                    'data-pjax' => '0'
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>

</div>