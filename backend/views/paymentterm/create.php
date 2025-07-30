<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Paymentterm $model */

$this->title = 'สร้างเงื่อนไขชำระเงิน';
$this->params['breadcrumbs'][] = ['label' => 'เงื่อนไขชำระเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="paymentterm-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
