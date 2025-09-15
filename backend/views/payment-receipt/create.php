<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Job $model */

$this->title = 'สร้างรายการรับชำระเงิน';
$this->params['breadcrumbs'][] = ['label' => 'รับชำระเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-create">
    <?= $this->render('_form', [
        'model' => $model,
       // 'model_line' => null,
    ]) ?>

</div>
