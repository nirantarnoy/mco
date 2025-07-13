<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use backend\models\JournalTrans;
use backend\models\Product;
use kartik\date\DatePicker;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\JournalTrans */
/* @var $lines common\models\JournalTransLine[] */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile('@web/js/journal-trans.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="journal-trans-form">

    <?php $form = ActiveForm::begin([
        'id' => 'journal-trans-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-sm-9\">{input}\n{error}</div>",
            'labelOptions' => ['class' => 'col-sm-3 control-label'],
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'trans_date')->widget(DatePicker::class, [
                'options' => ['placeholder' => 'Select transaction date'],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                ]
            ]) ?>

            <?= $form->field($model, 'trans_type_id')->dropDownList(
                JournalTrans::getTransTypeOptions(),
                [
                    'prompt' => 'Select Transaction Type',
                    'id' => 'trans-type-select',
                    'onchange' => 'updateStockType(this.value)'
                ]
            ) ?>

            <?= $form->field($model, 'stock_type_id')->dropDownList(
                JournalTrans::getStockTypeOptions(),
                ['prompt' => 'Select Stock Type', 'id' => 'stock-type-select']
            ) ?>

            <?= $form->field($model, 'warehouse_id')->dropDownList(
                ArrayHelper::map(\common\models\Warehouse::find()->all(), 'id', 'name'),
                ['prompt' => 'Select Warehouse', 'id' => 'warehouse-select']
            ) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'customer_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'remark')->textarea(['rows' => 3]) ?>

            <?php if (!$model->isNewRecord): ?>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Journal No</label>
                    <div class="col-sm-9">
                        <p class="form-control-static"><?= Html::encode($model->journal_no) ?></p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Status</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            <span class="label label-<?= $model->status === 'approved' ? 'success' : 'default' ?>">
                                <?= Html::encode(ucfirst($model->status)) ?>
                            </span>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <hr>

    <h4>Transaction Lines</h4>

    <div id="transaction-lines">
        <?php foreach ($lines as $index => $line): ?>
            <div class="line-item" data-index="<?= $index ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">
                            Line <?= $index + 1 ?>
                            <?php if ($index > 0): ?>
                                <button type="button" class="btn btn-xs btn-danger pull-right remove-line">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <?= Html::activeLabel($line, "[$index]product_id", ['class' => 'control-label']) ?>
                                <?= Select2::widget([
                                    'model' => $line,
                                    'attribute' => "[$index]product_id",
                                    'data' => ArrayHelper::map(Product::find()->all(), 'id', function($model) {
                                        return $model->code . ' - ' . $model->name;
                                    }),
                                    'options' => [
                                        'placeholder' => 'Select Product',
                                        'class' => 'product-select',
                                        'data-index' => $index,
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ]) ?>
                            </div>

                            <div class="col-md-2">
                                <?= Html::activeLabel($line, "[$index]qty", ['class' => 'control-label']) ?>
                                <?= Html::activeTextInput($line, "[$index]qty", [
                                    'class' => 'form-control qty-input',
                                    'data-index' => $index,
                                    'type' => 'number',
                                    'step' => '0.01',
                                ]) ?>
                            </div>

                            <div class="col-md-2">
                                <?= Html::activeLabel($line, "[$index]sale_price", ['class' => 'control-label']) ?>
                                <?= Html::activeTextInput($line, "[$index]sale_price", [
                                    'class' => 'form-control sale-price',
                                    'readonly' => true,
                                ]) ?>
                            </div>

                            <div class="col-md-2">
                                <?= Html::activeLabel($line, "[$index]line_price", ['class' => 'control-label']) ?>
                                <?= Html::activeTextInput($line, "[$index]line_price", [
                                    'class' => 'form-control line-price',
                                    'readonly' => true,
                                ]) ?>
                            </div>

                            <div class="col-md-2">
                                <label class="control-label">Available Stock</label>
                                <div class="form-control-static available-stock" data-index="<?= $index ?>">-</div>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="col-md-6">
                                <?= Html::activeLabel($line, "[$index]remark", ['class' => 'control-label']) ?>
                                <?= Html::activeTextarea($line, "[$index]remark", [
                                    'class' => 'form-control',
                                    'rows' => 2,
                                ]) ?>
                            </div>

                            <!-- Return borrow fields -->
                            <div class="col-md-6 return-borrow-fields" style="display: none;">
                                <?= Html::activeLabel($line, "[$index]return_to_type", ['class' => 'control-label']) ?>
                                <?= Html::activeDropDownList($line, "[$index]return_to_type",
                                    \backend\models\JournalTransLine::getReturnTypeOptions(),
                                    [
                                        'class' => 'form-control return-type-select',
                                        'prompt' => 'Select Return Type',
                                        'data-index' => $index,
                                    ]
                                ) ?>

                                <div class="return-note-field" style="margin-top: 10px; display: none;">
                                    <?= Html::activeLabel($line, "[$index]return_note", ['class' => 'control-label']) ?>
                                    <?= Html::activeTextarea($line, "[$index]return_note", [
                                        'class' => 'form-control',
                                        'rows' => 2,
                                        'placeholder' => 'Describe the condition of returned items...',
                                    ]) ?>
                                </div>
                            </div>
                        </div>

                        <?= Html::activeHiddenInput($line, "[$index]id") ?>
                        <?= Html::activeHiddenInput($line, "[$index]warehouse_id", ['class' => 'line-warehouse-id']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button type="button" class="btn btn-success" id="add-line">
                <i class="fa fa-plus"></i> Add Line
            </button>
        </div>
    </div>

    <hr>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', [
                'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
            ]) ?>
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
    // Transaction type to stock type mapping
    const transTypeStockTypeMap = {
        '1': '1', // PO Receive -> Stock In
        '2': '2', // Cancel PO Receive -> Stock Out
        '3': '2', // Issue Stock -> Stock Out
        '4': '1', // Return Issue -> Stock In
        '5': '2', // Issue Borrow -> Stock Out
        '6': '1'  // Return Borrow -> Stock In
    };

    function updateStockType(transTypeId) {
        const stockTypeSelect = document.getElementById('stock-type-select');
        if (transTypeStockTypeMap[transTypeId]) {
            stockTypeSelect.value = transTypeStockTypeMap[transTypeId];
        }

        // Show/hide return borrow fields
        const isReturnBorrow = transTypeId == '6';
        $('.return-borrow-fields').toggle(isReturnBorrow);

        if (isReturnBorrow) {
            $('.return-borrow-fields').show();
        } else {
            $('.return-borrow-fields').hide();
            $('.return-type-select').val('');
            $('.return-note-field').hide();
        }
    }

    // Initialize on page load
    $(document).ready(function() {
        const transType = $('#trans-type-select').val();
        if (transType) {
            updateStockType(transType);
        }
    });
</script>