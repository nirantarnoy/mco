<?php

use backend\models\BankAccount;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var backend\models\BankAccountSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'บัญชีธนาคาร';
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าทั่วไป', 'url' => ['/site/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bank-account-index">

    <div class="row">
        <div class="col-lg-10">
            <p>
                <?= Html::a(Yii::t('app', '<i class="fa fa-plus"></i> สร้างใหม่'), ['create'], ['class' => 'btn btn-success']) ?>
            </p>
        </div>
        <div class="col-lg-2" style="text-align: right">
            <form id="form-perpage" class="form-inline" action="<?= Url::to(['bank-account/index'], true) ?>"
                  method="post">
                <div class="form-group">
                    <label>แสดง </label>
                    <select class="form-control" name="perpage" id="perpage" onchange="$('#form-perpage').submit();">
                        <option value="20" <?= $perpage == '20' ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= $perpage == '50' ? 'selected' : '' ?> >50</option>
                        <option value="100" <?= $perpage == '100' ? 'selected' : '' ?>>100</option>
                    </select>
                    <label> รายการ</label>
                </div>
            </form>
        </div>
    </div>

    <?php Pjax::begin(); ?>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'emptyCell' => '-',
        'layout' => "{items}\n{summary}\n<div class='text-center'>{pager}</div>",
        'summary' => "แสดง {begin} - {end} ของทั้งหมด {totalCount} รายการ",
        'showOnEmpty' => false,
        'id' => 'bank-account-grid',
        'tableOptions' => ['class' => 'table table-hover table-bordered'],
        'emptyText' => '<div style="color: red;text-align: center;"> <b>ไม่พบรายการไดๆ</b> <span> เพิ่มรายการโดยการคลิกที่ปุ่ม </span><span class="text-success">"สร้างใหม่"</span></div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'bank_name',
            'account_no',
            'account_name',
            'branch',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($data) {
                    if ($data->status == 1) {
                        return '<div class="badge badge-success" >ใช้งาน</div>';
                    } else {
                        return '<div class="badge badge-secondary" >ไม่ใช้งาน</div>';
                    }
                }
            ],
            [
                'header' => 'ตัวเลือก',
                'headerOptions' => ['style' => 'text-align:center; width:100px;'],
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'text-align: center'],
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $data, $index) {
                        return Html::a('<span class="fas fa-eye btn btn-xs btn-default"></span>', $url, [
                            'title' => 'ดู',
                            'data-pjax' => '0',
                        ]);
                    },
                    'update' => function ($url, $data, $index) {
                        return Html::a('<span class="fas fa-edit btn btn-xs btn-default"></span>', $url, [
                            'title' => 'แก้ไข',
                            'data-pjax' => '0',
                        ]);
                    },
                    'delete' => function ($url, $data, $index) {
                        return Html::a('<span class="fas fa-trash-alt btn btn-xs btn-default"></span>', $url, [
                            'title' => 'ลบ',
                            'data-confirm' => 'คุณต้องการลบรายการนี้ใช่หรือไม่?',
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ]);
                    }
                ]
            ],
        ],
        'pager' => ['class' => \yii\widgets\LinkPager::className()],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
