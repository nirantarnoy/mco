<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\daterange\DateRangePicker;
use kartik\export\ExportMenu;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CreditNoteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'ใบลดหนี้ / ใบกำกับภาษี';
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');

    body {
        font-family: 'Prompt', sans-serif !important;
    }

    .credit-note-index {
        font-family: 'Prompt', sans-serif;
    }

    .btn-toolbar {
        margin-bottom: 20px;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-draft {
        background-color: #ffc107;
        color: #000;
    }

    .status-approved {
        background-color: #28a745;
        color: #fff;
    }

    .status-cancelled {
        background-color: #dc3545;
        color: #fff;
    }
</style>

<div class="credit-note-index">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">

            <div class="btn-toolbar" role="toolbar">
                <div class="btn-group mr-2" role="group">
                    <?= Html::a('<i class="fas fa-plus"></i> สร้างใบลดหนี้', ['create'], ['class' => 'btn btn-success']) ?>
                </div>

                <div class="btn-group mr-2" role="group">
                    <?= Html::a('<i class="fas fa-print"></i> พิมพ์', '#', [
                        'class' => 'btn btn-info',
                        'onclick' => 'printSelected(); return false;'
                    ]) ?>

                    <?= Html::a('<i class="fas fa-file-pdf"></i> PDF', '#', [
                        'class' => 'btn btn-danger',
                        'onclick' => 'exportPdfSelected(); return false;'
                    ]) ?>
                </div>

                <div class="btn-group" role="group">
                    <?= ExportMenu::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            'document_no',
                            [
                                'attribute' => 'document_date',
                                'format' => ['date', 'php:d/m/Y']
                            ],
                            [
                                'attribute' => 'customer_id',
                                'value' => 'customer.name'
                            ],
                            'adjust_amount:decimal',
                            'vat_amount:decimal',
                            'total_amount:decimal',
                            [
                                'attribute' => 'status',
                                'value' => function($model) {
                                    return $model->getStatusLabel();
                                }
                            ],
                        ],
                        'target' => ExportMenu::TARGET_BLANK,
                        'fontAwesome' => true,
                        'dropdownOptions' => [
                            'label' => '<i class="fas fa-file-excel"></i> Excel',
                            'class' => 'btn btn-success',
                        ],
                        'exportConfig' => [
                            ExportMenu::FORMAT_EXCEL => false,
                            ExportMenu::FORMAT_EXCEL_X => [
                                'label' => 'Excel',
                                'icon' => 'file-excel',
                                'config' => [
                                    'methods' => [
                                        'SetTitle' => 'รายงานใบลดหนี้',
                                        'SetSubject' => 'Credit Notes Report',
                                    ],
                                ],
                            ],
                        ],
                    ]) ?>
                </div>
            </div>

            <?php Pjax::begin(); ?>

            <?= GridView::widget([
                'id' => 'credit-note-grid',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-striped table-bordered'],
                'columns' => [
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                    ],
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['width' => '50px'],
                    ],
                    [
                        'attribute' => 'document_no',
                        'headerOptions' => ['width' => '150px'],
                    ],
                    [
                        'attribute' => 'document_date',
                        'format' => ['date', 'php:d/m/Y'],
                        'filter' => DateRangePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'document_date',
                            'convertFormat' => true,
                            'pluginOptions' => [
                                'locale' => [
                                    'format' => 'd/m/Y',
                                    'separator' => ' - ',
                                ],
                                'opens' => 'left'
                            ],
                        ]),
                        'headerOptions' => ['width' => '200px'],
                    ],
                    [
                        'attribute' => 'customer_id',
                        'value' => 'customer.name',
                        'filter' => \kartik\select2\Select2::widget([
                            'model' => $searchModel,
                            'attribute' => 'customer_id',
                            'data' => \yii\helpers\ArrayHelper::map(
                                \backend\models\Customer::find()->orderBy('name')->all(),
                                'id',
                                'name'
                            ),
                            'options' => ['placeholder' => 'เลือกลูกค้า...'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]),
                    ],
                    [
                        'attribute' => 'adjust_amount',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-right'],
                        'headerOptions' => ['width' => '120px'],
                    ],
                    [
                        'attribute' => 'vat_amount',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-right'],
                        'headerOptions' => ['width' => '120px'],
                    ],
                    [
                        'attribute' => 'total_amount',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-right'],
                        'headerOptions' => ['width' => '120px'],
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            return Html::tag('span', $model->getStatusLabel(), [
                                'class' => 'status-badge status-' . $model->status
                            ]);
                        },
                        'format' => 'raw',
                        'filter' => \backend\models\CreditNote::getStatusList(),
                        'headerOptions' => ['width' => '100px'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {print} {delete}',
                        'buttons' => [
                            'print' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-print"></i>', ['print', 'id' => $model->id], [
                                    'title' => 'พิมพ์',
                                    'target' => '_blank',
                                    'data-pjax' => '0',
                                ]);
                            },
                        ],
                        'headerOptions' => ['width' => '100px'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>
</div>

<script>
    function printSelected() {
        var keys = $('#credit-note-grid').yiiGridView('getSelectedRows');
        if (keys.length === 0) {
            alert('กรุณาเลือกรายการที่ต้องการพิมพ์');
            return;
        }

        keys.forEach(function(key) {
            window.open('<?= \yii\helpers\Url::to(['print']) ?>?id=' + key, '_blank');
        });
    }

    function exportPdfSelected() {
        var keys = $('#credit-note-grid').yiiGridView('getSelectedRows');
        if (keys.length === 0) {
            alert('กรุณาเลือกรายการที่ต้องการ Export PDF');
            return;
        }

        // Create form and submit
        var form = $('<form>', {
            method: 'POST',
            action: '<?= \yii\helpers\Url::to(['export-pdf']) ?>',
            target: '_blank'
        });

        form.append($('<input>', {
            type: 'hidden',
            name: '<?= Yii::$app->request->csrfParam ?>',
            value: '<?= Yii::$app->request->csrfToken ?>'
        }));

        form.append($('<input>', {
            type: 'hidden',
            name: 'ids',
            value: keys.join(',')
        }));

        form.appendTo('body').submit().remove();
    }
</script>