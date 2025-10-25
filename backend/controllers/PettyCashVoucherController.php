<?php

namespace backend\controllers;

use backend\models\PettyCashAdvance;
use backend\models\PettyCashReportSearch;
use Yii;
use backend\models\PettyCashVoucher;
use backend\models\PettyCashDetail;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Json;
use yii\web\UploadedFile;

/**
 * PettyCashVoucherController implements the CRUD actions for PettyCashVoucher model.
 */
class PettyCashVoucherController extends Controller
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
                    'delete' => ['POST','GET'],
                ],
            ],
        ];
    }

    /**
     * Lists all PettyCashVoucher models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => PettyCashVoucher::find()->where(['status' => 1])->orderBy(['id' => SORT_DESC]),
        ]);

        $dataProvider->query->andFilterWhere(['company_id'=> \Yii::$app->session->get('company_id')]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PettyCashVoucher model.
     * @param integer $id
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
     * Creates a new PettyCashVoucher model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PettyCashVoucher();
        $model->date = date('Y-m-d');
        $model->issued_date = date('Y-m-d');

        // Initialize with one empty detail
        $details = [new PettyCashDetail()];


        // ตรวจสอบสถานะวงเงิน
        $currentBalance = PettyCashAdvance::getCurrentBalance();
        $needsRefill = PettyCashAdvance::needsRefill();
        $maxAmount = PettyCashAdvance::MAX_AMOUNT;
        $minAmount = PettyCashAdvance::MIN_AMOUNT;

        // แจ้งเตือนถ้าเงินเหลือน้อย
        if ($needsRefill) {
            \Yii::$app->session->setFlash('warning',
                'เงินสดย่อยเหลือน้อย (' . number_format($currentBalance, 2) . ' บาท) 
            ควรเบิกเงินทดแทนเพื่อเติมวงเงิน'
            );
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->amount > $currentBalance) {
                \Yii::$app->session->setFlash('error',
                    'ไม่สามารถจ่ายเงินได้ เนื่องจากจำนวนเงินเกินยอดคงเหลือ
                <br>ยอดคงเหลือปัจจุบัน: ' . number_format($currentBalance, 2) . ' บาท
                <br>จำนวนที่ต้องการจ่าย: ' . number_format($model->amount, 2) . ' บาท'
                );
            }else {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $model->created_by = \Yii::$app->user->id;
                    if ($model->save()) {
                        // Handle details
                        $detailsData = Yii::$app->request->post('PettyCashDetail', []);
                        $this->saveDetails($model, $detailsData);

                        $uploaded = UploadedFile::getInstancesByName('file_doc_slip');
                        if (!empty($uploaded)) {
                            $loop = 0;
                            foreach ($uploaded as $file) {
                                $upfiles = "invoice_" . time() . "_" . $loop . "." . $file->getExtension();
                                if ($file->saveAs('uploads/pettycash_doc_slip/' . $upfiles)) {
                                    $model_doc = new \common\models\PettyCashVoucherDocSlip();
                                    $model_doc->petty_cash_voucher_id = $model->id;
                                    $model_doc->doc = $upfiles;
                                    $model_doc->created_by = \Yii::$app->user->id;
                                    $model_doc->created_at = time();
                                    $model_doc->save(false);
                                }
                                $loop++;
                            }
                        }

                        $uploaded2 = UploadedFile::getInstancesByName('file_doc_bill');
                        if (!empty($uploaded2)) {
                            $loopx = 0;
                            foreach ($uploaded2 as $file) {
                                $upfiles = "invoice_" . time() . "_" . $loopx . "." . $file->getExtension();
                                if ($file->saveAs('uploads/pettycash_doc_bill/' . $upfiles)) {
                                    $model_doc = new \common\models\PettyCashVoucherDocBill();
                                    $model_doc->petty_cash_voucher_id = $model->id;
                                    $model_doc->doc = $upfiles;
                                    $model_doc->created_by = \Yii::$app->user->id;
                                    $model_doc->created_at = time();
                                    $model_doc->save(false);
                                }
                                $loopx++;
                            }
                        }

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
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
            'details' => $details,
        ]);
    }

    /**
     * Updates an existing PettyCashVoucher model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $details = $model->details;

        // Ensure at least one detail row
        if (empty($details)) {
            $details = [new PettyCashDetail()];
        }

        // ตรวจสอบสถานะวงเงิน
        $currentBalance = PettyCashAdvance::getCurrentBalance();
        $needsRefill = PettyCashAdvance::needsRefill();
        $maxAmount = PettyCashAdvance::MAX_AMOUNT;
        $minAmount = PettyCashAdvance::MIN_AMOUNT;

        // แจ้งเตือนถ้าเงินเหลือน้อย
        if ($needsRefill) {
            \Yii::$app->session->setFlash('warning',
                'เงินสดย่อยเหลือน้อย (' . number_format($currentBalance, 2) . ' บาท) 
            ควรเบิกเงินทดแทนเพื่อเติมวงเงิน'
            );
        }

        if ($model->load(Yii::$app->request->post())) {
            // ตรวจสอบว่าจำนวนเงินที่จ่ายไม่เกินยอดคงเหลือ
            if ($model->amount > $currentBalance) {
                \Yii::$app->session->setFlash('error',
                    'ไม่สามารถจ่ายเงินได้ เนื่องจากจำนวนเงินเกินยอดคงเหลือ
                <br>ยอดคงเหลือปัจจุบัน: ' . number_format($currentBalance, 2) . ' บาท
                <br>จำนวนที่ต้องการจ่าย: ' . number_format($model->amount, 2) . ' บาท'
                );
            } else {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $model->updated_by = \Yii::$app->user->id;
                    if ($model->save()) {
                        // Delete existing details
                        PettyCashDetail::deleteAll(['voucher_id' => $model->id]);

                        // Handle new details
                        $detailsData = Yii::$app->request->post('PettyCashDetail', []);
                        $this->saveDetails($model, $detailsData);

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'details' => $details,
        ]);
    }

    /**
     * Deletes an existing PettyCashVoucher model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        //$model->status = 0; // Soft delete
       // $model->save(false);
        PettyCashVoucher::deleteAll(['id'=>$id]);

        Yii::$app->session->setFlash('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        return $this->redirect(['index']);
    }

    /**
     * Print voucher
     * @param integer $id
     * @return mixed
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);

        return $this->render('print', [
            'model' => $model,
        ]);
    }

    /**
     * Petty Cash Report
     * @return mixed
     */
    public function actionReport()
    {
        $searchModel = new PettyCashReportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Print Report
     * @return mixed
     */
    public function actionPrintReport()
    {
        $searchModel = new PettyCashReportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Remove pagination for print
        $dataProvider->pagination = false;

        return $this->render('print-report', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Save details
     */
    private function saveDetails($model, $detailsData)
    {
        $sortOrder = 1;
        foreach ($detailsData as $detailData) {
            // Skip empty rows - check if any significant data exists
            $hasData = !empty($detailData['detail']) ||
                !empty($detailData['amount']) ||
                !empty($detailData['ac_code']);

            if (!$hasData) {
                continue;
            }

            $detail = new PettyCashDetail();
            $detail->voucher_id = $model->id;
            $detail->sort_order = $sortOrder++;

            // Clean and validate data before loading
            $cleanData = [
                'ac_code' => isset($detailData['ac_code']) ? trim($detailData['ac_code']) : '',
                'detail_date' => !empty($detailData['detail_date']) ? $detailData['detail_date'] : null,
                'detail' => isset($detailData['detail']) ? trim($detailData['detail']) : '',
                'job_ref_id' => isset($detailData['job_ref_id']) ? trim($detailData['job_ref_id']) : 0,
                'amount' => !empty($detailData['amount']) ? (float)$detailData['amount'] : 0.00,
                'vat' => !empty($detailData['vat']) ? (float)$detailData['vat'] : 0.00,
                'vat_amount' => !empty($detailData['vat_amount']) ? (float)$detailData['vat_amount'] : 0.00,
                'wht' => !empty($detailData['wht']) ? (float)$detailData['wht'] : 0.00,
                'other' => !empty($detailData['other']) ? (float)$detailData['other'] : 0.00,
            ];

            // Load the cleaned data
            $detail->attributes = $cleanData;

            if (!$detail->save()) {
                // Log validation errors for debugging
                Yii::error('Failed to save detail: ' . json_encode($detail->errors), __METHOD__);
            }
        }

        // Update total amount
        $model->updateAmountFromDetails();
    }

    /**
     * Finds the PettyCashVoucher model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PettyCashVoucher the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PettyCashVoucher::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAddDocFile(){
        $id = \Yii::$app->request->post('id');
        if($id){
            $uploaded = UploadedFile::getInstancesByName('file_doc');
            if (!empty($uploaded)) {
                $loop = 0;
                foreach ($uploaded as $file) {
                    $upfiles = "invoice_" . time()."_".$loop . "." . $file->getExtension();
                    if ($file->saveAs('uploads/pettycash_doc_slip/' . $upfiles)) {
                        $model_doc = new \common\models\PettyCashVoucherDocSlip();
                        $model_doc->petty_cash_voucher_id = $id;
                        $model_doc->doc = $upfiles;
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

    public function actionAddDocFileBill(){
        $id = \Yii::$app->request->post('id');
        if($id){
            $uploaded = UploadedFile::getInstancesByName('file_doc');
            if (!empty($uploaded)) {
                $loop = 0;
                foreach ($uploaded as $file) {
                    $upfiles = "invoice_" . time()."_".$loop . "." . $file->getExtension();
                    if ($file->saveAs('uploads/pettycash_doc_bill/' . $upfiles)) {
                        $model_doc = new \common\models\PettyCashVoucherDocBill();
                        $model_doc->petty_cash_voucher_id = $id;
                        $model_doc->doc = $upfiles;
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
    public function actionDeleteDocFile(){
        $id = \Yii::$app->request->post('id');
        $doc_delete_list = trim(\Yii::$app->request->post('doc_delete_list'));
        $doc_type_id = trim(\Yii::$app->request->post('doc_delete_type'));
        if($id){
            if($doc_type_id ==2){
                $model_doc = \common\models\PettyCashVoucherDocBill::find()->where(['petty_cash_voucher_id' => $id,'doc' => $doc_delete_list])->one();
                if($model_doc){
                    if($model_doc->delete()){
                        if(file_exists('uploads/pettycash_doc_bill/'.$model_doc->doc)){
                            unlink('uploads/pettycash_doc_bill/'.$model_doc->doc);
                        }
                    }
                }
            }else{
                $model_doc = \common\models\PettyCashVoucherDocSlip::find()->where(['petty_cash_voucher_id' => $id,'doc' => $doc_delete_list])->one();
                if($model_doc){
                    if($model_doc->delete()){
                        if(file_exists('uploads/pettycash_doc_slip/'.$model_doc->doc)){
                            unlink('uploads/pettycash_doc_slip/'.$model_doc->doc);
                        }
                    }
                }
            }

        }
        return $this->redirect(['update', 'id' => $id]);
    }

    public function actionApprove($id){
        if($id){
            $model = \backend\models\PettyCashVoucher::find()->where(['id'=>$id])->one();
            if($model){
                $model->approved_by = \Yii::$app->user->id;
                $model->approve_status = 1;
                $model->approved_date = date('Y-m-d H:i:s');
                if($model->save(false)){
                    Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                    return $this->redirect(['view', 'id' => $model->id]);
                }else{
                    Yii::$app->session->setFlash('error', 'พบปัญหาการบันทึกข้อมูล');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }
    }

    public function actionGetJobInfo()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $request = \Yii::$app->request;

        // ถ้าขอข้อมูลสินค้าทั้งหมดสำหรับ autocomplete
        if ($request->get('action') === 'get-all-jobs') {
            $jobs = \backend\models\Job::find()
                ->where(['status' => 1]) // approved
                ->all();

            $result = [];
            foreach ($jobs as $job) {
                $result[] = [
                    'id' => $job->id,
                    'job_no' => $job->job_no,
                    'quotation_id' => $job->quotation_id ?? '',
                    'job_amount' => $job->job_amount ?? 0,
                    'display' => $job->job_no,// $product->code . ($product->name ? ' (' . $product->name . ')' : '')
                ];
            }

            return $result;
        }

        // ถ้าขอข้อมูลสินค้าเฉพาะ ID (สำหรับการเลือกสินค้า)
        $id = $request->get('id');
        if ($id) {
            $job = \backend\models\Job::findOne($id);
            if ($job) {
                return [
                    'id' => $job->id,
                    'job_no' => $job->job_no,
                    'quotation_id' => $job->quotation_id ?? '',
                    'job_amount' => $job->job_amount ?? 0,
                    'display' => $job->job_no,// $product->code . ($product->name ? ' (' . $product->name . ')' : '')
                ];
            }
        }

        return ['error' => 'Product not found'];
    }
}