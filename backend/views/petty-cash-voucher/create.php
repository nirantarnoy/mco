<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\PettyCashVoucher */
/* @var $details backend\models\PettyCashDetail[] */

$this->title = 'สร้างใบสำคัญจ่ายเงินสดย่อย';
$this->params['breadcrumbs'][] = ['label' => 'ใบสำคัญจ่ายเงินสดย่อย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="petty-cash-voucher-create">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <?= Html::a('<i class="fas fa-arrow-left"></i> กลับไปรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'details' => $details,
    ]) ?>

</div>