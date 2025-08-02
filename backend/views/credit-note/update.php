<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\CreditNote */
/* @var $modelsItem backend\models\CreditNoteItem[] */

$this->title = 'แก้ไขใบลดหนี้: ' . $model->document_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบลดหนี้', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->document_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="credit-note-update">

    <?= $this->render('_form', [
        'model' => $model,
        'modelsItem' => $modelsItem,
    ]) ?>

</div>