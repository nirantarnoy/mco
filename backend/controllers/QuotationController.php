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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * QuotationController implements the CRUD actions for Quotation model.
 */
class QuotationController extends BaseController
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

    public function beforeAction($action)
    {
        if ($action->id == 'export-express') {
            $auth = Yii::$app->authManager;
            $permissionName = 'quotation/exportexpress';
            if (!$auth->getPermission($permissionName)) {
                try {
                    $permission = $auth->createPermission($permissionName);
                    $permission->description = 'ใบเสนอราคา - ส่งออก Excel (Express)';
                    $auth->add($permission);
                    
                    // Auto-assign to any role that has quotation/index or quotation/view
                    $roles = $auth->getRoles();
                    foreach ($roles as $role) {
                        $rolePermissions = $auth->getPermissionsByRole($role->name);
                        $hasQuotationAccess = false;
                        foreach ($rolePermissions as $rp) {
                            if ($rp->name == 'quotation/index' || $rp->name == 'quotation/view') {
                                $hasQuotationAccess = true;
                                break;
                            }
                        }
                        if ($hasQuotationAccess && !$auth->hasChild($role, $permission)) {
                            $auth->addChild($role, $permission);
                        }
                    }
                } catch (\Exception $e) {
                    Yii::error("Error auto-adding permission: " . $e->getMessage());
                }
            }
        }
        return parent::beforeAction($action);
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
            $discount_total_amount = \Yii::$app->request->post('discount_total_amount');
            $total_vat_amount = \Yii::$app->request->post('total_vat_amount');
            $sum_total_amount = \Yii::$app->request->post('summary_total_amount');

            $quotationLines = [];
            $valid = $model->validate();
            $lineErrors = [];

            if (isset($_POST['QuotationLine'])) {
                foreach ($_POST['QuotationLine'] as $index => $quotationLineData) {
                    $quotationLine = new QuotationLine();
                    $quotationLine->setAttributes($quotationLineData);
                    $quotationLines[] = $quotationLine;
                    if (!$quotationLine->validate()) {
                        $valid = false;
                        $lineErrors[] = "รายการที่ " . ($index + 1) . ": " . implode(', ', $quotationLine->getFirstErrors());
                    }
                }
            }

            if (!$valid) {
                $errorMsg = implode('<br>', $model->getFirstErrors());
                if (!empty($lineErrors)) {
                    $errorMsg .= '<br>' . implode('<br>', $lineErrors);
                }
                Yii::$app->session->setFlash('error', '<b>พบข้อผิดพลาด:</b><br>' . $errorMsg);
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $model->total_discount_amount = $discount_total_amount;
                    $model->vat_percent = 7;
                    $model->vat_total_amount = $total_vat_amount;
                    $model->total_amount = $sum_total_amount;
                    $model->total_amount_text = $model->numtothai($model->total_amount);
                    if ($model->save()) {
                        // Save quotation lines
                        foreach ($quotationLines as $quotationLine) {
                            $quotationLine->quotation_id = $model->id;
                            $quotationLine->status = 1;
                            if (!$quotationLine->save(false)) {
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
            $discount_total_amount = \Yii::$app->request->post('discount_total_amount');
            $total_vat_amount = \Yii::$app->request->post('total_vat_amount');
            $sum_total_amount = \Yii::$app->request->post('summary_total_amount');

            $quotationLines = [];
            $valid = $model->validate();
            $lineErrors = [];

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
                    $quotationLine->setAttributes($quotationLineData);
                    $quotationLines[] = $quotationLine;
                    if (!$quotationLine->validate()) {
                        $valid = false;
                        $lineErrors[] = "รายการที่ " . ($index + 1) . ": " . implode(', ', $quotationLine->getFirstErrors());
                    }
                }
            }

            if (!$valid) {
                $errorMsg = implode('<br>', $model->getFirstErrors());
                if (!empty($lineErrors)) {
                    $errorMsg .= '<br>' . implode('<br>', $lineErrors);
                }
                Yii::$app->session->setFlash('error', '<b>พบข้อผิดพลาด:</b><br>' . $errorMsg);
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $model->total_discount_amount = $discount_total_amount;
                    $model->vat_percent = 7;
                    $model->vat_total_amount = $total_vat_amount;
                    $model->total_amount = $sum_total_amount;
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
                            if (!$quotationLine->save(false)) {
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

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->save()) {
                // Check if a job already exists for this quotation to avoid duplicates
                $model_job = Job::find()->where(['quotation_id' => $model->id])->one();
                if (!$model_job) {
                    $model_job = new Job();
                    $model_job->quotation_id = $model->id;
                    $model_job->job_no = $model->quotation_no;
                    $model_job->job_date = date('Y-m-d');
                    $model_job->status = Job::JOB_STATUS_OPEN;
                    $model_job->job_amount = $model->total_amount;
                    if (!$model_job->save(false)) {
                        throw new \Exception('Failed to save job');
                    }
                } else {
                   // Update existing job amount if needed
                   $model_job->job_amount = $model->total_amount;
                   $model_job->save(false);
                }

                // Sync job lines (re-create them to ensure they match quotation lines)
                JobLine::deleteAll(['job_id' => $model_job->id]);
                
                $model_lines = QuotationLine::find()->where(['quotation_id' => $model->id])->all();
                foreach ($model_lines as $line) {
                    $model_job_line = new JobLine();
                    $model_job_line->job_id = $model_job->id;
                    $model_job_line->product_id = $line->product_id;
                    $model_job_line->product_name = $line->product_name;
                    $model_job_line->qty = $line->qty;
                    $model_job_line->line_price = $line->line_price;
                    $model_job_line->line_total = $line->line_total;
                    if (!$model_job_line->save(false)) {
                        throw new \Exception('Failed to save job line');
                    }
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'อนุมัติใบเสนอราคาเรียบร้อยแล้ว');
            } else {
                throw new \Exception('Failed to approve quotation');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'ไม่สามารถอนุมัติใบเสนอราคาได้: ' . $e->getMessage());
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
                    'display' => $product->name
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
                    'display' => $product->name
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

    public function actionExportExpress($id = null)
    {
        if ($id) {
            $models = Quotation::find()->where(['id' => $id])->all();
        } else {
            $searchModel = new QuotationSearch();
            $params = Yii::$app->request->queryParams;
            if (isset($params['date_from'])) {
                $searchModel->date_from = $params['date_from'];
            }
            if (isset($params['date_to'])) {
                $searchModel->date_to = $params['date_to'];
            }
            $dataProvider = $searchModel->search($params);
            $dataProvider->query->orderBy(['quotation_date' => SORT_ASC, 'quotation_no' => SORT_ASC]);
            $dataProvider->pagination = false;
            $models = $dataProvider->getModels();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Columns Map
        $columns = [
            'DEPCOD', 'DOCNUM', 'DOCDAT', 'CUSCOD', 'CUSNAM', 'STKCOD', 'STKDES', 
            'TRNQTY', 'UNITPR', 'DISC', 'AMOUNT', 'PAYTRM', 'DLVDAT', 'DISCOUNT', 
            'ORGNUM', 'SLMCOD'
        ];

        // Row 1: Headers
        $colIndex = 1;
        foreach ($columns as $header) {
            $sheet->setCellValueByColumnAndRow($colIndex, 1, $header);
            $colIndex++;
        }

        // Row 2: Thai Labels
        $labels = [
            'แผนก', 'เลขที่บิล', 'วันที่บิล', 'รหัสลูกค้า', 'ชื่อลูกค้า', 'รหัสสินค้า', 'ชื่อสินค้า',
            'จำนวน', 'ราคาต่อหน่วย', 'ส่วนลดแต่ละรายการ', 'จำนวนเงิน', 'เครดิต', 'ส่งของวันที่', 'ส่วนลดท้ายบิล',
            'ลำดับสาขา', 'รหัสพนักงานขาย'
        ];
        $colIndex = 1;
        foreach ($labels as $label) {
            $sheet->setCellValueByColumnAndRow($colIndex, 2, $label);
            $colIndex++;
        }

        // Row 3: Red Constraints
        $constraints = [
            'ห้ามเกิน 4 ตัว',
            'ห้ามเกิน 30 ตัว',
            'DD/MM/YYYY',
            'ห้ามเกิน 10 ตัว ห้ามเว้นวรรค,ห้ามใช้เครื่องหมาย / \ " \' .',
            'ห้ามเกิน 60 ตัว',
            'ห้ามเกิน 20 ตัว',
            'ห้ามเกิน 50 ตัว',
            'ตัวเลข',
            'ตัวเลข',
            'ห้ามเกิน 10 ตัว',
            'ตัวเลข',
            'ห้ามเกิน 3 ตัว',
            'DD/MM/YYYY',
            'ห้ามเกิน 10 ตัว',
            'สำนักงานใหญ่ กรอก 0 สาขา เช่น สาขาที่ 00011 ให้กรอก 11 เท่านั้น',
            'ห้ามเกิน 10 ตัว'
        ];
        $colIndex = 1;
        foreach ($constraints as $constraint) {
            $sheet->setCellValueByColumnAndRow($colIndex, 3, $constraint);
            $sheet->getStyleByColumnAndRow($colIndex, 3)->getFont()->getColor()->setARGB('FFFF0000');
            $colIndex++;
        }

        // Row 4: Blue Notes
        $notes = [
            '',
            '',
            '',
            '**กรณีกำหนดรหัสเป็นภาษาอังกฤษ จะต้องใช้อักษรตัวใหญ่',
            '**ห้ามใส่เครื่องหมาย " กรณีต้องการพิมพ์รายงานจาก Express ไปยัง Excel',
            'ห้ามเว้นวรรค,ห้ามใช้เครื่องหมาย / \ " \' .',
            '**ห้ามใส่เครื่องหมาย " กรณีต้องการพิมพ์รายงานจาก Express ไปยัง Excel',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];
        $colIndex = 1;
        foreach ($notes as $note) {
            $sheet->setCellValueByColumnAndRow($colIndex, 4, $note);
            $sheet->getStyleByColumnAndRow($colIndex, 4)->getFont()->getColor()->setARGB('FF0000FF');
            $colIndex++;
        }

        // Row 5: Empty (leave empty)

        // Row 6: Yellow Warning Message
        $sheet->mergeCells('A6:P6');
        $sheet->setCellValue('A6', 'ถ้าใส่ข้อมูลครบแล้วให้ลบบรรทัดที่ 2 ถึง บรรทัดที่ 6 ออก แล้วนำไฟล์นี้ไปใช้ใน EXPRESS PLATFORM');
        
        // Apply yellow background to merged range A6:P6
        $sheet->getStyle('A6:P6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
        $sheet->getStyle('A6:P6')->getFont()->setBold(true);

        // Center alignments for header rows
        for ($c = 1; $c <= 16; $c++) {
            $sheet->getStyleByColumnAndRow($c, 2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($c, 3)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($c, 4)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $row = 7;
        foreach ($models as $model) {
            $lines = QuotationLine::find()->where(['quotation_id' => $model->id])->all();
            foreach ($lines as $line) {
                $colIndex = 1;

                // A: DEPCOD
                $depcod = '';
                if ($model->sale_emp_id) {
                    $emp = \backend\models\Employee::findOne($model->sale_emp_id);
                    if ($emp && $emp->department) {
                        $depcod = mb_substr($emp->department->name, 0, 4);
                    }
                }
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $depcod);

                // B: DOCNUM
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, mb_substr($model->quotation_no ?? '', 0, 30));

                // C: DOCDAT
                $formattedDate = '';
                if ($model->quotation_date) {
                    $formattedDate = date('d/m/Y', strtotime($model->quotation_date));
                }
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $formattedDate);

                // D: CUSCOD
                $cuscod = '';
                if ($model->customer) {
                    $cuscod = $model->customer->code;
                }
                $cuscod = strtoupper(preg_replace('/[\s\/\x22\x27\x5c\.]/', '', $cuscod));
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, mb_substr($cuscod, 0, 10));

                // E: CUSNAM
                $cusnam = $model->customer_name ?: ($model->customer ? $model->customer->name : '');
                $cusnam = str_replace('"', '', $cusnam);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, mb_substr($cusnam, 0, 60));

                // F: STKCOD
                $stkcod = '';
                if ($line->product) {
                    $stkcod = $line->product->code;
                }
                $stkcod = strtoupper(preg_replace('/[\s\/\x22\x27\x5c\.]/', '', $stkcod));
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, mb_substr($stkcod, 0, 20));

                // G: STKDES
                $stkdes = $line->product_name ?: ($line->product ? $line->product->name : '');
                $stkdes = str_replace('"', '', $stkdes);
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, mb_substr($stkdes, 0, 50));

                // H: TRNQTY
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $line->qty);

                // I: UNITPR
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $line->line_price);

                // J: DISC
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $line->discount_amount ?: 0);

                // K: AMOUNT
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $line->line_total);

                // L: PAYTRM
                $paytrm = '';
                if ($model->payment_term_id) {
                    $pt = \backend\models\Paymentterm::findOne($model->payment_term_id);
                    if ($pt) {
                        $paytrm = $pt->day_count;
                    }
                }
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, mb_substr($paytrm, 0, 3));

                // M: DLVDAT
                $dlvdat = '';
                if ($model->quotation_date) {
                    $dlvdat = date('d/m/Y', strtotime($model->quotation_date));
                }
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $dlvdat);

                // N: DISCOUNT
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $model->total_discount_amount ?: 0);

                // O: ORGNUM
                $orgnum = '0';
                if ($model->customer) {
                    if ($model->customer->is_head == 1) {
                        $orgnum = '0';
                    } else {
                        $branch = preg_replace('/[^0-9]/', '', $model->customer->branch_name);
                        $orgnum = ltrim($branch, '0');
                        if ($orgnum === '') {
                            $orgnum = '0';
                        }
                    }
                }
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, $orgnum);

                // P: SLMCOD
                $slmcod = '';
                if ($model->sale_emp_id) {
                    $slmcod = \backend\models\Employee::findCode($model->sale_emp_id);
                }
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, mb_substr($slmcod, 0, 10));

                $row++;
            }
        }

        // Set Auto width for all columns
        foreach (range(1, 16) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $filename = 'QUOTATION_EXPRESS_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
