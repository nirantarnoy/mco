<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $filter_qty string */
/* @var $product_group_id int|null */

$this->title = 'รายงานแสดงยอดสินค้าคงเหลือ (แยก Lot)';
$this->params['breadcrumbs'][] = ['label' => 'จัดการสต๊อกสินค้า', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$stockSums = $dataProvider->getModels();
?>
<style>
    .stock-report table {
        font-size: 14px;
    }
    .stock-report .text-right {
        text-align: right;
    }
    .stock-report .text-center {
        text-align: center;
    }
    @media print {
        .card-tools, .main-footer, .breadcrumb, .main-sidebar, .main-header {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
        }
    }
</style>
<div class="stock-report">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?php
                $productGroups = \yii\helpers\ArrayHelper::map(\backend\models\Productgroup::find()->where(['status' => 1])->all(), 'id', 'name');
                ?>
                <label style="margin-right: 5px;">ยอด ณ วันที่:</label>
                <input type="date" class="form-control" style="width: auto; display: inline-block; margin-right: 10px;" 
                       value="<?= Html::encode($as_of_date ?? '') ?>" 
                       onchange="location.href='<?= Url::current(['as_of_date' => '']) ?>' + this.value">
                <label style="margin-right: 5px;">ยอดสิ้นเดือน:</label>
                <select class="form-control" style="width: auto; display: inline-block; margin-right: 10px;" onchange="location.href=this.value">
                    <option value="<?= Url::current(['snapshot_period' => null]) ?>">-- ปัจจุบัน --</option>
                    <?php if (isset($availableSnapshots) && is_array($availableSnapshots)): ?>
                        <?php foreach ($availableSnapshots as $period => $label): ?>
                            <option value="<?= Url::current(['snapshot_period' => $period]) ?>" <?= ($snapshot_period ?? '') == $period ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <select class="form-control" style="width: auto; display: inline-block; margin-right: 10px;" onchange="location.href=this.value">
                    <option value="<?= Url::current(['product_group_id' => null]) ?>">-- ทุกหมวดหมู่ --</option>
                    <?php foreach ($productGroups as $id => $name): ?>
                        <option value="<?= Url::current(['product_group_id' => $id]) ?>" <?= ($product_group_id ?? '') == $id ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-control" style="width: auto; display: inline-block; margin-right: 10px;" onchange="location.href=this.value">
                    <option value="<?= Url::current(['filter_qty' => 'all']) ?>" <?= ($filter_qty ?? '') == 'all' ? 'selected' : '' ?>>แสดงทั้งหมด</option>
                    <option value="<?= Url::current(['filter_qty' => 'gt0']) ?>" <?= ($filter_qty ?? '') == 'gt0' ? 'selected' : '' ?>>แสดงคงเหลือ > 0</option>
                    <option value="<?= Url::current(['filter_qty' => 'eq0']) ?>" <?= ($filter_qty ?? '') == 'eq0' ? 'selected' : '' ?>>แสดงคงเหลือ = 0</option>
                </select>
                <a href="<?= Url::current(['export' => 'excel']) ?>" class="btn btn-success"><i class="fas fa-file-excel"></i> Export Excel</a>
                <button class="btn btn-default" onclick="window.print()"><i class="fas fa-print"></i> พิมพ์</button>
                <button class="btn btn-warning" data-toggle="modal" data-target="#snapshotModal"><i class="fas fa-save"></i> ประมวลผลยอดสิ้นเดือน</button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>หมวดหมู่สินค้า</th>
                        <th>รหัสสินค้า</th>
                        <th>รายการสินค้า</th>
                        <th>Lot No.</th>
                        <th>หน่วยนับ</th>
                        <th class="text-right">คงเหลือ</th>
                        <th class="text-right">ราคาต่อหน่วย</th>
                        <th class="text-right">มูลค่าคงเหลือ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $current_group = null;
                    $group_qty = 0;
                    $group_value = 0;
                    $total_qty = 0;
                    $total_value = 0;

                    foreach ($stockSums as $index => $stockSum):
                        $product = $stockSum->product;
                        if (!$product) continue;
                        
                        $group_name = $product->productGroup ? $product->productGroup->name : 'ไม่ระบุหมวดหมู่';

                        // Find original receiving price for this specific lot
                        $receiveLine = \backend\models\JournalTransLine::find()
                            ->joinWith('journalTrans')
                            ->where(['journal_trans_line.product_id' => $product->id])
                            ->andWhere(['journal_trans_line.lot_no' => $stockSum->lot_no])
                            ->andWhere(['journal_trans.trans_type_id' => \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE])
                            ->one();
                        
                        // If found, use sale_price (which stores the unit price from PO), otherwise fallback to cost_price or sale_price
                        $unit_price = $receiveLine && $receiveLine->sale_price > 0 ? $receiveLine->sale_price : 0;
                        
                        if ($unit_price <= 0) {
                            $unit_price = $product->cost_price > 0 ? $product->cost_price : ($product->sale_price > 0 ? $product->sale_price : 0);
                        }
                        
                        // If still 0, try to get the latest PO price for this product as last resort
                        if ($unit_price <= 0) {
                            $latestReceive = \backend\models\JournalTransLine::find()
                                ->joinWith('journalTrans')
                                ->where(['journal_trans_line.product_id' => $product->id])
                                ->andWhere(['journal_trans.trans_type_id' => \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE])
                                ->andWhere(['>', 'journal_trans_line.sale_price', 0])
                                ->orderBy(['journal_trans.id' => SORT_DESC])
                                ->one();
                            if ($latestReceive) {
                                $unit_price = $latestReceive->sale_price;
                            }
                        }

                        $balance_value = $stockSum->qty * floatval($unit_price);

                        if ($current_group !== null && $current_group !== $group_name):
                    ?>
                        <tr style="background-color: #f4f4f4; font-weight: bold;">
                            <td colspan="5" class="text-right">รวมหมวดนี้</td>
                            <td class="text-right"><?= number_format($group_qty, 2) ?></td>
                            <td></td>
                            <td class="text-right"><?= number_format($group_value, 2) ?></td>
                        </tr>
                    <?php
                            $group_qty = 0;
                            $group_value = 0;
                        endif;

                        $current_group = $group_name;
                        $group_qty += $stockSum->qty;
                        $group_value += $balance_value;
                        $total_qty += $stockSum->qty;
                        $total_value += $balance_value;
                    ?>
                        <tr>
                            <td><?= Html::encode($group_name) ?></td>
                            <td><a href="<?= Url::to(['product/update', 'id' => $product->id]) ?>" target="_blank" title="คลิกเพื่อแก้ไขข้อมูลสินค้า"><?= Html::encode($product->code) ?></a></td>
                            <td><?= Html::encode($product->name) ?></td>
                            <td><?= Html::encode($stockSum->lot_no ?: '-') ?></td>
                            <td><?= Html::encode($product->unit ? $product->unit->name : '') ?></td>
                            <td class="text-right"><?= number_format($stockSum->qty, 2) ?></td>
                            <td class="text-right"><?= number_format($unit_price, 2) ?></td>
                            <td class="text-right"><?= number_format($balance_value, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if ($current_group !== null): ?>
                        <tr style="background-color: #f4f4f4; font-weight: bold;">
                            <td colspan="5" class="text-right">รวมหมวดนี้</td>
                            <td class="text-right"><?= number_format($group_qty, 2) ?></td>
                            <td></td>
                            <td class="text-right"><?= number_format($group_value, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #e9ecef; font-weight: bold;">
                        <td colspan="5" class="text-center">รวมทั้งสิ้น</td>
                        <td class="text-right"><?= number_format($total_qty, 2) ?></td>
                        <td></td>
                        <td class="text-right"><?= number_format($total_value, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Snapshot Modal -->
<div class="modal fade" id="snapshotModal" tabindex="-1" role="dialog" aria-labelledby="snapshotModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?= Html::beginForm(['process-snapshot'], 'post') ?>
      <div class="modal-header">
        <h5 class="modal-title" id="snapshotModalLabel">ประมวลผลยอดยกไปสิ้นเดือน</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label>เลือกเดือนที่ต้องการบันทึกยอด (คศ-เดือน)</label>
            <input type="month" name="snapshot_period" class="form-control" value="<?= date('Y-m') ?>" required>
            <small class="form-text text-muted">ระบบจะทำการคำนวณและบันทึกยอดคงเหลือ ณ วันสิ้นเดือนของเดือนที่เลือก (หากเคยบันทึกแล้วจะถูกแทนที่ด้วยข้อมูลใหม่)</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">ยืนยันการประมวลผล</button>
      </div>
      <?= Html::endForm() ?>
    </div>
  </div>
</div>

