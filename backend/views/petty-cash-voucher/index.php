<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PettyCashVoucherSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'ใบสำคัญจ่ายเงินสดย่อย (Petty Cash Voucher)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="petty-cash-voucher-index">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">

            <div>
                <?= Html::a('<i class="fas fa-plus"></i> สร้างใหม่', ['create'], [
                    'class' => 'btn btn-success'
                ]) ?>
                <?= Html::a('<i class="fas fa-chart-bar"></i> รายงาน', ['report'], [
                    'class' => 'btn btn-info'
                ]) ?>
                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#printSummaryModal">
                    <i class="fas fa-print"></i> พิมพ์ใบสรุปเบิกเงินสดย่อย
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
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
                'pager'=>[
                        'class'=> \yii\bootstrap4\LinkPager::className(),
                ]
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>

</div>

<!-- Modal Print Summary -->
<div class="modal fade" id="printSummaryModal" tabindex="-1" role="dialog" aria-labelledby="printSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printSummaryModalLabel">พิมพ์ใบสรุปการเบิกชดเชยเงินสดย่อย</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?= Html::beginForm(['print-summary'], 'get', ['target' => '_blank']) ?>
            <div class="modal-body">
                <div class="form-group">
                    <label>ตั้งแต่วันที่</label>
                    <?= \kartik\date\DatePicker::widget([
                        'name' => 'from_date',
                        'value' => date('Y-m-01'),
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true
                        ]
                    ]) ?>
                </div>
                <div class="form-group">
                    <label>ถึงวันที่</label>
                    <?= \kartik\date\DatePicker::widget([
                        'name' => 'to_date',
                        'value' => date('Y-m-d'),
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true
                        ]
                    ]) ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> พิมพ์รายงาน</button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>