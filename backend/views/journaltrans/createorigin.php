<?php

use yii\helpers\Html;
use backend\models\JournalTrans;

/* @var $this yii\web\View */
/* @var $model common\models\JournalTransX */
/* @var $lines common\models\JournalTransLineX[] */

$title = 'สร้างรายการ';
if ($model->trans_type_id) {
    $list = JournalTrans::getTransTypeOptions();
    if (isset($list[$model->trans_type_id])) {
        $title = 'สร้างรายการ' . $list[$model->trans_type_id];
    }
}

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'รายการ Stock Transaction', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="journal-trans-create">
    <?= $this->render('_form_origin', [
        'model' => $model,
        'modelsLine' => null,
        'lines' => $lines,
    ]) ?>

</div>