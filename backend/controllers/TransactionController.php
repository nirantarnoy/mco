<?php
namespace backend\controllers;

use Yii;
use backend\models\JournalTrans;
use backend\models\JournalTransLine;
use backend\models\StockTrans;
use backend\models\StockSum;
use backend\models\Product;
use backend\models\Warehouse;
use backend\models\TransactionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\data\ActiveDataProvider;

/**
 * TransactionController implements stock transaction management
 */
class TransactionController extends Controller
{
    // Transaction Types
    const TRANS_TYPE_PO_RECEIVE = 1;
    const TRANS_TYPE_CANCEL_PO_RECEIVE = 2;
    const TRANS_TYPE_ISSUE_STOCK = 3;
    const TRANS_TYPE_RETURN_ISSUE = 4;
    const TRANS_TYPE_ISSUE_BORROW = 5;
    const TRANS_TYPE_RETURN_BORROW = 6;

    // Stock Types
    const STOCK_TYPE_IN = 1;
    const STOCK_TYPE_OUT = 2;

    // Approval Status
    const APPROVE_STATUS_PENDING = 0;
    const APPROVE_STATUS_APPROVED = 1;
    const APPROVE_STATUS_REJECTED = 2;

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
                    'reject' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all transactions
     */
    public function actionIndex()
    {
        $searchModel = new TransactionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single transaction
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Issue Stock (เบิกสินค้า)
     */
    public function actionIssueStock()
    {
        $model = new JournalTrans();
        $model->trans_type_id = self::TRANS_TYPE_ISSUE_STOCK;
        $model->stock_type_id = self::STOCK_TYPE_OUT;
        $model->trans_date = date('Y-m-d H:i:s');
        $model->status = 0; // Pending approval

        if ($model->load(Yii::$app->request->post())) {
            $items = Yii::$app->request->post('items', []);
            $result = $this->processTransaction($model, $items);

            if ($result['success']) {
                Yii::$app->session->setFlash('success', $result['message']);
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', $result['message']);
            }
        }

        return $this->render('issue-stock', [
            'model' => $model,
            'warehouses' => Warehouse::getWarehouseList(),
            'products' => Product::getProductList(),
        ]);
    }

    /**
     * Return Issue (คืนเบิก)
     */
    public function actionReturnIssue()
    {
        $model = new JournalTrans();
        $model->trans_type_id = self::TRANS_TYPE_RETURN_ISSUE;
        $model->stock_type_id = self::STOCK_TYPE_IN;
        $model->trans_date = date('Y-m-d H:i:s');
        $model->status = 0; // Pending approval

        if ($model->load(Yii::$app->request->post())) {
            $items = Yii::$app->request->post('items', []);
            $result = $this->processTransaction($model, $items);

            if ($result['success']) {
                Yii::$app->session->setFlash('success', $result['message']);
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', $result['message']);
            }
        }

        return $this->render('return-issue', [
            'model' => $model,
            'warehouses' => Warehouse::getWarehouseList(),
            'products' => Product::getProductList(),
        ]);
    }

    /**
     * Issue Borrow (ยืม)
     */
    public function actionIssueBorrow()
    {
        $model = new JournalTrans();
        $model->trans_type_id = self::TRANS_TYPE_ISSUE_BORROW;
        $model->stock_type_id = self::STOCK_TYPE_OUT;
        $model->trans_date = date('Y-m-d H:i:s');
        $model->status = 0; // Pending approval

        if ($model->load(Yii::$app->request->post())) {
            $items = Yii::$app->request->post('items', []);
            $result = $this->processTransaction($model, $items);

            if ($result['success']) {
                Yii::$app->session->setFlash('success', $result['message']);
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', $result['message']);
            }
        }

        return $this->render('issue-borrow', [
            'model' => $model,
            'warehouses' => Warehouse::getWarehouseList(),
            'products' => Product::getProductList(),
        ]);
    }

    /**
     * Return Borrow (คืนยืม)
     */
    public function actionReturnBorrow()
    {
        $model = new JournalTrans();
        $model->trans_type_id = self::TRANS_TYPE_RETURN_BORROW;
        $model->stock_type_id = self::STOCK_TYPE_IN;
        $model->trans_date = date('Y-m-d H:i:s');
        $model->status = 0; // Pending approval

        if ($model->load(Yii::$app->request->post())) {
            $items = Yii::$app->request->post('items', []);
            $result = $this->processReturnBorrow($model, $items);

            if ($result['success']) {
                Yii::$app->session->setFlash('success', $result['message']);
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', $result['message']);
            }
        }

        return $this->render('return-borrow', [
            'model' => $model,
            'warehouses' => Warehouse::getWarehouseList(),
            'products' => Product::getProductList(),
        ]);
    }

    /**
     * Process transaction
     */
    private function processTransaction($model, $items)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Generate journal number
            $model->journal_no = $this->generateJournalNumber($model->trans_type_id);

            // Calculate total quantity and amount
            $totalQty = 0;
            $totalAmount = 0;
            $validItems = [];

            foreach ($items as $item) {
                if (!empty($item['product_id']) && !empty($item['qty']) && $item['qty'] > 0) {
                    $product = Product::findOne($item['product_id']);
                    if ($product) {
                        $item['sale_price'] = $product->sale_price;
                        $item['line_total'] = $item['qty'] * $product->sale_price;
                        $totalQty += $item['qty'];
                        $totalAmount += $item['line_total'];
                        $validItems[] = $item;
                    }
                }
            }

            if (empty($validItems)) {
                throw new \Exception('กรุณาระบุรายการสินค้า');
            }

            $model->qty = $totalQty;

            if (!$model->save()) {
                throw new \Exception('ไม่สามารถบันทึก Journal Transaction ได้: ' . implode(', ', $model->getFirstErrors()));
            }

            // Save transaction lines
            foreach ($validItems as $item) {
                $line = new JournalTransLine();
                $line->journal_trans_id = $model->id;
                $line->product_id = $item['product_id'];
                $line->warehouse_id = $model->warehouse_id;
                $line->qty = $item['qty'];
                $line->remark = $item['remark'] ?? '';

                if (!$line->save()) {
                    throw new \Exception('ไม่สามารถบันทึก Transaction Line ได้');
                }

                // Create stock transaction
                $stockTrans = new StockTrans();
                $stockTrans->journal_trans_id = $model->id;
                $stockTrans->trans_date = $model->trans_date;
                $stockTrans->product_id = $item['product_id'];
                $stockTrans->warehouse_id = $model->warehouse_id;
                $stockTrans->trans_type_id = $model->trans_type_id;
                $stockTrans->stock_type_id = $model->stock_type_id;
                $stockTrans->qty = $item['qty'];
                $stockTrans->line_price = $item['sale_price'];
                $stockTrans->status = 1;
                $stockTrans->remark = $item['remark'] ?? '';

                if (!$stockTrans->save()) {
                    throw new \Exception('ไม่สามารถบันทึก Stock Transaction ได้');
                }
            }

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'บันทึกรายการเรียบร้อยแล้ว เลขที่เอกสาร: ' . $model->journal_no
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process return borrow with condition check
     */
    private function processReturnBorrow($model, $items)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Generate journal number
            $model->journal_no = $this->generateJournalNumber($model->trans_type_id);

            $totalQty = 0;
            $validItems = [];

            foreach ($items as $item) {
                if (!empty($item['product_id']) && !empty($item['qty']) && $item['qty'] > 0) {
                    $product = Product::findOne($item['product_id']);
                    if ($product) {
                        $item['sale_price'] = $product->sale_price;
                        $item['condition_status'] = $item['condition_status'] ?? 'good'; // good, damaged, missing
                        $item['condition_note'] = $item['condition_note'] ?? '';
                        $totalQty += $item['qty'];
                        $validItems[] = $item;
                    }
                }
            }

            if (empty($validItems)) {
                throw new \Exception('กรุณาระบุรายการสินค้า');
            }

            $model->qty = $totalQty;

            if (!$model->save()) {
                throw new \Exception('ไม่สามารถบันทึก Journal Transaction ได้: ' . implode(', ', $model->getFirstErrors()));
            }

            // Save transaction lines with condition check
            foreach ($validItems as $item) {
                $line = new JournalTransLine();
                $line->journal_trans_id = $model->id;
                $line->product_id = $item['product_id'];
                $line->warehouse_id = $model->warehouse_id;
                $line->qty = $item['qty'];
                $line->remark = "สภาพ: {$item['condition_status']} - {$item['condition_note']}";

                if (!$line->save()) {
                    throw new \Exception('ไม่สามารถบันทึก Transaction Line ได้');
                }

                // Create stock transaction
                $stockTrans = new StockTrans();
                $stockTrans->journal_trans_id = $model->id;
                $stockTrans->trans_date = $model->trans_date;
                $stockTrans->product_id = $item['product_id'];
                $stockTrans->warehouse_id = $model->warehouse_id;
                $stockTrans->trans_type_id = $model->trans_type_id;
                $stockTrans->stock_type_id = $model->stock_type_id;
                $stockTrans->qty = $item['qty'];
                $stockTrans->line_price = $item['sale_price'];
                $stockTrans->status = 1;
                $stockTrans->remark = "สภาพ: {$item['condition_status']} - {$item['condition_note']}";

                if (!$stockTrans->save()) {
                    throw new \Exception('ไม่สามารถบันทึก Stock Transaction ได้');
                }
            }

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'บันทึกการคืนยืมเรียบร้อยแล้ว เลขที่เอกสาร: ' . $model->journal_no
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Approve transaction
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);

        if ($model->status != 0) {
            Yii::$app->session->setFlash('warning', 'รายการนี้ได้รับการอนุมัติหรือถูกยกเลิกแล้ว');
            return $this->redirect(['view', 'id' => $id]);
        }

        $result = $this->processApproval($model, true);

        if ($result['success']) {
            Yii::$app->session->setFlash('success', $result['message']);
        } else {
            Yii::$app->session->setFlash('error', $result['message']);
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Reject transaction
     */
    public function actionReject($id)
    {
        $model = $this->findModel($id);

        if ($model->status != 0) {
            Yii::$app->session->setFlash('warning', 'รายการนี้ได้รับการอนุมัติหรือถูกยกเลิกแล้ว');
            return $this->redirect(['view', 'id' => $id]);
        }

        $result = $this->processApproval($model, false);

        if ($result['success']) {
            Yii::$app->session->setFlash('success', $result['message']);
        } else {
            Yii::$app->session->setFlash('error', $result['message']);
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Process approval/rejection
     */
    private function processApproval($model, $approve = true)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model->status = $approve ? 1 : 2; // 1 = approved, 2 = rejected

            if (!$model->save()) {
                throw new \Exception('ไม่สามารถอัพเดทสถานะได้');
            }

            if ($approve) {
                // Update stock when approved
                foreach ($model->journalTransLines as $line) {
                    // Update stock summary
                    if (!StockSum::updateStock(
                        $line->product_id,
                        $line->warehouse_id,
                        $line->qty,
                        $model->stock_type_id
                    )) {
                        throw new \Exception("ไม่สามารถอัพเดทสต๊อกสินค้า ID: {$line->product_id} ได้");
                    }

                    // Update product total stock
                    $this->updateProductTotalStock($line->product_id);
                }
            }

            $transaction->commit();
            return [
                'success' => true,
                'message' => $approve ? 'อนุมัติรายการเรียบร้อยแล้ว' : 'ปฏิเสธรายการเรียบร้อยแล้ว'
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update product total stock from all warehouses
     */
    private function updateProductTotalStock($productId)
    {
        $totalStock = StockSum::find()
            ->where(['product_id' => $productId])
            ->sum('qty');

        Product::updateAll(['stock_qty' => $totalStock ?: 0], ['id' => $productId]);
    }

    /**
     * Generate journal number by transaction type
     */
    private function generateJournalNumber($transTypeId)
    {
        $prefixes = [
            self::TRANS_TYPE_PO_RECEIVE => 'REC',
            self::TRANS_TYPE_CANCEL_PO_RECEIVE => 'CRE',
            self::TRANS_TYPE_ISSUE_STOCK => 'ISS',
            self::TRANS_TYPE_RETURN_ISSUE => 'RIS',
            self::TRANS_TYPE_ISSUE_BORROW => 'BOR',
            self::TRANS_TYPE_RETURN_BORROW => 'RBO',
        ];

        $prefix = $prefixes[$transTypeId] . date('Ym');

        $lastRecord = JournalTrans::find()
            ->where(['like', 'journal_no', $prefix])
            ->andWhere(['trans_type_id' => $transTypeId])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->journal_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%04d', $newNumber);
    }

    /**
     * Get product info for AJAX
     */
    public function actionGetProductInfo($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $product = Product::findOne($id);
        if ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'sale_price' => $product->sale_price,
                'stock_qty' => $product->stock_qty,
                'unit_id' => $product->unit_id,
            ];
        }

        return null;
    }

    /**
     * Find model
     */
    protected function findModel($id)
    {
        if (($model = JournalTrans::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}