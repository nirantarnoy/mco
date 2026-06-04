<?php
use yii\helpers\Html;

$this->title = 'ตรวจสอบข้อมูลนำเข้าผู้ขาย';
$this->params['breadcrumbs'][] = ['label' => 'ผู้ขาย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vendor-import-preview">
    <div class="card">
        <div class="card-body">
            
            <form action="<?= \yii\helpers\Url::to(['vendor/import-confirm']) ?>" method="post">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">
                
                <?php if (!empty($mismatchedVendors)): ?>
                    <h4 class="text-warning">ผู้ขายที่มีแล้วในระบบแต่ข้อมูล Tax ID ไม่ตรงกัน</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ชื่อผู้ขาย</th>
                                    <th>ที่อยู่ (เดิม)</th>
                                    <th>ที่อยู่ (ใหม่)</th>
                                    <th>Tax ID (เดิม)</th>
                                    <th>Tax ID (ใหม่)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mismatchedVendors as $index => $vendor): ?>
                                    <tr>
                                        <td>
                                            <?= Html::encode($vendor['name']) ?>
                                            <input type="hidden" name="mismatchedVendors[<?= $index ?>][id]" value="<?= Html::encode($vendor['id']) ?>">
                                            <input type="hidden" name="mismatchedVendors[<?= $index ?>][new_taxid]" value="<?= Html::encode($vendor['new_taxid']) ?>">
                                            <input type="hidden" name="mismatchedVendors[<?= $index ?>][address]" value="<?= Html::encode($vendor['address']) ?>">
                                        </td>
                                        <td><?= Html::encode($vendor['old_address']) ?></td>
                                        <td><?= Html::encode($vendor['address']) ?></td>
                                        <td><?= Html::encode($vendor['old_taxid']) ?></td>
                                        <td><span class="text-danger"><?= Html::encode($vendor['new_taxid']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <br>
                <?php endif; ?>

                <?php if (!empty($newVendors)): ?>
                    <h4 class="text-success">ผู้ขายใหม่ที่ยังไม่มีในระบบ</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ชื่อผู้ขาย</th>
                                    <th>ที่อยู่</th>
                                    <th>Tax ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($newVendors as $index => $vendor): ?>
                                    <tr>
                                        <td>
                                            <?= Html::encode($vendor['name']) ?>
                                            <input type="hidden" name="newVendors[<?= $index ?>][name]" value="<?= Html::encode($vendor['name']) ?>">
                                            <input type="hidden" name="newVendors[<?= $index ?>][address]" value="<?= Html::encode($vendor['address']) ?>">
                                            <input type="hidden" name="newVendors[<?= $index ?>][taxid]" value="<?= Html::encode($vendor['taxid']) ?>">
                                        </td>
                                        <td><?= Html::encode($vendor['address']) ?></td>
                                        <td><?= Html::encode($vendor['taxid']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (empty($mismatchedVendors) && empty($newVendors)): ?>
                    <div class="alert alert-info">
                        ไม่พบผู้ขายใหม่ หรือข้อมูลที่ต้องอัพเดท
                    </div>
                    <a href="<?= \yii\helpers\Url::to(['vendor/index']) ?>" class="btn btn-secondary">กลับไปหน้าผู้ขาย</a>
                <?php else: ?>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> ยืนยันการอัพเดทและนำเข้าข้อมูล</button>
                        <a href="<?= \yii\helpers\Url::to(['vendor/index']) ?>" class="btn btn-secondary">ยกเลิก</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
