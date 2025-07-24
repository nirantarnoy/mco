<?php

namespace backend\controllers;

use backend\models\Job;
use backend\models\JobSearch;
use backend\models\UnitSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
}
