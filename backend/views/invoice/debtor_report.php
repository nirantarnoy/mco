<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $reportData array */
/* @var $category int|null */
/* @var $startDate string|null */
/* @var $endDate string|null */
/* @var $searchKeyword string|null */

$this->title = 'รายงานสถานะลูกหนี้ (Account Receivable Report)';
$this->params['breadcrumbs'][] = ['label' => 'จัดการเอกสาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$categoryTitles = [
    1 => '1. ใบแจ้งหนี้ ที่ยังไม่ออกใบกำกับ',
    2 => '2. ใบกำกับที่ออกใบวางบิลแล้ว แต่ยังไม่ออกใบเสร็จ',
    3 => '3. ใบเสร็จที่ออกแล้ว แต่ยังไม่บันทึกรับเงิน',
];

$categoryBadges = [
    1 => 'badge-primary',
    2 => 'badge-info',
    3 => 'badge-warning',
];

$count1 = count($reportData[1] ?? []);
$count2 = count($reportData[2] ?? []);
$count3 = count($reportData[3] ?? []);
$countTotal = $count1 + $count2 + $count3;
?>

<div class="debtor-report-index">
    <!-- Filter Card -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0"><i class="fas fa-filter mr-2"></i>ตัวกรองรายงานสถานะลูกหนี้</h5>
        </div>
        <div class="card-body">
            <form method="get" action="<?= Url::to(['debtor-report']) ?>" class="form-inline row">
                <input type="hidden" name="r" value="invoice/debtor-report">
                
                <div class="form-group col-md-3 mb-2">
                    <label class="mr-2 font-weight-bold">กลุ่มสถานะ:</label>
                    <select name="category" class="form-control w-100">
                        <option value="" <?= empty($category) ? 'selected' : '' ?>>-- ทั้งหมด (3 กลุ่ม) --</option>
                        <option value="1" <?= $category == 1 ? 'selected' : '' ?>>1. ใบแจ้งหนี้ ที่ยังไม่ออกใบกำกับ</option>
                        <option value="2" <?= $category == 2 ? 'selected' : '' ?>>2. ใบกำกับที่ออกใบวางบิลแล้ว แต่ยังไม่ออกใบเสร็จ</option>
                        <option value="3" <?= $category == 3 ? 'selected' : '' ?>>3. ใบเสร็จที่ออกแล้ว แต่ยังไม่บันทึกรับเงิน</option>
                    </select>
                </div>

                <div class="form-group col-md-2 mb-2">
                    <label class="mr-2 font-weight-bold">วันที่เริ่มต้น:</label>
                    <input type="date" name="start_date" value="<?= Html::encode($startDate) ?>" class="form-control w-100">
                </div>

                <div class="form-group col-md-2 mb-2">
                    <label class="mr-2 font-weight-bold">วันที่สิ้นสุด:</label>
                    <input type="date" name="end_date" value="<?= Html::encode($endDate) ?>" class="form-control w-100">
                </div>

                <div class="form-group col-md-3 mb-2">
                    <label class="mr-2 font-weight-bold">คำค้นหา:</label>
                    <input type="text" name="search" value="<?= Html::encode($searchKeyword) ?>" class="form-control w-100" placeholder="เลขที่เอกสาร, ใบงาน, ลูกค้า...">
                </div>

                <div class="form-group col-md-2 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2 flex-grow-1">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                    <a href="<?= Url::to(['debtor-report']) ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-white border shadow-sm">
                <div class="inner p-3">
                    <h3 class="text-primary mb-1"><?= number_format($count1) ?></h3>
                    <p class="text-muted mb-0 font-weight-bold">1. ใบแจ้งหนี้ที่ยังไม่ออกใบกำกับ</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice text-primary" style="opacity: 0.15; font-size: 50px; position: absolute; right: 15px; top: 15px;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-white border shadow-sm">
                <div class="inner p-3">
                    <h3 class="text-info mb-1"><?= number_format($count2) ?></h3>
                    <p class="text-muted mb-0 font-weight-bold">2. วางบิลแล้วยังไม่ออกใบเสร็จ</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar text-info" style="opacity: 0.15; font-size: 50px; position: absolute; right: 15px; top: 15px;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-white border shadow-sm">
                <div class="inner p-3">
                    <h3 class="text-warning mb-1"><?= number_format($count3) ?></h3>
                    <p class="text-muted mb-0 font-weight-bold">3. ใบเสร็จที่ยังไม่ได้รับเงิน</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt text-warning" style="opacity: 0.15; font-size: 50px; position: absolute; right: 15px; top: 15px;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-white border shadow-sm">
                <div class="inner p-3">
                    <h3 class="text-danger mb-1"><?= number_format($countTotal) ?></h3>
                    <p class="text-muted mb-0 font-weight-bold">รวมลูกหนี้ค้างชำระทั้งหมด</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd text-danger" style="opacity: 0.15; font-size: 50px; position: absolute; right: 15px; top: 15px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> กลับหน้ารายการเอกสาร', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <div>
            <?= Html::a('<i class="fas fa-file-excel mr-1"></i> ดึงออกเป็น Excel (.xlsx)', [
                'export-debtor',
                'category' => $category,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'search' => $searchKeyword,
            ], [
                'class' => 'btn btn-success btn-lg shadow-sm font-weight-bold',
                'target' => '_blank',
            ]) ?>
        </div>
    </div>

    <!-- Data Tables By Category -->
    <?php foreach ([1, 2, 3] as $catKey): ?>
        <?php if (!empty($category) && $category != $catKey) continue; ?>
        <?php 
            $rows = $reportData[$catKey] ?? []; 
            $catTotal = 0;
            $catCnDn = 0;
            $catPaid = 0;
            $catRemaining = 0;
        ?>
        <div class="card card-outline <?= ($catKey == 1) ? 'card-primary' : (($catKey == 2) ? 'card-info' : 'card-warning') ?> mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 font-weight-bold">
                    <span class="badge <?= $categoryBadges[$catKey] ?> mr-2">กลุ่ม <?= $catKey ?></span>
                    <?= $categoryTitles[$catKey] ?>
                </h5>
                <span class="badge badge-secondary"><?= count($rows) ?> รายการ</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped table-bordered mb-0">
                    <thead class="thead-light">
                        <tr class="text-center text-nowrap">
                            <th style="width: 50px;">#</th>
                            <th>ใบงาน (Job No)</th>
                            <th>วันที่ออกเอกสาร</th>
                            <th>เลขที่เอกสาร</th>
                            <th>ลูกค้า</th>
                            <th>วันครบกำหนดชำระ</th>
                            <th class="text-right">ยอดจำนวนเต็ม</th>
                            <th class="text-right">ใบลดหนี้/เพิ่มหนี้</th>
                            <th class="text-right">ยอดที่ชำระแล้ว</th>
                            <th class="text-right">ยอดค้างชำระ</th>
                            <th>วันที่เกินกำหนดชำระ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle text-success mr-1"></i> ไม่พบรายการค้างชำระในกลุ่มนี้
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $index => $row): ?>
                                <?php
                                    $catTotal += floatval($row['total_amount']);
                                    $catCnDn += floatval($row['net_cn_dn']);
                                    $catPaid += floatval($row['total_paid']);
                                    $catRemaining += floatval($row['remaining_amount']);
                                ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td class="text-center font-weight-bold"><?= Html::encode($row['job_no']) ?></td>
                                    <td class="text-center text-nowrap"><?= !empty($row['invoice_date']) ? date('d/m/Y', strtotime($row['invoice_date'])) : '-' ?></td>
                                    <td class="text-center font-weight-bold">
                                        <?= Html::a(Html::encode($row['invoice_number']), ['view', 'id' => $row['id']], ['target' => '_blank', 'class' => 'text-primary']) ?>
                                    </td>
                                    <td><?= Html::encode($row['customer_name'] ?: '-') ?></td>
                                    <td class="text-center text-nowrap"><?= !empty($row['computed_due_date']) ? date('d/m/Y', strtotime($row['computed_due_date'])) : '-' ?></td>
                                    <td class="text-right font-weight-bold"><?= number_format($row['total_amount'], 2) ?></td>
                                    <td class="text-right <?= ($row['net_cn_dn'] < 0) ? 'text-danger' : (($row['net_cn_dn'] > 0) ? 'text-success' : 'text-muted') ?>">
                                        <?= ($row['net_cn_dn'] > 0 ? '+' : '') . number_format($row['net_cn_dn'], 2) ?>
                                    </td>
                                    <td class="text-right text-success font-weight-bold"><?= number_format($row['total_paid'], 2) ?></td>
                                    <td class="text-right text-danger font-weight-bold" style="font-size: 1.05rem;"><?= number_format($row['remaining_amount'], 2) ?></td>
                                    <td class="text-center text-nowrap">
                                        <?php if ($row['overdue_days'] > 0): ?>
                                            <span class="badge badge-danger p-2"><i class="fas fa-exclamation-triangle mr-1"></i>เกิน <?= $row['overdue_days'] ?> วัน</span>
                                        <?php else: ?>
                                            <span class="badge badge-success p-2"><i class="fas fa-clock mr-1"></i>ยังไม่เกินกำหนด</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($rows)): ?>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="6" class="text-right">รวมยอดกลุ่มนี้ (<?= count($rows) ?> รายการ):</td>
                                <td class="text-right"><?= number_format($catTotal, 2) ?></td>
                                <td class="text-right"><?= ($catCnDn > 0 ? '+' : '') . number_format($catCnDn, 2) ?></td>
                                <td class="text-right text-success"><?= number_format($catPaid, 2) ?></td>
                                <td class="text-right text-danger" style="font-size: 1.1rem;"><?= number_format($catRemaining, 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>
