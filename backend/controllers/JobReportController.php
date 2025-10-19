<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use backend\models\Job;
use backend\models\JournalTrans;
use backend\models\JournalTransLine;
use kartik\mpdf\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yii\web\Response;

class JobReportController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new JobReportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionPrint()
    {
        $searchModel = new JobReportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false; // ปิด pagination สำหรับการพิมพ์

       // $this->layout = 'print'; // ใช้ layout สำหรับการพิมพ์

        return $this->render('print', [
            'dataProvider' => $dataProvider,
            'searchParams' => Yii::$app->request->queryParams,
        ]);
    }

    public function actionPrintPdf()
    {
        $searchModel = new JobReportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        $content = $this->renderPartial('pdf', [
            'dataProvider' => $dataProvider,
            'searchParams' => Yii::$app->request->queryParams,
        ]);

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            'destination' => Pdf::DEST_BROWSER,
            'filename' => 'job-report-' . date('Y-m-d') . '.pdf',
            'content' => $content,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '.kv-heading-1{font-size:18px}',
            'options' => ['title' => 'รายงานใบงาน'],
        ]);

        return $pdf->render();
    }

    public function actionExportExcel()
    {
        $searchModel = new JobReportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ตั้งค่า header
        $headers = [
            'A1' => 'เลขใบงาน',
            'B1' => 'วันที่เริ่ม',
            'C1' => 'สถานะ',
            'D1' => 'มูลค่างาน',
            'E1' => 'มูลค่าเบิกของ',
            'F1' => 'กำไร/ขาดทุน',
            'G1' => 'เปอร์เซ็นต์',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // ใส่ข้อมูล
        $row = 2;
        foreach ($dataProvider->getModels() as $model) {
            $totalWithdraw = $model->getTotalWithdrawAmount();
            $profitLoss = $model->job_amount - $totalWithdraw;
            $percentage = $model->job_amount > 0 ? ($profitLoss / $model->job_amount) * 100 : 0;

            $sheet->setCellValue('A' . $row, $model->job_no);
            $sheet->setCellValue('B' . $row, $model->start_date);
            $sheet->setCellValue('C' . $row, $model->status);
            $sheet->setCellValue('D' . $row, number_format($model->job_amount, 2));
            $sheet->setCellValue('E' . $row, number_format($totalWithdraw, 2));
            $sheet->setCellValue('F' . $row, number_format($profitLoss, 2));
            $sheet->setCellValue('G' . $row, number_format($percentage, 2) . '%');
            $row++;
        }

        // ตั้งค่าการดาวน์โหลด
        $writer = new Xlsx($spreadsheet);
        $filename = 'job-report-' . date('Y-m-d') . '.xlsx';

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename="' . $filename . '"');

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $content;
    }
}

class JobReportSearch extends Job
{
    public $company_id;
    public $start_date_from;
    public $start_date_to;

    public function rules()
    {
        return [
            [['job_no', 'status','company_id'], 'safe'],
            [['start_date_from', 'start_date_to'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function search($params)
    {
        $query = Job::find()
            ->with(['journalTrans.journalTransLines']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'start_date' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // กรองตามบริษัท (Multiple Selection)
        if (!empty($this->company_id)) {
            // แปลงเป็น array ถ้ายังไม่ใช่
            $companyIds = is_array($this->company_id) ? $this->company_id : [$this->company_id];

            // ตรวจสอบว่ามีค่า "0" (ทั้งหมด) หรือไม่
            if (in_array("0", $companyIds) || in_array(0, $companyIds)) {
                // ถ้าเลือกทั้งหมด ไม่ต้อง filter
                // Query จะดึงข้อมูลทุกบริษัท
            } else {
                // กรองเฉพาะบริษัทที่เลือก
                $query->andFilterWhere(['IN', 'company_id', $companyIds]);
            }
        }else{
            $query->andFilterWhere(['company_id'=> \Yii::$app->session->get('company_id')]);
        }

        // กรองตามเลขใบงาน
        $query->andFilterWhere(['like', 'job_no', $this->job_no]);

        // กรองตามสถานะ
        $query->andFilterWhere(['=', 'status', $this->status]);

        // กรองตามช่วงวันที่
        if ($this->start_date_from) {
            $query->andFilterWhere(['>=', 'start_date', $this->start_date_from]);
        }

        if ($this->start_date_to) {
            $query->andFilterWhere(['<=', 'start_date', $this->start_date_to]);
        }

        return $dataProvider;
    }
}