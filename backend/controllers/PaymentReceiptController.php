<?php

namespace backend\controllers;

use Yii;
use backend\models\PaymentReceipt;
use backend\models\PaymentReceiptSearch;
use backend\models\BillingInvoice;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Json;

/**
 * PaymentReceiptController implements the CRUD actions for PaymentReceipt model.
 */
class PaymentReceiptController extends Controller
{
    /**
     * {@inheritdoc}
     */
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
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all PaymentReceipt models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PaymentReceiptSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PaymentReceipt model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new PaymentReceipt model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($invoice_id = null)
    {
        $model = new PaymentReceipt();

        // Pre-fill billing invoice if provided
        if ($invoice_id) {
            $billingInvoice = BillingInvoice::findOne($invoice_id);
            if ($billingInvoice) {
                $model->billing_invoice_id = $invoice_id;
                $model->job_id = $billingInvoice->job_id ?? null;

                // Calculate remaining balance
                $totalPaid = PaymentReceipt::find()
                    ->where(['billing_invoice_id' => $invoice_id])
                    ->sum('net_amount') ?? 0;
                $model->remaining_balance = $billingInvoice->total_amount - $totalPaid;
                $model->received_amount = $model->remaining_balance;
            }
        }

        $model->payment_date = date('Y-m-d');
        $model->received_by = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post())) {
            $model->attachment_file = UploadedFile::getInstance($model, 'attachment_file');

            if ($model->upload() && $model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกการรับเงินเรียบร้อยแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PaymentReceipt model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldAttachment = $model->attachment_path;

        if ($model->load(Yii::$app->request->post())) {
            $model->attachment_file = UploadedFile::getInstance($model, 'attachment_file');

            if ($model->attachment_file) {
                // Delete old file if exists
                if ($oldAttachment && file_exists(Yii::getAlias('@backend/web/') . $oldAttachment)) {
                    unlink(Yii::getAlias('@backend/web/') . $oldAttachment);
                }
            } else {
                // Keep old attachment if no new file uploaded
                $model->attachment_path = $oldAttachment;
            }

            if ($model->upload() && $model->save()) {
                Yii::$app->session->setFlash('success', 'แก้ไขการรับเงินเรียบร้อยแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PaymentReceipt model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Delete attachment file
        if ($model->attachment_path && file_exists(Yii::getAlias('@backend/web/') . $model->attachment_path)) {
            unlink(Yii::getAlias('@backend/web/') . $model->attachment_path);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบการรับเงินเรียบร้อยแล้ว');

        return $this->redirect(['index']);
    }

    /**
     * Get billing invoice info via AJAX
     */
    public function actionGetInvoiceInfo($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $invoice = BillingInvoice::find()
            ->with(['billingInvoiceItems', 'customer'])
            ->where(['id' => $id])
            ->one();

        if (!$invoice) {
            return ['success' => false, 'message' => 'ไม่พบใบแจ้งหนี้'];
        }

        // Calculate paid amount
        $totalPaid = PaymentReceipt::find()
            ->where(['billing_invoice_id' => $id])
            ->sum('net_amount') ?? 0;

        $remainingBalance = $invoice->total_amount - $totalPaid;

        return [
            'success' => true,
            'data' => [
                'invoice_number' => $invoice->billing_number,
                'customer_name' => $invoice->customer->name ?? '',
                'total_amount' => $invoice->total_amount,
                'paid_amount' => $totalPaid,
                'remaining_balance' => $remainingBalance,
                'job_id' => 0,// $invoice->job_id,
                'vat_amount' => $invoice->vat_amount,
            ]
        ];
    }

    /**
     * Print receipt
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);

        return $this->render('print', [
            'model' => $model,
        ]);
    }

    /**
     * Download attachment
     */
    public function actionDownload($id)
    {
        $model = $this->findModel($id);

        if ($model->attachment_path) {
            $filePath = Yii::getAlias('@backend/web/') . $model->attachment_path;

            if (file_exists($filePath)) {
                return Yii::$app->response->sendFile($filePath, $model->attachment_name);
            }
        }

        throw new NotFoundHttpException('ไม่พบไฟล์ที่ต้องการ');
    }

    /**
     * Get payment summary by job
     */
    public function actionSummaryByJob($job_id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $payments = PaymentReceipt::find()
            ->with(['billingInvoice'])
            ->where(['job_id' => $job_id])
            ->all();

        $totalReceived = 0;
        $paymentList = [];

        foreach ($payments as $payment) {
            $totalReceived += $payment->net_amount;
            $paymentList[] = [
                'id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'payment_date' => $payment->payment_date,
                'amount' => $payment->net_amount,
                'method' => PaymentReceipt::getPaymentMethods()[$payment->payment_method] ?? $payment->payment_method,
                'invoice_number' => $payment->billingInvoice->billing_number ?? '',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'total_received' => $totalReceived,
                'payment_count' => count($payments),
                'payments' => $paymentList,
            ]
        ];
    }

    /**
     * Finds the PaymentReceipt model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PaymentReceipt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PaymentReceipt::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}