<?php

namespace backend\controllers;

use Yii;
use backend\models\PurchaseMaster;
use backend\models\Company;
use yii\web\Controller;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PurchasetaxreportController extends BaseController
{
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

    public function actionIndex()
    {
        $month = Yii::$app->request->get('month', date('m'));
        $year = Yii::$app->request->get('year', date('Y'));
        $data = $this->getReportData($month, $year);

        return $this->render('index', [
            'models' => $data,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function actionPrint()
    {
        $this->layout = 'empty';
        $month = Yii::$app->request->get('month', date('m'));
        $year = Yii::$app->request->get('year', date('Y'));
        $data = $this->getReportData($month, $year);

        return $this->render('_print', [
            'models' => $data,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function actionExportPdf()
    {
        $month = Yii::$app->request->get('month', date('m'));
        $year = Yii::$app->request->get('year', date('Y'));
        $data = $this->getReportData($month, $year);

        $content = $this->renderPartial('_report', [
            'models' => $data,
            'month' => $month,
            'year' => $year,
        ]);

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $content,
            'cssInline' => '
                body { font-family: "Garuda", "sans-serif"; font-size: 11pt; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #000; padding: 3px 5px; }
                th { text-align: center; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-left { text-align: left; }
                .no-border { border: none !important; }
            ',
            'options' => ['title' => 'รายงานภาษีซื้อ'],
            'methods' => [
                'SetHeader' => [''],
                'SetFooter' => ['หน้า {PAGENO}'],
            ]
        ]);

        return $pdf->render();
    }

    public function actionExportExcel()
    {
        $month = Yii::$app->request->get('month', date('m'));
        $year = Yii::$app->request->get('year', date('Y'));
        $data = $this->getReportData($month, $year);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $months = [
            '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
            '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
            '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
        ];
        $monthName = isset($months[$month]) ? $months[$month] : '';
        $thaiYear = (int)$year + 543;
        
        $companyId = Yii::$app->session->get('company_id');
        $companyName = '';
        $companyAddress = '';
        if ($companyId) {
            $company = Company::findOne($companyId);
            if ($company) {
                $companyName = $company->name;
                $companyAddress = trim($company->address_1 . ' ' . $company->address_2 . ' ' . $company->address_3);
            }
        }

        // Header Title
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'รายงานภาษีซื้อ (สำนักงานใหญ่)');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A3', 'ชื่อผู้ประกอบการ');
        $sheet->setCellValue('B3', $companyName);
        $sheet->setCellValue('E3', 'สำนักงานใหญ่');
        $sheet->setCellValue('G3', 'เดือน ' . $monthName . ' ' . $thaiYear);

        $sheet->setCellValue('A4', 'ชื่อสถานประกอบการ');
        $sheet->setCellValue('B4', $companyName);
        $sheet->setCellValue('E4', 'สำนักงานใหญ่');

        $sheet->setCellValue('A5', 'ที่อยู่ผู้ประกอบการ');
        $sheet->mergeCells('B5:J5');
        $sheet->setCellValue('B5', $companyAddress);

        $sheet->getStyle('A3:A5')->getFont()->setBold(true);

        // Table Header
        $row = 6;
        $sheet->mergeCells("A$row:A".($row+1));
        $sheet->setCellValue("A$row", 'ลำดับที่');
        
        $sheet->mergeCells("B$row:C$row");
        $sheet->setCellValue("B$row", 'ใบกำกับภาษี');
        $sheet->setCellValue("B".($row+1), 'วัน.เดือน/ปี');
        $sheet->setCellValue("C".($row+1), 'เลขที่ เล่มที่');
        
        $sheet->mergeCells("D$row:D".($row+1));
        $sheet->setCellValue("D$row", 'เลขประจำตัวผู้เสียภาษีอากร');
        
        $sheet->mergeCells("E$row:F$row");
        $sheet->setCellValue("E$row", 'สถานประกอบการ');
        $sheet->setCellValue("E".($row+1), 'สนง.ใหญ่');
        $sheet->setCellValue("F".($row+1), 'สาขา');
        
        $sheet->mergeCells("G$row:G".($row+1));
        $sheet->setCellValue("G$row", 'ชื่อผู้ขายสินค้า/ผู้ให้บริการ');
        
        $sheet->mergeCells("H$row:H".($row+1));
        $sheet->setCellValue("H$row", 'มูลค่าสินค้า NON-VAT');
        
        $sheet->mergeCells("I$row:I".($row+1));
        $sheet->setCellValue("I$row", 'มูลค่าสินค้าหรือบริการ');
        
        $sheet->mergeCells("J$row:J".($row+1));
        $sheet->setCellValue("J$row", 'จำนวนเงินภาษีมูลค่าเพิ่ม');
        
        $sheet->mergeCells("K$row:K".($row+1));
        $sheet->setCellValue("K$row", 'หมายเหตุ');

        $sheet->getStyle("A$row:K".($row+1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$row:K".($row+1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A$row:K".($row+1))->getFont()->setBold(true);
        $sheet->getStyle("A$row:K".($row+1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Data rows
        $row = 8;
        $i = 1;
        $totalBaseHq = 0;
        $totalVatHq = 0;
        $totalBaseBr = 0;
        $totalVatBr = 0;

        foreach ($data as $m) {
            $vatDate = $m->vatdat ? date('d/m/Y', strtotime($m->vatdat)) : ($m->docdat ? date('d/m/Y', strtotime($m->docdat)) : '');
            $isHq = ($m->orgnum == '00000' || empty($m->orgnum));
            
            $base = (float)$m->vatpr0;
            $vat = (float)$m->vat_amount;
            
            if ($isHq) {
                $totalBaseHq += $base;
                $totalVatHq += $vat;
            } else {
                $totalBaseBr += $base;
                $totalVatBr += $vat;
            }

            $sheet->setCellValue("A$row", $i++);
            $sheet->setCellValue("B$row", $vatDate);
            $sheet->setCellValue("C$row", $m->invoice_no ? $m->invoice_no : $m->refnum);
            $sheet->setCellValueExplicit("D$row", $m->taxid, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("E$row", $isHq ? '/' : '');
            $sheet->setCellValueExplicit("F$row", !$isHq ? $m->orgnum : '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("G$row", $m->supnam);
            $sheet->setCellValue("H$row", ''); // NON-VAT
            $sheet->setCellValue("I$row", $base);
            $sheet->setCellValue("J$row", $vat);
            $sheet->setCellValue("K$row", $m->remark);

            $sheet->getStyle("I$row:J$row")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("A$row:K$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            $sheet->getStyle("A$row:B$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D$row:F$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            $row++;
        }

        // Totals HQ
        $sheet->mergeCells("G$row:H$row");
        $sheet->setCellValue("G$row", 'รวมยอดรายงานภาษีซื้อ (สำนักงานใหญ่)');
        $sheet->setCellValue("I$row", $totalBaseHq);
        $sheet->setCellValue("J$row", $totalVatHq);
        $sheet->getStyle("G$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("G$row:J$row")->getFont()->setBold(true);
        $sheet->getStyle("I$row:J$row")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("G$row:J$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $row += 2;
        
        // Separator for Branch
        $sheet->mergeCells("A$row:K$row");
        $sheet->setCellValue("A$row", 'รายงานภาษีซื้อ (สาขา)');
        $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(12);
        $row++;
        
        // Branch Totals Table (just summary as in the image)
        $sheet->mergeCells("G$row:H$row");
        $sheet->setCellValue("G$row", 'รวมยอดจำนวนภาษีซื้อ (สาขา)');
        $sheet->setCellValue("I$row", $totalBaseBr);
        $sheet->setCellValue("J$row", $totalVatBr);
        $sheet->getStyle("G$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("G$row:J$row")->getFont()->setBold(true);
        $sheet->getStyle("I$row:J$row")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("G$row:J$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        $sheet->mergeCells("G$row:H$row");
        $sheet->setCellValue("G$row", 'รวมยอดจำนวนภาษีซื้อทั้งสิ้น');
        $sheet->setCellValue("I$row", $totalBaseHq + $totalBaseBr);
        $sheet->setCellValue("J$row", $totalVatHq + $totalVatBr);
        $sheet->getStyle("G$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("G$row:J$row")->getFont()->setBold(true);
        $sheet->getStyle("I$row:J$row")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("G$row:J$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'PurchaseTaxReport_' . $year . $month . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    protected function getReportData($month, $year)
    {
        $companyId = Yii::$app->session->get('company_id');
        $query = PurchaseMaster::find()
            ->where(['!=', 'status', PurchaseMaster::STATUS_CANCELLED])
            ->andWhere([
                'OR',
                ['AND', ['IS NOT', 'vatdat', null], ['YEAR(vatdat)' => $year], ['MONTH(vatdat)' => $month]],
                ['AND', ['vatdat' => null], ['YEAR(docdat)' => $year], ['MONTH(docdat)' => $month]]
            ])
            ->orderBy(['vatdat' => SORT_ASC, 'docdat' => SORT_ASC]);
            
        if ($companyId && $companyId != 100) {
            $query->andWhere(['company_id' => $companyId]);
        }

        return $query->all();
    }
}
