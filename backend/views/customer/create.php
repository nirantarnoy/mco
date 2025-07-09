<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Customer $model */

$this->title = 'สร้างข้อมูลลูกค้า';
$this->params['breadcrumbs'][] = ['label' => 'ลูกค้า', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
