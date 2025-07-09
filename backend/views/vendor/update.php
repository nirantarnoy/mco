<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Vendor $model */

$this->title = 'แก้ไขผู้ขาย: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'ผู้ขาย', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="vendor-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
