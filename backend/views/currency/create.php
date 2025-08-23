<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Currency $model */

$this->title = 'สร้างข้อมูลสกุลเงิน';
$this->params['breadcrumbs'][] = ['label' => 'สกุลเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="currency-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
