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
        $model->status = PaymentVoucher::STATUS_ACTIVE;

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

    private function getPaidAmount($refType, $refId, $excludeVoucherId = null)
    {
        $query = \backend\models\PaymentVoucherRef::find()
            ->alias('ref')
            ->innerJoin('payment_voucher pv', 'ref.payment_voucher_id = pv.id')
            ->where(['ref.ref_type' => $refType, 'ref.ref_id' => $refId])
            ->andWhere(['!=', 'pv.status', \backend\models\PaymentVoucher::STATUS_CANCELLED]);
            
        if ($excludeVoucherId) {
            $query->andWhere(['!=', 'ref.payment_voucher_id', $excludeVoucherId]);
        }
        
        return $query->sum('ref.amount') ?: 0;
    }

    /**
     * ดึงรายการ Pre-Advance ตาม Vendor (ที่ยังไม่จ่ายครบ)
     */
    public function actionGetPreAdvanceByVendor($vendor_id = null, $q = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $query = \backend\models\PreAdvance::find()
            ->where(['!=', 'status', \backend\models\PreAdvance::STATUS_CANCELLED])
            ->andWhere(['>', 'amount', 0]);
            
        if ($vendor_id && $vendor_id !== 'null' && $vendor_id !== '') {
            $query->andWhere([
                'or',
                ['vendor_id' => $vendor_id],
                ['vendor_id' => null],
                ['vendor_id' => 0]
            ]);
        }
        
        if ($q) {
            $query->andWhere(['like', 'pre_advance_no', $q]);
        }
        
        $prs = $query->limit(20)->all();
        
        $result = [];
        foreach ($prs as $pr) {
            // คำนวณยอดที่จ่ายไปแล้ว
            $paidAmount = $this->getPaidAmount(\backend\models\PaymentVoucher::REF_TYPE_PRE_ADVANCE, $pr->id);
            
            $remaining = $pr->amount - $paidAmount;
            
            // แสดงเฉพาะที่ยังมียอดคงเหลือ
            if ($remaining > 0) {
                $has_draft_pv = \backend\models\PaymentVoucherRef::find()
                    ->alias('r')
                    ->innerJoin('payment_voucher pv', 'r.payment_voucher_id = pv.id')
                    ->where(['r.ref_type' => \backend\models\PaymentVoucher::REF_TYPE_PRE_ADVANCE, 'r.ref_id' => $pr->id])
                    ->andWhere(['pv.status' => \backend\models\PaymentVoucher::STATUS_DRAFT])
                    ->exists();
                $pv_suffix = $has_draft_pv ? ' [สร้าง PV แล้ว - ยังไม่จ่าย]' : '';
                
                $result[] = [
                    'id' => $pr->id,
                    'text' => $pr->pre_advance_no . ' (คงเหลือ: ' . number_format($remaining, 2) . ( !empty($pr->recipient_name) ? ' - ' . $pr->recipient_name : '' ) . ')' . $pv_suffix,
                    'total_amount' => $pr->amount,
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
            $vat_percent = 0;
            $wht_percent = 0;
            if ($po->is_vat == 1) {
                $vat_percent = $po->vat_percent > 0 ? $po->vat_percent : 7;
            }
            if ($po->whd_tax_per > 0) {
                $wht_percent = $po->whd_tax_per;
            }
            $multiplier = 1 + ($vat_percent / 100);
            $po_full_before_vat = $multiplier > 0 ? ($po->net_amount / $multiplier) : $po->net_amount;
            $po_full_wht = $po_full_before_vat * ($wht_percent / 100);
            $po_target_payment = $po->net_amount - $po_full_wht;
            
            // คำนวณยอดที่จ่ายไปแล้ว
            $paidAmount = $this->getPaidAmount(\backend\models\PaymentVoucherRef::REF_TYPE_PO, $po->id);
            
            $remaining = $po_target_payment - $paidAmount;
            
            // แสดงเฉพาะที่ยังมียอดคงเหลือ
            if ($remaining > 0) {
                $has_draft_pv = \backend\models\PaymentVoucherRef::find()
                    ->alias('r')
                    ->innerJoin('payment_voucher pv', 'r.payment_voucher_id = pv.id')
                    ->where(['r.ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PO, 'r.ref_id' => $po->id])
                    ->andWhere(['pv.status' => \backend\models\PaymentVoucher::STATUS_DRAFT])
                    ->exists();
                $pv_suffix = $has_draft_pv ? ' [สร้าง PV แล้ว - ยังไม่จ่าย]' : '';
                
                $result[] = [
                    'id' => $po->id,
                    'text' => $po->purch_no . ' (คงเหลือ: ' . number_format($remaining, 2) . ( !empty($po->vendor_name) ? ' - ' . $po->vendor_name : '' ) . ')' . $pv_suffix,
                    'total_amount' => $po->net_amount,
                    'paid_amount' => $paidAmount,
                    'remaining' => $remaining,
                ];
            }
        }
        
        return ['results' => $result];
    }

    /**
     * ดึงรายการ None PR ตาม Vendor (ที่ยังไม่จ่ายครบ)
     */
    public function actionGetNonePrByVendor($vendor_id = null, $q = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $query = \backend\models\PurchaseMaster::find()
            ->where(['approve_status' => \backend\models\PurchaseMaster::APPROVE_STATUS_APPROVED])
            ->andWhere(['status' => \backend\models\PurchaseMaster::STATUS_ACTIVE])
            ->andWhere(['>', 'total_amount', 0]);
            
        if ($vendor_id && $vendor_id !== 'null' && $vendor_id !== '') {
            $query->andWhere(['supcod' => $vendor_id]);
        }
        
        if ($q) {
            $query->andWhere(['like', 'docnum', $q]);
        }
        
        $none_prs = $query->limit(20)->all();
        
        $result = [];
        foreach ($none_prs as $none_pr) {
            $vat_percent = 0;
            $wht_percent = 0;
            if ($none_pr->vat_percent > 0) {
                $vat_percent = $none_pr->vat_percent;
            }
            if ($none_pr->tax_percent > 0) {
                $wht_percent = $none_pr->tax_percent;
            }
            $multiplier = 1 + ($vat_percent / 100);
            $none_pr_full_before_vat = $multiplier > 0 ? ($none_pr->total_amount / $multiplier) : $none_pr->total_amount;
            $none_pr_full_wht = $none_pr_full_before_vat * ($wht_percent / 100);
            $none_pr_target_payment = $none_pr->total_amount - $none_pr_full_wht;
            
            // คำนวณยอดที่จ่ายไปแล้ว
            $paidAmount = $this->getPaidAmount(\backend\models\PaymentVoucherRef::REF_TYPE_NONE_PR, $none_pr->id);
            
            $remaining = $none_pr_target_payment - $paidAmount;
            
            // แสดงเฉพาะที่ยังมียอดคงเหลือ
            if ($remaining > 0) {
                $has_draft_pv = \backend\models\PaymentVoucherRef::find()
                    ->alias('r')
                    ->innerJoin('payment_voucher pv', 'r.payment_voucher_id = pv.id')
                    ->where(['r.ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_NONE_PR, 'r.ref_id' => $none_pr->id])
                    ->andWhere(['pv.status' => \backend\models\PaymentVoucher::STATUS_DRAFT])
                    ->exists();
                $pv_suffix = $has_draft_pv ? ' [สร้าง PV แล้ว - ยังไม่จ่าย]' : '';
                
                $result[] = [
                    'id' => $none_pr->id,
                    'text' => $none_pr->docnum . ' (คงเหลือ: ' . number_format($remaining, 2) . ( !empty($none_pr->supnam) ? ' - ' . $none_pr->supnam : '' ) . ')' . $pv_suffix,
                    'total_amount' => $none_pr->total_amount,
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
        
        $pr_ids_raw = Yii::$app->request->post('pr_ids', []);
        $po_ids_raw = Yii::$app->request->post('po_ids', []);
        $none_pr_ids_raw = Yii::$app->request->post('none_pr_ids', []);
        
        $pr_ids = is_string($pr_ids_raw) ? (json_decode($pr_ids_raw, true) ?: []) : $pr_ids_raw;
        $po_ids = is_string($po_ids_raw) ? (json_decode($po_ids_raw, true) ?: []) : $po_ids_raw;
        $none_pr_ids = is_string($none_pr_ids_raw) ? (json_decode($none_pr_ids_raw, true) ?: []) : $none_pr_ids_raw;
        
        $lines = [];
        $total_amount = 0;
        $total_before_vat = 0;
        $total_vat = 0;
        $paid_for_items = [];
        $vendor_id = null;
        
        // ดึงข้อมูลจาก Pre-Advance
        foreach ($pr_ids as $pr_id) {
            $pr = \backend\models\PreAdvance::findOne($pr_id);
            if ($pr) {
                $paidAmount = $this->getPaidAmount(\backend\models\PaymentVoucher::REF_TYPE_PRE_ADVANCE, $pr->id);
                
                $remaining = $pr->amount - $paidAmount;
                $total_amount += $remaining;
                $paid_for_items[] = 'Pre-Advance: ' . $pr->pre_advance_no;
                if (!$vendor_id) {
                    $vendor_id = $pr->vendor_id;
                    $vendor_name = $pr->recipient_name;
                }

                // Add lines from Pre-Advance
                if ($pr->preAdvanceLines) {
                    foreach ($pr->preAdvanceLines as $paLine) {
                        $lines[] = [
                            'account_code' => '',
                            'bill_code' => $pr->pre_advance_no,
                            'description1' => $paLine->description,
                            'description2' => $paLine->remark,
                            'debit' => $paLine->amount,
                            'credit' => 0,
                        ];
                    }
                }
            }
        }
        
        // ดึงข้อมูลจาก PO และคำนวณยอดรวมก่อน VAT และ VAT
        foreach ($po_ids as $po_id) {
            $po = Purch::findOne($po_id);
            if ($po) {
                $vat_percent = 0;
                $wht_percent = 0;
                if ($po->is_vat == 1) {
                    $vat_percent = $po->vat_percent > 0 ? $po->vat_percent : 7;
                }
                if ($po->whd_tax_per > 0) {
                    $wht_percent = $po->whd_tax_per;
                }
                
                $multiplier = 1 + ($vat_percent / 100);
                $po_full_before_vat = $multiplier > 0 ? ($po->net_amount / $multiplier) : $po->net_amount;
                $po_full_wht = $po_full_before_vat * ($wht_percent / 100);
                $po_target_payment = $po->net_amount - $po_full_wht;
                
                $paidAmount = $this->getPaidAmount(\backend\models\PaymentVoucherRef::REF_TYPE_PO, $po->id);
                
                $remaining = $po_target_payment - $paidAmount;
                
                if ($remaining > 0) {
                    $total_amount += $remaining;
                    
                    // คำนวณแยกกลับเป็นก่อนภาษี จากยอดที่ต้องจ่าย
                    $multiplier2 = 1 + ($vat_percent / 100) - ($wht_percent / 100);
                    if ($multiplier2 > 0) {
                        $po_before_vat = $remaining / $multiplier2;
                    } else {
                        $po_before_vat = $remaining;
                    }
                    $po_vat = $po_before_vat * ($vat_percent / 100);
                    $po_wht = $po_before_vat * ($wht_percent / 100);
                    
                    $total_before_vat += $po_before_vat;
                    $total_vat += $po_vat;
                    $total_wht += $po_wht;
                }
            }
        }

        // ดึงข้อมูลจาก None PR
        foreach ($none_pr_ids as $none_pr_id) {
            $none_pr = \backend\models\PurchaseMaster::findOne($none_pr_id);
            if ($none_pr) {
                $vat_percent = 0;
                $wht_percent = 0;
                if ($none_pr->vat_percent > 0) {
                    $vat_percent = $none_pr->vat_percent;
                }
                if ($none_pr->tax_percent > 0) {
                    $wht_percent = $none_pr->tax_percent;
                }
                
                $multiplier = 1 + ($vat_percent / 100);
                $none_pr_full_before_vat = $multiplier > 0 ? ($none_pr->total_amount / $multiplier) : $none_pr->total_amount;
                $none_pr_full_wht = $none_pr_full_before_vat * ($wht_percent / 100);
                $none_pr_target_payment = $none_pr->total_amount - $none_pr_full_wht;
                
                $paidAmount = $this->getPaidAmount(\backend\models\PaymentVoucherRef::REF_TYPE_NONE_PR, $none_pr->id);
                
                $remaining = $none_pr_target_payment - $paidAmount;
                
                if ($remaining > 0) {
                    $total_amount += $remaining;
                    
                    $multiplier2 = 1 + ($vat_percent / 100) - ($wht_percent / 100);
                    if ($multiplier2 > 0) {
                        $none_pr_before_vat = $remaining / $multiplier2;
                    } else {
                        $none_pr_before_vat = $remaining;
                    }
                    $none_pr_vat = $none_pr_before_vat * ($vat_percent / 100);
                    $none_pr_wht = $none_pr_before_vat * ($wht_percent / 100);
                    
                    $total_before_vat += $none_pr_before_vat;
                    $total_vat += $none_pr_vat;
                    $total_wht += $none_pr_wht;
                }
            }
        }
        
        // สร้างแถวบัญชีอัตโนมัติ (เฉพาะเมื่อมี PO หรือ None PR)
        if (count($po_ids) > 0 || count($none_pr_ids) > 0) {
            // แถวที่ 1: สินค้า (Debit)
            $lines[] = [
                'account_code' => '',
                'bill_code' => '',
                'description1' => 'สินค้า/บริการ',
                'description2' => '',
                'debit' => round($total_before_vat, 2),
                'credit' => 0,
            ];
            
            // แถวที่ 2: ภาษีซื้อ (Debit)
            if ($total_vat > 0) {
                $lines[] = [
                    'account_code' => '',
                    'bill_code' => '',
                    'description1' => 'ภาษีซื้อ',
                    'description2' => '',
                    'debit' => round($total_vat, 2),
                    'credit' => 0,
                ];
            }
            
            // แถวที่ 3: ธนาคาร (Credit)
            $lines[] = [
                'account_code' => '',
                'bill_code' => '',
                'description1' => '',
                'description2' => 'ธนาคารเงินฝากกระแสรายวัน',
                'debit' => 0,
                'credit' => round($total_before_vat + $total_vat - $total_wht, 2),
            ];

            // แถวที่ 4: ภาษีหัก ณ ที่จ่าย (Credit)
            if ($total_wht > 0) {
                $lines[] = [
                    'account_code' => '',
                    'bill_code' => '',
                    'description1' => '',
                    'description2' => 'ภาษีหัก ณ ที่จ่ายค้างจ่าย',
                    'debit' => 0,
                    'credit' => round($total_wht, 2),
                ];
            }
        }
        
        return [
            'success' => true,
            'amount' => $total_amount,
            'paid_for' => implode(', ', $paid_for_items),
            'lines' => $lines,
            'pr_ids' => $pr_ids,
            'po_ids' => $po_ids,
            'none_pr_ids' => $none_pr_ids,
            'vendor_id' => $vendor_id ?? null,
            'vendor_name' => $vendor_name ?? null,
        ];
    }

    public function actionPrint($id, $step = 1)
    {
        $model = $this->findModel($id);
        $this->layout = false;
        return $this->render('print', [
            'model' => $model,
            'step' => $step,
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
        $none_pr_ids_raw = Yii::$app->request->post('none_pr_ids', []);
        
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

        if (is_string($none_pr_ids_raw)) {
            $none_pr_ids = json_decode($none_pr_ids_raw, true) ?: [];
        } else {
            $none_pr_ids = $none_pr_ids_raw;
        }
        
        // Debug log
        Yii::info("PR IDs: " . print_r($pr_ids, true), 'payment_voucher');
        Yii::info("PO IDs: " . print_r($po_ids, true), 'payment_voucher');
        Yii::info("None PR IDs: " . print_r($none_pr_ids, true), 'payment_voucher');
        
        // ลบ refs เดิม (กรณี update)
        \backend\models\PaymentVoucherRef::deleteAll(['payment_voucher_id' => $model->id]);
        
        $available_amount = $model->amount;

        // บันทึก Pre-Advance refs
        foreach ($pr_ids as $pr_id) {
            if ($available_amount <= 0) break;
            $pr = \backend\models\PreAdvance::findOne($pr_id);
            if ($pr) {
                $paidAmount = $this->getPaidAmount(\backend\models\PaymentVoucher::REF_TYPE_PRE_ADVANCE, $pr->id, $model->id);
                
                $remaining = $pr->amount - $paidAmount;
                
                if ($remaining > 0) {
                    $allocate = min($remaining, $available_amount);
                    $ref = new \backend\models\PaymentVoucherRef();
                    $ref->payment_voucher_id = $model->id;
                    $ref->ref_type = \backend\models\PaymentVoucher::REF_TYPE_PRE_ADVANCE;
                    $ref->ref_id = $pr->id;
                    $ref->ref_no = $pr->pre_advance_no;
                    $ref->amount = $allocate;
                    $ref->created_at = time();
                    $ref->save(false);
                    
                    $available_amount -= $allocate;
                }
            }
        }
        
        // บันทึก PO refs
        foreach ($po_ids as $po_id) {
            if ($available_amount <= 0) break;
            $po = Purch::findOne($po_id);
            if ($po) {
                $vat_percent = 0;
                $wht_percent = 0;
                if ($po->is_vat == 1) {
                    $vat_percent = $po->vat_percent > 0 ? $po->vat_percent : 7;
                }
                if ($po->whd_tax_per > 0) {
                    $wht_percent = $po->whd_tax_per;
                }
                
                $multiplier = 1 + ($vat_percent / 100);
                $po_full_before_vat = $multiplier > 0 ? ($po->net_amount / $multiplier) : $po->net_amount;
                $po_full_wht = $po_full_before_vat * ($wht_percent / 100);
                $po_target_payment = $po->net_amount - $po_full_wht;

                $paidAmount = $this->getPaidAmount(\backend\models\PaymentVoucherRef::REF_TYPE_PO, $po->id, $model->id);
                
                $remaining = $po_target_payment - $paidAmount;
                
                if ($remaining > 0) {
                    $allocate = min($remaining, $available_amount);
                    $ref = new \backend\models\PaymentVoucherRef();
                    $ref->payment_voucher_id = $model->id;
                    $ref->ref_type = \backend\models\PaymentVoucherRef::REF_TYPE_PO;
                    $ref->ref_id = $po->id;
                    $ref->ref_no = $po->purch_no;
                    $ref->amount = $allocate;
                    $ref->created_at = time();
                    $ref->save(false);
                    
                    $available_amount -= $allocate;
                }
            }
        }

        // บันทึก None PR refs
        foreach ($none_pr_ids as $none_pr_id) {
            if ($available_amount <= 0) break;
            $none_pr = \backend\models\PurchaseMaster::findOne($none_pr_id);
            if ($none_pr) {
                $vat_percent = 0;
                $wht_percent = 0;
                if ($none_pr->vat_percent > 0) {
                    $vat_percent = $none_pr->vat_percent;
                }
                if ($none_pr->tax_percent > 0) {
                    $wht_percent = $none_pr->tax_percent;
                }
                
                $multiplier = 1 + ($vat_percent / 100);
                $none_pr_full_before_vat = $multiplier > 0 ? ($none_pr->total_amount / $multiplier) : $none_pr->total_amount;
                $none_pr_full_wht = $none_pr_full_before_vat * ($wht_percent / 100);
                $none_pr_target_payment = $none_pr->total_amount - $none_pr_full_wht;

                $paidAmount = $this->getPaidAmount(\backend\models\PaymentVoucherRef::REF_TYPE_NONE_PR, $none_pr->id, $model->id);
                
                $remaining = $none_pr_target_payment - $paidAmount;
                
                if ($remaining > 0) {
                    $allocate = min($remaining, $available_amount);
                    $ref = new \backend\models\PaymentVoucherRef();
                    $ref->payment_voucher_id = $model->id;
                    $ref->ref_type = \backend\models\PaymentVoucherRef::REF_TYPE_NONE_PR;
                    $ref->ref_id = $none_pr->id;
                    $ref->ref_no = $none_pr->docnum;
                    $ref->amount = $allocate;
                    $ref->created_at = time();
                    $ref->save(false);
                    
                    $available_amount -= $allocate;
                }
            }
        }
        
        // ตรวจสอบว่าใส่ยอดเงินเกินกว่ายอดคงเหลือของ PR/PO ที่เลือกหรือไม่
        $has_refs = (count($pr_ids) + count($po_ids) + count($none_pr_ids)) > 0;
        if ($has_refs && round($available_amount, 2) > 0) {
            throw new \Exception('ยอดรวมใน Voucher มากกว่ายอดคงเหลือรวมของ PR/PO ที่เลือก (เกินมา ' . number_format($available_amount, 2) . ' บาท)');
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
