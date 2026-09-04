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
use backend\models\PurchLine;
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
use backend\models\User;

class ExecutiveDashboardController extends BaseController
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }

        if (!\backend\models\User::isUserAdmin()) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าถึงหน้า Executive Dashboard (สิทธิ์เฉพาะผู้ดูแลระบบ / System Administrator เท่านั้น)');
        }

        return true;
    }

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
    /**
     * Helper function to calculate revenue based on selected revenue recognition mode
     */
    private function getCalculatedRevenue($revenueMode, $companyId = null, $fromDate = null, $toDate = null)
    {
        $revenue = 0;
        if ($revenueMode === 'ar') {
            // Mode 'ar': Combined AR (ยอดค้างรับวางบิล AR + ยอดค้างรับยังไม่วาง AR)
            $unbilledQuery = Invoice::find()
                ->where(['invoices.status' => Invoice::STATUS_ACTIVE, 'invoices.is_billed' => 0])
                ->andWhere(['invoices.invoice_type' => [Invoice::TYPE_TAX_INVOICE, Invoice::TYPE_QUOTATION]]);
                
            if (!empty($companyId) && $companyId != '0') {
                if ($companyId == 1) {
                    $unbilledQuery->andWhere(['or', ['invoices.company_id' => 1], ['invoices.company_id' => null], ['invoices.company_id' => 0]]);
                } else {
                    $unbilledQuery->andWhere(['invoices.company_id' => $companyId]);
                }
            }
            if (!empty($toDate)) {
                $unbilledQuery->andWhere([
                    'or',
                    ['<=', 'invoices.invoice_date', $toDate],
                    ['and', ['invoices.invoice_date' => null], ['<=', 'invoices.created_at', $toDate . ' 23:59:59']]
                ]);
            }
            $unbilledAmt = (float)$unbilledQuery->sum('total_amount');

            $receiptQuery = Invoice::find()
                ->where(['invoices.status' => Invoice::STATUS_ACTIVE])
                ->andWhere(['invoices.invoice_type' => [Invoice::TYPE_RECEIPT, '4', 4]]);
                
            if (!empty($companyId) && $companyId != '0') {
                if ($companyId == 1) {
                    $receiptQuery->andWhere(['or', ['invoices.company_id' => 1], ['invoices.company_id' => null], ['invoices.company_id' => 0]]);
                } else {
                    $receiptQuery->andWhere(['invoices.company_id' => $companyId]);
                }
            }
            if (!empty($toDate)) {
                $receiptQuery->andWhere([
                    'or',
                    ['<=', 'invoices.invoice_date', $toDate],
                    ['and', ['invoices.invoice_date' => null], ['<=', 'invoices.created_at', $toDate . ' 23:59:59']]
                ]);
            }

            $pendingRec = 0;
            foreach ($receiptQuery->all() as $receipt) {
                $receiptNoVat = $receipt->subtotal - $receipt->discount_amount;
                $receiptTotal = $receipt->total_amount;
                
                $totalPaid = \backend\models\InvoicePaymentReceipt::find()
                    ->where(['invoice_id' => $receipt->id])
                    ->sum('amount') ?: 0;
                
                $receipt_ids = \backend\models\InvoicePaymentReceipt::find()
                    ->select('id')
                    ->where(['invoice_id' => $receipt->id])
                    ->column();

                if (!empty($receipt_ids)) {
                    $total_extras = \backend\models\InvoicePaymentExtra::find()
                        ->where(['payment_receipt_id' => $receipt_ids])
                        ->sum('amount') ?: 0;
                    $totalPaid += $total_extras;
                }

                $ratio = ($receiptTotal > 0) ? ($receiptNoVat / $receiptTotal) : 1;
                $paidNoVat = $totalPaid * $ratio;
                
                $pendingRec += max(0, $receiptNoVat - $paidNoVat);
            }

            $revenue = $pendingRec + $unbilledAmt;
        } elseif ($revenueMode === 'invoice') {
            // Accrual / Invoiced Revenue Basis (ใบแจ้งหนี้ / ใบวางบิล / ใบกำกับภาษี)
            $query = Invoice::find()->where([
                'status' => Invoice::STATUS_ACTIVE,
                'invoice_type' => [Invoice::TYPE_BILL_PLACEMENT, Invoice::TYPE_TAX_INVOICE]
            ]);
            if (!empty($companyId) && $companyId != '0') {
                if ($companyId == 1) {
                    $query->andWhere(['or', ['company_id' => 1], ['company_id' => null], ['company_id' => 0]]);
                } else {
                    $query->andWhere(['company_id' => $companyId]);
                }
            }
            if (!empty($fromDate) && !empty($toDate)) {
                $fromDt = $fromDate . ' 00:00:00';
                $toDt = $toDate . ' 23:59:59';
                $query->andWhere([
                    'or',
                    ['between', 'invoice_date', $fromDate, $toDate],
                    ['and', ['invoice_date' => null], ['between', 'created_at', $fromDt, $toDt]]
                ]);
            }
            $revenue = (float)$query->sum('subtotal - COALESCE(discount_amount, 0)');
        } elseif ($revenueMode === 'receipt') {
            // Cash Basis / Cash Received (ใบเสร็จรับเงิน & ยอดรับชำระเงินจริง)
            $query = Invoice::find()->where([
                'status' => Invoice::STATUS_ACTIVE,
                'invoice_type' => [Invoice::TYPE_RECEIPT, '4']
            ]);
            if (!empty($companyId) && $companyId != '0') {
                if ($companyId == 1) {
                    $query->andWhere(['or', ['company_id' => 1], ['company_id' => null], ['company_id' => 0]]);
                } else {
                    $query->andWhere(['company_id' => $companyId]);
                }
            }
            if (!empty($fromDate) && !empty($toDate)) {
                $fromDt = $fromDate . ' 00:00:00';
                $toDt = $toDate . ' 23:59:59';
                $query->andWhere([
                    'or',
                    ['between', 'invoice_date', $fromDate, $toDate],
                    ['and', ['invoice_date' => null], ['between', 'created_at', $fromDt, $toDt]]
                ]);
            }
            $revInvoices = (float)$query->sum('subtotal - COALESCE(discount_amount, 0)');

            $payQuery = \backend\models\InvoicePaymentReceipt::find()
                ->innerJoin('invoices', 'invoices.id = invoice_payment_receipt.invoice_id')
                ->where(['invoices.status' => Invoice::STATUS_ACTIVE]);
            if (!empty($companyId) && $companyId != '0') {
                if ($companyId == 1) {
                    $payQuery->andWhere(['or', ['invoice_payment_receipt.company_id' => 1], ['invoice_payment_receipt.company_id' => null], ['invoice_payment_receipt.company_id' => 0]]);
                } else {
                    $payQuery->andWhere(['invoice_payment_receipt.company_id' => $companyId]);
                }
            }
            if (!empty($fromDate) && !empty($toDate)) {
                $payQuery->andWhere(['between', 'invoice_payment_receipt.payment_date', $fromDate, $toDate]);
            }
            $revPayments = (float)$payQuery->sum('invoice_payment_receipt.amount') / 1.07;

            $revenue = max($revInvoices, $revPayments);
            if ($revenue == 0 && ($revInvoices > 0 || $revPayments > 0)) {
                $revenue = $revInvoices + $revPayments;
            }
        } else {
            // Mode 'job': Job Amount (นำมูลค่างานของใบ Job ที่ Active (Open/Closed) มารวมทั้งหมด)
            $query = Job::find()
                ->where(['job.status' => [Job::JOB_STATUS_OPEN, Job::JOB_STATUS_CLOSED]]);
            if (!empty($companyId) && $companyId != '0') {
                if ($companyId == 1) {
                    $query->andWhere(['or', ['job.company_id' => 1], ['job.company_id' => null], ['job.company_id' => 0]]);
                } else {
                    $query->andWhere(['job.company_id' => $companyId]);
                }
            }
            if (!empty($fromDate) && !empty($toDate)) {
                $fromTs = strtotime($fromDate . ' 00:00:00');
                $toTs = strtotime($toDate . ' 23:59:59');
                $query->andWhere([
                    'or',
                    ['between', 'job.job_date', $fromDate . ' 00:00:00', $toDate . ' 23:59:59'],
                    ['and', ['job.job_date' => null], ['between', 'job.created_at', $fromTs, $toTs]]
                ]);
            }
            foreach ($query->all() as $j) {
                $revenue += (float)($j->job_amount ?: ($j->quotation ? $j->quotation->total_amount : 0));
            }
        }
        return $revenue;
    }

    public function actionIndex()
    {
        $companyId = Yii::$app->request->get('company_id', '');
        $rawFromDate = Yii::$app->request->get('from_date', '');
        $rawToDate = Yii::$app->request->get('to_date', '');
        $revenueMode = Yii::$app->request->get('revenue_mode', 'job');
        if (!in_array($revenueMode, ['ar', 'job', 'invoice', 'receipt'])) {
            $revenueMode = 'job';
        }
        
        $fromDate = !empty($rawFromDate) ? $this->normalizeDate($rawFromDate) : date('Y-01-01');
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
        $expensesQuery = Purch::find()->where(['purch.approve_status' => 1]);
        $nonePrQuery = PurchaseMaster::find()->where(['purchase_master.approve_status' => PurchaseMaster::APPROVE_STATUS_APPROVED]);
        $invoiceQuery = Invoice::find()->where(['invoices.status' => Invoice::STATUS_ACTIVE]);
        $vehicleQuery = VehicleExpense::find();
        $wageQuery = DriverWageReport::find();

        if (!empty($companyId) && $companyId != '0') {
            $expensesQuery->andWhere(['purch.company_id' => $companyId]);
            $nonePrQuery->andWhere(['purchase_master.company_id' => $companyId]);
            $invoiceQuery->andWhere(['invoices.company_id' => $companyId]);
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

            // Invoices can stay by date for other cashflow views
            $invoiceQuery->andWhere([
                'or',
                ['between', 'invoices.invoice_date', $fromDate, $toDate],
                ['and', ['invoices.invoice_date' => null], ['between', 'invoices.created_at', $fromDateTime, $toDateTime]]
            ]);

            // Filter Vehicle Expenses strictly by expense_date (วันที่ใช้งานรถจริง)
            $vehicleQuery->andWhere(['between', 'expense_date', $fromDate, $toDate]);

            // Filter Driver Wage Reports by year/month
            $wageQuery->andWhere(['between', new \yii\db\Expression('(report_year * 100 + report_month)'), $startYm, $endYm]);
        }

        // --- 2. รายรับรวม = ใบเสนอราคาที่เอาไปเปิดเป็น PO แล้ว (Job status Open/Closed, NO VAT) ---
        $jobsWithPoQuery = Job::find()->where(['job.status' => [1, 2]]);
            
        if (!empty($companyId) && $companyId != '0') {
            if ($companyId == 1) {
                $jobsWithPoQuery->andWhere(['or', ['job.company_id' => 1], ['job.company_id' => null], ['job.company_id' => 0]]);
            } else {
                $jobsWithPoQuery->andWhere(['job.company_id' => $companyId]);
            }
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $fromTs = strtotime($fromDate . ' 00:00:00');
            $toTs = strtotime($toDate . ' 23:59:59');
            $jobsWithPoQuery->andWhere([
                'or',
                ['between', 'job.job_date', $fromDate . ' 00:00:00', $toDate . ' 23:59:59'],
                ['and', ['job.job_date' => null], ['between', 'job.created_at', $fromTs, $toTs]]
            ]);
        }
        
        $totalRevenue = $this->getCalculatedRevenue($revenueMode, $companyId, $fromDate, $toDate);
        $jobIds = [];
        $jobNos = [];
        
        $totalPoExpenses = 0;
        $totalNonePrExpenses = 0;
        $totalPettyCashExpenses = 0;
        $totalInventoryExpenses = 0;
        $totalKm = 0;
        $totalVehicleExpenses = 0;
        $totalVehicleWages = 0;

        foreach ((clone $jobsWithPoQuery)->all() as $j) {
            $eval = $this->evaluateJobStepStatuses($j);
            $jobIds[] = $j->id;
            if (!empty(trim($j->job_no))) {
                $jobNos[] = trim($j->job_no);
            }

            $totalPoExpenses += (float)($eval['metrics']['jobPoCostWithInterest'] ?? 0);
            $totalNonePrExpenses += (float)($eval['metrics']['jobNonePrCostWithInterest'] ?? 0);
            $totalPettyCashExpenses += (float)($eval['metrics']['jobPettyCashTotal'] ?? 0);
            $totalInventoryExpenses += (float)($eval['metrics']['jobInventoryCostWithInterest'] ?? 0);
            $totalKm += (float)($eval['metrics']['jobKmTotal'] ?? 0);
            $totalVehicleExpenses += (float)($eval['metrics']['effectiveVehicleCost'] ?? 0);
            $totalVehicleWages += (float)($eval['metrics']['jobVehicleWage'] ?? 0);
        }

        $vehicleCostByKm = $totalKm * 5;
        $totalWages = $totalVehicleWages; // ในแบบ Job Costing ใช้เฉพาะค่าจ้างที่ผูกกับใบงาน
        $effectiveVehicleExpense = $totalVehicleExpenses;

        $totalSalaryExpenses = 0;
        if (!empty($fromDate) && !empty($toDate)) {
            $startM = (int)date('m', strtotime($fromDate));
            $startY = (int)date('Y', strtotime($fromDate));
            $endM = (int)date('m', strtotime($toDate));
            $endY = (int)date('Y', strtotime($toDate));
            
            $salaryQ = \backend\models\CompanySalary::find()
                ->andWhere(['between', new \yii\db\Expression('(salary_year * 100 + salary_month)'), $startY * 100 + $startM, $endY * 100 + $endM]);
            if (!empty($companyId) && $companyId != '0') {
                $salaryQ->andWhere(['company_id' => $companyId]);
            }
            $totalSalaryExpenses = (float)$salaryQ->sum('amount');
        }
        
        // ค่าใช้จ่ายรวมภาพรวมของ Job (PO + None PR + Petty Cash + Stock + ค่ารถ + ค่าจ้าง)
        $totalExpenses = $totalPoExpenses + $totalNonePrExpenses + $totalPettyCashExpenses + $totalInventoryExpenses + $effectiveVehicleExpense + $totalVehicleWages;

        // --- 2. รายรับรวม = ใบเสนอราคาที่เอาไปเปิดเป็น PO แล้ว (Job status Open/Closed, NO VAT) ---
        $jobsWithPoQuery = Job::find()->where(['job.status' => [1, 2]]);
            


        // Compatibility for other view vars
        $totalInvoicedAmount = 0;
        
        $unbilledQuery = Invoice::find()
            ->where(['invoices.status' => Invoice::STATUS_ACTIVE, 'invoices.is_billed' => 0])
            ->andWhere(['invoices.invoice_type' => [Invoice::TYPE_TAX_INVOICE, Invoice::TYPE_QUOTATION]]);
            
        if (!empty($companyId) && $companyId != '0') {
            $unbilledQuery->andWhere(['invoices.company_id' => $companyId]);
        }
        if (!empty($toDate)) {
            $unbilledQuery->andWhere([
                'or',
                ['<=', 'invoices.invoice_date', $toDate],
                ['and', ['invoices.invoice_date' => null], ['<=', 'invoices.created_at', $toDate . ' 23:59:59']]
            ]);
        }
        
        $unbilledJobAmount = (float)$unbilledQuery->sum('total_amount');
        $totalReceivableExposure = 0;

        // --- 3. ยอดค้างรับ (ที่ออกใบเสร็จแต่ยังไม่ได้บันทึกรับเงิน) & 4. ยอดเงินที่ได้รับ ---
        $receiptQuery = Invoice::find()
            ->where(['invoices.status' => Invoice::STATUS_ACTIVE])
            ->andWhere(['invoices.invoice_type' => [Invoice::TYPE_RECEIPT, '4', 4]]);
            
        if (!empty($companyId) && $companyId != '0') {
            $receiptQuery->andWhere(['invoices.company_id' => $companyId]);
        }
        if (!empty($toDate)) {
            // ดึง Invoice ทั้งหมดที่สร้างขึ้นก่อนหรือภายในวันที่เลือก (ไม่สนใจ fromDate) เพื่อหายอดค้างรับสะสม
            $receiptQuery->andWhere([
                'or',
                ['<=', 'invoices.invoice_date', $toDate],
                ['and', ['invoices.invoice_date' => null], ['<=', 'invoices.created_at', $toDate . ' 23:59:59']]
            ]);
        }

        $pendingReceivables = 0;

        foreach ((clone $receiptQuery)->all() as $receipt) {
            $receiptNoVat = $receipt->subtotal - $receipt->discount_amount;
            $receiptTotal = $receipt->total_amount;
            
            $totalPaid = \backend\models\InvoicePaymentReceipt::find()
                ->where(['invoice_id' => $receipt->id])
                ->sum('amount') ?: 0;
            
            $receipt_ids = \backend\models\InvoicePaymentReceipt::find()
                ->select('id')
                ->where(['invoice_id' => $receipt->id])
                ->column();

            if (!empty($receipt_ids)) {
                $total_extras = \backend\models\InvoicePaymentExtra::find()
                    ->where(['payment_receipt_id' => $receipt_ids])
                    ->sum('amount') ?: 0;
                $totalPaid += $total_extras;
            }

            $ratio = ($receiptTotal > 0) ? ($receiptNoVat / $receiptTotal) : 1;
            $paidNoVat = $totalPaid * $ratio;
            
            $pendingReceivables += max(0, $receiptNoVat - $paidNoVat);
        }

        // --- 4. ยอดเงินที่ได้รับ (คำนวณจากวันที่รับชำระเงิน payment_date) ---
        $totalReceivedAmount = 0;
        $paymentQuery = \backend\models\InvoicePaymentReceipt::find()
            ->innerJoin('invoices', 'invoices.id = invoice_payment_receipt.invoice_id')
            ->where(['invoices.status' => Invoice::STATUS_ACTIVE])
            ->andWhere(['invoices.invoice_type' => [\backend\models\Invoice::TYPE_RECEIPT, '4', 4]]);
            
        if (!empty($companyId) && $companyId != '0') {
            $paymentQuery->andWhere(['invoices.company_id' => $companyId]);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $paymentQuery->andWhere(['between', 'invoice_payment_receipt.payment_date', $fromDate, $toDate]);
        }

        foreach ($paymentQuery->all() as $payment) {
            $receipt = $payment->invoice;
            $receiptNoVat = $receipt->subtotal - $receipt->discount_amount;
            $receiptTotal = $receipt->total_amount;
            $ratio = ($receiptTotal > 0) ? ($receiptNoVat / $receiptTotal) : 1;
            
            $amt = $payment->amount;
            $extras = \backend\models\InvoicePaymentExtra::find()
                ->where(['payment_receipt_id' => $payment->id])
                ->sum('amount') ?: 0;
            $amt += $extras;
            
            $totalReceivedAmount += $amt * $ratio;
        }

        // --- 5. สรุปผลกำไร / ขาดทุนสุทธิภาพรวม ---
        $netProfitLoss = $totalRevenue - $totalExpenses;
        
        // --- 6. Past Jobs Expenses & Revenue (สำหรับแสดงใน Modal) ---
        $pastJobsExpenses = 0;
        $pastJobsRevenue = 0;
        $pastJobExpenseList = [];
        $pastJobRevenueList = [];

        if (!empty($fromDate) && !empty($toDate)) {
            $fromTs = strtotime($fromDate . ' 00:00:00');
            
            // 1. Past Job Expenses (PO, Non-PR, Petty Cash in current period but Job < $fromDate)
            // 1.1 PO (Purch)
            $pastPoItems = \backend\models\Purch::find()
                ->where(['purch.approve_status' => 1])
                ->andWhere(['between', 'purch.purch_date', $fromDate, $toDate])
                ->innerJoin('job j', 'j.id = purch.job_id')
                ->andWhere([
                    'or',
                    ['<', 'j.job_date', date('Y-m-d 00:00:00', $fromTs)],
                    ['and', ['j.job_date' => null], ['<', 'j.created_at', $fromTs]]
                ])
                ->select(['purch.purch_no as doc_no', 'purch.purch_date as doc_date', '((purch.net_amount - COALESCE(purch.vat_amount, 0)) * COALESCE(NULLIF(purch.currency_rate, 0), 1)) as amount', 'j.job_no', 'j.id as job_id', 'purch.id as doc_id']);
                
            if (!empty($companyId) && $companyId != '0') {
                $pastPoItems->andWhere(['j.company_id' => $companyId]);
            }
                
            foreach($pastPoItems->asArray()->all() as $po) {
                $pastJobsExpenses += (float)$po['amount'];
                $pastJobExpenseList[] = [
                    'type' => 'PO',
                    'doc_no' => $po['doc_no'],
                    'doc_date' => $po['doc_date'],
                    'amount' => (float)$po['amount'],
                    'job_no' => $po['job_no'],
                    'job_id' => $po['job_id'],
                    'detail_url' => \yii\helpers\Url::to(['purch/view', 'id' => $po['doc_id']])
                ];
            }
            
            // 1.2 Non-PR (PurchaseMaster)
            $pastNonPrItems = \backend\models\PurchaseMaster::find()
                ->where(['purchase_master.approve_status' => 1])
                ->andWhere(['between', 'purchase_master.docdat', $fromDate, $toDate])
                ->innerJoin('job j', 'j.job_no = purchase_master.job_no')
                ->andWhere([
                    'or',
                    ['<', 'j.job_date', date('Y-m-d 00:00:00', $fromTs)],
                    ['and', ['j.job_date' => null], ['<', 'j.created_at', $fromTs]]
                ])
                ->select(['purchase_master.docnum as doc_no', 'purchase_master.docdat as doc_date', '(purchase_master.total_amount - COALESCE(purchase_master.vat_amount, 0)) as amount', 'j.job_no', 'j.id as job_id', 'purchase_master.id as doc_id']);
                
            if (!empty($companyId) && $companyId != '0') {
                $pastNonPrItems->andWhere(['j.company_id' => $companyId]);
            }
                
            foreach($pastNonPrItems->asArray()->all() as $npr) {
                $pastJobsExpenses += (float)$npr['amount'];
                $pastJobExpenseList[] = [
                    'type' => 'Non-PR',
                    'doc_no' => $npr['doc_no'],
                    'doc_date' => $npr['doc_date'],
                    'amount' => (float)$npr['amount'],
                    'job_no' => $npr['job_no'],
                    'job_id' => $npr['job_id'],
                    'detail_url' => \yii\helpers\Url::to(['purchase-master/view', 'id' => $npr['doc_id']])
                ];
            }
            
            // 1.3 Petty Cash
            $pastPettyItems = \backend\models\PettyCashVoucher::find()
                ->where(['petty_cash_voucher.status' => 1])
                ->andWhere(['between', 'petty_cash_voucher.date', $fromDate, $toDate])
                ->innerJoin('job j', 'j.id = petty_cash_voucher.job_id')
                ->andWhere([
                    'or',
                    ['<', 'j.job_date', date('Y-m-d 00:00:00', $fromTs)],
                    ['and', ['j.job_date' => null], ['<', 'j.created_at', $fromTs]]
                ])
                ->select(['petty_cash_voucher.pcv_no as doc_no', 'petty_cash_voucher.date as doc_date', 'petty_cash_voucher.amount', 'j.job_no', 'j.id as job_id', 'petty_cash_voucher.id as doc_id']);
                
            if (!empty($companyId) && $companyId != '0') {
                $pastPettyItems->andWhere(['j.company_id' => $companyId]);
            }
                
            foreach($pastPettyItems->asArray()->all() as $pcv) {
                $pastJobsExpenses += (float)$pcv['amount'];
                $pastJobExpenseList[] = [
                    'type' => 'Petty Cash',
                    'doc_no' => $pcv['doc_no'],
                    'doc_date' => $pcv['doc_date'],
                    'amount' => (float)$pcv['amount'],
                    'job_no' => $pcv['job_no'],
                    'job_id' => $pcv['job_id'],
                    'detail_url' => \yii\helpers\Url::to(['petty-cash-voucher/view', 'id' => $pcv['doc_id']])
                ];
            }

            // 2. Past Job Revenue (Invoices issued in current period but Job < $fromDate)
            $pastRevQuery = \backend\models\Invoice::find()
                ->alias('inv')
                ->innerJoin('job j', 'j.id = inv.job_id')
                ->where(['inv.status' => \backend\models\Invoice::STATUS_ACTIVE])
                ->andWhere(['inv.invoice_type' => [\backend\models\Invoice::TYPE_TAX_INVOICE, \backend\models\Invoice::TYPE_RECEIPT, '4', 4, \backend\models\Invoice::TYPE_QUOTATION]])
                ->andWhere(['between', 'inv.invoice_date', $fromDate, $toDate])
                ->andWhere([
                    'or',
                    ['<', 'j.job_date', date('Y-m-d 00:00:00', $fromTs)],
                    ['and', ['j.job_date' => null], ['<', 'j.created_at', $fromTs]]
                ]);
                
            if (!empty($companyId) && $companyId != '0') {
                $pastRevQuery->andWhere(['inv.company_id' => $companyId]);
            }
            
            $pastRevItems = $pastRevQuery->select(['inv.invoice_number as doc_no', 'inv.invoice_date as doc_date', 'inv.total_amount as amount', 'j.job_no', 'j.id as job_id', 'inv.id as doc_id', 'inv.invoice_type'])
                ->asArray()
                ->all();
            
            $dedupRevJobs = [];
            foreach($pastRevItems as $rev) {
                if (!isset($dedupRevJobs[$rev['job_id']])) {
                    $dedupRevJobs[$rev['job_id']] = $rev;
                } else {
                    if ((float)$rev['amount'] > (float)$dedupRevJobs[$rev['job_id']]['amount']) {
                        $dedupRevJobs[$rev['job_id']] = $rev;
                    }
                }
            }
            
            foreach($dedupRevJobs as $rev) {
                $pastJobsRevenue += (float)$rev['amount'];
                $typeLabel = 'Invoice';
                if ($rev['invoice_type'] == 'tax_invoice') $typeLabel = 'Tax Invoice';
                if ($rev['invoice_type'] == 'receipt' || $rev['invoice_type'] == '4') $typeLabel = 'Receipt';
                if ($rev['invoice_type'] == 'quotation') $typeLabel = 'Quotation';
                
                $pastJobRevenueList[] = [
                    'type' => $typeLabel,
                    'doc_no' => $rev['doc_no'],
                    'doc_date' => $rev['doc_date'],
                    'amount' => (float)$rev['amount'],
                    'job_no' => $rev['job_no'],
                    'job_id' => $rev['job_id'],
                    'detail_url' => \yii\helpers\Url::to(['invoice/view', 'id' => $rev['doc_id']])
                ];
            }
        }

        // --- Accounting PO Cashflow Alert & Comparison ---
        $latestClosing = MonthlyAccountClosing::find()
            ->andFilterWhere(['company_id' => $companyId])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $totalMainBankBalance = $latestClosing ? (float)$latestClosing->main_account_balance : 0;
        $totalPettyCashBalance = $latestClosing ? (float)$latestClosing->petty_cash_balance : 0;

        if ($totalPettyCashBalance == 0) {
            $pcvBalQ = PettyCashVoucher::find()->where(['!=', 'status', 3]);
            if (!empty($companyId) && $companyId != '0') {
                if ($companyId == 1) {
                    $pcvBalQ->andWhere(['or', ['company_id' => 1], ['company_id' => 0], ['company_id' => null]]);
                } else {
                    $pcvBalQ->andWhere(['company_id' => $companyId]);
                }
            }
            foreach ($pcvBalQ->all() as $pcv) {
                $totalPettyCashBalance += (float)$pcv->amount > 0 ? (float)$pcv->amount : (float)$pcv->calculateTotalAmount();
            }
        }

        $currentAvailableCash = $totalMainBankBalance + $totalPettyCashBalance;

        // Pending PO Payables (filtered by selected company if applicable)
        $poPayableQuery = Purch::find()
            ->where(['or', ['!=', 'approve_status', 2], ['approve_status' => null]])
            ->andWhere(['!=', 'status', Purch::STATUS_CANCELLED]);
        if (!empty($companyId) && $companyId != '0') {
            $poPayableQuery->andWhere(['company_id' => $companyId]);
        }
        $pendingPoPayables = (float)$poPayableQuery->sum('net_amount * COALESCE(NULLIF(currency_rate, 0), 1)');

        $isCashflowWarning = ($currentAvailableCash + $pendingReceivables) < $pendingPoPayables;

        // Monthly Closings History
        $monthlyClosings = MonthlyAccountClosing::find()
            ->orderBy(['id' => SORT_DESC])
            ->limit(12)
            ->all();

        // --- 8.8.4 Advanced Search System ---
        $jobsQuery = Job::find()->where(['job.status' => [1, 2]])->with(['company', 'quotation']);
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

            // Job-Based Revenue & Expenses for chart month
            $mJobsQuery = Job::find()->where(['job.status' => [1, 2]]);
            if (!empty($companyId) && $companyId != '0') {
                $mJobsQuery->andWhere(['job.company_id' => $companyId]);
            }
            $mJobsQuery->andWhere([
                'or',
                ['between', 'job.job_date', $mStart . ' 00:00:00', $mEnd . ' 23:59:59'],
                ['and', ['job.job_date' => null], ['between', 'job.created_at', strtotime($mStartDt), strtotime($mEndDt)]]
            ]);

            $mRev = $this->getCalculatedRevenue($revenueMode, $companyId, $mStart, $mEnd);
            $mJobIds = [];
            $mJobNos = [];
            $mPoIds = [];
            $mNonePrIds = [];

            foreach ((clone $mJobsQuery)->all() as $j) {
                $mJobIds[] = $j->id;
                if (!empty(trim($j->job_no))) {
                    $mJobNos[] = trim($j->job_no);
                }

                $jPos = $this->getJobPos($j);
                foreach ($jPos as $po) {
                    if ($po->approve_status == 1) {
                        $mPoIds[] = $po->id;
                    }
                }

                $jNonePrs = $this->getJobNonePrs($j);
                foreach ($jNonePrs as $npr) {
                    if ($npr->approve_status == PurchaseMaster::APPROVE_STATUS_APPROVED) {
                        $mNonePrIds[] = $npr->id;
                    }
                }
            }

            $mTotalExpenses = 0;

            $mPoIds = array_unique(array_filter($mPoIds));
            if (!empty($mPoIds)) {
                $mPo = (float)Purch::find()
                    ->where(['in', 'id', $mPoIds])
                    ->sum('(net_amount - COALESCE(vat_amount, 0)) * COALESCE(NULLIF(currency_rate, 0), 1)');
                $mTotalExpenses += $mPo;
            }

            if (!empty($mJobIds)) {
                $mPetty = (float)PettyCashVoucher::find()->where(['status' => 1, 'job_id' => $mJobIds])->sum('amount');
                $mTotalExpenses += $mPetty;

                $mStockIssues = (new \yii\db\Query())
                    ->select('l.qty, l.line_price, l.sale_price, p.sale_price as p_sale_price, p.cost_price as p_cost_price')
                    ->from('journal_trans_line l')
                    ->innerJoin('journal_trans t', 't.id = l.journal_trans_id')
                    ->leftJoin('product p', 'p.id = l.product_id')
                    ->where(['t.trans_type_id' => 3, 't.status' => 2, 't.job_id' => $mJobIds])
                    ->all();
                
                foreach ($mStockIssues as $issue) {
                    if (!empty($issue['line_price']) && (float)$issue['line_price'] > 0) {
                        $mTotalExpenses += (float)$issue['line_price'];
                    } elseif (!empty($issue['sale_price']) && (float)$issue['sale_price'] > 0) {
                        $mTotalExpenses += (float)$issue['sale_price'] * (float)$issue['qty'];
                    } else {
                        $unitPrice = (float)$issue['p_sale_price'] > 0 ? (float)$issue['p_sale_price'] : (float)$issue['p_cost_price'];
                        $mTotalExpenses += $unitPrice * (float)$issue['qty'];
                    }
                }
            }

            $mNonePrIds = array_unique(array_filter($mNonePrIds));
            if (!empty($mNonePrIds)) {
                $mNpr = (float)PurchaseMaster::find()
                    ->where(['in', 'id', $mNonePrIds])
                    ->sum('total_amount - COALESCE(vat_amount, 0)');
                $mTotalExpenses += $mNpr;
            }

            if (!empty($mJobNos)) {
                $veMQuery = VehicleExpense::find()->where(['job_no' => $mJobNos]);
                $mKm = abs((float)(clone $veMQuery)->sum('total_distance'));
                $mVehicleCost = abs((float)(clone $veMQuery)->sum('vehicle_cost'));
                $mVehicleWage = abs((float)(clone $veMQuery)->sum('total_wage'));
                $mEffVehicleCost = max($mVehicleCost, $mKm * 5);
                
                $mTotalExpenses += $mEffVehicleCost + $mVehicleWage;
            }

            // Cumulative Receivables Approximate for chart
            $invSubtotal = (float)Invoice::find()->where(['status' => Invoice::STATUS_ACTIVE, 'invoice_type' => [Invoice::TYPE_RECEIPT, '4', 4]])->andWhere(['<=', 'invoice_date', $mEnd])->sum('subtotal - COALESCE(discount_amount, 0)');
            $paidTotal = (float)\backend\models\InvoicePaymentReceipt::find()
                ->innerJoin('invoices', 'invoices.id = invoice_payment_receipt.invoice_id')
                ->where(['invoices.status' => Invoice::STATUS_ACTIVE, 'invoices.invoice_type' => [Invoice::TYPE_RECEIPT, '4', 4]])
                ->andWhere(['<=', 'invoice_payment_receipt.payment_date', $mEnd])
                ->sum('invoice_payment_receipt.amount');
            // Roughly remove VAT ratio from paid total assuming mostly 7%
            $paidNoVatApprox = $paidTotal / 1.07;
            $mRec = max(0, $invSubtotal - $paidNoVatApprox);

            $chartLabels[] = $mLabel;
            $chartRevenueData[] = round($mRev, 2);
            $chartExpensesData[] = round($mTotalExpenses, 2);
            $chartReceivablesData[] = round($mRec, 2);
        }

        // --- 8.9 Calculate Comprehensive Expenses by Company (Independent of Filtered Job) ---
        $companySummaries = [];
        $allComps = \backend\models\Company::find()->all();
        
        $totalAllRev = 0;
        $totalAllPo = 0;
        $totalAllNonePr = 0;
        $totalAllPetty = 0;
        $totalAllInv = 0;
        $totalAllVehicle = 0;
        $totalAllSalary = 0;
        $totalAllExp = 0;
        $totalAllNet = 0;

        foreach ($allComps as $comp) {
            $cId = $comp->id;
            
            // Revenue
            $cRev = $this->getCalculatedRevenue($revenueMode, $cId, $fromDate, $toDate);
            
            // PO
            $cPoQuery = Purch::find()
                ->where(['or', ['!=', 'approve_status', 2], ['approve_status' => null]])
                ->andWhere(['!=', 'status', Purch::STATUS_CANCELLED]);
            if ($cId == 1) {
                $cPoQuery->andWhere(['or', ['company_id' => 1], ['company_id' => null], ['company_id' => 0]]);
            } else {
                $cPoQuery->andWhere(['company_id' => $cId]);
            }
            if (!empty($fromDate) && !empty($toDate)) {
                $cPoQuery->andWhere(['between', 'purch_date', $fromDate, $toDate]);
            }
            $cPo = (float)$cPoQuery->sum('(net_amount - COALESCE(vat_amount, 0)) * COALESCE(NULLIF(currency_rate, 0), 1)');
                
            // Non PR
            $cNonePrQuery = PurchaseMaster::find()
                ->where(['or', ['!=', 'approve_status', 2], ['approve_status' => null]])
                ->andWhere(['!=', 'status', PurchaseMaster::STATUS_CANCELLED]);
            if ($cId == 1) {
                $cNonePrQuery->andWhere(['or', ['company_id' => 1], ['company_id' => null], ['company_id' => 0]]);
            } else {
                $cNonePrQuery->andWhere(['company_id' => $cId]);
            }
            if (!empty($fromDate) && !empty($toDate)) {
                $cNonePrQuery->andWhere(['between', 'docdat', $fromDate, $toDate]);
            }
            $cNonePr = (float)$cNonePrQuery->sum('total_amount - COALESCE(vat_amount, 0)');
                
            // Petty Cash
            $cPettyQuery = PettyCashVoucher::find()
                ->where(['!=', 'status', 3]);
            if ($cId == 1) {
                $cPettyQuery->andWhere(['or', ['company_id' => 1], ['company_id' => null], ['company_id' => 0]]);
            } else {
                $cPettyQuery->andWhere(['company_id' => $cId]);
            }
            if (!empty($fromDate) && !empty($toDate)) {
                $fromTsStr = $fromDate . ' 00:00:00';
                $toTsStr = $toDate . ' 23:59:59';
                $cPettyQuery->andWhere([
                    'or',
                    ['between', 'date', $fromDate, $toDate],
                    ['between', 'created_at', $fromTsStr, $toTsStr]
                ]);
            }
            $cPetty = 0;
            foreach ($cPettyQuery->all() as $pcv) {
                $cPetty += (float)$pcv->amount > 0 ? (float)$pcv->amount : (float)$pcv->calculateTotalAmount();
            }
                
            // Inventory (รวมทั้งที่ผูก Job และไม่ผูก Job)
            $stockQ = (new \yii\db\Query())
                ->select('l.qty, l.line_price, l.sale_price, p.sale_price as p_sale_price, p.cost_price as p_cost_price')
                ->from('journal_trans_line l')
                ->innerJoin('journal_trans t', 't.id = l.journal_trans_id')
                ->leftJoin('job j', 'j.id = t.job_id')
                ->leftJoin('product p', 'p.id = l.product_id')
                ->where(['t.trans_type_id' => 3, 't.status' => 2]);
            if ($cId == 1) {
                $stockQ->andWhere(['or', ['j.company_id' => 1], ['j.company_id' => null], ['t.job_id' => null], ['t.job_id' => 0]]);
            } else {
                $stockQ->andWhere(['j.company_id' => $cId]);
            }
            if (!empty($fromDate) && !empty($toDate)) {
                $stockQ->andWhere(['between', 't.trans_date', $fromDate, $toDate]);
            }
            $stockIssues = $stockQ->all();
            
            $cInv = 0;
            foreach ($stockIssues as $issue) {
                if (!empty($issue['line_price']) && (float)$issue['line_price'] > 0) {
                    $cInv += (float)$issue['line_price'];
                } elseif (!empty($issue['sale_price']) && (float)$issue['sale_price'] > 0) {
                    $cInv += (float)$issue['sale_price'] * (float)$issue['qty'];
                } else {
                    $unitPrice = (float)$issue['p_sale_price'] > 0 ? (float)$issue['p_sale_price'] : (float)$issue['p_cost_price'];
                    $cInv += $unitPrice * (float)$issue['qty'];
                }
            }

            // Vehicles (รวมทั้งที่ผูก Job และไม่ผูก Job)
            $veQ = VehicleExpense::find()
                ->leftJoin('job j', 'j.job_no COLLATE utf8mb4_unicode_ci = vehicle_expense.job_no COLLATE utf8mb4_unicode_ci');
            if ($cId == 1) {
                $veQ->where(['or', ['j.company_id' => 1], ['j.company_id' => null], ['vehicle_expense.job_no' => null], ['vehicle_expense.job_no' => '']]);
            } else {
                $veQ->where(['j.company_id' => $cId]);
            }
            if (!empty($fromDate) && !empty($toDate)) {
                $veQ->andWhere(['between', 'vehicle_expense.expense_date', $fromDate, $toDate]);
            }
            $cKm = abs((float)(clone $veQ)->sum('vehicle_expense.total_distance'));
            $cVehicleCost = abs((float)(clone $veQ)->sum('vehicle_expense.vehicle_cost'));
            $cVehicleWage = abs((float)(clone $veQ)->sum('vehicle_expense.total_wage'));
            $cEffVehicleCost = max($cVehicleCost, $cKm * 5);
            $cVehicleTotal = $cEffVehicleCost + $cVehicleWage;
            
            // Company Salary (เงินเดือนพนักงานประจำ)
            $cSalary = 0;
            if (!empty($fromDate) && !empty($toDate)) {
                $startM = (int)date('m', strtotime($fromDate));
                $startY = (int)date('Y', strtotime($fromDate));
                $endM = (int)date('m', strtotime($toDate));
                $endY = (int)date('Y', strtotime($toDate));
                
                $cSalary = (float)\backend\models\CompanySalary::find()
                    ->where(['company_id' => $cId])
                    ->andWhere(['between', new \yii\db\Expression('(salary_year * 100 + salary_month)'), $startY * 100 + $startM, $endY * 100 + $endM])
                    ->sum('amount');
            } else {
                $cSalary = (float)\backend\models\CompanySalary::find()
                    ->where(['company_id' => $cId, 'salary_year' => (int)date('Y'), 'salary_month' => (int)date('m')])
                    ->sum('amount');
            }

            $cTotalExp = $cPo + $cNonePr + $cPetty + $cInv + $cVehicleTotal + $cSalary;
            $cNetProfit = $cRev - $cTotalExp;

            $totalAllRev += $cRev;
            $totalAllPo += $cPo;
            $totalAllNonePr += $cNonePr;
            $totalAllPetty += $cPetty;
            $totalAllInv += $cInv;
            $totalAllVehicle += $cVehicleTotal;
            $totalAllSalary += $cSalary;
            $totalAllExp += $cTotalExp;
            $totalAllNet += $cNetProfit;
            
            $companySummaries[] = [
                'company_id' => $comp->id,
                'company_name' => $comp->name,
                'revenue' => $cRev,
                'po' => $cPo,
                'none_pr' => $cNonePr,
                'petty_cash' => $cPetty,
                'inventory' => $cInv,
                'vehicle' => $cVehicleTotal,
                'salary' => $cSalary,
                'total_expenses' => $cTotalExp,
                'net_profit' => $cNetProfit
            ];
        }

        $companySummariesTotals = [
            'revenue' => $totalAllRev,
            'po' => $totalAllPo,
            'none_pr' => $totalAllNonePr,
            'petty_cash' => $totalAllPetty,
            'inventory' => $totalAllInv,
            'vehicle' => $totalAllVehicle,
            'salary' => $totalAllSalary,
            'total_expenses' => $totalAllExp,
            'net_profit' => $totalAllNet
        ];

        // Synchronize top summary cards and Net Profit banner with Section 8.8.3 company summaries
        if (!empty($companyId) && $companyId != '0') {
            foreach ($companySummaries as $cs) {
                if (isset($cs['company_id']) && $cs['company_id'] == $companyId) {
                    $totalRevenue = $cs['revenue'];
                    $totalPoExpenses = $cs['po'];
                    $totalNonePrExpenses = $cs['none_pr'];
                    $totalPettyCashExpenses = $cs['petty_cash'];
                    $totalInventoryExpenses = $cs['inventory'];
                    $effectiveVehicleExpense = $cs['vehicle'];
                    $totalSalaryExpenses = $cs['salary'];
                    $totalExpenses = $cs['total_expenses'];
                    $netProfitLoss = $cs['net_profit'];
                    break;
                }
            }
            $companySummariesTotals = [
                'revenue' => $totalRevenue,
                'po' => $totalPoExpenses,
                'none_pr' => $totalNonePrExpenses,
                'petty_cash' => $totalPettyCashExpenses,
                'inventory' => $totalInventoryExpenses,
                'vehicle' => $effectiveVehicleExpense,
                'salary' => $totalSalaryExpenses,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfitLoss
            ];
        } else {
            $totalRevenue = $companySummariesTotals['revenue'];
            $totalPoExpenses = $companySummariesTotals['po'];
            $totalNonePrExpenses = $companySummariesTotals['none_pr'];
            $totalPettyCashExpenses = $companySummariesTotals['petty_cash'];
            $totalInventoryExpenses = $companySummariesTotals['inventory'];
            $effectiveVehicleExpense = $companySummariesTotals['vehicle'];
            $totalSalaryExpenses = $companySummariesTotals['salary'];
            $totalExpenses = $companySummariesTotals['total_expenses'];
            $netProfitLoss = $companySummariesTotals['net_profit'];
        }

        return $this->render('index', [
            'companyId' => $companyId,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totalExpenses' => $totalExpenses,
            'totalPettyCashExpenses' => $totalPettyCashExpenses,
            'totalInventoryExpenses' => $totalInventoryExpenses,
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
            'totalSalaryExpenses' => $totalSalaryExpenses,
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
            'totalReceivedAmount' => $totalReceivedAmount,
            'pastJobsExpenses' => $pastJobsExpenses,
            'pastJobsRevenue' => $pastJobsRevenue,
            'pastJobExpenseList' => $pastJobExpenseList,
            'pastJobRevenueList' => $pastJobRevenueList,
            'companySummaries' => $companySummaries,
            'companySummariesTotals' => $companySummariesTotals,
            'revenueMode' => $revenueMode,
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
        if (!$job || $job->status == Job::JOB_STATUS_CANCELLED) {
            return [
                'statuses' => array_fill(1, 15, JobActivityStatus::STATUS_CANCELLED),
                'details' => array_fill(1, 15, 'Job ถูกยกเลิกรายการ'),
                'metrics' => [
                    'jobRevenue' => 0,
                    'jobPoTotal' => 0,
                    'jobNonePrTotal' => 0,
                    'jobPettyCashTotal' => 0,
                    'inventoryTotal' => 0,
                    'jobKmTotal' => 0,
                    'jobVehicleCost' => 0,
                    'jobVehicleWage' => 0,
                    'jobKmCostAt5' => 0,
                    'effectiveVehicleCost' => 0,
                    'jobTotalExpenses' => 0,
                    'jobNetProfit' => 0,
                    'jobProfitPercent' => 0,
                    'daysRemaining' => 0,
                    'jobVehicleExpList' => [],
                    'jobInventoryCostWithInterest' => 0,
                    'jobPoCostWithInterest' => 0,
                    'jobNonePrCostWithInterest' => 0,
                    'jobProfitBeforeTax' => 0,
                ]
            ];
        }

        $manualStatuses = JobActivityStatus::find()
            ->where(['job_id' => $job->id])
            ->indexBy('step_no')
            ->all();

        $jobRevenue = (float)($job->job_amount ?: ($job->quotation ? $job->quotation->total_amount : 0));
        
        // Find Receipt Date for Interest Calculation
        $latestReceipt = (new \yii\db\Query())
            ->select('r.payment_date')
            ->from('invoices i')
            ->innerJoin('quotation q', 'q.id = i.quotation_id')
            ->innerJoin('job j', 'j.quotation_id = q.id')
            ->innerJoin('invoice_payment_receipt r', 'r.invoice_id = i.id')
            ->where(['j.id' => $job->id, 'i.invoice_type' => ['receipt', '4', 4]])
            ->orderBy(['r.payment_date' => SORT_DESC])
            ->scalar();
            
        $receiptDate = $latestReceipt ? $latestReceipt : date('Y-m-d');

        // Helper function for month difference
        $getMonthsDiff = function($start, $end) {
            if (empty($start) || empty($end)) return 0;
            $ts1 = strtotime($start);
            $ts2 = strtotime($end);
            if ($ts1 > $ts2) return 0;
            $y1 = (int)date('Y', $ts1); $m1 = (int)date('m', $ts1);
            $y2 = (int)date('Y', $ts2); $m2 = (int)date('m', $ts2);
            $months = (($y2 - $y1) * 12) + ($m2 - $m1);
            return max(0, $months);
        };

        $jobPos = $this->getJobPos($job);
        $jobPoTotal = 0;
        $jobPoInterest = 0;
        $jobPoHasDoc = false;
        foreach ($jobPos as $po) {
            $netNoVat = (float)$po->net_amount - (float)($po->vat_amount ?: 0);
            $amt = $netNoVat * ((float)$po->currency_rate > 0 ? (float)$po->currency_rate : 1);
            $jobPoTotal += $amt;
            if (!empty($po->purch_date)) {
                $m = $getMonthsDiff($po->purch_date, $receiptDate);
                $jobPoInterest += $amt * 0.01 * $m;
            }
            $poDocExists = (new \yii\db\Query())->from('purch_doc')->where(['purch_id' => $po->id])->exists();
            if ($poDocExists) $jobPoHasDoc = true;
        }
        
        $jobNonePrs = $this->getJobNonePrs($job);
        $jobNonePrTotal = 0;
        $jobNonePrInterest = 0;
        $jobNonePrHasDoc = false;
        foreach ($jobNonePrs as $npr) {
            $amt = (float)$npr->total_amount - (float)($npr->vat_amount ?: 0);
            $jobNonePrTotal += $amt;
            if (!empty($npr->docdat)) {
                $m = $getMonthsDiff($npr->docdat, $receiptDate);
                $jobNonePrInterest += $amt * 0.01 * $m;
            }
            $nonePrDocExists = (new \yii\db\Query())->from('purch_none_pr_doc')->where(['purchase_master_id' => $npr->id])->exists();
            if ($nonePrDocExists) {
                $jobNonePrHasDoc = true;
            }
        }

        // Petty Cash Expenses (Active vouchers, non-cancelled)
        $pcvQuery = PettyCashVoucher::find()
            ->where(['!=', 'status', 3])
            ->andWhere([
                'or',
                ['job_id' => $job->id],
                ['and', ['!=', 'quotation_id', null], ['quotation_id' => $job->quotation_id]]
            ]);
        if (!empty($job->job_no)) {
            $cleanJn = trim($job->job_no);
            $pcvQuery->orWhere(['and', ['!=', 'status', 3], ['like', 'paid_for', $cleanJn]]);
            $pcvQuery->orWhere(['and', ['!=', 'status', 3], ['like', 'pcv_no', $cleanJn]]);
        }
        $jobPettyCashTotal = 0;
        foreach ($pcvQuery->all() as $pcv) {
            $jobPettyCashTotal += (float)$pcv->amount > 0 ? (float)$pcv->amount : (float)$pcv->calculateTotalAmount();
        }
        
        // Inventory Cost (1.5% interest)
        $inventoryTotal = 0;
        $inventoryInterest = 0;
        $stockIssues = (new \yii\db\Query())
            ->select('l.product_id, l.qty, l.line_price, l.sale_price, t.trans_date')
            ->from('journal_trans_line l')
            ->innerJoin('journal_trans t', 't.id = l.journal_trans_id')
            ->where(['t.trans_type_id' => 3, 't.status' => 2, 't.job_id' => $job->id])
            ->all();
            
        foreach ($stockIssues as $issue) {
            $lineAmount = 0;
            if (!empty($issue['line_price']) && (float)$issue['line_price'] > 0) {
                $lineAmount = (float)$issue['line_price']; // line_price is total amount
            } elseif (!empty($issue['sale_price']) && (float)$issue['sale_price'] > 0) {
                $lineAmount = (float)$issue['sale_price'] * (float)$issue['qty'];
            } else {
                $product = \backend\models\Product::findOne($issue['product_id']);
                if ($product) {
                    $unitPrice = $product->sale_price > 0 ? $product->sale_price : $product->cost_price;
                    $lineAmount = (float)$unitPrice * (float)$issue['qty'];
                }
            }
            $inventoryTotal += $lineAmount;
            
            // Find purchase date (latest receive trans for this product before issue date)
            $purchaseDate = (new \yii\db\Query())
                ->select('t.trans_date')
                ->from('journal_trans_line l')
                ->innerJoin('journal_trans t', 't.id = l.journal_trans_id')
                ->where(['t.trans_type_id' => 1, 't.status' => 2, 'l.product_id' => $issue['product_id']])
                ->andWhere(['<=', 't.trans_date', $issue['trans_date']])
                ->orderBy(['t.trans_date' => SORT_DESC])
                ->scalar();
                
            $pDate = $purchaseDate ? $purchaseDate : $issue['trans_date'];
            $m = $getMonthsDiff($pDate, $receiptDate);
            $inventoryInterest += $lineAmount * 0.015 * $m;
        }

        $cleanJobNo = trim($job->job_no);
        $jobVehicleExp = [];
        if (!empty($cleanJobNo)) {
            // Strict exact match for this specific job_no only (TRIM exact match)
            $jobVehicleExp = VehicleExpense::find()
                ->where(['TRIM(job_no)' => $cleanJobNo])
                ->orderBy(['expense_date' => SORT_DESC])
                ->all();
        }

        $jobKmTotal = 0;
        $jobVehicleCost = 0;
        $jobVehicleWage = 0;
        foreach ($jobVehicleExp as $ve) {
            $jobKmTotal += abs((float)$ve->total_distance);
            $jobVehicleCost += abs((float)$ve->vehicle_cost);
            $jobVehicleWage += abs((float)$ve->total_wage);
        }
        $jobKmCostAt5 = $jobKmTotal * 5;
        // ใช้ค่าใช้จ่ายตามระยะทางจริง (x 5 บาท) หรือค่าใช้จ่ายรถที่บันทึก
        $effectiveVehicleCost = max($jobKmCostAt5, $jobVehicleCost);
        
        $jobPoCostWithInterest = $jobPoTotal + $jobPoInterest;
        $jobNonePrCostWithInterest = $jobNonePrTotal + $jobNonePrInterest;
        $jobInventoryCostWithInterest = $inventoryTotal + $inventoryInterest;
        
        $jobTotalExpenses = $jobPoCostWithInterest + $jobNonePrCostWithInterest + $jobInventoryCostWithInterest + $jobPettyCashTotal + $effectiveVehicleCost + $jobVehicleWage;
        
        // กำไร/ขาดทุนก่อนหักภาษี = Revenue - Total Expenses (Po+NonePr+Inventory+PettyCash+Vehicle+Wage) - 2% of Revenue
        $revenueNet2Percent = $jobRevenue * 0.02;
        $jobProfitBeforeTax = $jobRevenue - $jobTotalExpenses - $revenueNet2Percent;
        
        // กำไร/ขาดทุนสุทธิ = Profit before tax - 20%
        $jobNetProfit = $jobProfitBeforeTax - ($jobProfitBeforeTax > 0 ? ($jobProfitBeforeTax * 0.20) : 0);
        
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
                    $poDocsList = \backend\models\JobPoDoc::find()->where(['job_id' => $job->id])->all();
                    if (!empty($job->cus_po_doc) || !empty($poDocsList)) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $count = count($poDocsList) + (!empty($job->cus_po_doc) ? 1 : 0);
                        $stepDetails[$step] = 'แนบเอกสาร PO ลูกค้าแล้ว (' . $count . ' ไฟล์)';
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
                    $reportDocsList = \backend\models\JobReportDoc::find()->where(['job_id' => $job->id])->all();
                    if (!empty($job->report_doc) || !empty($reportDocsList)) {
                        $stepStatuses[$step] = JobActivityStatus::STATUS_GREEN;
                        $count = count($reportDocsList) + (!empty($job->report_doc) ? 1 : 0);
                        $stepDetails[$step] = 'แนบเอกสาร Final Report เรียบร้อย (' . $count . ' ไฟล์)';
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
                'jobPettyCashTotal' => $jobPettyCashTotal,
                'inventoryTotal' => $inventoryTotal,
                'jobKmTotal' => $jobKmTotal,
                'jobVehicleCost' => $jobVehicleCost,
                'jobVehicleWage' => $jobVehicleWage,
                'jobKmCostAt5' => $jobKmCostAt5,
                'effectiveVehicleCost' => $effectiveVehicleCost,
                'jobTotalExpenses' => $jobTotalExpenses,
                'jobNetProfit' => $jobNetProfit,
                'jobProfitPercent' => $jobProfitPercent,
                'daysRemaining' => $daysRemaining,
                'jobVehicleExpList' => $jobVehicleExp,
                'jobInventoryCostWithInterest' => $jobInventoryCostWithInterest,
                'jobPoCostWithInterest' => $jobPoCostWithInterest,
                'jobNonePrCostWithInterest' => $jobNonePrCostWithInterest,
                'jobProfitBeforeTax' => $jobProfitBeforeTax,
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

        // Fetch POs & Non-PRs with line items and attached docs for PO Search Modal
        $jobPosDetail = [];

        // 1. POs
        $jobPos = $this->getJobPos($job);
        foreach ($jobPos as $po) {
            $rawLines = PurchLine::find()->where(['purch_id' => $po->id])->all();
            $lines = [];
            foreach ($rawLines as $pl) {
                $rawPName = $pl->product_name;
                $rawPDesc = $pl->product_description;
                $pNote = $pl->note;
                $pCode = '';
                $pName = $rawPName;
                $pDesc = $rawPDesc;
                $brand = '';
                $modelName = '';

                if ($pl->product_id) {
                    $brand = \backend\models\Product::findBrand($pl->product_id);
                    $modelName = \backend\models\Product::findModelName($pl->product_id);
                }

                if ($pl->product) {
                    $pCode = $pl->product->code ?: '';
                    if (empty($pName)) {
                        $pName = $pl->product->name;
                    }
                    if (empty($pDesc)) {
                        $pDesc = $pl->product->description;
                    }
                }
                $lines[] = [
                    'product_name' => $pName ?: 'สินค้า',
                    'product_description' => $pDesc ?: '',
                    'raw_product_name' => $rawPName ?: '',
                    'raw_product_description' => $rawPDesc ?: '',
                    'brand' => $brand ?: '',
                    'model_name' => $modelName ?: '',
                    'product_code' => $pCode,
                    'qty' => (float)$pl->qty,
                    'unit' => isset($pl->unit_id) ? \backend\models\Unit::findName($pl->unit_id) : (isset($pl->unit) ? $pl->unit : ''),
                    'line_price' => (float)$pl->line_price,
                    'line_total' => (float)$pl->line_total,
                    'note' => $pNote ?: '',
                ];
            }
            $docs = (new \yii\db\Query())->from('purch_doc')->where(['purch_id' => $po->id])->all();
            $vendorName = $po->vendor_name;
            if (empty($vendorName) && $po->vendor_id) {
                $vendor = \backend\models\Vendor::findOne($po->vendor_id);
                if ($vendor) $vendorName = $vendor->name;
            }
            $jobPosDetail[] = [
                'type' => 'PO',
                'id' => $po->id,
                'doc_no' => $po->purch_no ?: ('PO-' . $po->id),
                'doc_date' => $po->purch_date ?: '-',
                'vendor_name' => $vendorName ?: 'ไม่ระบุ Vendor',
                'amount' => (float)$po->net_amount,
                'status_label' => $po->getApproveStatusLabel(),
                'lines' => $lines,
                'docs' => $docs,
                'detail_url' => \yii\helpers\Url::to(['purch/view', 'id' => $po->id])
            ];
        }

        // 2. Non-PRs
        $jobNonePrs = $this->getJobNonePrs($job);
        foreach ($jobNonePrs as $npr) {
            try {
                $rawLines = (new \yii\db\Query())->from('purchase_detail')->where(['purchase_master_id' => $npr->id])->all();
            } catch (\Exception $e) {
                $rawLines = [];
            }
            $lines = [];
            foreach ($rawLines as $npl) {
                $pName = $npl['stkdes'] ?: ($npl['stkcod'] ?: 'สินค้า');
                $pCode = $npl['stkcod'] ?? '';
                $pDesc = (!empty($pCode) && $pName !== $pCode) ? ('รหัสสินค้า: ' . $pCode) : '';
                $lines[] = [
                    'product_name' => $pName,
                    'product_description' => $pDesc,
                    'product_code' => $pCode,
                    'qty' => (float)($npl['uqnty'] ?? ($npl['qty'] ?? 0)),
                    'unit' => $npl['unit'] ?? '',
                    'line_price' => (float)($npl['unitpr'] ?? 0),
                    'line_total' => (float)($npl['amount'] ?? ($npl['total_amount'] ?? 0)),
                    'note' => $npl['remark'] ?? '',
                ];
            }
            $vendorName = $npr->supnam ?? '';
            if (empty($vendorName) && !empty($npr->supcod)) {
                $vendor = \backend\models\Vendor::find()->where(['code' => $npr->supcod])->one();
                if ($vendor) $vendorName = $vendor->name;
            }
            if (empty($vendorName) && isset($npr->vendor_id) && $npr->vendor_id) {
                $vendor = \backend\models\Vendor::findOne($npr->vendor_id);
                if ($vendor) $vendorName = $vendor->name;
            }
            $docs = (new \yii\db\Query())->from('purch_none_pr_doc')->where(['purchase_master_id' => $npr->id])->all();
            if (empty($docs) && !empty($npr->cus_po_doc)) {
                $filePath = Yii::getAlias('@webroot/uploads/purch_doc/' . $npr->cus_po_doc);
                if (file_exists($filePath)) {
                    $docs[] = ['doc_name' => $npr->cus_po_doc, 'title' => 'PO Doc'];
                }
            }
            $jobPosDetail[] = [
                'type' => 'None-PR',
                'id' => $npr->id,
                'doc_no' => $npr->docnum ?: ($npr->job_no ?: 'None-PR-' . $npr->id),
                'doc_date' => $npr->docdat ?: '-',
                'vendor_name' => $vendorName ?: 'ไม่ระบุ Vendor',
                'amount' => (float)$npr->total_amount,
                'status_label' => $npr->approve_status == 1 ? 'อนุมัติ' : 'รอพิจารณา',
                'lines' => $lines,
                'docs' => $docs,
                'detail_url' => \yii\helpers\Url::to(['purchase-master/view', 'id' => $npr->id])
            ];
        }

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
            'jobInventoryCostWithInterest' => $eval['metrics']['jobInventoryCostWithInterest'],
            'jobPoCostWithInterest' => $eval['metrics']['jobPoCostWithInterest'],
            'jobNonePrCostWithInterest' => $eval['metrics']['jobNonePrCostWithInterest'],
            'jobPettyCashTotal' => $eval['metrics']['jobPettyCashTotal'],
            'jobProfitBeforeTax' => $eval['metrics']['jobProfitBeforeTax'],
            'canCancel' => $canCancel,
            'jobPosDetail' => $jobPosDetail,
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

    /**
     * Helper to retrieve all POs linked to a Job (via direct job_id, PR link, PO number, or line item ref)
     */
    private function getJobPos($job)
    {
        if (!$job) return [];

        $jobId = $job->id;
        $jobNo = trim($job->job_no);

        // Core variations of job_no (e.g. "ARC-QT26-000062" -> core = "QT26-000062", num = "000062")
        $jobNoCore = $jobNo;
        if (preg_match('/[A-Z]+-(.+)/i', $jobNo, $m)) {
            $jobNoCore = trim($m[1]);
        }
        $jobNoNum = '';
        if (preg_match('/(\d{5,})/', $jobNo, $m)) {
            $jobNoNum = $m[1];
        }

        $poIds = [];

        // 1. Direct match by job_id
        if ($jobId) {
            $directIds = Purch::find()->select('id')
                ->where(['or', ['job_id' => $jobId], ['job_id' => (string)$jobId]])
                ->column();
            $poIds = array_merge($poIds, $directIds);
        }

        // 2. Linked via Purchase Request (purch_req where job_id matches or purch_req_no contains job_no)
        $prQuery = (new \yii\db\Query())
            ->select('purch_id')
            ->from('purch_req')
            ->where(['is not', 'purch_id', null])
            ->andWhere(['>', 'purch_id', 0]);

        $prWhere = ['or'];
        if ($jobId) {
            $prWhere[] = ['job_id' => $jobId];
            $prWhere[] = ['job_id' => (string)$jobId];
        }
        if (!empty($jobNo)) {
            $prWhere[] = ['like', 'purch_req_no', $jobNo];
        }
        if (!empty($jobNoCore) && $jobNoCore !== $jobNo) {
            $prWhere[] = ['like', 'purch_req_no', $jobNoCore];
        }
        if (!empty($jobNoNum)) {
            $prWhere[] = ['like', 'purch_req_no', $jobNoNum];
        }

        $prPoIds = $prQuery->andWhere($prWhere)->column();
        $poIds = array_merge($poIds, $prPoIds);

        // 3. Match by PO number, ref_no, note, delivery_note, special_note
        $whereOr = ['or'];
        if (!empty($jobNo)) {
            $whereOr[] = ['like', 'purch_no', $jobNo];
            $whereOr[] = ['like', 'ref_no', $jobNo];
            $whereOr[] = ['like', 'note', $jobNo];
        }
        if (!empty($jobNoCore) && $jobNoCore !== $jobNo) {
            $whereOr[] = ['like', 'purch_no', $jobNoCore];
            $whereOr[] = ['like', 'ref_no', $jobNoCore];
            $whereOr[] = ['like', 'note', $jobNoCore];
        }
        if (!empty($jobNoNum)) {
            $whereOr[] = ['like', 'purch_no', $jobNoNum];
            $whereOr[] = ['like', 'ref_no', $jobNoNum];
        }
        if (count($whereOr) > 1) {
            $byNoPoIds = Purch::find()->select('id')->where($whereOr)->column();
            $poIds = array_merge($poIds, $byNoPoIds);
        }

        // 4. Match by purch_line doc_ref_no or note
        $lineWhereOr = ['or'];
        if (!empty($jobNo)) {
            $lineWhereOr[] = ['like', 'doc_ref_no', $jobNo];
            $lineWhereOr[] = ['like', 'note', $jobNo];
        }
        if (!empty($jobNoCore) && $jobNoCore !== $jobNo) {
            $lineWhereOr[] = ['like', 'doc_ref_no', $jobNoCore];
            $lineWhereOr[] = ['like', 'note', $jobNoCore];
        }
        if (!empty($jobNoNum)) {
            $lineWhereOr[] = ['like', 'doc_ref_no', $jobNoNum];
        }
        if (count($lineWhereOr) > 1) {
            $linePoIds = (new \yii\db\Query())
                ->select('purch_id')
                ->from('purch_line')
                ->where($lineWhereOr)
                ->column();
            $poIds = array_merge($poIds, $linePoIds);
        }

        $poIds = array_unique(array_filter($poIds));
        if (empty($poIds)) {
            return [];
        }

        return Purch::find()
            ->where(['in', 'id', $poIds])
            ->andWhere(['or', ['!=', 'approve_status', 2], ['approve_status' => null]])
            ->andWhere(['!=', 'status', Purch::STATUS_CANCELLED])
            ->all();
    }

    /**
     * Helper to retrieve all None-PRs (PurchaseMaster) linked to a Job
     */
    private function getJobNonePrs($job)
    {
        if (!$job) return [];

        $jobId = (string)$job->id;
        $jobNo = trim($job->job_no);

        $jobNoCore = $jobNo;
        if (preg_match('/[A-Z]+-(.+)/i', $jobNo, $m)) {
            $jobNoCore = trim($m[1]);
        }
        $jobNoNum = '';
        if (preg_match('/(\d{5,})/', $jobNo, $m)) {
            $jobNoNum = $m[1];
        }

        $whereOr = [
            'or',
            ['job_no' => $jobId],
            ['job_no' => $jobNo],
            ['like', 'job_no', $jobNo],
            ['like', 'docnum', $jobNo],
            ['like', 'refnum', $jobNo],
            ['like', 'remark', $jobNo],
            ['like', 'additional_note', $jobNo],
        ];

        if (!empty($jobNoCore) && $jobNoCore !== $jobNo) {
            $whereOr[] = ['job_no' => $jobNoCore];
            $whereOr[] = ['like', 'job_no', $jobNoCore];
            $whereOr[] = ['like', 'docnum', $jobNoCore];
            $whereOr[] = ['like', 'refnum', $jobNoCore];
            $whereOr[] = ['like', 'remark', $jobNoCore];
            $whereOr[] = ['like', 'additional_note', $jobNoCore];
        }

        if (!empty($jobNoNum)) {
            $whereOr[] = ['like', 'job_no', $jobNoNum];
            $whereOr[] = ['like', 'docnum', $jobNoNum];
            $whereOr[] = ['like', 'refnum', $jobNoNum];
        }

        return PurchaseMaster::find()
            ->where($whereOr)
            ->andWhere(['or', ['!=', 'approve_status', 2], ['approve_status' => null]])
            ->andWhere(['!=', 'status', PurchaseMaster::STATUS_CANCELLED])
            ->all();
    }
}
