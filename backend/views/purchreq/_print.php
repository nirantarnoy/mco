<?php
/**
 * @var $this yii\web\View
 */

use yii\helpers\Html;

//$emp_info  = \backend\models\Employee::findEmpInfo(\Yii::$app->user->id);
$emp_info  = \backend\models\Employee::findEmpInfo($model->created_by);
//print_r($emp_info);
// Mock data
$prNumber = $model->purch_req_no;
$date = date('d/m/Y',strtotime($model->purch_req_date));
//$requestor = $emp_info != null ? $emp_info['fname'] . ' ' . $emp_info['lname'] : '';
$requestor = \backend\models\User::findEmployeeNameByUserId($model->created_by);
$department = $emp_info != null ? $emp_info['department_name'] : '';
$requestType = $model->reason_title_id; // minimumstock, capex, additional_work, expenses, other
$deliveryLocation = 'warehouse'; // warehouse, service_support, other_location, other


$model_footer = \common\models\PurchReqFootTitle::find()->orderBy(['id' => SORT_ASC])->all();
$model_footer_data = \common\models\PurchReqFoot::find()->where(['purch_req_id' => $model->id])->all();

$items = [];

if($model_line !=null){
    foreach($model_line as $value){
        $item = [
            'stock_no' => \backend\models\Product::findCode($value->product_id),
            'description' => $value->product_name ,//\backend\models\Product::findName(($value->product_id)),
            'product_description'=>$value->product_description,
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
        @page {
            size: A4;
            margin: 0.15in;
        }

        * {
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html, body {
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }

        .print-container {
            width: 100% !important;
            height: auto !important;
            min-height: auto !important;
            margin: 0 !important;
            padding: 8mm !important;
            transform: scale(0.95);
            transform-origin: top left;
            page-break-inside: avoid;
            page-break-before: auto;
            page-break-after: auto;
            position: relative;
            background: white !important;
        }

        .no-print {
            display: none !important;
        }

        /* เพิ่มคุณภาพสำหรับ PDF */
        .items-table {
            border-collapse: collapse !important;
        }

        .items-table,
        .items-table th,
        .items-table td {
            border: 1px solid #000 !important;
            border-collapse: collapse !important;
        }

        .form-section {
            border: 1px solid #000 !important;
        }

        .signature-line {
            border-bottom: 1px solid #000 !important;
        }

        .form-value {
            border-bottom: 1px dotted #999 !important;
        }

        /* รักษาคุณภาพของรูปภาพ */
        img {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }

        /* ปรับขนาดฟอนต์ให้ใหญ่ขึ้นเพื่อเต็มหน้า */
        .print-container {
            font-size: 14px !important;
            line-height: 1.3 !important;
            padding: 8mm !important;
        }

        .company-info h3 {
            font-size: 16px !important;
            margin-bottom: 3px !important;
        }

        .company-info p {
            font-size: 12px !important;
            margin: 1px 0 !important;
            line-height: 1.2 !important;
        }

        .pr-title {
            font-size: 20px !important;
            margin-bottom: 8px !important;
        }

        .form-label {
            font-size: 13px !important;
        }

        .form-value {
            font-size: 13px !important;
            min-width: 180px !important;
        }

        .logo {
            max-width: 140px !important;
            margin-bottom: 8px !important;
        }

        .checkbox-group {
            font-size: 12px !important;
            margin-right: 18px !important;
        }

        /* เพิ่มขนาดส่วนหัว */
        .header-section {
            margin-bottom: 18px !important;
        }

        /* เพิ่มขนาด form sections */
        .form-section {
            padding: 12px !important;
            margin-bottom: 12px !important;
        }

        .form-row {
            margin-bottom: 10px !important;
        }

        /* เพิ่มขนาดตาราง */
        .items-table {
            margin-bottom: 18px !important;
            font-size: 14px !important;
        }

        .items-table th,
        .items-table td {
            padding: 5px !important;
        }

        .items-table td {
            height: 28px !important;
        }

        .items-table th {
            font-size: 12px !important;
        }

        /* เพิ่มขนาด document section */
        .document-section {
            margin-bottom: 18px !important;
        }

        .document-section label {
            font-size: 12px !important;
        }

        /* เพิ่มขนาดส่วนลายเซ็นต์ */
        .signature-section {
            margin-top: 30px !important;
        }

        .signature-line {
            margin: 30px 18px 5px 18px !important;
            min-height: 60px !important;
        }

        .signature-line img {
            max-width: 130px !important;
            max-height: 55px !important;
            object-fit: contain;
        }

        /* เพิ่มขนาด form number */
        .form-number {
            margin-top: 15px !important;
            font-size: 12px !important;
        }
    }

    .print-container {
        width: 210mm;
        margin: 0 auto;
        padding: 15mm;
        background: white;
        font-family: 'Sarabun', Arial, sans-serif;
        font-size: 15px;
        line-height: 1.4;
        color: #000;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .company-info {
        flex: 1;
    }

    .company-info h3 {
        margin: 0 0 5px 0;
        font-size: 17px;
        font-weight: bold;
    }

    .company-info p {
        margin: 2px 0;
        font-size: 14px;
    }

    .pr-info {
        text-align: right;
    }

    .pr-title {
        font-size: 21px;
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
        font-size: 15px;
    }

    .form-value {
        border-bottom: 1px dotted #999;
        display: inline-block;
        min-width: 200px;
        padding: 0 5px;
        font-size: 15px;
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
        margin-bottom: 18px;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #000;
        padding: 6px;
        text-align: center;
    }

    .items-table th {
        background-color: #f0f0f0;
        font-weight: bold;
    }

    .items-table td {
        height: 30px;
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
        margin-bottom: 18px;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        margin-top: 35px;
    }

    .signature-box {
        width: 45%;
        text-align: center;
    }

    .signature-line {
        border-bottom: 1px solid #000;
        margin: 35px 20px 5px 20px;
        min-height: 65px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        position: relative;
    }

    .signature-line img {
        max-width: 150px;
        max-height: 60px;
        object-fit: contain;
        width: auto;
        height: auto;
        position: absolute;
        bottom: 5px;
        left: 50%;
        transform: translateX(-50%);
    }

    .logo {
        max-width: 160px;
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
                <td class="description"><?= Html::encode($item['description']).'<br />'.Html::encode($item['product_description']) ?></td>
                <td><?= $item['qty'] ?></td>
                <td><?= Html::encode(\backend\models\Unit::findName($item['unit'])) ?></td>
                <td class="number"><?= number_format($item['estimated_price'], 2) ?></td>
                <td class="number"><?= number_format($item['qty'] * $item['estimated_price'], 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <!-- Empty rows -->
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
                if(!empty($requestor_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?=$requestor_signature?>" alt="Requestor Signature">
                <?php endif; ?>
            </div>
            <div>วันที่ <?= Html::encode($requestorDate) ?></div>
        </div>
        <div class="signature-box">
            <div><strong>ผู้อนุมัติ</strong></div>
            <div class="signature-line">
                <?php
                $approve_signature = \backend\models\User::findEmployeeSignature($model->approve_by);
                if(!empty($approve_signature)): ?>
                    <img src="../../backend/web/uploads/employee_signature/<?=$approve_signature?>" alt="Approver Signature">
                <?php endif; ?>
            </div>
            <div>วันที่ <?=!empty($model->approve_date) ? date('m/d/Y',strtotime($model->approve_date)) : ''?></div>
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
<!--    <a href="--><?php //= \yii\helpers\Url::to(['print', 'id' => $model->id, 'format' => 'pdf']) ?><!--"-->
<!--       class="btn btn-danger" target="_blank" style="font-size: 20px;font-weight: bold;">-->
<!--        <i class="glyphicon glyphicon-file"></i> Download PDF-->
<!--    </a>-->
    <button onclick="printToPDF()" class="btn btn-success" style="font-size: 20px;font-weight: bold;">
        <i class="glyphicon glyphicon-download-alt"></i> Print to PDF
    </button>
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
    // ฟังก์ชันสำหรับ Print to PDF
    function printToPDF() {
        // // แสดงคำแนะนำสำหรับการตั้งค่า PDF
        // alert('เคล็ดลับสำหรับ PDF คุณภาพสูง:\n\n' +
        //     '1. กด Ctrl+P (Windows) หรือ Cmd+P (Mac)\n' +
        //     '2. เลือก "Save as PDF" หรือ "Microsoft Print to PDF"\n' +
        //     '3. ใน More settings:\n' +
        //     '   - Paper size: A4\n' +
        //     '   - Margins: Minimum\n' +
        //     '   - Scale: Custom 100%\n' +
        //     '   - Options: ✓ Background graphics\n' +
        //     '4. กด Save');

        // เปิด print dialog
        setTimeout(() => {
            window.print();
        }, 100);
    }

    // Auto print when page loads (optional)
    // window.onload = function() {
    //     window.print();
    // };

    // เพิ่มการตั้งค่าสำหรับ PDF generation
    window.addEventListener('beforeprint', function() {
        // เพิ่มคลาสพิเศษก่อน print
        document.body.classList.add('printing');
    });

    window.addEventListener('afterprint', function() {
        // ลบคลาสหลัง print
        document.body.classList.remove('printing');
    });
</script>