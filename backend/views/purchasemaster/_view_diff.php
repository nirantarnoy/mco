<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ActionLogModel */

$data = $model->getFormattedData();
$changes = $data['changed_attributes'] ?? [];
$pmModel = new \backend\models\PurchaseMaster();

?>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ฟิลด์ที่แก้ไข</th>
                <th>ค่าก่อนหน้า (Old Value)</th>
                <th>ค่าใหม่ (New Value)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($changes)): ?>
                <?php foreach ($changes as $attribute => $values): 
                    $isAmount = (strpos($attribute, 'amount') !== false || strpos($attribute, 'price') !== false);
                    $oldVal = is_array($values['old']) ? json_encode($values['old'], JSON_UNESCAPED_UNICODE) : ($isAmount && is_numeric($values['old']) ? number_format((float)$values['old'], 2) : $values['old']);
                    $newVal = is_array($values['new']) ? json_encode($values['new'], JSON_UNESCAPED_UNICODE) : ($isAmount && is_numeric($values['new']) ? number_format((float)$values['new'], 2) : $values['new']);
                    
                    $label = $pmModel->getAttributeLabel($attribute);
                ?>
                    <tr>
                        <td><strong><?= Html::encode($label) ?></strong> 
                            <br><small class="text-muted">(<?= Html::encode($attribute) ?>)</small>
                        </td>
                        <td class="text-danger">
                            <del><?= Html::encode($oldVal ?? '-') ?></del>
                        </td>
                        <td class="text-success">
                            <?= Html::encode($newVal ?? '-') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">ไม่พบข้อมูลการเปลี่ยนแปลง หรือเป็นการสร้างใหม่</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
