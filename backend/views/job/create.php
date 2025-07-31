<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Job $model */

$this->title = 'สร้างใบงาน';
$this->params['breadcrumbs'][] = ['label' => 'ใบงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-create">
    <?= $this->render('_form', [
        'model' => $model,
        'model_line' => null,
    ]) ?>

</div>
