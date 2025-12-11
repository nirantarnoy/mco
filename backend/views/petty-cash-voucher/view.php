<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\PettyCashVoucher */

$this->title = $model->pcv_no;
$this->params['breadcrumbs'][] = ['label' => 'ใบสำคัญจ่ายเงินสดย่อย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$model_doc_slip = \common\models\PettyCashVoucherDocSlip::find()->where(['petty_cash_voucher_id' => $model->id])->all();
$model_doc_bill = \common\models\PettyCashVoucherDocBill::find()->where(['petty_cash_voucher_id' => $model->id])->all();
?>
<div class="petty-cash-voucher-view">
    <!-- Flash Messages -->
    <?php if (\Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= \Yii::$app->session->getFlash('warning') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <?= \Yii::$app->session->getFlash('info') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'target' => '_blank'
            ]) ?>
            <?php if ($model->approve_status !=1): ?>
                <?php if (\Yii::$app->user->can('CanApprovePettyCash')): ?>
                    <?= Html::a('อนุมัติ', ['approve', 'id' => $model->id], [
                        'class' => 'btn btn-success',
                        'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะอนุมัติใบจ่ายเงินสดย่อย ?',
                        'data-method' => 'post',
                    ]) ?>

                <?php endif; ?>
            <?php endif; ?>
            <?= Html::a('<i class="fas fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                'data-method' => 'post',
            ]) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> กลับไปรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> ข้อมูลทั่วไป</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-bordered detail-view'],
                        'attributes' => [
                            'pcv_no',
                            [
                                'attribute' => 'date',
                                'value' => Yii::$app->formatter->asDate($model->date, 'dd/MM/yyyy'),
                            ],
                            'name',
                            [
                                'attribute' => 'amount',
                                'value' => number_format($model->amount, 2) . ' บาท',
                            ],
                            [
                                'attribute' => 'paid_for',
                                'value' => $model->paid_for ?: '-',
                                'format' => 'ntext',
                            ],
                            [
                                'label' => 'VAT',
                                'value' => number_format($model->getTotalVat(), 2) . ' บาท',
                            ],
                            [
                                'label' => 'VAT ต้องห้าม',
                                'value' => number_format($model->getTotalVatProhibit(), 2) . ' บาท',
                            ],
                            [
                                'label' => 'W/H',
                                'value' => number_format($model->getTotalWht(), 2) . ' บาท',
                            ],
                            [
                                'label' => 'อื่นๆ',
                                'value' => number_format($model->getTotalOther(), 2) . ' บาท',
                            ],
                            [
                                'label' => 'TOTAL',
                                'value' => number_format($model->calculateTotalAmount(), 2) . ' บาท',
                                'contentOptions' => ['class' => 'font-weight-bold'],
                                'captionOptions' => ['class' => 'font-weight-bold'],
                            ],
                            [
                                'attribute' => 'quotation_id',
                                'value' => function ($model) {
                                    return \backend\models\Quotation::findNo($model->quotation_id);
                                },
                            ],
                            [
                                'attribute' => 'job_id',
                                'value' => function ($model) {
                                    return \backend\models\Job::findJobNo($model->job_id);
                                },
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-users"></i> ผู้ดำเนินการ</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-bordered detail-view'],
                        'attributes' => [
                            [
                                'attribute' => 'issued_by',
                                'value' => $model->issued_by ?: '-',
                            ],
                            [
                                'attribute' => 'issued_date',
                                'value' => $model->issued_date ? Yii::$app->formatter->asDate($model->issued_date, 'dd/MM/yyyy') : '-',
                            ],
                            [
                                'attribute' => 'approved_status',
                                'value' => function ($data) {
                                    if($data->approve_status == 1){
                                        return 'อนุมัติ';
                                    }else {
                                        return 'ไม่อนุมัติ';
                                    }
                                }
                            ],
                            [
                                'attribute' => 'approved_by',
                                'value' => $model->approved_by ?: '-',
                            ],
                            [
                                'attribute' => 'approved_date',
                                'value' => $model->approved_date ? Yii::$app->formatter->asDate($model->approved_date, 'dd/MM/yyyy') : '-',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-list"></i> รายละเอียดการจ่าย</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($model->details)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">สำหรับเลขที่บิล</th>
                            <th width="10%">DATE</th>
                            <th width="30%">DETAIL</th>
                            <th width="10%">JOB</th>
                            <th width="12%">AMOUNT</th>
                            <th width="8%">VAT</th>
                            <th width="10%">VAT ต้องห้าม</th>
                            <th width="8%">W/H</th>
                            <th width="8%">อื่นๆ</th>
                            <th width="12%">TOTAL</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $totalAmount = 0; ?>
                        <?php foreach ($model->details as $index => $detail): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= Html::encode($detail->ac_code) ?></td>
                                <td class="text-center">
                                    <?= $detail->detail_date ? Yii::$app->formatter->asDate($detail->detail_date, 'dd/MM/yyyy') : '-' ?>
                                </td>
                                <td><?= Html::encode($detail->detail) ?></td>
                                <td><?= Html::encode(\backend\models\Job::findJobNo($detail->job_ref_id)) ?></td>
                                <td class="text-right"><?= number_format($detail->amount, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->vat, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->vat_prohibit, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->wht, 2) ?></td>
                                <td class="text-right"><?= number_format($detail->other, 2) ?></td>
                                <td class="text-right font-weight-bold"><?= number_format($detail->total, 2) ?></td>
                            </tr>
                            <?php $totalAmount += $detail->total; ?>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                        <tr>
                            <th colspan="10" class="text-right">รวมทั้งหมด:</th>
                            <th class="text-right"><?= number_format($totalAmount, 2) ?> บาท</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted p-4">
                    <i class="fas fa-inbox fa-2x"></i><br>
                    ไม่มีรายละเอียดการจ่าย
                </div>
            <?php endif; ?>
        </div>
    </div>
    <br/>
    <div class="label">
        <h4>เอกสารแนบสลิป</h4>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <table class="table table-bordered table-striped" style="width: 100%">
                <thead>
                <tr>
                    <th style="width: 5%;text-align: center">#</th>
                    <th style="width: 50%;text-align: center">ชื่อไฟล์</th>
                    <th style="width: 10%;text-align: center">ดูเอกสาร</th>
                    <th style="width: 5%;text-align: center">-</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($model_doc_slip != null): ?>

                    <?php foreach ($model_doc_slip as $key => $value): ?>
                        <tr>
                            <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                            <td><?= $value->doc ?></td>
                            <td style="text-align: center">
                                <a href="<?= Yii::$app->request->BaseUrl . '/uploads/pettycash_doc_slip/' . $value->doc ?>"
                                   target="_blank">
                                    ดูเอกสาร
                                </a>
                            </td>
                            <td style="text-align: center">
                                <!--                                <div class="btn btn-danger" data-var="-->
                                <?php //= trim($value->doc_name) ?><!--" onclick="delete_doc($(this))">ลบ</div>-->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <br/>
    <br/>
    <div class="label">
        <h4>เอกสารแนบใบเสร็จ</h4>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <table class="table table-bordered table-striped" style="width: 100%">
                <thead>
                <tr>
                    <th style="width: 5%;text-align: center">#</th>
                    <th style="width: 50%;text-align: center">ชื่อไฟล์</th>
                    <th style="width: 10%;text-align: center">ดูเอกสาร</th>
                    <th style="width: 5%;text-align: center">-</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($model_doc_bill != null): ?>

                    <?php foreach ($model_doc_bill as $key => $value): ?>
                        <tr>
                            <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                            <td><?= $value->doc ?></td>
                            <td style="text-align: center">
                                <a href="<?= Yii::$app->request->BaseUrl . '/uploads/pettycash_doc_bill/' . $value->doc ?>"
                                   target="_blank">
                                    ดูเอกสาร
                                </a>
                            </td>
                            <td style="text-align: center">
                                <!--                                <div class="btn btn-danger" data-var="-->
                                <?php //= trim($value->doc_name) ?><!--" onclick="delete_doc($(this))">ลบ</div>-->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <br/>

</div>