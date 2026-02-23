<?php

namespace backend\controllers;

use Yii;
use backend\models\PaymentVoucher;
use backend\models\PaymentVoucherSearch;
use backend\models\PaymentVoucherLine;
use backend\models\PurchReq;
use backend\models\Purch;
use backend\models\PaymentVoucherDoc;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

/**
 * PaymentVoucherController implements the CRUD actions for PaymentVoucher model.
 */
class PaymentvoucherController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all PaymentVoucher models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PaymentVoucherSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PaymentVoucher model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new PaymentVoucher model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PaymentVoucher();
        $model->trans_date = date('Y-m-d');
        $model->status = PaymentVoucher::STATUS_DRAFT;

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // บันทึก Lines
                    $this->saveVoucherLines($model);
                    
                    // บันทึก Refs (PR/PO ที่เลือก)
                    $this->saveVoucherRefs($model);
                    
                    // อัปโหลดไฟล์แนบ
                    $this->uploadAttachments($model);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'บันทึก Payment Voucher สำเร็จ');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PaymentVoucher model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // บันทึก Lines
                    $this->saveVoucherLines($model);
                    
                    // บันทึก Refs (PR/PO ที่เลือก)
                    $this->saveVoucherRefs($model);
                    
                    // อัปโหลดไฟล์แนบ
                    $this->uploadAttachments($model);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'อัปเดต Payment Voucher สำเร็จ');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PaymentVoucher model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Pull PR data
     */
    public function actionPullPr($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $pr = PurchReq::findOne($id);
        if ($pr) {
            $lines = [];
            foreach ($pr->getPurchReqLines()->all() as $line) {
                $lines[] = [
                    'account_code' => '',
                    'bill_code' => $pr->purch_req_no,
                    'description' => $line->product_name . ($line->product_description ? ' (' . $line->product_description . ')' : ''),
                    'debit' => $line->line_total,
                    'credit' => 0,
                ];
            }
            return [
                'success' => true,
                'recipient_name' => $pr->vendor_name,
                'amount' => $pr->net_amount,
                'paid_for' => $pr->purch_req_no,
                'lines' => $lines,
            ];
        }
        return ['success' => false];
    }

    /**
     * Pull PO data
     */
    public function actionPullPo($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $po = Purch::findOne($id);
        if ($po) {
            $lines = [];
            foreach ($po->getPurchLines()->all() as $line) {
                $lines[] = [
                    'account_code' => '',
                    'bill_code' => $po->purch_no,
                    'description' => $line->product_name . ($line->product_description ? ' (' . $line->product_description . ')' : ''),
                    'debit' => $line->line_total,
                    'credit' => 0,
                ];
            }
            return [
                'success' => true,
                'recipient_name' => $po->vendor_name,
                'amount' => $po->net_amount,
                'paid_for' => $po->purch_no,
                'lines' => $lines,
            ];
        }
        return ['success' => false];
    }

    /**
     * ดึงรายการ PR ตาม Vendor (ที่ยังไม่จ่ายครบ)
     */
    public function actionGetPrByVendor($vendor_id = null, $q = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $query = PurchReq::find()
            ->where(['approve_status' => 1])
            ->andWhere(['>', 'net_amount', 0]);
            
        if ($vendor_id && $vendor_id !== 'null' && $vendor_id !== '') {
            $query->andWhere(['vendor_id' => $vendor_id]);
        }
        
        if ($q) {
            $query->andWhere(['like', 'purch_req_no', $q]);
        }
        
        $prs = $query->limit(20)->all();
        
        $result = [];
        foreach ($prs as $pr) {
            // คำนวณยอดที่จ่ายไปแล้ว
            $paidAmount = \backend\models\PaymentVoucherRef::find()
                ->where(['ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PR, 'ref_id' => $pr->id])
                ->sum('amount') ?: 0;
            
            $remaining = $pr->net_amount - $paidAmount;
            
            // แสดงเฉพาะที่ยังมียอดคงเหลือ
            if ($remaining > 0) {
                $result[] = [
                    'id' => $pr->id,
                    'text' => $pr->purch_req_no . ' (คงเหลือ: ' . number_format($remaining, 2) . ( !empty($pr->vendor_name) ? ' - ' . $pr->vendor_name : '' ) . ')',
                    'total_amount' => $pr->net_amount,
                    'paid_amount' => $paidAmount,
                    'remaining' => $remaining,
                ];
            }
        }
        
        return ['results' => $result];
    }

    /**
     * ดึงรายการ PO ตาม Vendor (ที่ยังไม่จ่ายครบ)
     */
    public function actionGetPoByVendor($vendor_id = null, $q = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $query = Purch::find()
            ->where(['approve_status' => 1])
            ->andWhere(['>', 'net_amount', 0]);
            
        if ($vendor_id && $vendor_id !== 'null' && $vendor_id !== '') {
            $query->andWhere(['vendor_id' => $vendor_id]);
        }
        
        if ($q) {
            $query->andWhere(['like', 'purch_no', $q]);
        }
        
        $pos = $query->limit(20)->all();
        
        $result = [];
        foreach ($pos as $po) {
            // คำนวณยอดที่จ่ายไปแล้ว
            $paidAmount = \backend\models\PaymentVoucherRef::find()
                ->where(['ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PO, 'ref_id' => $po->id])
                ->sum('amount') ?: 0;
            
            $remaining = $po->net_amount - $paidAmount;
            
            // แสดงเฉพาะที่ยังมียอดคงเหลือ
            if ($remaining > 0) {
                $result[] = [
                    'id' => $po->id,
                    'text' => $po->purch_no . ' (คงเหลือ: ' . number_format($remaining, 2) . ( !empty($po->vendor_name) ? ' - ' . $po->vendor_name : '' ) . ')',
                    'total_amount' => $po->net_amount,
                    'paid_amount' => $paidAmount,
                    'remaining' => $remaining,
                ];
            }
        }
        
        return ['results' => $result];
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
        $total_before_vat = 0;
        $total_vat = 0;
        $paid_for_items = [];
        $vendor_id = null;
        
        // ดึงข้อมูลจาก PR
        foreach ($pr_ids as $pr_id) {
            $pr = PurchReq::findOne($pr_id);
            if ($pr) {
                $paidAmount = \backend\models\PaymentVoucherRef::find()
                    ->where(['ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PR, 'ref_id' => $pr->id])
                    ->sum('amount') ?: 0;
                
                $remaining = $pr->net_amount - $paidAmount;
                $total_amount += $remaining;
                $paid_for_items[] = 'PR: ' . $pr->purch_req_no;
                if (!$vendor_id) {
                    $vendor_id = $pr->vendor_id;
                    $vendor_name = $pr->vendor_name;
                }
            }
        }
        
        // ดึงข้อมูลจาก PO และคำนวณยอดรวมก่อน VAT และ VAT
        foreach ($po_ids as $po_id) {
            $po = Purch::findOne($po_id);
            if ($po) {
                $paidAmount = \backend\models\PaymentVoucherRef::find()
                    ->where(['ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PO, 'ref_id' => $po->id])
                    ->sum('amount') ?: 0;
                
                $remaining = $po->net_amount - $paidAmount;
                $total_amount += $remaining;
                $paid_for_items[] = 'PO: ' . $po->purch_no;
                if (!$vendor_id) {
                    $vendor_id = $po->vendor_id;
                    $vendor_name = $po->vendor_name;
                }
                
                // คำนวณยอดก่อน VAT และ VAT จาก PO
                // net_amount = total_amount - discount + vat_amount
                // ดังนั้น amount before vat = net_amount - vat_amount
                $po_vat = $po->vat_amount ?: 0;
                $po_before_vat = $remaining - $po_vat;
                
                // สัดส่วนที่จ่าย (กรณีจ่ายบางส่วน)
                if ($po->net_amount > 0) {
                    $ratio = $remaining / $po->net_amount;
                    $po_vat = $po_vat * $ratio;
                    $po_before_vat = $remaining - $po_vat;
                }
                
                $total_before_vat += $po_before_vat;
                $total_vat += $po_vat;
            }
        }
        
        // สร้างแถวบัญชีอัตโนมัติ (เฉพาะเมื่อมี PO)
        if (count($po_ids) > 0) {
            // แถวที่ 1: สินค้า (Debit)
            $lines[] = [
                'account_code' => '1120',
                'bill_code' => '',
                'description1' => 'สินค้า',
                'description2' => '',
                'debit' => round($total_before_vat, 2),
                'credit' => 0,
            ];
            
            // แถวที่ 2: ภาษีมูลค่าเพิ่ม (Debit)
            if ($total_vat > 0) {
                $lines[] = [
                    'account_code' => '2200',
                    'bill_code' => '',
                    'description1' => 'ภาษีมูลค่าเพิ่ม',
                    'description2' => '',
                    'debit' => round($total_vat, 2),
                    'credit' => 0,
                ];
            }
            
            // แถวที่ 3: เจ้าหนี้ (Credit)
            $lines[] = [
                'account_code' => '2017',
                'bill_code' => '',
                'description1' => '',
                'description2' => 'เจ้าหนี้',
                'debit' => 0,
                'credit' => round($total_before_vat + $total_vat, 2),
            ];
        }
        
        return [
            'success' => true,
            'amount' => $total_amount,
            'paid_for' => implode(', ', $paid_for_items),
            'lines' => $lines,
            'pr_ids' => $pr_ids,
            'po_ids' => $po_ids,
            'vendor_id' => $vendor_id ?? null,
            'vendor_name' => $vendor_name ?? null,
        ];
    }

    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $this->layout = false;
        return $this->render('print', [
            'model' => $model,
        ]);
    }

    /**
     * บันทึก Voucher Lines
     */
    private function saveVoucherLines($model)
    {
        // ลบ lines เดิม
        PaymentVoucherLine::deleteAll(['payment_voucher_id' => $model->id]);
        
        $account_codes = Yii::$app->request->post('line_account_code', []);
        $bill_codes = Yii::$app->request->post('line_bill_code', []);
        $descriptions1 = Yii::$app->request->post('line_description1', []);
        $descriptions2 = Yii::$app->request->post('line_description2', []);
        $debits = Yii::$app->request->post('line_debit', []);
        $credits = Yii::$app->request->post('line_credit', []);
        
        // ใช้ description1 เป็น base สำหรับ loop
        foreach ($descriptions1 as $i => $desc1) {
            // ข้ามถ้าทั้ง 2 ช่องว่าง
            if (empty($desc1) && empty($descriptions2[$i] ?? '')) continue;
            
            $line = new PaymentVoucherLine();
            $line->payment_voucher_id = $model->id;
            $line->account_code = $account_codes[$i] ?? '';
            $line->bill_code = $bill_codes[$i] ?? '';
            
            // รวม description 2 ช่องด้วย |||
            $desc2 = $descriptions2[$i] ?? '';
            $line->description = $desc1 . '|||' . $desc2;
            
            $line->debit = $debits[$i] ?? 0;
            $line->credit = $credits[$i] ?? 0;
            $line->save(false);
        }
    }

    /**
     * บันทึก Voucher Refs (PR/PO ที่เลือก)
     */
    private function saveVoucherRefs($model)
    {
        $pr_ids_raw = Yii::$app->request->post('pr_ids', []);
        $po_ids_raw = Yii::$app->request->post('po_ids', []);
        
        // รองรับทั้ง array และ JSON string
        if (is_string($pr_ids_raw)) {
            $pr_ids = json_decode($pr_ids_raw, true) ?: [];
        } else {
            $pr_ids = $pr_ids_raw;
        }
        
        if (is_string($po_ids_raw)) {
            $po_ids = json_decode($po_ids_raw, true) ?: [];
        } else {
            $po_ids = $po_ids_raw;
        }
        
        // Debug log
        Yii::info("PR IDs: " . print_r($pr_ids, true), 'payment_voucher');
        Yii::info("PO IDs: " . print_r($po_ids, true), 'payment_voucher');
        
        // ลบ refs เดิม (กรณี update)
        \backend\models\PaymentVoucherRef::deleteAll(['payment_voucher_id' => $model->id]);
        
        // บันทึก PR refs
        foreach ($pr_ids as $pr_id) {
            $pr = PurchReq::findOne($pr_id);
            if ($pr) {
                $paidAmount = \backend\models\PaymentVoucherRef::find()
                    ->where(['ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PR, 'ref_id' => $pr->id])
                    ->andWhere(['!=', 'payment_voucher_id', $model->id])
                    ->sum('amount') ?: 0;
                
                $remaining = $pr->net_amount - $paidAmount;
                
                $ref = new \backend\models\PaymentVoucherRef();
                $ref->payment_voucher_id = $model->id;
                $ref->ref_type = \backend\models\PaymentVoucherRef::REF_TYPE_PR;
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
                $paidAmount = \backend\models\PaymentVoucherRef::find()
                    ->where(['ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PO, 'ref_id' => $po->id])
                    ->andWhere(['!=', 'payment_voucher_id', $model->id])
                    ->sum('amount') ?: 0;
                
                $remaining = $po->net_amount - $paidAmount;
                
                $ref = new \backend\models\PaymentVoucherRef();
                $ref->payment_voucher_id = $model->id;
                $ref->ref_type = \backend\models\PaymentVoucherRef::REF_TYPE_PO;
                $ref->ref_id = $po->id;
                $ref->ref_no = $po->purch_no;
                $ref->amount = $remaining;
                $ref->created_at = time();
                $ref->save(false);
            }
        }
    }

    /**
     * Finds the PaymentVoucher model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PaymentVoucher the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PaymentVoucher::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * จัดการอัปโหลดไฟล์แนบ
     */
    private function uploadAttachments($model)
    {
        $files = UploadedFile::getInstancesByName('upload_files');
        if ($files) {
            $uploadPath = Yii::getAlias('@backend/web/uploads/payment_voucher/');
            if (!file_exists($uploadPath)) {
                FileHelper::createDirectory($uploadPath, 0777);
            }

            foreach ($files as $file) {
                $newName = time() . '_' . Yii::$app->security->generateRandomString(10) . '.' . $file->extension;
                if ($file->saveAs($uploadPath . $newName)) {
                    $doc = new PaymentVoucherDoc();
                    $doc->payment_voucher_id = $model->id;
                    $doc->file_name = $file->baseName . '.' . $file->extension;
                    $doc->file_path = $newName;
                    $doc->file_size = $file->size;
                    $doc->uploaded_at = time();
                    $doc->uploaded_by = Yii::$app->user->id;
                    $doc->save(false);
                }
            }
        }
    }

    /**
     * ลบไฟล์แนบ (AJAX)
     */
    public function actionRemoveAttachment($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $doc = PaymentVoucherDoc::findOne($id);
        if ($doc && $doc->delete()) {
            return ['success' => true];
        }
        return ['success' => false];
    }
}
