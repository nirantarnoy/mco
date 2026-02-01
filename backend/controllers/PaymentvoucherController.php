<?php

namespace backend\controllers;

use Yii;
use backend\models\PaymentVoucher;
use backend\models\PaymentVoucherSearch;
use backend\models\PaymentVoucherLine;
use backend\models\PurchReq;
use backend\models\Purch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * PaymentVoucherController implements the CRUD actions for PaymentVoucher model.
 */
class PaymentvoucherController extends BaseController
{
    /**
     * {@inheritdoc}
     */
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

    /**
     * Lists all PaymentVoucher models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PaymentVoucherSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PaymentVoucher model.
     * @param int $id ID
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
     * Creates a new PaymentVoucher model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PaymentVoucher();
        $model->trans_date = date('Y-m-d');
        $model->status = PaymentVoucher::STATUS_DRAFT;

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $line_account_codes = Yii::$app->request->post('line_account_code');
                    $line_bill_codes = Yii::$app->request->post('line_bill_code');
                    $line_descriptions = Yii::$app->request->post('line_description');
                    $line_debits = Yii::$app->request->post('line_debit');
                    $line_credits = Yii::$app->request->post('line_credit');

                    if ($line_descriptions) {
                        for ($i = 0; $i < count($line_descriptions); $i++) {
                            if (empty($line_descriptions[$i])) continue;
                            $line = new PaymentVoucherLine();
                            $line->payment_voucher_id = $model->id;
                            $line->account_code = $line_account_codes[$i] ?? '';
                            $line->bill_code = $line_bill_codes[$i] ?? '';
                            $line->description = $line_descriptions[$i];
                            $line->debit = $line_debits[$i] ?? 0;
                            $line->credit = $line_credits[$i] ?? 0;
                            if (!$line->save()) {
                                throw new \Exception('Failed to save line');
                            }
                        }
                    }
                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PaymentVoucher model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    PaymentVoucherLine::deleteAll(['payment_voucher_id' => $model->id]);
                    
                    $line_account_codes = Yii::$app->request->post('line_account_code');
                    $line_bill_codes = Yii::$app->request->post('line_bill_code');
                    $line_descriptions = Yii::$app->request->post('line_description');
                    $line_debits = Yii::$app->request->post('line_debit');
                    $line_credits = Yii::$app->request->post('line_credit');

                    if ($line_descriptions) {
                        for ($i = 0; $i < count($line_descriptions); $i++) {
                            if (empty($line_descriptions[$i])) continue;
                            $line = new PaymentVoucherLine();
                            $line->payment_voucher_id = $model->id;
                            $line->account_code = $line_account_codes[$i] ?? '';
                            $line->bill_code = $line_bill_codes[$i] ?? '';
                            $line->description = $line_descriptions[$i];
                            $line->debit = $line_debits[$i] ?? 0;
                            $line->credit = $line_credits[$i] ?? 0;
                            if (!$line->save()) {
                                throw new \Exception('Failed to save line');
                            }
                        }
                    }
                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PaymentVoucher model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Pull PR data
     */
    public function actionPullPr($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $pr = PurchReq::findOne($id);
        if ($pr) {
            $lines = [];
            foreach ($pr->getPurchReqLines()->all() as $line) {
                $lines[] = [
                    'account_code' => '',
                    'bill_code' => $pr->purch_req_no,
                    'description' => $line->product_name . ($line->product_description ? ' (' . $line->product_description . ')' : ''),
                    'debit' => $line->line_total,
                    'credit' => 0,
                ];
            }
            return [
                'success' => true,
                'recipient_name' => $pr->vendor_name,
                'amount' => $pr->net_amount,
                'paid_for' => $pr->purch_req_no,
                'lines' => $lines,
            ];
        }
        return ['success' => false];
    }

    /**
     * Pull PO data
     */
    public function actionPullPo($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $po = Purch::findOne($id);
        if ($po) {
            $lines = [];
            foreach ($po->getPurchLines()->all() as $line) {
                $lines[] = [
                    'account_code' => '',
                    'bill_code' => $po->purch_no,
                    'description' => $line->product_name . ($line->product_description ? ' (' . $line->product_description . ')' : ''),
                    'debit' => $line->line_total,
                    'credit' => 0,
                ];
            }
            return [
                'success' => true,
                'recipient_name' => $po->vendor_name,
                'amount' => $po->net_amount,
                'paid_for' => $po->purch_no,
                'lines' => $lines,
            ];
        }
        return ['success' => false];
    }

    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $this->layout = false;
        return $this->render('print', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the PaymentVoucher model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PaymentVoucher the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PaymentVoucher::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
