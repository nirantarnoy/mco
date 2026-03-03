<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\ChartOfAccount $model */

$this->title = 'Create Chart Of Account';
$this->params['breadcrumbs'][] = ['label' => 'Chart Of Accounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chart-of-account-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
