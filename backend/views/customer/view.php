<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Customer $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'ลูกค้า', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="customer-view">

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

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'code',
            'name',
            'description',
            'taxid',
            [
                'label' => 'ที่อยู่',
                'value' => function ($model) {
                    $addr = \backend\helpers\AddressHelper::formatFullAddress([
                        'home_number' => $model->home_number,
                        'street' => $model->street,
                        'aisle' => $model->aisle,
                        'district_name' => $model->district_name,
                        'city_name' => $model->city_name,
                        'province_name' => $model->province_name,
                        'zipcode' => $model->zipcode,
                    ]);
                    return !empty($addr) ? $addr : '-';
                }
            ],
            'customer_group_id',
            'status',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ],
    ]) ?>

</div>
