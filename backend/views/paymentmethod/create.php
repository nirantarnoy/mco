<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Paymentmethod $model */

$this->title = 'สร้างวิธีชำระเงิน';
$this->params['breadcrumbs'][] = ['label' => 'วิธีชำระเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="paymentmethod-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
