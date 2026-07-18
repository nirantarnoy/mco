<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'รายงานสรุปภาษีหัก ณ ที่จ่าย ประจำเดือน';
$this->params['breadcrumbs'][] = ['label' => 'ระบบภาษีหัก ณ ที่จ่าย', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$months = [
    '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม',
    '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน',
    '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน',
    '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
];
?>
<div class="wht-report">

    <div class="row no-print">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="<?= Url::to(['wht/report']) ?>" method="GET" class="form-inline">
                        <label class="mr-2">เดือน:</label>
                        <select name="month" class="form-control mr-3">
                            <?php foreach ($months as $k => $v): ?>
                                <option value="<?= $k ?>" <?= $k == $month ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <label class="mr-2">ปี (ค.ศ.):</label>
                        <select name="year" class="form-control mr-3">
                            <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                        
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> ค้นหา</button>
                        <button type="button" class="btn btn-success ml-2" onclick="window.print()"><i class="fas fa-print"></i> พิมพ์รายงาน</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h4 class="text-center mb-4">รายงานสรุปภาษีหัก ณ ที่จ่าย (ภ.ง.ด. <?= Html::encode($month) ?>/<?= Html::encode($year) ?>)</h4>
            
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-striped">
                    <thead class="thead-light text-center">
                        <tr>
                            <th>ลำดับ</th>
                            <th>วันที่จ่ายเงิน</th>
                            <th>เลขที่ 50 ทวิ</th>
                            <th>ชื่อผู้ถูกหักภาษี</th>
                            <th>เลขประจำตัวผู้เสียภาษี</th>
                            <th>สาขา</th>
                            <th>ประเภทเงินได้</th>
                            <th>จำนวนเงินที่จ่าย (ก่อนภาษี)</th>
                            <th>จำนวนภาษีที่หัก (WHT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_base = 0;
                        $total_wht = 0;
                        $index = 1;
                        foreach ($dataProvider->models as $model): 
                            $total_base += $model->base_amount;
                            $total_wht += $model->wht_amount;
                            
                            $vendorBranch = '';
                            if ($model->vendor && !empty($model->vendor->branch_name)) {
                                $vendorBranch = $model->vendor->branch_name;
                            } else {
                                $vendorBranch = '-';
                            }
                        ?>
                        <tr>
                            <td class="text-center"><?= $index++ ?></td>
                            <td class="text-center"><?= $model->trans_date ? date('d/m/Y', strtotime($model->trans_date)) : '' ?></td>
                            <td><?= Html::encode($model->wht_no) ?></td>
                            <td><?= $model->vendor ? Html::encode($model->vendor->name) : '-' ?></td>
                            <td class="text-center"><?= $model->vendor ? Html::encode($model->vendor->taxid) : '-' ?></td>
                            <td class="text-center"><?= Html::encode($vendorBranch) ?></td>
                            <td><?= Html::encode($model->wht_desc == 'อื่นๆ' || $model->wht_desc == 'อื่นๆ (ระบุ)' ? $model->other_desc : $model->wht_desc) ?></td>
                            <td class="text-right"><?= number_format($model->base_amount, 2) ?></td>
                            <td class="text-right"><?= number_format($model->wht_amount, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($dataProvider->models)): ?>
                        <tr>
                            <td colspan="9" class="text-center">ไม่พบข้อมูลในเดือนที่เลือก</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold bg-light">
                            <td colspan="7" class="text-right">รวมทั้งสิ้น</td>
                            <td class="text-right text-primary"><?= number_format($total_base, 2) ?></td>
                            <td class="text-right text-danger"><?= number_format($total_wht, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        </div>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .wht-report, .wht-report * { visibility: visible; }
    .wht-report { position: absolute; left: 0; top: 0; width: 100%; }
    .no-print { display: none !important; }
    .card { border: none !important; }
    .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
    .table th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
}
</style>
