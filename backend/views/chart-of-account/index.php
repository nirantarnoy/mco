<?php

use backend\models\ChartOfAccount;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var backend\models\ChartOfAccountSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Chart Of Accounts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="chart-of-account-index">

    <p>
        <?= Html::a('Create Chart Of Account', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn', 'headerOptions' => ['style' => 'width: 50px;']],
            [
                'attribute' => 'account_code',
                'headerOptions' => ['style' => 'width: 120px;'],
            ],
            [
                'attribute' => 'account_name',
                'format' => 'raw',
                'value' => function($model) {
                    $indent = ($model->account_level - 1) * 20;
                    return '<div style="padding-left: ' . $indent . 'px;">' . ($model->account_level > 1 ? '↳ ' : '') . Html::encode($model->account_name) . '</div>';
                }
            ],
            [
                'attribute' => 'account_group',
                'headerOptions' => ['style' => 'width: 80px;'],
            ],
            [
                'attribute' => 'account_level',
                'headerOptions' => ['style' => 'width: 80px; text-align: center;'],
                'contentOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'account_type',
                'headerOptions' => ['style' => 'width: 100px;'],
                'value' => function($model) {
                    return $model->getAccountTypeName();
                }
            ],
            [
                'attribute' => 'parent_account_id',
                'headerOptions' => ['style' => 'width: 120px;'],
                'value' => function($model) {
                    return $model->parentAccount ? $model->parentAccount->account_code : '';
                }
            ],
            [
                'class' => ActionColumn::className(),
                'headerOptions' => ['style' => 'width: 100px;'],
                'urlCreator' => function ($action, ChartOfAccount $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
