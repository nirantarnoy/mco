<?php
namespace backend\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use backend\models\PettyCashAdvance;
use backend\models\PettyCashVoucher;
use yii\data\ArrayDataProvider;

class PettyCashReportController extends Controller
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
        ];
    }

    public function actionIndex()
    {
        $currentBalance = PettyCashAdvance::getCurrentBalance();
        $maxAmount = PettyCashAdvance::MAX_AMOUNT;
        $minAmount = PettyCashAdvance::MIN_AMOUNT;
        $needsRefill = PettyCashAdvance::needsRefill();

        // ข้อมูลการเบิกทดแทน
        $totalAdvanced = PettyCashAdvance::find()
            ->where(['status' => ['approved', 'paid']])
            ->sum('amount') ?? 0;

        $pendingAdvance = PettyCashAdvance::find()
            ->where(['status' => 'pending'])
            ->sum('amount') ?? 0;

        // ข้อมูลการใช้จ่าย
        $totalUsed = PettyCashVoucher::find()
            ->sum('amount') ?? 0;

        $thisMonthUsed = PettyCashVoucher::find()
            ->where(['>=', 'date', date('Y-m-01')])
            ->sum('amount') ?? 0;

        // รายการเบิกทดแทนล่าสุด
        $recentAdvances = PettyCashAdvance::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        // รายการใช้จ่ายล่าสุด
        $recentVouchers = PettyCashVoucher::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        // ข้อมูลสำหรับกราฟ
        $monthlyData = $this->getMonthlyData();

        return $this->render('index', [
            'currentBalance' => $currentBalance,
            'maxAmount' => $maxAmount,
            'minAmount' => $minAmount,
            'needsRefill' => $needsRefill,
            'totalAdvanced' => $totalAdvanced,
            'pendingAdvance' => $pendingAdvance,
            'totalUsed' => $totalUsed,
            'thisMonthUsed' => $thisMonthUsed,
            'recentAdvances' => $recentAdvances,
            'recentVouchers' => $recentVouchers,
            'monthlyData' => $monthlyData,
        ]);
    }

    public function actionSummary($from_date = null, $to_date = null)
    {
        if (!$from_date) $from_date = date('Y-m-01');
        if (!$to_date) $to_date = date('Y-m-t');

        // สร้างรายงานสรุป
        $summary = $this->generateSummaryReport($from_date, $to_date);

        return $this->render('summary', [
            'summary' => $summary,
            'from_date' => $from_date,
            'to_date' => $to_date,
        ]);
    }

    private function getMonthlyData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-{$i} months"));
            $month = date('Y-m-01', strtotime("-{$i} months"));
            $nextMonth = date('Y-m-01', strtotime("-" . ($i-1) . " months"));

            $advanced = PettyCashAdvance::find()
                ->where(['>=', 'request_date', $month])
                ->andWhere(['<', 'request_date', $nextMonth])
                ->andWhere(['status' => ['approved', 'paid']])
                ->sum('amount') ?? 0;

            $used = PettyCashVoucher::find()
                ->where(['>=', 'date', $month])
                ->andWhere(['<', 'date', $nextMonth])
                ->sum('amount') ?? 0;

            $data[] = [
                'month' => $date,
                'monthName' => date('M Y', strtotime($date)),
                'advanced' => (float)$advanced,
                'used' => (float)$used,
                'balance' => (float)($advanced - $used),
            ];
        }

        return $data;
    }

    private function generateSummaryReport($from_date, $to_date)
    {
        // ยอดเริ่มต้นของงวด
        $startBalance = PettyCashAdvance::find()
            ->where(['<', 'request_date', $from_date])
            ->andWhere(['status' => ['approved', 'paid']])
            ->sum('amount') ?? 0;

        $startUsed = PettyCashVoucher::find()
            ->where(['<', 'date', $from_date])
            ->sum('amount') ?? 0;

        $openingBalance = $startBalance - $startUsed;

        // รายการในช่วงเวลาที่เลือก
        $periodAdvances = PettyCashAdvance::find()
            ->where(['>=', 'request_date', $from_date])
            ->andWhere(['<=', 'request_date', $to_date])
            ->andWhere(['status' => ['approved', 'paid']])
            ->all();

        $periodVouchers = PettyCashVoucher::find()
            ->where(['>=', 'date', $from_date])
            ->andWhere(['<=', 'date', $to_date])
            ->all();

        $totalAdvanced = array_sum(array_column($periodAdvances, 'amount'));
        $totalUsed = array_sum(array_column($periodVouchers, 'amount'));

        $closingBalance = $openingBalance + $totalAdvanced - $totalUsed;

        return [
            'period' => $from_date . ' ถึง ' . $to_date,
            'openingBalance' => $openingBalance,
            'totalAdvanced' => $totalAdvanced,
            'totalUsed' => $totalUsed,
            'closingBalance' => $closingBalance,
            'advances' => $periodAdvances,
            'vouchers' => $periodVouchers,
        ];
    }
}