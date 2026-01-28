<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\UnitSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="unit-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <div class="input-group">
        <?= Html::activeTextInput($model, 'globalSearch', ['class' => 'form-control', 'placeholder' => 'ค้นหา...']) ?>
        <div class="input-group-append">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> ค้นหา</button>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
