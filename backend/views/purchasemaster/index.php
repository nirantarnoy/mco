<?php

use backend\models\PurchaseMaster;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\PurchaseMasterSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'บันทึกการซื้อ (None Purchase Requisition)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-master-index">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-plus"></i> สร้างใบซื้อใหม่', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">

            <!-- ฟอร์มค้นหาและ Export -->
            <div class="mb-3">
                <?php $form = ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                ]); ?>

                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($searchModel, 'date_from')->input('date', ['class' => 'form-control'])->label('วันที่เริ่มต้น') ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($searchModel, 'date_to')->input('date', ['class' => 'form-control'])->label('วันที่สิ้นสุด') ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($searchModel, 'docnum')->textInput(['class' => 'form-control'])->label('เลขที่เอกสาร') ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($searchModel, 'supnam')->textInput(['class' => 'form-control'])->label('ชื่อผู้จำหน่าย') ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <?= Html::submitButton('<i class="fas fa-search"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('<i class="fas fa-redo"></i> รีเซ็ต', ['index'], ['class' => 'btn btn-secondary']) ?>
                        <?= Html::a('<i class="fas fa-file-excel"></i> Export Excel',
                            ['export',
                                'date_from' => $searchModel->date_from,
                                'date_to' => $searchModel->date_to
                            ],
                            ['class' => 'btn btn-success', 'target' => '_blank']
                        ) ?>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            </div>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'docnum',
                    'docdat:date:วันที่เอกสาร',
                    [
                        'attribute' => 'supcod',
                        'label' => 'รหัสผู้จำหน่าย',
                    ],
                    [
                        'attribute' => 'supnam',
                        'label' => 'ชื่อผู้จำหน่าย',
                    ],
                    [
                        'attribute' => 'total_amount',
                        'label' => 'ยอดรวม',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function($model) {
                            if ($model->status == 1) {
                                return '<span class="badge badge-success">ใช้งาน</span>';
                            } else {
                                return '<span class="badge badge-danger">ยกเลิก</span>';
                            }
                        },
                        'filter' => [1 => 'ใช้งาน', 0 => 'ยกเลิก'],
                    ],
                    [
                        'class' => ActionColumn::class,
                        'template' => '{view} {update} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
                                    'class' => 'btn btn-info btn-sm',
                                    'title' => 'ดูรายละเอียด',
                                ]);
                            },
                            'update' => function ($url, $model) {
                                return Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], [
                                    'class' => 'btn btn-warning btn-sm',
                                    'title' => 'แก้ไข',
                                ]);
                            },
                            'delete' => function ($url, $model) {
                                return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-danger btn-sm',
                                    'title' => 'ลบ',
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