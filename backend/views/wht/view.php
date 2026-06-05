<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->wht_no;
$this->params['breadcrumbs'][] = ['label' => 'WHT', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="wht-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Print', ['print', 'id' => $model->id], ['class' => 'btn btn-info', 'target' => '_blank']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'wht_no',
            'trans_date',
            [
                'attribute' => 'wht_type',
                'value' => $model->wht_type == 3 ? 'ภ.ง.ด. 3' : 'ภ.ง.ด. 53',
            ],
            [
                'attribute' => 'vendor_id',
                'value' => $model->vendor ? $model->vendor->name : '-',
            ],
            'wht_desc',
            'other_desc',
            'base_amount:decimal',
            'wht_percent',
            'wht_amount:decimal',
            [
                'attribute' => 'pay_condition',
                'value' => function($model) {
                    $c = [1 => 'หัก ณ ที่จ่าย', 2 => 'ออกภาษีให้ตลอดไป', 3 => 'ออกภาษีให้ครั้งเดียว'];
                    return $c[$model->pay_condition] ?? '-';
                }
            ]
        ],
    ]) ?>

</div>
