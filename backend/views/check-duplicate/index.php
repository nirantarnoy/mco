<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $customerList array */
/* @var $vendorList array */

$this->title = 'ตรวจสอบรหัสซ้ำ (ลูกค้าและผู้ขาย)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="check-duplicate-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">ลูกค้า (Customer) ที่รหัสซ้ำกัน</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($customerList)): ?>
                        <div class="alert alert-success">ไม่พบข้อมูลลูกค้าที่รหัสซ้ำกัน</div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>รหัสซ้ำ</th>
                                    <th>ชื่อลูกค้า</th>
                                    <th>รหัสใหม่</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customerList as $code => $models): ?>
                                    <?php foreach ($models as $index => $model): ?>
                                    <tr id="row-customer-<?= $model->id ?>">
                                        <?php if ($index === 0): ?>
                                        <td rowspan="<?= count($models) ?>" class="align-middle text-center text-danger fw-bold">
                                            <?= Html::encode($code) ?>
                                        </td>
                                        <?php endif; ?>
                                        <td class="align-middle"><?= Html::encode($model->name) ?></td>
                                        <td class="align-middle">
                                            <input type="text" class="form-control new-code-input" id="new-code-customer-<?= $model->id ?>" value="<?= Html::encode($model->code) ?>">
                                        </td>
                                        <td class="align-middle">
                                            <button class="btn btn-sm btn-success btn-update" data-type="customer" data-id="<?= $model->id ?>">อัพเดท</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">ผู้ขาย (Vendor) ที่รหัสซ้ำกัน</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($vendorList)): ?>
                        <div class="alert alert-success">ไม่พบข้อมูลผู้ขายที่รหัสซ้ำกัน</div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>รหัสซ้ำ</th>
                                    <th>ชื่อผู้ขาย</th>
                                    <th>รหัสใหม่</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vendorList as $code => $models): ?>
                                    <?php foreach ($models as $index => $model): ?>
                                    <tr id="row-vendor-<?= $model->id ?>">
                                        <?php if ($index === 0): ?>
                                        <td rowspan="<?= count($models) ?>" class="align-middle text-center text-danger fw-bold">
                                            <?= Html::encode($code) ?>
                                        </td>
                                        <?php endif; ?>
                                        <td class="align-middle"><?= Html::encode($model->name) ?></td>
                                        <td class="align-middle">
                                            <input type="text" class="form-control new-code-input" id="new-code-vendor-<?= $model->id ?>" value="<?= Html::encode($model->code) ?>">
                                        </td>
                                        <td class="align-middle">
                                            <button class="btn btn-sm btn-success btn-update" data-type="vendor" data-id="<?= $model->id ?>">อัพเดท</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$updateUrl = Url::to(['check-duplicate/update-code']);
$js = <<<JS
$('.btn-update').on('click', function() {
    var btn = $(this);
    var type = btn.data('type');
    var id = btn.data('id');
    var newCode = $('#new-code-' + type + '-' + id).val();
    
    if (!newCode.trim()) {
        alert('กรุณาระบุรหัสใหม่');
        return;
    }
    
    if (confirm('คุณต้องการอัพเดทรหัสใหม่เป็น ' + newCode + ' ใช่หรือไม่?')) {
        btn.prop('disabled', true).text('กำลังบันทึก...');
        
        $.ajax({
            url: '{$updateUrl}',
            type: 'POST',
            data: {
                type: type,
                id: id,
                new_code: newCode,
            },
            success: function(response) {
                if (response.success) {
                    alert('อัพเดทสำเร็จ');
                    location.reload(); // โหลดหน้าใหม่เพื่อให้ข้อมูลอัพเดท
                } else {
                    alert('เกิดข้อผิดพลาด: ' + response.message);
                    btn.prop('disabled', false).text('อัพเดท');
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                btn.prop('disabled', false).text('อัพเดท');
            }
        });
    }
});
JS;
$this->registerJs($js);
?>
