<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AccountCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการหมวดบัญชี';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-category-index">

    <div class="card card-white shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title text-primary mb-0"><i class="fas fa-list-ul me-2"></i><?= Html::encode($this->title) ?></h3>
            <div class="card-tools ml-auto">
                <?= Html::a('<i class="fas fa-plus-circle"></i> เพิ่มหมวดบัญชีใหม่', ['create'], ['class' => 'btn btn-success']) ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => true,
                'bordered' => false,
                'striped' => true,
                'hover' => true,
                'tableOptions' => ['class' => 'table table-hover mb-0'],
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['style' => 'width: 50px;', 'class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],

                    [
                        'attribute' => 'code',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'contentOptions' => ['class' => 'font-weight-bold text-primary'],
                    ],
                    'name',
                    'description:ntext',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function($model) {
                            $class = $model->status == 1 ? 'badge-success' : 'badge-danger';
                            $label = $model->status == 1 ? 'Active' : 'Inactive';
                            return '<span class="badge ' . $class . '" style="font-size: 90%;">' . $label . '</span>';
                        },
                        'filter' => [1 => 'Active', 0 => 'Inactive'],
                        'headerOptions' => ['style' => 'width: 120px;', 'class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'headerOptions' => ['style' => 'width: 100px;', 'class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'buttonOptions' => ['class' => 'btn btn-xs btn-outline-secondary'],
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>

<style>
.card { border-radius: 8px; border: none; overflow: hidden; }
.card-header { padding: 15px 20px; border-bottom: 1px solid rgba(0,0,0,.05); }
.table thead th { background-color: #f8f9fa; border-top: none; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
.badge { padding: 6px 10px; border-radius: 4px; }
</style>
