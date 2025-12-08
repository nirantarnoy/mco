<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\DeliveryNoteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'ใบส่งของ';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="delivery-note-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="fas fa-plus"></i> สร้างใบส่งของ', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'dn_no',
            'date',
            [
                'attribute' => 'job_id',
                'value' => function ($model) {
                    return $model->job ? $model->job->job_no : '-';
                },
            ],
            'customer_name',
            'our_ref',
            //'ref_no',
            //'status',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete} {print}',
                'buttons' => [
                    'print' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-print"></i>', ['print', 'id' => $model->id], [
                            'title' => 'พิมพ์',
                            'target' => '_blank',
                            'data-pjax' => '0',
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>
