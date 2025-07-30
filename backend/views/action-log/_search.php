<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\ActionLogSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="action-log-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1,
            'class' => 'form-horizontal'
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'user_search')->textInput([
                'placeholder' => 'Enter username or user ID'
            ])->label('User (Username/ID)') ?>
        </div>

        <div class="col-md-4">
            <?= $form->field($model, 'action')->widget(Select2::classname(), [
                'data' => array_merge([''], \backend\models\ActionLogSearchModel::getPopularActions()),
                'options' => ['placeholder' => 'Select action...'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'tags' => true, // อนุญาตให้พิมพ์ action ใหม่ได้
                ],
            ]) ?>
        </div>

        <div class="col-md-4">
            <?= $form->field($model, 'status')->widget(Select2::classname(), [
                'data' => array_merge([''], \backend\models\ActionLogModel::getStatusOptions()),
                'options' => ['placeholder' => 'Select status...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'controller')->textInput([
                'placeholder' => 'Controller name'
            ]) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'action_method')->textInput([
                'placeholder' => 'Action method'
            ]) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'model_class')->textInput([
                'placeholder' => 'Model class name'
            ]) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'ip_address')->textInput([
                'placeholder' => 'IP Address'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'date_from')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'From date'],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                    'todayBtn' => true,
                ]
            ])->label('Date From') ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'date_to')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'To date'],
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'todayHighlight' => true,
                    'todayBtn' => true,
                ]
            ])->label('Date To') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'message')->textInput([
                'placeholder' => 'Search in message content'
            ]) ?>
        </div>
    </div>

    <!-- Quick Date Filters -->
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">Quick Filters:</label><br>
                <?= Html::button('Today', [
                    'class' => 'btn btn-default btn-sm',
                    'onclick' => 'setDateRange("today")'
                ]) ?>
                <?= Html::button('Yesterday', [
                    'class' => 'btn btn-default btn-sm',
                    'onclick' => 'setDateRange("yesterday")'
                ]) ?>
                <?= Html::button('Last 7 days', [
                    'class' => 'btn btn-default btn-sm',
                    'onclick' => 'setDateRange("week")'
                ]) ?>
                <?= Html::button('Last 30 days', [
                    'class' => 'btn btn-default btn-sm',
                    'onclick' => 'setDateRange("month")'
                ]) ?>
                <?= Html::button('Clear', [
                    'class' => 'btn btn-default btn-sm',
                    'onclick' => 'clearDateRange()'
                ]) ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Reset', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
    function setDateRange(period) {
        var today = new Date();
        var fromDate = new Date();
        var toDate = new Date();

        switch(period) {
            case 'today':
                fromDate = today;
                toDate = today;
                break;
            case 'yesterday':
                fromDate.setDate(today.getDate() - 1);
                toDate.setDate(today.getDate() - 1);
                break;
            case 'week':
                fromDate.setDate(today.getDate() - 7);
                toDate = today;
                break;
            case 'month':
                fromDate.setDate(today.getDate() - 30);
                toDate = today;
                break;
        }

        var formatDate = function(date) {
            var year = date.getFullYear();
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            var day = ('0' + date.getDate()).slice(-2);
            return year + '-' + month + '-' + day;
        };

        $('#actionlogsearch-date_from').val(formatDate(fromDate));
        $('#actionlogsearch-date_to').val(formatDate(toDate));
    }

    function clearDateRange() {
        $('#actionlogsearch-date_from').val('');
        $('#actionlogsearch-date_to').val('');
    }
</script>