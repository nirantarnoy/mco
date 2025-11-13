<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\PurchaseMaster $model */

$this->title = 'แก้ไขใบซื้อ: ' . $model->docnum;
$this->params['breadcrumbs'][] = ['label' => 'บันทึกการซื้อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->docnum, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="purchase-master-update">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>

</div>