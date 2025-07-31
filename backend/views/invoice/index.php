<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\Invoice;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการเอกสาร';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="invoice-index">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">
                <i class="fas fa-file-invoice"></i> <?= Html::encode($this->title) ?>
            </h4>
            <div>
                <?= Html::a('<i class="fas fa-plus"></i> สร้างเอกสารใหม่', ['select'], [
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
                        'attribute' => 'invoice_type',
                        'headerOptions' => ['width' => '120px'],
                        'contentOptions' => ['class' => 'text-center'],
                        'value' => function($model) {
                            $badges = [
                                Invoice::TYPE_QUOTATION => '<span class="badge badge-primary">ใบแจ้งหนี้</span>',
                                Invoice::TYPE_BILL_PLACEMENT => '<span class="badge badge-info">ใบวางบิล</span>',
                                Invoice::TYPE_TAX_INVOICE => '<span class="badge badge-success">ใบกำกับภาษี</span>',
                                Invoice::TYPE_RECEIPT => '<span class="badge badge-warning">ใบเสร็จ</span>',
                            ];
                            return isset($badges[$model->invoice_type]) ? $badges[$model->invoice_type] : $model->invoice_type;
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'invoice_number',
                        'headerOptions' => ['width' => '150px'],
                        'contentOptions' => ['class' => 'text-center font-weight-bold'],
                    ],
                    [
                        'attribute' => 'invoice_date',
                        'headerOptions' => ['width' => '120px'],
                        'contentOptions' => ['class' => 'text-center'],
                        'value' => function($model) {
                            return Yii::$app->formatter->asDate($model->invoice_date, 'dd/MM/yyyy');
                        }
                    ],
                    [
                        'attribute' => 'customer_name',
                        'headerOptions' => ['width' => '250px'],
                        'value' => function($model) {
                            $customerInfo = $model->customer_code ? $model->customer_code . ' - ' : '';
                            return $customerInfo . $model->customer_name;
                        }
                    ],
                    [
                        'attribute' => 'total_amount',
                        'headerOptions' => ['width' => '120px'],
                        'contentOptions' => ['class' => 'text-right'],
                        'value' => function($model) {
                            return number_format($model->total_amount, 2);
                        }
                    ],
                    [
                        'attribute' => 'status',
                        'headerOptions' => ['width' => '100px'],
                        'contentOptions' => ['class' => 'text-center'],
                        'value' => function($model) {
                            return $model->status == Invoice::STATUS_ACTIVE
                                ? '<span class="badge badge-success">ใช้งาน</span>'
                                : '<span class="badge badge-danger">ยกเลิก</span>';
                        },
                        'format' => 'raw',
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'headerOptions' => ['width' => '200px'],
                        'contentOptions' => ['class' => 'text-center'],
                        'template' => '{view} {update} {print} {copy} {delete}',
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
                            'copy' => function ($url, $model, $key) {
                                $copyOptions = [
                                    Invoice::TYPE_QUOTATION => ['class' => 'dropdown-item', 'data-pjax' => '0'],
                                    Invoice::TYPE_BILL_PLACEMENT => ['class' => 'dropdown-item', 'data-pjax' => '0'],
                                    Invoice::TYPE_TAX_INVOICE => ['class' => 'dropdown-item', 'data-pjax' => '0'],
                                    Invoice::TYPE_RECEIPT => ['class' => 'dropdown-item', 'data-pjax' => '0'],
                                ];

                                $typeLabels = Invoice::getTypeOptions();
                                $dropdownItems = '';

                                foreach ($copyOptions as $type => $options) {
                                    if ($type != $model->invoice_type) {
                                        $dropdownItems .= Html::a($typeLabels[$type], ['copy', 'id' => $model->id, 'new_type' => $type], $options);
                                    }
                                }

                                return '<div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-warning dropdown-toggle" data-toggle="dropdown" title="คัดลอกเป็น">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <div class="dropdown-menu">' . $dropdownItems . '</div>
                                </div>';
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
    </div>

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">ใบแจ้งหนี้</h5>
                    <h3><?= Invoice::find()->where(['invoice_type' => Invoice::TYPE_QUOTATION, 'status' => Invoice::STATUS_ACTIVE])->count() ?></h3>
                    <small>รายการ</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">ใบวางบิล</h5>
                    <h3><?= Invoice::find()->where(['invoice_type' => Invoice::TYPE_BILL_PLACEMENT, 'status' => Invoice::STATUS_ACTIVE])->count() ?></h3>
                    <small>รายการ</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">ใบกำกับภาษี</h5>
                    <h3><?= Invoice::find()->where(['invoice_type' => Invoice::TYPE_TAX_INVOICE, 'status' => Invoice::STATUS_ACTIVE])->count() ?></h3>
                    <small>รายการ</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">ใบเสร็จ</h5>
                    <h3><?= Invoice::find()->where(['invoice_type' => Invoice::TYPE_RECEIPT, 'status' => Invoice::STATUS_ACTIVE])->count() ?></h3>
                    <small>รายการ</small>
                </div>
            </div>
        </div>
    </div>

</div>