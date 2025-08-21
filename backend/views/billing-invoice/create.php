<?php
// backend/views/billing-invoice/create.php
use yii\helpers\Html;

$this->title = 'สร้างใบวางบิล';
$this->params['breadcrumbs'][] = ['label' => 'ใบวางบิล', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="billing-invoice-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>