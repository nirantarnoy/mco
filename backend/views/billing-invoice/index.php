<?php
// backend/views/billing-invoice/index.php
use yii\helpers\Html;
//use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\grid\GridView;

$this->title = 'ใบวางบิล';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="billing-invoice-index">

    <p>
        <?= Html::a('สร้างใบวางบิล', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'billing_number',
            [
                'attribute' => 'billing_date',
                'value' => function ($model) {
                    return date('m-d-Y', strtotime($model->billing_date));
                }
            ],
            [
                'attribute' => 'customer_id',
                'value' => function ($model) {
                    return $model->customer->code . ' - ' . $model->customer->name;
                },
                'label' => 'ลูกค้า',
            ],
            [
                'attribute' => 'total_amount',
                'value' => function ($model) {
                    return number_format($model->total_amount, 2);
                }
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    $statusMap = [
                        'draft' => 'ร่าง',
                        'issued' => 'ออกแล้ว',
                        'paid' => 'ชำระแล้ว',
                        'cancelled' => 'ยกเลิก',
                    ];
                    return $statusMap[$model->status] ?? $model->status;
                },
                'filter' => [
                    'draft' => 'ร่าง',
                    'issued' => 'ออกแล้ว',
                    'paid' => 'ชำระแล้ว',
                    'cancelled' => 'ยกเลิก',
                ],
            ],
//            'created_at:datetime',

            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['width' => '200px'],
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
                            'title' => 'ยกเลิก',
                            'class' => 'btn btn-sm btn-danger',
                            'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะยกเลิกเอกสารนี้?',
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