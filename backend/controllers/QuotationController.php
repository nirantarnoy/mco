<?php
namespace backend\controllers;

use backend\models\Job;
use common\models\JobLine;
use Mpdf\Mpdf;
use Yii;
use backend\models\Quotation;
use backend\models\QuotationSearch;
use backend\models\QuotationLine;
use backend\models\Product;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * QuotationController implements the CRUD actions for Quotation model.
 */
class QuotationController extends Controller
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
     * Lists all Quotation models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new QuotationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Quotation model.
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
     * Creates a new Quotation model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Quotation();
        $model->status = Quotation::STATUS_DRAFT;
        $model->approve_status = Quotation::APPROVE_STATUS_PENDING;
        $model->quotation_date = date('Y-m-d');

        // Initialize with one empty quotation line
        $model->quotationLines = [new QuotationLine()];

        if ($model->load(Yii::$app->request->post())) {
            $quotationLines = [];
            $valid = $model->validate();

            if (isset($_POST['QuotationLine'])) {
                foreach ($_POST['QuotationLine'] as $index => $quotationLineData) {
                    $quotationLine = new QuotationLine();
                    $quotationLine->load($quotationLineData, '');
                    $quotationLines[] = $quotationLine;
                    $valid = $quotationLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // Save quotation lines
                        foreach ($quotationLines as $quotationLine) {
                            $quotationLine->quotation_id = $model->id;
                            if (!$quotationLine->save()) {
                                throw new \Exception('Failed to save quotation line');
                            }
                        }
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'สร้างใบเสนอราคาเรียบร้อยแล้ว');
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
     * Updates an existing Quotation model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Load existing quotation lines
        $model->quotationLines = $model->getQuotationLines()->all();
        if (empty($model->quotationLines)) {
            $model->quotationLines = [new QuotationLine()];
        }

        if ($model->load(Yii::$app->request->post())) {
            $quotationLines = [];
            $valid = $model->validate();

            if (isset($_POST['QuotationLine'])) {
                foreach ($_POST['QuotationLine'] as $index => $quotationLineData) {
                    if (isset($quotationLineData['id']) && !empty($quotationLineData['id'])) {
                        // Update existing line
                        $quotationLine = QuotationLine::findOne($quotationLineData['id']);
                        if (!$quotationLine) {
                            $quotationLine = new QuotationLine();
                        }
                    } else {
                        // Create new line
                        $quotationLine = new QuotationLine();
                    }
                    $quotationLine->load($quotationLineData, '');
                    $quotationLines[] = $quotationLine;
                    $valid = $quotationLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // Delete existing lines that are not in the new list
                        $existingLineIds = [];
                        foreach ($quotationLines as $quotationLine) {
                            if (!$quotationLine->isNewRecord) {
                                $existingLineIds[] = $quotationLine->id;
                            }
                        }

                        QuotationLine::deleteAll([
                            'and',
                            ['quotation_id' => $model->id],
                            ['not in', 'id', $existingLineIds]
                        ]);

                        // Save quotation lines
                        foreach ($quotationLines as $quotationLine) {
                            $quotationLine->quotation_id = $model->id;
                            if (!$quotationLine->save()) {
                                throw new \Exception('Failed to save quotation line');
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
     * Deletes an existing Quotation model.
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
            // Delete all quotation lines first
            QuotationLine::deleteAll(['quotation_id' => $id]);

            // Delete the quotation record
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
     * Print quotation
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
//    public function actionPrint($id)
//    {
//        $model = $this->findModel($id);
//
//        // Set response format for print
//        $this->layout = false;
//
//        return $this->render('print', [
//            'model' => $model,
//        ]);
//    }
//
//    /**
//     * Generate PDF for quotation
//     * @param int $id
//     * @return mixed
//     * @throws NotFoundHttpException
//     */
//    public function actionPdf($id)
//    {
//        $model = $this->findModel($id);
//
//        // Get HTML content
//        $content = $this->renderPartial('print', [
//            'model' => $model,
//        ]);
//
//        // Configure mPDF
//        $pdf = new \Mpdf\Mpdf([
//            'mode' => 'utf-8',
//            'format' => 'A4',
//            'margin_left' => 15,
//            'margin_right' => 15,
//            'margin_top' => 16,
//            'margin_bottom' => 16,
//            'margin_header' => 9,
//            'margin_footer' => 9,
//            'default_font' => 'garuda'
//        ]);
//
//        $pdf->WriteHTML($content);
//
//        // Output PDF
//        $filename = 'Quotation_' . $model->quotation_no . '.pdf';
//        $pdf->Output($filename, 'I'); // 'I' for inline, 'D' for download
//
//        exit;
//    }

    /**
     * Get product info for AJAX
     */
//    public function actionGetProductInfo($id)
//    {
//        \Yii::$app->response->format = Response::FORMAT_JSON;
//        return \common\models\Product::findOne($id);
//    }

    /**
     * Approve quotation
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = Quotation::APPROVE_STATUS_APPROVED;
        $model->approve_by = Yii::$app->user->id;
        $model->status = Quotation::STATUS_ACTIVE;

        if ($model->save()) {
            // create new job

            $model_job = new Job();
            $model_job->quotation_id = $model->id;
            $model_job->job_no = Job::generateJobNo();
            $model_job->job_date = date('Y-m-d');
            $model_job->status = Job::JOB_STATUS_OPEN;
            $model_job->job_amount = $model->total_amount;
            if($model_job->save(false)){
                $model_line = QuotationLine::find()->where(['quotation_id' => $model->id])->all();
                foreach ($model_line as $line) {
                    $model_job_line = new JobLine();
                    $model_job_line->job_id = $model_job->id;
                    $model_job_line->product_id = $line->product_id;
                    $model_job_line->qty = $line->qty;
                    $model_job_line->line_price = $line->line_price;
                    $model_job_line->line_total = $line->line_total;
                    $model_job_line->save(false);
                }
            }

            Yii::$app->session->setFlash('success', 'อนุมัติใบเสนอราคาเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถอนุมัติใบเสนอราคาได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Reject quotation
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionReject($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = Quotation::APPROVE_STATUS_REJECTED;
        $model->approve_by = Yii::$app->user->id;
        $model->status = Quotation::STATUS_CANCELLED;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'ไม่อนุมัติใบเสนอราคาเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถปฏิเสธใบเสนอราคาได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the Quotation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Quotation the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Quotation::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    // เพิ่ม action นี้ใน Controller ของคุณ (เช่น QuotationController.php)

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

    /**
     * Print quotation
     * @param integer $id
     * @param string $format (html or pdf)
     * @return mixed
     */
    public function actionPrint($id, $format = 'html')
    {
        $quotation = Quotation::findOne($id);

        if (!$quotation) {
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        }

        // ดึงข้อมูล quotation lines พร้อม product
        $quotationLines = QuotationLine::find()
            ->where(['quotation_id' => $id])
            ->with('product')
            ->all();

        if ($format == 'pdf') {
            return $this->generatePdf($quotation, $quotationLines);
        }

        // แสดงแบบ HTML
        $this->layout = '@backend/views/layouts/main_print';
       // $this->layout = false;

        return $this->render('print', [
            'quotation' => $quotation,
            'quotationLines' => $quotationLines,
            'showButtons' => true,
        ]);
    }

    /**
     * Generate PDF
     */
    protected function generatePdf($quotation, $quotationLines)
    {
        // Render HTML content
        $content = $this->renderPartial('print-pdf', [
            'quotation' => $quotation,
            'quotationLines' => $quotationLines,
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
            'fontDir' => [Yii::getAlias('@webroot') . '/fonts/'],
            'fontdata' => [
                'thsarabun' => [
                    'R' => 'THSarabunNew.ttf',
                    'B' => 'THSarabunNew-Bold.ttf',
                ],
            ],
            'default_font' => 'thsarabun',
            'default_font_size' => 14,
        ]);

        // Write HTML to PDF
        $mpdf->WriteHTML($content);

        // Output PDF
        $filename = 'Quotation_' . $quotation->quotation_no . '.pdf';
        $mpdf->Output($filename, 'I'); // I = inline, D = download

        exit;
    }


}