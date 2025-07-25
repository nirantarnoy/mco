<?php
namespace backend\controllers;

use Yii;
use backend\models\PurchReq;
use backend\models\PurchReqSearch;
use backend\models\PurchReqLine;
use backend\models\Product;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * PurchReqController implements the CRUD actions for PurchReq model.
 */
class PurchreqController extends Controller
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
     * Lists all PurchReq models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchReqSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PurchReq model.
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
     * Creates a new PurchReq model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PurchReq();
        $model->status = PurchReq::STATUS_DRAFT;
        $model->approve_status = PurchReq::APPROVE_STATUS_PENDING;
        $model->purch_req_date = date('Y-m-d');

        // Initialize with one empty purch req line
        $model->purchReqLines = [new PurchReqLine()];

        if ($model->load(Yii::$app->request->post())) {
            $purchReqLines = [];
            $valid = $model->validate();

            if (isset($_POST['PurchReqLine'])) {
                foreach ($_POST['PurchReqLine'] as $index => $purchReqLineData) {
                    $purchReqLine = new PurchReqLine();
                    $purchReqLine->load($purchReqLineData, '');
                    $purchReqLines[] = $purchReqLine;
                    $valid = $purchReqLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // คำนวณยอดรวม
                        $totalAmount = 0;
                        $discountAmount = 0;
                        $vatAmount = 0;
                        $netAmount = 0;
                        // Save purch req lines
                        foreach ($purchReqLines as $purchReqLine) {
                            $purchReqLine->purch_req_id = $model->id;
                            if (!$purchReqLine->save()) {
                                throw new \Exception('Failed to save purch req line');
                            }
                            // คำนวณยอดรวมจากแต่ละรายการ
                            $lineTotal = $purchReqLine->qty * $purchReqLine->line_price;
                            $totalAmount += $lineTotal;
                        }
                        // คำนวณส่วนลด (สมมติว่ามีฟิลด์ discount_percent ใน model)
                        if (isset($model->discount_percent) && $model->discount_percent > 0) {
                            $discountAmount = ($totalAmount * $model->discount_percent) / 100;
                        } else if (isset($model->discount_amount) && $model->discount_amount > 0) {
                            $discountAmount = $model->discount_amount;
                        }

                        // คำนวณยอดหลังหักส่วนลด
                        $afterDiscountAmount = $totalAmount - $discountAmount;

                        // คำนวณ VAT (สมมติว่ามีฟิลด์ vat_percent ใน model หรือใช้ VAT 7%)
                        $vatPercent = isset($model->vat_percent) ? $model->vat_percent : 7;
                        if ($vatPercent > 0) {
                            $vatAmount = ($afterDiscountAmount * $vatPercent) / 100;
                        }

                        // คำนวณยอดสุทธิ
                        $netAmount = $afterDiscountAmount + $vatAmount;

                        // อัพเดทยอดรวมใน purch_req ถ้าจำเป็น
                        $model->total_amount = $totalAmount;
                        $model->discount_amount = $discountAmount;
                        $model->vat_amount = $vatAmount;
                        $model->net_amount = $netAmount;
                        $model->save(false); // skip validation เพราะ validate แล้ว


                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'สร้างใบขอซื้อเรียบร้อยแล้ว');
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
     * Updates an existing PurchReq model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Load existing purch req lines
        $model->purchReqLines = $model->getPurchReqLines()->all();
        if (empty($model->purchReqLines)) {
            $model->purchReqLines = [new PurchReqLine()];
        }

        if ($model->load(Yii::$app->request->post())) {
            $purchReqLines = [];
            $valid = $model->validate();

            if (isset($_POST['PurchReqLine'])) {
                foreach ($_POST['PurchReqLine'] as $index => $purchReqLineData) {
                    if (isset($purchReqLineData['id']) && !empty($purchReqLineData['id'])) {
                        // Update existing line
                        $purchReqLine = PurchReqLine::findOne($purchReqLineData['id']);
                        if (!$purchReqLine) {
                            $purchReqLine = new PurchReqLine();
                        }
                    } else {
                        // Create new line
                        $purchReqLine = new PurchReqLine();
                    }
                    $purchReqLine->load($purchReqLineData, '');
                    $purchReqLines[] = $purchReqLine;
                    $valid = $purchReqLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // Delete existing lines that are not in the new list
                        $existingLineIds = [];
                        foreach ($purchReqLines as $purchReqLine) {
                            if (!$purchReqLine->isNewRecord) {
                                $existingLineIds[] = $purchReqLine->id;
                            }
                        }

                        PurchReqLine::deleteAll([
                            'and',
                            ['purch_req_id' => $model->id],
                            ['not in', 'id', $existingLineIds]
                        ]);

                        // Save purch req lines
                        foreach ($purchReqLines as $purchReqLine) {
                            $purchReqLine->purch_req_id = $model->id;
                            if (!$purchReqLine->save()) {
                                throw new \Exception('Failed to save purch req line');
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
     * Deletes an existing PurchReq model.
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
            // Delete all purch req lines first
            PurchReqLine::deleteAll(['purch_req_id' => $id]);

            // Delete the purch req record
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
     * Approve purchase request
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = PurchReq::APPROVE_STATUS_APPROVED;
        $model->approve_by = Yii::$app->user->id;
        $model->status = PurchReq::STATUS_ACTIVE;
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'อนุมัติใบขอซื้อเรียบร้อยแล้ว'. implode(', ', array_map(function($e) {
                        return implode(', ', $e);
                    }, $model->getErrors())));
                $transaction->commit();
                return $this->redirect(['view', 'id' => $model->id]);

            } else {
                Yii::$app->session->setFlash('error', 'ไม่สามารถอนุมัติใบขอซื้อได้: ' . implode(', ', array_map(function($e) {
                        return implode(', ', $e);
                    }, $model->getErrors())));
                $transaction->rollBack();
                return $this->redirect(['view', 'id' => $model->id]);
            }

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            return $this->redirect(['view', 'id' => $model->id]);
        }


    }

    /**
     * Reject purchase request
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionReject($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = PurchReq::APPROVE_STATUS_REJECTED;
        $model->status = PurchReq::STATUS_CANCELLED;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'ไม่อนุมัติใบขอซื้อเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถปฏิเสธใบขอซื้อได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the PurchReq model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PurchReq the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchReq::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionPrint($id)
    {
        $model = $this->findModel($id);

        // Set response format for PDF
        $this->layout = false;

        return $this->render('print', [
            'model' => $model,
        ]);
    }

    /**
     * Generate PDF for purchase request
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionPdf($id)
    {
        $model = $this->findModel($id);

        // Get HTML content
        $content = $this->renderPartial('print', [
            'model' => $model,
        ]);

        // Configure mPDF
        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
            'default_font' => 'garuda'
        ]);

        $pdf->WriteHTML($content);

        // Output PDF
        $filename = 'PurchaseRequest_' . $model->purch_req_no . '.pdf';
        $pdf->Output($filename, 'I'); // 'I' for inline, 'D' for download

        exit;
    }
    /**
     * Convert purchase request to purchase order
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionConvertToPurchaseOrder($id)
    {
        $purchReqModel = $this->findModel($id);

        // Check if already converted
        if ($purchReqModel->purch_id) {
            Yii::$app->session->setFlash('warning', 'ใบขอซื้อนี้ได้ถูกแปลงเป็นใบสั่งซื้อแล้ว (PO ID: ' . $purchReqModel->purch_id . ')');
            return $this->redirect(['view', 'id' => $purchReqModel->id]);
        }

        // Check if approved
        if ($purchReqModel->approve_status != PurchReq::APPROVE_STATUS_APPROVED) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถแปลงเป็นใบสั่งซื้อได้ กรุณาอนุมัติใบขอซื้อก่อน');
            return $this->redirect(['view', 'id' => $purchReqModel->id]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Create new Purchase Order
            $purchModel = new \backend\models\Purch();
            $purchModel->purch_date = $purchReqModel->purch_req_date;
            $purchModel->vendor_id = $purchReqModel->vendor_id;
            $purchModel->vendor_name = $purchReqModel->vendor_name;
            $purchModel->status = \backend\models\Purch::STATUS_ACTIVE;
            $purchModel->approve_status = \backend\models\Purch::APPROVE_STATUS_APPROVED;
            $purchModel->total_amount = $purchReqModel->total_amount;
            $purchModel->discount_amount = $purchReqModel->discount_amount;
            $purchModel->vat_amount = $purchReqModel->vat_amount;
            $purchModel->net_amount = $purchReqModel->net_amount;
            $purchModel->discount_percent = $purchReqModel->discount_percent;
            $purchModel->vat_percent = $purchReqModel->vat_percent;
           // $purchModel->note = 'แปลงจากใบขอซื้อ: ' . $purchReqModel->purch_req_no . ($purchReqModel->note ? ' - ' . $purchReqModel->note : '');
            //$purchModel->ref_text = 'PR: ' . $purchReqModel->purch_req_no;

            if (!$purchModel->save(false)) {
                throw new \Exception('ไม่สามารถสร้างใบสั่งซื้อได้: ' . implode(', ', $purchModel->getFirstErrors()));
            }

           // print_r($purchReqModel);return;
            // Copy purchase request lines to purchase order lines
            $purch_req_lines = \backend\models\PurchReqLine::find()->where(['purch_req_id' => $purchReqModel->id])->all();
            if($purch_req_lines){
                foreach ($purch_req_lines as $reqLine) {
                    $purchLine = new \backend\models\PurchLine();
                    $purchLine->purch_id = $purchModel->id;
                    $purchLine->product_id = $reqLine->product_id;
                    $purchLine->product_name = $reqLine->product_name;
                    $purchLine->product_type = $reqLine->product_type;
                    $purchLine->qty = $reqLine->qty;
                    $purchLine->line_price = $reqLine->line_price;
                    $purchLine->line_total = $reqLine->line_total;
                    $purchLine->unit_id = $reqLine->unit_id;
                    $purchLine->status = \backend\models\PurchLine::STATUS_ACTIVE;
                    $purchLine->note = $reqLine->note . ($reqLine->product_name ? ' - ' . $reqLine->product_name : '');

                    if (!$purchLine->save(false)) {
                        throw new \Exception('ไม่สามารถสร้างรายการสินค้าได้: ' . implode(', ', $purchLine->getFirstErrors()));
                    }
                }
            }


            // Update purchase request with reference ID
            $purchReqModel->purch_id = $purchModel->id;
            if (!$purchReqModel->save()) {
                throw new \Exception('ไม่สามารถอัพเดทใบขอซื้อได้: ' . implode(', ', $purchReqModel->getFirstErrors()));
            }

            $transaction->commit();

            Yii::$app->session->setFlash('success',
                'แปลงใบขอซื้อเป็นใบสั่งซื้อเรียบร้อยแล้ว<br>' .
                'เลขที่ใบสั่งซื้อ: <strong>' . $purchModel->purch_no . '</strong>'
            );

            // Redirect to purchase order view
            return $this->redirect(['/purch/view', 'id' => $purchModel->id]);

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            return $this->redirect(['view', 'id' => $purchReqModel->id]);
        }
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
    public function actionPrintPr($id = null)
    {
       // echo "ok";
        //$this->layout = 'print'; // Use a minimal print layout
        $model = \backend\models\PurchReq::find()->where(['id' => $id])->one();
        $model_line = \backend\models\PurchReqLine::find()->where(['purch_req_id' => $model->id])->all();
       // $this->layout = 'main_print';
        return $this->render('_print',[
            'model' => $model,
            'model_line' => $model_line,
        ]);
    }
}