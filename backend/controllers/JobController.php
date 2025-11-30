<?php

namespace backend\controllers;

use backend\models\Job;
use backend\models\JobExpense;
use backend\models\JobSearch;
use backend\models\UnitSearch;
use common\models\JobLine;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;
use yii\web\UploadedFile;

/**
 * JobController implements the CRUD actions for Job model.
 */
class JobController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Job models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $pageSize = \Yii::$app->request->post("perpage");
        $searchModel = new JobSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        $dataProvider->setSort(['defaultOrder' => ['id' => SORT_DESC]]);
        $dataProvider->pagination->pageSize = $pageSize;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'perpage' => $pageSize,
        ]);
    }

    /**
     * Displays a single Job model.
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
     * Creates a new Job model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Job();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $jdate = date('Y-m-d H:i:s');
                $xp = explode("/", $model->job_date);
                if($xp != null){
                    if(count($xp) > 1){
                        $jdate = $xp[0] . '/'. $xp[1].'/'.$xp[2];
                    }
                }
                $sdate = date('Y-m-d H:i:s');
                $xp2 = explode("/", $model->start_date);
                if($xp2 != null){
                    if(count($xp2) > 1){
                        $sdate = $xp2[0] . '/'. $xp2[1].'/'.$xp2[2];
                    }
                }
                $ndate = date('Y-m-d H:i:s');
                $xp3 = explode("/", $model->end_date);
                if($xp3 != null){
                    if(count($xp3) > 1){
                        $ndate = $xp3[0] . '/'. $xp3[1].'/'.$xp3[2];
                    }
                }
                $cus_po_date = date('Y-m-d H:i:s');
                $xp4 = explode("/", $model->cus_po_date);
                if($xp4 != null){
                    if(count($xp4) > 1){
                        $cus_po_date = $xp4[0] . '/'. $xp4[1].'/'.$xp4[2];
                    }
                }

                $line_name = \Yii::$app->request->post('line_name');
                $line_description = \Yii::$app->request->post('line_description');
                $line_phone = \Yii::$app->request->post('line_phone');
                $line_email = \Yii::$app->request->post('line_email');


                $model->quotation_id = 0;
                $model->job_date = date('Y-m-d',strtotime($jdate));
                $model->start_date = date('Y-m-d',strtotime($sdate));
                $model->end_date = date('Y-m-d',strtotime($ndate));
                $model->cus_po_date = date('Y-m-d',strtotime($cus_po_date));
                if($model->save()){
                  if($line_name !=null){
                      for($i=0;$i<=count($line_name)-1;$i++){
                          $model_line = new \common\models\JobContactInfo();
                          $model_line->job_id = $model->id;
                          $model_line->name = $line_name[$i];
                        //  $model_line->description = $line_description[$i];
                          $model_line->phone = $line_phone[$i];
                          $model_line->email = $line_email[$i];
                          $model_line->save(false);
                      }
                  }
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Job model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//        $model_line = JobLine::find()->where(['job_id' => $model->id])->all();
//        $model_contact = \common\models\JobContactInfo::find()->where(['job_id' => $model->id])->all();
//
//        // เพิ่มส่วน expense
//        $model_expenses = $model->jobExpenses;
//        if(empty($model_expenses)){
//            $model_expenses = [new JobExpense()];
//        }
//
//        if ($this->request->isPost && $model->load($this->request->post())) {
//            $jdate = date('Y-m-d H:i:s');
//            $xp = explode("/", $model->job_date);
//            if($xp != null){
//                if(count($xp) > 1){
//                    $jdate = $xp[2] . '/'. $xp[0].'/'.$xp[1];
//                }
//            }
//            $sdate = date('Y-m-d H:i:s');
//            $xp2 = explode("/", $model->start_date);
//            if($xp2 != null){
//                if(count($xp2) > 1){
//                    $sdate = $xp2[2] . '/'. $xp2[0].'/'.$xp2[1];
//                }
//            }
//            $ndate = date('Y-m-d H:i:s');
//            $xp3 = explode("/", $model->end_date);
//            if($xp3 != null){
//                if(count($xp3) > 1){
//                    $ndate = $xp3[2] . '/'. $xp3[0].'/'.$xp3[1];
//                }
//            }
//
//            $cus_po_date = date('Y-m-d H:i:s');
//            $xp4 = explode("/", $model->cus_po_date);
//            if($xp4 != null){
//                if(count($xp4) > 1){
//                    $cus_po_date = $xp4[0] . '/'. $xp4[1].'/'.$xp4[2];
//                }
//            }
//
//            $line_name = \Yii::$app->request->post('line_name');
//            $line_description = \Yii::$app->request->post('line_description');
//            $line_phone = \Yii::$app->request->post('line_phone');
//            $line_email = \Yii::$app->request->post('line_email');
//
//            $recordDel = \Yii::$app->request->post('removelist');
//
//
//            //echo $ndate;return;
//            $model->job_date = date('Y-m-d',strtotime($jdate));
//            $model->start_date = date('Y-m-d',strtotime($sdate));
//            $model->end_date = date('Y-m-d',strtotime($ndate));
//            $model->cus_po_date = date('Y-m-d',strtotime($cus_po_date));
//
//
//            $oldExpenseIDs = ArrayHelper::map($model_expenses, 'id', 'id');
//            $model_expenses = \backend\models\Model::createMultiple(JobExpense::classname(), $model_expenses);
//            \backend\models\Model::loadMultiple($model_expenses, Yii::$app->request->post());
//            $deletedExpenseIDs = array_diff($oldExpenseIDs, array_filter(ArrayHelper::map($model_expenses, 'id', 'id')));
//
//            $valid = $model->validate();
//            $valid = \backend\models\Model::validateMultiple($model_expenses) && $valid;
//
//          //  print_r($model_expenses);return;
//            if($valid) {
//                $transaction = \Yii::$app->db->beginTransaction();
//                try {
//                    if ($flag = $model->save(false)) {
//                        /// contact info
//                        if ($recordDel != null) {
//                            $recordDel = explode(",", $recordDel);
//                            for ($i = 0; $i <= count($recordDel) - 1; $i++) {
//                                $model_line = \common\models\JobContactInfo::find()->where(['id' => $recordDel[$i]])->one();
//                                $model_line->delete();
//                            }
//                        }
//                        if ($line_name != null) {
//                            for ($i = 0; $i <= count($line_name) - 1; $i++) {
//                                if ($line_name[$i] == '') continue;
//                                $model_dup = \common\models\JobContactInfo::find()->where(['job_id' => $model->id, 'name' => trim($line_name[$i])])->one();
//                                if ($model_dup == null) {
//                                    $model_line = new \common\models\JobContactInfo();
//                                    $model_line->job_id = $model->id;
//                                    $model_line->name = $line_name[$i];
//                                    //   $model_line->description = $line_description[$i];
//                                    $model_line->phone = $line_phone[$i];
//                                    $model_line->email = $line_email[$i];
//                                    $model_line->save(false);
//                                } else {
//                                    // $model_dup->description = $line_description[$i];
//                                    $model_dup->phone = $line_phone[$i];
//                                    $model_dup->email = $line_email[$i];
//                                    $model_dup->save(false);
//                                }
//                            }
//                        }
//
//                        $uploaded = UploadedFile::getInstances($model, 'jsa_doc');
//                        if (!empty($uploaded)) {
//                            $loop = 0;
//                            foreach ($uploaded as $file) {
//                                $upfiles = "jsa_" . time() . "_" . $loop . "." . $file->getExtension();
//                                if ($file->saveAs('uploads/job/' . $upfiles)) {
//                                    $model->jsa_doc = $upfiles;
//                                    $model->save(false);
//                                }
//                                $loop++;
//                            }
//                        }
//
//                        $uploaded = UploadedFile::getInstances($model, 'report_doc');
//                        if (!empty($uploaded)) {
//                            $loop = 0;
//                            foreach ($uploaded as $file) {
//                                $upfiles = "report_" . time() . "_" . $loop . "." . $file->getExtension();
//                                if ($file->saveAs('uploads/job/' . $upfiles)) {
//                                    $model->report_doc = $upfiles;
//                                    $model->save(false);
//                                }
//                                $loop++;
//                            }
//                        }
//
//                        // ลบรายการเก่า
//                        if (!empty($deletedExpenseIDs)) {
//                            JobExpense::deleteAll(['id' => $deletedExpenseIDs]);
//                        }
//
//                        $i = 0;
//                        foreach ($model_expenses as $expense) {
//                            $expense->job_id = $model->id;
//
//                            // จัดการ upload file
//                            $expense->expense_file = UploadedFile::getInstance($expense, "[{$i}]expense_file");
//
//                            if ($expense->expense_file) {
//                                $fileName = 'expense_' . time() . '_' . $i . '_' . rand(1000, 9999) . '.' . $expense->expense_file->extension;
//                                $uploadPath = 'uploads/expense/';
//
//                                if (!is_dir($uploadPath)) {
//                                    mkdir($uploadPath, 0777, true);
//                                }
//
//                                $expense->expense_file->saveAs($uploadPath . $fileName);
//                                $expense->line_doc = $fileName;
//                            }
//
//                            if (!($flag = $expense->save(false))) {
//                                $transaction->rollBack();
//                                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $expense->getErrors());
//                                break;
//                            }
//                            $i++;
//                        }
//
//
//                        if ($flag) {
//                            $transaction->commit();
//                            return $this->redirect(['view', 'id' => $model->id]);
//                        }
//                    }
//                }catch (\Exception $e){
//                    $transaction->rollBack();
//                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
//                }
//            }else{
//                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: '. json_encode($model->getErrors()));
//                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: '. json_encode($model_expenses));
//            }
//        }
//
//        return $this->render('update', [
//            'model' => $model,
//            'model_line' => $model_line,
//            'model_contact' => $model_contact,
//            'model_expenses' => (empty($model_expenses)) ? [new JobExpense] : $model_expenses
//        ]);
//    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model_line = JobLine::find()->where(['job_id' => $model->id])->all();
        $model_contact = \common\models\JobContactInfo::find()->where(['job_id' => $model->id])->all();

        // เพิ่มส่วน expense
        $model_expenses = JobExpense::find()->where(['job_id' => $model->id])->all();
        if(empty($model_expenses)){
            $model_expenses = [new JobExpense()]; // สร้าง model ใหม่ถ้าไม่มี
        }


        if ($this->request->isPost && $model->load($this->request->post())) {
            $jdate = date('Y-m-d H:i:s');
            $xp = explode("/", $model->job_date);
            if($xp != null){
                if(count($xp) > 1){
                    $jdate = $xp[2] . '/'. $xp[0].'/'.$xp[1];
                }
            }
            $sdate = date('Y-m-d H:i:s');
            $xp2 = explode("/", $model->start_date);
            if($xp2 != null){
                if(count($xp2) > 1){
                    $sdate = $xp2[2] . '/'. $xp2[0].'/'.$xp2[1];
                }
            }
            $ndate = date('Y-m-d H:i:s');
            $xp3 = explode("/", $model->end_date);
            if($xp3 != null){
                if(count($xp3) > 1){
                    $ndate = $xp3[2] . '/'. $xp3[0].'/'.$xp3[1];
                }
            }

            $cus_po_date = date('Y-m-d H:i:s');
            $xp4 = explode("/", $model->cus_po_date);
            if($xp4 != null){
                if(count($xp4) > 1){
                    $cus_po_date = $xp4[0] . '/'. $xp4[1].'/'.$xp4[2];
                }
            }

            // Contact info variables
            $line_name = \Yii::$app->request->post('line_name');
            $line_description = \Yii::$app->request->post('line_description');
            $line_phone = \Yii::$app->request->post('line_phone');
            $line_email = \Yii::$app->request->post('line_email');

            // Expense variables - เพิ่มใหม่
            $expense_id = \Yii::$app->request->post('expense_id');
            $expense_date = \Yii::$app->request->post('expense_date');
            $expense_desc = \Yii::$app->request->post('expense_desc');
            $expense_amount = \Yii::$app->request->post('expense_amount');

            $recordDel = \Yii::$app->request->post('removelist');
            $expenseRemoveList = \Yii::$app->request->post('expense_removelist'); // เพิ่มใหม่

            //echo $ndate;return;
            $model->job_date = date('Y-m-d',strtotime($jdate));
            $model->start_date = date('Y-m-d',strtotime($sdate));
            $model->end_date = date('Y-m-d',strtotime($ndate));
            $model->cus_po_date = date('Y-m-d',strtotime($cus_po_date));

            if($model->save(false)){
                // จัดการลบ contact
                if($recordDel != null){
                    $recordDel = explode(",", $recordDel);
                    for($i=0;$i<=count($recordDel)-1;$i++){
                        if($recordDel[$i] == '') continue;
                        $model_line = \common\models\JobContactInfo::find()->where(['id'=>$recordDel[$i]])->one();
                        if($model_line != null){
                            $model_line->delete();
                        }
                    }
                }

                // จัดการลบ expense - เพิ่มใหม่
                if($expenseRemoveList != null){
                    $expenseRemoveList = explode(",", $expenseRemoveList);
                    for($i=0;$i<=count($expenseRemoveList)-1;$i++){
                        if($expenseRemoveList[$i] == '') continue;
                        $model_expense_del = JobExpense::find()->where(['id'=>$expenseRemoveList[$i]])->one();
                        if($model_expense_del != null){
                            // ลบไฟล์ถ้ามี
                            if($model_expense_del->line_doc){
                                $oldFile = 'uploads/expense/' . $model_expense_del->line_doc;
                                if(file_exists($oldFile)){
                                    unlink($oldFile);
                                }
                            }
                            $model_expense_del->delete();
                        }
                    }
                }

                // บันทึก contact info
                if($line_name !=null){
                    for($i=0;$i<=count($line_name)-1;$i++){
                        if($line_name[$i]=='')continue;
                        $model_dup = \common\models\JobContactInfo::find()->where(['job_id'=>$model->id,'name'=>trim($line_name[$i])])->one();
                        if($model_dup == null){
                            $model_line = new \common\models\JobContactInfo();
                            $model_line->job_id = $model->id;
                            $model_line->name = $line_name[$i];
                            //   $model_line->description = $line_description[$i];
                            $model_line->phone = $line_phone[$i];
                            $model_line->email = $line_email[$i];
                            $model_line->save(false);
                        }else{
                            // $model_dup->description = $line_description[$i];
                            $model_dup->phone = $line_phone[$i];
                            $model_dup->email = $line_email[$i];
                            $model_dup->save(false);
                        }
                    }
                }

                // บันทึก expense - เพิ่มใหม่
                if($expense_desc != null){
                    // รับไฟล์ที่อัพโหลด
                    $uploadedFiles = UploadedFile::getInstancesByName('expense_file');

                    for($i=0; $i<=count($expense_desc)-1; $i++){
                        if($expense_desc[$i]=='') continue;

                        // แปลงวันที่
                        $exp_date = null;
                        if($expense_date[$i] != ''){
                            $exp_date = date('Y-m-d', strtotime($expense_date[$i]));
                        }

                        // ตรวจสอบว่าเป็นการอัพเดทหรือเพิ่มใหม่
                        if(isset($expense_id[$i]) && $expense_id[$i] > 0){
                            // อัพเดทข้อมูลเดิม
                            $model_expense = JobExpense::find()->where(['id'=>$expense_id[$i]])->one();
                            if($model_expense != null){
                                $model_expense->trans_date = $exp_date;
                                $model_expense->description = $expense_desc[$i];
                                $model_expense->line_amount = $expense_amount[$i] ?: 0;

                                // จัดการไฟล์อัพโหลด
                                if(isset($uploadedFiles[$i]) && $uploadedFiles[$i] != null && !$uploadedFiles[$i]->error){
                                    // ลบไฟล์เก่า
                                    if($model_expense->line_doc){
                                        $oldFile = 'uploads/expense/' . $model_expense->line_doc;
                                        if(file_exists($oldFile)){
                                            unlink($oldFile);
                                        }
                                    }

                                    // อัพโหลดไฟล์ใหม่
                                    $fileName = 'expense_' . time() . '_' . $i . '.' . $uploadedFiles[$i]->getExtension();
                                    $uploadPath = 'uploads/expense/';

                                    if(!is_dir($uploadPath)){
                                        mkdir($uploadPath, 0777, true);
                                    }

                                    if($uploadedFiles[$i]->saveAs($uploadPath . $fileName)){
                                        $model_expense->line_doc = $fileName;
                                    }
                                }

                                $model_expense->save(false);
                            }
                        }else{
                            // เพิ่มข้อมูลใหม่
                            $model_expense = new JobExpense();
                            $model_expense->job_id = $model->id;
                            $model_expense->trans_date = $exp_date;
                            $model_expense->description = $expense_desc[$i];
                            $model_expense->line_amount = $expense_amount[$i] ?: 0;

                            // จัดการไฟล์อัพโหลด
                            if(isset($uploadedFiles[$i]) && $uploadedFiles[$i] != null && !$uploadedFiles[$i]->error){
                                $fileName = 'expense_' . time() . '_' . $i . '.' . $uploadedFiles[$i]->getExtension();
                                $uploadPath = 'uploads/expense/';

                                if(!is_dir($uploadPath)){
                                    mkdir($uploadPath, 0777, true);
                                }

                                if($uploadedFiles[$i]->saveAs($uploadPath . $fileName)){
                                    $model_expense->line_doc = $fileName;
                                }
                            }

                            $model_expense->save(false);
                        }
                    }
                }

                // จัดการไฟล์ jsa_doc (โค้ดเดิม)
                $uploaded = UploadedFile::getInstances($model, 'jsa_doc');
                if (!empty($uploaded)) {
                    $loop = 0;
                    foreach ($uploaded as $file) {
                        $upfiles = "jsa_" . time()."_".$loop . "." . $file->getExtension();
                        if ($file->saveAs('uploads/job/' . $upfiles)) {
                            $model->jsa_doc = $upfiles;
                            $model->save(false);
                        }
                        $loop++;
                    }
                }

                // จัดการไฟล์ report_doc (โค้ดเดิม)
                $uploaded = UploadedFile::getInstances($model, 'report_doc');
                if (!empty($uploaded)) {
                    $loop = 0;
                    foreach ($uploaded as $file) {
                        $upfiles = "report_" . time()."_".$loop . "." . $file->getExtension();
                        if ($file->saveAs('uploads/job/' . $upfiles)) {
                            $model->report_doc = $upfiles;
                            $model->save(false);
                        }
                        $loop++;
                    }
                }
                // จัดการไฟล์ cus_po_doc (โค้ดเดิม)
                $uploaded = UploadedFile::getInstances($model, 'cus_po_doc');
                if (!empty($uploaded)) {
                    $loop = 0;
                    foreach ($uploaded as $file) {
                        $upfiles = "cus_po_" . time()."_".$loop . "." . $file->getExtension();
                        if ($file->saveAs('uploads/job/' . $upfiles)) {
                            $model->cus_po_doc = $upfiles;
                            $model->save(false);
                        }
                        $loop++;
                    }
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'model_line' => $model_line,
            'model_contact' => $model_contact,
            'model_expenses' => $model_expenses
        ]);
    }

    /**
     * Deletes an existing Job model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Job model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Job the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Job::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionPrintInvoice($id){

            $model = Job::find()->where(['id' => $id])->one();
            $model_line =  JobLine::find()->where(['job_id' => $id])->all();
            $this->layout = 'main_print';
            return $this->render('_print-invoice',[
                'model' => $model,
                'model_line' =>$model_line,
            ]);

    }

    public function actionPrintBillPlacement($id){

        $model = Job::find()->where(['id' => $id])->one();
        $model_line =  JobLine::find()->where(['job_id' => $id])->all();
        $this->layout = 'main_print';
        return $this->render('_print-bill-placement',[
            'model' => $model,
            'model_line' =>$model_line,
        ]);

    }

    public function actionPrintTaxInvoice($id){

        $model = Job::find()->where(['id' => $id])->one();
        $model_line =  JobLine::find()->where(['job_id' => $id])->all();
        $this->layout = 'main_print';
        return $this->render('_print-tax-invoice',[
            'model' => $model,
            'model_line' =>$model_line,
        ]);

    }
    public function actionGetJobNo(){
        $id = Yii::$app->request->post('id');
        $prefix = 'PR-';

        if ($id) {
            $job = \backend\models\Job::findOne($id);
            if (!$job) {
                echo 'Job not found';
                return;
            }

            $job_no = $job->job_no;

            // หา PR ล่าสุดในระบบเพื่อรันเลขลำดับหลัก (PR-00001)
            $lastPr = \backend\models\PurchReq::find()
                ->orderBy(['id' => SORT_DESC])
                ->one();

            $new_job_no ='';
            if($job_no !=null){
                $xp = explode("-", $job_no);
                if(count($xp) == 3){
                    $new_job_no = $xp[1].'-'.$xp[2];
                }else{
                    $new_job_no = $job_no;
                }
            }

            $mainNumber = 1;
            if ($lastPr) {
                $prParts = explode('-', $lastPr->purch_req_no);
                $mainNumber = isset($prParts[1]) ? ((int)$prParts[1]) + 1 : 1;
            }

            // หาจำนวน PR ที่มีใน job นี้ เพื่อรัน .01, .02, ...
            $lastSubPr = \backend\models\PurchReq::find()
                ->where(['job_id' => $id])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if ($lastSubPr) {
                $subParts = explode('.', $lastSubPr->purch_req_no);
                $subNumber = isset($subParts[1]) ? ((int)$subParts[1]) + 1 : 1;
            } else {
                $subNumber = 1;
            }

            $fullCode = 'PR-' . sprintf('%05d', $mainNumber) . '-' . $new_job_no . '.' . sprintf('%02d', $subNumber);
            echo $fullCode;
        } else {
            echo 'No job ID';
        }
    }
    /**
     * แสดง Timeline รายละเอียดของใบงาน
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionTimeline($id)
    {
        $model = $this->findModel($id);

        // ดึงข้อมูล Purchase Request ที่เกี่ยวข้องกับใบงาน
        $purchReqs = $this->getPurchaseRequests($model->id);

        // ดึงข้อมูล Purchase Order ที่เกี่ยวข้องกับใบงาน
        $purchases = $this->getPurchaseOrders($model->id);

        // ดึงข้อมูล Purchase Order ที่เกี่ยวข้องกับใบงาน
        $purchasesnonepr = $this->getPurchaseOrdersNonePr($model->id);

        // ดึงข้อมูล Journal Transaction ที่เกี่ยวข้องกับใบงาน
        $journalTrans = $this->getJournalTransactions($model->id);

        // ดึงข้อมูล Invoice ที่เกี่ยวข้องกับใบงาน
        $invoices = $this->getInvoices($model->id);

        $billinginvoices = $this->getBillingInvoices($model->id);

        // ดึงข้อมูล Petty Cash Voucher ที่เกี่ยวข้องกับใบงาน
        $pettyCashVouchers = $this->getPettyCashVouchers($model->id);

        // ดึงข้อมูล Payment Receipt ที่เกี่ยวข้องกับใบงาน
        $paymentReceipts = $this->getPaymentReceipts($model->id);

        $vehicleExpense = $this->getJobVehicleExpense($model->job_no);

        // ดึงข้อมูล Job Expense (ค่าใช้จ่ายอื่นๆ)
        $jobExpenses = JobExpense::find()->where(['job_id' => $model->id])->all();

        return $this->render('timeline', [
            'model' => $model,
            'purchReqs' => $purchReqs,
            'purchases' => $purchases,
            'journalTrans' => $journalTrans,
            'invoices' => $invoices,
            'billingInvoices'=> $billinginvoices,
            'pettyCashVouchers' => $pettyCashVouchers,
            'paymentReceipts' => $paymentReceipts,
            'vehicleExpense' => $vehicleExpense,
            'purchasesnonepr'=>$purchasesnonepr,
            'jobExpenses' => $jobExpenses, // ส่งข้อมูลไปที่ view
        ]);
    }

    /**
     * ดึงข้อมูล Purchase Request ที่เกี่ยวข้องกับใบงาน
     * @param integer $jobId
     * @return array
     */
    protected function getPurchaseRequests($jobId)
    {
        $query = "
            SELECT 
                pr.id,
                pr.purch_req_no,
                pr.purch_req_date,
                pr.vendor_id,
                pr.vendor_name,
                pr.approve_status,
                pr.total_amount,
                pr.note,
                pr.created_by,
                em.fname,
                em.lname
            FROM purch_req pr INNER JOIN user ON user.id = pr.created_by INNER JOIN employee as em ON em.id = user.emp_ref_id
            WHERE pr.job_id = :jobId
            ORDER BY pr.purch_req_date DESC
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':jobId', $jobId);

        return $command->queryAll();
    }

    /**
     * ดึงข้อมูล Payment Receipt ที่เกี่ยวข้องกับใบงาน
     * @param integer $jobId
     * @return array
     */
    protected function getPaymentReceipts($jobId)
    {
        $query = "
        SELECT 
            pr.id,
            pr.receipt_number,
            pr.billing_invoice_id,
            pr.job_id,
            pr.payment_date,
            pr.payment_method,
            pr.bank_name,
            pr.account_number,
            pr.cheque_number,
            pr.cheque_date,
            pr.received_amount,
            pr.discount_amount,
            pr.vat_amount,
            pr.withholding_tax,
            pr.net_amount,
            pr.remaining_balance,
            pr.payment_status,
            pr.attachment_path,
            pr.attachment_name,
            pr.notes,
            pr.received_by,
            pr.created_by,
            pr.status,
            bi.billing_number,
            bi.total_amount as billing_amount,
            c.name as customer_name,
            CONCAT(e.fname, ' ', e.lname) as receiver_name
        FROM payment_receipts pr
        LEFT JOIN billing_invoices bi ON bi.id = pr.billing_invoice_id
        LEFT JOIN customer c ON c.id = bi.customer_id
        LEFT JOIN user u ON u.id = pr.received_by
        LEFT JOIN employee e ON e.id = u.emp_ref_id
        WHERE pr.job_id = :jobId
        ORDER BY pr.payment_date DESC
    ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':jobId', $jobId);
        $receipts = $command->queryAll();

        // ดึงรายละเอียดของแต่ละ receipt
        foreach ($receipts as &$receipt) {
            $detailQuery = "
            SELECT 
                prd.id,
                prd.payment_receipt_id,
                prd.billing_invoice_item_id,
                prd.description,
                prd.amount,
                bii.invoice_id,
                i.invoice_number
            FROM payment_receipt_details prd
            LEFT JOIN billing_invoice_items bii ON bii.id = prd.billing_invoice_item_id
            LEFT JOIN invoices i ON i.id = bii.invoice_id
            WHERE prd.payment_receipt_id = :receiptId
            ORDER BY prd.id
        ";

            $detailCommand = Yii::$app->db->createCommand($detailQuery);
            $detailCommand->bindParam(':receiptId', $receipt['id']);
            $receipt['details'] = $detailCommand->queryAll();

            // คำนวณยอดรวมจาก details
            $receipt['total_detail_amount'] = array_sum(array_column($receipt['details'], 'amount'));
        }

        return $receipts;
    }

    /**
     * ดึงข้อมูล Purchase Order ที่เกี่ยวข้องกับใบงาน
     * @param integer $jobId
     * @return array
     */
    protected function getPurchaseOrders($jobId)
    {
        $query = "
            SELECT 
                p.id,
                p.purch_no,
                p.purch_date,
                p.vendor_id,
                p.approve_status,
                p.total_amount,
                p.discount_amount,
                p.vat_amount,
                p.net_amount,
                p.payment_note,
                p.delivery_note,
                vd.name as vendor_name
            FROM purch p INNER JOIN vendor as vd ON vd.id = p.vendor_id
            WHERE p.job_id = :jobId
            ORDER BY p.purch_date DESC
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':jobId', $jobId);

        return $command->queryAll();
    }

    protected function getPurchaseOrdersNonePr($jobId)
    {
        $query = "
            SELECT 
                p.id,
                p.docnum as purch_no,
                p.docdat as purch_date,
                p.supcod as vendor_id,
                p.total_amount,
                vd.name as vendor_name
            FROM purchase_master p INNER JOIN vendor as vd ON vd.id = p.supcod
            WHERE p.job_no = :jobId
            ORDER BY p.docdat DESC
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':jobId', $jobId);

        return $command->queryAll();
    }

    /**
     * ดึงข้อมูล Journal Transaction ที่เกี่ยวข้องกับใบงาน
     * @param integer $jobId
     * @return array
     */
    protected function getJournalTransactions($jobId)
    {
        $query = "
            SELECT 
                jt.id,
                jt.journal_no,
                jt.trans_date,
                jt.trans_type_id,
                jt.stock_type_id,
                jt.customer_id,
                jt.customer_name,
                jt.qty,
                jt.remark,
                jt.status,
                jt.party_id,
                jt.warehouse_id
            FROM journal_trans jt
            WHERE jt.job_id = :jobId AND jt.trans_type_id = 3
            ORDER BY jt.trans_date DESC
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':jobId', $jobId);

        return $command->queryAll();
    }

    /**
     * ดึงข้อมูล Invoice ที่เกี่ยวข้องกับใบงาน
     * @param integer $jobId
     * @return array
     */
    protected function getInvoices($jobId)
    {
        $query = "
            SELECT 
                i.id,
                i.invoice_type,
                i.invoice_number,
                i.invoice_date,
                i.customer_code,
                i.customer_name,
                i.customer_address,
                i.customer_tax_id,
                i.po_number,
                i.po_date,
                i.credit_terms,
                i.due_date,
                i.subtotal,
                i.discount_percent,
                i.discount_amount,
                i.vat_percent,
                i.vat_amount,
                i.total_amount,
                i.total_amount_text,
                i.payment_due_date,
                i.check_due_date,
                i.notes,
                i.status
            FROM invoices i LEFT JOIN quotation q ON q.id = i.quotation_id LEFT JOIN job j ON q.id=j.quotation_id
            WHERE j.id = :jobId
            ORDER BY i.invoice_date DESC
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':jobId', $jobId);

        return $command->queryAll();
    }

    /**
     * ดึงข้อมูล Billing Invoice ที่เกี่ยวข้องกับใบงาน
     * @param integer $jobId
     * @return array
     */
    protected function getBillingInvoices($jobId)
    {
        // ดึงข้อมูลใบวางบิลที่เกี่ยวข้องกับใบงาน
        // เนื่องจาก billing_invoices ไม่มี job_id ต้อง join ผ่าน billing_invoice_items -> invoices -> job
        $billingQuery = "
            SELECT DISTINCT
                bi.id,
                bi.billing_number,
                bi.billing_date,
                bi.customer_id,
                cm.name as customer_name,
                bi.subtotal,
                bi.discount_percent,
                bi.discount_amount,
                bi.vat_percent,
                bi.vat_amount,
                bi.total_amount,
                bi.payment_due_date,
                bi.credit_terms,
                bi.notes,
                bi.status
            FROM billing_invoices bi
            LEFT JOIN customer cm ON cm.id = bi.customer_id
            INNER JOIN billing_invoice_items bii ON bii.billing_invoice_id = bi.id
            INNER JOIN invoices i ON i.id = bii.invoice_id
            LEFT JOIN quotation qt ON qt.id = i.quotation_id
            LEFT JOIN job j ON j.quotation_id = qt.id
            WHERE j.id = :jobId
            ORDER BY bi.billing_date DESC
        ";

        $command = Yii::$app->db->createCommand($billingQuery);
        $command->bindParam(':jobId', $jobId);
        $billings = $command->queryAll();

        // ดึงรายการ invoice ที่อยู่ในแต่ละใบวางบิล (เฉพาะที่เกี่ยวข้องกับ job นี้)
        foreach ($billings as &$billing) {
            $invoicesQuery = "
                SELECT 
                    i.id,
                    i.invoice_type,
                    i.invoice_number,
                    i.invoice_date,
                    i.customer_code,
                    i.customer_name,
                    i.customer_address,
                    i.customer_tax_id,
                    i.po_number,
                    i.po_date,
                    i.credit_terms,
                    i.due_date,
                    i.subtotal,
                    i.discount_percent,
                    i.discount_amount,
                    i.vat_percent,
                    i.vat_amount,
                    i.total_amount,
                    i.total_amount_text,
                    i.payment_due_date,
                    i.check_due_date,
                    i.notes,
                    i.status,
                    bii.item_seq,
                    bii.amount
                FROM invoices i
                INNER JOIN billing_invoice_items bii ON bii.invoice_id = i.id
                LEFT JOIN quotation qt ON qt.id = i.quotation_id
                LEFT JOIN job j ON j.quotation_id = qt.id
                WHERE bii.billing_invoice_id = :billingId
                AND j.id = :jobId AND i.is_billed = 1
                ORDER BY bii.item_seq, i.invoice_date DESC
            ";

            $invoicesCommand = Yii::$app->db->createCommand($invoicesQuery);
            $invoicesCommand->bindParam(':billingId', $billing['id']);
            $invoicesCommand->bindParam(':jobId', $jobId);
            $billing['invoices'] = $invoicesCommand->queryAll();
        }

        return $billings;
    }


    /**
     * ดึงรายการสินค้า/บริการของใบงาน
     * @param integer $jobId
     * @return array
     */
    protected function getJobLines($jobId)
    {
        $query = "
            SELECT 
                jl.id,
                jl.product_id,
                jl.product_name,
                jl.line_price,
                jl.line_total,
                jl.note,
                jl.status
            FROM job_line jl
            WHERE jl.job_id = :jobId
            ORDER BY jl.id
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':jobId', $jobId);

        return $command->queryAll();
    }

    /**
     * ดึงรายการรายละเอียดใบขอซื้อ
     * @param integer $purchReqId
     * @return array
     */
    protected function getPurchaseRequestLines($purchReqId)
    {
        $query = "
            SELECT 
                prl.id,
                prl.product_id,
                prl.product_name,
                prl.product_type,
                prl.qty,
                prl.line_price,
                prl.line_total,
                prl.status,
                prl.note,
                prl.unit_id
            FROM purch_req_line prl
            WHERE prl.purch_req_id = :purchReqId
            ORDER BY prl.id
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':purchReqId', $purchReqId);

        return $command->queryAll();
    }

    /**
     * ดึงรายการรายละเอียดใบสั่งซื้อ
     * @param integer $purchId
     * @return array
     */
    protected function getPurchaseOrderLines($purchId)
    {
        $query = "
            SELECT 
                pl.id,
                pl.product_id,
                pl.product_name,
                pl.product_type,
                pl.qty,
                pl.line_price,
                pl.line_total,
                pl.status,
                pl.note,
                pl.unit_id
            FROM purch_line pl
            WHERE pl.purch_id = :purchId
            ORDER BY pl.id
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':purchId', $purchId);

        return $command->queryAll();
    }

    /**
     * ดึงรายการรายละเอียด Journal Transaction
     * @param integer $journalTransId
     * @return array
     */
    protected function getJournalTransactionLines($journalTransId)
    {
        $query = "
            SELECT 
                jtl.id,
                jtl.product_id,
                jtl.warehouse_id,
                jtl.qty,
                jtl.remark,
                jtl.status,
                jtl.line_price,
                jtl.return_to_type,
                jtl.sale_price,
                jtl.return_note,
                jtl.good_qty,
                jtl.damaged_qty,
                jtl.missing_qty,
                jtl.condition_note,
                jtl.item_condition,
                jtl.unit_id,
                jtl.line_total,
                jtl.is_damage
            FROM journal_trans_line jtl
            WHERE jtl.journal_trans_id = :journalTransId
            ORDER BY jtl.id
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':journalTransId', $journalTransId);

        return $command->queryAll();
    }

    /**
     * ดึงรายการรายละเอียดใบกำกับภาษี
     * @param integer $invoiceId
     * @return array
     */
    protected function getInvoiceItems($invoiceId)
    {
        $query = "
            SELECT 
                ii.id,
                ii.item_seq,
                ii.item_description,
                ii.quantity,
                ii.unit,
                ii.unit_price,
                ii.amount,
                ii.sort_order,
                ii.product_id,
                ii.unit_id
            FROM invoice_items ii
            WHERE ii.invoice_id = :invoiceId
            ORDER BY ii.sort_order, ii.item_seq
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':invoiceId', $invoiceId);

        return $command->queryAll();
    }

    /**
     * ดึงข้อมูล Petty Cash Voucher ที่เกี่ยวข้องกับใบงาน
     * @param integer $jobId
     * @return array
     */
    protected function getPettyCashVouchers($jobId)
    {
        $query = "
        SELECT 
            pcv.id,
            pcv.pcv_no,
            pcv.date as pcv_date,
            pcv.name,
            pcv.amount,
            pcv.paid_for,
            pcv.issued_by,
            pcv.issued_date,
            pcv.approved_by,
            pcv.approved_date,
            pcv.status,
            pcv.quotation_id,
            pcv.pay_for_emp_id,
            pcv.customer_id,
            pcv.vendor_id,
            pcv.approve_status,
            c.name as customer_name,
            v.name as vendor_name,
            CONCAT(e.fname, ' ', e.lname) as employee_name
        FROM petty_cash_voucher pcv
        LEFT JOIN customer c ON c.id = pcv.customer_id
        LEFT JOIN vendor v ON v.id = pcv.vendor_id
        LEFT JOIN employee e ON e.id = pcv.pay_for_emp_id
        WHERE pcv.job_id = :jobId
        ORDER BY pcv.date DESC
    ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':jobId', $jobId);
        $vouchers = $command->queryAll();

        // ดึงรายละเอียดของแต่ละ voucher
        foreach ($vouchers as &$voucher) {
            $detailQuery = "
            SELECT 
                pcd.id,
                pcd.voucher_id,
                pcd.ac_code,
                pcd.detail_date,
                pcd.detail,
                pcd.amount
            FROM petty_cash_detail pcd
            WHERE pcd.voucher_id = :voucherId
            ORDER BY pcd.detail_date, pcd.id
        ";

            $detailCommand = Yii::$app->db->createCommand($detailQuery);
            $detailCommand->bindParam(':voucherId', $voucher['id']);
            $voucher['details'] = $detailCommand->queryAll();

            // คำนวณยอดรวมจาก details
            $voucher['total_detail_amount'] = array_sum(array_column($voucher['details'], 'amount'));
        }

        return $vouchers;
    }

    protected function getJobVehicleExpense($job_no)
    {
        $query = "
            SELECT 
                ve.id,
                ve.vehicle_no,
                ve.expense_date,
                ve.job_description,
                ve.total_distance,
                ve.vehicle_cost,
                ve.passenger_count,
                ve.total_wage
            FROM vehicle_expense ve
            WHERE ve.job_no = :jobNo
            ORDER BY ve.expense_date DESC
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':jobNo', $job_no);

        return $command->queryAll();
    }

    /**
     * แสดงรายการเอกสารแนบของกิจกรรม
     * @param integer $id Job ID
     * @param string $type ประเภทกิจกรรม (purch_req, purchase, journal_trans, invoice, billing)
     * @param integer $activityId ID ของกิจกรรม
     * @return mixed
     */
    public function actionDocuments($id, $type, $activityId)
    {
        $model = $this->findModel($id);

        // ตรวจสอบประเภทกิจกรรมและดึงข้อมูลเอกสาร
        $documents = $this->getActivityDocuments($type, $activityId);
        $activityName = $this->getActivityName($type, $activityId);

        return $this->render('documents', [
            'model' => $model,
            'activityType' => $type,
            'activityId' => $activityId,
            'activityName' => $activityName,
            'documents' => $documents,
        ]);
    }

    /**
     * ดูเอกสาร
     * @param string $type ประเภทกิจกรรม
     * @param integer $id ID ของกิจกรรม
     * @param integer $docId ID ของเอกสาร
     * @return Response
     */
    public function actionViewDocument($type, $id, $docId)
    {
        $document = $this->findDocument($type, $docId);

        if (!$document) {
            throw new NotFoundHttpException('ไม่พบเอกสารที่ต้องการ');
        }

        $document_name = '';
        if(!empty($document['doc'])){
            $document_name = $document['doc'];
        }else if(!empty($document['doc_name'])){
            $document_name = $document['doc_name'];
        }

        $filePath = $this->getDocumentPath($type, $document_name);

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('ไม่พบไฟล์เอกสาร');
        }

        // ตั้งค่า header สำหรับการแสดงผล
        $response = Yii::$app->response;
        $response->headers->set('Content-Type', $this->getMimeType($document_name));
        $response->headers->set('Content-Disposition', 'inline; filename="' . $document_name . '"');

        return $response->sendFile($filePath);
    }

    /**
     * ดาวน์โหลดเอกสาร
     * @param string $type ประเภทกิจกรรม
     * @param integer $id ID ของกิจกรรม
     * @param integer $docId ID ของเอกสาร
     * @return Response
     */
    public function actionDownloadDocument($type, $id, $docId)
    {
        $document = $this->findDocument($type, $docId);

        if (!$document) {
            throw new NotFoundHttpException('ไม่พบเอกสารที่ต้องการ');
        }

        $document_name = '';
        if(!empty($document['doc'])){
            $document_name = $document['doc'];
        }else if(!empty($document['doc_name'])){
            $document_name = $document['doc_name'];
        }
        $filePath = $this->getDocumentPath($type, $document_name);

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('ไม่พบไฟล์เอกสาร');
        }

        return Yii::$app->response->sendFile($filePath, $document_name);
    }

    /**
     * พิมพ์เอกสาร
     * @param string $type ประเภทกิจกรรม
     * @param integer $id ID ของกิจกรรม
     * @param integer $docId ID ของเอกสาร
     * @return Response
     */
    public function actionPrintDocument($type, $id, $docId)
    {
        $document = $this->findDocument($type, $docId);

        if (!$document) {
            throw new NotFoundHttpException('ไม่พบเอกสารที่ต้องการ');
        }

        $document_name = '';
        if(!empty($document['doc'])){
            $document_name = $document['doc'];
        }else if(!empty($document['doc_name'])){
            $document_name = $document['doc_name'];
        }

        $filePath = $this->getDocumentPath($type, $document_name);

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('ไม่พบไฟล์เอกสาร');
        }

        // ตั้งค่า header สำหรับการพิมพ์
        $response = Yii::$app->response;
        $response->headers->set('Content-Type', $this->getMimeType($document_name));
        $response->headers->set('Content-Disposition', 'inline; filename="print_' . $document_name . '"');

        return $response->sendFile($filePath);
    }

    /**
     * ดึงข้อมูลเอกสารของกิจกรรม
     * @param string $type ประเภทกิจกรรม
     * @param integer $activityId ID ของกิจกรรม
     * @return array
     */
    protected function getActivityDocuments($type, $activityId)
    {
        $tableName = $this->getDocumentTableName($type);
        $foreignKey = $this->getDocumentForeignKey($type);

        $doc_name = 'doc';
        if($tableName =='purch_req_doc' || $tableName == 'purch_doc'){
            $doc_name = 'doc_name';
        }

        $query = "
            SELECT 
                id,
                {$foreignKey},
                {$doc_name} as doc,
                created_at,
                created_by
            FROM {$tableName}
            WHERE {$foreignKey} = :activityId
            ORDER BY created_at DESC
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':activityId', $activityId);
        $documents = $command->queryAll();

        // เพิ่มข้อมูลขนาดไฟล์
        foreach ($documents as &$doc) {
            $filePath = $this->getDocumentPath($type, $doc['doc']);
            $doc['file_size'] = file_exists($filePath) ? filesize($filePath) : 0;
        }

        return $documents;
    }

    /**
     * ค้นหาเอกสาร
     * @param string $type ประเภทกิจกรรม
     * @param integer $docId ID ของเอกสาร
     * @return array|null
     */
    protected function findDocument($type, $docId)
    {
        $tableName = $this->getDocumentTableName($type);

        $query = "
            SELECT *
            FROM {$tableName}
            WHERE id = :docId
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':docId', $docId);

        return $command->queryOne();
    }

    /**
     * รับชื่อตารางเอกสารตามประเภทกิจกรรม
     * @param string $type ประเภทกิจกรรม
     * @return string
     */
    protected function getDocumentTableName($type)
    {
        $tableMap = [
            'purch_req' => 'purch_req_doc',
            'purchase' => 'purch_doc',
            'journal_trans' => 'journal_trans_doc',
            'invoice' => 'invoice_doc',
            'billing' => 'invoice_doc', // ใช้ตารางเดียวกับ invoice
        ];

        return $tableMap[$type] ?? 'document';
    }

    /**
     * รับชื่อ foreign key ตามประเภทกิจกรรม
     * @param string $type ประเภทกิจกรรม
     * @return string
     */
    protected function getDocumentForeignKey($type)
    {
        $keyMap = [
            'purch_req' => 'purch_req_id',
            'purchase' => 'purch_id',
            'journal_trans' => 'journal_trans_id',
            'invoice' => 'invoice_id',
            'billing' => 'invoice_id',
        ];

        return $keyMap[$type] ?? 'reference_id';
    }

    /**
     * รับชื่อกิจกรรม
     * @param string $type ประเภทกิจกรรม
     * @param integer $activityId ID ของกิจกรรม
     * @return string
     */
    protected function getActivityName($type, $activityId)
    {
        $queries = [
            'purch_req' => "SELECT CONCAT('ใบขอซื้อ: ', purch_req_no) FROM purch_req WHERE id = :id",
            'purchase' => "SELECT CONCAT('ใบสั่งซื้อ: ', purch_no) FROM purch WHERE id = :id",
            'journal_trans' => "SELECT CONCAT('รายการ: ', journal_no) FROM journal_trans WHERE id = :id",
            'invoice' => "SELECT CONCAT('ใบกำกับ: ', invoice_number) FROM invoices WHERE id = :id",
            'billing' => "SELECT CONCAT('ใบวางบิล: ', billing_number) FROM billing_invoices WHERE id = :id",
        ];

        if (!isset($queries[$type])) {
            return 'กิจกรรมไม่ทราบ';
        }

        $command = Yii::$app->db->createCommand($queries[$type]);
        $command->bindParam(':id', $activityId);
        $result = $command->queryScalar();

        return $result ?: 'ไม่พบข้อมูล';
    }

    /**
     * รับ path ของไฟล์เอกสาร
     * @param string $type ประเภทกิจกรรม
     * @param string $filename ชื่อไฟล์
     * @return string
     */
    protected function getDocumentPath($type, $filename)
    {
        if ($type == 'purch_req') {
            return Yii::getAlias('@webroot/uploads/purch_req_doc/' . $filename);
        }
        if ($type == 'purchase') {
            return Yii::getAlias('@webroot/uploads/purch_doc/' . $filename);
        }

        $basePath = Yii::getAlias('@webroot/uploads/documents');
        return $basePath . '/' . $type . '/' . $filename;
    }

    /**
     * รับ MIME type ของไฟล์
     * @param string $filename ชื่อไฟล์
     * @return string
     */
    protected function getMimeType($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    public function actionGetcustomerinfo(){
       $id = \Yii::$app->request->post('id');
       $customer_name = '';
       if($id){
           $model = \backend\models\Quotation::find()->where(['id'=>$id])->one();
           if($model){
               $customer_name = \backend\models\Customer::findName($model->customer_id);
           }
       }

       echo $customer_name;
    }
    public function actionDeleteFile($id)
    {
        $model = $this->findModel($id);
        if ($model && $model->jsa_doc) {
            $filePath = Yii::getAlias('@webroot/uploads/่job/' . $model->jsa_doc);

            if (file_exists($filePath)) {
                @unlink($filePath); // ลบไฟล์จริง
            }

            $model->jsa_doc = null;
            $model->save(false); // อัปเดตข้อมูล

            Yii::$app->session->setFlash('success', 'ลบไฟล์เรียบร้อยแล้ว');
        }
        if ($model && $model->cus_po_doc != '') {
            $filePath = Yii::getAlias('@webroot/uploads/่job/' . $model->cus_po_doc);

            if (file_exists($filePath)) {
                @unlink($filePath); // ลบไฟล์จริง
            }

            $model->cus_po_doc = null;
            $model->save(false); // อัปเดตข้อมูล

            Yii::$app->session->setFlash('success', 'ลบไฟล์เรียบร้อยแล้ว');
        }

        return $this->redirect(['update', 'id' => $id]);
    }


    public function actionDeleteExpenseFile($id)
    {
        $model = JobExpense::findOne($id);

        if($model !== null && $model->line_doc){
            $filePath = Yii::getAlias('@webroot/uploads/่job/'. $model->line_doc);
            if(file_exists($filePath)){
                unlink($filePath);
            }
            $model->line_doc = null;
            $model->save(false);
        }

        return $this->redirect(Yii::$app->request->referrer);
    }


    public function actionUpdateDocStatus(){
        $model = \backend\models\Job::find()->all();
        if($model){
            foreach ($model as $value){
                \backend\models\JobDocComplete::checkActivityDoc($value->id);
            }
        }
    }



}
