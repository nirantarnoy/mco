<?php
use yii\helpers\Html;

// Data preparation
//$emp_info  = \backend\models\Employee::findEmpInfo(\Yii::$app->user->id);
$emp_info  = \backend\models\Employee::findEmpInfo($model->created_by);
$prNumber = $model->purch_req_no;
$date = date('d/m/Y',strtotime($model->purch_req_date));
$requestor = \backend\models\User::findEmployeeNameByUserId($model->created_by);
$department = $emp_info != null ? $emp_info['department_name'] : '';
$requestType = $model->reason_title_id;

$model_footer = \common\models\PurchReqFootTitle::find()->orderBy(['id' => SORT_ASC])->all();

$items = [];
if($model_line !=null){
    foreach($model_line as $value){
        $item = [
            'stock_no' => \backend\models\Product::findCode($value->product_id),
            'description' => $value->product_name,
            'product_description'=>$value->product_description,
            'qty' => $value->qty,
            'unit' => $value->unit_id,
            'estimated_price' => $value->line_price,
            'budget' => $value->line_price,
        ];
        $items[] = $item;
    }
}

$reasons = \common\models\PurchReqReasonTitle::find()->all();
$departments = \common\models\Department::find()->all();

// Approvers
$requestorDate = date('m/d/Y');

function isCheckedPdf($purch_req_id, $key, $value) {
    $model = \common\models\PurchReqFoot::find()->where(['purch_req_id' => $purch_req_id,'footer_id' => $key])->one();
    if($model) {
        return $model->is_enable == $value;
    }
    return false;
}

// Image paths for mPDF
$logoPath = Yii::getAlias('@webroot') . '/uploads/logo/mco_logo_2.png';
?>

<style>
    body {
        font-family: 'sarabun', 'garuda', sans-serif;
        font-size: 14px;
        line-height: 1.3;
    }
    .layout-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
        margin-bottom: 10px;
    }
    .layout-table td {
        border: none;
        vertical-align: top;
        padding: 2px;
    }
    .form-section {
        border: 1px solid #000;
        padding: 10px;
        margin-bottom: 10px;
    }
    .form-label {
        font-weight: bold;
        margin-right: 5px;
    }
    .form-value {
        border-bottom: 1px dotted #999;
        display: inline-block;
        min-width: 100px;
    }
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    .items-table th, .items-table td {
        border: 1px solid #000;
        padding: 5px;
        text-align: center;
        font-size: 12px;
    }
    .items-table th {
        background-color: #f0f0f0;
    }
    .items-table .description {
        text-align: left;
    }
    .items-table .number {
        text-align: right;
    }
    .checkbox-item {
        display: inline-block;
        margin-right: 10px;
    }
    /* Checkbox simulation for PDF */
    .checkbox-box {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 1px solid #000;
        margin-right: 3px;
        position: relative;
        top: 2px;
        text-align: center;
        line-height: 11px;
        font-size: 10px;
        font-weight: bold;
    }
    .signature-line {
        border-bottom: 1px solid #000;
        margin-top: 5px;
        margin-bottom: 5px;
        height: 1px;
    }
</style>

<!-- Header -->
<table class="layout-table">
    <tr>
        <td width="60%">
            <img src="<?= $logoPath ?>" style="max-width: 150px;">
            <h3 style="margin: 5px 0;">M.C.O. COMPANY LIMITED</h3>
            <p style="margin: 0; font-size: 12px;">
                8/18 Koh-Kloy Road.<br>
                Tambon Cherngnern, Amphur Muang ,<br>
                Rayong 21000 Thailand.<br>
                <span style="text-decoration: underline;">เลขประจำตัวผู้เสียภาษี 0215543000985</span>
            </p>
        </td>
        <td width="40%" align="right" style="vertical-align: top;">
            <div style="font-size: 18px; font-weight: bold; color: #003366;">PURCHASE REQUISITION</div>
            <div style="font-size: 18px; font-weight: bold; color: #003366; margin-bottom: 10px;">ใบขอซื้อ / ขอจ้าง</div>
            <p style="margin: 2px 0;"><strong>PR.NO. / เลขที่ :</strong> <?= Html::encode($prNumber) ?></p>
            <p style="margin: 2px 0;"><strong>Date / วันที่ :</strong> <?= Html::encode($date) ?></p>
            <p style="margin: 2px 0;"><strong>วันที่ต้องการใช้งาน :</strong> <?= date('d/m/Y',strtotime($model->required_date)) ?></p>
        </td>
    </tr>
</table>

<!-- Request Info -->
<div class="form-section">
    <table class="layout-table">
        <tr>
            <td width="40%">
                <span class="form-label">ผู้ขอซื้อ :</span>
                <span class="form-value"><?= Html::encode($requestor) ?></span>
            </td>
            <td width="30%">
                <span class="form-label">หน่วยงาน :</span>
                <span class="form-value"><?= Html::encode($department) ?></span>
            </td>
            <td width="30%">
                <span class="form-label">ฝ่าย :</span>
                <span class="form-value"></span>
            </td>
        </tr>
    </table>

    <table class="layout-table">
        <tr>
            <td width="50%">
                <span class="form-label">เหตุผลในการสั่งซื้อ :</span><br>
                <?php foreach ($reasons as $reason) : 
                    $isChecked = $requestType == $reason->id;
                ?>
                    <div class="checkbox-item">
                        <div class="checkbox-box"><?= $isChecked ? '/' : '' ?></div>
                        <?= Html::encode($reason->name) ?>
                    </div>
                <?php endforeach; ?>
                <div style="margin-left: 10px; display: inline-block; border-bottom: 1px dotted #999; min-width: 50px;">
                    <?=$model->reason?>
                </div>
            </td>
            <td width="50%">
                <span class="form-label">ค่าใช้จ่ายในส่วนแผนก :</span><br>
                <?php foreach ($departments as $department) : 
                     $isChecked = $model->req_for_dep_id == $department->id;
                ?>
                    <div class="checkbox-item">
                        <div class="checkbox-box"><?= $isChecked ? '/' : '' ?></div>
                        <?= Html::encode($department->name) ?>
                    </div>
                <?php endforeach; ?>
            </td>
        </tr>
    </table>

    <table class="layout-table">
        <tr>
            <td>
                <span class="form-label">คำอธิบายเพิ่มเติม :</span>
                <span class="form-value" style="width: 80%;">
                    <?= Html::encode($model->note) ?>
                </span>
            </td>
        </tr>
    </table>
</div>

<!-- Items Table -->
<table class="items-table">
    <thead>
    <tr>
        <th style="width: 5%;">ITEM</th>
        <th style="width: 10%;">STOCK NO.</th>
        <th style="width: 40%;">DESCRIPTION</th>
        <th style="width: 5%;">Q'TY</th>
        <th style="width: 8%;">UNIT</th>
        <th style="width: 16%;">TOTAL ESTIMATED<br>PRICE</th>
        <th style="width: 16%;">TOTAL OPEX BUDGETED</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $index => $item): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= Html::encode($item['stock_no']) ?></td>
            <td class="description"><?= Html::encode($item['description']).'<br />'.Html::encode($item['product_description']) ?></td>
            <td><?= $item['qty'] ?></td>
            <td><?= Html::encode(\backend\models\Unit::findName($item['unit'])) ?></td>
            <td class="number"><?= number_format($item['estimated_price'], 2) ?></td>
            <td class="number"><?= number_format($item['qty'] * $item['estimated_price'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php for ($i =0; $i <= 10 - count($items); $i++): ?>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>

<!-- Footer / Document Section -->
<div style="margin-bottom: 20px;">
    <table class="layout-table">
        <tr>
            <td width="60%">
                <?php foreach ($model_footer as $key => $item): ?>
                    <div style="margin-bottom: 5px;">
                        <span style="font-weight: bold;"><?= htmlspecialchars($item->name) ?></span><br>
                        
                        <?php 
                        $isYes = isCheckedPdf($model->id, $item->id, '1');
                        $isNo = isCheckedPdf($model->id, $item->id, '0');
                        ?>
                        
                        <div class="checkbox-box"><?= $isYes ? '/' : '' ?></div> ใช่
                        &nbsp;&nbsp;
                        <div class="checkbox-box"><?= $isNo ? '/' : '' ?></div> ไม่ใช่
                    </div>
                <?php endforeach; ?>
            </td>
        </tr>
    </table>
</div>

<!-- Signature Section -->
<table class="layout-table">
    <tr>
        <td width="45%" align="center">
            <strong>ผู้ขอซื้อ</strong>
            <div style="height: 60px; position: relative;">
                 <?php
                $requestor_signature = \backend\models\User::findEmployeeSignature($model->created_by);
                if(!empty($requestor_signature)): 
                    $sigPath = Yii::getAlias('@webroot') . '/uploads/employee_signature/' . $requestor_signature;
                    if (file_exists($sigPath)) {
                        echo '<img src="'.$sigPath.'" style="max-height: 50px; margin-top: 10px;">';
                    }
                endif; ?>
            </div>
            <div class="signature-line"></div>
            <div>วันที่ <?= Html::encode($requestorDate) ?></div>
        </td>
        <td width="10%"></td>
        <td width="45%" align="center">
            <strong>ผู้อนุมัติ</strong>
            <div style="height: 60px; position: relative;">
                <?php
                $approve_signature = \backend\models\User::findEmployeeSignature($model->approve_by);
                if(!empty($approve_signature)): 
                    $sigPath = Yii::getAlias('@webroot') . '/uploads/employee_signature/' . $approve_signature;
                    if (file_exists($sigPath)) {
                        echo '<img src="'.$sigPath.'" style="max-height: 50px; margin-top: 10px;">';
                    }
                endif; ?>
            </div>
            <div class="signature-line"></div>
            <div>วันที่ <?=!empty($model->approve_date) ? date('m/d/Y',strtotime($model->approve_date)) : ''?></div>
        </td>
    </tr>
</table>

<div class="form-number" style="text-align: right; font-size: 12px; margin-top: 20px;">
    F-WP-PMA-002-001 R.3
</div>
