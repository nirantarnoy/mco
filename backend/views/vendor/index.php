<?php

use backend\models\Vendor;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
//use yii\widgets\LinkPager;
use yii\widgets\Pjax;
use yii\bootstrap4\LinkPager;
/** @var yii\web\View $this */
/** @var backend\models\VendorSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ผู้ขาย';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vendor-index">

    <?php Pjax::begin(); ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="btn-group">
                                <?= Html::a(Yii::t('app', '<i class="fa fa-plus"></i> สร้างใหม่'), ['create'], ['class' => 'btn btn-success']) ?>
                                <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseImport" aria-expanded="false" aria-controls="collapseImport">
                                    <i class="fa fa-upload"></i> นำเข้าข้อมูล
                                </button>
                                <?= Html::a(Yii::t('app', '<i class="fa fa-download"></i> Export vendors'), ['export-vendors'], ['class' => 'btn btn-info']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="collapse" id="collapseImport">
                        <div class="card card-body mt-3">
                            <form action="<?= \yii\helpers\Url::to(['vendor/importvendor'], true) ?>" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <input type="file" name="file_vendor" class="form-control" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <button class="btn btn-success"><i class="fa fa-check"></i> ยืนยันนำเข้า</button>
                                    </div>
                                    <div class="col-lg-6">
                                        <small class="text-muted">รูปแบบไฟล์: COMPANY, ADDRESS, TAXID (ข้ามแถวแรก)</small>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br />
    <?php echo $this->render('_search', ['model' => $searchModel,'viewstatus'=>null]); ?>
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
                'contentOptions' => ['style' => 'text-align: center'],
            ],
            'code',
            'name',
            'description',
            'home_number',
            'street',
            'aisle',
            'district_name',
            'city_name',
            'province_name',
            'contact_name',
            'phone',
            'branch_name',
            'email',
            //'status',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [

                'header' => 'ตัวเลือก',
                'headerOptions' => ['style' => 'text-align:center;', 'class' => 'activity-view-link',],
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'text-align: center'],
                'template' => '{view} {update}{delete}',
                'buttons' => [
                    'view' => function ($url, $data, $index) {
                        $options = [
                            'title' => Yii::t('yii', 'View'),
                            'aria-label' => Yii::t('yii', 'View'),
                            'data-pjax' => '0',
                        ];
                        return Html::a(
                            '<span class="fas fa-eye btn btn-xs btn-default"></span>', $url, $options);
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
                        return Html::a('<span class="fas fa-trash-alt btn btn-xs btn-default"></span>', 'javascript:void(0)', $options);
                    }
                ]
            ],
        ],
        'pager' => ['class' => LinkPager::className()],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
