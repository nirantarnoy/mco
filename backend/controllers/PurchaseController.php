<?php

namespace backend\controllers;

use Yii;
use backend\models\Purchase;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * PurchaseController implements the CRUD actions for Purchase model.
 */
class PurchaseController extends Controller
{
   // public $enableCsrfValidation = false;
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST','GET'],
                ],
            ],
        ];
    }

    /**
     * Lists all Purchase models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Purchase();
//        $purchases = Purchase::find()
//            ->orderBy(['id' => SORT_DESC])
//            ->all();
        $sql = "
            SELECT
                p.*,
                dp.name as dep_name,
                v.name as vendor_name,
                pd.name as product_name,
                pm.name as payment_method_name
                FROM purchase p LEFT JOIN department dp ON p.dedcod = dp.id
                 LEFT JOIN vendor v ON p.supcod = v.id
                 LEFT JOIN product pd ON p.stkcod = pd.id
                 LEFT JOIN payment_method pm ON p.payfrm = pm.id
                ORDER BY p.id DESC
        ";
        $purchases = Yii::$app->db->createCommand($sql)->queryAll();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . json_encode($model->errors));
            }
        }

        return $this->render('index', [
            'model' => $model,
            'purchases' => $purchases,
        ]);
    }

    /**
     * Export to Excel
     * @return mixed
     */
    public function actionExport()
    {
        $sql = "
            SELECT
                p.*,
                dp.name as dep_name,
                v.name as vendor_name,
                pd.name as product_name,
                pm.name as payment_method_name
                FROM purchase p 
                LEFT JOIN department dp ON p.dedcod = dp.id
                LEFT JOIN vendor v ON p.supcod = v.id
                LEFT JOIN product pd ON p.stkcod = pd.id
                LEFT JOIN payment_method pm ON p.payfrm = pm.id
                ORDER BY p.id ASC
        ";

        $purchases = Yii::$app->db->createCommand($sql)->queryAll();

        // สร้าง Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ตั้งค่าหัวตาราง
        $sheet->setTitle('Purchase Data');

        // กำหนด Headers
        $headers = [
            'A1' => 'ลำดับ',
            'B1' => 'ชื่อแผนก',
            'C1' => 'เลขที่เอกสาร',
            'D1' => 'วันที่เอกสาร',
            'E1' => 'ชื่อผู้จำหน่าย',
            'F1' => 'ชื่อผู้จำหน่าย (supnam)',
            'G1' => 'ชื่อสินค้า',
            'H1' => 'รายละเอียดสินค้า',
            'I1' => 'จำนวน',
            'J1' => 'ราคาต่อหน่วย',
            'K1' => 'ส่วนลด',
            'L1' => 'จำนวนเงิน',
            'M1' => 'วิธีชำระ',
            'N1' => 'วันครบกำหนด',
            'O1' => 'เลขประจำตัวผู้เสียภาษี',
            'P1' => 'ส่วนลดทั่วไป',
            'Q1' => 'ที่อยู่ 1',
            'R1' => 'ที่อยู่ 2',
            'S1' => 'ที่อยู่ 3',
            'T1' => 'รหัสไปรษณีย์',
            'U1' => 'เบอร์โทร',
            'V1' => 'ลำดับเรียง',
            'W1' => 'เลขที่ใบกำกับ',
            'X1' => 'วันที่ภาษี',
            'Y1' => 'มูลค่าภาษี',
            'Z1' => 'ยังไม่ได้เอกสาร',
        ];

        // ใส่หัวตาราง
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // จัดรูปแบบหัวตาราง
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A1:Z1')->applyFromArray($headerStyle);

        // ใส่ข้อมูล (เปลี่ยนจาก object เป็น array)
        $row = 2;
        foreach ($purchases as $index => $purchase) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $purchase['dep_name'] ?? '');
            $sheet->setCellValue('C' . $row, $purchase['docnum'] ?? '');
            $sheet->setCellValue('D' . $row, !empty($purchase['docdat']) ? Yii::$app->formatter->asDate($purchase['docdat'], 'php:d/m/Y') : '');
            $sheet->setCellValue('E' . $row, $purchase['vendor_name'] ?? '');
            $sheet->setCellValue('F' . $row, $purchase['supnam'] ?? '');
            $sheet->setCellValue('G' . $row, $purchase['product_name'] ?? '');
            $sheet->setCellValue('H' . $row, $purchase['stkdes'] ?? '');
            $sheet->setCellValue('I' . $row, $purchase['trnqty'] ?? '');
            $sheet->setCellValue('J' . $row, $purchase['untpri'] ?? '');
            $sheet->setCellValue('K' . $row, $purchase['disc'] ?? '');
            $sheet->setCellValue('L' . $row, $purchase['amount'] ?? '');
            $sheet->setCellValue('M' . $row, $purchase['payment_method_name'] ?? '');
            $sheet->setCellValue('N' . $row, !empty($purchase['duedat']) ? Yii::$app->formatter->asDate($purchase['duedat'], 'php:d/m/Y') : '');
            $sheet->setCellValue('O' . $row, $purchase['taxid'] ?? '');
            $sheet->setCellValue('P' . $row, $purchase['discod'] ?? '');
            $sheet->setCellValue('Q' . $row, $purchase['addr01'] ?? '');
            $sheet->setCellValue('R' . $row, $purchase['addr02'] ?? '');
            $sheet->setCellValue('S' . $row, $purchase['addr03'] ?? '');
            $sheet->setCellValue('T' . $row, $purchase['zipcod'] ?? '');
            $sheet->setCellValue('U' . $row, $purchase['telnum'] ?? '');
            $sheet->setCellValue('V' . $row, $purchase['orgnum'] ?? '');
            $sheet->setCellValue('W' . $row, $purchase['refnum'] ?? '');
            $sheet->setCellValue('X' . $row, !empty($purchase['vatdat']) ? Yii::$app->formatter->asDate($purchase['vatdat'], 'php:d/m/Y') : '');
            $sheet->setCellValue('Y' . $row, $purchase['vatpr0'] ?? '');
            $sheet->setCellValue('Z' . $row, $purchase['late'] ?? '');
            $row++;
        }

        // จัดรูปแบบข้อมูล
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];

        $lastRow = $row - 1;
        if ($lastRow > 1) {
            $sheet->getStyle('A2:Z' . $lastRow)->applyFromArray($dataStyle);
        }

        // ปรับความกว้างคอลัมน์อัตโนมัติ
        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // สร้างไฟล์ Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // ตั้งชื่อไฟล์
        $filename = 'purchase_data_' . date('Ymd_His') . '.xlsx';

        // ส่งออกไฟล์
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Updates an existing Purchase model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
            return $this->redirect(['index']);
        }

        $purchases = Purchase::find()
            ->orderBy(['id' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'model' => $model,
            'purchases' => $purchases,
            'isUpdate' => true,
        ]);
    }

    /**
     * Deletes an existing Purchase model via AJAX.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = \Yii::$app->request->post('id');
        try {
            $this->findModel($id)->delete();
            return ['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $e->getMessage()];
        }
    }

    /**
     * Load model for editing via AJAX.
     * @param integer $id
     * @return mixed
     */
    public function actionLoadModel($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $model = $this->findModel($id);
            return [
                'success' => true,
                'data' => $model->attributes
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'ไม่พบข้อมูล'];
        }
    }

    /**
     * Finds the Purchase model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Purchase the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Purchase::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}