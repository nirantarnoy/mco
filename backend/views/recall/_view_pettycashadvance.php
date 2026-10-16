<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model backend\models\PettyCashAdvance */
?>
<div class="recall-view-pettycashadvance">
    <!-- Recall Reason Alert -->
    <div class="alert alert-warning border border-warning">
        <h5 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> คำร้องขอดึงใบเบิกเงินทดแทนสดย่อยกลับมาแก้ไข (Recall)</h5>
        <div class="row mt-2">
            <div class="col-md-6">
                <strong>ผู้ร้องขอ:</strong> <?= \backend\models\User::findEmployeeNameByUserId($model->recall_by) ?: 'ไม่ระบุ' ?>
            </div>
            <div class="col-md-6">
                <strong>เวลาที่ขอ:</strong> <?= $model->recall_at ? date('d/m/Y H:i:s', strtotime($model->recall_at)) : '-' ?>
            </div>
            <div class="col-12 mt-2">
                <strong>เหตุผลที่ระบุ:</strong>
                <div class="p-2 bg-white rounded border mt-1 text-dark">
                    <?= nl2br(Html::encode($model->recall_reason)) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Info -->
    <div class="row">
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr>
                    <th class="bg-light" style="width: 40%;">เลขที่ใบเบิกทดแทน</th>
                    <td><strong><?= Html::encode($model->advance_no) ?></strong></td>
                </tr>
                <tr>
                    <th class="bg-light">วันที่เบิก</th>
                    <td><?= $model->request_date ? date('d/m/Y', strtotime($model->request_date)) : '-' ?></td>
                </tr>
                <tr>
                    <th class="bg-light">พนักงานที่เบิก</th>
                    <td><?= Html::encode($model->employee ? $model->employee->fname . ' ' . $model->employee->lname : '-') ?></td>
                </tr>
                <tr>
                    <th class="bg-light">สถานะ</th>
                    <td><span class="badge badge-success"><?= Html::encode($model->status) ?></span></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-bordered">
                <tr>
                    <th class="bg-light" style="width: 40%;">ผู้อนุมัติเดิม</th>
                    <td><?= Html::encode($model->approver ? $model->approver->fname . ' ' . $model->approver->lname : '-') ?></td>
                </tr>
                <tr>
                    <th class="bg-light">วัตถุประสงค์</th>
                    <td><?= nl2br(Html::encode($model->purpose)) ?></td>
                </tr>
                <tr class="table-warning font-weight-bold">
                    <th>ยอดเบิกทดแทน</th>
                    <td class="text-right" style="font-size: 16px;"><?= number_format($model->amount, 2) ?> บาท</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Action Buttons inside Modal -->
    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
        <div>
            <?= Html::a('<i class="fas fa-external-link-alt mr-1"></i> เปิดดูเอกสารเต็มหน้าต่างใหม่', ['petty-cash-advance/view', 'id' => $model->id], [
                'class' => 'btn btn-outline-secondary btn-sm',
                'target' => '_blank',
            ]) ?>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-check mr-1"></i> อนุมัติ Recall (ปลดล็อคให้แก้ไข)', ['approve', 'type' => 'pettycashadvance', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'ยืนยันการอนุมัติ Recall รายการ ' . $model->advance_no . '? สถานะจะถูกเปลี่ยนเป็นรอดำเนินการเพื่อให้สามารถแก้ไขได้',
                    'method' => 'post',
                ],
            ]) ?>
            <button type="button" class="btn btn-danger btn-reject-modal ml-2" data-type="pettycashadvance" data-id="<?= $model->id ?>" data-doc="<?= Html::encode($model->advance_no) ?>" data-dismiss="modal">
                <i class="fas fa-times mr-1"></i> ปฏิเสธ Recall
            </button>
        </div>
    </div>
</div>
