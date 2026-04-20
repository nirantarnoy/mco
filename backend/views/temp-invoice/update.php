<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\TempInvoice $model */

$this->title = 'Update Temp Invoice: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Temp Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="temp-invoice-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
