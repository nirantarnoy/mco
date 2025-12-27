<?php

namespace backend\controllers;

use app\behaviors\ActionLogBehavior;
use backend\models\JournalTrans;
use backend\models\PurchReq;
use Mpdf\Mpdf;
use Yii;
use backend\models\Purch;
use backend\models\PurchSearch;
use backend\models\PurchLine;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\db\Transaction;
use yii\web\UploadedFile;

/**
 * PurchController implements the CRUD actions for Purch model.
 */
class PurchController extends BaseController
{
    public $enableCsrfValidation = false;

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
            'actionLog' => [
                'class' => ActionLogBehavior::class,
                'actions' => ['create', 'update', 'delete', 'approve', 'reject', 'receive', 'cancel-receive', 'view', 'print'], // Log เฉพาะ actions เหล่านี้
            ],
        ];
    }

    public function beforeAction($action)
    {
        if ($action->id == 'showdoc') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Lists all Purch models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Purch model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $payment_date = '';
        $paymentLines = null;
        $model_pay = \backend\models\PurchPayment::find()->where(['purch_id' => $id])->one();
        if ($model_pay) {
            $payment_date = $model_pay->trans_date;
            $paymentLines = \backend\models\PurchPaymentLine::find()
                ->where(['purch_payment_id' => $model_pay->id])
                ->orderBy(['id' => SORT_ASC])
                ->all();
        }
        return $this->render('view', [
            'model' => $this->findModel($id),
            'paymentLines' => $paymentLines,
            'payment_date' => $payment_date,
        ]);
    }

    public function actionViewSlip($id)
    {
        $payment_date = '';
        $model_pay = \backend\models\PurchPayment::find()->where(['purch_id' => $id])->one();
        if ($model_pay) {
            $payment_date = $model_pay->trans_date;
        }
        $paymentLine = \backend\models\PurchPaymentLine::findOne($id);

        if ($paymentLine === null) {
            throw new \yii\web\NotFoundHttpException('ไม่พบข้อมูลการโอนเงิน');
        }

        return $this->renderAjax('_view_slip', [
            'model' => $paymentLine,
            'payment_date' => $payment_date,
        ]);
    }

    /**
     * Creates a new Purch model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Purch();
        $model->status = Purch::STATUS_DRAFT;
        $model->approve_status = Purch::APPROVE_STATUS_PENDING;
        $model->purch_date = date('Y-m-d');

        // Initialize with one empty purch line
        $model->purchLines = [new PurchLine()];

        if ($model->load(Yii::$app->request->post())) {
            $purchLines = [];
            $valid = $model->validate();

            $deposit_date = \Yii::$app->request->post('deposit_data');
            $deposit_amount = \Yii::$app->request->post('deposit_amount');
            $deposit_doc = UploadedFile::getInstanceByName('deposit_doc');
            $custom_vat_amount = \Yii::$app->request->post('purch_vat_amount');
            $customer_tax_amount = \Yii::$app->request->post('purch_tax_amount');


            if (isset($_POST['PurchLine'])) {
                foreach ($_POST['PurchLine'] as $index => $purchLineData) {
                    $purchLine = new PurchLine();
                    $purchLine->load($purchLineData, '');
                    $purchLine->status = 1;
                    $purchLines[] = $purchLine;
                    $valid = $purchLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        $totalAmount = 0;
                        $discountAmount = 0;
                        $vatAmount = 0;
                        $netAmount = 0;
                        $tax_amount = 0;

                        // Save purch lines
                        foreach ($purchLines as $purchLine) {
                            $purchLine->purch_id = $model->id;
                            $rate = $model->currency_rate > 0 ? $model->currency_rate : 1;
                            $purchLine->value_amount = ($purchLine->qty * $purchLine->line_price) * $rate;
                            if (!$purchLine->save()) {
                                throw new \Exception('Failed to save purch line');
                            }
                            // คำนวณยอดรวมจากแต่ละรายการ
                            $lineTotal = $purchLine->qty * $purchLine->line_price;
                            $totalAmount += $lineTotal;
                        }

                        // คำนวณส่วนลด
                        if (isset($model->discount_per) && $model->discount_per > 0) {
                            $discountAmount = ($totalAmount * $model->discount_per) / 100;
                        }
                        if (isset($model->discount_amount) && $model->discount_amount > 0.00) {
                            $discountAmount += $model->discount_amount;
                        }

                        // คำนวณยอดหลังหักส่วนลด
                        $afterDiscountAmount = $totalAmount - $discountAmount;

                        // คำนวณ VAT
                        $vatPercent = isset($model->vat_percent) ? $model->vat_percent : 7;
                        if ($vatPercent > 0 && $model->is_vat == 1) {
                            if ($custom_vat_amount != null) {
                                $vatAmount = $custom_vat_amount;
                            } else {
                                $vatAmount = ($afterDiscountAmount * $vatPercent) / 100;
                            }
                        }

                        // คำนวน WHT
                        if ($model->whd_tax_per > 0) {
                            if ($customer_tax_amount != null) {
                                $tax_amount = $customer_tax_amount;
                            } else {
                                $tax_amount = ($afterDiscountAmount * $model->whd_tax_per) / 100;
                            }
                        } else {
                            if ($customer_tax_amount != null) {
                                $tax_amount = $customer_tax_amount;
                            }
                        }

                        // คำนวณยอดสุทธิ
                        $netAmount = $afterDiscountAmount + $vatAmount - $tax_amount;

                        $model->total_amount = $totalAmount;
                        $model->discount_total_amount = $discountAmount;
                        $model->vat_amount = $vatAmount;
                        $model->whd_tax_amount = $tax_amount;
                        $model->net_amount = $netAmount;
                        $rate = $model->currency_rate > 0 ? $model->currency_rate : 1;
                        $model->value_amount = $netAmount * $rate;
                        $model->total_text = PurchReq::numtothai($netAmount);
                        if (!$model->save()) {
                            throw new \Exception('Failed to update total amount');
                        }

                        // upload

                        $uploaded = UploadedFile::getInstancesByName('file_acknowledge_doc');
                        $uploaded1 = UploadedFile::getInstancesByName('file_invoice_doc');
                        $uploaded2 = UploadedFile::getInstancesByName('file_slip_doc');
                        if (!empty($uploaded)) {
                            $loop = 0;
                            foreach ($uploaded as $file) {
                                $upfiles = "purch_" . time() . "_" . $loop . "." . $file->getExtension();
                                if ($file->saveAs('uploads/purch_doc/' . $upfiles)) {
                                    $model_doc = new \common\models\PurchReqDoc();
                                    $model_doc->purch_req_id = $id;
                                    $model_doc->doc_name = $upfiles;
                                    $model_doc->doc_type_id = 1;
                                    $model_doc->created_by = \Yii::$app->user->id;
                                    $model_doc->created_at = time();
                                    $model_doc->save(false);
                                }
                                $loop++;
                            }
                        }
                        if (!empty($uploaded1)) {
                            $loop = 0;
                            foreach ($uploaded1 as $file) {
                                $upfiles = "purch_" . time() . "_" . $loop . "." . $file->getExtension();
                                if ($file->saveAs('uploads/purch_doc/' . $upfiles)) {
                                    $model_doc = new \common\models\PurchReqDoc();
                                    $model_doc->purch_req_id = $id;
                                    $model_doc->doc_name = $upfiles;
                                    $model_doc->doc_type_id = 2;
                                    $model_doc->created_by = \Yii::$app->user->id;
                                    $model_doc->created_at = time();
                                    $model_doc->save(false);
                                }
                                $loop++;
                            }
                        }
                        if (!empty($uploaded2)) {
                            $loop = 0;
                            foreach ($uploaded2 as $file) {
                                $upfiles = "purch_" . time() . "_" . $loop . "." . $file->getExtension();
                                if ($file->saveAs('uploads/purch_doc/' . $upfiles)) {
                                    $model_doc = new \common\models\PurchReqDoc();
                                    $model_doc->purch_req_id = $id;
                                    $model_doc->doc_name = $upfiles;
                                    $model_doc->doc_type_id = 3;
                                    $model_doc->created_by = \Yii::$app->user->id;
                                    $model_doc->created_at = time();
                                    $model_doc->save(false);
                                }
                                $loop++;
                            }
                        }

                        if ($model->is_deposit == 1) {
                            if ($deposit_amount > 0) {
                                $model_purch_deposit = new \backend\models\PurchDeposit();
                                $model_purch_deposit->purch_id = $model->id;
                                $model_purch_deposit->status = 0;
                                $model_purch_deposit->created_by = \Yii::$app->user->id;
                                $model_purch_deposit->created_at = time();
                                if ($model_purch_deposit->save(false)) {
                                    if (!empty($deposit_doc)) {
                                        $file = 'purch_deposit_' . time() . '_' . ($deposit_doc->getExtension());
                                        $deposit_doc->saveAs('uploads/purch_doc/' . $file);

                                        $model_purch_deposit_line = new \backend\models\PurchDepositLine();
                                        $model_purch_deposit_line->purch_deposit_id = $model_purch_deposit->id;
                                        $model_purch_deposit_line->deposit_date = date('Y-m-d H:i:s', strtotime($deposit_date));
                                        $model_purch_deposit_line->deposit_amount = (double)$deposit_amount;
                                        $model_purch_deposit_line->deposit_doc = $file;
                                        $model_purch_deposit_line->save(false);
                                    }

                                }
                            }
                        }

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'สร้างใบสั่งซื้อเรียบร้อยแล้ว');
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

    /**
     * Updates an existing Purch model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Load existing purch lines
        $model->purchLines = $model->getPurchLines()->all();
        if (empty($model->purchLines)) {
            $model->purchLines = [new PurchLine()];
        }

        $model_deposit_all = \backend\models\PurchDeposit::find()->where(['purch_id' => $id])->one();
        $model_deposit_line_all = null;
        if ($model_deposit_all) {
            $model_deposit_line_all = \backend\models\PurchDepositLine::find()->where(['purch_deposit_id' => $model_deposit_all->id])->one();
        }

        $model_purch_vendor_bill = \common\models\PurchVendorBill::find()->where(['purch_id' => $id])->one();

        // ดึงข้อมูล Payment Lines ที่เกี่ยวข้องกับ Purch นี้
        $paymentLines = null;
//        $model_pay = \backend\models\PurchPayment::find()->where(['purch_id' => $id])->one();
//        if($model_pay){
//            $paymentLines = \backend\models\PurchPaymentLine::find()
//                ->where(['purch_payment_id' => $model_pay->id])
//                ->orderBy(['created_at' => SORT_DESC])
//                ->all();
//        }


        if ($model->load(Yii::$app->request->post())) {
            $custom_vat_amount = \Yii::$app->request->post('purch_vat_amount');
            $customer_tax_amount = \Yii::$app->request->post('purch_tax_amount');
            $deposit_date = \Yii::$app->request->post('deposit_date');
            $receive_date = \Yii::$app->request->post('deposit_receive_date');
            $deposit_amount = \Yii::$app->request->post('deposit_amount');
            $deposit_doc = UploadedFile::getInstanceByName('deposit_doc');

            $purch_vendor_bill_date = \Yii::$app->request->post('purch_vendor_bill_date');
            $purch_bill_date = \Yii::$app->request->post('purch_bill_date');
            $purch_vendor_bill_doc = UploadedFile::getInstanceByName('purch_vendor_bill_doc');

            $purchLines = [];
            $valid = $model->validate();

            if (isset($_POST['PurchLine'])) {
                foreach ($_POST['PurchLine'] as $index => $purchLineData) {
                    if (isset($purchLineData['id']) && !empty($purchLineData['id'])) {
                        // Update existing line
                        $purchLine = PurchLine::findOne($purchLineData['id']);
                        if (!$purchLine) {
                            $purchLine = new PurchLine();
                        }
                    } else {
                        // Create new line
                        $purchLine = new PurchLine();
                    }
                    $purchLine->load($purchLineData, '');
                    $purchLine->status = 1;
                    $purchLines[] = $purchLine;
                    $valid = $purchLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        $totalAmount = 0;
                        $discountAmount = 0;
                        $vatAmount = 0;
                        $netAmount = 0;
                        $tax_amount = 0;
                        // Delete existing lines that are not in the new list
                        $existingLineIds = [];
                        foreach ($purchLines as $purchLine) {
                            if (!$purchLine->isNewRecord) {
                                $existingLineIds[] = $purchLine->id;
                            }
                        }

                        PurchLine::deleteAll([
                            'and',
                            ['purch_id' => $model->id],
                            ['not in', 'id', $existingLineIds]
                        ]);

                        // Save purch lines
                        foreach ($purchLines as $purchLine) {
                            $purchLine->purch_id = $model->id;
                            $rate = $model->currency_rate > 0 ? $model->currency_rate : 1;
                            $purchLine->value_amount = ($purchLine->qty * $purchLine->line_price) * $rate;
                            if (!$purchLine->save()) {
                                throw new \Exception('Failed to save purch line');
                            }
                            // คำนวณยอดรวมจากแต่ละรายการ
                            $lineTotal = $purchLine->qty * $purchLine->line_price;
                            $totalAmount += $lineTotal;
                        }

                        // คำนวณส่วนลด (สมมติว่ามีฟิลด์ discount_percent ใน model)
                        if (isset($model->discount_per) && $model->discount_per > 0) {
                            $discountAmount = ($totalAmount * $model->discount_per) / 100;
                        }
                        if (isset($model->discount_amount) && $model->discount_amount > 0.00) {
                            $discountAmount += $model->discount_amount;
                        }

                        // คำนวณยอดหลังหักส่วนลด
                        $afterDiscountAmount = $totalAmount - $discountAmount;

                        // คำนวณ VAT (สมมติว่ามีฟิลด์ vat_percent ใน model หรือใช้ VAT 7%)

                        $vatPercent = isset($model->vat_percent) ? $model->vat_percent : 7;
                        if ($vatPercent > 0 && $model->is_vat == 1) {
                            if ($custom_vat_amount != null) {
                                $vatAmount = $custom_vat_amount;
                            } else {
                                $vatAmount = ($afterDiscountAmount * $vatPercent) / 100;
                            }

                        }

                        // คำนวน WHT

                        if ($model->whd_tax_per > 0) {
                            if ($customer_tax_amount != null) {
                                $tax_amount = $customer_tax_amount;
                            } else {
                                $tax_amount = ($afterDiscountAmount * $model->whd_tax_per) / 100;
                            }

                        } else {
                            if ($customer_tax_amount != null) {
                                $tax_amount = $customer_tax_amount;
                            }
                        }

                        // คำนวณยอดสุทธิ
                        $netAmount = $afterDiscountAmount + $vatAmount - $tax_amount;


                        $model->total_amount = $totalAmount; // sub total
                        $model->discount_total_amount = $discountAmount;
                        $model->vat_amount = $vatAmount;
                        $model->whd_tax_amount = $tax_amount;
                        $model->net_amount = $netAmount;
                        $rate = $model->currency_rate > 0 ? $model->currency_rate : 1;
                        $model->value_amount = $netAmount * $rate;
                        $model->total_text = PurchReq::numtothai($netAmount);
                        if (!$model->save()) {
                            throw new \Exception('Failed to update total amount');
                        }

                        if ($model->is_deposit == 1) { // มีมัดจำ
                            if ($deposit_amount > 0) {
                                $model_purch_deposit = \backend\models\PurchDeposit::find()->where(['purch_id' => $model->id])->one();
                                if (!$model_purch_deposit) {
                                    $model_purch_deposit = new \backend\models\PurchDeposit();
                                    $model_purch_deposit->purch_id = $model->id;
                                    $model_purch_deposit->created_by = \Yii::$app->user->id;
                                    $model_purch_deposit->created_at = time();
                                }
                                $model_purch_deposit->trans_date = date('Y-m-d H:i:s', strtotime($deposit_date));
                                $model_purch_deposit->status = 0;

                                if ($model_purch_deposit->save(false)) {
                                    $model_purch_deposit_line = \backend\models\PurchDepositLine::find()->where(['purch_deposit_id' => $model_purch_deposit->id])->one();
                                    if (!$model_purch_deposit_line) {
                                        $model_purch_deposit_line = new \backend\models\PurchDepositLine();
                                        $model_purch_deposit_line->purch_deposit_id = $model_purch_deposit->id;
                                    }
                                    
                                    $model_purch_deposit_line->deposit_date = date('Y-m-d H:i:s', strtotime($deposit_date));
                                    $model_purch_deposit_line->deposit_amount = (double)$deposit_amount;

                                    if (!empty($deposit_doc)) {
                                        $file = 'purch_deposit_' . time() . '_' . ($deposit_doc->getExtension());
                                        if ($deposit_doc->saveAs('uploads/purch_doc/' . $file)) {
                                            $model_purch_deposit_line->deposit_doc = $file;
                                        }
                                    }

                                    if ($receive_date) {
                                        $model_purch_deposit_line->receive_date = date('Y-m-d H:i:s', strtotime($receive_date));
                                    }

                                    $deposit_receive_doc = UploadedFile::getInstanceByName('deposit_receive_doc');
                                    if (!empty($deposit_receive_doc)) {
                                        $file_receive = 'purch_deposit_return_' . time() . '_' . ($deposit_receive_doc->getExtension());
                                        if ($deposit_receive_doc->saveAs('uploads/purch_doc/' . $file_receive)) {
                                            $model_purch_deposit_line->receive_doc = $file_receive;
                                        }
                                    }

                                    $model_purch_deposit_line->save(false);
                                }
                            }
                        } else { // ไม่มีมัดจำให้เคลียร์
                            $model_deposit = \backend\models\PurchDeposit::find()->where(['purch_id' => $id])->one();
                            if ($model_deposit) {
                                $model_deposit_line = \backend\models\PurchDepositLine::find()->where(['purch_deposit_id' => $model_deposit->id])->all();
                                if (!empty($model_deposit_line)) {
                                    foreach ($model_deposit_line as $model_deposit_line) {
                                        if (file_exists('uploads/purch_doc/' . $model_deposit_line->deposit_doc)) {
                                            unlink('uploads/purch_doc/' . $model_deposit_line->deposit_doc);
                                        }
                                        $model_deposit_line->delete();
                                    }
                                }
                                $model_deposit->delete();
                            }
                        }

                        if ($purch_bill_date != null && $purch_vendor_bill_date != null) {
                            if (!empty($purch_vendor_bill_doc)) {
                                if (!empty($purch_vendor_bill_doc)) {
                                    $file = 'purch_vendor_bill_' . time() . '_' . ($purch_vendor_bill_doc->getExtension());
                                    $purch_vendor_bill_doc->saveAs('uploads/purch_doc/' . $file);

                                    $model_vendor_bill = \common\models\PurchVendorBill::find()->where(['purch_id' => $id])->one();
                                    if ($model_vendor_bill) {
                                        if (file_exists('uploads/purch_doc/' . $model_vendor_bill->vendor_bill_doc)) {
                                            unlink('uploads/purch_doc/' . $model_vendor_bill->vendor_bill_doc);
                                        }
                                        $model_vendor_bill->bill_date = date('Y-m-d', strtotime($purch_bill_date));
                                        $model_vendor_bill->appoinment_date = date('Y-m-d', strtotime($purch_vendor_bill_date));
                                        $model_vendor_bill->bill_doc = $file;
                                        $model_vendor_bill->save(false);
                                    } else {
                                        $model_vendor_bill = new \common\models\PurchVendorBill();
                                        $model_vendor_bill->purch_id = $id;
                                        $model_vendor_bill->bill_date = date('Y-m-d', strtotime($purch_bill_date));
                                        $model_vendor_bill->appoinment_date = date('Y-m-d', strtotime($purch_vendor_bill_date));
                                        $model_vendor_bill->bill_doc = $file;
                                        $model_vendor_bill->save(false);
                                    }
                                }
                            }
                        }

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
                }
            } else {
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด');
            }
        }

        return $this->render('update', [
            'model' => $model,
            'paymentLines' => $paymentLines,
            'model_deposit_all' => $model_deposit_all,
            'model_deposit_line_all' => $model_deposit_line_all,
            'model_purch_vendor_bill' => $model_purch_vendor_bill
        ]);
    }

    public function calculateTotalAmount($id, $lines)
    {
        $total = 0;
        $discount = 0;
        $vat_amount = 0;

        $model = $this->findModel($id);

        foreach ($lines as $line) {
            $total += $line->line_total;
        }

        if ($model->discount_per > 0) {
            $discount = $total * ($model->discount_per / 100);
        }
        if ($model->discount_amount > 0) {
            $discount += $model->discount_amount;
        }
        if ($model->is_vat == 1) {
            $vat_amount = $total * 0.07;
        }

        $total -= $discount;
        $total += $vat_amount;

        return $total;
    }

    public function calculateTotalAmount2($lines)
    {
        $total = 0;
        foreach ($lines as $line) {
            $total += $line->line_total;
        }
        return $total;
    }

    /**
     * Deletes an existing Purch model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Delete all purch lines first
            PurchLine::deleteAll(['purch_id' => $id]);

            // Delete the purch record
            $model->delete();

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบข้อมูลได้: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Get product info for AJAX
     */
//    public function actionGetProductInfo($id)
//    {
//        Yii::$app->response->format = Response::FORMAT_JSON;
//        return Product::getProductInfo($id);
//    }

    /**
     * Approve purchase order
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = Purch::APPROVE_STATUS_APPROVED;
        $model->status = Purch::STATUS_ACTIVE;
        $model->approve_by = Yii::$app->user->id;
        $model->approve_date = date('Y-m-d H:i:s');

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'อนุมัติใบสั่งซื้อเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถอนุมัติใบสั่งซื้อได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Reject purchase order
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionReject($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = Purch::APPROVE_STATUS_REJECTED;
        $model->status = Purch::STATUS_CANCELLED;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'ไม่อนุมัติใบสั่งซื้อเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถปฏิเสธใบสั่งซื้อได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = Purch::STATUS_CANCELLED;
        $model->status = Purch::STATUS_CANCELLED;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'ยกเลิกใบสั่งซื้อเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถยกเลิกใบสั่งซื้อซื้อได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the Purch model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Purch the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Purch::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionReceive($id)
    {
        $purchModel = $this->findModel($id);

        // Check if PO is approved
        if ($purchModel->approve_status != Purch::APPROVE_STATUS_APPROVED) {
            \Yii::$app->session->setFlash('error', 'ไม่สามารถรับสินค้าได้ กรุณาอนุมัติใบสั่งซื้อก่อน');
            return $this->redirect(['view', 'id' => $purchModel->id]);
        }

        // Get PO lines with remaining quantities
        $poLines = $this->getPOLinesWithRemaining($purchModel->id);

        if (empty($poLines)) {
            \Yii::$app->session->setFlash('warning', 'สินค้าทั้งหมดได้รับเข้าครบแล้ว');
            return $this->redirect(['view', 'id' => $purchModel->id]);
        }

        if (\Yii::$app->request->isPost) {
            $receiveData = \Yii::$app->request->post('receive', []);
            $warehouseId = \Yii::$app->request->post('line_warehouse_id');
            $remark = \Yii::$app->request->post('remark', '');
            $checklistData = \Yii::$app->request->post('checklist', []);
            $uploaded = UploadedFile::getInstancesByName('file_doc');

            if (empty($warehouseId)) {
                \Yii::$app->session->setFlash('error', 'กรุณาเลือกคลังสินค้า');
            } else {
                // Process receive with checklist
                $result = $this->processReceiveWithChecklist($purchModel, $receiveData, $warehouseId, $remark, $checklistData);

                if ($result['success']) {
                    // Upload documents if any
                    if (!empty($uploaded)) {
                        $loop = 0;
                        foreach ($uploaded as $file) {
                            $upfiles = "purch_receive_" . time() . "_" . $loop . "." . $file->getExtension();
                            if ($file->saveAs('uploads/purch_receive_doc/' . $upfiles)) {
                                $model_doc = new \backend\models\PurchReceiveDoc();
                                $model_doc->purch_id = $id;
                                $model_doc->doc_name = $upfiles;
                                $model_doc->created_by = \Yii::$app->user->id;
                                $model_doc->created_at = time();
                                $model_doc->save(false);
                            }
                            $loop++;
                        }
                    }

                    \Yii::$app->session->setFlash('success', $result['message']);
                    return $this->redirect(['view', 'id' => $purchModel->id]);
                } else {
                    \Yii::$app->session->setFlash('error', $result['message']);
                }
            }
        }

        return $this->render('receive', [
            'purchModel' => $purchModel,
            'poLines' => $poLines,
            'warehouses' => \backend\models\Warehouse::getWarehouseList(),
        ]);
    }

    /**
     * Confirm Service Receipt (No Inventory Transaction)
     */
    public function actionConfirmServiceReceive($id)
    {
        $model = $this->findModel($id);

        if ($model->approve_status != Purch::APPROVE_STATUS_APPROVED) {
            \Yii::$app->session->setFlash('error', 'ใบสั่งซื้อยังไม่อนุมัติ');
            return $this->redirect(['view', 'id' => $id]);
        }

        if ($model->status == Purch::STATUS_COMPLETED) {
            \Yii::$app->session->setFlash('warning', 'ใบสั่งซื้อนี้เสร็จสมบูรณ์แล้ว');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Update status to COMPLETED
        $model->status = Purch::STATUS_COMPLETED;
        if ($model->save()) {
            \Yii::$app->session->setFlash('success', 'ยืนยันการรับบริการเรียบร้อยแล้ว');
        } else {
            \Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกข้อมูลได้: ' . implode(', ', $model->getFirstErrors()));
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Cancel PO receive
     * @param int $id Journal Trans ID
     * @return Response
     */
    public function actionCancelReceive($id)
    {
        $journalTrans = \backend\models\JournalTrans::findOne($id);

        if (!$journalTrans) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการรับสินค้า');
            return $this->redirect(['index']);
        }

        if ($journalTrans->status == \backend\models\JournalTrans::STATUS_CANCELLED) {
            Yii::$app->session->setFlash('warning', 'รายการนี้ถูกยกเลิกแล้ว');
            return $this->redirect(['view', 'id' => $journalTrans->trans_ref_id]);
        }

        $result = $this->processCancelReceive($journalTrans);

        if ($result['success']) {
            Yii::$app->session->setFlash('success', $result['message']);
        } else {
            Yii::$app->session->setFlash('error', $result['message']);
        }

        return $this->redirect(['view', 'id' => $journalTrans->trans_ref_id]);
    }

    /**
     * Get PO lines with remaining quantities
     */
    private function getPOLinesWithRemaining($purchId)
    {
        $sql = "
            SELECT 
                pl.*,
                p.product_type_id,
                p.name as master_name,
                p.description as master_description,
                COALESCE(received.total_received, 0) as total_received,
                (pl.qty - COALESCE(received.total_received, 0)) as remaining_qty
            FROM purch_line pl
            LEFT JOIN product p ON pl.product_id = p.id
            LEFT JOIN (
                SELECT 
                    product_id,
                    SUM(jtl.qty) as total_received
                FROM journal_trans_line jtl
                INNER JOIN journal_trans jt ON jtl.journal_trans_id = jt.id
                WHERE jt.trans_ref_id = :purchId 
                AND jt.trans_type_id = :transType 
                AND jt.po_rec_status = :status
                AND jt.company_id = :companyId
                GROUP BY product_id
            ) received ON pl.product_id = received.product_id
            WHERE pl.purch_id = :purchId 
            AND (pl.status = :lineStatus OR pl.status IS NULL)
            AND (pl.qty - COALESCE(received.total_received, 0)) > 0
        ";

        // Use session company_id or default to 1
        $companyId = \Yii::$app->session->get('company_id') ?: 1;

        return \Yii::$app->db->createCommand($sql, [
            ':purchId' => $purchId,
            ':transType' => \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE,
            ':status' => 1,
            ':companyId' => $companyId,
            ':lineStatus' => \backend\models\PurchLine::STATUS_ACTIVE,
        ])->queryAll();
    }

    /**
     * Process receive with dynamic checklist
     */
    private function processReceiveWithChecklist($purchModel, $receiveData, $warehouseId, $remark, $checklistData)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // Validate receive data
            $validItems = [];
            $totalQty = 0;

            foreach ($receiveData as $productId => $qty) {
                $qty = floatval($qty);
                if ($qty > 0) {
                    $validItems[$productId] = $qty;
                    $totalQty += $qty;
                }
            }

            if (empty($validItems)) {
                return ['success' => false, 'message' => 'กรุณาระบุจำนวนสินค้าที่ต้องการรับเข้า'];
            }

            // Create Journal Transaction
            $journalTrans = new \backend\models\JournalTrans();
            $journalTrans->trans_date = date('Y-m-d H:i:s');
            $journalTrans->trans_type_id = \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE;
            $journalTrans->stock_type_id = \backend\models\JournalTrans::STOCK_TYPE_IN;
            $journalTrans->trans_ref_id = $purchModel->id;
            $journalTrans->warehouse_id = 0;
            $journalTrans->customer_name = $purchModel->vendor_name;
            $journalTrans->status = 0;
            $journalTrans->po_rec_status = 1;
            $journalTrans->qty = $totalQty;
            $journalTrans->remark = $remark;

            if (!$journalTrans->save()) {
                throw new \Exception('ไม่สามารถสร้าง Journal Transaction ได้: ' . implode(', ', $journalTrans->getFirstErrors()));
            }

            $loop_index = 0;
            // Process each item
            foreach ($validItems as $lineId => $qty) {
                $line_warehouse_id = $warehouseId[$loop_index];
                $loop_index++;

                // Get PO line info by ID
                $poLine = \backend\models\PurchLine::findOne($lineId);

                if (!$poLine || $poLine->purch_id != $purchModel->id) {
                    throw new \Exception("ไม่พบรายการสินค้าในใบสั่งซื้อ (Line ID: $lineId)");
                }

                $productId = $poLine->product_id;
                $productCode = '';
                if($productId){
                    $product = \backend\models\Product::findOne($productId);
                    if($product){
                        $productCode = $product->code;
                    }
                }

                // Create Journal Transaction Line
                $journalTransLine = new \backend\models\JournalTransLine();
                $journalTransLine->journal_trans_id = $journalTrans->id;
                $journalTransLine->product_id = $productId;
                $journalTransLine->warehouse_id = $line_warehouse_id;
                $journalTransLine->qty = $qty;
                $journalTransLine->remark = $poLine->product_name . ' (' . $purchModel->purch_no . ')';
                // Save unit_id if available in PO line
                if(isset($poLine->unit_id)){
                     $journalTransLine->unit_id = $poLine->unit_id;
                }

                if (!$journalTransLine->save()) {
                    throw new \Exception('ไม่สามารถสร้าง Journal Transaction Line ได้: ' . implode(', ', $journalTransLine->getFirstErrors()));
                }

                if ($line_warehouse_id > 0 && !empty($productId)) {
                    // Create Stock Transaction
                    $stockTrans = new \backend\models\StockTrans();
                    $stockTrans->journal_trans_id = $journalTrans->id;
                    $stockTrans->trans_date = $journalTrans->trans_date;
                    $stockTrans->product_id = $productId;
                    $stockTrans->warehouse_id = $line_warehouse_id;
                    $stockTrans->trans_type_id = \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE;
                    $stockTrans->stock_type_id = \backend\models\JournalTrans::STOCK_TYPE_IN;
                    $stockTrans->qty = $qty;
                    $stockTrans->line_price = $poLine->line_price;
                    $stockTrans->status = 1;
                    $stockTrans->remark = $poLine->product_name;

                    if (!$stockTrans->save()) {
                        throw new \Exception('ไม่สามารถสร้าง Stock Transaction ได้: ' . implode(', ', $stockTrans->getFirstErrors()));
                    }

                    // Update Stock Summary
                    if (!\backend\models\StockSum::updateStockIn($productId, $line_warehouse_id, $qty, 1)) {
                        throw new \Exception("ไม่สามารถอัพเดทสต๊อกสินค้า ID: $productId ได้");
                    }
                }
            }

            // Save Dynamic Checklist if data provided
            if (!empty($checklistData['checker_name']) || !empty($checklistData['check_date'])) {
                $checklist = new \backend\models\ReceivingChecklist();
                $checklist->purch_id = $purchModel->id;
                $checklist->journal_trans_id = $journalTrans->id;
                $checklist->check_date = $checklistData['check_date'] ?? date('Y-m-d');
                $checklist->checker_name = $checklistData['checker_name'] ?? '';

                // Save all checklist arrays
                $checklist->setGeneralConditionArray($checklistData['general_condition'] ?? []);
                $checklist->setCorrectItemsArray($checklistData['correct_items'] ?? []);
                $checklist->setCorrectQuantityArray($checklistData['correct_quantity'] ?? []);
                $checklist->setCorrectSpecArray($checklistData['correct_spec'] ?? []);
                $checklist->setHasCertificateArray($checklistData['has_certificate'] ?? []);
                $checklist->setHasManualArray($checklistData['has_manual'] ?? []);

                // Notes
                $checklist->notes = $checklistData['notes'] ?? '';

                if (!$checklist->save()) {
                    // Log error but don't fail the transaction
                    \Yii::error('ไม่สามารถบันทึก Checklist: ' . implode(', ', $checklist->getFirstErrors()));
                }
            }

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'รับสินค้าเข้าคลังเรียบร้อยแล้ว เลขที่เอกสาร: ' . $journalTrans->journal_no
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
    }

    /**
     * Process cancel receive
     */
    private function processCancelReceive($journalTrans)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // Cancel Journal Transaction
            $journalTrans->status = \backend\models\JournalTrans::STATUS_CANCELLED;
            if (!$journalTrans->save()) {
                throw new \Exception('ไม่สามารถยกเลิก Journal Transaction ได้' . implode(', ', $journalTrans->getFirstErrors()));
            }

            // Cancel all related Stock Transactions
            \backend\models\StockTrans::updateAll(
                ['status' => \backend\models\StockTrans::STATUS_CANCELLED],
                ['journal_trans_id' => $journalTrans->id]
            );

            // Reverse stock quantities
            // $journalTransLines = $journalTrans->journalTransLines;
            $journalTransLines = \backend\models\JournalTransLine::find()
                ->where(['journal_trans_id' => $journalTrans->id])->all();
            foreach ($journalTransLines as $line) {
                // Reverse stock by reducing the quantity (opposite of receive)
                if (!\backend\models\StockSum::updateStockOut(
                    $line->product_id,
                    $line->warehouse_id,
                    $line->qty,
                    \backend\models\JournalTrans::STOCK_TYPE_OUT
                )) {
                    throw new \Exception("ไม่สามารถปรับปรุงสต๊อกสินค้า ID: {$line->product_id} ได้");
                }
            }

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'ยกเลิกการรับสินค้าเรียบร้อยแล้ว เลขที่เอกสาร: ' . $journalTrans->journal_no
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
    }

    /**
     * Get receive history for PO
     * @param int $id
     * @return Response
     */
    public function actionReceiveHistory($id)
    {
        $purchModel = $this->findModel($id);

        $receiveHistory = \backend\models\JournalTrans::find()
            ->where([
                'trans_ref_id' => $id,
                'trans_type_id' => \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE
            ])
            ->with(['journalTransLines', 'journalTransLines.product', 'journalTransLines.warehouse'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('receive-history', [
            'purchModel' => $purchModel,
            'receiveHistory' => $receiveHistory,
        ]);
    }

    public function actionGetProductInfo()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $request = \Yii::$app->request;

        // ถ้าขอข้อมูลสินค้าทั้งหมดสำหรับ autocomplete
        if ($request->get('action') === 'get-all-products') {
            $products = \backend\models\Product::find()
                ->where(['status' => 1])
                ->all();

            $result = [];
            foreach ($products as $product) {
                $result[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code ?? '',
                    'price' => $product->sale_price ?? 0,
                    'display' => $product->name,// $product->code . ($product->name ? ' (' . $product->name . ')' : '')
                ];
            }

            return $result;
        }

        // ถ้าขอข้อมูลสินค้าเฉพาะ ID (สำหรับการเลือกสินค้า)
        $id = $request->get('id');
        if ($id) {
            $product = \backend\models\Product::findOne($id);
            if ($product) {
                return [
                    'id' => $product->id,
                    'product_name' => $product->name,
                    'name' => $product->name,
                    'code' => $product->code ?? '',
                    'price' => $product->sale_price ?? 0,
                    'display' => $product->code . ($product->name ? ' (' . $product->name . ')' : '')
                ];
            }
        }

        return ['error' => 'Product not found'];
    }

    public function actionPrint($id, $format = 'html')
    {
        $purchase = Purch::findOne($id);

        if (!$purchase) {
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        }

        // ดึงข้อมูล purchase lines พร้อม product
        $purchaseLines = PurchLine::find()
            ->where(['purch_id' => $id])
            ->with('product')
            ->all();

        if ($format == 'pdf') {
            return $this->generatePdf($purchase, $purchaseLines);
        }

        // แสดงแบบ HTML
        $this->layout = '@backend/views/layouts/main_print';

        return $this->render('print', [
            'purchase' => $purchase,
            'purchaseLines' => $purchaseLines,
            'showButtons' => true,
        ]);
    }

    public function actionPrintForExport($id, $format = 'html')
    {
        $purchase = Purch::findOne($id);

        if (!$purchase) {
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        }

        // ดึงข้อมูล purchase lines พร้อม product
        $purchaseLines = PurchLine::find()
            ->where(['purch_id' => $id])
            ->with('product')
            ->all();

        if ($format == 'pdf') {
            return $this->generatePdf($purchase, $purchaseLines);
        }

        // แสดงแบบ HTML
        $this->layout = '@backend/views/layouts/main_print';

        return $this->render('print-for-export', [
            'purchase' => $purchase,
            'purchaseLines' => $purchaseLines,
            'showButtons' => true,
        ]);
    }


    public function actionPrintReceiveBill($id)
    {
        $model = \backend\models\JournalTrans::findOne($id);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');

        }
    }

    /**
     * Generate PDF
     */
    protected function generatePdf($purchase, $purchaseLines)
    {
        // Render HTML content
        $content = $this->renderPartial('print-pdf', [
            'purchase' => $purchase,
            'purchaseLines' => $purchaseLines,
            'showButtons' => false,
        ]);

        // Setup mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 5,
            'margin_footer' => 5,
            'fontDir' => [Yii::getAlias('@backend') . '/web/fonts/'],
            'fontdata' => [
                'thsarabun' => [
                    'R' => 'THSarabunNew.ttf',
                    'B' => 'THSarabunNewBold.ttf',
                ],
            ],
            'default_font' => 'thsarabun',
            'default_font_size' => 14,
        ]);

        // Write HTML to PDF
        $mpdf->WriteHTML($content);

        // Output PDF
        $filename = 'PO_' . $purchase->purch_no . '.pdf';
        $mpdf->Output($filename, 'I'); // I = inline, D = download

        exit;
    }

    public function actionPrintreceipt($id = null)
    {
        //$this->layout = 'print'; // Use a minimal print layout
        $model = \backend\models\Purch::findOne($id);
        $model_line = \backend\models\PurchLine::find()->where(['purch_id' => $model->id])->all();
//        $checklist = \backend\models\ReceivingChecklist::find()
//            ->where(['purch_id' => $id])
//            ->orderBy(['id' => SORT_DESC])
//            ->one();
        $this->layout = 'main_print';
        return $this->render('_printreceipt', [
            'model' => $model,
            'model_line' => $model_line,
          //  'checklist' => $checklist,
        ]);
    }

    // In your controller
    public function actionPrintTags()
    {
        $id = \Yii::$app->request->post('purch_id');
        if ($id) {
            $model = \backend\models\PurchLine::find()->where(['purch_id' => $id])->all();
            $selectedProducts = [];

            foreach ($model as $product) {
                // if (in_array($product['id'], $selectedIds)) {
                $selectedProducts[] = $product;
                // }
            }

            return $this->render('_print-tag', [
                'selectedProducts' => $selectedProducts,
            ]);
        }
    }

    public function actionGenerateTags()
    {
//        $productData = Yii::$app->request->post('products');
//        $printData = [];
//
//        if ($productData) {
//            foreach ($productData as $data) {
//                $product = json_decode($data['product'], true);
//                $copies = (int)$data['copies'];
//
//                for ($i = 0; $i < $copies; $i++) {
//                    $printData[] = $product;
//                }
//            }
//        }

        $printData = [];

        $id = \Yii::$app->request->post('purch_id');
        // $purch_no = \backend\models\Purch::findNo($id);
        $line_ref_po = \Yii::$app->request->post('line_ref_po');
        $line_description = \Yii::$app->request->post('line_description');
        $line_model = \Yii::$app->request->post('line_model');
        $line_brand = \Yii::$app->request->post('line_brand');
        $line_quantity = \Yii::$app->request->post('line_quantity');
        $line_copies = \Yii::$app->request->post('line_copies');
        $productData = [];
        for ($i = 0; $i < count($line_ref_po); $i++) {
            for ($j = 0; $j < $line_copies[$i]; $j++) {
                $productData[] = [
                    'ref_po' => $line_ref_po[$i],
                    'description' => $line_description[$i],
                    'model' => $line_model[$i],
                    'brand' => $line_brand[$i],
                    'quantity' => $line_quantity[$i],
                    'copies' => $line_copies[$i],
                ];

                $printData = $productData;
            }
//            $productData[] = [
//                'ref_po' => $line_ref_po[$i],
//                'description' => $line_description[$i],
//                'model' => $line_model[$i],
//                'brand' => $line_brand[$i],
//                'quantity' => $line_quantity[$i],
//                'copies' => $line_copies[$i],
//            ];
//            $printData = $productData;
        }

        // print_r($printData);return;

        $format = Yii::$app->request->get('format', 'html');

        if ($format === 'pdf') {
            $content = $this->renderPartial('_print-preview', ['printData' => $printData]);

            $pdf = new \kartik\mpdf\Pdf([
                'mode' => \kartik\mpdf\Pdf::MODE_UTF8,
                'format' => \kartik\mpdf\Pdf::FORMAT_A4,
                'orientation' => \kartik\mpdf\Pdf::ORIENT_PORTRAIT,
                'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
                'content' => $content,
                'cssFile' => '@frontend/web/css/print-tag.css',
                'options' => ['title' => 'Product Tags'],
                'methods' => [
                    'SetHeader' => ['Product Tags'],
                    'SetFooter' => ['{PAGENO}'],
                ]
            ]);

            return $pdf->render();
        } elseif ($format === 'excel') {
            return $this->exportExcel($printData);
        } else {
            return $this->renderPartial('_print-preview', ['printData' => $printData]);
        }
    }

    public function actionPrintDeliveryNote($id = null)
    {
        $this->layout = 'main_print'; // Use minimal print layout
        return $this->render('_printdeliverynote');
    }


    private function exportExcel($printData)
    {
        $objPHPExcel = new \PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();

        $row = 1;
        $col = 0;

        foreach ($printData as $index => $product) {
            if ($col >= 3) {
                $col = 0;
                $row += 5;
            }

            $startCol = $col * 3;

            $sheet->setCellValueByColumnAndRow($startCol, $row, 'Ref.Po: ' . $product['ref_po']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 1, 'Descrip: ' . $product['description']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 2, 'Model: ' . $product['model']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 3, 'Brand: ' . $product['brand']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 4, 'Q\'ty: ' . $product['quantity']);

            $col++;
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="product_tags.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

        Yii::$app->end();
    }

    public function actionAddDocFile()
    {
        $id = \Yii::$app->request->post('id');
        if ($id) {
            $uploaded = UploadedFile::getInstancesByName('file_doc');
            if (!empty($uploaded)) {
                $loop = 0;
                foreach ($uploaded as $file) {
                    $upfiles = "purch_" . time() . "_" . $loop . "." . $file->getExtension();
                    if ($file->saveAs('uploads/purch_doc/' . $upfiles)) {
                        $model_doc = new \common\models\PurchDoc();
                        $model_doc->purch_id = $id;
                        $model_doc->doc_name = $upfiles;
                        $model_doc->created_by = \Yii::$app->user->id;
                        $model_doc->created_at = time();
                        $model_doc->save(false);
                    }
                    $loop++;
                }
            }

        }
        return $this->redirect(['update', 'id' => $id]);
    }

    public function actionDeleteDocFile()
    {
        $id = \Yii::$app->request->post('id');
        $doc_delete_list = trim(\Yii::$app->request->post('doc_delete_list'));
        if ($id) {
            $model_doc = \common\models\PurchDoc::find()->where(['purch_id' => $id, 'doc_name' => trim($doc_delete_list)])->one();
            if ($model_doc) {
                if ($model_doc->delete()) {
                    if (file_exists('uploads/purch_doc/' . $model_doc->doc_name)) {
                        unlink('uploads/purch_doc/' . $model_doc->doc_name);
                    }
                }
            }
            return $this->redirect(['update', 'id' => $id]);
        }
        return $this->redirect(['index']);
    }

    public function actionAddDocFileNew()
    {
        // upload
        $id = \Yii::$app->request->post('id');
        $uploaded = UploadedFile::getInstancesByName('file_acknowledge_doc');
        $uploaded1 = UploadedFile::getInstancesByName('file_invoice_doc');
        $uploaded2 = UploadedFile::getInstancesByName('file_slip_doc');
        if($id){
            if (!empty($uploaded)) {
                $loop = 0;
                foreach ($uploaded as $file) {
                    $upfiles = "purch_" . time() . "_" . $loop . "." . $file->getExtension();
                    if ($file->saveAs('uploads/purch_doc/' . $upfiles)) {
                        $model_doc = new \common\models\PurchDoc();
                        $model_doc->purch_id = $id;
                        $model_doc->doc_name = $upfiles;
                        $model_doc->doc_type_id = 1;
                        $model_doc->created_by = \Yii::$app->user->id;
                        $model_doc->created_at = time();
                        $model_doc->save(false);
                    }
                    $loop++;
                }
            }
            if (!empty($uploaded1)) {
                $loop = 0;
                foreach ($uploaded1 as $file) {
                    $upfiles = "purch_" . time() . "_" . $loop . "." . $file->getExtension();
                    if ($file->saveAs('uploads/purch_doc/' . $upfiles)) {
                        $model_doc = new \common\models\PurchDoc();
                        $model_doc->purch_id = $id;
                        $model_doc->doc_name = $upfiles;
                        $model_doc->doc_type_id = 2;
                        $model_doc->created_by = \Yii::$app->user->id;
                        $model_doc->created_at = time();
                        $model_doc->save(false);
                    }
                    $loop++;
                }
            }
            if (!empty($uploaded2)) {
                $loop = 0;
                foreach ($uploaded2 as $file) {
                    $upfiles = "purch_" . time() . "_" . $loop . "." . $file->getExtension();
                    if ($file->saveAs('uploads/purch_doc/' . $upfiles)) {
                        $model_doc = new \common\models\PurchDoc();
                        $model_doc->purch_id = $id;
                        $model_doc->doc_name = $upfiles;
                        $model_doc->doc_type_id = 3;
                        $model_doc->created_by = \Yii::$app->user->id;
                        $model_doc->created_at = time();
                        $model_doc->save(false);
                    }
                    $loop++;
                }
            }
        }
        return $this->redirect(['update', 'id' => $id]);

    }
    public function actionShowdoc($filename){
        $filePath = Yii::getAlias('@backend/web/uploads/purch_doc/') . $filename;

        // Debug ดูว่า path ถูกไหม
        // echo $filePath; exit;

        if (file_exists($filePath)) {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            // กำหนด MIME type
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
            ];

            $mimeType = isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : mime_content_type($filePath);

            return Yii::$app->response->sendFile($filePath, $filename, [
                'inline' => true,
                'mimeType' => $mimeType
            ]);
        } else {
            throw new \yii\web\NotFoundHttpException('ไม่พบไฟล์: ' . $filename);
        }
    }

    public function actionReportVendorSummary()
    {
        $searchModel = new \backend\models\PurchSearch();
        $dataProvider = null;
        
        $from_date = \Yii::$app->request->get('from_date');
        $to_date = \Yii::$app->request->get('to_date');
        $vendor_id = \Yii::$app->request->get('vendor_id');

        $query = \backend\models\Purch::find()->asArray();
        $query->select([
            'vendor_id',
            'COUNT(id) as po_count',
            'SUM(net_amount) as total_amount'
        ]);
        
        if ($from_date && $to_date) {
            $query->andFilterWhere(['between', 'purch_date', $from_date, $to_date]);
        }

        if ($vendor_id) {
            $query->andFilterWhere(['vendor_id' => $vendor_id]);
        }
        
        $query->groupBy(['vendor_id']);
        $query->orderBy(['total_amount' => SORT_DESC]);

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'key' => 'vendor_id',
        ]);

        return $this->render('report_vendor_summary', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'vendor_id' => $vendor_id,
        ]);
    }

}
