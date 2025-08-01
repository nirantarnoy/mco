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

            $fullCode = 'PR-' . sprintf('%05d', $mainNumber) . '-' . $job_no . '.' . sprintf('%02d', $subNumber);
            echo $fullCode;
        } else {
            echo 'No job ID';
        }
    }

}
