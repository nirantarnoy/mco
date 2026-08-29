<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Vendor $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'ผู้ขาย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="vendor-view">

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
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
            'code',
            'name',
            'description',
            'taxid',
            'bank_name',
            'account_name',
            'account_num',
            [
                'attribute' => 'bank_account_file',
                'format' => 'raw',
                'value' => function ($model) {
                    if (!empty($model->bank_account_file)) {
                        return Html::a('<i class="fas fa-file-invoice"></i> ดูหน้าบัญชีธนาคาร', Yii::$app->request->baseUrl . '/uploads/vendor_doc/' . $model->bank_account_file, [
                            'target' => '_blank',
                            'class' => 'btn btn-sm btn-info text-white'
                        ]);
                    }
                    return '<span class="text-muted">ไม่มีไฟล์แนบ</span>';
                }
            ],
            'status',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
