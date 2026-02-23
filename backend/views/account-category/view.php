<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\AccountCategory */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'จัดการหมวดบัญชี', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="account-category-view">

    <h3><?= Html::encode($this->title) ?></h3>

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'คุณแน่ใจว่าต้องการลบรายการนี้?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <div class="card shadow-sm">
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'code',
                    'name',
                    'description:ntext',
                    [
                        'attribute' => 'status',
                        'value' => $model->status == 1 ? 'Active' : 'Inactive',
                    ],
                    'created_at:datetime',
                    'created_by',
                    'updated_at:datetime',
                    'updated_by',
                ],
            ]) ?>
        </div>
    </div>

</div>
