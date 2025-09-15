<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Job $model */

$this->title = 'แก้ไขชำระเงิน: ' . $model->job_no;
$this->params['breadcrumbs'][] = ['label' => 'ับชำระเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->job_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="job-update">
    <?= $this->render('_form', [
        'model' => $model,
     //   'model_line' => $model_line
    ]) ?>

</div>
