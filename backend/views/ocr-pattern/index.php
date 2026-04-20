<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการ OCR Pattern';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ocr-pattern-index">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-plus"></i> เพิ่มรูปแบบใหม่', ['create'], ['class' => 'btn btn-success']) ?>
            </div>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'name',
                    'tax_id',
                    [
                        'attribute' => 'status',
                        'value' => function($model) {
                            return $model->status == 1 ? '<span class="badge badge-success">ใช้งาน</span>' : '<span class="badge badge-danger">ระงับ</span>';
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'updated_at',
                        'value' => function($model) {
                            return $model->updated_at ? date('d/m/Y H:i', $model->updated_at) : '-';
                        }
                    ],
                    ['class' => 'yii\grid\ActionColumn'],
                ],
            ]); ?>
        </div>
    </div>
</div>
