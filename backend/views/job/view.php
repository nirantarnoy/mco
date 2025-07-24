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
                    return $data->quotation != null ? $data->quotation->quotation_no:'';
                }
            ],
            [
                'attribute' => 'job_date',
                'value' => function ($data) {
                    return date('m/d/Y', strtotime($data->job_date));
                }
            ],
            [
                'attribute' => 'start_date',
                'value' => function ($data) {
                    if ($data->start_date != null) {
                        if (date('Y', strtotime($data->start_date)) != 1970) {
                            return date('m/d/Y', strtotime($data->start_date));
                        } else {
                            return '';
                        }
                    }

                }
            ],
            [
                'attribute' => 'end_date',
                'value' => function ($data) {
                    if ($data->start_date != null) {
                        if (date('Y', strtotime($data->end_date)) != 1970) {
                            return date('m/d/Y', strtotime($data->end_date));
                        } else {
                            return '';
                        }
                    }
                }
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($data) {
                    return \backend\models\Job::getJobStatusBadge($data->status);
                }
            ],
            [
                'attribute' => 'job_amount',
                'value' => function ($data) {
                    return number_format($data->job_amount, 2);
                }
            ],
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
