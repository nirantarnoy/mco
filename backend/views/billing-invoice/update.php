<?php
// backend/views/billing-invoice/create.php
use yii\helpers\Html;

$this->title = 'แก้ไขใบวางบิล'.$model->billing_number;
$this->params['breadcrumbs'][] = ['label' => 'ใบวางบิล', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="billing-invoice-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>