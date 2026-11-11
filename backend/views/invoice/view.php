<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\Invoice;

/* @var $this yii\web\View */
/* @var $model backend\models\Invoice */

$this->title = $model->invoice_number;
$this->params['breadcrumbs'][] = ['label' => 'จัดการเอกสาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$typeLabels = Invoice::getTypeOptions();
$statusLabels = Invoice::getStatusOptions();

$model_doc = \common\models\InvoiceDoc::find()->where(['invoice_id' => $model->id])->all();
?>
<div class="invoice-view">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <span class="badge badge-<?= $model->status == Invoice::STATUS_ACTIVE ? 'success' : 'danger' ?> ml-2">
                <?= $statusLabels[$model->status] ?>
            </span>
        </h1>
        <div>
            <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'target' => '_blank'
            ]) ?>

            <!-- Copy Dropdown -->
            <div class="btn-group">
                <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-copy"></i> คัดลอกเป็น
                </button>
                <div class="dropdown-menu">
                    <?php foreach (Invoice::getTypeOptions() as $type => $label): ?>
                        <?php if ($type != $model->invoice_type): ?>
                            <?= Html::a($label, ['copy', 'id' => $model->id, 'new_type' => $type], ['class' => 'dropdown-item']) ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <?= Html::a('<i class="fas fa-trash"></i> ยกเลิก', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data-confirm' => 'คุณแน่ใจหรือไม่ที่จะยกเลิกเอกสารนี้?',
                'data-method' => 'post',
            ]) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> กลับ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> ข้อมูลเอกสาร
                        <span class="badge badge-primary ml-2"><?= $typeLabels[$model->invoice_type] ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-bordered detail-view'],
                        'attributes' => [
                            'invoice_number',
                            [
                                'attribute' => 'job_id',
                                'value' => function ($model) {
                                    $jobNo = '-';
                                    $customerName = '';
                                    if ($model->job_id && $model->job) {
                                        $jobNo = $model->job->job_no;
                                        if ($model->job->quotation_id) {
                                            $quotation = \backend\models\Quotation::find()->where(['id' => $model->job->quotation_id])->one();
                                            if ($quotation) {
                                                $customerName = $quotation->customer ? $quotation->customer->name : $quotation->customer_name;
                                            }
                                        }
                                    } elseif ($model->quotation_id) {
                                        $job = \backend\models\Job::find()->where(['quotation_id' => $model->quotation_id])->one();
                                        if ($job) {
                                            $jobNo = $job->job_no;
                                            $quotation = \backend\models\Quotation::find()->where(['id' => $model->quotation_id])->one();
                                            if ($quotation) {
                                                $customerName = $quotation->customer ? $quotation->customer->name : $quotation->customer_name;
                                            }
                                        }
                                    }
                                    return $jobNo !== '-' ? $jobNo . ($customerName ? ' ' . $customerName : '') : '-';
                                }
                            ],
                            'customer_name',
                            [
                                'attribute' => 'customer_address',
                                'value' => $model->customer_address ?: '-',
                                'format' => 'ntext',
                            ],
                            [
                                'attribute' => 'customer_tax_id',
                                'value' => $model->customer_tax_id ?: '-',
                            ],
                            [
                                'attribute' => 'po_number',
                                'value' => $model->po_number ?: '-',
                            ],
                            [
                                'attribute' => 'po_date',
                                'value' => $model->po_date ? Yii::$app->formatter->asDate($model->po_date, 'MM/dd/yyyy') : '-',
                            ],
                            [
                                'attribute' => 'payment_term_id',
                                'value' => function ($model) {
                                    return $model->payment_term_id ? $model->paymentTerm->name : '-';
                                }
                            ],
                            [
                                'attribute' => 'due_date',
                                'value' => $model->due_date ? Yii::$app->formatter->asDate($model->due_date, 'MM/dd/yyyy') : '-',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator"></i> สรุปยอดเงิน
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>รวมเงิน:</td>
                            <td class="text-right"><?= number_format($model->subtotal, 2) ?></td>
                        </tr>
                        <tr>
                            <td>ส่วนลด (<?= $model->discount_percent ?>%):</td>
                            <td class="text-right"><?= number_format($model->discount_amount, 2) ?></td>
                        </tr>
                        <tr>
                            <td>ภาษี (<?= $model->vat_percent ?>%):</td>
                            <td class="text-right"><?= number_format($model->vat_amount, 2) ?></td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>รวมทั้งสิ้น:</strong></td>
                            <td class="text-right"><strong><?= number_format($model->total_amount, 2) ?> บาท</strong></td>
                        </tr>
                    </table>

                    <?php if ($model->total_amount_text): ?>
                        <div class="mt-3">
                            <small class="text-muted">ตัวอักษร:</small><br>
                            <span class="font-italic"><?= Html::encode($model->total_amount_text) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($model->invoice_type == Invoice::TYPE_BILL_PLACEMENT): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-check"></i> กำหนดการชำระ
                        </h5>
                    </div>
                    <div class="card-body">
                        <?= DetailView::widget([
                            'model' => $model,
                            'options' => ['class' => 'table table-bordered detail-view'],
                            'attributes' => [
                                [
                                    'attribute' => 'payment_due_date',
                                    'value' => $model->payment_due_date ? Yii::$app->formatter->asDate($model->payment_due_date, 'MM/dd/yyyy') : '-',
                                ],
                                [
                                    'attribute' => 'check_due_date',
                                    'value' => $model->check_due_date ? Yii::$app->formatter->asDate($model->check_due_date, 'MM/dd/yyyy') : '-',
                                ],
                            ],
                        ]) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Items Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> รายการสินค้า/บริการ
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($model->items)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                        <tr>
                            <th width="5%">ลำดับ</th>
                            <th width="40%">รายการ</th>
                            <th width="10%">จำนวน</th>
                            <th width="10%">หน่วย</th>
                            <th width="15%">ราคาต่อหน่วย</th>
                            <th width="15%">จำนวนเงิน</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($model->items as $index => $item): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= nl2br(Html::encode($item->item_description)) ?></td>
                                <td class="text-center"><?= number_format($item->quantity, 3) ?></td>
                                <td class="text-center"><?= Html::encode(\backend\models\Unit::findName($item->unit_id)) ?></td>
                                <td class="text-right"><?= number_format($item->unit_price, 3) ?></td>
                                <td class="text-right font-weight-bold"><?= number_format($item->amount, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                        <tr>
                            <th colspan="5" class="text-right">รวมทั้งหมด:</th>
                            <th class="text-right"><?= number_format($model->subtotal, 2) ?> บาท</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted p-4">
                    <i class="fas fa-inbox fa-2x"></i><br>
                    ไม่มีรายการสินค้า/บริการ
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    // ดึงเอกสารใบแจ้งหนี้/ใบกำกับภาษี และใบลดหนี้/ใบเพิ่มหนี้ ที่ผูกกับเอกสารนี้
    // 1. จากความสัมพันธ์ invoice_relations
    $relatedRelations = \backend\models\InvoiceRelation::find()
        ->where(['child_invoice_id' => $model->id])
        ->orWhere(['parent_invoice_id' => $model->id])
        ->all();

    $relatedInvIds = [];
    $relatedCnIds = [];
    $relatedDnIds = [];

    foreach ($relatedRelations as $rel) {
        $otherId = ($rel->child_invoice_id == $model->id) ? $rel->parent_invoice_id : $rel->child_invoice_id;
        if ($rel->relation_type === 'credit_note_to_receipt') {
            $relatedCnIds[] = $rel->parent_invoice_id;
        } elseif ($rel->relation_type === 'debit_note_to_receipt') {
            $relatedDnIds[] = $rel->parent_invoice_id;
        } else {
            $relatedInvIds[] = $otherId;
        }
    }

    // 2. ถ้าเป็น Receipt หรือมี quotation_id ให้ดึงใบแจ้งหนี้/ใบลดหนี้/ใบเพิ่มหนี้ ที่อยู่ใน quotation_id เดียวกันมาแสดงด้วย
    if ($model->quotation_id) {
        $sameQtInvoices = Invoice::find()
            ->where(['quotation_id' => $model->quotation_id])
            ->andWhere(['!=', 'id', $model->id])
            ->andWhere(['status' => Invoice::STATUS_ACTIVE])
            ->all();
        foreach ($sameQtInvoices as $sqInv) {
            $relatedInvIds[] = $sqInv->id;
        }

        $sameQtCns = \backend\models\CreditNote::find()
            ->where(['or', ['quotation_id' => $model->quotation_id], ['in', 'invoice_id', array_merge([0], $relatedInvIds)]])
            ->andWhere(['!=', 'status', 'canceled'])
            ->all();
        foreach ($sameQtCns as $sqCn) {
            $relatedCnIds[] = $sqCn->id;
        }

        $sameQtDns = \backend\models\DebitNote::find()
            ->where(['or', ['quotation_id' => $model->quotation_id], ['in', 'invoice_id', array_merge([0], $relatedInvIds)]])
            ->andWhere(['!=', 'status', 'canceled'])
            ->all();
        foreach ($sameQtDns as $sqDn) {
            $relatedDnIds[] = $sqDn->id;
        }
    }

    $relatedInvIds = array_unique(array_filter($relatedInvIds));
    $relatedCnIds = array_unique(array_filter($relatedCnIds));
    $relatedDnIds = array_unique(array_filter($relatedDnIds));

    $displayInvoices = !empty($relatedInvIds) ? Invoice::find()->where(['in', 'id', $relatedInvIds])->all() : [];
    $displayCns = !empty($relatedCnIds) ? \backend\models\CreditNote::find()->where(['in', 'id', $relatedCnIds])->all() : [];
    $displayDns = !empty($relatedDnIds) ? \backend\models\DebitNote::find()->where(['in', 'id', $relatedDnIds])->all() : [];
    ?>

    <?php if (!empty($displayInvoices) || !empty($displayCns) || !empty($displayDns)): ?>
        <div class="card mt-4 border-info">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0 text-info font-weight-bold">
                    <i class="fas fa-file-invoice mr-1"></i> เอกสารใบแจ้งหนี้/ใบกำกับภาษี และใบลดหนี้/ใบเพิ่มหนี้ ที่เกี่ยวข้อง
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm mb-0">
                        <thead class="table-light">
                        <tr class="text-center small">
                            <th width="5%">#</th>
                            <th width="15%">ประเภทเอกสาร</th>
                            <th width="20%">เลขที่เอกสาร</th>
                            <th width="15%">วันที่เอกสาร</th>
                            <th width="20%" class="text-right">ยอดเงิน/มูลค่า</th>
                            <th width="12%" class="text-right">VAT</th>
                            <th width="13%">การเชื่อมโยง</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $rowNum = 1; ?>
                        <?php foreach ($displayInvoices as $dInv): ?>
                            <tr>
                                <td class="text-center"><?= $rowNum++ ?></td>
                                <td class="text-center"><span class="badge badge-primary"><?= $dInv->getTypeLabel() ?></span></td>
                                <td class="font-weight-bold text-center">
                                    <?= Html::a(Html::encode($dInv->invoice_number), ['view', 'id' => $dInv->id], ['target' => '_blank', 'class' => 'text-primary']) ?>
                                </td>
                                <td class="text-center"><?= $dInv->invoice_date ? Yii::$app->formatter->asDate($dInv->invoice_date, 'php:d/m/Y') : '-' ?></td>
                                <td class="text-right font-weight-bold"><?= number_format($dInv->total_amount, 2) ?> บาท</td>
                                <td class="text-right"><?= number_format($dInv->vat_amount, 2) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-info">QT เดียวกัน</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php foreach ($displayCns as $dCn): ?>
                            <tr class="table-danger">
                                <td class="text-center"><?= $rowNum++ ?></td>
                                <td class="text-center"><span class="badge badge-danger">ใบลดหนี้ (Credit Note)</span></td>
                                <td class="font-weight-bold text-center">
                                    <?= Html::a(Html::encode($dCn->document_no), ['credit-note/view', 'id' => $dCn->id], ['target' => '_blank', 'class' => 'text-danger font-weight-bold']) ?>
                                </td>
                                <td class="text-center"><?= $dCn->document_date ? Yii::$app->formatter->asDate($dCn->document_date, 'php:d/m/Y') : '-' ?></td>
                                <td class="text-right font-weight-bold text-danger">-<?= number_format($dCn->total_amount, 2) ?> บาท</td>
                                <td class="text-right text-danger"><?= number_format($dCn->vat_amount, 2) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-danger">ลดหนี้</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php foreach ($displayDns as $dDn): ?>
                            <tr class="table-warning">
                                <td class="text-center"><?= $rowNum++ ?></td>
                                <td class="text-center"><span class="badge badge-warning text-dark">ใบเพิ่มหนี้ (Debit Note)</span></td>
                                <td class="font-weight-bold text-center">
                                    <?= Html::a(Html::encode($dDn->document_no), ['debit-note/view', 'id' => $dDn->id], ['target' => '_blank', 'class' => 'text-warning font-weight-bold text-dark']) ?>
                                </td>
                                <td class="text-center"><?= $dDn->document_date ? Yii::$app->formatter->asDate($dDn->document_date, 'php:d/m/Y') : '-' ?></td>
                                <td class="text-right font-weight-bold text-success">+<?= number_format($dDn->total_amount, 2) ?> บาท</td>
                                <td class="text-right"><?= number_format($dDn->vat_amount, 2) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-warning text-dark">เพิ่มหนี้</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Notes Section -->
    <?php if ($model->notes): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-sticky-note"></i> หมายเหตุ
                </h5>
            </div>
            <div class="card-body">
                <?= nl2br(Html::encode($model->notes)) ?>
            </div>
        </div>
    <?php endif; ?>

    <br/>
    <div class="label">
        <h4>เอกสารแนบ</h4>
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
                <?php if ($model_doc != null): ?>

                    <?php foreach ($model_doc as $key => $value): ?>
                        <tr>
                            <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                            <td><?= $value->doc ?></td>
                            <td style="text-align: center">
                                <a href="<?= Yii::$app->request->BaseUrl . '/uploads/invoice_doc/' . $value->doc ?>"
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

    <!-- Activity Log Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-history"></i> ประวัติการทำงาน
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>สร้างเมื่อ:</strong><br>
                    <?= ($model->created_at && !($model->created_at instanceof \yii\db\Expression) && $model->created_at !== 'NOW()') ? Yii::$app->formatter->asDatetime($model->created_at, 'dd/MM/yyyy HH:mm') : '-' ?><br>
                    <small class="text-muted">โดย: <?= $model->created_by ? 'User #' . $model->created_by : 'ระบบ' ?></small>
                </div>
                <div class="col-md-6">
                    <strong>แก้ไขล่าสุด:</strong><br>
                    <?= ($model->updated_at && !($model->updated_at instanceof \yii\db\Expression) && $model->updated_at !== 'NOW()') ? Yii::$app->formatter->asDatetime($model->updated_at, 'dd/MM/yyyy HH:mm') : '-' ?><br>
                    <small class="text-muted">โดย: <?= $model->updated_by ? 'User #' . $model->updated_by : 'ระบบ' ?></small>
                </div>
            </div>
        </div>
    </div>

</div>