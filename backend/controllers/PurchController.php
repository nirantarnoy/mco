<?php
namespace backend\controllers;

use app\behaviors\ActionLogBehavior;
use backend\models\JournalTrans;
use Mpdf\Mpdf;
use Yii;
use backend\models\Purch;
use backend\models\PurchSearch;
use backend\models\PurchLine;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\db\Transaction;
use yii\web\UploadedFile;

/**
 * PurchController implements the CRUD actions for Purch model.
 */
class PurchController extends Controller
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
                ],
            ],
            'actionLog' => [
                'class' => ActionLogBehavior::class,
                'actions' => ['create', 'update', 'delete', 'approve','reject','receive','cancel-receive','view','print'], // Log เฉพาะ actions เหล่านี้
            ],
        ];
    }

    /**
     * Lists all Purch models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Purch model.
     * @param int $id ID
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
     * Creates a new Purch model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Purch();
        $model->status = Purch::STATUS_DRAFT;
        $model->approve_status = Purch::APPROVE_STATUS_PENDING;
        $model->purch_date = date('Y-m-d');

        // Initialize with one empty purch line
        $model->purchLines = [new PurchLine()];

        if ($model->load(Yii::$app->request->post())) {
            $purchLines = [];
            $valid = $model->validate();

            if (isset($_POST['PurchLine'])) {
                foreach ($_POST['PurchLine'] as $index => $purchLineData) {
                    $purchLine = new PurchLine();
                    $purchLine->load($purchLineData, '');
                    $purchLines[] = $purchLine;
                    $valid = $purchLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // Save purch lines
                        foreach ($purchLines as $purchLine) {
                            $purchLine->purch_id = $model->id;
                            if (!$purchLine->save()) {
                                throw new \Exception('Failed to save purch line');
                            }
                        }
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'สร้างใบสั่งซื้อเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Purch model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Load existing purch lines
        $model->purchLines = $model->getPurchLines()->all();
        if (empty($model->purchLines)) {
            $model->purchLines = [new PurchLine()];
        }

        if ($model->load(Yii::$app->request->post())) {
            $purchLines = [];
            $valid = $model->validate();

            if (isset($_POST['PurchLine'])) {
                foreach ($_POST['PurchLine'] as $index => $purchLineData) {
                    if (isset($purchLineData['id']) && !empty($purchLineData['id'])) {
                        // Update existing line
                        $purchLine = PurchLine::findOne($purchLineData['id']);
                        if (!$purchLine) {
                            $purchLine = new PurchLine();
                        }
                    } else {
                        // Create new line
                        $purchLine = new PurchLine();
                    }
                    $purchLine->load($purchLineData, '');
                    $purchLines[] = $purchLine;
                    $valid = $purchLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // Delete existing lines that are not in the new list
                        $existingLineIds = [];
                        foreach ($purchLines as $purchLine) {
                            if (!$purchLine->isNewRecord) {
                                $existingLineIds[] = $purchLine->id;
                            }
                        }

                        PurchLine::deleteAll([
                            'and',
                            ['purch_id' => $model->id],
                            ['not in', 'id', $existingLineIds]
                        ]);

                        // Save purch lines
                        foreach ($purchLines as $purchLine) {
                            $purchLine->purch_id = $model->id;
                            if (!$purchLine->save()) {
                                throw new \Exception('Failed to save purch line');
                            }
                        }
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Purch model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Delete all purch lines first
            PurchLine::deleteAll(['purch_id' => $id]);

            // Delete the purch record
            $model->delete();

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบข้อมูลได้: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Get product info for AJAX
     */
//    public function actionGetProductInfo($id)
//    {
//        Yii::$app->response->format = Response::FORMAT_JSON;
//        return Product::getProductInfo($id);
//    }

    /**
     * Approve purchase order
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = Purch::APPROVE_STATUS_APPROVED;
        $model->status = Purch::STATUS_ACTIVE;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'อนุมัติใบสั่งซื้อเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถอนุมัติใบสั่งซื้อได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Reject purchase order
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionReject($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = Purch::APPROVE_STATUS_REJECTED;
        $model->status = Purch::STATUS_CANCELLED;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'ไม่อนุมัติใบสั่งซื้อเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถปฏิเสธใบสั่งซื้อได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the Purch model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Purch the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Purch::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionReceive($id)
    {
        $purchModel = $this->findModel($id);

        // Check if PO is approved
        if ($purchModel->approve_status != Purch::APPROVE_STATUS_APPROVED) {
            \Yii::$app->session->setFlash('error', 'ไม่สามารถรับสินค้าได้ กรุณาอนุมัติใบสั่งซื้อก่อน');
            return $this->redirect(['view', 'id' => $purchModel->id]);
        }

        // Get PO lines with remaining quantities
        $poLines = $this->getPOLinesWithRemaining($purchModel->id);

        if (empty($poLines)) {
            \Yii::$app->session->setFlash('warning', 'สินค้าทั้งหมดได้รับเข้าครบแล้ว');
            return $this->redirect(['view', 'id' => $purchModel->id]);
        }

        if (\Yii::$app->request->isPost) {
            $receiveData = \Yii::$app->request->post('receive', []);
            $warehouseId = \Yii::$app->request->post('line_warehouse_id');
            $remark = \Yii::$app->request->post('remark', '');

//            echo '<pre>';
//            print_r($receiveData);
//            echo '</pre>';return;

            if (empty($warehouseId)) {
                \Yii::$app->session->setFlash('error', 'กรุณาเลือกคลังสินค้า');
            } else {
                $result = $this->processReceive($purchModel, $receiveData, $warehouseId, $remark);
                if ($result['success']) {
                    \Yii::$app->session->setFlash('success', $result['message']);
                    return $this->redirect(['view', 'id' => $purchModel->id]);
                } else {
                    \Yii::$app->session->setFlash('error', $result['message']);
                    return $this->redirect(['view', 'id' => $purchModel->id]);
                }
            }
        }

        return $this->render('receive', [
            'purchModel' => $purchModel,
            'poLines' => $poLines,
            'warehouses' => \backend\models\Warehouse::getWarehouseList(),
        ]);
    }

    /**
     * Cancel PO receive
     * @param int $id Journal Trans ID
     * @return Response
     */
    public function actionCancelReceive($id)
    {
        $journalTrans = \backend\models\JournalTrans::findOne($id);

        if (!$journalTrans) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการรับสินค้า');
            return $this->redirect(['index']);
        }

        if ($journalTrans->status == \backend\models\JournalTrans::STATUS_CANCELLED) {
            Yii::$app->session->setFlash('warning', 'รายการนี้ถูกยกเลิกแล้ว');
            return $this->redirect(['view', 'id' => $journalTrans->trans_ref_id]);
        }

        $result = $this->processCancelReceive($journalTrans);

        if ($result['success']) {
            Yii::$app->session->setFlash('success', $result['message']);
        } else {
            Yii::$app->session->setFlash('error', $result['message']);
        }

        return $this->redirect(['view', 'id' => $journalTrans->trans_ref_id]);
    }

    /**
     * Get PO lines with remaining quantities
     */
    private function getPOLinesWithRemaining($purchId)
    {
        $sql = "
            SELECT 
                pl.*,
                COALESCE(received.total_received, 0) as total_received,
                (pl.qty - COALESCE(received.total_received, 0)) as remaining_qty
            FROM purch_line pl
            LEFT JOIN (
                SELECT 
                    product_id,
                    SUM(jtl.qty) as total_received
                FROM journal_trans_line jtl
                INNER JOIN journal_trans jt ON jtl.journal_trans_id = jt.id
                WHERE jt.trans_ref_id = :purchId 
                AND jt.trans_type_id = :transType 
                AND jt.po_rec_status = :status
                GROUP BY product_id
            ) received ON pl.product_id = received.product_id
            WHERE pl.purch_id = :purchId 
            AND pl.status = :lineStatus
            AND (pl.qty - COALESCE(received.total_received, 0)) > 0
        ";

        return \Yii::$app->db->createCommand($sql, [
            ':purchId' => $purchId,
            ':transType' => \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE,
            ':status' => 1,
            ':lineStatus' => \backend\models\PurchLine::STATUS_ACTIVE,
        ])->queryAll();
    }
    private function processReceive($purchModel, $receiveData, $warehouseId, $remark)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // Validate receive data
            $validItems = [];
            $totalQty = 0;



            foreach ($receiveData as $productId => $qty) {
                $qty = floatval($qty);
                if ($qty > 0) {
                    $validItems[$productId] = $qty;
                    $totalQty += $qty;
                }
            }

            if (empty($validItems)) {
                return ['success' => false, 'message' => 'กรุณาระบุจำนวนสินค้าที่ต้องการรับเข้า'];
            }

            // Create Journal Transaction
            $journalTrans = new \backend\models\JournalTrans();
            $journalTrans->trans_date = date('Y-m-d H:i:s');
            $journalTrans->trans_type_id = \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE;
            $journalTrans->stock_type_id = \backend\models\JournalTrans::STOCK_TYPE_IN;
            $journalTrans->trans_ref_id = $purchModel->id;
            $journalTrans->warehouse_id = 0;//$line_warehouse_id;
            $journalTrans->customer_name = $purchModel->vendor_name;
            $journalTrans->status = 0;
            $journalTrans->po_rec_status = 1;
            $journalTrans->qty = $totalQty;
            $journalTrans->remark = $remark;


            if (!$journalTrans->save()) {
                throw new \Exception('ไม่สามารถสร้าง Journal Transaction ได้: ' . implode(', ', $journalTrans->getFirstErrors()));
            }

            $loop_index = 0;
            // Process each item
            foreach ($validItems as $productId => $qty) {
                $line_warehouse_id = $warehouseId[$loop_index];
                $loop_index++;


                // Get product and PO line info
                $poLine = \backend\models\PurchLine::find()
                    ->where(['purch_id' => $purchModel->id, 'product_id' => $productId])
                    ->one();

                if (!$poLine) {
                    throw new \Exception("ไม่พบสินค้า ID: $productId ในใบสั่งซื้อ");
                }

                // Create Journal Transaction Line
                $journalTransLine = new \backend\models\JournalTransLine();
                $journalTransLine->journal_trans_id = $journalTrans->id;
                $journalTransLine->product_id = $productId;
                $journalTransLine->warehouse_id = $line_warehouse_id;
                $journalTransLine->qty = $qty;
                $journalTransLine->remark = "รับสินค้าจาก PO: " . $purchModel->purch_no;

                if (!$journalTransLine->save()) {
                    throw new \Exception('ไม่สามารถสร้าง Journal Transaction Line ได้: ' . implode(', ', $journalTransLine->getFirstErrors()));
                }

                // Create Stock Transaction
                $stockTrans = new \backend\models\StockTrans();
                $stockTrans->journal_trans_id = $journalTrans->id;
                $stockTrans->trans_date = $journalTrans->trans_date;
                $stockTrans->product_id = $productId;
                $stockTrans->warehouse_id = $line_warehouse_id;
                $stockTrans->trans_type_id = \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE;
                $stockTrans->stock_type_id = \backend\models\JournalTrans::STOCK_TYPE_IN;
                $stockTrans->qty = $qty;
                $stockTrans->line_price = $poLine->line_price;
                $stockTrans->status = 1;
                $stockTrans->remark = "รับสินค้าจาก PO: " . $purchModel->purch_no;

                if (!$stockTrans->save()) {
                    throw new \Exception('ไม่สามารถสร้าง Stock Transaction ได้: ' . implode(', ', $stockTrans->getFirstErrors()));
                }

                // Update Stock Summary
                if (!\backend\models\StockSum::updateStockIn($productId, $line_warehouse_id, $qty, 1)) {
                    throw new \Exception("ไม่สามารถอัพเดทสต๊อกสินค้า ID: $productId ได้");
                }
            }

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'รับสินค้าเข้าคลังเรียบร้อยแล้ว เลขที่เอกสาร: ' . $journalTrans->journal_no
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
    }

    /**
     * Process cancel receive
     */
    private function processCancelReceive($journalTrans)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Cancel Journal Transaction
            $journalTrans->status = \backend\models\JournalTrans::STATUS_CANCELLED;
            if (!$journalTrans->save()) {
                throw new \Exception('ไม่สามารถยกเลิก Journal Transaction ได้'. implode(', ', $journalTrans->getFirstErrors()));
            }

            // Cancel all related Stock Transactions
            \backend\models\StockTrans::updateAll(
                ['status' => \backend\models\StockTrans::STATUS_CANCELLED],
                ['journal_trans_id' => $journalTrans->id]
            );

            // Reverse stock quantities
           // $journalTransLines = $journalTrans->journalTransLines;
            $journalTransLines = \backend\models\JournalTransLine::find()
                ->where(['journal_trans_id' => $journalTrans->id])->all();
            foreach ($journalTransLines as $line) {
                // Reverse stock by reducing the quantity (opposite of receive)
                if (!\backend\models\StockSum::updateStockOut(
                    $line->product_id,
                    $line->warehouse_id,
                    $line->qty,
                    \backend\models\JournalTrans::STOCK_TYPE_OUT
                )) {
                    throw new \Exception("ไม่สามารถปรับปรุงสต๊อกสินค้า ID: {$line->product_id} ได้");
                }
            }

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'ยกเลิกการรับสินค้าเรียบร้อยแล้ว เลขที่เอกสาร: ' . $journalTrans->journal_no
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
    }

    /**
     * Get receive history for PO
     * @param int $id
     * @return Response
     */
    public function actionReceiveHistory($id)
    {
        $purchModel = $this->findModel($id);

        $receiveHistory = \backend\models\JournalTrans::find()
            ->where([
                'trans_ref_id' => $id,
                'trans_type_id' => \backend\models\JournalTrans::TRANS_TYPE_PO_RECEIVE
            ])
            ->with(['journalTransLines', 'journalTransLines.product', 'journalTransLines.warehouse'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('receive-history', [
            'purchModel' => $purchModel,
            'receiveHistory' => $receiveHistory,
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

    public function actionPrint($id, $format = 'html')
    {
        $purchase = Purch::findOne($id);

        if (!$purchase) {
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        }

        // ดึงข้อมูล purchase lines พร้อม product
        $purchaseLines = PurchLine::find()
            ->where(['purch_id' => $id])
            ->with('product')
            ->all();

        if ($format == 'pdf') {
            return $this->generatePdf($purchase, $purchaseLines);
        }

        // แสดงแบบ HTML
        $this->layout = '@backend/views/layouts/main_print';

        return $this->render('print', [
            'purchase' => $purchase,
            'purchaseLines' => $purchaseLines,
            'showButtons' => true,
        ]);
    }

    public function actionPrintReceiveBill($id)
    {
       $model = \backend\models\JournalTrans::findOne($id);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');

        }
    }

    /**
     * Generate PDF
     */
    protected function generatePdf($purchase, $purchaseLines)
    {
        // Render HTML content
        $content = $this->renderPartial('print-pdf', [
            'purchase' => $purchase,
            'purchaseLines' => $purchaseLines,
            'showButtons' => false,
        ]);

        // Setup mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 5,
            'margin_footer' => 5,
            'fontDir' => [Yii::getAlias('@backend') . '/web/fonts/'],
            'fontdata' => [
                'thsarabun' => [
                    'R' => 'THSarabunNew.ttf',
                    'B' => 'THSarabunNewBold.ttf',
                ],
            ],
            'default_font' => 'thsarabun',
            'default_font_size' => 14,
        ]);

        // Write HTML to PDF
        $mpdf->WriteHTML($content);

        // Output PDF
        $filename = 'PO_' . $purchase->purch_no . '.pdf';
        $mpdf->Output($filename, 'I'); // I = inline, D = download

        exit;
    }
    public function actionPrintreceipt($id = null)
    {
        //$this->layout = 'print'; // Use a minimal print layout
        $model = \backend\models\Purch::findOne($id);
        $model_line = \backend\models\PurchLine::find()->where(['purch_id' => $model->id])->all();
        $this->layout = 'main_print';
        return $this->render('_printreceipt',[
            'model' => $model,
            'model_line' => $model_line,
        ]);
    }
    // In your controller
    public function actionPrintTags()
    {
        $id = \Yii::$app->request->post('purch_id');
        if($id){
            $model = \backend\models\PurchLine::find()->where(['purch_id' => $id])->all();
            $selectedProducts = [];

            foreach ($model as $product) {
               // if (in_array($product['id'], $selectedIds)) {
                    $selectedProducts[] = $product;
               // }
            }

            return $this->render('_print-tag', [
                'selectedProducts' => $selectedProducts,
            ]);
        }
    }

    public function actionGenerateTags()
    {
//        $productData = Yii::$app->request->post('products');
//        $printData = [];
//
//        if ($productData) {
//            foreach ($productData as $data) {
//                $product = json_decode($data['product'], true);
//                $copies = (int)$data['copies'];
//
//                for ($i = 0; $i < $copies; $i++) {
//                    $printData[] = $product;
//                }
//            }
//        }

        $printData = [];

        $id = \Yii::$app->request->post('purch_id');
        $line_ref_po = \Yii::$app->request->post('line_ref_po');
        $line_description = \Yii::$app->request->post('line_description');
        $line_model = \Yii::$app->request->post('line_model');
        $line_brand = \Yii::$app->request->post('line_brand');
        $line_quantity = \Yii::$app->request->post('line_quantity');
        $line_copies = \Yii::$app->request->post('line_copies');
        $productData = [];
        for ($i = 0; $i < count($line_ref_po); $i++) {
            for ($j = 0; $j < $line_copies[$i]; $j++) {
                $productData[] = [
                    'ref_po' => $line_ref_po[$i],
                    'description' => $line_description[$i],
                    'model' => $line_model[$i],
                    'brand' => $line_brand[$i],
                    'quantity' => $line_quantity[$i],
                    'copies' => $line_copies[$i],
                ];

                $printData = $productData;
            }
//            $productData[] = [
//                'ref_po' => $line_ref_po[$i],
//                'description' => $line_description[$i],
//                'model' => $line_model[$i],
//                'brand' => $line_brand[$i],
//                'quantity' => $line_quantity[$i],
//                'copies' => $line_copies[$i],
//            ];
//            $printData = $productData;
        }

      // print_r($printData);return;

        $format = Yii::$app->request->get('format', 'html');

        if ($format === 'pdf') {
            $content = $this->renderPartial('_print-preview', ['printData' => $printData]);

            $pdf = new \kartik\mpdf\Pdf([
                'mode' => \kartik\mpdf\Pdf::MODE_UTF8,
                'format' => \kartik\mpdf\Pdf::FORMAT_A4,
                'orientation' => \kartik\mpdf\Pdf::ORIENT_PORTRAIT,
                'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
                'content' => $content,
                'cssFile' => '@frontend/web/css/print-tag.css',
                'options' => ['title' => 'Product Tags'],
                'methods' => [
                    'SetHeader' => ['Product Tags'],
                    'SetFooter' => ['{PAGENO}'],
                ]
            ]);

            return $pdf->render();
        } elseif ($format === 'excel') {
            return $this->exportExcel($printData);
        } else {
            return $this->renderPartial('_print-preview', ['printData' => $printData]);
        }
    }
    public function actionPrintDeliveryNote($id = null)
    {
        $this->layout = 'main_print'; // Use minimal print layout
        return $this->render('_printdeliverynote');
    }


    private function exportExcel($printData)
    {
        $objPHPExcel = new \PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();

        $row = 1;
        $col = 0;

        foreach ($printData as $index => $product) {
            if ($col >= 3) {
                $col = 0;
                $row += 5;
            }

            $startCol = $col * 3;

            $sheet->setCellValueByColumnAndRow($startCol, $row, 'Ref.Po: ' . $product['ref_po']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 1, 'Descrip: ' . $product['description']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 2, 'Model: ' . $product['model']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 3, 'Brand: ' . $product['brand']);
            $sheet->setCellValueByColumnAndRow($startCol, $row + 4, 'Q\'ty: ' . $product['quantity']);

            $col++;
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="product_tags.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

        Yii::$app->end();
    }

    public function actionAddDocFile(){
        $id = \Yii::$app->request->post('id');
        if($id){
            $uploaded = UploadedFile::getInstancesByName('file_doc');
            if (!empty($uploaded)) {
                $loop = 0;
                foreach ($uploaded as $file) {
                    $upfiles = "purch_" . time()."_".$loop . "." . $file->getExtension();
                    if ($file->saveAs('uploads/purch_doc/' . $upfiles)) {
                        $model_doc = new \common\models\PurchDoc();
                        $model_doc->purch_id = $id;
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
            $model_doc = \common\models\PurchDoc::find()->where(['purch_id' => $id,'doc_name' => $doc_delete_list])->one();
            if($model_doc){
                if($model_doc->delete()){
                    if(file_exists('uploads/purch_doc/'.$model_doc->doc_name)){
                        unlink('uploads/purch_doc/'.$model_doc->doc_name);
                    }
                }
            }
        }
        return $this->redirect(['update', 'id' => $id]);
    }

}