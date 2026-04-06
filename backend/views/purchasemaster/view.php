<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\PurchaseMaster $model */

$this->title = $model->docnum;
$this->params['breadcrumbs'][] = ['label' => 'บันทึกการซื้อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="purchase-master-view">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ใบซื้อเลขที่: <?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?php if ($model->status != \backend\models\PurchaseMaster::STATUS_CANCELLED): ?>
                    <?php if ($model->approve_status == \backend\models\PurchaseMaster::APPROVE_STATUS_PENDING): ?>
                        <?= Html::a('<i class="fas fa-check"></i> อนุมัติ', ['approve', 'id' => $model->id], [
                            'class' => 'btn btn-success btn-sm',
                            'data' => [
                                'confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบซื้อนี้?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    <?php endif; ?>
                    <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm']) ?>
                <?php endif; ?>
                <?= Html::a('<i class="fas fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('<i class="fas fa-list"></i> กลับไปรายการ', ['index'], ['class' => 'btn btn-secondary btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">ข้อมูลหลัก</h5>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'docnum',
                            [
                                'attribute' => 'docdat',
                                'value' => function ($model) {
                                    return date('m/d/Y', strtotime($model->docdat));
                                }
                            ],
                            [
                                'attribute' => 'supcod',
                                'value' => function ($model) {
                                    return \backend\models\Vendor::findCode($model->supcod);
                                }
                            ],
                            [
                                'attribute' => 'supnam',
                                'value' => function ($model) {
                                    return \backend\models\Vendor::findName($model->supcod);
                                }
                            ],
                            [
                                'attribute' => 'job_no',
                                'value' => function ($model) {
                                    return \backend\models\Job::findJobNo($model->job_no);
                                }
                            ],
                            [
                                'attribute' => 'paytrm',
                                'value' => function ($model) {
                                    return \backend\models\Paymentterm::findName($model->paytrm);
                                }
                            ],
                            'duedat:date:วันครบกำหนด',
                            'taxid',
                            'discod',
                        ],
                    ]) ?>
                </div>

                <div class="col-md-6">
                    <h5 class="mb-3">ที่อยู่และข้อมูลอื่นๆ</h5>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'addr01',
                            'addr02',
                            'addr03',
                            'zipcod',
                            'telnum',
                            'orgnum',
                            'refnum',
                            'vatdat:date:วันที่ภาษี',
                        ],
                    ]) ?>
                </div>
            </div>

            <?php
            $model_deposit = \backend\models\PurchNonePrDeposit::find()->where(['purchase_master_id' => $model->id])->one();
            if ($model_deposit):
                $model_deposit_line = \backend\models\PurchNonePrDepositLine::find()->where(['purch_none_pr_deposit_id' => $model_deposit->id])->one();
            ?>
                <h5 class="mt-4 mb-3">ข้อมูลมัดจำ</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th width="25%">วันที่เอกสารมัดจำ</th>
                                <th width="25%">วันที่จ่ายมัดจำ</th>
                                <th class="text-right" width="25%">จำนวนเงินมัดจำ</th>
                                <th class="text-center" width="25%">เอกสารแนบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($model_deposit->trans_date)) ?></td>
                                <td><?= ($model_deposit_line && $model_deposit_line->deposit_date) ? date('d/m/Y', strtotime($model_deposit_line->deposit_date)) : '-' ?></td>
                                <td class="text-right"><?= $model_deposit_line ? Yii::$app->formatter->asDecimal($model_deposit_line->deposit_amount, 2) : '0.00' ?> บาท</td>
                                <td class="text-center">
                                    <?php if ($model_deposit_line && $model_deposit_line->deposit_doc): ?>
                                        <?= Html::a('<i class="fas fa-file-download"></i> ดูเอกสาร', Yii::$app->request->baseUrl . '/uploads/purch_doc/' . $model_deposit_line->deposit_doc, ['target' => '_blank', 'class' => 'btn btn-outline-info btn-sm', 'data-pjax' => '0']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">ไม่มีเอกสารแนบ</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ข้อมูลการรับมัดจำคืน -->
                <h6 class="mt-3 mb-2 font-weight-bold">ข้อมูลการรับมัดจำคืน</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th width="33%">วันที่รับมัดจำคืน</th>
                                <th class="text-right" width="33%">จำนวนเงินมัดจำที่รับคืน</th>
                                <th class="text-center" width="34%">เอกสารแนบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= ($model_deposit_line && $model_deposit_line->receive_date) ? date('d/m/Y', strtotime($model_deposit_line->receive_date)) : '-' ?></td>
                                <td class="text-right"><?= ($model_deposit_line && $model_deposit_line->receive_doc != null && $model_deposit_line->deposit_amount) ? Yii::$app->formatter->asDecimal($model_deposit_line->deposit_amount, 2) : '0.00' ?> บาท</td>
                                <td class="text-center">
                                    <?php if ($model_deposit_line && $model_deposit_line->receive_doc): ?>
                                        <?= Html::a('<i class="fas fa-file-download"></i> ดูเอกสาร', Yii::$app->request->baseUrl . '/uploads/purch_doc/' . $model_deposit_line->receive_doc, ['target' => '_blank', 'class' => 'btn btn-outline-info btn-sm', 'data-pjax' => '0']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">ไม่มีเอกสารแนบ</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <h5 class="mt-4 mb-3">รายละเอียดสินค้า</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                    <tr>
                        <th class="text-center" width="50">ลำดับ</th>
                        <th width="120">รหัสสินค้า</th>
                        <th>รายละเอียด</th>
                        <th class="text-center" width="100">จำนวน</th>
                        <th class="text-right" width="120">ราคา/หน่วย</th>
                        <th class="text-center" width="100">ส่วนลด</th>
                        <th class="text-right" width="120">จำนวนเงิน</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($model->purchaseDetails)): ?>
                        <?php foreach ($model->purchaseDetails as $index => $detail): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= Html::encode($detail->stkcod) ?></td>
                                <td><?= Html::encode($detail->stkdes) ?></td>
                                <td class="text-center"><?= Yii::$app->formatter->asDecimal($detail->uqnty, 2) ?></td>
                                <td class="text-right"><?= Yii::$app->formatter->asDecimal($detail->unitpr, 2) ?></td>
                                <td class="text-center"><?= Html::encode($detail->disc) ?></td>
                                <td class="text-right"><?= Yii::$app->formatter->asDecimal($detail->amount, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">ไม่มีรายละเอียดสินค้า</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <h5>หมายเหตุ</h5>
                    <p><?= nl2br(Html::encode($model->remark)) ?></p>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td class="text-right" width="60%"><strong>มูลค่าสินค้า:</strong></td>
                                    <td class="text-right"
                                        width="40%"><?= Yii::$app->formatter->asDecimal($model->vatpr0, 2) ?> บาท
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-right"><strong>VAT (<?= $model->vat_percent ?>%):</strong></td>
                                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($model->vat_amount, 2) ?>
                                        บาท
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-right"><strong>TAX (<?= $model->tax_percent ?>%):</strong></td>
                                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($model->tax_amount, 2) ?>
                                        บาท
                                    </td>
                                </tr>
                                <tr class="table-active">
                                    <td class="text-right"><h5><strong>รวมทั้งสิ้น:</strong></h5></td>
                                    <td class="text-right"><h5>
                                            <strong><?= Yii::$app->formatter->asDecimal($model->total_amount, 2) ?>
                                                บาท</strong></h5></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function($model) {
                                return $model->getStatusLabel();
                            },
                        ],
                        [
                            'attribute' => 'approve_status',
                            'format' => 'raw',
                            'value' => function($model) {
                                return $model->getApproveStatusBadge();
                            },
                        ],
                        'created_at:datetime:สร้างเมื่อ',
                        'updated_at:datetime:แก้ไขเมื่อ',
                    ],
                ]) ?>
            </div>

        </div>
    </div>

</div>