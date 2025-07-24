<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Worker $model */

$this->title = 'สร้างข้อมูลลูกจ้าง';
$this->params['breadcrumbs'][] = ['label' => 'ลูกจ้าง', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="worker-create">

    <?= $this->render('_form', [
        'model' => $model,
        'model_doc' => null,
    ]) ?>

</div>
