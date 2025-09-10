<?php
// backend/controllers/BillingInvoiceController.php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Json;
use backend\models\BillingInvoice;
use backend\models\Customer;
use backend\models\Invoice;
use backend\models\BillingInvoiceSearch;

class BillingInvoiceController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new BillingInvoiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new BillingInvoice();
        $model->billing_number = BillingInvoice::generateBillingNumber();
        $model->billing_date = date('Y-m-d');
        $model->credit_terms = 30;
        $model->vat_percent = 7.00;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'สร้างใบวางบิลเรียบร้อยแล้ว');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'แก้ไขใบวางบิลเรียบร้อยแล้ว');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Unmark invoices as billed
        foreach ($model->billingInvoiceItems as $item) {
            $invoice = $item->invoice;
            $invoice->is_billed = 0;
            $invoice->status = 1;
            $invoice->save();
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบใบวางบิลเรียบร้อยแล้ว');

        return $this->redirect(['index']);
    }

    public function actionGetUnbilledInvoices()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $customerId = Yii::$app->request->post('customer_id');
        if (!$customerId) {
            return ['success' => false, 'message' => 'ไม่พบรหัสลูกค้า'];
        }

        $invoices = Invoice::getUnbilledInvoices($customerId);
        $data = [];

        foreach ($invoices as $invoice) {
            $data[] = [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => date('m/d/Y', strtotime($invoice->invoice_date)),
                'total_amount' => $invoice->total_amount,
                'total_amount_raw' => $invoice->total_amount,
                'invoice_type' => $invoice->invoice_type == 'tax_invoice' ? 'ใบกำกับภาษี' : 'ใบแจ้งหนี้',
            ];
        }

        return ['success' => true, 'data' => $data];
    }

    public function actionPrint($id)
    {
        $model = $this->findModel($id);

        // Set layout for printing
       // $this->layout = 'print';

        return $this->render('print', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = BillingInvoice::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}