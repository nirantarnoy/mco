<?php

namespace backend\controllers;

use backend\models\Stocksum;
use backend\models\StocksumSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;

/**
 * StocksumController implements the CRUD actions for Stocksum model.
 */
class StocksumController extends BaseController
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
     * Lists all Stocksum models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StocksumSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Stocksum model.
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
     * Creates a new Stocksum model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Stocksum();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
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
     * Updates an existing Stocksum model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Stocksum model.
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

    public function actionStockReport()
    {
        $filter_qty = \Yii::$app->request->get('filter_qty');

        $query = \backend\models\Product::find()
            ->with(['productGroup', 'unit'])
            ->orderBy(['product_group_id' => SORT_ASC, 'code' => SORT_ASC]);
        
        if ($filter_qty === 'gt0') {
            $query->andWhere(['>', 'stock_qty', 0]);
        } elseif ($filter_qty === 'eq0') {
            $query->andWhere(['<=', 'stock_qty', 0]);
        }

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        return $this->render('stock_report', [
            'dataProvider' => $dataProvider,
            'filter_qty' => $filter_qty,
        ]);
    }

    public function actionBorrowReturnReport()
    {
        $job_id = $this->request->get('job_id');
        $product_id = $this->request->get('product_id');
        $from_date = $this->request->get('from_date');
        $to_date = $this->request->get('to_date');

        $query = \backend\models\JournalTransLine::find()
            ->select([
                'MAX(journal_trans_line.id) AS id',
                'journal_trans.job_id AS job_id',
                'journal_trans_line.product_id',
                'SUM(CASE WHEN journal_trans.trans_type_id = 3 THEN journal_trans_line.qty ELSE 0 END) as total_withdraw',
                'SUM(CASE WHEN journal_trans.trans_type_id = 4 THEN journal_trans_line.qty ELSE 0 END) as total_return_withdraw',
                'SUM(CASE WHEN journal_trans.trans_type_id = 5 THEN journal_trans_line.qty ELSE 0 END) as total_borrow',
                'SUM(CASE WHEN journal_trans.trans_type_id = 6 THEN journal_trans_line.qty ELSE 0 END) as total_return_borrow',
                'SUM(COALESCE(journal_trans_line.damaged_qty, 0)) as total_damaged',
                'SUM(COALESCE(journal_trans_line.missing_qty, 0)) as total_missing',
                'GROUP_CONCAT(DISTINCT journal_trans_line.condition_note SEPARATOR ", ") as remarks'
            ])
            ->joinWith('journalTrans')
            ->where(['journal_trans.status' => \backend\models\JournalTrans::STATUS_APPROVED])
            ->groupBy(['journal_trans.job_id', 'journal_trans_line.product_id']);

        if ($job_id) {
            $query->andWhere(['journal_trans.job_id' => $job_id]);
        }
        if ($product_id) {
            $query->andWhere(['journal_trans_line.product_id' => $product_id]);
        }
        if ($from_date) {
            $query->andWhere(['>=', 'journal_trans.trans_date', $from_date]);
        }
        if ($to_date) {
            $query->andWhere(['<=', 'journal_trans.trans_date', $to_date]);
        }

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query->asArray(),
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('borrow_return_report', [
            'dataProvider' => $dataProvider,
            'job_id' => $job_id,
            'product_id' => $product_id,
            'from_date' => $from_date,
            'to_date' => $to_date,
        ]);
    }

    /**
     * Finds the Stocksum model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Stocksum the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Stocksum::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
