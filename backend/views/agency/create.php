<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Agency $model */

$this->title = 'สร้างข้อมูลหน่วยงานราชการ';
$this->params['breadcrumbs'][] = ['label' => 'หน่วยงานราชการ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agency-create">
    <?= $this->render('_form', [
        'model' => $model,
        'model_doc' => null,
    ]) ?>

</div>
