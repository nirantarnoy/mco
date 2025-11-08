<?php
/**
 * View: views/purch/_view_slip.php
 * Partial view สำหรับแสดง Slip ใน Modal
 */

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\PurchPaymentLine */
?>

<div class="slip-detail">
    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered table-sm">
                <tr>
                    <th style="width: 40%;">วันที่โอน:</th>
                    <td><?= date('d-m-Y H:i:s',strtotime($payment_date)) ?></td>
                </tr>
                <tr>
                    <th>ธนาคาร:</th>
                    <td><strong><?= Html::encode($model->bank_id) ?></strong></td>
                </tr>
                <tr>
                    <th>ชื่อบัญชี:</th>
                    <td><?= Html::encode($model->bank_name) ?></td>
                </tr>
                <tr>
                    <th>วิธีการชำระ:</th>
                    <td>
                        <span class="badge badge-primary">
                            <?= Html::encode($model->payment_method_id) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>จำนวนเงิน:</th>
                    <td>
                        <strong class="text-success" style="font-size: 1.2em;">
                            <?= Yii::$app->formatter->asDecimal($model->pay_amount, 2) ?>
                        </strong> บาท
                    </td>
                </tr>
                <?php if (!empty($model->note)): ?>
                    <tr>
                        <th>หมายเหตุ:</th>
                        <td><?= Html::encode($model->note) ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="col-md-6">
            <div class="slip-image-container">
                <h5 class="mb-3">รูปภาพ Slip:</h5>

                <?php if (!empty($model->doc)): ?>
                    <?php
                    // ตรวจสอบว่า doc เป็น URL หรือ path
                    $imageUrl = $model->doc;

                    // ถ้าเป็น path ใน server ให้ปรับเป็น URL
                    if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        // สมมติว่าเก็บไฟล์ใน web/uploads/slips/
                        $imageUrl = Yii::getAlias('@web/uploads/slips/' . $model->doc);
                    }
                    ?>

                    <div class="text-center">
                        <img src="<?= Html::encode($imageUrl) ?>"
                             class="img-fluid img-thumbnail slip-image"
                             alt="Slip การโอนเงิน"
                             style="max-height: 400px; cursor: pointer;"
                             onclick="window.open('<?= Html::encode($imageUrl) ?>', '_blank')">

                        <div class="mt-3">
                            <a href="<?= Html::encode($imageUrl) ?>"
                               target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> เปิดในหน้าต่างใหม่
                            </a>
                            <a href="<?= Html::encode($imageUrl) ?>"
                               download
                               class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download"></i> ดาวน์โหลด
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> ไม่พบรูปภาพ Slip
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .slip-detail {
        padding: 10px;
    }

    .slip-image {
        border: 2px solid #dee2e6;
        transition: transform 0.3s ease;
    }

    .slip-image:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .slip-image-container {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }
</style>