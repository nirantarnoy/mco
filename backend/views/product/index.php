<?php

use backend\models\Product;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;

use kartik\grid\GridView;

//use yii\grid\GridView;
use yii\widgets\Pjax;

//use yii\widgets\LinkPager;
use yii\bootstrap4\LinkPager;

/** @var yii\web\View $this */
/** @var backend\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'สินค้า';
$this->params['breadcrumbs'][] = $this->title;
?>

    <div id="loading"
         style="display:none; position: fixed; top: 0; left: 0; z-index: 9999; width: 100%; height: 100%; background-color: rgba(255,255,255,0.7); text-align: center; padding-top: 20%;">
        <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><br>กำลังโหลดข้อมูล...
    </div>
    <div class="employee-index">
        <?php Pjax::begin(); ?>
        <div class="row">
            <div class="col-lg-10">
                <div class="btn-group">
                    <?= Html::a(Yii::t('app', '<i class="fa fa-plus"></i> สร้างใหม่'), ['create'], ['class' => 'btn btn-success']) ?>
                    <form action="<?= Url::to(['product/export-products'], true) ?>" method="post"
                          style="margin-left: 10px">
                        <button class="btn btn-info"><i class="fa fa-download"></i> Download product pattern</button>
                    </form>

                </div>

            </div>
            <div class="col-lg-2" style="text-align: right">
                <form id="form-perpage" class="form-inline" action="<?= Url::to(['product/index'], true) ?>"
                      method="post">
                    <div class="form-group">
                        <label>แสดง </label>
                        <select class="form-control" name="perpage" id="perpage" onchange="$('#form-perpage').submit()">
                            <option value="20" <?= $perpage == '20' ? 'selected' : '' ?>>20</option>
                            <option value="50" <?= $perpage == '50' ? 'selected' : '' ?> >50</option>
                            <option value="100" <?= $perpage == '100' ? 'selected' : '' ?>>100</option>
                        </select>
                        <label> รายการ</label>
                    </div>
                </form>
            </div>
        </div>
        <br/>

        <?php echo $this->render('_search', ['model' => $searchModel, 'viewstatus' => $viewstatus]); ?>
        <div id="div-delete-btn" style="padding: 10px;display: none">
            <?php echo Html::button('ลบ', [
                'class' => 'btn btn-danger',
                'id' => 'bulk-delete-btn',
                'data-url' => Url::to(['bulk-delete']),
            ]); ?>
            <?= Html::submitButton('Add To Print Tag', [
                'class' => 'btn btn-primary',
                'id' => 'print-tag-btn',
                'style' => 'display: none;',
                'data-url' => Url::to(['print-tag']),
            ]) ?>
        </div>


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
            'pjax' => true,

            // 'pjaxSettings' => ['neverTimeout' => true],
            //'tableOptions' => ['class' => 'table table-hover'],
            'emptyText' => '<div style="color: red;text-align: center;"> <b>ไม่พบรายการไดๆ</b> <span> เพิ่มรายการโดยการคลิกที่ปุ่ม </span><span class="text-success">"สร้างใหม่"</span></div>',
            'columns' => [
                ['class' => 'kartik\grid\CheckboxColumn'],
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => ['style' => 'text-align:center;'],
                    'contentOptions' => ['style' => 'text-align: center'],
                ],
                [
                    'attribute' => 'photo',
                    'headerOptions' => ['style' => 'text-align: center'],
                    'contentOptions' => ['style' => 'text-align: center'],
                    'format' => 'raw',
                    'value' => function ($data) {
                        $url = \Yii::$app->request->hostInfo . Yii::$app->request->baseUrl . '/uploads/product_photo/' . $data->photo;
                        return Html::a(
                            Html::img($url, ['style' => 'max-width:50px']),
                            $url,
                            [
                                'target' => '_blank',
                                'encode' => false,
                                'data-pjax' => 0,
                            ],
                        );
                        //  return '<a href="' . \Yii::$app->request->hostInfo . Yii::$app->request->baseUrl . '/uploads/product_photo/' . $data->photo . '" target="_blank">' .$data->photo . '</a>';
                    }
                ],
                'code',
                'name',
                //  'description',
                // 'product_type_id',
                [
                    'attribute' => 'product_group_id',
                    'value' => function ($data) {
                        return \backend\models\Productgroup::findName($data->product_group_id);
                    }
                ],
                [
                    'attribute' => 'brand_id',
                    'value' => function ($data) {
                        return \backend\models\Productbrand::findName($data->brand_id);
                    }
                ],
                [
                    'attribute' => 'warehouse_id',
                    'label' => 'คลัง',
                    'format' => 'raw',
                    'value' => function ($data) {
                        return \backend\models\Product::findWarehouseOnhand($data->id);
                    }
                ],
                [
                    'attribute' => 'sale_price',
                    'headerOptions' => ['style' => 'text-align: center'],
                    'contentOptions' => ['style' => 'text-align: center'],
                    'value' => function ($data) {
                        return $data->sale_price != null ? number_format($data->sale_price, 0) : '0';
                    }
                ],
                [
                    'attribute' => 'unit_id',
                    'value' => function ($data) {
                        return \backend\models\Unit::findName($data->unit_id);
                    }
                ],
//                [
//                    'attribute' => 'product_type_id',
//                    'headerOptions' => ['style' => 'text-align: center'],
//                    'contentOptions' => ['style' => 'text-align: center'],
//                    'value' => function ($data) {
//                        return \backend\helpers\ProductType::getTypeById($data->product_type_id);
//                    }
//                ],
//                [
//                    'attribute' => 'type_id',
//                    'headerOptions' => ['style' => 'text-align: center'],
//                    'contentOptions' => ['style' => 'text-align: center'],
//                    'value' => function ($data) {
//                        return \backend\helpers\CatType::getTypeById($data->type_id);
//                    }
//                ],
                //'status',
                //'last_price',
                //'std_price',
                //'company_id',
                [
                    'attribute' => 'minimum_stock',
                    'label' => 'Min.Stock',
                    'headerOptions' => ['style' => 'text-align: center'],
                    'contentOptions' => ['style' => 'text-align: center'],
                    'value' => function ($data) {
                        return number_format($data->minimum_stock, 0);
                    }
                ],
                [
                    'attribute' => 'stock_qty',
                    'headerOptions' => ['style' => 'text-align: center'],
                    'contentOptions' => ['style' => 'text-align: center'],
                    'format' => 'raw',
                    'value' => function ($data) {
                        if ($data->stock_qty < $data->minimum_stock) {
                            return '<div style="color: red">' . $data->stock_qty . '</div>';
                        } else {
                            return number_format($data->stock_qty, 0);
                        }

                    }
                ],
                [
                    'attribute' => 'information',
                    'label' => 'แจ้งเตือน',
                    'format' => 'raw',
                    'headerOptions' => ['style' => 'text-align: center'],
                    'contentOptions' => ['style' => 'text-align: center'],
                    'value' => function ($data) {
                        if ($data->stock_qty < $data->minimum_stock) {
                            return '<div class="badge badge-pill badge-danger" style="padding: 10px;">Low Stock</div>';
                        } else {
                            return '';
                        }
                    }
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'headerOptions' => ['style' => 'text-align: center'],
                    'contentOptions' => ['style' => 'text-align: center'],
                    'value' => function ($data) {
                        if ($data->status == 1) {
                            return '<div class="badge badge-pill badge-success" style="padding: 10px;">ใช้งาน</div>';
                        } else {
                            return '<div class="badge badge-pill badge-secondary" style="padding: 10px;">ไม่ใช้งาน</div>';
                        }
                    }
                ],
//                [
//                    'attribute' => 'stock_qty',
//                    'label' => 'คงเหลือ',
//                    'headerOptions' => ['style' => 'text-align: right'],
//                    'contentOptions' => ['style' => 'text-align: right'],
//                    'value' => function ($data) {
//                        // $qty = \backend\models\Product::getTotalQty($data->id);
//                        return number_format($data->stock_qty, 0);
//                    }
//                ],

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

    <br/>
    <div class="row" style="display: nonex;">
        <form action="<?= \yii\helpers\Url::to(['product/importproduct'], true) ?>" method="post"
              enctype="multipart/form-data">

            <input type="file" name="file_product" class="form-control">
            <br/>
            <button class="btn btn-success">Import</button>
        </form>
    </div>

    <form id="form-print-tag" action="<?= Url::to(['product/print-tag'], true) ?>" method="post">
        <input type="hidden" name="ids" id="print-product-tag-id" value="">
    </form>
<?php
$this->registerJs(<<<JS

function toggleDeleteButton() {
    let selected = $('#product-grid').yiiGridView('getSelectedRows');
    if (selected.length > 0) {
        $('#div-delete-btn').show();
        $("#bulk-delete-btn").text("ลบ " + selected.length + " รายการ");
        
       //   $('#print-tag-btn').show();
    } else {
        $('#div-delete-btn').hide();
        $("#bulk-delete-btn").text("ลบ");
        
       //  $('#print-tag-btn').hide();
    }
}


// ฟังก์ชัน bind checkbox ภายใน grid
function bindCheckboxEvent() {
    $('#product-grid').off('change', 'input[name="selection[]"]').on('change', 'input[name="selection[]"]', function () {
        toggleDeleteButton();
    });

    // กรณีคลิก checkbox ทั้งหมด
    $('#product-grid').off('change', 'input[name="selection_all"]').on('change', 'input[name="selection_all"]', function () {
        setTimeout(toggleDeleteButton, 100); // ต้องหน่วงเวลาเล็กน้อยให้ checkbox อัปเดตก่อน
    });
}

// โหลดครั้งแรก
$(document).ready(function () {
    bindCheckboxEvent();
    toggleDeleteButton(); // เช็ค checkbox เดิมด้วย
});

// ตอน PJAX เริ่มโหลด
$(document).on('pjax:start', function() {
    $('#loading').fadeIn();
});

// ตอน PJAX โหลดเสร็จ
$(document).on('pjax:end', function() {
    $('#loading').fadeOut();
    bindCheckboxEvent(); // bind ใหม่หลัง PJAX
    toggleDeleteButton(); // อัปเดตสถานะปุ่มลบ
});

// // ปุ่มลบหลายรายการ
// $('#bulk-delete-btn').on('click', function() {
//     var keys = $('#product-grid').yiiGridView('getSelectedRows');
//     if (keys.length === 0) {
//         alert('กรุณาเลือกรายการที่ต้องการลบ');
//         return;
//     }
//     if (confirm('คุณแน่ใจว่าต้องการลบรายการที่เลือก?')) {
//         $.post($(this).data('url'), {ids: keys}, function(response) {
//             $.pjax.reload({container: '#p0'}); // แก้ให้ตรง container ID ของคุณ
//         });
//     }
// });

// ใช้ delegation เพื่อให้รองรับ DOM ใหม่จาก PJAX
$(document).on('click', '#bulk-delete-btn', function() {
    var keys = $('#product-grid').yiiGridView('getSelectedRows');
    if (keys.length === 0) {
        alert('กรุณาเลือกรายการที่ต้องการลบ');
        return;
    }
    if (confirm('คุณแน่ใจว่าต้องการลบรายการที่เลือก?')) {
        $.post($(this).data('url'), {ids: keys}, function(response) {
            $.pjax.reload({container: '#p0'}); // หรือ container id จริงของ PJAX
        });
    }
});

$('#print-tag-btn').on('click', function() {
    var keys = $('#product-grid').yiiGridView('getSelectedRows');
    if (keys.length === 0) {
        alert('กรุณาเลือกรายการที่ต้องการปริ้น');
        return;
    }
    
    $("#print-product-tag-id").val(keys);
    $("#form-print-tag").attr("url", $(this).data('url'));
    $("#form-print-tag").submit();
    // //if (confirm('คุณแน่ใจว่าต้องการลบรายการที่เลือก?')) {
    //     $.post($(this).data('url'), {ids: keys}, function(response) {
    //         $.pjax.reload({container: '#p0'}); // แก้ให้ตรง container ID ของคุณ
    //     });
    // //}
});


JS
);

?>

