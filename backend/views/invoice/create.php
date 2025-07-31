<?php

use yii\helpers\Html;
use backend\models\Invoice;

/* @var $this yii\web\View */
/* @var $model backend\models\Invoice */
/* @var $items backend\models\InvoiceItem[] */
/* @var $customers array */

$typeLabels = Invoice::getTypeOptions();
$currentTypeLabel = isset($typeLabels[$model->invoice_type]) ? $typeLabels[$model->invoice_type] : 'เอกสาร';

$this->title = 'สร้าง' . $currentTypeLabel;
$this->params['breadcrumbs'][] = ['label' => 'จัดการเอกสาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="invoice-create">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="fas fa-plus-circle text-success"></i>
            <?= Html::encode($this->title) ?>
        </h1>
        <div>
            <?= Html::a('<i class="fas fa-list"></i> รายการเอกสาร', ['index'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> เลือกประเภทใหม่', ['select'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'items' => $items,
        'customers' => $customers,
    ]) ?>

</div>