<?php
/**
 * @var $this yii\web\View
 */

use yii\helpers\Html;

$emp_info  = \backend\models\Employee::findEmpInfo(\Yii::$app->user->id);
//print_r($emp_info);
// Mock data
$prNumber = $model->purch_req_no;
$date = date('d/m/Y',strtotime($model->purch_req_date));
$requestor = $emp_info != null ? $emp_info['fname'] . ' ' . $emp_info['lname'] : '';
$department = $emp_info != null ? $emp_info['department_name'] : '';
$requestType = $model->reason_title_id; // minimumstock, capex, additional_work, expenses, other
$deliveryLocation = 'warehouse'; // warehouse, service_support, other_location, other


$model_footer = \common\models\PurchReqFootTitle::find()->orderBy(['id' => SORT_ASC])->all();
$model_footer_data = \common\models\PurchReqFoot::find()->where(['purch_req_id' => $model->id])->all();

//$items = [
//    [
//        'stock_no' => 'ST-001',
//        'description' => 'กระดาษ A4 80 แกรม',
//        'qty' => 10,
//        'unit' => 'รีม',
//        'estimated_price' => 1200.00,
//        'budget' => 1500.00
//    ],
//    [
//        'stock_no' => 'ST-002',
//        'description' => 'หมึกพิมพ์ Canon Black',
//        'qty' => 5,
//        'unit' => 'ขวด',
//        'estimated_price' => 2500.00,
//        'budget' => 3000.00
//    ],
//    [
//        'stock_no' => 'ST-003',
//        'description' => 'แฟ้มเอกสาร 2 นิ้ว',
//        'qty' => 20,
//        'unit' => 'แฟ้ม',
//        'estimated_price' => 2000.00,
//        'budget' => 2500.00
//    ],
//];

$items = [];

if($model_line !=null){
    foreach($model_line as $value){
        $item = [
            'stock_no' => $value->product->code,
            'description' => $value->product->name,
            'qty' => $value->qty,
            'unit' => $value->unit_id,
            'estimated_price' => $value->line_price,
            'budget' => $value->line_price,

        ];
        $items[] = $item;
    }
}

// Document options
$hasQuotation = true;
$hasSpecification = false;
$hasCertificate = false;

$reasons = \common\models\PurchReqReasonTitle::find()->all();
$departments = \common\models\Department::find()->all();

// Approvers
$requestorName = 'นายสมชาย ใจดี';
$requestorDate = date('m/d/Y');
$approverName = 'นายอนุมัติ ผู้จัดการ';
$approverDate = '';

?>

<style>
    @media print {
        body {
            margin: 0;
            padding: 0;
        }
        .print-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0;
            padding: 10mm;
        }
    }

    .print-container {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        padding: 10mm;
        background: white;
        font-family: 'Sarabun', Arial, sans-serif;
        font-size: 14px;
        line-height: 1.4;
        color: #000;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .company-info {
        flex: 1;
    }

    .company-info h3 {
        margin: 0 0 5px 0;
        font-size: 16px;
        font-weight: bold;
    }

    .company-info p {
        margin: 2px 0;
        font-size: 13px;
    }

    .pr-info {
        text-align: right;
    }

    .pr-title {
        font-size: 20px;
        font-weight: bold;
        color: #003366;
        margin-bottom: 10px;
    }

    .form-section {
        border: 1px solid #000;
        padding: 10px;
        margin-bottom: 10px;
    }

    .form-row {
        display: flex;
        margin-bottom: 10px;
    }

    .form-group {
        flex: 1;
        margin-right: 20px;
    }

    .form-group:last-child {
        margin-right: 0;
    }

    .form-label {
        font-weight: bold;
        margin-right: 10px;
    }

    .form-value {
        border-bottom: 1px dotted #999;
        display: inline-block;
        min-width: 200px;
        padding: 0 5px;
    }

    .checkbox-group {
        display: inline-block;
        margin-right: 20px;
    }

    .checkbox-group input[type="checkbox"] {
        margin-right: 5px;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #000;
        padding: 5px;
        text-align: center;
    }

    .items-table th {
        background-color: #f0f0f0;
        font-weight: bold;
    }

    .items-table td {
        height: 25px;
    }

    .items-table .description {
        text-align: left;
        padding-left: 10px;
    }

    .items-table .number {
        text-align: right;
        padding-right: 10px;
    }

    .document-section {
        margin-bottom: 20px;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        margin-top: 40px;
    }

    .signature-box {
        width: 45%;
        text-align: center;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        margin: 40px 20px 5px 20px;
    }

    .logo {
        max-width: 150px;
        margin-bottom: 10px;
    }

    .form-number {
        text-align: right;
        font-size: 12px;
        margin-top: 20px;
    }
</style>

<div class="print-container">
    <!-- Header Section -->
    <div class="header-section">
        <div class="company-info">
            <?= Html::img('../../backend/web/uploads/logo/mco_logo_2.png',['style' => 'max-width: 150px;']) ?>
            <h3>Company Name :</h3>
            <h3>M.C.O. COMPANY LIMITED</h3>
            <p>8/18 Koh-Kloy Road.</p>
            <p>Tambon Cherngnern,</p>
            <p>Amphur Muang ,</p>
            <p>Rayong 21000 Thailand.</p>
            <p style="text-decoration: underline;">เลขประจำตัวผู้เสียภาษี 0215543000985</p>
        </div>
        <div class="pr-info">
            <div class="pr-title">PURCHASE REQUISITION</div>
            <div class="pr-title">ใบขอซื้อ / ขอจ้าง</div>
            <p><strong>PR.NO. / เลขที่ :</strong> <?= Html::encode($prNumber) ?></p>
            <p><strong>Date / วันที่ :</strong> <?= Html::encode($date) ?></p>
            <p><strong>วันที่ต้องการใช้งาน :</strong> _____________________</p>
        </div>
    </div>

    <!-- Request Info Section -->
    <div class="form-section">
        <div class="form-row">
            <div class="form-group">
                <span class="form-label">ผู้ขอซื้อ :</span>
                <span class="form-value"><?= Html::encode($requestor) ?></span>
            </div>
            <div class="form-group">
                <span class="form-label">หน่วยงาน :</span>
                <span class="form-value"><?= Html::encode($department) ?></span>
            </div>
            <div class="form-group">
                <span class="form-label">ฝ่าย :</span>
                <span class="form-value"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <span class="form-label">เหตุผลในการสั่งซื้อ :</span>
                <div style="margin-top: 5px;">
                    <?php foreach ($reasons as $reason) :?>

                    <div class="checkbox-group">
                        <input type="checkbox" <?= $requestType == $reason->id ? 'checked' : '' ?> onclick="return false">
                        <label><?= Html::encode($reason->name) ?></label>
                    </div>
                    <?php endforeach; ?>
                  <span><?=$model->reason?></span>
                </div>
            </div>
            <div class="form-group">
                <span class="form-label">ค่าใช้จ่ายในส่วนแผนก :</span>
                <div style="margin-top: 5px;">
                    <?php foreach ($departments as $department) :?>

                        <div class="checkbox-group">
                            <input type="checkbox" <?= $model->req_for_dep_id == $department->id ? 'checked' : '' ?> onclick="return false">
                            <label><?= Html::encode($department->name) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="form-row">
            <span class="form-label">คำอธิบายเพิ่มเติม :</span>
            <span class="form-value" style="min-width: 90%;">
                <?= Html::encode($model->note) ?>
            </span>
        </div>
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
                <td class="description"><?= Html::encode($item['description']) ?></td>
                <td><?= $item['qty'] ?></td>
                <td><?= Html::encode(\backend\models\Unit::findName($item['unit'])) ?></td>
                <td class="number"><?= number_format($item['estimated_price'], 2) ?></td>
                <td class="number"><?= number_format($item['budget'], 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <!-- Empty rows -->
        <?php for ($i = count($items); $i < 15; $i++): ?>
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

    <!-- Document Section -->
    <div class="document-section">
        <div class="form-row">
            <div style="width: 50%;">
                <?php foreach ($model_footer as $key => $item): ?>
                    <div class="form-group mb-2">
                        <label><?= htmlspecialchars($item->name) ?></label><br>
                        <input type="checkbox"
                               name="answers[<?= $key ?>]"
                               value="1"
                            <?php echo isChecked($model->id,$item->id, '1') ?>
                               onclick="return false">
                        ใช่
                        <input type="checkbox"
                               name="answers[<?= $key ?>]"
                               value="0"
                            <?php echo isChecked($model->id,$item->id, '0') ?>
                               onclick="return false">
                        ไม่ใช่
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div><strong>ผู้ขอซื้อ</strong></div>
            <div class="signature-line">
                <?php
                $requestor_signature = \backend\models\User::findEmployeeSignature($model->created_by);
                ?>
                <img src="../../backend/web/uploads/employee_signature/<?=$requestor_signature?>" width="90%" alt="">
            </div>
            <div>วันที่ <?= Html::encode($requestorDate) ?></div>
        </div>
        <div class="signature-box">
            <div><strong>ผู้อนุมัติ</strong></div>
            <div class="signature-line">
                <?php
                  $approve_signature = \backend\models\User::findEmployeeSignature($model->approve_by);
                  ?>
                <img src="../../backend/web/uploads/employee_signature/<?=$approve_signature?>" width="90%" alt="">
            </div>
            <div>วันที่ <?=date('m/d/Y',strtotime($model->approve_date))?></div>
        </div>
    </div>

    <!-- Form Number -->
    <div class="form-number">
        F-WP-PMA-002-001 R.3
    </div>
</div>

<div class="no-print" style="text-align: center; margin: 20px;">
    <button onclick="window.print()" class="btn btn-primary" style="font-size: 20px;font-weight: bold;">
        <i class="glyphicon glyphicon-print"></i> Print
    </button>
    <a href="<?= \yii\helpers\Url::to(['print', 'id' => $model->id, 'format' => 'pdf']) ?>"
       class="btn btn-danger" target="_blank" style="font-size: 20px;font-weight: bold;">
        <i class="glyphicon glyphicon-file"></i> Download PDF
    </a>
    <button onclick="window.close()" class="btn btn-default" style="font-size: 20px;font-weight: bold;">Close</button>
</div>
<?php
function isChecked($purch_req_id, $key, $value) {
    $model = \common\models\PurchReqFoot::find()->where(['purch_req_id' => $purch_req_id,'footer_id' => $key])->one();
    if($model) {
        return $model->is_enable == $value ? 'checked' : '';
    }else{
        return '';
    }
}

?>
<script>
    // Auto print when page loads (optional)
    // window.onload = function() {
    //     window.print();
    // };
</script>