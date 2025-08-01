<?php

use backend\models\DebitNoteItem;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Job $model */

$this->title = 'สร้าง Debit Note';
$this->params['breadcrumbs'][] = ['label' => 'Debit Note', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="job-create">
    <?= $this->render('_form', [
        'model' => $model,
        'model_line' => null,
        'modelsItem' => (empty($modelsItem)) ? [new DebitNoteItem] : $modelsItem
    ]) ?>

</div>
