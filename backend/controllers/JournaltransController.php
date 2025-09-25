<?php

namespace backend\controllers;

use app\behaviors\ActionLogBehavior;
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
class JournaltransController extends Controller
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
                'actions' => ['create', 'update', 'delete','view','print', 'approve','createorigin'], // Log เฉพาะ actions เหล่านี้
            ],
        ];
    }

    /**
     * Lists all JournalTransX models.
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
     * Creates a new JournalTransX model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
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

//                    if (!$journalTransLine->validate()) {
//                        $valid = false;
//                        foreach ($journalTransLine->errors as $attribute => $errors) {
//                            foreach ($errors as $error) {
//                                $errorMessages[] = "แถวที่ " . ($index + 1) . " - {$attribute}: {$error}";
//                            }
//                        }
//                    }
                }
//                if (!$valid) {
//                    print_r($errorMessages);return;
//                }
            }

            if($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        foreach ($journalTransLines as $journalTransLine) {
                            $journalTransLine->journal_trans_id = $model->id;
                            $journalTransLine->status = 0;
                            if($journalTransLine->save(false)){
                                $trans_type_id = 3;
                                 $this->calStock($journalTransLine->product_id,$journalTransLine->qty,$journalTransLine->warehouse_id,$trans_type_id);
                            }else{
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
            }else{
                Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกข้อมูลได้: ' . json_encode($model->errors));
            }

//            if ($this->saveTransaction($model, $lines)) {
//                Yii::$app->session->setFlash('success', 'Transaction created successfully.');
//                return $this->redirect(['view', 'id' => $model->id]);
//            }
        }

        return $this->render('createorigin', [
            'model' => $model,
            'lines' => $lines,
        ]);
    }

    /**
     * Updates an existing JournalTransX model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//
//        // Only allow edit if status is draft
//        if ($model->status !== JournalTransX::STATUS_DRAFT) {
//            Yii::$app->session->setFlash('error', 'Cannot edit approved or processed transactions.');
//            return $this->redirect(['view', 'id' => $model->id]);
//        }
//
//        $lines = $model->journalTransLines;
//        if (empty($lines)) {
//            $lines = [new JournalTransLineX()];
//        }
//
//        if ($model->load(Yii::$app->request->post())) {
//            $linesData = Yii::$app->request->post('JournalTransLineX', []);
//            $lines = [];
//
//            foreach ($linesData as $lineData) {
//                if (!empty($lineData['id'])) {
//                    $line = JournalTransLineX::findOne($lineData['id']);
//                } else {
//                    $line = new JournalTransLineX();
//                }
//                $line->load($lineData, '');
//                $lines[] = $line;
//            }
//
//            if ($this->saveTransaction($model, $lines)) {
//                Yii::$app->session->setFlash('success', 'Transaction updated successfully.');
//                return $this->redirect(['view', 'id' => $model->id]);
//            }
//        }
//
//        return $this->render('update', [
//            'model' => $model,
//            'lines' => $lines,
//        ]);
//    }

//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//
//        // Only allow edit if status is draft
//        if ($model->status !== JournalTransX::STATUS_DRAFT) {
//            Yii::$app->session->setFlash('error', 'Cannot edit approved or processed transactions.');
//            return $this->redirect(['view', 'id' => $model->id]);
//        }
//
//        // Load existing journal trans lines
//        $model->journalTransLines = $model->getJournalTransLines()->all();
//        if (empty($model->journalTransLines)) {
//            $model->journalTransLines = [new JournalTransLineX()];
//        }
//
//        if ($model->load(Yii::$app->request->post())) {
//            $journalTransLines = [];
//            $valid = $model->validate();
//
//            if (isset($_POST['JournalTransLineX'])) {
//                foreach ($_POST['JournalTransLineX'] as $index => $journalTransLineData) {
//                    if (isset($journalTransLineData['id']) && !empty($journalTransLineData['id'])) {
//                        // Update existing line
//                        $journalTransLine = JournalTransLineX::findOne($journalTransLineData['id']);
//                        if (!$journalTransLine) {
//                            $journalTransLine = new JournalTransLineX();
//                        }
//                    } else {
//                        // Create new line
//                        $journalTransLine = new JournalTransLineX();
//                    }
//                    $journalTransLine->load($journalTransLineData, '');
//                    $journalTransLines[] = $journalTransLine;
//                    $valid = $journalTransLine->validate() && $valid;
//                }
//            }
//
//            if ($valid) {
//                $transaction = Yii::$app->db->beginTransaction();
//                try {
//                    if ($model->save()) {
//                        // Delete existing lines that are not in the new list
//                        $existingLineIds = [];
//                        foreach ($journalTransLines as $journalTransLine) {
//                            if (!$journalTransLine->isNewRecord) {
//                                $existingLineIds[] = $journalTransLine->id;
//                            }
//                        }
//
//                        // Delete lines that are not in the updated list
//                        if (!empty($existingLineIds)) {
//                            JournalTransLineX::deleteAll([
//                                'and',
//                                ['journal_trans_id' => $model->id],
//                                ['not in', 'id', $existingLineIds]
//                            ]);
//                        } else {
//                            // If no existing lines, delete all lines for this transaction
//                            JournalTransLineX::deleteAll(['journal_trans_id' => $model->id]);
//                        }
//
//                        // Save journal trans lines
//                        foreach ($journalTransLines as $journalTransLine) {
//                            $journalTransLine->journal_trans_id = $model->id;
//                            if (!$journalTransLine->save()) {
//                                throw new \Exception('Failed to save journal trans line');
//                            }
//                        }
//
//                        $transaction->commit();
//                        Yii::$app->session->setFlash('success', 'Transaction updated successfully.');
//                        return $this->redirect(['view', 'id' => $model->id]);
//                    }
//                } catch (\Exception $e) {
//                    $transaction->rollBack();
//                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
//                }
//            }
//        }
//
//        return $this->render('update', [
//            'model' => $model,
//        ]);
//    }

    /**
     * Deletes an existing JournalTransX model.
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
                    $availableStock = $line->product->getAvailableStockInWarehouse($line->warehouse_id);
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
            ->andWhere(['status' => 'approved'])
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
                'journal_trans.status' => 'approved'
            ])
            ->sum('journal_trans_line.qty') ?: 0;

        return $originalQty - $returnedQty;
    }

    /**
     * Enhanced actionUpdate with return transaction support
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Only allow edit if status is draft
        if ($model->status !== JournalTrans::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', 'Cannot edit approved or processed transactions.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Load existing journal trans lines
        $model->journalTransLinesline = $model->getJournalTransLines()->all();
        if (empty($model->journalTransLines)) {
            $model->journalTransLinesline = [new JournalTransLine()];
        }

        if ($model->load(Yii::$app->request->post())) {
            $journalTransLines = [];
            $valid = $model->validate();

            if (isset($_POST['JournalTransLine'])) {
                foreach ($_POST['JournalTransLine'] as $index => $journalTransLineData) {
                    if (isset($journalTransLineData['id']) && !empty($journalTransLineData['id'])) {
                        // Update existing line
                        $journalTransLine = JournalTransLine::findOne($journalTransLineData['id']);
                        if (!$journalTransLine) {
                            $journalTransLine = new JournalTransLine();
                        }
                    } else {
                        // Create new line
                        $journalTransLine = new JournalTransLine();
                    }

                    $journalTransLine->load($journalTransLineData, '');

                    // สำหรับ return transaction ให้ตรวจสอบและบันทึกข้อมูลสภาพสินค้า
                    if (in_array($model->trans_type_id, [JournalTrans::TRANS_TYPE_RETURN_ISSUE, JournalTrans::TRANS_TYPE_RETURN_BORROW])) {
                        $this->processReturnLineData($journalTransLine, $journalTransLineData);

                        // ตรวจสอบจำนวนที่สามารถคืนได้
                        if ($model->return_for_trans_id) {
                            $availableQty = $this->getAvailableReturnQty($model->return_for_trans_id, $journalTransLine->product_id);
                            if ($journalTransLine->qty > $availableQty) {
                                $journalTransLine->addError('qty', "จำนวนที่คืน ({$journalTransLine->qty}) เกินจำนวนที่สามารถคืนได้ ({$availableQty})");
                                $valid = false;
                            }
                        }
                    }

                    $journalTransLines[] = $journalTransLine;
                    $valid = $journalTransLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // Delete existing lines that are not in the new list
                        $existingLineIds = [];
                        foreach ($journalTransLines as $journalTransLine) {
                            if (!$journalTransLine->isNewRecord) {
                                $existingLineIds[] = $journalTransLine->id;
                            }
                        }

                        // Delete lines that are not in the updated list
                        if (!empty($existingLineIds)) {
                            JournalTransLine::deleteAll([
                                'and',
                                ['journal_trans_id' => $model->id],
                                ['not in', 'id', $existingLineIds]
                            ]);
                        } else {
                            // If no existing lines, delete all lines for this transaction
                            JournalTransLine::deleteAll(['journal_trans_id' => $model->id]);
                        }

                        // Save journal trans lines
                        foreach ($journalTransLines as $journalTransLine) {
                            $journalTransLine->journal_trans_id = $model->id;
                            if (!$journalTransLine->save()) {
                                throw new \Exception('Failed to save journal trans line');
                            }
                        }

                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'Transaction updated successfully.');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
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

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Process return line data including condition information
     * @param JournalTransLine $line
     * @param array $data
     */
    protected function processReturnLineData($line, $data)
    {
        // บันทึกข้อมูลสภาพสินค้าสำหรับ return borrow
        if (isset($data['good_qty'])) {
            $line->good_qty = (float)$data['good_qty'];
        }
        if (isset($data['damaged_qty'])) {
            $line->damaged_qty = (float)$data['damaged_qty'];
        }
        if (isset($data['missing_qty'])) {
            $line->missing_qty = (float)$data['missing_qty'];
        }
        if (isset($data['condition_note'])) {
            $line->condition_note = $data['condition_note'];
        }
        if (isset($data['return_note'])) {
            $line->return_note = $data['return_note'];
        }

        // กำหนด return_to_type ตามสภาพสินค้า
        if ($line->good_qty > 0 && $line->damaged_qty == 0 && $line->missing_qty == 0) {
            $line->return_to_type = JournalTransLine::RETURN_TYPE_COMPLETE;
        } elseif ($line->damaged_qty > 0) {
            $line->return_to_type = JournalTransLine::RETURN_TYPE_DAMAGED;
        } elseif ($line->missing_qty > 0) {
            $line->return_to_type = JournalTransLine::RETURN_TYPE_INCOMPLETE;
        }
    }

    /**
     * Enhanced actionCreate with return transaction support
     */
    public function actionCreate()
    {
        $model = new JournalTrans();
        $lines = [new JournalTransLine()];

        if ($model->load(Yii::$app->request->post())) {
            $journalTransLines = [];
            $valid = $model->validate();

            if (isset($_POST['JournalTransLine'])) {
               // print_r($_POST['JournalTransLine']);return;
                foreach ($_POST['JournalTransLine'] as $index => $journalTransLineData) {
                    $journalTransLine = new JournalTransLine();
                    $journalTransLine->load($journalTransLineData, '');

                    // สำหรับ return transaction
                    if (in_array($model->trans_type_id, [JournalTrans::TRANS_TYPE_RETURN_ISSUE, JournalTrans::TRANS_TYPE_RETURN_BORROW])) {
                        $this->processReturnLineData($journalTransLine, $journalTransLineData);

                        // ตรวจสอบจำนวนที่สามารถคืนได้
                        if ($model->return_for_trans_id) {
                            $availableQty = $this->getAvailableReturnQty($model->return_for_trans_id, $journalTransLine->product_id);
                            if ($journalTransLine->qty > $availableQty) {
                                $journalTransLine->addError('qty', "จำนวนที่คืน ({$journalTransLine->qty}) เกินจำนวนที่สามารถคืนได้ ({$availableQty})");
                                $valid = false;
                            }
                        }
                    }

                    $journalTransLines[] = $journalTransLine;
                    $valid = $journalTransLine->validate() && $valid;
                }
            }

            if($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $model->created_at = date('Y-m-d H:i:s');
                    $model->created_by = \Yii::$app->user->id;
                    if ($model->save()) {
                        foreach ($journalTransLines as $journalTransLine) {
                            $journalTransLine->journal_trans_id = $model->id;
                            $journalTransLine->status = 0;
                            if($journalTransLine->save(false)){
                                $trans_type_id = 4;
                               $this->calStock($journalTransLine->product_id,$journalTransLine->qty,$journalTransLine->warehouse_id,$trans_type_id);
                            }else{
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
            'lines' => $lines,
        ]);
    }

    public function calStock($product_id, $qty, $warehouse_id, $trans_type_id) {
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

    function updateProductStock($product_id) {
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

    public function actionPrint($id){
        if($id){
            $model = JournalTrans::findOne($id);
            $model_line = JournalTransLine::find()->where(['journal_trans_id'=>$id])->all();
            return $this->render('_printissue',[
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

    public function actionAddDocFile(){
        $id = \Yii::$app->request->post('id');
        if($id){
            $uploaded = UploadedFile::getInstancesByName('file_doc');
            if (!empty($uploaded)) {
                $loop = 0;
                foreach ($uploaded as $file) {
                    $upfiles = "worker_" . time()."_".$loop . "." . $file->getExtension();
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
    public function actionDeleteDocFile(){
        $id = \Yii::$app->request->post('id');
        $doc_delete_list = trim(\Yii::$app->request->post('doc_delete_list'));
        if($id){
            $model_doc = \common\models\JournalTransDoc::find()->where(['journal_trans_id' => $id,'doc_name' => $doc_delete_list])->one();
            if($model_doc){
                if($model_doc->delete()){
                    if(file_exists('uploads/journal_trans_doc/'.$model_doc->doc_name)){
                        unlink('uploads/journal_trans_doc/'.$model_doc->doc_name);
                    }
                }
            }
        }
        return $this->redirect(['update', 'id' => $id]);
    }
}