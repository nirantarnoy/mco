<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\TempInvoice $model */

$this->title = 'Create Temp Invoice';
$this->params['breadcrumbs'][] = ['label' => 'Temp Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="temp-invoice-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
