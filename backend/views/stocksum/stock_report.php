<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'รายงานแสดงยอดสินค้าคงเหลือ';
$this->params['breadcrumbs'][] = ['label' => 'จัดการสต๊อกสินค้า', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$products = $dataProvider->getModels();
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
                <button class="btn btn-default" onclick="window.print()"><i class="fas fa-print"></i> พิมพ์</button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>หมวดหมู่สินค้า</th>
                        <th>รหัสสินค้า</th>
                        <th>รายการสินค้า</th>
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

                    foreach ($products as $index => $product):
                        $group_name = $product->productGroup ? $product->productGroup->name : 'ไม่ระบุหมวดหมู่';
                        $balance_value = $product->stock_qty * $product->cost_price;

                        if ($current_group !== null && $current_group !== $group_name):
                    ?>
                        <tr style="background-color: #f4f4f4; font-weight: bold;">
                            <td colspan="4" class="text-right">รวมหมวดนี้</td>
                            <td class="text-right"><?= number_format($group_qty, 2) ?></td>
                            <td></td>
                            <td class="text-right"><?= number_format($group_value, 2) ?></td>
                        </tr>
                    <?php
                            $group_qty = 0;
                            $group_value = 0;
                        endif;

                        $current_group = $group_name;
                        $group_qty += $product->stock_qty;
                        $group_value += $balance_value;
                        $total_qty += $product->stock_qty;
                        $total_value += $balance_value;
                    ?>
                        <tr>
                            <td><?= Html::encode($group_name) ?></td>
                            <td><?= Html::encode($product->code) ?></td>
                            <td><?= Html::encode($product->name) ?></td>
                            <td><?= Html::encode($product->unit ? $product->unit->name : '') ?></td>
                            <td class="text-right"><?= number_format($product->stock_qty, 2) ?></td>
                            <td class="text-right"><?= number_format($product->cost_price, 2) ?></td>
                            <td class="text-right"><?= number_format($balance_value, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if ($current_group !== null): ?>
                        <tr style="background-color: #f4f4f4; font-weight: bold;">
                            <td colspan="4" class="text-right">รวมหมวดนี้</td>
                            <td class="text-right"><?= number_format($group_qty, 2) ?></td>
                            <td></td>
                            <td class="text-right"><?= number_format($group_value, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #e9ecef; font-weight: bold;">
                        <td colspan="4" class="text-center">รวมทั้งสิ้น</td>
                        <td class="text-right"><?= number_format($total_qty, 2) ?></td>
                        <td></td>
                        <td class="text-right"><?= number_format($total_value, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
