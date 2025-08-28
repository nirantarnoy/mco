<?php
namespace backend\controllers;

use backend\models\Invoice;
use backend\models\PettyCashAdvance;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;

class PettyCashAdvanceController extends Controller
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
    public function actionPrint($id)
    {
        $model = $this->findModel($id);

        // ข้อมูลเพิ่มเติมสำหรับการพิมพ์
        $currentBalance = PettyCashAdvance::getCurrentBalance();
        $pettyCashLimit = PettyCashAdvance::MAX_AMOUNT;

        return $this->render('print', [
            'model' => $model,
            'currentBalance' => $currentBalance,
            'pettyCashLimit' => $pettyCashLimit,
            'from_date'=>null,
            'to_date'=>null,
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

}
