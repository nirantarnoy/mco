<?php

use yii\bootstrap4\LinkPager;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\date\DatePicker;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ActionLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $statistics array */
/* @var $popularActions array */

$this->title = 'Action Logs';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="action-log-index">
    <!-- Statistics Cards -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-3">
            <div class="card card-primary">
                <div class="card-body text-center">
                    <h3><?= number_format($statistics['total_logs']) ?></h3>
                    <p>Total Logs (30 days)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-success">
                <div class="card-body text-center">
                    <h3><?= number_format($statistics['success_logs']) ?></h3>
                    <p>Success Logs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-danger">
                <div class="card-body text-center">
                    <h3><?= number_format($statistics['failed_logs']) ?></h3>
                    <p>Failed Logs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-info">
                <div class="card-body text-center">
                    <h3><?= number_format($statistics['unique_users']) ?></h3>
                    <p>Unique Users</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-12">
            <?= Html::a('Dashboard', ['dashboard'], ['class' => 'btn btn-info']) ?>
            <?= Html::a('Export CSV', ['export'] + Yii::$app->request->queryParams, ['class' => 'btn btn-success']) ?>
            <?= Html::a('Clean Old Logs (90+ days)', ['clean-old'], [
                'class' => 'btn btn-warning',
                'data' => [
                    'confirm' => 'Are you sure you want to delete logs older than 90 days?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'action-log-pjax']); ?>

    <!-- Search Form -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" href="#search-collapse">
                    <i class="glyphicon glyphicon-search"></i> Search Filters
                </a>
            </h4>
        </div>
        <div id="search-collapse" class="panel-collapse collapse">
            <div class="panel-body">
                <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            </div>
        </div>
    </div>

    <!-- Grid View -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->id];
                }
            ],

          //  'id',

            [
                'attribute' => 'user_search',
                'label' => 'ผู้ดำเนินการ (User)',
                'format' => 'raw',
                'value' => function ($model) {
                    $empName = $model->getEmployeeName();
                    if (!empty($empName)) {
                        return '<strong>' . Html::encode($empName) . '</strong><br><small class="text-muted">' . Html::encode($model->username) . ($model->user_id ? ' (ID: ' . $model->user_id . ')' : '') . '</small>';
                    }
                    return Html::encode($model->username . ($model->user_id ? " (ID: {$model->user_id})" : ''));
                },
                'filter' => Html::activeTextInput($searchModel, 'user_search', [
                    'class' => 'form-control',
                    'placeholder' => 'ค้นหาผู้ดำเนินการ / Username...',
                ]),
            ],

            [
                'attribute' => 'action',
                'label' => 'การกระทำ (Action)',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->action == 'MODEL_CREATE') return '<span class="badge badge-success">สร้าง (Model)</span>';
                    if ($model->action == 'MODEL_UPDATE') return '<span class="badge badge-warning">แก้ไข (Model)</span>';
                    if ($model->action == 'MODEL_DELETE') return '<span class="badge badge-danger">ลบ (Model)</span>';
                    if ($model->action == 'REQUEST_RECALL') return '<span class="badge badge-warning text-dark"><i class="fas fa-undo-alt"></i> ร้องขอ Recall</span>';
                    if ($model->action == 'APPROVE_RECALL') return '<span class="badge badge-success"><i class="fas fa-check"></i> อนุมัติ Recall</span>';
                    if ($model->action == 'REJECT_RECALL') return '<span class="badge badge-danger"><i class="fas fa-times"></i> ปฏิเสธ Recall</span>';
                    if ($model->action == 'REJECT') return '<span class="badge badge-danger"><i class="fas fa-ban"></i> ไม่อนุมัติ</span>';
                    if ($model->action == 'RETURN_FOR_EDIT') return '<span class="badge badge-warning text-dark"><i class="fas fa-undo"></i> ส่งกลับแก้ไข</span>';
                    if ($model->action == 'APPROVE') return '<span class="badge badge-success"><i class="fas fa-check"></i> อนุมัติ</span>';
                    if ($model->action == 'CONTROLLER_ACTION') return '<span class="badge badge-primary">เข้าใช้งาน (' . Html::encode($model->action_method) . ')</span>';
                    return '<span class="badge badge-info">' . Html::encode($model->action) . '</span>';
                },
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'action',
                    'data' => array_merge(['' => 'ทั้งหมด (All Actions)'], \backend\models\ActionLogSearchModel::getPopularActionsList()),
                    'options' => ['placeholder' => 'เลือก Action...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],

            [
                'attribute' => 'message',
                'label' => 'รายละเอียด / หมายเหตุ',
                'value' => function ($model) {
                    return $model->message ?: '-';
                },
                'filter' => Html::activeTextInput($searchModel, 'message', [
                    'class' => 'form-control',
                    'placeholder' => 'ค้นหาข้อความ...',
                ]),
            ],

            [
                'attribute' => 'status',
                'label' => 'สถานะ',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->getStatusLabel();
                },
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'status',
                    'data' => array_merge(['' => 'ทั้งหมด (All Status)'], \backend\models\ActionLogModel::getStatusOptions()),
                    'options' => ['placeholder' => 'เลือกสถานะ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],

            'ip_address',

//            [
//                'attribute' => 'model_class',
//                'filter' => Html::activeTextInput($searchModel, 'model_class', ['class' => 'form-control'])
//            ],
//
//            'model_id',
            'controller',
            'action_method',
            [
                'attribute' => 'created_at',
              //  'format' => 'datetime',
                'filter' => false, // ใช้ date range filter แทน
                'value' => function ($model) {
                      return date('m-m-Y H:i:s', strtotime($model->created_at));
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<span class="fa fa-eye"></span>', $url, [
                            'title' => 'View',
                            'class' => 'btn btn-xs btn-primary'
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<span class="fa fa-trash"></span>', $url, [
                            'title' => 'Delete',
                            'class' => 'btn btn-xs btn-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this log?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
        'pager' => [
            'class' => LinkPager::className(),
            'maxButtonCount' => 10,
        ],
        'summary' => 'Showing {begin}-{end} of {totalCount} logs',
        'emptyText' => 'No logs found.',
    ]); ?>

    <!-- Bulk Actions -->
    <div class="row" style="margin-top: 15px;">
        <div class="col-md-12">
            <?= Html::beginForm(['bulk-delete'], 'post', ['id' => 'bulk-form']) ?>
            <?= Html::button('Delete Selected', [
                'class' => 'btn btn-danger',
                'onclick' => 'bulkDelete()',
            ]) ?>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?php Pjax::end(); ?>

</div>

<!-- JavaScript for bulk operations -->
<script>
    function bulkDelete() {
        var keys = $('#action-log-pjax').yiiGridView('getSelectedRows');
        if (keys.length === 0) {
            alert('Please select at least one log to delete.');
            return;
        }

        if (confirm('Are you sure you want to delete ' + keys.length + ' selected log(s)?')) {
            var form = $('#bulk-form');

            // Add selected IDs to form
            keys.forEach(function(key) {
                form.append('<input type="hidden" name="selection[]" value="' + key + '">');
            });

            form.submit();
        }
    }
</script>

<style>
    .panel {
        margin-bottom: 20px;
    }

    .panel-body h3 {
        margin: 0;
        color: #fff;
    }

    .panel-primary .panel-body h3 {
        color: #fff;
    }

    .panel-success .panel-body h3 {
        color: #fff;
    }

    .panel-danger .panel-body h3 {
        color: #fff;
    }

    .panel-info .panel-body h3 {
        color: #fff;
    }

    .grid-view .summary {
        margin-bottom: 10px;
    }

    .action-column {
        width: 80px;
    }

    .checkbox-column {
        width: 30px;
    }
</style>