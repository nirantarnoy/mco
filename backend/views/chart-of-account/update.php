<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\ChartOfAccount $model */

$this->title = 'Update Chart Of Account: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Chart Of Accounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="chart-of-account-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
