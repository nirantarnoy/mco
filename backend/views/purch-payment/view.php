<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\PurchPayment */

$this->title = 'รายละเอียดการจ่ายเงิน #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'รายการบันทึกการจ่ายเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="purch-payment-view">

    <p>
        <?= Html::a('<i class="fas fa-edit"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fas fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'คุณแน่ใจหรือไม่ที่จะลบรายการนี้?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('<i class="fas fa-arrow-left"></i> กลับ', ['index'], ['class' => 'btn btn-secondary']) ?>
    </p>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ข้อมูลการจ่ายเงิน</h3>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-bordered detail-view'],
                'attributes' => [
                 //   'id',
                    [
                        'attribute' => 'purch_id',
                        'value' => function($model) {
                            return $model->purch ? $model->purch->purch_no : '-';
                        },
                        'label' => 'เลขที่ใบสั่งซื้อ',
                    ],
                    [
                        'attribute' => 'vendor_name',
                        'value' => function($model) {
                            return $model->purch ? $model->purch->vendor_name : '-';
                        },
                        'label' => 'ชื่อผู้ขาย',
                    ],
                    [
                        'attribute' => 'trans_date',
                        'format' => ['date', 'php:d/m/Y'],
                    ],
                    'status',
                    [
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:d/m/Y H:i:s'],
                    ],
                    [
                        'attribute' => 'created_by',
                        'value' => function($model) {
                            return $model->created_by ? \backend\models\User::findEmployeeNameByUserId($model->created_by) : '-';
                        }
                    ],
                ],
            ]) ?>
        </div>
    </div>

    <?php if ($model->purch): ?>
        <div class="card mt-3">
            <div class="card-header bg-info">
                <h3 class="card-title">รายละเอียดใบสั่งซื้อ</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>เลขที่ใบสั่งซื้อ:</strong>
                        <div><?= $model->purch->purch_no ?></div>
                    </div>
                    <div class="col-md-3">
                        <strong>ชื่อผู้ขาย:</strong>
                        <div><?= $model->purch->vendor_name ?></div>
                    </div>
                    <div class="col-md-3">
                        <strong>วันที่สั่งซื้อ:</strong>
                        <div><?= Yii::$app->formatter->asDate($model->purch->purch_date, 'php:d/m/Y') ?></div>
                    </div>
                    <div class="col-md-3">
                        <strong>ยอดสุทธิ:</strong>
                        <div class="text-danger font-weight-bold"><?= Yii::$app->formatter->asDecimal($model->purch->net_amount, 2) ?> บาท</div>
                    </div>
                </div>

                <?php if ($model->purch->purchLines): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead class="thead-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">รหัสสินค้า</th>
                                <th width="25%">ชื่อสินค้า</th>
                                <th width="15%">รายละเอียด</th>
                                <th width="10%" class="text-center">จำนวน</th>
                                <th width="10%" class="text-right">ราคา/หน่วย</th>
                                <th width="10%" class="text-right">ราคารวม</th>
                                <th width="10%">หมายเหตุ</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($model->purch->purchLines as $index => $line): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td><?= $line->product_id ?: '-' ?></td>
                                    <td><?= $line->product_name ?: '-' ?></td>
                                    <td><?= $line->product_description ?: '-' ?></td>
                                    <td class="text-center"><?= $line->qty ?: 0 ?></td>
                                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($line->line_price, 2) ?></td>
                                    <td class="text-right"><?= Yii::$app->formatter->asDecimal($line->line_total, 2) ?></td>
                                    <td><?= $line->note ?: '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card mt-3">
        <div class="card-header bg-success">
            <h3 class="card-title">รายการโอนเงิน</h3>
        </div>
        <div class="card-body">
            <?php if ($model->purchPaymentLines): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">ธนาคาร</th>
                            <th width="15%">ประเภทการโอน</th>
                            <th width="15%" class="text-right">จำนวนเงิน</th>
                            <th width="20%">เอกสารแนบ</th>
                            <th width="30%">หมายเหตุ</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $total = 0;
                        foreach ($model->purchPaymentLines as $index => $line):
                            $total += $line->pay_amount;
                            ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= $line->bank_name ?: '-' ?></td>
                                <td><?= $line->paymentMethod ? $line->paymentMethod->name : '-' ?></td>
                                <td class="text-right"><?= Yii::$app->formatter->asDecimal($line->pay_amount, 2) ?></td>
                                <td>
                                    <?php if ($line->doc): ?>
                                        <a href="<?= Yii::getAlias('@web/uploads/payment_slips/' . $line->doc) ?>" target="_blank" class="btn btn-info btn-sm">
                                            <i class="fas fa-file"></i> ดูไฟล์
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">ไม่มีไฟล์</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $line->nodet ?: '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>ยอดรวมที่โอน:</strong></td>
                            <td class="text-right">
                                <strong class="text-success"><?= Yii::$app->formatter->asDecimal($total, 2) ?> บาท</strong>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">ไม่มีรายการโอนเงิน</p>
            <?php endif; ?>
        </div>
    </div>

</div>