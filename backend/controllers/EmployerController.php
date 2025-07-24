<?php

namespace backend\controllers;

use backend\models\Employer;
use backend\models\EmployerSearch;
use backend\models\UnitSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * EmployerController implements the CRUD actions for Employer model.
 */
class EmployerController extends Controller
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
     * Lists all Employer models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $pageSize = \Yii::$app->request->post("perpage");
        $searchModel = new EmployerSearch();
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
     * Displays a single Employer model.
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
     * Creates a new Employer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Employer();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $uploaded = UploadedFile::getInstance($model,'doc');

                if (!empty($uploaded)) {
                    $upfiles = "employer_" . time() . "." . $uploaded->getExtension();
                    if ($uploaded->saveAs('uploads/aricat/' . $upfiles)) {
                        // \backend\models\Agency::updateAll(['doc' => $upfiles], ['id' => $model->id]);
                        $model->doc = $upfiles;
                    }

                }
                if($model->save(false)){
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Employer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model_doc = \common\models\EmployerDoc::find()->where(['employer_id' => $id])->all();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $uploaded = UploadedFile::getInstances($model,'doc');
            $old_doc = \Yii::$app->request->post('old_doc');
            $doc_delete_list = \Yii::$app->request->post('doc_delete_list');


            $model->doc = null;
            if($model->save(false)){
                if (!empty($uploaded)) {
                    $loop = 0;
                    foreach ($uploaded as $file) {
                        $upfiles = "employer_" . time()."_".$loop . "." . $file->getExtension();
                        if ($file->saveAs('uploads/aricat/' . $upfiles)) {
                            $model_doc = new \common\models\EmployerDoc();
                            $model_doc->employer_id = $model->id;
                            $model_doc->doc = $upfiles;
                            $model_doc->save(false);
                        }
                        $loop++;
                    }
                }
                if(!empty($doc_delete_list)){
                    $xp = explode(",", $doc_delete_list);
                    for($i = 0; $i < count($xp); $i++){
                        if(file_exists('uploads/aricat/'.$xp[$i])){
                            if(unlink('uploads/aricat/'.$xp[$i])){
                                \common\models\EmployerDoc::deleteAll(['doc' => trim($xp[$i])]);
                            }
                        }
                    }
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }

        }

        return $this->render('update', [
            'model' => $model,
            'model_doc' => $model_doc
        ]);
    }

    /**
     * Deletes an existing Employer model.
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
     * Finds the Employer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Employer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Employer::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
