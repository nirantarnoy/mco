<?php
namespace backend\controllers;

use Yii;
use backend\models\VehicleExpense;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use PhpOffice\PhpSpreadsheet\IOFactory;

class VehicleExpenseController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'delete-batch' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * หน้า Import
     */
    public function actionImport()
    {
        $model = new \yii\base\DynamicModel(['file']);
        $model->addRule(['file'], 'required')
            ->addRule(['file'], 'file', [
                'extensions' => ['csv'],
                'maxSize' => 1024 * 1024 * 10, // 10MB
            ]);

        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->validate()) {
                try {
                    $result = $this->processCsvFile($model->file->tempName);

                    Yii::$app->session->setFlash('success',
                        "นำเข้าข้อมูลสำเร็จ {$result['success']} รายการ" .
                        ($result['skipped'] > 0 ? " (ข้ามแถวว่าง/รวม {$result['skipped']} แถว)" : "") .
                        ($result['errors'] > 0 ? " (ข้อมูลผิดพลาด {$result['errors']} แถว)" : "")
                    );

                    if ($result['success'] == 0) {
                        Yii::$app->session->setFlash('warning',
                            "ไม่มีข้อมูลที่ถูกนำเข้า กรุณาตรวจสอบรูปแบบไฟล์"
                        );
                    }

                    return $this->redirect(['list']);
                } catch (\Exception $e) {
                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
                    Yii::error("Import Exception: " . $e->getMessage());
                    Yii::error("Stack trace: " . $e->getTraceAsString());
                }
            } else {
                Yii::$app->session->setFlash('error', 'ไฟล์ไม่ถูกต้อง: ' . json_encode($model->errors));
            }
        }

        return $this->render('import', [
            'model' => $model,
        ]);
    }

    /**
     * ประมวลผลไฟล์ CSV
     */
    private function processCsvFile($filePath)
    {
        $batchId = date('YmdHis') . '_' . Yii::$app->user->id;
        $successCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $currentDate = null;

        $errorDetails = [];

        // อ่านไฟล์
        $content = file_get_contents($filePath);

        // ลบ BOM ถ้ามี
        $content = str_replace("\xEF\xBB\xBF", '', $content);

        // แปลงเป็น UTF-8 (ใช้ iconv แทน mb_detect_encoding)
        if (!mb_check_encoding($content, 'UTF-8')) {
            // ลองแปลงจาก Windows-874 (Thai)
            $converted = @iconv('Windows-874', 'UTF-8//IGNORE', $content);
            if ($converted !== false) {
                $content = $converted;
            } else {
                // ถ้าไม่ได้ ลองแปลงจาก ISO-8859-1
                $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $content);
                if ($converted !== false) {
                    $content = $converted;
                }
            }
        }

        // บันทึกเป็นไฟล์ชั่วคราว
        $tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($tempFile, $content);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $handle = fopen($tempFile, 'r');
            if ($handle === false) {
                throw new \Exception('ไม่สามารถเปิดไฟล์ CSV ได้');
            }

            $rowIndex = 0;

            while (($data = fgetcsv($handle, 10000, ',')) !== false) {
                $rowIndex++;

                // ข้าม header
                if ($rowIndex == 1) {
                    continue;
                }

                // ตรวจสอบจำนวนคอลัมน์
                if (count($data) < 7) {
                    $skippedCount++;
                    continue;
                }

                $colA = trim($data[0] ?? '');
                $colB = trim($data[1] ?? '');
                $colC = trim($data[2] ?? '');
                $colD = trim($data[3] ?? '');
                $colE = trim($data[4] ?? '');
                $colF = trim($data[5] ?? '');
                $colG = trim($data[6] ?? '');

                // ข้ามแถว "รวม"
                if (stripos($colA, 'รวม') !== false) {
                    $skippedCount++;
                    continue;
                }

                // ข้ามแถวว่าง
                if (empty($colA) && empty($colB) && empty($colC) && empty($colD) && empty($colE) && empty($colF) && empty($colG)) {
                    $skippedCount++;
                    continue;
                }

                // จัดการวันที่
                if (!empty($colA)) {
                    $currentDate = $this->parseDate($colA);
                }

                if (!$currentDate) {
                    $currentDate = date('Y-m-d');
                }

                // ทำความสะอาด Job No
                $jobNo = null;
                if (!empty($colB)) {
                    if (preg_match('/(RY-[A-Z]{2}\d{2}-\d{6})/i', $colB, $matches)) {
                        $jobNo = strtoupper($matches[1]);
                    } elseif (preg_match('/(RY-[A-Z]{2}\d{2}-\d{5})/i', $colB, $matches)) {
                        $jobNo = strtoupper($matches[1]);
                    }
                }

                $vehicleNo = !empty($colC) ? trim($colC) : null;
                $totalDistance = $this->parseNumber($colD);
                $vehicleCost = $this->parseNumber($colE);
                $passengerCount = $this->parseNumber($colF);
                $totalWage = $this->parseNumber($colG);

                // ข้ามแถวที่ไม่มีข้อมูลสำคัญ
                if (empty($vehicleNo) && $totalDistance == 0 && $vehicleCost == 0 && $totalWage == 0) {
                    $skippedCount++;
                    continue;
                }

                // บันทึกข้อมูล
                $expense = new VehicleExpense();
                $expense->expense_date = $currentDate;
                $expense->job_no = $jobNo;
                $expense->vehicle_no = $vehicleNo;
                $expense->total_distance = $totalDistance;
                $expense->vehicle_cost = $vehicleCost;
                $expense->passenger_count = intval($passengerCount);
                $expense->total_wage = $totalWage;
                $expense->import_batch = $batchId;

                if ($expense->save()) {
                    $successCount++;
                } else {
                    $errorCount++;

                    if (count($errorDetails) < 10) {
                        $errorDetails[] = [
                            'row' => $rowIndex,
                            'data' => [
                                'date' => $colA,
                                'job_no' => $colB,
                                'vehicle' => $colC,
                                'distance' => $colD,
                                'cost' => $colE,
                                'passengers' => $colF,
                                'wage' => $colG,
                            ],
                            'parsed' => [
                                'date' => $currentDate,
                                'job_no' => $jobNo,
                                'vehicle' => $vehicleNo,
                                'distance' => $totalDistance,
                                'cost' => $vehicleCost,
                                'passengers' => $passengerCount,
                                'wage' => $totalWage,
                            ],
                            'errors' => $expense->errors,
                        ];
                    }

                    Yii::error("Row {$rowIndex} validation failed: " . json_encode($expense->errors), __METHOD__);
                }
            }

            fclose($handle);
            @unlink($tempFile);

            $transaction->commit();

            if (!empty($errorDetails)) {
                Yii::$app->session->set('import_errors', $errorDetails);
            }

            return [
                'success' => $successCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount,
            ];

        } catch (\Exception $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            if (isset($tempFile) && file_exists($tempFile)) {
                @unlink($tempFile);
            }
            $transaction->rollBack();
            Yii::error("Import error: " . $e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * แปลงวันที่จากรูปแบบต่างๆ
     */
    private function parseDate($dateValue)
    {
        if (empty($dateValue)) {
            return date('Y-m-d');
        }

        $dateStr = trim($dateValue);

        // ลบคำว่า "รวม" ออก
        $dateStr = str_replace('รวม', '', $dateStr);
        $dateStr = trim($dateStr);

        // แทนที่ separator ต่างๆ ด้วย -
        $dateStr = str_replace(['/', '.', ' '], '-', $dateStr);

        // รูปแบบ DD/MM/YYYY หรือ DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2,4})/', $dateStr, $matches)) {
            $day = intval($matches[1]);
            $month = intval($matches[2]);
            $year = intval($matches[3]);

            // ถ้าปีเป็น 2 หลัก
            if ($year < 100) {
                $year = $year < 70 ? 2000 + $year : 1900 + $year;
            }

            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        // ลองแปลงด้วย strtotime
        try {
            $timestamp = strtotime($dateStr);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        } catch (\Exception $e) {
            // ถ้าแปลงไม่ได้ให้ใช้วันที่ปัจจุบัน
        }

        return date('Y-m-d');
    }

    /**
     * แปลงค่าตัวเลขจาก CSV
     */
    private function parseNumber($value)
    {
        if (empty($value) || $value === '') {
            return 0;
        }

        // ลบ comma และช่องว่าง
        $value = str_replace([',', ' ', '฿', '"'], '', trim($value));

        // แปลงเป็นตัวเลข
        return floatval($value);
    }

    /**
     * หน้ารายการข้อมูล
     */
    public function actionList()
    {
        $searchModel = new \yii\base\DynamicModel([
            'job_no', 'vehicle_no', 'date_from', 'date_to', 'import_batch'
        ]);

        $searchModel->addRule(['job_no', 'vehicle_no', 'import_batch'], 'string')
            ->addRule(['date_from', 'date_to'], 'safe');

        $query = VehicleExpense::find()->orderBy(['expense_date' => SORT_DESC, 'id' => SORT_DESC]);

        $params = Yii::$app->request->queryParams;
        if (!empty($params['job_no'])) {
            $query->andWhere(['like', 'job_no', $params['job_no']]);
        }
        if (!empty($params['vehicle_no'])) {
            $query->andWhere(['like', 'vehicle_no', $params['vehicle_no']]);
        }
        if (!empty($params['date_from'])) {
            $query->andWhere(['>=', 'expense_date', $params['date_from']]);
        }
        if (!empty($params['date_to'])) {
            $query->andWhere(['<=', 'expense_date', $params['date_to']]);
        }
        if (!empty($params['import_batch'])) {
            $query->andWhere(['import_batch' => $params['import_batch']]);
        }

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * ลบข้อมูลทั้ง Batch
     */
    public function actionDeleteBatch($batch)
    {
        $count = VehicleExpense::deleteAll(['import_batch' => $batch]);
        Yii::$app->session->setFlash('success', "ลบข้อมูล {$count} รายการสำเร็จ");
        return $this->redirect(['list']);
    }

    /**
     * ดาวน์โหลดไฟล์ Template Excel
     */
    public function actionDownloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // หัวตาราง
        $headers = [
            'A1' => 'วันที่ใช้งานรถ',
            'B1' => 'Job no. (เช่น RY-QTXX-XXXXXX)',
            'C1' => 'ทะเบียนรถ',
            'D1' => 'ระยะทางรวม (กม)',
            'E1' => 'ค่าใช้จ่ายรถ (บาท)',
            'F1' => 'จำนวนผู้ใช้รถ',
            'G1' => 'ค่าจ้างรวม (บาท)',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // ข้อมูลตัวอย่าง
        $sheet->setCellValue('A2', '23/07/2023');
        $sheet->setCellValue('B2', 'RY-QT23-000007');
        $sheet->setCellValue('C2', 'บล 1057');
        $sheet->setCellValue('D2', 76);
        $sheet->setCellValue('E2', 1300);
        $sheet->setCellValue('F2', 2);
        $sheet->setCellValue('G2', 4000);

        $sheet->setCellValue('A3', '25/05/2025');
        $sheet->setCellValue('B3', 'RY-QT25-000103');
        $sheet->setCellValue('C3', 'กล 2432');
        $sheet->setCellValue('D3', 65);
        $sheet->setCellValue('E3', 0);
        $sheet->setCellValue('F3', 1);
        $sheet->setCellValue('G3', 0);

        $sheet->setCellValue('A4', '');
        $sheet->setCellValue('B4', 'RY-QT25-000024');
        $sheet->setCellValue('C4', 'กฉ 2432');
        $sheet->setCellValue('D4', 80);
        $sheet->setCellValue('E4', 400);
        $sheet->setCellValue('F4', 1);
        $sheet->setCellValue('G4', 0);

        // คำแนะนำ
        $sheet->setCellValue('A6', 'คำแนะนำ:');
        $sheet->getStyle('A6')->getFont()->setBold(true);
        $sheet->setCellValue('A7', '1. หลังจากกรอกข้อมูลเรียบร้อย ให้ Save As เป็น CSV (Comma delimited) (*.csv)');
        $sheet->setCellValue('A8', '2. ถ้าแถวใดไม่มีวันที่ ระบบจะใช้วันที่จากแถวก่อนหน้า');
        $sheet->setCellValue('A9', '3. แถว "รวม" จะถูกข้ามโดยอัตโนมัติ');
        $sheet->setCellValue('A10', '4. Job No ต้องเป็นรูปแบบ RY-QTXX-XXXXXX');

        // ปรับความกว้างคอลัมน์
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ส่งออกไฟล์ Excel
        $filename = 'vehicle_expense_template_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * ดาวน์โหลดไฟล์ Template CSV
     */
    public function actionDownloadCsvTemplate()
    {
        $filename = 'vehicle_expense_template_' . date('Ymd') . '.csv';

        // สร้างเนื้อหา CSV
        $csv = [];

        // Header
        $csv[] = [
            'วันที่ใช้งานรถ',
            'Job no. (เช่น RY-QTXX-XXXXXX)',
            'ทะเบียนรถ',
            'ระยะทางรวม (กม)',
            'ค่าใช้จ่ายรถ (บาท)',
            'จำนวนผู้ใช้รถ',
            'ค่าจ้างรวม (บาท)',
        ];

        // ข้อมูลตัวอย่าง
        $csv[] = ['23/07/2023', 'RY-QT23-000007', 'บล 1057', '76', '1300', '2', '4000'];
        $csv[] = ['25/05/2025', 'RY-QT25-000103', 'กล 2432', '65', '0', '1', '0'];
        $csv[] = ['', 'RY-QT25-000024', 'กฉ 2432', '80', '400', '1', '0'];

        // สร้าง CSV content
        $output = fopen('php://temp', 'r+');

        // เพิ่ม BOM สำหรับ UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        foreach ($csv as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        // ส่งออกไฟล์
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo $csvContent;
        exit;
    }
}
