<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Employer $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'นายจ้าง', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="employer-view">
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
            //'id',
            'name',
            'description',
            'phone',
            'idcard_no',
            'doc',
            'status',
            [
                'attribute' => 'created_at',
                'label' => 'วันที่สร้าง',
                'format' => ['datetime', 'php:m/d/Y H:i'],
            ],
            [
                'attribute' => 'created_by',
                'label' => 'สร้างโดย',
                'value' => function ($model) {
                    return \backend\models\User::findEmployeeNameByUserId($model->created_by);
                }
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'วันที่แก้ไข',
                'format' => ['datetime', 'php:m/d/Y H:i'],
            ],
            [
                'attribute' => 'updated_by',
                'label' => 'แก้ไขโดย',
                'value' => function ($model) {
                    return \backend\models\User::findEmployeeNameByUserId($model->updated_by);
                }
            ],
        ],
    ]) ?>

</div>
