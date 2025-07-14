<?php

namespace backend\controllers;

use Yii;
use backend\models\JournalTrans;
use backend\models\JournalTransLine;
use backend\models\Product;
use backend\models\StockSum;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Response;

/**
 * JournalTransController implements the CRUD actions for JournalTrans model.
 */
class JournaltransController extends Controller
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
                    'approve' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all JournalTrans models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => JournalTrans::find()->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single JournalTrans model.
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
     * Creates a new JournalTrans model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new JournalTrans();
        $lines = [new JournalTransLine()];

        if ($model->load(Yii::$app->request->post())) {
            $linesData = Yii::$app->request->post('JournalTransLine', []);
            $lines = [];

            foreach ($linesData as $lineData) {
                $line = new JournalTransLine();
                $line->load($lineData, '');
                $lines[] = $line;
            }

            if ($this->saveTransaction($model, $lines)) {
                Yii::$app->session->setFlash('success', 'Transaction created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'lines' => $lines,
        ]);
    }

    /**
     * Updates an existing JournalTrans model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Only allow edit if status is draft
        if ($model->status !== JournalTrans::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', 'Cannot edit approved or processed transactions.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $lines = $model->journalTransLines;
        if (empty($lines)) {
            $lines = [new JournalTransLine()];
        }

        if ($model->load(Yii::$app->request->post())) {
            $linesData = Yii::$app->request->post('JournalTransLine', []);
            $lines = [];

            foreach ($linesData as $lineData) {
                if (!empty($lineData['id'])) {
                    $line = JournalTransLine::findOne($lineData['id']);
                } else {
                    $line = new JournalTransLine();
                }
                $line->load($lineData, '');
                $lines[] = $line;
            }

            if ($this->saveTransaction($model, $lines)) {
                Yii::$app->session->setFlash('success', 'Transaction updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'lines' => $lines,
        ]);
    }

    /**
     * Deletes an existing JournalTrans model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Only allow delete if status is draft
        if ($model->status !== JournalTrans::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', 'Cannot delete approved or processed transactions.');
            return $this->redirect(['index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Delete lines first
            JournalTransLine::deleteAll(['journal_trans_id' => $id]);
            // Delete main record
            $model->delete();

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Transaction deleted successfully.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Error deleting transaction: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Approve transaction
     * @param integer $id
     * @return mixed
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== JournalTrans::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', 'Transaction cannot be approved.');
            return $this->redirect(['view', 'id' => $id]);
        }

        try {
            // Validate stock availability for outbound transactions
            if ($model->stock_type_id == JournalTrans::STOCK_TYPE_OUT) {
                foreach ($model->journalTransLines as $line) {
                    $availableStock = $line->product->getAvailableStockInWarehouse($model->warehouse_id);
                    if ($availableStock < $line->qty) {
                        throw new \Exception("Insufficient stock for product: {$line->product->name}. Available: {$availableStock}, Required: {$line->qty}");
                    }
                }
            }

            $model->approve();
            Yii::$app->session->setFlash('success', 'Transaction approved successfully.');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error approving transaction: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Get product info for AJAX
     * @param integer $id
     * @return array
     */
    public function actionGetProduct($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $product = Product::findOne($id);
        if ($product) {
            return [
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'sale_price' => $product->sale_price,
                    'stock_qty' => $product->stock_qty,
                ]
            ];
        }

        return ['success' => false, 'message' => 'Product not found'];
    }

    /**
     * Get stock in warehouse for AJAX
     * @param integer $productId
     * @param integer $warehouseId
     * @return array
     */
    public function actionGetStock($productId, $warehouseId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $stockSum = StockSum::find()
            ->where(['product_id' => $productId, 'warehouse_id' => $warehouseId])
            ->one();

        $stock = $stockSum ? $stockSum->qty : 0;
        $available = $stockSum ? $stockSum->getAvailableQty() : 0;

        return [
            'success' => true,
            'stock' => $stock,
            'available' => $available,
        ];
    }

    /**
     * Save transaction with lines
     * @param JournalTrans $model
     * @param array $lines
     * @return boolean
     */
    protected function saveTransaction($model, $lines)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Validate lines
            $valid = $model->validate();
            foreach ($lines as $line) {
                if (!$line->validate()) {
                    $valid = false;
                }
            }

            if (!$valid) {
                return false;
            }

            // Save main record
            if (!$model->save(false)) {
                throw new \Exception('Failed to save transaction');
            }

            // Delete existing lines if updating
            if (!$model->isNewRecord) {
                JournalTransLine::deleteAll(['journal_trans_id' => $model->id]);
            }

            // Save lines
            foreach ($lines as $line) {
                $line->journal_trans_id = $model->id;
                if (!$line->save(false)) {
                    throw new \Exception('Failed to save transaction line');
                }
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $model->addError('', $e->getMessage());
            return false;
        }
    }

    /**
     * Finds the JournalTrans model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return JournalTrans the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = JournalTrans::find()->with(['journalTransLines.product', 'stockTrans.product'])->where(['id' => $id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
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
                    'display' => $product->code . ($product->name ? ' (' . $product->name . ')' : '')
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

    public function actionGetProductStock()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $productId = \Yii::$app->request->get('product_id');

        if (!$productId) {
            return ['error' => 'Product ID required'];
        }

        // Query เพื่อดึงข้อมูลสต็อกจากทุกคลัง
        $stocks = (new \yii\db\Query())
            ->select([
                'w.id as warehouse_id',
                'w.name as warehouse_name',
                'COALESCE(s.qty, 0) as qty',
                'p.unit_id',
                'u.name as unit',
            ])
            ->from('warehouse w')
            ->leftJoin('stock_sum s', 'w.id = s.warehouse_id AND s.product_id = :product_id', [':product_id' => $productId])
            ->leftJoin('product p', 'p.id = :product_id', [':product_id' => $productId])
            ->leftJoin('unit u', 'u.id = p.unit_id')
            ->where(['w.status' => 1])
            ->all();

        return $stocks;
    }
}