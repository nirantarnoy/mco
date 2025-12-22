<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PurchSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'รายงานสรุปยอดสั่งซื้อตามผู้ขาย';
$this->params['breadcrumbs'][] = ['label' => 'ใบสั่งซื้อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purch-report-vendor-summary">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="get" action="<?= \yii\helpers\Url::to(['report-vendor-summary']) ?>">
                        <input type="hidden" name="r" value="purch/report-vendor-summary">
                        <div class="row">
                            <div class="col-md-3">
                                <label>จากวันที่</label>
                                <?= DatePicker::widget([
                                    'name' => 'from_date',
                                    'value' => $from_date,
                                    'pluginOptions' => [
                                        'format' => 'yyyy-mm-dd',
                                        'autoclose' => true,
                                        'todayHighlight' => true
                                    ]
                                ]); ?>
                            </div>
                            <div class="col-md-3">
                                <label>ถึงวันที่</label>
                                <?= DatePicker::widget([
                                    'name' => 'to_date',
                                    'value' => $to_date,
                                    'pluginOptions' => [
                                        'format' => 'yyyy-mm-dd',
                                        'autoclose' => true,
                                        'todayHighlight' => true
                                    ]
                                ]); ?>
                            </div>
                            <div class="col-md-3">
                                <label>ผู้ขาย</label>
                                <?= \kartik\select2\Select2::widget([
                                    'name' => 'vendor_id',
                                    'value' => $vendor_id,
                                    'data' => \yii\helpers\ArrayHelper::map(\backend\models\Vendor::find()->all(), 'id', 'name'),
                                    'options' => ['placeholder' => 'เลือกผู้ขาย...'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ]); ?>
                            </div>
                            <div class="col-md-3" style="padding-top: 30px;">
                                <button type="submit" class="btn btn-primary">ค้นหา</button>
                                <a href="<?= \yii\helpers\Url::to(['report-vendor-summary']) ?>" class="btn btn-default">รีเซ็ต</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <br>

    <div class="card">
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                //'filterModel' => $searchModel,
                'showFooter' => true,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'vendor_id',
                        'label' => 'ผู้ขาย',
                        'value' => function ($model) {
                            return \backend\models\Vendor::findName($model['vendor_id']);
                        },
                    ],
                    [
                        'attribute' => 'po_count',
                        'label' => 'จำนวนใบสั่งซื้อ',
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'headerOptions' => ['style' => 'text-align: right;'],
                        'value' => function ($model) {
                            return number_format($model['po_count']);
                        },
                        'footer' => number_format(array_sum(array_map(function($model){ return $model['po_count']; }, $dataProvider->getModels()))),
                        'footerOptions' => ['style' => 'text-align: right; font-weight: bold;'],
                    ],
                    [
                        'attribute' => 'total_amount',
                        'label' => 'ยอดรวมสุทธิ',
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'headerOptions' => ['style' => 'text-align: right;'],
                        'value' => function ($model) {
                            return number_format((float)$model['total_amount'], 2);
                        },
                        'footer' => number_format(array_sum(array_map(function($model){ return (float)$model['total_amount']; }, $dataProvider->getModels())), 2),
                        'footerOptions' => ['style' => 'text-align: right; font-weight: bold;'],
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>
