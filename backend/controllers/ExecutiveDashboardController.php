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
        $fromDate = Yii::$app->request->get('from_date', date('Y-m-01'));
        $toDate = Yii::$app->request->get('to_date', date('Y-m-t'));
        $searchJobNo = Yii::$app->request->get('search_job_no', '');
        $searchVendor = Yii::$app->request->get('search_vendor', '');
        $searchCustomer = Yii::$app->request->get('search_customer', '');
        $searchProduct = Yii::$app->request->get('search_product', '');

        // --- 8.8.1 Group Companies Financial Calculation ---
        $expensesQuery = Purch::find()->where(['approve_status' => 1]);
        $nonePrQuery = PurchaseMaster::find()->where(['approve_status' => PurchaseMaster::APPROVE_STATUS_APPROVED]);
        $invoiceQuery = Invoice::find();
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

            $expensesQuery->andWhere(['between', 'created_at', $fromTs, $toTs]);
            $nonePrQuery->andWhere(['between', 'created_at', $fromTs, $toTs]);
            $invoiceQuery->andWhere(['between', 'created_at', $fromTs, $toTs]);
            $vehicleQuery->andWhere(['between', 'created_at', $fromTs, $toTs]);
        }

        $totalPoExpenses = (float)$expensesQuery->sum('net_amount');
        $totalNonePrExpenses = (float)$nonePrQuery->sum('total_amount');
        $totalVehicleExpenses = (float)$vehicleQuery->sum('total_cost');
        $totalWages = (float)$wageQuery->sum('net_total');
        
        $totalExpenses = $totalPoExpenses + $totalNonePrExpenses + $totalVehicleExpenses + $totalWages;

        $totalRevenue = (float)$invoiceQuery->sum('total_amount');

        // Pending Receivables (Active Invoices)
        $unpaidInvoices = Invoice::find()
            ->where(['status' => Invoice::STATUS_ACTIVE])
            ->andFilterWhere(['company_id' => $companyId])
            ->sum('total_amount');
        $pendingReceivables = (float)$unpaidInvoices;

        // Vehicle Usage Km x 5 THB/km
        $totalKm = (float)$vehicleQuery->sum('total_distance');
        $vehicleCostByKm = $totalKm * 5;

        // Net Profit / Loss for Group
        $netProfitLoss = $totalRevenue - ($totalExpenses + $vehicleCostByKm);

        // --- 8.8.2 Accounting PO Cashflow Alert & Comparison ---
        $mainBankAccounts = BankAccount::find()->where(['status' => 1])->all();
        $totalMainBankBalance = 0;
        foreach ($mainBankAccounts as $acc) {
            $totalMainBankBalance += (float)$acc->balance;
        }

        // Petty Cash Balance
        $totalPettyCashBalance = (float)PettyCashVoucher::find()
            ->where(['status' => 1])
            ->sum('amount');

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

        // Map 15-step activity statuses for searchJobsList
        $jobIds = ArrayHelper::getColumn($searchJobsList, 'id');
        $jobActivityMap = [];
        if (!empty($jobIds)) {
            $statuses = JobActivityStatus::find()->where(['in', 'job_id', $jobIds])->all();
            foreach ($statuses as $st) {
                $jobActivityMap[$st->job_id][$st->step_no] = $st->status;
            }
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
     * Job Activity Pipeline Detail Page
     */
    public function actionJobPipeline($id)
    {
        $job = Job::findOne($id);
        if (!$job) {
            throw new NotFoundHttpException('ไม่พบข้อมูล Job ที่ต้องการ');
        }

        // Initialize 15 activity statuses for this Job if not created
        for ($step = 1; $step <= 15; $step++) {
            $statusModel = JobActivityStatus::findOne(['job_id' => $job->id, 'step_no' => $step]);
            if (!$statusModel) {
                $statusModel = new JobActivityStatus();
                $statusModel->job_id = $job->id;
                $statusModel->step_no = $step;
                $statusModel->status = JobActivityStatus::STATUS_RED; // Default Red
                $statusModel->created_at = time();
                $statusModel->save(false);
            }
        }

        $activityStatuses = JobActivityStatus::find()
            ->where(['job_id' => $job->id])
            ->indexBy('step_no')
            ->all();

        // Financial & Metrics calculations for this specific Job
        $jobRevenue = (float)($job->job_amount ?: ($job->quotation ? $job->quotation->total_amount : 0));

        // Related POs & Expenses for this Job
        $jobPos = Purch::find()->where(['job_id' => $job->id])->all();
        $jobPoTotal = 0;
        foreach ($jobPos as $po) {
            $jobPoTotal += (float)$po->net_amount;
        }

        $jobNonePrs = PurchaseMaster::find()->where(['job_no' => $job->job_no])->all();
        $jobNonePrTotal = 0;
        foreach ($jobNonePrs as $npr) {
            $jobNonePrTotal += (float)$npr->total_amount;
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

        // Auto-update Step 10 & Step 15 Status based on profit percentage
        $step10 = $activityStatuses[10] ?? null;
        if ($step10 && $step10->status != JobActivityStatus::STATUS_CANCELLED) {
            if ($jobProfitPercent >= 20) {
                $step10->status = JobActivityStatus::STATUS_GREEN;
            } elseif ($jobProfitPercent > 0) {
                $step10->status = JobActivityStatus::STATUS_ORANGE;
            } else {
                $step10->status = JobActivityStatus::STATUS_RED;
            }
            $step10->save(false);
        }

        // Days remaining calculation
        $daysRemaining = 0;
        $dueDate = !empty($job->end_date) ? $job->end_date : $job->job_date;
        if (!empty($dueDate)) {
            $targetTs = strtotime($dueDate);
            $todayTs = strtotime(date('Y-m-d'));
            $daysRemaining = round(($targetTs - $todayTs) / (60 * 60 * 24));
        }

        $canCancel = $this->checkCanCancelStep();

        return $this->render('job_pipeline', [
            'job' => $job,
            'activityStatuses' => $activityStatuses,
            'jobRevenue' => $jobRevenue,
            'jobPoTotal' => $jobPoTotal,
            'jobNonePrTotal' => $jobNonePrTotal,
            'jobKmTotal' => $jobKmTotal,
            'jobKmCostAt5' => $jobKmCostAt5,
            'jobTotalExpenses' => $jobTotalExpenses,
            'jobNetProfit' => $jobNetProfit,
            'jobProfitPercent' => $jobProfitPercent,
            'daysRemaining' => $daysRemaining,
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
