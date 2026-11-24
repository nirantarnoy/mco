<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap4\LinkPager;

$this->title = 'ผู้ใช้งาน';
$this->params['breadcrumbs'][] = $this->title;

$isAdmin = \backend\models\User::isUserAdmin();
?>
<div class="user-index">
    <?php if (Yii::$app->session->hasFlash('msg_success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= Yii::$app->session->getFlash('msg_success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('msg_err')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= Yii::$app->session->getFlash('msg_err') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    <?php Pjax::begin(); ?>
    <div class="row">
        <div class="col-lg-10">
            <p>
                <?= Html::a(Yii::t('app', '<i class="fa fa-plus"></i> สร้างใหม่'), ['create'], ['class' => 'btn btn-success']) ?>
                <?php if ($isAdmin): ?>
                    <?= Html::a('<i class="fas fa-key"></i> รีเซ็ตรหัสผ่านทั้งหมด (ยกเว้น Admin)', ['resetpasswordall'], [
                        'class' => 'btn btn-warning ml-2',
                        'data' => [
                            'confirm' => 'คุณต้องการรีเซ็ตรหัสผ่านของผู้ใช้งานทั้งหมด (ยกเว้น Admin) เป็น Na12345678 ใช่หรือไม่?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="col-lg-2" style="text-align: right">
            <form id="form-perpage" class="form-inline" action="<?= Url::to(['user/index'], true) ?>"
                  method="post">
                <div class="form-group">
                    <label>แสดง </label>
                    <select class="form-control" name="perpage" id="perpage">
                        <option value="20" <?= $perpage == '20' ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= $perpage == '50' ? 'selected' : '' ?> >50</option>
                        <option value="100" <?= $perpage == '100' ? 'selected' : '' ?>>100</option>
                    </select>
                    <label> รายการ</label>
                </div>
            </form>
        </div>
    </div>
    <?php echo $this->render('_search', ['model' => $searchModel,'viewstatus'=>$viewstatus]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'emptyCell' => '-',
        'layout' => "{items}\n{summary}\n<div class='text-center'>{pager}</div>",
        'summary' => "แสดง {begin} - {end} ของทั้งหมด {totalCount} รายการ",
        'showOnEmpty' => false,
        //    'bordered' => true,
        //     'striped' => false,
        //    'hover' => true,
        'id' => 'product-grid',
        //'tableOptions' => ['class' => 'table table-hover'],
        'emptyText' => '<div style="color: red;text-align: center;"> <b>ไม่พบรายการไดๆ</b> <span> เพิ่มรายการโดยการคลิกที่ปุ่ม </span><span class="text-success">"สร้างใหม่"</span></div>',
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['style' => 'text-align: center'],
                'contentOptions' => ['style' => 'text-align: center']],
            'username',
            [
                'attribute' => 'user_group_id',
                'label'=>'กลุ่มผู้ใช้งาน',
                'headerOptions' => ['style' => 'text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'value' => function ($data) {
                   return \backend\models\Usergroup::findName($data->user_group_id);
                }
            ],
//            [
//                'attribute' => 'employee_ref_id',
//                'label'=>'พนักงาน',
//                'headerOptions' => ['style' => 'text-align: center'],
//                'contentOptions' => ['style' => 'text-align: left'],
//                'value' => function ($data) {
//                    return \backend\models\Employee::findFullName($data->employee_ref_id);
//                }
//            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'label'=>'สถานะ',
                'headerOptions' => ['style' => 'text-align: center'],
                'contentOptions' => ['style' => 'text-align: center'],
                'value' => function ($data) {
                    if ($data->status == 1) {
                        return '<div class="badge badge-pill badge-success" style="padding: 10px;">ใช้งาน</div>';
                    } else {
                        return '<div class="badge badge-pill badge-success" style="padding: 10px;">ไม่ใช้งาน</div>';
                    }
                }
            ],
            //'company_id',
            //'branch_id',
            //'created_at',
            //'updated_at',
            //'created_by',
            //'updated_by',

            [

                'header' => 'ตัวเลือก',
                'headerOptions' => ['style' => 'text-align:center;', 'class' => 'activity-view-link',],
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'text-align: center'],
                'template' => $isAdmin ? '{updatecustomer} {update} {resetpassword} {delete}' : '{updatecustomer} {update} {delete}',
                'buttons' => [
//                    'view' => function ($url, $data, $index) {
//                        $options = [
//                            'title' => Yii::t('yii', 'View'),
//                            'aria-label' => Yii::t('yii', 'View'),
//                            'data-pjax' => '0',
//                        ];
//                        return Html::a(
//                            '<span class="fas fa-eye btn btn-xs btn-default"></span>', $url, $options);
//                    },
                    'updatecustomer' => function ($url, $data, $index) {
                        $options = [
                            'title' => Yii::t('yii', 'View'),
                            'aria-label' => Yii::t('yii', 'View'),
                            'data-pjax' => '0',
                        ];
                        return Html::a(
                            '<span class="fas fa-user btn btn-xs btn-default"></span>', $url, $options);
                    },
                    'update' => function ($url, $data, $index) {
                        $options = array_merge([
                            'title' => Yii::t('yii', 'Update'),
                            'aria-label' => Yii::t('yii', 'Update'),
                            'data-pjax' => '0',
                            'id' => 'modaledit',
                        ]);
                        return Html::a(
                            '<span class="fas fa-edit btn btn-xs btn-default"></span>', $url, [
                            'id' => 'activity-view-link',
                            //'data-toggle' => 'modal',
                            // 'data-target' => '#modal',
                            'data-id' => $index,
                            'data-pjax' => '0',
                            // 'style'=>['float'=>'rigth'],
                        ]);
                    },
                    'resetpassword' => function ($url, $data, $index) use ($isAdmin) {
                        if (!$isAdmin) {
                            return '';
                        }
                        if (\backend\models\User::isProtectedAdmin($data->id)) {
                            return '';
                        }
                        return Html::a(
                            '<span class="fas fa-key btn btn-xs btn-outline-warning" style="margin-left: 2px;" title="รีเซ็ตรหัสผ่านเป็น Na12345678"></span>',
                            ['resetpassword', 'id' => $data->id],
                            [
                                'title' => 'รีเซ็ตรหัสผ่าน',
                                'data-pjax' => '0',
                                'data-confirm' => "คุณต้องการรีเซ็ตรหัสผ่านของผู้ใช้ '{$data->username}' เป็น Na12345678 ใช่หรือไม่?",
                                'data-method' => 'post',
                            ]
                        );
                    },
                    'delete' => function ($url, $data, $index) {
                        $options = array_merge([
                            'title' => Yii::t('yii', 'Delete'),
                            'aria-label' => Yii::t('yii', 'Delete'),
                            //'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                            //'data-method' => 'post',
                            //'data-pjax' => '0',
                            'data-url' => $url,
                            'data-var' => $data->id,
                            'onclick' => 'recDelete($(this));'
                        ]);
                        return $data->id == 1 ? '' : Html::a('<span class="fas fa-trash-alt btn btn-xs btn-default"></span>', 'javascript:void(0)', $options);
                    }
                ]
            ],
        ],
        'pager' => ['class' => LinkPager::className()],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
