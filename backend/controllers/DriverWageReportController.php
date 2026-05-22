<?php

namespace backend\controllers;

use Yii;
use backend\models\DriverWageReport;
use backend\models\VehicleExpense;
use backend\models\Employee;
use backend\models\Worker;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DriverWageReportController implements reports and management for driver wages.
 */
class DriverWageReportController extends BaseController
{
    /**
     * Lists all driver wages reports for the selected month and year.
     * @return mixed
     */
    public function actionIndex()
    {
        $month = Yii::$app->request->get('month', date('m'));
        $year = Yii::$app->request->get('year', date('Y'));

        // 1. Query distinct vehicles and sum of total_wage from vehicle_expense for the given month/year
        $vehicleExpenses = (new \yii\db\Query())
            ->select([
                'vehicle_no',
                'SUM(total_wage) AS total_wage_sum'
            ])
            ->from('vehicle_expense')
            ->where('YEAR(expense_date) = :year AND MONTH(expense_date) = :month', [
                ':year' => $year,
                ':month' => $month
            ])
            ->groupBy('vehicle_no')
            ->all();

        // 2. Query saved records from driver_wage_report
        $savedReports = DriverWageReport::find()
            ->where(['report_month' => $month, 'report_year' => $year])
            ->indexBy('vehicle_no')
            ->all();

        // 3. Merge them
        $reportModels = [];
        
        // Add active vehicles from vehicle_expense
        foreach ($vehicleExpenses as $exp) {
            $vNo = $exp['vehicle_no'];
            if (empty($vNo) || $vNo === '#NAME?') {
                continue;
            }
            
            if (isset($savedReports[$vNo])) {
                $model = $savedReports[$vNo];
                // Keep the latest total wage from imported vehicle expenses
                $model->trip_allowance = floatval($exp['total_wage_sum']);
            } else {
                $model = new DriverWageReport();
                $model->report_month = intval($month);
                $model->report_year = intval($year);
                $model->vehicle_no = $vNo;
                $model->trip_allowance = floatval($exp['total_wage_sum']);
                $model->cost_of_living = 0.00;
                $model->social_security = 0.00;
                $model->ot = 0.00;
                $model->food_allowance = 0.00;
                $model->tax_withholding = 0.00;
                $model->cash_advance = 0.00;
                $model->traffic_fine = 0.00;
                $model->damage_insurance = 0.00;
                $model->product_damage = 0.00;
                $model->other_deduction = 0.00;
            }
            
            // Calculate total and net_total
            $total_income = $model->cost_of_living + $model->trip_allowance;
            $model->net_total = $total_income + $model->ot + $model->food_allowance - (
                $model->social_security + $model->tax_withholding + $model->cash_advance +
                $model->traffic_fine + $model->damage_insurance + $model->product_damage + $model->other_deduction
            );
            
            $reportModels[$vNo] = $model;
        }

        // Add any saved reports that might not have trips in vehicle_expense for this month
        foreach ($savedReports as $vNo => $model) {
            if (!isset($reportModels[$vNo])) {
                $reportModels[$vNo] = $model;
            }
        }

        // Sort by vehicle_no
        ksort($reportModels);

        // 4. Fetch drivers list for dropdown
        $employees = Employee::find()->where(['status' => 1])->all();
        $workers = Worker::find()->where(['status' => 1])->all();
        $driversList = [];
        foreach ($employees as $emp) {
            $name = trim($emp->fname . ' ' . $emp->lname);
            if (!empty($name)) {
                $driversList[$name] = $name . ' (พนักงาน)';
            }
        }
        foreach ($workers as $w) {
            $name = trim($w->fnam . ' ' . $w->lname);
            if (empty($name)) {
                $name = trim($w->name);
            }
            if (!empty($name)) {
                $driversList[$name] = $name . ' (ลูกจ้าง)';
            }
        }
        ksort($driversList);

        return $this->render('index', [
            'reportModels' => $reportModels,
            'driversList' => $driversList,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Saves the posted driver wages reports.
     */
    public function actionSave()
    {
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post('report');
            $month = intval(Yii::$app->request->post('month'));
            $year = intval(Yii::$app->request->post('year'));

            if (is_array($postData)) {
                foreach ($postData as $vNo => $data) {
                    $model = DriverWageReport::findOne([
                        'report_month' => $month,
                        'report_year' => $year,
                        'vehicle_no' => $vNo
                    ]);

                    if (!$model) {
                        $model = new DriverWageReport();
                        $model->report_month = $month;
                        $model->report_year = $year;
                        $model->vehicle_no = $vNo;
                    }

                    $model->driver_name = $data['driver_name'] ?? null;
                    $model->cost_of_living = floatval($data['cost_of_living'] ?? 0);
                    $model->trip_allowance = floatval($data['trip_allowance'] ?? 0);
                    $model->social_security = floatval($data['social_security'] ?? 0);
                    $model->ot = floatval($data['ot'] ?? 0);
                    $model->food_allowance = floatval($data['food_allowance'] ?? 0);
                    $model->tax_withholding = floatval($data['tax_withholding'] ?? 0);
                    $model->cash_advance = floatval($data['cash_advance'] ?? 0);
                    $model->traffic_fine = floatval($data['traffic_fine'] ?? 0);
                    $model->damage_insurance = floatval($data['damage_insurance'] ?? 0);
                    $model->product_damage = floatval($data['product_damage'] ?? 0);
                    $model->other_deduction = floatval($data['other_deduction'] ?? 0);

                    // Re-calculate net_total in backend
                    $total_income = $model->cost_of_living + $model->trip_allowance;
                    $model->net_total = $total_income + $model->ot + $model->food_allowance - (
                        $model->social_security + $model->tax_withholding + $model->cash_advance +
                        $model->traffic_fine + $model->damage_insurance + $model->product_damage + $model->other_deduction
                    );

                    if (!$model->save()) {
                        Yii::error("Save driver wage report failed: " . json_encode($model->getErrors()), __METHOD__);
                    }
                }
                Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
            }
            return $this->redirect(['index', 'month' => $month, 'year' => $year]);
        }

        return $this->redirect(['index']);
    }
}
