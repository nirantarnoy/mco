<?php

namespace backend\controllers;

use backend\models\PurchaseMaster;
use backend\models\PurchaseDetail;
use backend\models\PurchaseMasterSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use Yii;
use yii\db\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use yii\web\UploadedFile;

/**
 * PurchaseMasterController implements the CRUD actions for PurchaseMaster model.
 */
class PurchasemasterController extends Controller
{
    public $enableCsrfValidation = false;
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all PurchaseMaster models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseMasterSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PurchaseMaster model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new PurchaseMaster model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new PurchaseMaster();
        $model->docnum = PurchaseMaster::generateDocnum();
        $model->docdat = date('Y-m-d');
        $model->vatdat = date('Y-m-d');
        $model->vat_percent = 7;
        $model->tax_percent = 0;

        if ($this->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->load($this->request->post())) {

                    // บันทึก Master
                    if ($model->save()) {

                        // บันทึก Details
                        $details = Yii::$app->request->post('PurchaseDetail', []);

                        if (!empty($details)) {
                            foreach ($details as $index => $detailData) {
                                if (!empty($detailData['stkcod']) || !empty($detailData['stkdes'])) {
                                    $detail = new PurchaseDetail();
                                    $detail->purchase_master_id = $model->id;
                                    $detail->line_no = $index + 1;
                                    $detail->stkcod = $detailData['stkcod'] ?? null;
                                    $detail->stkdes = $detailData['stkdes'] ?? null;
                                    $detail->uqnty = $detailData['uqnty'] ?? 0;
                                    $detail->unitpr = $detailData['unitpr'] ?? 0;
                                    $detail->disc = $detailData['disc'] ?? null;
                                    $detail->remark = $detailData['remark'] ?? null;

                                    // คำนวณยอดเงิน
                                    $detail->calculateAmount();

                                    if (!$detail->save()) {
                                        throw new Exception('ไม่สามารถบันทึกรายละเอียดสินค้าได้');
                                    }
                                }
                            }
                        }

                        // คำนวณยอดรวม
                        $model->calculateTotals();
                        $model->save(false);

                        $transaction->commit();

                        Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PurchaseMaster model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model_deposit_all = \backend\models\PurchNonePrDeposit::find()->where(['purchase_master_id'=>$id])->one();
        $model_deposit_line_all = null;
        if($model_deposit_all){
            $model_deposit_line_all = \backend\models\PurchNonePrDepositLine::find()->where(['purch_none_pr_deposit_id'=>$model_deposit_all->id])->one();
        }

        if ($this->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->load($this->request->post())) {
                    $deposit_date = \Yii::$app->request->post('deposit_date');
                    $receive_date = \Yii::$app->request->post('deposit_receive_date');
                    $deposit_amount = \Yii::$app->request->post('deposit_amount');
                    $deposit_doc = UploadedFile::getInstanceByName('deposit_doc');

                    // บันทึก Master
                    if ($model->save()) {

                        // ลบรายละเอียดเก่า
                        PurchaseDetail::deleteAll(['purchase_master_id' => $model->id]);

                        // บันทึก Details ใหม่
                        $details = Yii::$app->request->post('PurchaseDetail', []);

                        if (!empty($details)) {
                            foreach ($details as $index => $detailData) {
                                if (!empty($detailData['stkcod']) || !empty($detailData['stkdes'])) {
                                    $detail = new PurchaseDetail();
                                    $detail->purchase_master_id = $model->id;
                                    $detail->line_no = $index + 1;
                                    $detail->stkcod = $detailData['stkcod'] ?? null;
                                    $detail->stkdes = $detailData['stkdes'] ?? null;
                                    $detail->uqnty = $detailData['uqnty'] ?? 0;
                                    $detail->unitpr = $detailData['unitpr'] ?? 0;
                                    $detail->disc = $detailData['disc'] ?? null;
                                    $detail->remark = $detailData['remark'] ?? null;

                                    // คำนวณยอดเงิน
                                    $detail->calculateAmount();

                                    if (!$detail->save()) {
                                        throw new Exception('ไม่สามารถบันทึกรายละเอียดสินค้าได้');
                                    }
                                }
                            }
                        }

                        // คำนวณยอดรวม
                        $model->calculateTotals();
                        $model->save(false);

                        // upload

                        $uploaded = UploadedFile::getInstancesByName('file_acknowledge_doc');
                        $uploaded1 = UploadedFile::getInstancesByName('file_invoice_doc');
                        $uploaded2 = UploadedFile::getInstancesByName('file_slip_doc');
                        if (!empty($uploaded)) {
                            $loop = 0;
                            foreach ($uploaded as $file) {
                                $upfiles = "purch_none_pr_" . time()."_".$loop . "." . $file->getExtension();
                                if ($file->saveAs('uploads/purch_doc/' . $upfiles)) {
                                    $model_doc = new \common\models\PurchNonePrDoc();
                                    $model_doc->purchase_master_id = $id;
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
                                $upfiles = "purch_none_pr" . time()."_".$loop . "." . $file->getExtension();
                                if ($file->saveAs('uploads/purch_doc/' . $upfiles)) {
                                    $model_doc = new \common\models\PurchNonePrDoc();
                                    $model_doc->purchase_master_id = $id;
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
                                $upfiles = "purch_none_pr_" . time()."_".$loop . "." . $file->getExtension();
                                if ($file->saveAs('uploads/purch_doc/' . $upfiles)) {
                                    $model_doc = new \common\models\PurchNonePrDoc();
                                    $model_doc->purchase_master_id = $id;
                                    $model_doc->doc_name = $upfiles;
                                    $model_doc->doc_type_id = 3;
                                    $model_doc->created_by = \Yii::$app->user->id;
                                    $model_doc->created_at = time();
                                    $model_doc->save(false);
                                }
                                $loop++;
                            }
                        }

                        if($model->is_deposit ==1){ // มีมัดจำ
                            if($deposit_amount > 0){
                                $ch = \backend\models\PurchNonePrDeposit::find()->where(['purchase_master_id'=>$model->id])->one();
                                if($ch){
                                    \backend\models\PurchNonePrDepositLine::deleteAll(['purch_none_pr_deposit_id'=>$ch->id]);
                                    $ch->delete();
                                }

                                $model_purch_deposit = new \backend\models\PurchNonePrDeposit();
                                $model_purch_deposit->trans_date = date('Y-m-d H:i:s',strtotime($deposit_date));
                                $model_purch_deposit->purchase_master_id = $model->id;
                                $model_purch_deposit->status = 0;
                                $model_purch_deposit->created_by = \Yii::$app->user->id;
                                $model_purch_deposit->created_at = time();
                                if($model_purch_deposit->save(false)){
                                    if(!empty($deposit_doc)){
                                        $file = 'purch_none_pr_deposit_'.time().'_'.($deposit_doc->getExtension());
                                        $deposit_doc->saveAs('uploads/purch_doc/' .$file);

                                        $model_purch_deposit_line = new \backend\models\PurchNonePrDepositLine();
                                        $model_purch_deposit_line->purch_none_pr_deposit_id = $model_purch_deposit->id;
                                        $model_purch_deposit_line->deposit_date = date('Y-m-d H:i:s',strtotime($deposit_date));
                                        $model_purch_deposit_line->deposit_amount = (double)$deposit_amount;
                                        $model_purch_deposit_line->deposit_doc = $file;
                                        $model_purch_deposit_line->save(false);
                                    }
                                }
                            }
                        }else{ // ไม่มีมัดจำให้เคลียร์
                            $model_deposit = \backend\models\PurchNonePrDeposit::find()->where(['purchase_master_id'=>$id])->one();
                            if($model_deposit){
                                $model_deposit_line = \backend\models\PurchNonePrDepositLine::find()->where(['purch_none_pr_deposit_id'=>$model_deposit->id])->all();
                                if(!empty($model_deposit_line)){
                                    foreach($model_deposit_line as $model_deposit_line){
                                        if(file_exists('uploads/purch_doc/'.$model_deposit_line->deposit_doc)){
                                            unlink('uploads/purch_doc/'.$model_deposit_line->deposit_doc);
                                        }
                                        $model_deposit_line->delete();
                                    }
                                }
                                $model_deposit->delete();
                            }
                        }

                        $transaction->commit();

                        Yii::$app->session->setFlash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'model_deposit_all'=> $model_deposit_all,
            'model_deposit_line_all'=> $model_deposit_line_all,
        ]);
    }

    /**
     * Deletes an existing PurchaseMaster model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = $this->findModel($id);

            // ลบรายละเอียด
            PurchaseDetail::deleteAll(['purchase_master_id' => $model->id]);

            // ลบหลัก
            $model->delete();

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Export to Excel for EXPRESS
     */
    public function actionExport()
    {
        $date_from = Yii::$app->request->get('date_from');
        $date_to = Yii::$app->request->get('date_to');

        $query = PurchaseMaster::find()
            ->with('purchaseDetails')
            ->orderBy(['docdat' => SORT_ASC, 'docnum' => SORT_ASC]);

        if ($date_from) {
            $query->andWhere(['>=', 'docdat', $date_from]);
        }

        if ($date_to) {
            $query->andWhere(['<=', 'docdat', $date_to]);
        }

        $models = $query->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Define columns
        $columns = [
            'Master ID',
            'DOCNUM',
            'DOCDAT',
            'SUPCOD',
            'SUPNAM',
            'JOB No',
            'Credit Term',
            'Due Date',
            'Tax ID',
            'Discount Code',
            'Address 1',
            'Address 2',
            'Address 3',
            'Zip Code',
            'Tel Num',
            'Branch Num',
            'Bill No',
            'Vat Date',
            'Vat Pr0',
            'Master Amount',
            'Master Unit Price',
            'Master Discount',
            'Vat %',
            'Vat Amount',
            'Tax %',
            'Tax Amount',
            'Total Amount',
            'Master Remark',
            'Status',
            'Master Created At',
            'Master Updated At',
            'Master Created By',
            'Master Updated By',
            'Department ID',
            'Invoice No',
            'Vat Period',
            'Additional Note',
            'Is Deposit',
            'Detail ID',
            'Line No',
            'STKCOD',
            'STKDES',
            'UQNTY',
            'Detail UNITPR',
            'Detail Discount',
            'Detail Amount',
            'Detail Remark',
        ];

        // Set Headers
        $colIndex = 1;
        foreach ($columns as $header) {
            $sheet->setCellValueByColumnAndRow($colIndex, 3, $header);
            $colIndex++;
        }

        // Set Data
        $row = 4;
        foreach ($models as $model) {
            foreach ($model->purchaseDetails as $detail) {
                $colIndex = 1;

                // Master Data
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->id);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->docnum);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->docdat);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->supcod);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->supnam);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->job_no);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->paytrm);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->duedat);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->taxid);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->discod);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->addr01);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->addr02);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->addr03);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->zipcod);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->telnum);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->orgnum);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->refnum);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->vatdat);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->vatpr0);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->amount);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->unitpr);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->disc);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->vat_percent);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->vat_amount);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->tax_percent);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->tax_amount);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->total_amount);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->remark);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->status);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->created_at ? date('Y-m-d H:i:s', $model->created_at) : '');
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->updated_at ? date('Y-m-d H:i:s', $model->updated_at) : '');
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->created_by);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->updated_by);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->department_id);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->invoice_no);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->vat_period);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->additional_note);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->is_deposit);

                // Detail Data
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $detail->id);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $detail->line_no);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $detail->stkcod);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $detail->stkdes);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $detail->uqnty);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $detail->unitpr);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $detail->disc);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $detail->amount);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $detail->remark);

                $row++;
            }
        }

        // ตั้งชื่อไฟล์
        $filename = 'purchase_export_' . date('Ymd_His') . '.xlsx';

        // ส่งออกไฟล์
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Search product by autocomplete
     */
    public function actionSearchProduct($q = '')
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // สมมติว่ามีตาราง product อยู่แล้ว
        // ถ้ายังไม่มี ให้แก้ไขตามชื่อตารางที่มีจริง
        $query = \backend\models\Product::find()
            ->select(['code as value', 'name as label', 'code', 'name', 'cost_price as price'])
            ->where(['like', 'code', $q])
            ->orWhere(['like', 'name', $q])
            ->limit(20);

        $products = $query->asArray()->all();

        return $products;
    }

    /**
     * Finds the PurchaseMaster model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PurchaseMaster the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchaseMaster::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionDeleteDoc(){
        $id = Yii::$app->request->post('id');
        $photo_name = Yii::$app->request->post('doc_name');
        if($id && $photo_name!=''){
            if(file_exists('uploads/purch_doc/'.$photo_name)){
                unlink('uploads/purch_doc/'.$photo_name);
            }
            \common\models\PurchNonePrDoc::deleteAll(['id' => $id]);
        }
        echo "OK";
    }
}