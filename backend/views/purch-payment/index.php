<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PurchPaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'รายการบันทึกการจ่ายเงิน';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purch-payment-index">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-plus"></i> สร้างรายการใหม่', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
                <?= Html::a('<i class="fas fa-file-alt"></i> รายงาน', ['report'], ['class' => 'btn btn-info btn-sm ml-2']) ?>
            </div>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-bordered table-striped'],
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['style' => 'width: 50px;'],
                    ],
//                    [
//                        'attribute' => 'id',
//                        'headerOptions' => ['style' => 'width: 80px;'],
//                    ],
                    [
                        'attribute' => 'purch_no',
                        'label' => 'เลขที่ใบสั่งซื้อ',
                        'value' => function($model) {
                            return $model->purch ? $model->purch->purch_no : '-';
                        }
                    ],
                    [
                        'attribute' => 'vendor_name',
                        'label' => 'ชื่อผู้ขาย',
                        'value' => function($model) {
                            return \backend\models\Purch::findVendorNameFromPurchId($model->purch_id);
                        }
                    ],
                    [
                        'attribute' => 'trans_date',
                        'format' => ['date', 'php:d/m/Y'],
                        'headerOptions' => ['style' => 'width: 120px;'],
                    ],
                    [
                        'attribute' => 'status',
                        'headerOptions' => ['style' => 'width: 100px;'],
                        'value' => function($model) {
                            return $model->status ?: '-';
                        }
                    ],
                    [
                        'label' => 'จำนวนรายการโอน',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'contentOptions' => ['class' => 'text-center'],
                        'value' => function($model) {
                            return count($model->purchPaymentLines);
                        }
                    ],
                    [
                        'label' => 'ยอดรวมโอน',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'contentOptions' => ['class' => 'text-right'],
                        'value' => function($model) {
                            $total = 0;
                            foreach ($model->purchPaymentLines as $line) {
                                $total += $line->pay_amount;
                            }
                            return Yii::$app->formatter->asDecimal($total, 2);
                        }
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:d/m/Y H:i'],
                        'headerOptions' => ['style' => 'width: 150px;'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'headerOptions' => ['style' => 'width: 100px;'],
                        'contentOptions' => ['class' => 'text-center'],
                        'template' => '{view} {update} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-eye"></i>', $url, [
                                    'title' => 'ดูรายละเอียด',
                                    'class' => 'btn btn-info btn-sm',
                                ]);
                            },
                            'update' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-edit"></i>', $url, [
                                    'title' => 'แก้ไข',
                                    'class' => 'btn btn-warning btn-sm',
                                ]);
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-trash"></i>', $url, [
                                    'title' => 'ลบ',
                                    'class' => 'btn btn-danger btn-sm',
                                    'data' => [
                                        'confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                                        'method' => 'post',
                                    ],
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>