<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Job $model */

$this->title = $model->job_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="job-view">
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
         //   'id',
            'job_no',
            [
                'attribute' => 'quotation_id',
                'value' => function ($data) {
                    return $data->quotation->quotation_no;
                }
            ],
            'job_date',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($data) {
                    return \backend\models\Job::getJobStatusBadge($data->status);
                }
            ],
            'job_amount',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ],
    ]) ?>

</div>
