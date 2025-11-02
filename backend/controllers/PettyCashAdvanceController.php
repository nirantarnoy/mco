<?php
namespace backend\controllers;

use backend\models\Employee;
use backend\models\Invoice;
use backend\models\PettyCashAdvance;
use backend\models\PettyCashDetail;
use backend\models\PettyCashVoucher;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\Response;

class PettyCashAdvanceController extends Controller
{
    public $enableCsrfValidation = false;
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
                    'delete' => ['POST','GET'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => PettyCashAdvance::find()->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $dataProvider->query->andFilterWhere(['company_id'=> \Yii::$app->session->get('company_id')]);

        $currentBalance = PettyCashAdvance::getCurrentBalance();
        $needsRefill = PettyCashAdvance::needsRefill();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'currentBalance' => $currentBalance,
            'needsRefill' => $needsRefill,
        ]);
    }

    public function actionCreate()
    {
        $model = new PettyCashAdvance();
        $model->status = PettyCashAdvance::STATUS_PENDING;
        $model->employee_id = \Yii::$app->user->identity->employee_id ?? null;
        $model->request_date = date('Y-m-d');

        if ($model->load(\Yii::$app->request->post())) {
            // ตรวจสอบว่าสามารถเบิกได้หรือไม่
            if (!PettyCashAdvance::canRequestAdvance($model->amount)) {
                $currentBalance = PettyCashAdvance::getCurrentBalance();
                $maxRequest = PettyCashAdvance::MAX_AMOUNT - $currentBalance;
                \Yii::$app->session->setFlash('error',
                    "ไม่สามารถเบิกเงินได้ เนื่องจากจะเกินวงเงินสูงสุด {PettyCashAdvance::MAX_AMOUNT} บาท
                    <br>ยอดคงเหลือปัจจุบัน: " . number_format($currentBalance, 2) . " บาท
                    <br>จำนวนที่เบิกได้สูงสุด: " . number_format($maxRequest, 2) . " บาท");
            } else {
                $model->advance_no = PettyCashAdvance::generateAdvanceNo();

                if ($model->save()) {
                    \Yii::$app->session->setFlash('success', 'บันทึกใบเบิกเงินทดแทนเรียบร้อยแล้ว');
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    \Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== PettyCashAdvance::STATUS_PENDING) {
            \Yii::$app->session->setFlash('error', 'ไม่สามารถแก้ไขได้ เนื่องจากสถานะไม่อนุญาต');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionApprove($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== PettyCashAdvance::STATUS_PENDING) {
            \Yii::$app->session->setFlash('error', 'สถานะไม่ถูกต้อง');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->status = PettyCashAdvance::STATUS_APPROVED;
        $model->approved_by = \Yii::$app->user->identity->employee_id ?? null;

        if ($model->save()) {
            \Yii::$app->session->setFlash('success', 'อนุมัติใบเบิกเงินทดแทนเรียบร้อยแล้ว');
        } else {
            \Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการอนุมัติ');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionReject($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== PettyCashAdvance::STATUS_PENDING) {
            \Yii::$app->session->setFlash('error', 'สถานะไม่ถูกต้อง');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->status = PettyCashAdvance::STATUS_REJECTED;
        $model->approved_by = \Yii::$app->user->identity->employee_id ?? null;

        if ($model->save()) {
            \Yii::$app->session->setFlash('success', 'ปฏิเสธใบเบิกเงินทดแทนเรียบร้อยแล้ว');
        } else {
            \Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการปฏิเสธ');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionBalanceCheck()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'currentBalance' => PettyCashAdvance::getCurrentBalance(),
            'maxAmount' => PettyCashAdvance::MAX_AMOUNT,
            'minAmount' => PettyCashAdvance::MIN_AMOUNT,
            'needsRefill' => PettyCashAdvance::needsRefill(),
        ];
    }

    protected function findModel($id)
    {
        if (($model = PettyCashAdvance::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * พิมพ์ใบสรุปการเบิกชดเชยเงินสดย่อย
     */
    public function actionPrintSummary($from_date = null, $to_date = null)
    {
        if (!$from_date) $from_date = date('Y-m-01');
        if (!$to_date) $to_date = date('Y-m-t');

        // ดึงข้อมูลการเบิกชดเชยในช่วงเวลาที่กำหนด
        $advances = PettyCashAdvance::find()
            ->where(['>=', 'request_date', $from_date])
            ->andWhere(['<=', 'request_date', $to_date])
            ->andWhere(['status' => ['approved', 'paid']])
            ->orderBy(['request_date' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        // คำนวณยอดต่างๆ
        $totalAdvanceAmount = array_sum(array_column($advances, 'amount'));
        $totalUsedAmount = PettyCashVoucher::find()
            ->where(['<=', 'date', $to_date])
            ->sum('amount') ?? 0;

        // ยอดคงเหลือปัจจุบัน
        $currentBalance = $this->getCurrentBalance();

        // วงเงินสดย่อย
        $pettyCashLimit = PettyCashAdvance::MAX_AMOUNT;

        // เงินสดย่อยเบิกเกิน (ถ้ามี)
        $overAdvance = max(0, $totalAdvanceAmount - $totalUsedAmount - $pettyCashLimit);

        return $this->render('print-summary', [
            'advances' => $advances,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'totalAdvanceAmount' => $totalAdvanceAmount,
            'currentBalance' => $currentBalance,
            'pettyCashLimit' => $pettyCashLimit,
            'overAdvance' => $overAdvance,
        ]);
    }

    /**
     * พิมพ์ใบเบิกเงินทดแทนแต่ละใบ
     */
//    public function actionPrint($id)
//    {
//        $model = $this->findModel($id);
//
//        // ข้อมูลเพิ่มเติมสำหรับการพิมพ์
//        $currentBalance = PettyCashAdvance::getCurrentBalance();
//        $pettyCashLimit = PettyCashAdvance::MAX_AMOUNT;
//
//        return $this->render('print', [
//            'model' => $model,
//            'currentBalance' => $currentBalance,
//            'pettyCashLimit' => $pettyCashLimit,
//            'from_date'=>null,
//            'to_date'=>null,
//            'reportData' => null,
//        ]);
//    }

    public function actionPrint($id = null, $from_date = null, $to_date = null)
    {
        // ถ้ามี id แสดงว่าต้องการพิมพ์เฉพาะใบนั้น
        if ($id !== null) {
            $model = $this->findModel($id);

            // ใช้วันที่ของใบเบิกเป็นช่วงเวลา
            $from_date = date('Y-m-d', strtotime($model->request_date . ' -7 days'));
            $to_date = date('Y-m-d', strtotime($model->request_date . ' +7 days'));

            // ดึงข้อมูลการเบิกในช่วงเวลาใกล้เคียง (สำหรับแสดงในรายงาน)
            $advances = PettyCashAdvance::find()
                ->where(['>=', 'request_date', $from_date])
                ->andWhere(['<=', 'request_date', $to_date])
                ->andWhere(['status' => [PettyCashAdvance::STATUS_APPROVED]])
                ->andFilterWhere(['company_id' => \Yii::$app->session->get('company_id')])
                ->orderBy(['request_date' => SORT_ASC, 'id' => SORT_ASC])
                ->all();
        } else {
            // ถ้าไม่มี id ให้ใช้ช่วงเวลาที่กำหนด
            if (!$from_date) $from_date = date('Y-m-01'); // วันแรกของเดือน
            if (!$to_date) $to_date = date('Y-m-t'); // วันสุดท้ายของเดือน

            // ดึงข้อมูลการเบิกในช่วงเวลาที่กำหนด
            $advances = PettyCashAdvance::find()
                ->where(['>=', 'request_date', $from_date])
                ->andWhere(['<=', 'request_date', $to_date])
                ->andWhere(['status' => [PettyCashAdvance::STATUS_APPROVED]])
                ->andFilterWhere(['company_id' => \Yii::$app->session->get('company_id')])
                ->orderBy(['request_date' => SORT_ASC, 'id' => SORT_ASC])
                ->all();
        }

        // คำนวณยอดคงเหลือปัจจุบัน
        $currentBalance = PettyCashAdvance::getCurrentBalance();

        // วงเงินสดย่อย
        $pettyCashLimit = PettyCashAdvance::MAX_AMOUNT;

        // ปิด layout สำหรับการพิมพ์
        $this->layout = false;

        return $this->render('print', [
            'model' => $id !== null ? $model : null,
            'advances' => $advances,
            'currentBalance' => $currentBalance,
            'pettyCashLimit' => $pettyCashLimit,
            'from_date' => $from_date,
            'to_date' => $to_date,
        ]);
    }

    /**
     * Export ใบสรุปเป็น Excel
     */
    public function actionExportSummaryExcel($from_date = null, $to_date = null)
    {
        if (!$from_date) $from_date = date('Y-m-01');
        if (!$to_date) $to_date = date('Y-m-t');

        $advances = PettyCashAdvance::find()
            ->where(['>=', 'request_date', $from_date])
            ->andWhere(['<=', 'request_date', $to_date])
            ->andWhere(['status' => ['approved', 'paid']])
            ->orderBy(['request_date' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        // คำนวณยอดต่างๆ
        $totalAdvanceAmount = array_sum(array_column($advances, 'amount'));
        $currentBalance = PettyCashAdvance::getCurrentBalance();
        $pettyCashLimit = PettyCashAdvance::MAX_AMOUNT;

        // สร้าง Excel data
        $data = [];

        // Header
        $data[] = ['บริษัท เอ็ม.ซี.โอ. จำกัด'];
        $data[] = ['ใบสรุปการเบิกชดเชยเงินสดย่อย'];
        $data[] = ['ประจำวันที่ ' . date('d/m/Y', strtotime($from_date)) . ' ถึง ' . date('d/m/Y', strtotime($to_date))];
        $data[] = ['F-WP-FMA-004-002 Rev.N'];
        $data[] = []; // Empty row

        // Summary info
        $data[] = ['วงเงินสดย่อย:', number_format($pettyCashLimit, 2) . ' บาท'];
        $data[] = ['เงินสดย่อยคงเหลือ:', number_format($currentBalance, 2) . ' บาท'];
        $data[] = ['เบิกชดเชยเงินสดย่อย:', number_format($totalAdvanceAmount, 2) . ' บาท'];
        $data[] = []; // Empty row

        // Table header
        $data[] = ['ลำดับ', 'ว.ด.ป.', 'วันที่รายงาน', 'เลขที่เบิก', 'รายการ', 'จำนวนเงิน', 'หมายเหตุ'];

        // Data rows
        foreach ($advances as $index => $advance) {
            $data[] = [
                $index + 1,
                date('d/m/Y', strtotime($advance->request_date)),
                date('d/m/Y', strtotime($advance->created_at ? date('Y-m-d', $advance->created_at) : $advance->request_date)),
                $advance->advance_no,
                $advance->purpose,
                $advance->amount,
                $advance->status === 'paid' ? 'จ่ายแล้ว' : 'อนุมัติ'
            ];
        }

        // Total row
        $data[] = ['', '', '', '', 'รวม', $totalAdvanceAmount, ''];

        // Create Excel file
        require_once \Yii::getAlias('@vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');

        $filename = 'ใบสรุปการเบิกชดเชยเงินสดย่อย_' . date('Y-m-d', strtotime($from_date)) . '_' . date('Y-m-d', strtotime($to_date)) . '.xlsx';

        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);
        $xlsx->downloadAs($filename);

        return;
    }

    /**
     * พิมพ์รายการเบิกตามพนักงาน
     */
    public function actionPrintByEmployee($employee_id, $from_date = null, $to_date = null)
    {
        if (!$from_date) $from_date = date('Y-m-01');
        if (!$to_date) $to_date = date('Y-m-t');

        $employee = Employee::findOne($employee_id);
        if (!$employee) {
            throw new NotFoundHttpException('ไม่พบข้อมูลพนักงาน');
        }

        $advances = PettyCashAdvance::find()
            ->where(['employee_id' => $employee_id])
            ->andWhere(['>=', 'request_date', $from_date])
            ->andWhere(['<=', 'request_date', $to_date])
            ->orderBy(['request_date' => SORT_ASC])
            ->all();

        return $this->render('print-by-employee', [
            'employee' => $employee,
            'advances' => $advances,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'totalAmount' => array_sum(array_column($advances, 'amount')),
        ]);
    }

    /**
     * API สำหรับดึงข้อมูลพิมพ์
     */
    public function actionPrintData($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = $this->findModel($id);

        return [
            'success' => true,
            'data' => [
                'advance_no' => $model->advance_no,
                'request_date' => $model->request_date,
                'employee_name' => $model->employee ? $model->employee->fname . ' ' . $model->employee->lname : '',
                'amount' => $model->amount,
                'purpose' => $model->purpose,
                'status' => $model->status,
                'approver_name' => $model->approver ? $model->approver->fname . ' ' . $model->approver->lname : '',
                'created_date' => date('Y-m-d H:i:s', $model->created_at),
            ],
            'print_url' => \yii\helpers\Url::to(['print', 'id' => $id], true),
        ];
    }

    public function actionPrintPetty()
    {
       // echo "ok";return;
        $request = Yii::$app->request;

        // Get filter parameters
        $dateFrom = $request->post('date_from');
        $dateTo = $request->post('date_to');
        $documentNo = $request->post('document_no');

        // Query for advances (รายรับ)
        $advanceQuery = PettyCashAdvance::find()
            ->select([
                'id',
                'advance_no',
                'request_date',
                'amount',
                'purpose',
                'status'
            ])
            ->orderBy(['request_date' => SORT_ASC, 'id' => SORT_ASC]);

        // Query for vouchers with details (รายจ่าย)
        $voucherQuery = PettyCashVoucher::find()
            ->alias('v')
            ->select([
                'v.id',
                'v.pcv_no',
                'v.date',
                'v.name',
                'v.status',
                'd.amount',
                'd.ac_code',
                'd.detail',
                'd.vat',
                'd.vat_amount',
                'd.wht',
                'd.other'
            ])
            ->innerJoin(['d' => PettyCashDetail::tableName()], 'd.voucher_id = v.id')
            ->orderBy(['v.date' => SORT_ASC, 'v.id' => SORT_ASC, 'd.id' => SORT_ASC]);

        // Apply date filters
        if (!empty($dateFrom)) {
            $advanceQuery->andWhere(['>=', 'request_date', $dateFrom]);
            $voucherQuery->andWhere(['>=', 'v.date', $dateFrom]);
        }

        if (!empty($dateTo)) {
            $advanceQuery->andWhere(['<=', 'request_date', $dateTo]);
            $voucherQuery->andWhere(['<=', 'v.date', $dateTo]);
        }

        // Apply document number filter
        if (!empty($documentNo)) {
            $advanceQuery->andWhere(['like', 'advance_no', $documentNo]);
            $voucherQuery->andWhere(['like', 'v.pcv_no', $documentNo]);
        }

        // Get data
        $advances = $advanceQuery->asArray()->all();
        $vouchers = $voucherQuery->asArray()->all();

        // Combine and sort all transactions by date
        $allTransactions = [];

        foreach ($advances as $advance) {
            $allTransactions[] = [
                'date' => $advance['request_date'],
                'document_no' => $advance['advance_no'],
                'description' => $advance['purpose'],
                'type' => 'advance',
                'income' => $advance['amount'],
                'expense' => 0,
                'vat' => 0,
                'vat_amount' => 0,
                'wht' => 0,
                'other' => 0,
                'total_expense' => 0,
            ];
        }

        foreach ($vouchers as $voucher) {
            $totalExpense = $voucher['amount'] + $voucher['vat_amount'] + $voucher['wht'] + $voucher['other'];

            $allTransactions[] = [
                'date' => $voucher['date'],
                'document_no' => $voucher['pcv_no'],
                'description' => $voucher['name'] . (!empty($voucher['detail']) ? ' - ' . $voucher['detail'] : ''),
                'type' => 'voucher',
                'income' => 0,
                'expense' => $voucher['amount'],
                'vat' => $voucher['vat'],
                'vat_amount' => $voucher['vat_amount'],
                'wht' => $voucher['wht'],
                'other' => $voucher['other'],
                'total_expense' => $totalExpense,
            ];
        }

        // Sort by date
        usort($allTransactions, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        // Calculate running balance
        $balance = 0;
        foreach ($allTransactions as &$transaction) {
            $balance += $transaction['income'];
            $balance -= $transaction['total_expense'];
            $transaction['balance'] = $balance;
        }

        // Calculate totals
        $totalIncome = array_sum(array_column($allTransactions, 'income'));
        $totalExpense = array_sum(array_column($allTransactions, 'expense'));
        $totalVat = array_sum(array_column($allTransactions, 'vat_amount'));
        $totalWht = array_sum(array_column($allTransactions, 'wht'));
        $totalOther = array_sum(array_column($allTransactions, 'other'));
        $totalAllExpenses = array_sum(array_column($allTransactions, 'total_expense'));
        $finalBalance = $balance;

        // Check if export to Excel is requested
        if ($request->get('export') === 'excel') {
            return $this->exportToExcel($allTransactions, [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'totalVat' => $totalVat,
                'totalWht' => $totalWht,
                'totalOther' => $totalOther,
                'totalAllExpenses' => $totalAllExpenses,
                'finalBalance' => $finalBalance,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ]);
        }

        return $this->render('_print_petty', [
            'transactions' => $allTransactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'totalVat' => $totalVat,
            'totalWht' => $totalWht,
            'totalOther' => $totalOther,
            'totalAllExpenses' => $totalAllExpenses,
            'finalBalance' => $finalBalance,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'documentNo' => $documentNo,
        ]);
    }

    /**
     * Export report to Excel
     */
    protected function exportToExcel($transactions, $totals)
    {
        Yii::$app->response->format = Response::FORMAT_RAW;

        $filename = 'petty_cash_report_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Create Excel file using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'รายงานเงินสดย่อย M.C.O.CO.,LTD');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        if (!empty($totals['dateFrom']) || !empty($totals['dateTo'])) {
            $dateRange = 'ระหว่างวันที่ ';
            if (!empty($totals['dateFrom'])) {
                $dateRange .= date('d/m/Y', strtotime($totals['dateFrom']));
            }
            if (!empty($totals['dateTo'])) {
                $dateRange .= ' ถึง ' . date('d/m/Y', strtotime($totals['dateTo']));
            }
            $sheet->setCellValue('A2', $dateRange);
            $sheet->mergeCells('A2:I2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $startRow = 4;
        } else {
            $startRow = 3;
        }

        // Column headers
        $headers = ['วันที่', 'เลขที่เอกสาร', 'รายการ', 'รายรับ', 'คชจ.', 'VAT', 'VAT ต้องหาม', 'W/H', 'อื่นๆ', 'รวมรายจ่าย', 'คงเหลือ'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $startRow, $header);
            $sheet->getStyle($col . $startRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $startRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $col++;
        }

        // Data rows
        $row = $startRow + 1;
        foreach ($transactions as $transaction) {
            $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($transaction['date'])));
            $sheet->setCellValue('B' . $row, $transaction['document_no']);
            $sheet->setCellValue('C' . $row, $transaction['description']);
            $sheet->setCellValue('D' . $row, $transaction['income'] > 0 ? $transaction['income'] : '');
            $sheet->setCellValue('E' . $row, $transaction['expense'] > 0 ? $transaction['expense'] : '');
            $sheet->setCellValue('F' . $row, $transaction['vat_amount'] > 0 ? $transaction['vat_amount'] : '');
            $sheet->setCellValue('G' . $row, ''); // VAT ต้องหาม - add logic if needed
            $sheet->setCellValue('H' . $row, $transaction['wht'] > 0 ? $transaction['wht'] : '');
            $sheet->setCellValue('I' . $row, $transaction['other'] > 0 ? $transaction['other'] : '');
            $sheet->setCellValue('J' . $row, $transaction['total_expense'] > 0 ? $transaction['total_expense'] : '');
            $sheet->setCellValue('K' . $row, $transaction['balance']);

            // Number format
            $sheet->getStyle('D' . $row . ':K' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');

            $row++;
        }

        // Total row
        $sheet->setCellValue('C' . $row, 'รวม');
        $sheet->setCellValue('D' . $row, $totals['totalIncome']);
        $sheet->setCellValue('E' . $row, $totals['totalExpense']);
        $sheet->setCellValue('F' . $row, $totals['totalVat']);
        $sheet->setCellValue('H' . $row, $totals['totalWht']);
        $sheet->setCellValue('I' . $row, $totals['totalOther']);
        $sheet->setCellValue('J' . $row, $totals['totalAllExpenses']);
        $sheet->setCellValue('K' . $row, $totals['finalBalance']);

        $sheet->getStyle('C' . $row . ':K' . $row)->getFont()->setBold(true);
        $sheet->getStyle('D' . $row . ':K' . $row)->getNumberFormat()
            ->setFormatCode('#,##0.00');

        // Auto-size columns
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A' . $startRow . ':K' . $row)->applyFromArray($styleArray);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

}
