<?php

namespace backend\controllers;

use Yii;
use backend\models\DeliveryNote;
use backend\models\DeliveryNoteSearch;
use backend\models\DeliveryNoteLine;
use backend\models\Job;
use backend\models\Quotation;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * DeliveryNoteController implements the CRUD actions for DeliveryNote model.
 */
class DeliveryNoteController extends BaseController
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
     * Lists all DeliveryNote models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DeliveryNoteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DeliveryNote model.
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
     * Creates a new DeliveryNote model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new DeliveryNote();
        $model->date = date('Y-m-d');
        $model->status = DeliveryNote::STATUS_DRAFT;
        
        $job_id = Yii::$app->request->get('job_id');
        if ($job_id) {
            $model->job_id = $job_id;
            $this->loadJobDetails($model, $job_id);
        }

        // Initialize lines
        $details = [new DeliveryNoteLine()];

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->created_by = Yii::$app->user->id;
                if ($model->save()) {
                    $detailsData = Yii::$app->request->post('DeliveryNoteLine', []);
                    $this->saveDetails($model, $detailsData);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'details' => $details,
        ]);
    }

    /**
     * Updates an existing DeliveryNote model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $details = $model->deliveryNoteLines;
        if (empty($details)) {
            $details = [new DeliveryNoteLine()];
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->updated_by = Yii::$app->user->id;
                if ($model->save()) {
                    // Delete existing lines
                    DeliveryNoteLine::deleteAll(['delivery_note_id' => $model->id]);
                    
                    $detailsData = Yii::$app->request->post('DeliveryNoteLine', []);
                    $this->saveDetails($model, $detailsData);

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'details' => $details,
        ]);
    }

    /**
     * Deletes an existing DeliveryNote model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Print Delivery Note
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $this->layout = false;
        return $this->render('print', [
            'model' => $model,
        ]);
    }

    /**
     * Export to PDF
     */
    public function actionExportPdf($id)
    {
        $model = $this->findModel($id);
        
        $content = $this->renderPartial('print', [
            'model' => $model,
        ]);

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'default_font' => 'garuda'
        ]);

        $pdf->WriteHTML($content);
        $pdf->Output('DeliveryNote_' . $model->dn_no . '.pdf', 'I');
        exit;
    }

    /**
     * Export to Excel
     */
    public function actionExportExcel($id)
    {
        $model = $this->findModel($id);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'ใบตรวจรับ / Delivery note');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        // Company Info (Simplified)
        $sheet->setCellValue('A3', 'M.C.O. COMPANY LIMITED');
        $sheet->setCellValue('D3', 'Date : ' . Yii::$app->formatter->asDate($model->date, 'php:d/m/Y'));

        $sheet->setCellValue('A4', '8/18 Koh-Kloy Rd., T. Cherngnoen,');
        $sheet->setCellValue('A5', 'A. Muang, Rayong 21000 Thailand.');
        $sheet->setCellValue('A6', 'ID.NO. 0215543000985');
        $sheet->setCellValue('A7', 'Tel : (038)-875258-9 , 094-6984555');

        $sheet->setCellValue('D7', 'OUR REF : ' . $model->our_ref);
        
        $sheet->setCellValue('A8', 'To : ' . $model->customer_name);
        $sheet->setCellValue('D8', 'FROM : ' . $model->from_name);
        
        $sheet->setCellValue('A9', $model->address);
        $sheet->setCellValue('D9', 'TEL : ' . $model->tel);
        
        $sheet->setCellValue('D10', 'REF.NO. : ' . $model->ref_no);
        $sheet->setCellValue('A11', 'Attn : ' . $model->attn);
        $sheet->setCellValue('D11', 'Page No. : ' . $model->page_no);

        // Table Header
        $row = 13;
        $sheet->setCellValue('A'.$row, 'ITEM');
        $sheet->setCellValue('B'.$row, 'DESCRIPTION');
        $sheet->setCellValue('C'.$row, 'P/N');
        $sheet->setCellValue('D'.$row, 'Q\'TY');
        $sheet->setCellValue('E'.$row, 'UNIT');
        
        $sheet->getStyle("A$row:E$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:E$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A$row:E$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Data
        $row++;
        foreach ($model->deliveryNoteLines as $index => $line) {
            $sheet->setCellValue('A'.$row, $line->item_no);
            $sheet->setCellValue('B'.$row, $line->description);
            $sheet->setCellValue('C'.$row, $line->part_no);
            $sheet->setCellValue('D'.$row, $line->qty);
            $sheet->setCellValue('E'.$row, $line->unit ? $line->unit->name : '');
            
            $sheet->getStyle("A$row:E$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $row++;
        }

        // Fill empty rows to look like the form if needed, or just stop here.
        
        // Signatures
        $row += 3;
        $sheet->setCellValue('A'.$row, 'Recipient _____________________');
        $sheet->setCellValue('D'.$row, 'Sender _____________________');
        $row++;
        $sheet->setCellValue('A'.$row, '(_____________________)');
        $sheet->setCellValue('D'.$row, '(_____________________)');
        $row++;
        $sheet->setCellValue('A'.$row, 'Date _____________________');
        $sheet->setCellValue('D'.$row, 'Date _____________________');

        // Auto size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'DeliveryNote_' . $model->dn_no . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * AJAX Get Job Details
     */
    public function actionGetJobDetails($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $job = Job::findOne($id);
        if ($job) {
            $quotation = $job->quotation;
            $customer = $quotation ? $quotation->customer : null;
            
            $lines = [];
            
            // Try to get lines from JobLine first
            $jobLines = $job->jobLines;
            if (!empty($jobLines)) {
                foreach ($jobLines as $jLine) {
                    $product = $jLine->product;
                    $unitId = null;
                    if ($jLine->hasAttribute('unit_id')) {
                        $unitId = $jLine->unit_id;
                    }
                    if (!$unitId && $product) {
                        $unitId = $product->unit_id;
                    }

                    $lines[] = [
                        'item_no' => count($lines) + 1,
                        'description' => $product ? $product->name : $jLine->note,
                        'part_no' => $product ? $product->code : '',
                        'qty' => $jLine->qty,
                        'unit_id' => $unitId,
                    ];
                }
            } 
            // Fallback to QuotationLine
            elseif ($quotation && $quotation->quotationLines) {
                foreach ($quotation->quotationLines as $qLine) {
                    $product = $qLine->product;
                    $lines[] = [
                        'item_no' => count($lines) + 1,
                        'description' => $qLine->product_name,
                        'part_no' => $product ? $product->code : '',
                        'qty' => $qLine->qty,
                        'unit_id' => $product ? $product->unit_id : null,
                    ];
                }
            }

            return [
                'customer_name' => $customer ? $customer->name : '',
                'address' => $customer ? 'เลขที่ ' . $customer->home_number . ' ถนน ' . $customer->street . ' ' . $customer->city_name . ' ' . $customer->province_name : '',
                'tel' => $customer ? $customer->phone : '',
                'attn' => '', // Attn might be in Quotation or Customer contact
                'our_ref' => $job->job_no,
                'ref_no' => $quotation ? $quotation->quotation_no : '',
                'lines' => $lines
            ];
        }
        return null;
    }

    protected function loadJobDetails($model, $job_id)
    {
        $job = Job::findOne($job_id);
        if ($job) {
            $quotation = $job->quotation;
            $customer = $quotation ? $quotation->customer : null;
            
            $model->customer_id = $customer ? $customer->id : null;
            $model->customer_name = $customer ? $customer->name : '';
            $model->address = $customer ? 'เลขที่ ' . $customer->home_number . ' ถนน ' . $customer->street . ' ' . $customer->city_name . ' ' . $customer->province_name : '';
            $model->tel = $customer ? $customer->phone : '';
            $model->our_ref = $job->job_no;
            $model->ref_no = $quotation ? $quotation->quotation_no : '';
            // Attn might be in Quotation or Customer contact
        }
    }

    protected function saveDetails($model, $detailsData)
    {
        foreach ($detailsData as $data) {
            if (empty($data['description']) && empty($data['qty'])) {
                continue;
            }
            $line = new DeliveryNoteLine();
            $line->delivery_note_id = $model->id;
            $line->attributes = $data;
            $line->save();
        }
    }

    protected function findModel($id)
    {
        if (($model = DeliveryNote::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
