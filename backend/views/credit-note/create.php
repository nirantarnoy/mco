<?php
// backend/views/credit-note/create.php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\CreditNote */
/* @var $modelsItem backend\models\CreditNoteItem[] */

$this->title = 'สร้างใบลดหนี้';
$this->params['breadcrumbs'][] = ['label' => 'ใบลดหนี้', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="credit-note-create">
    <?= $this->render('_form', [
        'model' => $model,
        'modelsItem' => $modelsItem,
    ]) ?>

</div>
