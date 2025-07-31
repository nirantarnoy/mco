<?php

use yii\helpers\Html;
use backend\models\Invoice;

/* @var $this yii\web\View */
/* @var $model backend\models\Invoice */
/* @var $items backend\models\InvoiceItem[] */
/* @var $customers array */

$this->title = 'แก้ไข: ' . $model->invoice_number;
$this->params['breadcrumbs'][] = ['label' => 'จัดการเอกสาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->invoice_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="invoice-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <?= Html::a('<i class="fas fa-eye"></i> ดูรายละเอียด', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-info']) ?>
            <?= Html::a('<i class="fas fa-list"></i> รายการเอกสาร', ['index'], ['class' => 'btn btn-outline-primary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'items' => $items,
        'customers' => $customers,
    ]) ?>

</div>