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
     * Executive Dashboard 8.8 Main Page
     */
    public function actionIndex()
    {
        $companyId = Yii::$app->request->get('company_id', '');
        $fromDate = Yii::$app->request->get('from_date', '');
        $toDate = Yii::$app->request->get('to_date', '');
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

            // Filter PO Expenses by integer timestamp OR string date
            $expensesQuery->andWhere([
                'or',
                ['between', 'created_at', $fromTs, $toTs],
                ['between', 'purch_date', $fromDateTime, $toDateTime]
            ]);

            // Filter None PR Expenses by integer timestamp OR string date
            $nonePrQuery->andWhere([
                'or',
                ['between', 'created_at', $fromTs, $toTs],
                ['between', 'docdat', $fromDate, $toDate]
            ]);

            // Filter Invoices by invoice_date OR created_at string
            $invoiceQuery->andWhere([
                'or',
                ['between', 'invoice_date', $fromDate, $toDate],
                ['between', 'created_at', $fromDateTime, $toDateTime]
            ]);

            // Filter Vehicle Expenses by expense_date OR created_at string
            $vehicleQuery->andWhere([
                'or',
                ['between', 'expense_date', $fromDate, $toDate],
                ['between', 'created_at', $fromDateTime, $toDateTime]
            ]);
        }

        $totalPoExpenses = (float)$expensesQuery->sum('net_amount');
        $totalNonePrExpenses = (float)$nonePrQuery->sum('total_amount');
        $totalVehicleExpenses = (float)$vehicleQuery->sum('total_cost');
        $totalWages = (float)$wageQuery->sum('net_total');
        
        $totalExpenses = $totalPoExpenses + $totalNonePrExpenses + $totalVehicleExpenses + $totalWages;

        // Total Revenue from Invoices
        $totalRevenue = (float)$invoiceQuery->sum('total_amount');

        // Fallback: If invoices in current filter return 0.00, check Job amount or Quotation total for that filter
        if ($totalRevenue == 0) {
            $jobRevQuery = Job::find()->where(['status' => 1]);
            if (!empty($companyId) && $companyId != '0') {
                $jobRevQuery->andWhere(['company_id' => $companyId]);
            }
            if (!empty($fromDate) && !empty($toDate)) {
                $jobRevQuery->andWhere([
                    'or',
                    ['between', 'created_at', strtotime($fromDate . ' 00:00:00'), strtotime($toDate . ' 23:59:59')],
                    ['between', 'job_date', $fromDate . ' 00:00:00', $toDate . ' 23:59:59']
                ]);
            }
            $totalRevenue = (float)$jobRevQuery->sum('job_amount');

            if ($totalRevenue == 0) {
                $quotRevQuery = Quotation::find()->where(['approve_status' => 1]);
                if (!empty($companyId) && $companyId != '0') {
                    $quotRevQuery->andWhere(['company_id' => $companyId]);
                }
                if (!empty($fromDate) && !empty($toDate)) {
                    $quotRevQuery->andWhere(['between', 'created_at', strtotime($fromDate . ' 00:00:00'), strtotime($toDate . ' 23:59:59')]);
                }
                $totalRevenue = (float)$quotRevQuery->sum('total_amount');
            }
        }

        // Pending Receivables (Active Invoices or Active Jobs)
        $unpaidInvoices = Invoice::find()
            ->where(['status' => Invoice::STATUS_ACTIVE])
            ->andFilterWhere(['company_id' => $companyId])
            ->sum('total_amount');
        $pendingReceivables = (float)$unpaidInvoices;
        if ($pendingReceivables == 0) {
            $pendingReceivables = (float)Job::find()
                ->where(['status' => 1])
                ->andFilterWhere(['company_id' => $companyId])
                ->sum('job_amount');
        }

        // Vehicle Usage Km x 5 THB/km
        $totalKm = (float)$vehicleQuery->sum('total_distance');
        $vehicleCostByKm = $totalKm * 5;
        if ($vehicleCostByKm == 0 && $totalVehicleExpenses > 0) {
            $vehicleCostByKm = $totalVehicleExpenses;
        }

        // Net Profit / Loss for Group
        $netProfitLoss = $totalRevenue - ($totalExpenses + $vehicleCostByKm);

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

        // Pending PO Payables
        $pendingPoPayables = (float)Purch::find()
            ->where(['approve_status' => 1])
            ->sum('net_amount');

        $isCashflowWarning = ($currentAvailableCash + $pendingReceivables) < $pendingPoPayables;

        // Monthly Closings History
        $monthlyClosings = MonthlyAccountClosing::find()
            ->orderBy(['id' => SORT_DESC])
            ->limit(12)
            ->all();

        // --- 8.8.4 Advanced Search System ---
        $jobsQuery = Job::find()->with(['company', 'quotation']);

        if (!empty($searchJobNo)) {
            $jobsQuery->andWhere(['like', 'job_no', $searchJobNo]);
        }
        if (!empty($searchCustomer)) {
            $jobsQuery->joinWith('quotation q')
                      ->andWhere(['or', ['like', 'q.customer_name', $searchCustomer], ['q.customer_id' => $searchCustomer]]);
        }
        if (!empty($companyId) && $companyId != '0') {
            $jobsQuery->andWhere(['job.company_id' => $companyId]);
        }

        $searchJobsList = $jobsQuery->orderBy(['id' => SORT_DESC])->limit(50)->all();

        // Auto-evaluate 15-step activity statuses for all searchJobsList
        $jobActivityMap = [];
        foreach ($searchJobsList as $jobItem) {
            $eval = $this->evaluateJobStepStatuses($jobItem);
            $jobActivityMap[$jobItem->id] = $eval['statuses'];
        }

        return $this->render('index', [
            'companyId' => $companyId,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totalExpenses' => $totalExpenses,
            'totalRevenue' => $totalRevenue,
            'pendingReceivables' => $pendingReceivables,
            'totalKm' => $totalKm,
            'vehicleCostByKm' => $vehicleCostByKm,
            'netProfitLoss' => $netProfitLoss,
            'currentAvailableCash' => $currentAvailableCash,
            'totalMainBankBalance' => $totalMainBankBalance,
            'totalPettyCashBalance' => $totalPettyCashBalance,
            'pendingPoPayables' => $pendingPoPayables,
            'isCashflowWarning' => $isCashflowWarning,
            'monthlyClosings' => $monthlyClosings,
            'searchJobsList' => $searchJobsList,
            'jobActivityMap' => $jobActivityMap,
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
        $jobVehicleExp = VehicleExpense::find()->where(['job_no' => $job->job_no])->all();
        $jobKmTotal = 0;
        $jobVehicleCost = 0;
        foreach ($jobVehicleExp as $ve) {
            $jobKmTotal += (float)$ve->total_distance;
            $jobVehicleCost += (float)($ve->vehicle_cost ?: $ve->total_cost);
        }
        $jobKmCostAt5 = $jobKmTotal * 5;
        $jobTotalExpenses = $jobPoTotal + $jobNonePrTotal + $jobVehicleCost + $jobKmCostAt5;
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
                        $stepDetails[$step] = 'บันทึกการใช้รถยนต์แล้ว ' . number_format($jobKmTotal, 1) . ' กม.';
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
                'jobKmCostAt5' => $jobKmCostAt5,
                'jobTotalExpenses' => $jobTotalExpenses,
                'jobNetProfit' => $jobNetProfit,
                'jobProfitPercent' => $jobProfitPercent,
                'daysRemaining' => $daysRemaining,
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
            'jobKmCostAt5' => $eval['metrics']['jobKmCostAt5'],
            'jobTotalExpenses' => $eval['metrics']['jobTotalExpenses'],
            'jobNetProfit' => $eval['metrics']['jobNetProfit'],
            'jobProfitPercent' => $eval['metrics']['jobProfitPercent'],
            'daysRemaining' => $eval['metrics']['daysRemaining'],
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
}
