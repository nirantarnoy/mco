<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Product $model */

$this->title = $model->code.' - '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'สินค้า', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="product-view">

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <?php
    $attributes = [
        'name',
        'description',
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
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function ($model) {
                return $model->status == 1 ? '<div class="badge badge-success" style="padding: 10px;">ใช้งาน</div>' : '<div class="badge badge-secondary">ไม่ใช้งาน</div>';
            }
        ],
        [
            'attribute' => 'created_at',
            'format' => ['date', 'php:d-m-Y H:i:s'],
        ],
        [
            'attribute' => 'created_by',
            'value' => function ($model) {
                return \backend\models\User::findName($model->created_by);
            }
        ],
        [
            'attribute' => 'updated_at',
            'format' => ['date', 'php:d-m-Y H:i:s'],
        ],
        [
            'attribute' => 'updated_by',
            'value' => function ($model) {
                return \backend\models\User::findName($model->updated_by);
            }
        ],
    ];

    $attributes2 = [
        [
            'attribute' => 'product_type_id',
            'value' => function ($data) {
                return \backend\helpers\ProductType::getTypeById($data->product_type_id);
            }
        ],
        [
            'attribute' => 'type_id',
            'value' => function ($data) {
                return \backend\helpers\CatType::getTypeById($data->type_id);
            }
        ],
    ];
    if (\Yii::$app->user->can('ViewCostPrice')) {
        $attributes2[] = [
            'attribute' => 'cost_price',
            'value' => function ($model) {
                return number_format($model->cost_price, 2);
            }
        ];
    }
    if (\Yii::$app->user->can('ViewSalePrice')) {
        $attributes2[] = [
            'attribute' => 'sale_price',
            'value' => function ($model) {
                return number_format($model->sale_price, 2);
            }
        ];
    }

    ?>
    <div class="row">
        <div class="col-lg-6">
            <?= DetailView::widget(['model' => $model,
                'attributes' => $attributes,]) ?>
        </div>
        <div class="col-lg-6">
            <?= DetailView::widget(['model' => $model,
                'attributes' => $attributes2,]) ?>
        </div>
    </div>


</div>
