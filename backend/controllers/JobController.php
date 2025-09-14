<?php

namespace backend\controllers;

use backend\models\Job;
use backend\models\JobSearch;
use backend\models\UnitSearch;
use common\models\JobLine;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

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


                $model->quotation_id = 0;
                $model->job_date = date('Y-m-d',strtotime($jdate));
                $model->start_date = date('Y-m-d',strtotime($sdate));
                $model->end_date = date('Y-m-d',strtotime($ndate));
                $model->save();
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
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model_line = JobLine::find()->where(['job_id' => $model->id])->all();

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

            //echo $ndate;return;
            $model->job_date = date('Y-m-d',strtotime($jdate));
            $model->start_date = date('Y-m-d',strtotime($sdate));
            $model->end_date = date('Y-m-d',strtotime($ndate));

            if($model->save()){
                return $this->redirect(['view', 'id' => $model->id]);
            }

        }

        return $this->render('update', [
            'model' => $model,
            'model_line' => $model_line
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

        // ดึงข้อมูล Journal Transaction ที่เกี่ยวข้องกับใบงาน
        $journalTrans = $this->getJournalTransactions($model->id);

        // ดึงข้อมูล Invoice ที่เกี่ยวข้องกับใบงาน
        $invoices = $this->getInvoices($model->id);

        return $this->render('timeline', [
            'model' => $model,
            'purchReqs' => $purchReqs,
            'purchases' => $purchases,
            'journalTrans' => $journalTrans,
            'invoices' => $invoices,
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
                pr.status,
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
                p.vendor_name,
                p.status,
                p.total_amount,
                p.discount_amount,
                p.vat_amount,
                p.net_amount,
                p.payment_note,
                p.delivery_note
            FROM purch p
            WHERE p.job_id = :jobId
            ORDER BY p.purch_date DESC
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
            WHERE jt.job_id = :jobId
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
            FROM invoices i
            WHERE i.job_id = :jobId
            ORDER BY i.invoice_date DESC
        ";

        $command = Yii::$app->db->createCommand($query);
        $command->bindParam(':jobId', $jobId);

        return $command->queryAll();
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

}
