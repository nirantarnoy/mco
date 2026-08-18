<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use backend\models\Job;
use backend\models\Purch;
use backend\models\PurchaseMaster;
use backend\models\Invoice;
use backend\models\Quotation;
use backend\models\VehicleExpense;
use backend\models\DriverWageReport;
use backend\models\Company;
use backend\models\BankAccount;
use backend\models\PettyCashVoucher;
use backend\models\JobActivityStatus;
use backend\models\MonthlyAccountClosing;

class ExecutiveDashboardController extends BaseController
{
    /**
     * Normalize any date input (DD/MM/YYYY or YYYY-MM-DD or BE year 2569) to standard YYYY-MM-DD
     */
    private function normalizeDate($dateStr)
    {
        if (empty($dateStr)) return '';
        $dateStr = str_replace('/', '-', trim($dateStr));
        $parts = explode('-', $dateStr);
        if (count($parts) == 3) {
            if (strlen($parts[0]) == 4) {
                // YYYY-MM-DD
                $year = (int)$parts[0];
                if ($year > 2400) $year -= 543;
                return sprintf('%04d-%02d-%02d', $year, (int)$parts[1], (int)$parts[2]);
            } else {
                // DD-MM-YYYY or D-M-YYYY
                $year = (int)$parts[2];
                if ($year > 2400) $year -= 543;
                return sprintf('%04d-%02d-%02d', $year, (int)$parts[1], (int)$parts[0]);
            }
        }
        $ts = strtotime($dateStr);
        return $ts ? date('Y-m-d', $ts) : $dateStr;
    }

    /**
     * Executive Dashboard 8.8 Main Page
     */
    public function actionIndex()
    {
        $companyId = Yii::$app->request->get('company_id', '');
        $rawFromDate = Yii::$app->request->get('from_date', '');
        $rawToDate = Yii::$app->request->get('to_date', '');
        
        $fromDate = !empty($rawFromDate) ? $this->normalizeDate($rawFromDate) : date('Y-m-01');
        $toDate = !empty($rawToDate) ? $this->normalizeDate($rawToDate) : date('Y-m-d');

        if (strtotime($fromDate) > strtotime($toDate)) {
            $tmp = $fromDate;
            $fromDate = $toDate;
            $toDate = $tmp;
        }

        $searchJobNo = Yii::$app->request->get('search_job_no', '');
        $searchVendor = Yii::$app->request->get('search_vendor', '');
        $searchCustomer = Yii::$app->request->get('search_customer', '');
        $searchProduct = Yii::$app->request->get('search_product', '');

        // --- Group Companies Financial Calculation ---
        $expensesQuery = Purch::find()->where(['approve_status' => 1]);
        $nonePrQuery = PurchaseMaster::find()->where(['approve_status' => PurchaseMaster::APPROVE_STATUS_APPROVED]);
        $invoiceQuery = Invoice::find()->where(['status' => Invoice::STATUS_ACTIVE]);
        $vehicleQuery = VehicleExpense::find();
        $wageQuery = DriverWageReport::find();

        if (!empty($companyId) && $companyId != '0') {
            $expensesQuery->andWhere(['company_id' => $companyId]);
            $nonePrQuery->andWhere(['company_id' => $companyId]);
            $invoiceQuery->andWhere(['company_id' => $companyId]);
        }

        if (!empty($fromDate) && !empty($toDate)) {
            $fromTs = strtotime($fromDate . ' 00:00:00');
            $toTs = strtotime($toDate . ' 23:59:59');
            $fromDateTime = $fromDate . ' 00:00:00';
            $toDateTime = $toDate . ' 23:59:59';

            $startMonth = (int)date('m', strtotime($fromDate));
            $startYear = (int)date('Y', strtotime($fromDate));
            $endMonth = (int)date('m', strtotime($toDate));
            $endYear = (int)date('Y', strtotime($toDate));

            $startYm = $startYear * 100 + $startMonth;
            $endYm = $endYear * 100 + $endMonth;

            // Filter PO Expenses by purch_date (or created_at if purch_date is empty)
            $expensesQuery->andWhere([
                'or',
                ['between', 'purch_date', $fromDateTime, $toDateTime],
                ['and', ['purch_date' => null], ['between', 'created_at', $fromTs, $toTs]]
            ]);

            // Filter None PR Expenses by docdat (or created_at if docdat is empty)
            $nonePrQuery->andWhere([
                'or',
                ['between', 'docdat', $fromDate, $toDate],
                ['and', ['docdat' => null], ['between', 'created_at', $fromTs, $toTs]]
            ]);

            // Filter Invoices by invoice_date (or created_at if invoice_date is empty)
            $invoiceQuery->andWhere([
                'or',
                ['between', 'invoice_date', $fromDate, $toDate],
                ['and', ['invoice_date' => null], ['between', 'created_at', $fromDateTime, $toDateTime]]
            ]);

            // Filter Vehicle Expenses strictly by expense_date (วันที่ใช้งานรถจริง)
            $vehicleQuery->andWhere(['between', 'expense_date', $fromDate, $toDate]);

            // Filter Driver Wage Reports by year/month
            $wageQuery->andWhere(['between', new \yii\db\Expression('(report_year * 100 + report_month)'), $startYm, $endYm]);
        }

        $totalPoExpenses = (float)(clone $expensesQuery)->sum('net_amount');
        $totalNonePrExpenses = (float)(clone $nonePrQuery)->sum('total_amount');
        
        $totalKm = abs((float)(clone $vehicleQuery)->sum('total_distance'));
        $vehicleCostByKm = $totalKm * 5;
        $totalVehicleExpenses = abs((float)(clone $vehicleQuery)->sum('vehicle_cost'));
        if ($totalVehicleExpenses == 0 && $vehicleCostByKm > 0) {
            $totalVehicleExpenses = $vehicleCostByKm;
        }

        $totalVehicleWages = abs((float)(clone $vehicleQuery)->sum('total_wage'));
        $totalDriverReportWages = abs((float)(clone $wageQuery)->sum('net_total'));
        $totalWages = $totalDriverReportWages + $totalVehicleWages;
        
        $effectiveVehicleExpense = max($totalVehicleExpenses, $vehicleCostByKm);
        $totalExpenses = $totalPoExpenses + $totalNonePrExpenses + $effectiveVehicleExpense + $totalWages;

        // Total Cash Revenue from Official Receipts (ใบเสร็จรับเงิน - Cash Basis)
        $cashReceiptQuery = Invoice::find()
            ->where(['status' => Invoice::STATUS_ACTIVE])
            ->andWhere(['invoice_type' => Invoice::TYPE_RECEIPT]);

        if (!empty($companyId) && $companyId != '0') {
            $cashReceiptQuery->andWhere(['company_id' => $companyId]);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $cashReceiptQuery->andWhere(['between', 'invoice_date', $fromDate, $toDate]);
        }

        $totalRevenue = (float)(clone $cashReceiptQuery)->sum('total_amount');

        // Total Invoiced Sales (ยอดขายตามใบแจ้งหนี้/ใบวางบิล - Accrual Basis)
        $invoicedSalesQuery = Invoice::find()
            ->where(['status' => Invoice::STATUS_ACTIVE])
            ->andWhere(['!=', 'invoice_type', Invoice::TYPE_RECEIPT]);

        if (!empty($companyId) && $companyId != '0') {
            $invoicedSalesQuery->andWhere(['company_id' => $companyId]);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $invoicedSalesQuery->andWhere(['between', 'invoice_date', $fromDate, $toDate]);
        }

        $totalInvoicedAmount = (float)(clone $invoicedSalesQuery)->sum('total_amount');

        // If Cash Revenue in date range is 0 but Invoiced Sales exist, use Invoiced Sales for Profit calculation
        $effectiveRevenueForProfit = $totalRevenue > 0 ? $totalRevenue : $totalInvoicedAmount;

        // Pending Receivables strictly within date range filter
        $unpaidInvQuery = Invoice::find()
            ->where(['status' => Invoice::STATUS_ACTIVE])
            ->andWhere(['!=', 'invoice_type', Invoice::TYPE_RECEIPT]);

        if (!empty($companyId) && $companyId != '0') {
            $unpaidInvQuery->andWhere(['company_id' => $companyId]);
        }

        if (!empty($fromDate) && !empty($toDate)) {
            $unpaidInvQuery->andWhere(['between', 'invoice_date', $fromDate, $toDate]);
        }

        $pendingReceivables = (float)(clone $unpaidInvQuery)->sum('total_amount');

        // Unbilled Jobs Amount (มูลค่างาน Job ที่ยังไม่ได้ทำ Invoice ในช่วงเวลา)
        $jobPendingQuery = Job::find()->where(['status' => 1]);
        if (!empty($companyId) && $companyId != '0') {
            $jobPendingQuery->andWhere(['company_id' => $companyId]);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $jobPendingQuery->andWhere([
                'or',
                ['between', 'job_date', $fromDate . ' 00:00:00', $toDate . ' 23:59:59'],
                ['and', ['job_date' => null], ['between', 'created_at', strtotime($fromDate . ' 00:00:00'), strtotime($toDate . ' 23:59:59')]]
            ]);
        }
        $totalJobAmountRange = (float)(clone $jobPendingQuery)->sum('job_amount');
        $unbilledJobAmount = max(0, $totalJobAmountRange - $pendingReceivables);
        $totalReceivableExposure = $pendingReceivables + $unbilledJobAmount;

        // Accrual Revenue for Net Operating Profit calculation (ยอดขายตั้งหนี้ตาม Invoice หรือ Job ในช่วงเวลา)
        $effectiveAccrualRevenue = max($totalRevenue, $totalInvoicedAmount);
        if ($effectiveAccrualRevenue == 0) {
            $effectiveAccrualRevenue = $totalJobAmountRange;
        }

        // Net Operating Profit / Loss = Accrual Revenue - Total Expenses
        $netProfitLoss = $effectiveAccrualRevenue - $totalExpenses;

        // --- Accounting PO Cashflow Alert & Comparison ---
        $latestClosing = MonthlyAccountClosing::find()
            ->andFilterWhere(['company_id' => $companyId])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $totalMainBankBalance = $latestClosing ? (float)$latestClosing->main_account_balance : 0;
        $totalPettyCashBalance = $latestClosing ? (float)$latestClosing->petty_cash_balance : (float)PettyCashVoucher::find()
            ->where(['status' => 1])
            ->andFilterWhere(['company_id' => $companyId])
            ->sum('amount');

        if ($totalPettyCashBalance == 0) {
            $totalPettyCashBalance = (float)PettyCashVoucher::find()
                ->where(['status' => 1])
                ->andFilterWhere(['company_id' => $companyId])
                ->sum('amount');
        }

        $currentAvailableCash = $totalMainBankBalance + $totalPettyCashBalance;

        // Pending PO Payables (filtered by selected company if applicable)
        $poPayableQuery = Purch::find()->where(['approve_status' => 1]);
        if (!empty($companyId) && $companyId != '0') {
            $poPayableQuery->andWhere(['company_id' => $companyId]);
        }
        $pendingPoPayables = (float)$poPayableQuery->sum('net_amount');

        $isCashflowWarning = ($currentAvailableCash + $pendingReceivables) < $pendingPoPayables;

        // Monthly Closings History
        $monthlyClosings = MonthlyAccountClosing::find()
            ->orderBy(['id' => SORT_DESC])
            ->limit(12)
            ->all();

        // --- 8.8.4 Advanced Search System ---
        $jobsQuery = Job::find()->with(['company', 'quotation']);

        if (!empty($fromDate) && !empty($toDate)) {
            $jobsQuery->andWhere([
                'or',
                ['between', 'job.job_date', $fromDate . ' 00:00:00', $toDate . ' 23:59:59'],
                ['and', ['job.job_date' => null], ['between', 'job.created_at', strtotime($fromDate . ' 00:00:00'), strtotime($toDate . ' 23:59:59')]]
            ]);
        }
        if (!empty($searchJobNo)) {
            $jobsQuery->andWhere(['like', 'job.job_no', $searchJobNo]);
        }
        if (!empty($searchCustomer)) {
            $jobsQuery->joinWith('quotation q')
                      ->andWhere(['or', ['like', 'q.customer_name', $searchCustomer], ['q.customer_id' => $searchCustomer]]);
        }
        if (!empty($companyId) && $companyId != '0') {
            $jobsQuery->andWhere(['job.company_id' => $companyId]);
        }

        $searchJobsList = $jobsQuery->orderBy(['job.id' => SORT_DESC])->limit(1000)->all();

        $jobIds = ArrayHelper::getColumn($searchJobsList, 'id');
        $jobActivityMap = [];
        if (!empty($jobIds)) {
            $statuses = JobActivityStatus::find()
                ->where(['in', 'job_id', $jobIds])
                ->asArray()
                ->all();
            foreach ($statuses as $st) {
                $jobActivityMap[$st['job_id']][$st['step_no']] = (int)$st['status'];
            }
        }

        // Auto-evaluate Step 13 (Vehicle Usage) in real time for all searched jobs
        $veJobNosMap = [];
        $veRows = VehicleExpense::find()
            ->select(['job_no'])
            ->where(['not', ['job_no' => null]])
            ->andWhere(['!=', 'job_no', ''])
            ->distinct()
            ->asArray()
            ->all();

        foreach ($veRows as $vr) {
            $cleanNo = strtoupper(trim($vr['job_no']));
            if (!empty($cleanNo)) {
                $veJobNosMap[$cleanNo] = true;
            }
        }

        foreach ($searchJobsList as $jobItem) {
            $cleanJn = strtoupper(trim($jobItem->job_no));
            if (!empty($cleanJn) && isset($veJobNosMap[$cleanJn])) {
                $jobActivityMap[$jobItem->id][13] = JobActivityStatus::STATUS_GREEN;
            }
        }

        // --- 8.8.5 Financial Comparison Line Chart Data (Revenue, Expenses, Receivables) ---
        $chartMonths = [];
        if (!empty($fromDate) && !empty($toDate)) {
            $startMonthTs = strtotime(date('Y-m-01', strtotime($fromDate)));
            $endMonthTs = strtotime(date('Y-m-01', strtotime($toDate)));
            
            $curTs = $startMonthTs;
            $mCount = 0;
            while ($curTs <= $endMonthTs) {
                $chartMonths[] = date('Y-m', $curTs);
                $curTs = strtotime('+1 month', $curTs);
                $mCount++;
            }
            if ($mCount < 4) {
                $chartMonths = [];
                $priorStart = strtotime('-3 months', $startMonthTs);
                $curTs = $priorStart;
                while ($curTs <= $endMonthTs) {
                    $chartMonths[] = date('Y-m', $curTs);
                    $curTs = strtotime('+1 month', $curTs);
                }
            }
        } else {
            for ($i = 5; $i >= 0; $i--) {
                $chartMonths[] = date('Y-m', strtotime("-{$i} months"));
            }
        }

        $chartLabels = [];
        $chartRevenueData = [];
        $chartExpensesData = [];
        $chartReceivablesData = [];

        foreach ($chartMonths as $m) {
            $mLabel = date('M Y', strtotime($m . '-01'));
            $mStart = $m . '-01';
            $mEnd = date('Y-m-t', strtotime($mStart));
            $mStartDt = $mStart . ' 00:00:00';
            $mEndDt = $mEnd . ' 23:59:59';

            // Revenue for month (Cash Receipts or Invoiced Revenue for month)
            $invMQuery = Invoice::find()
                ->where(['status' => Invoice::STATUS_ACTIVE])
                ->andWhere(['invoice_type' => Invoice::TYPE_RECEIPT])
                ->andWhere(['between', 'invoice_date', $mStart, $mEnd]);
            if (!empty($companyId) && $companyId != '0') {
                $invMQuery->andWhere(['company_id' => $companyId]);
            }
            $mRev = (float)(clone $invMQuery)->sum('total_amount');
            if ($mRev == 0) {
                $invTaxMQuery = Invoice::find()
                    ->where(['status' => Invoice::STATUS_ACTIVE])
                    ->andWhere(['!=', 'invoice_type', Invoice::TYPE_RECEIPT])
                    ->andWhere(['between', 'invoice_date', $mStart, $mEnd]);
                if (!empty($companyId) && $companyId != '0') {
                    $invTaxMQuery->andWhere(['company_id' => $companyId]);
                }
                $mRev = (float)(clone $invTaxMQuery)->sum('total_amount');
            }

            // PO Expenses for month
            $poMQuery = Purch::find()->where(['approve_status' => 1])
                ->andWhere(['between', 'purch_date', $mStartDt, $mEndDt]);
            if (!empty($companyId) && $companyId != '0') {
                $poMQuery->andWhere(['company_id' => $companyId]);
            }
            $mPo = (float)(clone $poMQuery)->sum('net_amount');

            // None PR Expenses for month
            $nprMQuery = PurchaseMaster::find()->where(['approve_status' => PurchaseMaster::APPROVE_STATUS_APPROVED])
                ->andWhere(['between', 'docdat', $mStart, $mEnd]);
            if (!empty($companyId) && $companyId != '0') {
                $nprMQuery->andWhere(['company_id' => $companyId]);
            }
            $mNpr = (float)(clone $nprMQuery)->sum('total_amount');

            // Vehicle Expenses for month
            $veMQuery = VehicleExpense::find()->andWhere(['between', 'expense_date', $mStart, $mEnd]);
            $mKm = abs((float)(clone $veMQuery)->sum('total_distance'));
            $mVehicleCost = abs((float)(clone $veMQuery)->sum('vehicle_cost'));
            $mVehicleWage = abs((float)(clone $veMQuery)->sum('total_wage'));
            $mEffVehicleCost = max($mVehicleCost, $mKm * 5);

            // Driver Wage Report for month
            $y = (int)date('Y', strtotime($mStart));
            $mon = (int)date('m', strtotime($mStart));
            $wageMQuery = DriverWageReport::find()->where(['report_year' => $y, 'report_month' => $mon]);
            $mDriverReportWage = abs((float)(clone $wageMQuery)->sum('net_total'));
            $mTotalWages = $mDriverReportWage + $mVehicleWage;

            $mTotalExpenses = $mPo + $mNpr + $mEffVehicleCost + $mTotalWages;

            // Receivables for month
            $recMQuery = Invoice::find()
                ->where(['status' => Invoice::STATUS_ACTIVE])
                ->andWhere(['!=', 'invoice_type', Invoice::TYPE_RECEIPT])
                ->andWhere(['between', 'invoice_date', $mStart, $mEnd]);
            if (!empty($companyId) && $companyId != '0') {
                $recMQuery->andWhere(['company_id' => $companyId]);
            }
            $mRec = (float)(clone $recMQuery)->sum('total_amount');

            $chartLabels[] = $mLabel;
            $chartRevenueData[] = round($mRev, 2);
            $chartExpensesData[] = round($mTotalExpenses, 2);
            $chartReceivablesData[] = round($mRec, 2);
        }

        return $this->render('index', [
            'companyId' => $companyId,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totalExpenses' => $totalExpenses,
            'totalRevenue' => $totalRevenue,
            'totalInvoicedAmount' => $totalInvoicedAmount,
            'totalPoExpenses' => $totalPoExpenses,
            'totalNonePrExpenses' => $totalNonePrExpenses,
            'pendingReceivables' => $pendingReceivables,
            'unbilledJobAmount' => $unbilledJobAmount,
            'totalReceivableExposure' => $totalReceivableExposure,
            'totalKm' => $totalKm,
            'vehicleCostByKm' => $vehicleCostByKm,
            'totalVehicleWages' => $totalVehicleWages,
            'totalWages' => $totalWages,
            'netProfitLoss' => $netProfitLoss,
            'currentAvailableCash' => $currentAvailableCash,
            'totalMainBankBalance' => $totalMainBankBalance,
            'totalPettyCashBalance' => $totalPettyCashBalance,
            'pendingPoPayables' => $pendingPoPayables,
            'isCashflowWarning' => $isCashflowWarning,
            'monthlyClosings' => $monthlyClosings,
            'searchJobsList' => $searchJobsList,
            'jobActivityMap' => $jobActivityMap,
            'chartLabels' => $chartLabels,
            'chartRevenueData' => $chartRevenueData,
            'chartExpensesData' => $chartExpensesData,
            'chartReceivablesData' => $chartReceivablesData,
            'searchJobNo' => $searchJobNo,
            'searchVendor' => $searchVendor,
            'searchCustomer' => $searchCustomer,
            'searchProduct' => $searchProduct,
        ]);
    }

    /**
     * Helper to check permission for canceling steps
     */
    private function checkCanCancelStep()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        $user = Yii::$app->user->identity;
        if (Yii::$app->user->can('executive-dashboard/cancel-step') || 
            Yii::$app->user->can('job/delete') || 
            Yii::$app->user->can('job/update') || 
            Yii::$app->user->can('admin') || 
            (isset($user->user_group_id) && in_array($user->user_group_id, [1, 2]))) {
            return true;
        }
        return false;
    }

    /**
     * Automatic evaluation of 15 Activity Steps based on real DB records and documents
     */
    public function evaluateJobStepStatuses($job)
    {
        if (!$job) {
            return ['statuses' => [], 'details' => [], 'metrics' => []];
        }

        $manualStatuses = JobActivityStatus::find()
            ->where(['job_id' => $job->id])
            ->indexBy('step_no')
            ->all();

        $jobRevenue = (float)($job->job_amount ?: ($job->quotation ? $job->quotation->total_amount : 0));
        $jobPos = Purch::find()->where(['job_id' => $job->id])->all();
        $jobPoTotal = 0;
        $jobPoHasDoc = false;
        foreach ($jobPos as $po) {
            $jobPoTotal += (float)$po->net_amount;
            $poDocExists = (new \yii\db\Query())
                ->from('purch_doc')
                ->where(['purch_id' => $po->id])
                ->exists();
            if ($poDocExists) {
                $jobPoHasDoc = true;
            }
        }
        $jobNonePrs = PurchaseMaster::find()->where(['job_no' => $job->job_no])->all();
        $jobNonePrTotal = 0;
        $jobNonePrHasDoc = false;
        foreach ($jobNonePrs as $npr) {
            $jobNonePrTotal += (float)$npr->total_amount;
            if (!empty($npr->invoice_no) || !empty($npr->refnum) || !empty($npr->cus_po_doc)) {
                $jobNonePrHasDoc = true;
            }
        }
        $cleanJobNo = trim($job->job_no);
        $upperJobNo = strtoupper($cleanJobNo);
        $jobVehicleExp = VehicleExpense::find()
            ->where([
                'or',
                ['job_no' => $job->job_no],
                ['job_no' => $cleanJobNo],
                ['job_no' => $upperJobNo],
                ['like', 'job_no', $cleanJobNo]
            ])
            ->orderBy(['expense_date' => SORT_DESC])
            ->all();
        $jobKmTotal = 0;
        $jobVehicleCost = 0;
        $jobVehicleWage = 0;
        foreach ($jobVehicleExp as $ve) {
            $jobKmTotal += (float)$ve->total_distance;
            $jobVehicleCost += (float)$ve->vehicle_cost;
            $jobVehicleWage += (float)$ve->total_wage;
        }
        $jobKmCostAt5 = $jobKmTotal * 5;
        // ถ้าค่าใช้จ่ายรถในระบบเดิมเท่ากับ 0 ให้ใช้ค่าตามระยะทาง x 5 บาท
        $effectiveVehicleCost = max($jobKmCostAt5, $jobVehicleCost);
        $jobTotalExpenses = $jobPoTotal + $jobNonePrTotal + $effectiveVehicleCost + $jobVehicleWage;
        $jobNetProfit = $jobRevenue - $jobTotalExpenses;
        $jobProfitPercent = $jobRevenue > 0 ? ($jobNetProfit / $jobRevenue) * 100 : 0;

        $dueDate = !empty($job->end_date) ? $job->end_date : $job->job_date;
        $daysRemaining = 0;
        if (!empty($dueDate)) {
            $targetTs = strtotime($dueDate);
            $todayTs = strtotime(date('Y-m-d'));
            $daysRemaining = round(($targetTs - $todayTs) / (60 * 60 * 24));
        }

        $stepStatuses = [];
        $stepDetails = [];

        for ($step = 1; $step <= 15; $step++) {
            $existing = $manualStatuses[$step] ?? null;
            if ($existing && $existing->status == JobActivityStatus::STATUS_CANCELLED) {
                $stepStatuses[$step] = JobActivityStatus::STATUS_CANCELLED;
                $stepDetails[$step] = 'ถูกกดยกเลิกขั้นตอนโดยผู้ดูแลระบบ';
                continue;
            }

            switch ($step) {
                case 1:
                    if (!empty($job->cus_po_doc)) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'แนบเอกสาร PO ลูกค้าแล้ว (' . $job->cus_po_doc . ')';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_ORANGE;
                        $stepDetails[$step] = 'เปิด Job No. แล้ว (รอแนบไฟล์ PO ลูกค้า)';
                    }
                    break;

                case 2:
                    if (!empty($jobPos) || !empty($jobNonePrs)) {
                        if ($jobPoHasDoc || $jobNonePrHasDoc) {
                            $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                            $stepDetails[$step] = 'เปิดรายการ PO/None PR และแนบไฟล์ครบถ้วน';
                        } else {
                            $stepStatuses[$step] = JobActivityStatus::STATUS_ORANGE;
                            $stepDetails[$step] = 'เปิดรายการ PO/None PR แล้ว (รอจัดเก็บแนบไฟล์)';
                        }
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'ยังไม่มีการเปิด PO หรือ None PR';
                    }
                    break;

                case 3:
                    $hasRec = $job->hasReceiveTransaction($job->id);
                    if ($hasRec == 100) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'รับของจาก Vendor และจัดเก็บแนบไฟล์ครบถ้วน';
                    } elseif ($hasRec > 0) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_ORANGE;
                        $stepDetails[$step] = 'มีรายการรับของแล้ว (รอจัดเก็บแนบไฟล์)';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'ยังไม่มีรายการรับของจาก Vendor';
                    }
                    break;

                case 4:
                    $hasWithd = $job->hasWithdrawTransaction($job->id);
                    if ($hasWithd == 100) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'เบิก/คืนของ และแนบเอกสารเรียบร้อย';
                    } elseif ($hasWithd > 0) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_ORANGE;
                        $stepDetails[$step] = 'มีรายการเบิก/คืนของ (รอจัดเก็บแนบไฟล์)';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'ยังไม่มีรายการเบิก/คืนของ';
                    }
                    break;

                case 5:
                    if (!empty($job->jsa_doc)) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'แนบเอกสาร JSA/เซฟตี้เรียบร้อยแล้ว';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'ยังไม่ได้แนบเอกสาร JSA/เซฟตี้';
                    }
                    break;

                case 6:
                    if ($job->status >= 2) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'สรุปงาน Engineering เรียบร้อย';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_ORANGE;
                        $stepDetails[$step] = 'อยู่ระหว่างดำเนินการ Engineering';
                    }
                    break;

                case 7:
                    if (!empty($job->report_doc)) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'แนบเอกสาร Final Report เรียบร้อย';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'ยังไม่ได้แนบเอกสาร Final Report';
                    }
                    break;

                case 8:
                    if ($job->status >= 3) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'ประเมินผลจากลูกค้าเรียบร้อย';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'รอแบบประเมินผลจากลูกค้า';
                    }
                    break;

                case 9:
                    $hasInv = $job->hasDebtNotification($job->id);
                    if ($hasInv == 100) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'ออก Invoice และแนบไฟล์เรียบร้อย';
                    } elseif ($hasInv > 0) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_ORANGE;
                        $stepDetails[$step] = 'ออก Invoice แล้ว (รอจัดเก็บแนบไฟล์)';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'ยังไม่ได้ออก Invoice';
                    }
                    break;

                case 10:
                    if ($jobProfitPercent >= 20) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'อัตรากำไร ≥20% (' . number_format($jobProfitPercent, 1) . '%)';
                    } elseif ($jobProfitPercent > 0) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_ORANGE;
                        $stepDetails[$step] = 'อัตรากำไร <20% (' . number_format($jobProfitPercent, 1) . '%)';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'ขาดทุน (' . number_format($jobProfitPercent, 1) . '%)';
                    }
                    break;

                case 11:
                    if ($daysRemaining >= 0) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'เหลือเวลาส่งมอบงาน ' . $daysRemaining . ' วัน';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'เกินกำหนดส่งมอบงาน ' . abs($daysRemaining) . ' วัน';
                    }
                    break;

                case 12:
                    $hasRecp = $job->hasReceipt($job->id);
                    if ($hasRecp == 100) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'รับชำระเงินและออกใบเสร็จเรียบร้อย';
                    } elseif ($hasRecp > 0) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_ORANGE;
                        $stepDetails[$step] = 'ออกใบเสร็จแล้ว (รอแนบไฟล์หลักฐานโอนเงิน)';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'ยังไม่ได้รับชำระเงิน/ออกใบเสร็จ';
                    }
                    break;

                case 13:
                    if (!empty($jobVehicleExp)) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $wageText = $jobVehicleWage > 0 ? ', ค่าจ้าง: ' . number_format($jobVehicleWage, 2) . ' บาท' : '';
                        $stepDetails[$step] = 'บันทึกการใช้รถยนต์แล้ว ' . number_format($jobKmTotal, 1) . ' กม. (ค่ารถ x5: ' . number_format($jobKmCostAt5, 2) . ' บาท' . $wageText . ')';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'ยังไม่มีบันทึกระยะทางใช้รถยนต์';
                    }
                    break;

                case 14:
                    if (!empty($job->jobLines)) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'บันทึกบุคลากรปฏิบัติงาน ' . count($job->jobLines) . ' รายการ';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_ORANGE;
                        $stepDetails[$step] = 'อยู่ระหว่างจัดสรรบุคลากรปฏิบัติงาน';
                    }
                    break;

                case 15:
                    if ($jobNetProfit > 0) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $stepDetails[$step] = 'สรุปผลกำไรสุทธิ ' . number_format($jobNetProfit, 2) . ' บาท';
                    } elseif ($jobNetProfit == 0) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_ORANGE;
                        $stepDetails[$step] = 'สรุปผลเท่าทุน 0.00 บาท';
                    } else {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_RED;
                        $stepDetails[$step] = 'สรุปผลขาดทุนสุทธิ ' . number_format($jobNetProfit, 2) . ' บาท';
                    }
                    break;
            }

            // Sync step status to JobActivityStatus table
            if (!$existing || $existing->status != $stepStatuses[$step]) {
                if (!$existing) {
                    $existing = new JobActivityStatus();
                    $existing->job_id = $job->id;
                    $existing->step_no = $step;
                    $existing->created_at = time();
                }
                $existing->status = $stepStatuses[$step];
                $existing->save(false);
            }
        }

        return [
            'statuses' => $stepStatuses,
            'details' => $stepDetails,
            'metrics' => [
                'jobRevenue' => $jobRevenue,
                'jobPoTotal' => $jobPoTotal,
                'jobNonePrTotal' => $jobNonePrTotal,
                'jobKmTotal' => $jobKmTotal,
                'jobVehicleCost' => $jobVehicleCost,
                'jobVehicleWage' => $jobVehicleWage,
                'jobKmCostAt5' => $jobKmCostAt5,
                'jobTotalExpenses' => $jobTotalExpenses,
                'jobNetProfit' => $jobNetProfit,
                'jobProfitPercent' => $jobProfitPercent,
                'daysRemaining' => $daysRemaining,
                'jobVehicleExpList' => $jobVehicleExp,
            ]
        ];
    }

    /**
     * Job Activity Pipeline Detail Page
     */
    public function actionJobPipeline($id)
    {
        $job = Job::findOne($id);
        if (!$job) {
            throw new NotFoundHttpException('ไม่พบข้อมูล Job ที่ต้องการ');
        }

        $eval = $this->evaluateJobStepStatuses($job);

        $activityStatuses = JobActivityStatus::find()
            ->where(['job_id' => $job->id])
            ->indexBy('step_no')
            ->all();

        $canCancel = $this->checkCanCancelStep();

        return $this->render('job_pipeline', [
            'job' => $job,
            'activityStatuses' => $activityStatuses,
            'stepDetails' => $eval['details'],
            'jobRevenue' => $eval['metrics']['jobRevenue'],
            'jobPoTotal' => $eval['metrics']['jobPoTotal'],
            'jobNonePrTotal' => $eval['metrics']['jobNonePrTotal'],
            'jobKmTotal' => $eval['metrics']['jobKmTotal'],
            'jobVehicleCost' => $eval['metrics']['jobVehicleCost'],
            'jobVehicleWage' => $eval['metrics']['jobVehicleWage'],
            'jobKmCostAt5' => $eval['metrics']['jobKmCostAt5'],
            'jobTotalExpenses' => $eval['metrics']['jobTotalExpenses'],
            'jobNetProfit' => $eval['metrics']['jobNetProfit'],
            'jobProfitPercent' => $eval['metrics']['jobProfitPercent'],
            'daysRemaining' => $eval['metrics']['daysRemaining'],
            'jobVehicleExpList' => $eval['metrics']['jobVehicleExpList'],
            'canCancel' => $canCancel,
        ]);
    }

    /**
     * AJAX Action: Cancel Activity Step
     */
    public function actionCancelStep()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->checkCanCancelStep()) {
            return ['success' => false, 'message' => 'คุณไม่มีสิทธิ์ในการยกเลิกขั้นตอนนี้'];
        }

        $jobId = Yii::$app->request->post('job_id');
        $stepNo = Yii::$app->request->post('step_no');

        $statusModel = JobActivityStatus::findOne(['job_id' => $jobId, 'step_no' => $stepNo]);
        if (!$statusModel) {
            $statusModel = new JobActivityStatus();
            $statusModel->job_id = $jobId;
            $statusModel->step_no = $stepNo;
        }

        $statusModel->status = JobActivityStatus::STATUS_CANCELLED;
        $statusModel->cancelled_by = $user->id;
        $statusModel->cancelled_at = time();
        $statusModel->updated_at = time();

        if ($statusModel->save(false)) {
            return ['success' => true, 'message' => 'ยกเลิกขั้นตอนเรียบร้อยแล้ว', 'status_html' => JobActivityStatus::getStatusLabel(JobActivityStatus::STATUS_CANCELLED)];
        }

        return ['success' => false, 'message' => 'ไม่สามารถบันทึกได้'];
    }

    /**
     * AJAX Action: Update Activity Step Status (Red / Orange / Green)
     */
    public function actionUpdateStepStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $jobId = Yii::$app->request->post('job_id');
        $stepNo = Yii::$app->request->post('step_no');
        $status = Yii::$app->request->post('status');

        $statusModel = JobActivityStatus::findOne(['job_id' => $jobId, 'step_no' => $stepNo]);
        if (!$statusModel) {
            $statusModel = new JobActivityStatus();
            $statusModel->job_id = $jobId;
            $statusModel->step_no = $stepNo;
        }

        $statusModel->status = (int)$status;
        $statusModel->updated_at = time();
        if ($statusModel->save(false)) {
            return ['success' => true, 'message' => 'อัปเดตสถานะเรียบร้อยแล้ว', 'status_html' => JobActivityStatus::getStatusLabel($statusModel->status)];
        }

        return ['success' => false, 'message' => 'ไม่สามารถบันทึกได้'];
    }

    /**
     * Save Monthly Account Closing
     */
    public function actionMonthlyClosing()
    {
        $companyId = Yii::$app->request->post('company_id');
        $yearMonth = Yii::$app->request->post('year_month', date('Y-m'));
        $pettyBalance = (float)Yii::$app->request->post('petty_cash_balance');
        $mainBalance = (float)Yii::$app->request->post('main_account_balance');
        $remarks = Yii::$app->request->post('remarks');

        $model = MonthlyAccountClosing::findOne(['company_id' => $companyId, 'year_month' => $yearMonth]);
        if (!$model) {
            $model = new MonthlyAccountClosing();
            $model->company_id = $companyId;
            $model->year_month = $yearMonth;
        }

        $model->petty_cash_balance = $pettyBalance;
        $model->main_account_balance = $mainBalance;
        $model->remarks = $remarks;
        $model->closed_by = Yii::$app->user->id;
        $model->closed_at = time();

        $file = UploadedFile::getInstanceByName('statement_file');
        if ($file) {
            $uploadPath = Yii::getAlias('@backend/web/uploads/statements/');
            if (!file_exists($uploadPath)) {
                FileHelper::createDirectory($uploadPath, 0777);
            }
            $fileName = 'statement_' . $yearMonth . '_' . time() . '.' . $file->extension;
            if ($file->saveAs($uploadPath . $fileName)) {
                $model->statement_file = $fileName;
            }
        }

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'บันทึกปิดยอดบัญชีประจำเดือนเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกปิดยอดบัญชีได้');
        }

        return $this->redirect(['index']);
    }

    /**
     * Delete Monthly Account Closing and Restore Pre-Closing Balance Values
     */
    public function actionDeleteMonthlyClosing($id)
    {
        $model = MonthlyAccountClosing::findOne($id);
        if ($model) {
            $yearMonth = $model->year_month;
            if (!empty($model->statement_file)) {
                $filePath = Yii::getAlias('@backend/web/uploads/statements/') . $model->statement_file;
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'ลบประวัติปิดยอดเดือน ' . $yearMonth . ' และคืนค่ากลับเป็นค่าตั้งต้นเรียบร้อยแล้ว');
            } else {
                Yii::$app->session->setFlash('error', 'ไม่สามารถลบประวัติปิดยอดได้');
            }
        }
        return $this->redirect(['index']);
    }
}
