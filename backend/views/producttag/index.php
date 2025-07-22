<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'รายการสินค้า';
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="product-index">

        <h1><?= Html::encode($this->title) ?></h1>

        <p>
            <?= Html::a('เพิ่มสินค้า', ['create'], ['class' => 'btn btn-success']) ?>
        </p>

        <?php $form = ActiveForm::begin([
            'action' => ['print-tag'],
            'method' => 'post',
        ]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'id' => 'product-grid',
            'columns' => [
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return ['value' => $model['id']];
                    }
                ],
                ['class' => 'yii\grid\SerialColumn'],
                'ref_po',
                'description',
                'model',
                'brand',
                'quantity',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {delete}',
                ],
            ],
        ]); ?>

        <div class="form-group">
            <?= Html::submitButton('Add To Print Tag', [
                'class' => 'btn btn-primary',
                'id' => 'print-tag-btn',
                'style' => 'display: none;',
                'onclick' => 'return validateSelection();'
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

<?php
$js = <<<JS
// Show/hide print button based on checkbox selection
$('#product-grid').on('change', 'input[type="checkbox"]', function() {
    var checkedCount = $('#product-grid').find('input[type="checkbox"]:checked').length;
    if (checkedCount > 0) {
        $('#print-tag-btn').show();
    } else {
        $('#print-tag-btn').hide();
    }
});

// Validate selection before submitting
function validateSelection() {
    var checkedCount = $('#product-grid').find('input[name="selection[]"]:checked').length;
    if (checkedCount === 0) {
        alert('กรุณาเลือกสินค้าอย่างน้อย 1 รายการ');
        return false;
    }
    return true;
}
JS;

$this->registerJs($js);
?>