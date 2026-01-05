<?php

namespace backend\controllers;

use app\behaviors\ActionLogBehavior;
use backend\models\StockTrans;
use backend\models\UnitSearch;
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
use yii\web\UploadedFile;

/**
 * JournalTransController implements the CRUD actions for JournalTransX model.
 */
class JournaltransController extends BaseController
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
                    'delete' => ['POST'],
                    'approve' => ['POST'],
                ],
            ],
            'actionLog' => [
                'class' => ActionLogBehavior::class,
                'actions' => ['create', 'update', 'delete', 'view', 'print', 'approve', 'createorigin'],
            ],
        ];
    }

    /**
     * Lists all JournalTransX models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new \backend\models\JournalTransSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        $dataProvider->setSort(['defaultOrder' => ['id' => SORT_DESC]]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single JournalTransX model.
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
     * Updates an existing JournalTrans model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $lines = $model->journalTransLines;

        if ($model->status !== JournalTrans::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', 'Only draft transactions can be updated.');
            return $this->redirect(['view', 'id' => $id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            $journalTransLines = [];
            $valid = $model->validate();

            if (isset($_POST['JournalTransLine'])) {
                foreach ($_POST['JournalTransLine'] as $index => $journalTransLineData) {
                    $journalTransLine = new JournalTransLine();
                    $journalTransLine->load($journalTransLineData, '');
                    $journalTransLines[] = $journalTransLine;
                    $valid = $journalTransLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $model->updated_at = date('Y-m-d H:i:s');
                    $model->updated_by = \Yii::$app->user->id;
                    if ($model->save()) {
                        // Delete old lines
                        JournalTransLine::deleteAll(['journal_trans_id' => $model->id]);

                        foreach ($journalTransLines as $journalTransLine) {
                            $journalTransLine->journal_trans_id = $model->id;
                            $journalTransLine->status = 0;
                            if (!$journalTransLine->save(false)) {
                                throw new \Exception('Failed to save journal trans line');
                            }
                        }

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกข้อมูลได้: ' . $e->getMessage());
                }
            }
        }

        $useOrigin = in_array($model->trans_type_id, [JournalTrans::TRANS_TYPE_ISSUE_STOCK, JournalTrans::TRANS_TYPE_ISSUE_BORROW]);

        return $this->render($useOrigin ? 'updateorigin' : 'update', [
            'model' => $model,
            'lines' => $lines,
        ]);
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
                    'display' => $product->code . ($product->name ? ' (' . $product->name . ')' : ''),
                    'unit_id' => $product->unit_id,
                    'unit_name' => \backend\models\Unit::findName($product->unit_id),
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

    /**
     * Get transaction lines for return transactions (AJAX)
     * @return array
     */
    public function actionGetTransactionLines()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $transactionId = Yii::$app->request->get('transaction_id');

        if (!$transactionId) {
            return ['success' => false, 'message' => 'Transaction ID required'];
        }

        $transaction = JournalTrans::find()
            ->with(['journalTransLines.product.unit', 'journalTransLines.warehouse'])
            ->where(['id' => $transactionId])
            ->andWhere(['status' => JournalTrans::STATUS_APPROVED])
            ->one();

        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found or not approved'];
        }

        $lines = [];
        foreach ($transaction->journalTransLines as $line) {
            // คำนวณจำนวนที่สามารถคืนได้
            $availableReturnQty = $this->getAvailableReturnQty($transactionId, $line->product_id);

            $lines[] = [
                'id' => $line->id,
                'product_id' => $line->product_id,
                'product_code' => $line->product->code ?? '',
                'product_name' => $line->product->name ?? '',
                'warehouse_id' => $line->warehouse_id,
                'warehouse_name' => $line->warehouse->name ?? '',
                'qty' => $line->qty,
                'available_return_qty' => $availableReturnQty,
                'unit_id' => $line->product->unit_id ?? '',
                'unit_name' => $line->product->unit->name ?? '',
                'line_price' => $line->line_price,
            ];
        }

        return [
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'journal_no' => $transaction->journal_no,
                'trans_date' => $transaction->trans_date,
                'trans_type_name' => JournalTrans::getTransTypeOptions()[$transaction->trans_type_id] ?? '',
                'customer_name' => $transaction->customer_name,
                'status' => $transaction->status,
                'lines' => $lines
            ]
        ];
    }

    /**
     * Get available return quantity for a product from a transaction
     * @param int $originalTransId
     * @param int $productId
     * @return float
     */
    protected function getAvailableReturnQty($originalTransId, $productId)
    {
        // ดึงจำนวนที่เบิกไปจาก transaction ต้นฉบับ
        $originalQty = JournalTransLine::find()
            ->where(['journal_trans_id' => $originalTransId, 'product_id' => $productId])
            ->sum('qty') ?: 0;

        // ดึงจำนวนที่คืนไปแล้วจาก return transactions
        $returnedQty = JournalTransLine::find()
            ->joinWith('journalTrans')
            ->where([
                'journal_trans_line.product_id' => $productId,
                'journal_trans.return_for_trans_id' => $originalTransId,
                'journal_trans.status' => JournalTrans::STATUS_APPROVED
            ])
            ->sum('journal_trans_line.qty') ?: 0;

        return $originalQty - $returnedQty;
    }

    /**
     * Creates a new JournalTrans model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new JournalTrans();
        $model->trans_date = date('Y-m-d');
        $lines = [new JournalTransLine()];

        if ($model->load(Yii::$app->request->post())) {
            $journalTransLines = [];
            $valid = $model->validate();

            if (isset($_POST['JournalTransLine'])) {
                foreach ($_POST['JournalTransLine'] as $index => $journalTransLineData) {
                    $journalTransLine = new JournalTransLine();
                    $journalTransLine->load($journalTransLineData, '');
                    $journalTransLines[] = $journalTransLine;
                    $valid = $journalTransLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $model->created_at = date('Y-m-d H:i:s');
                    $model->created_by = \Yii::$app->user->id;
                    if ($model->save()) {
                        foreach ($journalTransLines as $journalTransLine) {
                            $journalTransLine->journal_trans_id = $model->id;
                            $journalTransLine->status = 0;
                            if (!$journalTransLine->save(false)) {
                                throw new \Exception('Failed to save journal trans line');
                            }
                        }

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกข้อมูลได้: ' . $e->getMessage());
                }
            } else {
                 // แสดง validation errors
                $errors = [];
                if ($model->hasErrors()) {
                    foreach ($model->errors as $attribute => $attributeErrors) {
                        $errors = array_merge($errors, $attributeErrors);
                    }
                }

                foreach ($journalTransLines as $index => $line) {
                    if ($line->hasErrors()) {
                        foreach ($line->errors as $attribute => $attributeErrors) {
                            foreach ($attributeErrors as $error) {
                                $errors[] = "รายการที่ " . ($index + 1) . " - {$attribute}: {$error}";
                            }
                        }
                    }
                }

                if (!empty($errors)) {
                    Yii::$app->session->setFlash('error', 'พบข้อผิดพลาด: ' . implode('<br>', $errors));
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'lines' => (empty($journalTransLines)) ? $lines : $journalTransLines,
        ]);
    }

    public function actionCreateorigin()
    {
        $model = new JournalTrans();
        $lines = [new JournalTransLine()];

        if ($model->load(Yii::$app->request->post())) {
            $journalTransLines = [];
            $valid = $model->validate();

            if (isset($_POST['JournalTransLine'])) {
                foreach ($_POST['JournalTransLine'] as $index => $journalTransLineData) {
                    $journalTransLine = new JournalTransLine();
                    $journalTransLine->load($journalTransLineData, '');
                    $journalTransLines[] = $journalTransLine;
                    $valid = $journalTransLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $model->created_at = date('Y-m-d H:i:s');
                    $model->created_by = \Yii::$app->user->id;
                    if ($model->save()) {
                        foreach ($journalTransLines as $journalTransLine) {
                            $journalTransLine->journal_trans_id = $model->id;
                            $journalTransLine->status = 0;
                            if (!$journalTransLine->save(false)) {
                                throw new \Exception('Failed to save journal trans line');
                            }
                        }
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกข้อมูลได้: ' . $e->getMessage());
                }
            }
        }

        return $this->render('createorigin', [
            'model' => $model,
            'lines' => (empty($journalTransLines)) ? $lines : $journalTransLines,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
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
                $checkStock = [];
                foreach ($model->journalTransLines as $line) {
                    $key = $line->product_id . '_' . $line->warehouse_id;
                    if (!isset($checkStock[$key])) {
                        $checkStock[$key] = [
                            'product_name' => $line->product->name ?? 'Unknown',
                            'qty' => 0,
                            'product_model' => $line->product,
                            'warehouse_id' => $line->warehouse_id
                        ];
                    }
                    $checkStock[$key]['qty'] += $line->qty;
                }

                foreach ($checkStock as $check) {
                    if ($check['product_model']) {
                        $availableStock = $check['product_model']->getAvailableStockInWarehouse($check['warehouse_id']);
                        if ($availableStock < $check['qty']) {
                            throw new \Exception("Insufficient stock for product: {$check['product_name']}. Available: {$availableStock}, Required: {$check['qty']}");
                        }
                    }
                }
            }

            if ($model->approve()) {
                Yii::$app->session->setFlash('success', 'Transaction approved successfully.');
            } else {
                throw new \Exception("Failed to approve transaction.");
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error approving transaction: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Cancel the entire transaction and reverse all stock movements.
     * @param integer $id
     * @return mixed
     */
    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== JournalTrans::STATUS_APPROVED) {
            Yii::$app->session->setFlash('error', 'Only approved transactions can be cancelled.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($model->journalTransLines as $line) {
                if ($line->status != JournalTransLine::STATUS_CANCELLED) {
                    $reverseStockType = ($model->stock_type_id == JournalTrans::STOCK_TYPE_IN) ? JournalTrans::STOCK_TYPE_OUT : JournalTrans::STOCK_TYPE_IN;
                    
                    $this->adjustStock($line->product_id, $line->warehouse_id, $line->qty, $reverseStockType, "Cancel Transaction: " . $model->journal_no);
                    
                    $stockTrans = new StockTrans();
                    $stockTrans->journal_trans_id = $model->id;
                    $stockTrans->product_id = $line->product_id;
                    $stockTrans->warehouse_id = $line->warehouse_id;
                    $stockTrans->qty = $line->qty;
                    $stockTrans->stock_type_id = $reverseStockType;
                    $stockTrans->trans_type_id = $model->trans_type_id; 
                    $stockTrans->created_at = date('Y-m-d H:i:s');
                    $stockTrans->created_by = Yii::$app->user->id;
                    if (!$stockTrans->save(false)) {
                        throw new \Exception("Failed to save stock transaction history.");
                    }

                    $line->status = JournalTransLine::STATUS_CANCELLED;
                    if (!$line->save(false)) {
                        throw new \Exception("Failed to update line status.");
                    }
                }
            }

            // Update main transaction status
            $model->status = JournalTrans::STATUS_CANCELLED;
            $model->updated_by = Yii::$app->user->id;
            $model->updated_at = date('Y-m-d H:i:s');
            if (!$model->save(false)) {
                throw new \Exception("Failed to update transaction status.");
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Transaction cancelled successfully.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Error cancelling transaction: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Cancel a single line item from a transaction.
     * @param integer $id
     * @return mixed
     */
    public function actionCancelLine($id)
    {
        $line = JournalTransLine::findOne($id);
        if (!$line) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $model = $line->journalTrans;
        if ($model->status !== JournalTrans::STATUS_APPROVED) {
             Yii::$app->session->setFlash('error', 'Only approved transactions can be cancelled.');
             return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($line->status == JournalTransLine::STATUS_CANCELLED) {
             Yii::$app->session->setFlash('warning', 'This item is already cancelled.');
             return $this->redirect(['view', 'id' => $model->id]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $reverseStockType = ($model->stock_type_id == JournalTrans::STOCK_TYPE_IN) ? JournalTrans::STOCK_TYPE_OUT : JournalTrans::STOCK_TYPE_IN;
            
            $this->adjustStock($line->product_id, $line->warehouse_id, $line->qty, $reverseStockType, "Cancel Line Item: " . $line->product->code);
            
            $stockTrans = new StockTrans();
            $stockTrans->journal_trans_id = $model->id;
            $stockTrans->product_id = $line->product_id;
            $stockTrans->warehouse_id = $line->warehouse_id;
            $stockTrans->qty = $line->qty;
            $stockTrans->stock_type_id = $reverseStockType;
            $stockTrans->trans_type_id = $model->trans_type_id;
            $stockTrans->created_at = date('Y-m-d H:i:s');
            $stockTrans->created_by = Yii::$app->user->id;
            if (!$stockTrans->save(false)) {
                throw new \Exception("Failed to save stock transaction history.");
            }

            $line->status = JournalTransLine::STATUS_CANCELLED;
            if (!$line->save(false)) {
                throw new \Exception("Failed to update line status.");
            }

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Item cancelled successfully.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Error cancelling item: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Adjust stock quantity.
     * @param int $product_id
     * @param int $warehouse_id
     * @param float $qty
     * @param int $stock_type_id
     * @param string $remark
     * @throws \Exception
     */
    protected function adjustStock($product_id, $warehouse_id, $qty, $stock_type_id, $remark = null)
    {
        $stockSum = StockSum::find()->where(['product_id' => $product_id, 'warehouse_id' => $warehouse_id])->one();
        if (!$stockSum) {
            $stockSum = new StockSum();
            $stockSum->product_id = $product_id;
            $stockSum->warehouse_id = $warehouse_id;
            $stockSum->qty = 0;
        }

        if ($stock_type_id == JournalTrans::STOCK_TYPE_IN) {
            $stockSum->qty = ($stockSum->qty ?: 0) + $qty;
        } elseif ($stock_type_id == JournalTrans::STOCK_TYPE_OUT) {
             if (($stockSum->qty ?: 0) < $qty) {
                 throw new \Exception("Insufficient stock for product ID $product_id in warehouse $warehouse_id to perform cancellation.");
             }
            $stockSum->qty = ($stockSum->qty ?: 0) - $qty;
        }

        if (!$stockSum->save()) {
            throw new \Exception("Failed to update stock summary.");
        }
        
        $this->updateProductStock($product_id);
    }

    public function createStocktrans($journalTransLines,$stock_trans_type,$stock_type_id)
    {
        if (empty($journalTransLines) || $journalTransLines == null) {
            return; // Optionally, log or handle this scenario.
        }

        foreach ($journalTransLines as $journalTransLine) {
            $model = new StockTrans();
            $model->trans_date = date('Y-m-d H:i:s'); // Datetime
            $model->journal_trans_id = (int)$journalTransLine->journal_trans_id; // Integer
            $model->product_id = (int)$journalTransLine->product_id; // Integer
            $model->warehouse_id = (int)$journalTransLine->warehouse_id; // Integer
            $model->qty = (float)$journalTransLine->qty; // Float
            $model->trans_type_id = $stock_trans_type; // Integer
            $model->created_at = date('Y-m-d H:i:s'); // Datetime
            $model->created_by = Yii::$app->user->id; // Integer
            $model->updated_at = date('Y-m-d H:i:s'); // Datetime
         //   $model->updated_by = Yii::$app->user->id; // Integer
            $model->stock_type_id = $stock_type_id;
            $model->save(false);
        }
        return true;
    }

    public function calStock($product_id, $qty, $warehouse_id, $trans_type_id)
    {
        $res = 0;
        if ($product_id && $qty && $warehouse_id) {
            $stock_sum = \backend\models\StockSum::find()
                ->where(['product_id' => $product_id, 'warehouse_id' => $warehouse_id])
                ->one();

            if ($stock_sum) {
                if ($trans_type_id == 3 || $trans_type_id == 5) {
                    // ตัดสต็อก
                    if ($stock_sum->qty >= $qty) {
                        $stock_sum->qty = ($stock_sum->qty ?: 0) - $qty;
                    } else {
                        return 0; // ❗ สต็อกไม่พอ
                    }
                } else {
                    // เพิ่มสต็อก
                    $stock_sum->qty = ($stock_sum->qty ?: 0) + $qty;
                }

                if ($stock_sum->save(false)) {
                    $res = 1;
                }
            } else {
                // ถ้าไม่มี record เดิม
                if ($trans_type_id == 3 || $trans_type_id == 5) {
                    $res = 0; // ❗ ไม่มีสินค้าให้ตัด
                } else {
                    $stock_new = new \backend\models\StockSum();
                    $stock_new->product_id = $product_id;
                    $stock_new->warehouse_id = $warehouse_id;
                    $stock_new->qty = $qty;
                    if ($stock_new->save(false)) {
                        $res = 1;
                    }
                }
            }

            $this->updateProductStock($product_id);
        }

        return $res;
    }

    function updateProductStock($product_id)
    {
        if ($product_id) {
            $total_stock = \backend\models\StockSum::find()
                ->where(['product_id' => $product_id])
                ->sum('qty');

            \backend\models\Product::updateAll(
                ['stock_qty' => $total_stock ?: 0],
                ['id' => $product_id]
            );
        }
    }

    public function actionPrint($id)
    {
        if ($id) {
            $model = JournalTrans::findOne($id);
            $model_line = JournalTransLine::find()->where(['journal_trans_id' => $id])->all();
            return $this->render('_printissue', [
                'model' => $model,
                'model_line' => $model_line
            ]);
        }
    }

    // In your controller
    public function actionPrintIssue($id = null)
    {
        //$this->layout = 'print'; // Use minimal print layout
        return $this->render('_printissue');
    }

    public function actionPrintPickupOut($id = null)
    {
        //  $this->layout = 'print'; // Use minimal print layout
        return $this->render('_printpickupout');
    }

    public function actionAddDocFile()
    {
        $id = \Yii::$app->request->post('id');
        if ($id) {
            $uploaded = UploadedFile::getInstancesByName('file_doc');
            if (!empty($uploaded)) {
                $loop = 0;
                foreach ($uploaded as $file) {
                    $upfiles = "worker_" . time() . "_" . $loop . "." . $file->getExtension();
                    if ($file->saveAs('uploads/journal_trans_doc/' . $upfiles)) {
                        $model_doc = new \common\models\JournalTransDoc();
                        $model_doc->journal_trans_id = $id;
                        $model_doc->doc_name = $upfiles;
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

    public function actionDeleteDocFile()
    {
        $id = \Yii::$app->request->post('id');
        $doc_delete_list = trim(\Yii::$app->request->post('doc_delete_list'));
        if ($id) {
            $model_doc = \common\models\JournalTransDoc::find()->where(['journal_trans_id' => $id, 'doc_name' => $doc_delete_list])->one();
            if ($model_doc) {
                if ($model_doc->delete()) {
                    if (file_exists('uploads/journal_trans_doc/' . $model_doc->doc_name)) {
                        unlink('uploads/journal_trans_doc/' . $model_doc->doc_name);
                    }
                }
            }
        }
        return $this->redirect(['update', 'id' => $id]);
    }

    /**
     * Finds the JournalTransX model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return JournalTrans the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = \backend\models\JournalTrans::find()->with(['journalTransLines.product'])->where(['id' => $id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}