<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\PettyCashVoucher */
/* @var $details backend\models\PettyCashDetail[] */

$this->title = 'แก้ไขใบสำคัญจ่ายเงินสดย่อย: ' . $model->pcv_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบสำคัญจ่ายเงินสดย่อย', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->pcv_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="petty-cash-voucher-update">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="fas fa-edit text-primary"></i> <?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-eye"></i> ดูรายละเอียด', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-info']) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> กลับไปรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'details' => $details,
    ]) ?>

</div>