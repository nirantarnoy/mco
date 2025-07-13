<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\JournalTrans */
/* @var $modelsLine common\models\JournalTransLine[] */

$this->title = 'แก้ไขรายการ: ' . $model->journal_no;
$this->params['breadcrumbs'][] = ['label' => 'รายการ Stock Transaction', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->journal_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="journal-trans-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelsLine' => $modelsLine,
    ]) ?>

</div>