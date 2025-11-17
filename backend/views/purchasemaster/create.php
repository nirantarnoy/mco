<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\PurchaseMaster $model */

$this->title = 'สร้างใบซื้อใหม่';
$this->params['breadcrumbs'][] = ['label' => 'บันทึกการซื้อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-master-create">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
                'model_deposit_all'=> null,
                'model_deposit_line_all'=> null,
            ]) ?>
        </div>
    </div>

</div>