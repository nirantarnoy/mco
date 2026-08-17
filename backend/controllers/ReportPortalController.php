<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use backend\models\Job;
use backend\models\Company;
use backend\models\CompanySalary;
use backend\models\VehicleExpense;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportPortalController extends BaseController
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
                    'save-company-salary' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Main Report Portal Dashboard & Summary Hub
     */
    public function actionIndex()
    {
        $companyId = Yii::$app->request->get('company_id', '');
        $startDateFrom = Yii::$app->request->get('start_date_from', '');
        $startDateTo = Yii::$app->request->get('start_date_to', '');
        $jobNo = Yii::$app->request->get('job_no', '');
        $status = Yii::$app->request->get('status', '');
        $month = Yii::$app->request->get('month', date('m'));
        $year = Yii::$app->request->get('year', date('Y'));

        // Query Jobs
        $jobQuery = Job::find()->with(['company', 'quotation']);

        if (!empty($companyId) && $companyId != '0') {
            $companyQueryId = is_array($companyId) ? $companyId : [$companyId];
            if (in_array(1, $companyQueryId)) {
                $jobQuery->andFilterWhere(['OR', ['IN', 'company_id', $companyQueryId], ['company_id' => 0], ['is', 'company_id', null]]);
            } else {
                $jobQuery->andFilterWhere(['IN', 'company_id', $companyQueryId]);
            }
        } elseif (\Yii::$app->session->get('company_id') && \Yii::$app->session->get('company_id') != 100) {
            $sessCompId = \Yii::$app->session->get('company_id');
            if ($sessCompId == 1) {
                $jobQuery->andFilterWhere(['OR', ['company_id' => 1], ['company_id' => 0], ['is', 'company_id', null]]);
            } else {
                $jobQuery->andFilterWhere(['company_id' => $sessCompId]);
            }
        }

        if (!empty($jobNo)) {
            $jobQuery->andFilterWhere(['like', 'job_no', $jobNo]);
        }

        if (!empty($status) || $status === '0') {
            $jobQuery->andFilterWhere(['status' => $status]);
        }

        if (!empty($startDateFrom)) {
            $jobQuery->andFilterWhere(['>=', 'start_date', $startDateFrom]);
        }

        if (!empty($startDateTo)) {
            $jobQuery->andFilterWhere(['<=', 'start_date', $startDateTo]);
        }

        $jobs = $jobQuery->orderBy(['start_date' => SORT_DESC])->all();

        // 1. Process Job Summary Data
        $jobDataList = [];
        $totalJobRevenue = 0;
        $totalJobWithdraw = 0;
        $totalJobVehicleCost = 0;
        $totalJobVehicleWage = 0;
        $totalJobVehicleTotal = 0;
        $totalJobNetProfit = 0;

        foreach ($jobs as $job) {
            $revenueNoVat = floatval($job->getJobAmountNoVat());
            $withdrawAmount = floatval($job->getTotalWithdrawAmount() + $job->getJobExpenseAll());
            $vehicleCost = floatval($job->getVehicleExpenseOnly());
            $vehicleWage = floatval($job->getVehicleWageOnly());
            $vehicleTotal = $vehicleCost + $vehicleWage;

            $netProfit = $revenueNoVat - $withdrawAmount - $vehicleTotal;
            $profitPercentage = $revenueNoVat > 0 ? ($netProfit / $revenueNoVat) * 100 : 0;

            $totalJobRevenue += $revenueNoVat;
            $totalJobWithdraw += $withdrawAmount;
            $totalJobVehicleCost += $vehicleCost;
            $totalJobVehicleWage += $vehicleWage;
            $totalJobVehicleTotal += $vehicleTotal;
            $totalJobNetProfit += $netProfit;

            $jobDataList[] = [
                'model' => $job,
                'job_no' => $job->job_no,
                'start_date' => $job->start_date,
                'status' => $job->status,
                'status_text' => $job->getStatusText(),
                'company_name' => $job->company ? $job->company->name : 'N/A',
                'revenue_no_vat' => $revenueNoVat,
                'withdraw_amount' => $withdrawAmount,
                'vehicle_cost' => $vehicleCost,
                'vehicle_wage' => $vehicleWage,
                'vehicle_total' => $vehicleTotal,
                'net_profit' => $netProfit,
                'profit_percentage' => $profitPercentage,
            ];
        }

        // 2. Process Company Summary Data
        $companies = Company::find()->where(['status' => 1])->all();
        if (empty($companies)) {
            $companies = Company::find()->all();
        }

        $companySummaryList = [];
        $grandCompanyRevenue = 0;
        $grandCompanyWithdraw = 0;
        $grandCompanySalary = 0;
        $grandCompanyVehicleEx = 0;
        $grandCompanyNetProfit = 0;

        // Group Jobs by company
        $jobsByCompany = [];
        foreach ($jobs as $job) {
            $cId = ($job->company_id && $job->company_id != 0) ? $job->company_id : 1;
            if (!isset($jobsByCompany[$cId])) {
                $jobsByCompany[$cId] = [];
            }
            $jobsByCompany[$cId][] = $job;
        }

        foreach ($companies as $comp) {
            $cId = $comp->id;
            $compJobs = $jobsByCompany[$cId] ?? [];

            $compRevenue = 0;
            $compWithdraw = 0;
            $compVehicleCost = 0;
            $compVehicleWage = 0;
            $compVehicleTotal = 0;

            foreach ($compJobs as $job) {
                $compRevenue += floatval($job->getJobAmountNoVat());
                $compWithdraw += floatval($job->getTotalWithdrawAmount() + $job->getJobExpenseAll());
                $compVehicleCost += floatval($job->getVehicleExpenseOnly());
                $compVehicleWage += floatval($job->getVehicleWageOnly());
            }
            $compVehicleTotal = $compVehicleCost + $compVehicleWage;

            // Fetch company monthly salary
            $compSalary = CompanySalary::getSalaryAmount($cId, intval($month), intval($year));

            // Net Profit per Company = Revenue - Direct Expenses - Salary - Vehicle Expenses
            $compNetProfit = $compRevenue - $compWithdraw - $compSalary - $compVehicleTotal;
            $compProfitPercentage = $compRevenue > 0 ? ($compNetProfit / $compRevenue) * 100 : 0;

            $grandCompanyRevenue += $compRevenue;
            $grandCompanyWithdraw += $compWithdraw;
            $grandCompanySalary += $compSalary;
            $grandCompanyVehicleEx += $compVehicleTotal;
            $grandCompanyNetProfit += $compNetProfit;

            $companySummaryList[] = [
                'company' => $comp,
                'company_id' => $cId,
                'company_name' => $comp->name,
                'job_count' => count($compJobs),
                'revenue_no_vat' => $compRevenue,
                'withdraw_amount' => $compWithdraw,
                'salary_amount' => $compSalary,
                'vehicle_cost' => $compVehicleCost,
                'vehicle_wage' => $compVehicleWage,
                'vehicle_total' => $compVehicleTotal,
                'net_profit' => $compNetProfit,
                'profit_percentage' => $compProfitPercentage,
            ];
        }

        return $this->render('index', [
            'startDateFrom' => $startDateFrom,
            'startDateTo' => $startDateTo,
            'companyId' => $companyId,
            'jobNo' => $jobNo,
            'status' => $status,
            'month' => $month,
            'year' => $year,
            'jobDataList' => $jobDataList,
            'totalJobRevenue' => $totalJobRevenue,
            'totalJobWithdraw' => $totalJobWithdraw,
            'totalJobVehicleCost' => $totalJobVehicleCost,
            'totalJobVehicleWage' => $totalJobVehicleWage,
            'totalJobVehicleTotal' => $totalJobVehicleTotal,
            'totalJobNetProfit' => $totalJobNetProfit,
            'companySummaryList' => $companySummaryList,
            'grandCompanyRevenue' => $grandCompanyRevenue,
            'grandCompanyWithdraw' => $grandCompanyWithdraw,
            'grandCompanySalary' => $grandCompanySalary,
            'grandCompanyVehicleEx' => $grandCompanyVehicleEx,
            'grandCompanyNetProfit' => $grandCompanyNetProfit,
        ]);
    }

    /**
     * AJAX endpoint to save/update monthly company salary
     */
    public function actionSaveCompanySalary()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $companyId = Yii::$app->request->post('company_id');
        $month = Yii::$app->request->post('month');
        $year = Yii::$app->request->post('year');
        $amount = Yii::$app->request->post('amount', 0);
        $note = Yii::$app->request->post('note', '');

        if (!$companyId || !$month || !$year) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน'];
        }

        $model = CompanySalary::findOne([
            'company_id' => $companyId,
            'salary_month' => $month,
            'salary_year' => $year,
        ]);

        if (!$model) {
            $model = new CompanySalary();
            $model->company_id = intval($companyId);
            $model->salary_month = intval($month);
            $model->salary_year = intval($year);
        }

        $model->amount = floatval($amount);
        $model->note = $note;

        if ($model->save()) {
            return ['success' => true, 'message' => 'บันทึกเงินเดือนเรียบร้อยแล้ว', 'amount' => $model->amount];
        } else {
            return ['success' => false, 'message' => 'ไม่สามารถบันทึกได้: ' . json_encode($model->errors)];
        }
    }

    /**
     * Export Company Summary Report to Excel
     */
    public function actionExportCompanyExcel()
    {
        $startDateFrom = Yii::$app->request->get('start_date_from', '');
        $startDateTo = Yii::$app->request->get('start_date_to', '');
        $month = Yii::$app->request->get('month', date('m'));
        $year = Yii::$app->request->get('year', date('Y'));

        $jobQuery = Job::find()
            ->andFilterWhere(['>=', 'start_date', $startDateFrom])
            ->andFilterWhere(['<=', 'start_date', $startDateTo]);

        $jobs = $jobQuery->all();
        $companies = Company::find()->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('สรุปผลประกอบการบริษัท');

        $sheet->setCellValue('A1', 'รายงานสรุปผลประกอบการรายบริษัท (ยอดไม่รวม VAT)');
        $sheet->setCellValue('A2', "ช่วงเวลา: {$startDateFrom} ถึง {$startDateTo} (เดือน {$month}/{$year})");

        $headers = [
            'A4' => '#',
            'B4' => 'บริษัท',
            'C4' => 'จำนวนใบงาน',
            'D4' => 'รายรับ (ไม่รวม VAT)',
            'E4' => 'รายจ่ายเบิกของ (ไม่รวม VAT)',
            'F4' => 'เงินเดือนพนักงาน',
            'G4' => 'ค่าใช้จ่ายรถ & ค่าแรง',
            'H4' => 'กำไร/ขาดทุนสุทธิ',
            'I4' => 'เปอร์เซ็นต์กำไร (%)',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $jobsByCompany = [];
        foreach ($jobs as $job) {
            $cId = ($job->company_id && $job->company_id != 0) ? $job->company_id : 1;
            $jobsByCompany[$cId][] = $job;
        }

        $row = 5;
        $i = 1;
        foreach ($companies as $comp) {
            $cId = $comp->id;
            $compJobs = $jobsByCompany[$cId] ?? [];

            $compRevenue = 0;
            $compWithdraw = 0;
            $compVehicleTotal = 0;

            foreach ($compJobs as $job) {
                $compRevenue += floatval($job->getJobAmountNoVat());
                $compWithdraw += floatval($job->getTotalWithdrawAmount() + $job->getJobExpenseAll());
                $compVehicleTotal += floatval($job->getVehicleExpenseAll());
            }

            $compSalary = CompanySalary::getSalaryAmount($cId, intval($month), intval($year));
            $compNetProfit = $compRevenue - $compWithdraw - $compSalary - $compVehicleTotal;
            $compPercentage = $compRevenue > 0 ? ($compNetProfit / $compRevenue) * 100 : 0;

            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $comp->name);
            $sheet->setCellValue('C' . $row, count($compJobs));
            $sheet->setCellValue('D' . $row, number_format($compRevenue, 2));
            $sheet->setCellValue('E' . $row, number_format($compWithdraw, 2));
            $sheet->setCellValue('F' . $row, number_format($compSalary, 2));
            $sheet->setCellValue('G' . $row, number_format($compVehicleTotal, 2));
            $sheet->setCellValue('H' . $row, number_format($compNetProfit, 2));
            $sheet->setCellValue('I' . $row, number_format($compPercentage, 2) . '%');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'company-summary-report-' . date('Ymd-His') . '.xlsx';

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename="' . $filename . '"');

        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    /**
     * Export Job Summary Report to Excel
     */
    public function actionExportJobExcel()
    {
        $startDateFrom = Yii::$app->request->get('start_date_from', '');
        $startDateTo = Yii::$app->request->get('start_date_to', '');
        $companyId = Yii::$app->request->get('company_id', '');
        $jobNo = Yii::$app->request->get('job_no', '');

        $jobQuery = Job::find()->with(['company']);

        if (!empty($companyId) && $companyId != '0') {
            $jobQuery->andFilterWhere(['company_id' => $companyId]);
        }
        if (!empty($jobNo)) {
            $jobQuery->andFilterWhere(['like', 'job_no', $jobNo]);
        }
        if (!empty($startDateFrom)) {
            $jobQuery->andFilterWhere(['>=', 'start_date', $startDateFrom]);
        }
        if (!empty($startDateTo)) {
            $jobQuery->andFilterWhere(['<=', 'start_date', $startDateTo]);
        }

        $jobs = $jobQuery->orderBy(['start_date' => SORT_DESC])->all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('สรุปรายใบงาน');

        $sheet->setCellValue('A1', 'รายงานสรุปผลประกอบการรายใบงาน (ยอดไม่รวม VAT)');
        $sheet->setCellValue('A2', "ช่วงเวลา: {$startDateFrom} ถึง {$startDateTo}");

        $headers = [
            'A4' => '#',
            'B4' => 'เลขใบงาน',
            'C4' => 'บริษัท',
            'D4' => 'วันที่เริ่ม',
            'E4' => 'สถานะ',
            'F4' => 'รายรับ (ไม่รวม VAT)',
            'G4' => 'เบิกสินค้า/คชจ. (ไม่รวม VAT)',
            'H4' => 'ค่าเดินทางรถ',
            'I4' => 'ค่าแรงรถ',
            'J4' => 'รวมคชจ.รถ',
            'K4' => 'กำไร/ขาดทุนสุทธิ',
            'L4' => 'เปอร์เซ็นต์กำไร (%)',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $row = 5;
        $i = 1;
        foreach ($jobs as $job) {
            $revenueNoVat = floatval($job->getJobAmountNoVat());
            $withdrawAmount = floatval($job->getTotalWithdrawAmount() + $job->getJobExpenseAll());
            $vehicleCost = floatval($job->getVehicleExpenseOnly());
            $vehicleWage = floatval($job->getVehicleWageOnly());
            $vehicleTotal = $vehicleCost + $vehicleWage;

            $netProfit = $revenueNoVat - $withdrawAmount - $vehicleTotal;
            $percentage = $revenueNoVat > 0 ? ($netProfit / $revenueNoVat) * 100 : 0;

            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $job->job_no);
            $sheet->setCellValue('C' . $row, $job->company ? $job->company->name : 'N/A');
            $sheet->setCellValue('D' . $row, $job->start_date);
            $sheet->setCellValue('E' . $row, $job->getStatusText());
            $sheet->setCellValue('F' . $row, number_format($revenueNoVat, 2));
            $sheet->setCellValue('G' . $row, number_format($withdrawAmount, 2));
            $sheet->setCellValue('H' . $row, number_format($vehicleCost, 2));
            $sheet->setCellValue('I' . $row, number_format($vehicleWage, 2));
            $sheet->setCellValue('J' . $row, number_format($vehicleTotal, 2));
            $sheet->setCellValue('K' . $row, number_format($netProfit, 2));
            $sheet->setCellValue('L' . $row, number_format($percentage, 2) . '%');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'job-summary-report-' . date('Ymd-His') . '.xlsx';

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename="' . $filename . '"');

        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }
}
