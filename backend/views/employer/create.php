<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Employer $model */

$this->title = 'สร้างข้อมูลนายจ้าง';
$this->params['breadcrumbs'][] = ['label' => 'นายจ้าง', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="employer-create">
    <?= $this->render('_form', [
        'model' => $model,
        'model_doc' => null,
    ]) ?>

</div>
