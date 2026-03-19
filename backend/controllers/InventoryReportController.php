<?php

namespace backend\controllers;

use Yii;
use backend\models\StockCardSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * InventoryReportController implements the report actions for Inventory management.
 */
class InventoryReportController extends BaseController
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
     * Display stock card report for accountants
     * @return mixed
     */
    public function actionStockCard()
    {
        $searchModel = new StockCardSearch();
        $params = Yii::$app->request->queryParams;
        $searchModel->load($params);

        // Perform search if parameters provided
        $results = [];
        if (!empty($params)) {
             $results = $searchModel->getData();
        }

        return $this->render('stock-card', [
            'searchModel' => $searchModel,
            'results' => $results,
        ]);
    }
}
