<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\BankAccount $model */

$this->title = $model->bank_name;
$this->params['breadcrumbs'][] = ['label' => 'บัญชีธนาคาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="bank-account-view">

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'คุณต้องการลบรายการนี้ใช่หรือไม่?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('กลับหน้ารายการ', ['index'], ['class' => 'btn btn-default']) ?>
    </p>

    <div class="card card-default">
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'bank_name',
                    'account_no',
                    'account_name',
                    'branch',
                    [
                        'attribute' => 'status',
                        'value' => function ($data) {
                            return $data->status == 1 ? 'ใช้งาน' : 'ไม่ใช้งาน';
                        }
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => function ($data) {
                            return date('d/m/Y H:i', $data->created_at);
                        }
                    ],
                    [
                        'attribute' => 'updated_at',
                        'value' => function ($data) {
                            return date('d/m/Y H:i', $data->updated_at);
                        }
                    ],
                ],
            ]) ?>
        </div>
    </div>

</div>
