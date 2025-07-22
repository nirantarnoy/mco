<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $selectedProducts array */

$this->title = 'เลือกจำนวนพิมพ์ Tag สินค้า';
$this->params['breadcrumbs'][] = ['label' => 'รายการสินค้า', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="print-tag-selection">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'action' => ['generate-tags'],
        'method' => 'post',
        'options' => ['target' => '_blank'],
    ]); ?>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Ref.Po</th>
            <th>Description</th>
            <th>Model</th>
            <th>Brand</th>
            <th>Quantity</th>
            <th style="width: 150px;">จำนวนที่ต้องการพิมพ์</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($selectedProducts as $index => $product): ?>
            <tr>
                <td><?= '' ?></td>
                <td><?= Html::encode($product['code']) ?></td>
                <td><?= Html::encode($product['description']) ?></td>
                <td><?= Html::encode($product['brand_name']) ?></td>
                <td><?= Html::encode($product['stock_qty']) ?></td>
                <td>
                    <?= Html::hiddenInput("products[$index][code]", json_encode($product)) ?>
                    <?= Html::textInput("products[$index][copies]", 5, [
                        'class' => 'form-control',
                        'type' => 'number',
                        'min' => 1,
                        'max' => 100
                    ]) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-group">
        <?= Html::submitButton('Preview', [
            'class' => 'btn btn-primary',
            'name' => 'action',
            'value' => 'preview',
            'onclick' => "this.form.action = '" . \yii\helpers\Url::to(['generate-tags']) . "';"
        ]) ?>

        <?= Html::submitButton('Print', [
            'class' => 'btn btn-success',
            'name' => 'action',
            'value' => 'print',
            'onclick' => "this.form.action = '" . \yii\helpers\Url::to(['generate-tags']) . "'; window.print();"
        ]) ?>

        <?= Html::submitButton('Export PDF', [
            'class' => 'btn btn-danger',
            'name' => 'action',
            'value' => 'pdf',
            'onclick' => "this.form.action = '" . \yii\helpers\Url::to(['generate-tags', 'format' => 'pdf']) . "';"
        ]) ?>

        <?= Html::submitButton('Export Excel', [
            'class' => 'btn btn-info',
            'name' => 'action',
            'value' => 'excel',
            'onclick' => "this.form.action = '" . \yii\helpers\Url::to(['generate-tags', 'format' => 'excel']) . "';"
        ]) ?>

        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>