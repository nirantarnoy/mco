<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Job $model */

$this->title = 'แก้ไขใบงาน: ' . $model->job_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->job_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="job-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
