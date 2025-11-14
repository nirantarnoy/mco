<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $currentBalance float */
/* @var $needsRefill bool */

$this->title = 'การจัดการเงินทดแทนสดย่อย';
$this->params['breadcrumbs'][] = $this->title;

$maxAmount = \backend\models\PettyCashAdvance::MAX_AMOUNT;
$minAmount = \backend\models\PettyCashAdvance::MIN_AMOUNT;
?>

    <div class="petty-cash-advance-index">

        <!-- Dashboard สถานะวงเงิน -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card <?= $needsRefill ? 'border-warning' : 'border-success' ?>">
                    <div class="card-header <?= $needsRefill ? 'bg-warning text-dark' : 'bg-success text-white' ?>">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie"></i> ภาพรวมวงเงินสดย่อย
                            <?php if ($needsRefill): ?>
                                <span class="badge badge-danger ml-2">ต้องเบิกเติม!</span>
                            <?php else: ?>
                                <span class="badge badge-light ml-2">ปกติ</span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="text-center">
                                    <i class="fas fa-wallet fa-2x text-primary mb-2"></i>
                                    <h6>วงเงินสูงสุด</h6>
                                    <h4 class="text-primary"><?= number_format($maxAmount, 2) ?></h4>
                                    <small class="text-muted">บาท</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <i class="fas fa-coins fa-2x <?= $needsRefill ? 'text-warning' : 'text-success' ?> mb-2"></i>
                                    <h6>ยอดปัจจุบัน</h6>
                                    <h4 class="<?= $needsRefill ? 'text-warning' : 'text-success' ?>">
                                        <?= number_format($currentBalance, 2) ?>
                                    </h4>
                                    <small class="text-muted">บาท</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                    <h6>วงเงินขั้นต่ำ</h6>
                                    <h4 class="text-danger"><?= number_format($minAmount, 2) ?></h4>
                                    <small class="text-muted">บาท</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                                    <h6>เปอร์เซ็นต์การใช้งาน</h6>
                                    <?php
                                    $percentage = ($currentBalance / $maxAmount) * 100;
                                    $progressClass = $percentage <= 10 ? 'danger' : ($percentage <= 30 ? 'warning' : 'success');
                                    ?>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-<?= $progressClass ?>"
                                             style="width: <?= $percentage ?>%"></div>
                                    </div>
                                    <h4 class="text-<?= $progressClass ?>"><?= number_format($percentage, 1) ?>%</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <i class="fas fa-hand-holding-usd fa-2x text-success mb-2"></i>
                                    <h6>เบิกได้สูงสุด</h6>
                                    <h4 class="text-success"><?= number_format($maxAmount - $currentBalance, 2) ?></h4>
                                    <small class="text-muted">บาท</small>
                                </div>
                            </div>
                        </div>

                        <?php if ($needsRefill): ?>
                            <div class="alert alert-warning mt-3 mb-0">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>แจ้งเตือน:</strong> ยอดเงินสดย่อยเหลือน้อยกว่าวงเงินขั้นต่ำ
                                        (<?= number_format($currentBalance, 2) ?> บาท)
                                        ควรเบิกเงินทดแทนเพื่อเติมวงเงิน
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <?= Html::a('<i class="fas fa-plus-circle"></i> เบิกเงินทดแทน',
                                            ['create'],
                                            ['class' => 'btn btn-warning btn-lg pulse-animation']
                                        ) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ปุ่มสำหรับจัดการ -->
        <div class="row mb-3">
            <div class="col-md-6">
                <?= Html::a('<i class="fas fa-plus"></i> เบิกเงินทดแทน', ['create'], [
                    'class' => 'btn btn-success'
                ]) ?>
                <?= Html::a('<i class="fas fa-chart-bar"></i> รายงาน', ['print-petty'], [
                    'class' => 'btn btn-info'
                ]) ?>
            </div>
            <div class="col-md-6 text-right">
                <?= Html::a('<i class="fas fa-sync-alt"></i> รีเฟรช', ['index'], [
                    'class' => 'btn btn-outline-primary'
                ]) ?>
            </div>
        </div>

        <?php Pjax::begin(['id' => 'advance-pjax']); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-striped table-bordered'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                [
                    'attribute' => 'advance_no',
                    'label' => 'เลขที่ใบเบิก',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a($model->advance_no, ['view', 'id' => $model->id], [
                            'class' => 'btn-link'
                        ]);
                    }
                ],

                [
                    'attribute' => 'request_date',
                    'label' => 'วันที่เบิก',
                    'value'=>function($data){
                      return date('m-d-Y H:i:s',strtotime($data->request_date));
                    },
                ],

                [
                    'attribute' => 'employee_id',
                    'label' => 'พนักงาน',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if ($model->employee) {
                            return $model->employee->fname . ' ' . $model->employee->lname;
                        }
                        return '-';
                    },
                    'headerOptions' => ['style' => 'width: 150px;']
                ],

                [
                    'attribute' => 'amount',
                    'label' => 'จำนวนเงิน',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<div class="text-right">' . number_format($model->amount, 2) . '</div>';
                    },
                    'headerOptions' => ['style' => 'width: 120px; text-align: center;']
                ],

                [
                    'attribute' => 'purpose',
                    'label' => 'วัตถุประสงค์',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $purpose = $model->purpose;
                        if (strlen($purpose) > 50) {
                            return substr($purpose, 0, 50) . '...';
                        }
                        return $purpose;
                    },
                    'headerOptions' => ['style' => 'width: 200px;']
                ],

//                [
//                    'attribute' => 'status',
//                    'label' => 'สถานะ',
//                    'format' => 'raw',
//                    'value' => function ($model) {
//                        $statusLabels = [
//                            'pending' => ['label' => 'รอดำเนินการ', 'class' => 'warning'],
//                            'approved' => ['label' => 'อนุมัติแล้ว', 'class' => 'success'],
//                            'rejected' => ['label' => 'ปฏิเสธ', 'class' => 'danger'],
//                            'paid' => ['label' => 'จ่ายแล้ว', 'class' => 'info'],
//                        ];
//                        $status = $statusLabels[$model->status] ?? ['label' => $model->status, 'class' => 'secondary'];
//                        return '<span class="badge badge-' . $status['class'] . '">' . $status['label'] . '</span>';
//                    },
//                    'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
//                    'contentOptions' => ['class' => 'text-center']
//                ],

//                [
//                    'attribute' => 'created_at',
//                    'label' => 'วันที่สร้าง',
//                    'format' => ['datetime', 'php:d/m/Y H:i'],
//                    'headerOptions' => ['style' => 'width: 120px;']
//                ],

//                [
//                        'attribute'=>'request_date',
//                        'value'=>function($data){
//                            return date('m-d-Y',strtotime($data->request_date));
//                        }
//                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'จัดการ',
                    'template' => '{view} {update} {approve} {reject} {print} {delete}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-eye"></i>', $url, [
                                'title' => 'ดูรายละเอียด',
                                'class' => 'btn btn-sm btn-outline-info'
                            ]);
                        },
                        'update' => function ($url, $model, $key) {
                            if ($model->status === 'pending') {
                                return Html::a('<i class="fas fa-edit"></i>', $url, [
                                    'title' => 'แก้ไข',
                                    'class' => 'btn btn-sm btn-outline-primary'
                                ]);
                            }
                            return '';
                        },
                        'approve' => function ($url, $model, $key) {
                            if ($model->status === 'pending' && \Yii::$app->user->can('approve-advance')) {
                                return Html::a('<i class="fas fa-check"></i>', ['approve', 'id' => $model->id], [
                                    'title' => 'อนุมัติ',
                                    'class' => 'btn btn-sm btn-outline-success',
                                    'data' => [
                                        'confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบเบิกนี้?',
                                        'method' => 'post',
                                    ],
                                ]);
                            }
                            return '';
                        },
                        'reject' => function ($url, $model, $key) {
                            if ($model->status === 'pending' && \Yii::$app->user->can('approve-advance')) {
                                return Html::a('<i class="fas fa-times"></i>', ['reject', 'id' => $model->id], [
                                    'title' => 'ปฏิเสธ',
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'data' => [
                                        'confirm' => 'คุณแน่ใจหรือไม่ที่จะปฏิเสธใบเบิกนี้?',
                                        'method' => 'post',
                                    ],
                                ]);
                            }
                            return '';
                        },
                        'print' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-print"></i>', ['print', 'id' => $model->id], [
                                'title' => 'พิมพ์',
                                'class' => 'btn btn-sm btn-outline-secondary',
                                'target' => '_blank'
                            ]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return $model->status != 'approved' ? Html::a('<i class="fas fa-trash"></i>', $url, [
                                'title' => 'ลบ',
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                                'data-method' => 'post',
                                'data-pjax' => '0'
                            ]):'';
                        },
                    ],
                    'headerOptions' => ['style' => 'width: 150px; text-align: center;'],
                    'contentOptions' => ['class' => 'text-center']
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>

    <style>
        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .progress {
            height: 10px;
        }

        .badge {
            font-size: 0.8em;
        }
    </style>

<?php
$this->registerJs("
// Auto refresh every 30 seconds for real-time balance updates
setInterval(function() {
    $.pjax.reload({
        container: '#advance-pjax',
        timeout: 5000
    });
}, 30000);

// Tooltip initialization
$(function () {
    $('[data-toggle=\"tooltip\"]').tooltip();
});
");
?>