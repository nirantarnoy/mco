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

            <?php
            $ref_po = \backend\models\Purch::findNo($product['purch_id']);
            $description = \backend\models\Product::findDescription($product['product_id']);
            $model = \backend\models\Product::findModelName($product['product_id']);
            $brand = \backend\models\Product::findBrand($product['product_id']);
            $quantity = $product['qty'];
            ?>
            <tr>
                <td><?= Html::encode($ref_po) ?></td>
                <td><?= Html::encode($description) ?></td>
                <td><?= Html::encode($model) ?></td>
                <td><?= Html::encode($brand) ?></td>
                <td><?= Html::encode($quantity) ?></td>
                <td>
                    <input type="hidden" name="line_ref_po[]" value="<?= $ref_po ?>">
                    <input type="hidden" name="line_description[]" value="<?= $description ?>">
                    <input type="hidden" name="line_model[]" value="<?= $model ?>">
                    <input type="hidden" name="line_brand[]" value="<?= $brand ?>">
                    <input type="hidden" name="line_quantity[]" value="<?= $quantity ?>">
                    <?= Html::textInput("line_copies[]", 5, [
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

<!--        --><?php //= Html::submitButton('Export PDF', [
//            'class' => 'btn btn-danger',
//            'name' => 'action',
//            'value' => 'pdf',
//            'onclick' => "this.form.action = '" . \yii\helpers\Url::to(['generate-tags', 'format' => 'pdf']) . "';"
//        ]) ?>
<!---->
<!--        --><?php //= Html::submitButton('Export Excel', [
//            'class' => 'btn btn-info',
//            'name' => 'action',
//            'value' => 'excel',
//            'onclick' => "this.form.action = '" . \yii\helpers\Url::to(['generate-tags', 'format' => 'excel']) . "';"
//        ]) ?>

        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>