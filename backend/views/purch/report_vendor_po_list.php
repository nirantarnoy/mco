<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $vendor_id int */
/* @var $from_date string */
/* @var $to_date string */

$vendor_name = \backend\models\Vendor::findName($vendor_id);
$this->title = 'รายการใบสั่งซื้อ: ' . $vendor_name;
$this->params['breadcrumbs'][] = ['label' => 'รายงานสรุปยอดสั่งซื้อตามผู้ขาย', 'url' => ['report-vendor-summary', 'from_date' => $from_date, 'to_date' => $to_date]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purch-report-vendor-po-list">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <?php if ($from_date && $to_date): ?>
                <div class="card-tools">
                    <span class="badge badge-info">ช่วงวันที่: <?= Html::encode($from_date) ?> ถึง <?= Html::encode($to_date) ?></span>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'showFooter' => true,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'purch_no',
                        'label' => 'เลขที่ใบสั่งซื้อ',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a($model->purch_no, ['view', 'id' => $model->id], ['target' => '_blank']);
                        },
                    ],
                    [
                        'attribute' => 'purch_date',
                        'label' => 'วันที่',
                        'value' => function ($model) {
                            return date('d/m/Y', strtotime($model->purch_date));
                        },
                    ],
                    [
                        'attribute' => 'net_amount',
                        'label' => 'ยอดรวมทั้งหมด',
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'headerOptions' => ['style' => 'text-align: right;'],
                        'value' => function ($model) {
                            return number_format($model->net_amount, 2);
                        },
                        'footer' => number_format(array_sum(array_map(function($model){ return $model->net_amount; }, $dataProvider->getModels())), 2),
                        'footerOptions' => ['style' => 'text-align: right; font-weight: bold;'],
                    ],
                    [
                        'attribute' => 'status',
                        'label' => 'สถานะ',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->getStatusLabel();
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>
