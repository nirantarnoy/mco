# Payment Voucher Enhancement - Implementation Plan

## สรุปการปรับปรุง Payment Voucher

### 1. ✅ สร้างตารางและ Model ใหม่
- สร้างตาราง `payment_voucher_ref` สำหรับเชื่อมโยง PV กับ PR/PO หลายรายการ
- สร้าง Model `PaymentVoucherRef`
- เพิ่มฟิลด์ `vendor_id` ในตาราง `payment_voucher`
- อัปเดต Model `PaymentVoucher` เพิ่ม relation และ vendor_id

### 2. 🔄 ต้องดำเนินการต่อ - อัปเดต Controller

#### ไฟล์: `backend/controllers/PaymentvoucherController.php`

**เพิ่ม Action ใหม่:**
```php
/**
 * ดึงรายการ PR ตาม Vendor
 */
public function actionGetPrByVendor($vendor_id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    $prs = PurchReq::find()
        ->where(['vendor_id' => $vendor_id, 'approve_status' => 1])
        ->andWhere(['>', 'total_amount', 0])
        ->all();
    
    $result = [];
    foreach ($prs as $pr) {
        // คำนวณยอดที่จ่ายไปแล้ว
        $paidAmount = PaymentVoucherRef::find()
            ->where(['ref_type' => PaymentVoucherRef::REF_TYPE_PR, 'ref_id' => $pr->id])
            ->sum('amount') ?: 0;
        
        $remaining = $pr->total_amount - $paidAmount;
        
        // แสดงเฉพาะที่ยังมียอดคงเหลือ
        if ($remaining > 0) {
            $result[] = [
                'id' => $pr->id,
                'text' => $pr->purch_req_no . ' (คงเหลือ: ' . number_format($remaining, 2) . ')',
                'total_amount' => $pr->total_amount,
                'paid_amount' => $paidAmount,
                'remaining' => $remaining,
            ];
        }
    }
    
    return $result;
}

/**
 * ดึงรายการ PO ตาม Vendor
 */
public function actionGetPoByVendor($vendor_id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    $pos = Purch::find()
        ->where(['vendor_id' => $vendor_id, 'approve_status' => 1])
        ->andWhere(['>', 'total_amount', 0])
        ->all();
    
    $result = [];
    foreach ($pos as $po) {
        // คำนวณยอดที่จ่ายไปแล้ว
        $paidAmount = PaymentVoucherRef::find()
            ->where(['ref_type' => PaymentVoucherRef::REF_TYPE_PO, 'ref_id' => $po->id])
            ->sum('amount') ?: 0;
        
        $remaining = $po->total_amount - $paidAmount;
        
        // แสดงเฉพาะที่ยังมียอดคงเหลือ
        if ($remaining > 0) {
            $result[] = [
                'id' => $po->id,
                'text' => $po->purch_no . ' (คงเหลือ: ' . number_format($remaining, 2) . ')',
                'total_amount' => $po->total_amount,
                'paid_amount' => $paidAmount,
                'remaining' => $remaining,
            ];
        }
    }
    
    return $result;
}

/**
 * ดึงข้อมูลจาก PR/PO หลายรายการ
 */
public function actionPullMultiple()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    $pr_ids = Yii::$app->request->post('pr_ids', []);
    $po_ids = Yii::$app->request->post('po_ids', []);
    
    $lines = [];
    $total_amount = 0;
    $paid_for_items = [];
    
    // ดึงข้อมูลจาก PR
    foreach ($pr_ids as $pr_id) {
        $pr = PurchReq::findOne($pr_id);
        if ($pr) {
            $paidAmount = PaymentVoucherRef::find()
                ->where(['ref_type' => PaymentVoucherRef::REF_TYPE_PR, 'ref_id' => $pr->id])
                ->sum('amount') ?: 0;
            
            $remaining = $pr->total_amount - $paidAmount;
            $total_amount += $remaining;
            $paid_for_items[] = 'PR: ' . $pr->purch_req_no;
            
            // เพิ่ม lines จาก PR
            foreach ($pr->purchReqLines as $line) {
                $lines[] = [
                    'account_code' => '',
                    'bill_code' => '',
                    'description' => $line->description ?? 'PR: ' . $pr->purch_req_no,
                    'debit' => $remaining,
                    'credit' => 0,
                ];
            }
        }
    }
    
    // ดึงข้อมูลจาก PO
    foreach ($po_ids as $po_id) {
        $po = Purch::findOne($po_id);
        if ($po) {
            $paidAmount = PaymentVoucherRef::find()
                ->where(['ref_type' => PaymentVoucherRef::REF_TYPE_PO, 'ref_id' => $po->id])
                ->sum('amount') ?: 0;
            
            $remaining = $po->total_amount - $paidAmount;
            $total_amount += $remaining;
            $paid_for_items[] = 'PO: ' . $po->purch_no;
            
            // เพิ่ม lines จาก PO
            foreach ($po->purchLines as $line) {
                $lines[] = [
                    'account_code' => '',
                    'bill_code' => '',
                    'description' => $line->description ?? 'PO: ' . $po->purch_no,
                    'debit' => $remaining,
                    'credit' => 0,
                ];
            }
        }
    }
    
    return [
        'success' => true,
        'amount' => $total_amount,
        'paid_for' => implode(', ', $paid_for_items),
        'lines' => $lines,
        'pr_ids' => $pr_ids,
        'po_ids' => $po_ids,
    ];
}
```

**อัปเดต actionCreate และ actionUpdate:**
```php
public function actionCreate()
{
    $model = new PaymentVoucher();
    $model->status = PaymentVoucher::STATUS_ACTIVE;
    $model->trans_date = date('Y-m-d');

    if ($this->request->isPost) {
        if ($model->load($this->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // บันทึก Lines
                    $this->saveVoucherLines($model);
                    
                    // บันทึก Refs (PR/PO ที่เลือก)
                    $this->saveVoucherRefs($model);
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'บันทึก Payment Voucher สำเร็จ');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}

private function saveVoucherRefs($model)
{
    $pr_ids = Yii::$app->request->post('pr_ids', []);
    $po_ids = Yii::$app->request->post('po_ids', []);
    
    // ลบ refs เดิม (กรณี update)
    PaymentVoucherRef::deleteAll(['payment_voucher_id' => $model->id]);
    
    // บันทึก PR refs
    foreach ($pr_ids as $pr_id) {
        $pr = PurchReq::findOne($pr_id);
        if ($pr) {
            $paidAmount = PaymentVoucherRef::find()
                ->where(['ref_type' => PaymentVoucherRef::REF_TYPE_PR, 'ref_id' => $pr->id])
                ->andWhere(['!=', 'payment_voucher_id', $model->id])
                ->sum('amount') ?: 0;
            
            $remaining = $pr->total_amount - $paidAmount;
            
            $ref = new PaymentVoucherRef();
            $ref->payment_voucher_id = $model->id;
            $ref->ref_type = PaymentVoucherRef::REF_TYPE_PR;
            $ref->ref_id = $pr->id;
            $ref->ref_no = $pr->purch_req_no;
            $ref->amount = $remaining;
            $ref->created_at = time();
            $ref->save(false);
        }
    }
    
    // บันทึก PO refs
    foreach ($po_ids as $po_id) {
        $po = Purch::findOne($po_id);
        if ($po) {
            $paidAmount = PaymentVoucherRef::find()
                ->where(['ref_type' => PaymentVoucherRef::REF_TYPE_PO, 'ref_id' => $po->id])
                ->andWhere(['!=', 'payment_voucher_id', $model->id])
                ->sum('amount') ?: 0;
            
            $remaining = $po->total_amount - $paidAmount;
            
            $ref = new PaymentVoucherRef();
            $ref->payment_voucher_id = $model->id;
            $ref->ref_type = PaymentVoucherRef::REF_TYPE_PO;
            $ref->ref_id = $po->id;
            $ref->ref_no = $po->purch_no;
            $ref->amount = $remaining;
            $ref->created_at = time();
            $ref->save(false);
        }
    }
}

private function saveVoucherLines($model)
{
    // ลบ lines เดิม
    PaymentVoucherLine::deleteAll(['payment_voucher_id' => $model->id]);
    
    $account_codes = Yii::$app->request->post('line_account_code', []);
    $bill_codes = Yii::$app->request->post('line_bill_code', []);
    $descriptions = Yii::$app->request->post('line_description', []);
    $debits = Yii::$app->request->post('line_debit', []);
    $credits = Yii::$app->request->post('line_credit', []);
    
    foreach ($descriptions as $i => $description) {
        if (empty($description)) continue;
        
        $line = new PaymentVoucherLine();
        $line->payment_voucher_id = $model->id;
        $line->account_code = $account_codes[$i] ?? '';
        $line->bill_code = $bill_codes[$i] ?? '';
        $line->description = $description;
        $line->debit = $debits[$i] ?? 0;
        $line->credit = $credits[$i] ?? 0;
        $line->save(false);
    }
}
```

### 3. 🔄 ต้องดำเนินการต่อ - อัปเดต View

#### ไฟล์: `backend/views/paymentvoucher/_form.php`

**เปลี่ยนจาก:**
- ช่อง PR/PO แบบเลือกรายการเดียว

**เป็น:**
- ช่องเลือก Vendor
- ช่อง PR/PO แบบ multiple select ที่กรองตาม Vendor
- แสดงยอดคงเหลือของแต่ละรายการ

**ดูไฟล์ตัวอย่างที่ต้องแก้ไข:**
- บรรทัด 109-133: เปลี่ยนเป็นระบบใหม่

### 4. 🔄 ต้องดำเนินการต่อ - แสดง PV ใน PR/PO View

**ไฟล์ที่ต้องแก้:**
- `backend/views/purch-req/view.php`
- `backend/views/purch/view.php`

**เพิ่มส่วนแสดง Payment Vouchers ที่เกี่ยวข้อง:**
```php
<?php
$pvRefs = \backend\models\PaymentVoucherRef::find()
    ->where(['ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PO, 'ref_id' => $model->id])
    ->all();

if (!empty($pvRefs)):
?>
<div class="card mt-3">
    <div class="card-header">
        <h5>Payment Vouchers ที่เกี่ยวข้อง</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>PV No.</th>
                    <th>วันที่</th>
                    <th>จำนวนเงิน</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pvRefs as $ref): ?>
                    <tr>
                        <td><?= Html::a($ref->paymentVoucher->voucher_no, ['paymentvoucher/view', 'id' => $ref->payment_voucher_id]) ?></td>
                        <td><?= Yii::$app->formatter->asDate($ref->paymentVoucher->trans_date) ?></td>
                        <td class="text-end"><?= Yii::$app->formatter->asDecimal($ref->amount, 2) ?></td>
                        <td>
                            <?= Html::a('ดูรายละเอียด', ['paymentvoucher/view', 'id' => $ref->payment_voucher_id], ['class' => 'btn btn-sm btn-info']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
```

## ขั้นตอนการทำงานต่อ

1. **รัน Migration:**
   ```bash
   php yii migrate
   ```

2. **อัปเดต Controller** ตามโค้ดด้านบน

3. **อัปเดต View** ให้รองรับการเลือก Vendor และ PR/PO หลายรายการ

4. **ทดสอบระบบ:**
   - สร้าง PV ใหม่โดยเลือก Vendor
   - เลือก PR/PO หลายรายการ
   - ตรวจสอบว่า PO ที่จ่ายครบแล้วไม่แสดงในตัวเลือก
   - ตรวจสอบว่าเลขที่ PV แสดงใน PR/PO view

## หมายเหตุ
- ระบบจะคำนวณยอดคงเหลือโดยอัตโนมัติ
- PO/PR ที่จ่ายครบแล้วจะไม่แสดงในตัวเลือก
- สามารถเลือก PR และ PO พร้อมกันได้
