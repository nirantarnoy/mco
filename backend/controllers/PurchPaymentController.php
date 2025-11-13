<?php

namespace backend\controllers;

use Yii;
use backend\models\PurchPayment;
use backend\models\PurchPaymentLine;
use backend\models\PurchPaymentSearch;
use backend\models\Purch;
use backend\models\PurchLine;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\helpers\Json;

/**
 * PurchPaymentController implements the CRUD actions for PurchPayment model.
 */
class PurchPaymentController extends Controller
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
     * Lists all PurchPayment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchPaymentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PurchPayment model.
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
     * Creates a new PurchPayment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PurchPayment();
        $paymentLines = [new PurchPaymentLine()];

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $paymentLinesData = Yii::$app->request->post('PurchPaymentLine', []);

                    foreach ($paymentLinesData as $index => $lineData) {
                        $line = new PurchPaymentLine();
                        $line->attributes = $lineData;
                        $line->purch_payment_id = $model->id;

                        $line->doc_file = UploadedFile::getInstance($line, "[{$index}]doc_file");

                        if ($line->doc_file) {
                            $uploadPath = Yii::getAlias('@backend/web/uploads/payment_slips/');
                            if (!is_dir($uploadPath)) {
                                mkdir($uploadPath, 0777, true);
                            }

                            $fileName = time() . '_' . $index . '_' . $line->doc_file->baseName . '.' . $line->doc_file->extension;
                            if ($line->doc_file->saveAs($uploadPath . $fileName)) {
                                $line->doc = 'uploads/payment_slips/' . $fileName;
                            }
                        }

                        if (!$line->save(false)) {
                            $transaction->rollBack();
                            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการบันทึกรายการโอนเงิน');
                            return $this->render('create', [
                                'model' => $model,
                                'paymentLines' => $paymentLines,
                            ]);
                        }
                    }

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'paymentLines' => $paymentLines,
        ]);
    }

    /**
     * Updates an existing PurchPayment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $paymentLines = $model->purchPaymentLines ?: [new PurchPaymentLine()];

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    PurchPaymentLine::deleteAll(['purch_payment_id' => $model->id]);

                    $paymentLinesData = Yii::$app->request->post('PurchPaymentLine', []);

                    foreach ($paymentLinesData as $index => $lineData) {
                        $line = new PurchPaymentLine();
                        $line->attributes = $lineData;
                        $line->purch_payment_id = $model->id;

                        $line->doc_file = UploadedFile::getInstance($line, "[{$index}]doc_file");

                        if ($line->doc_file) {
                            $uploadPath = Yii::getAlias('@backend/web/uploads/payment_slips/');
                            if (!is_dir($uploadPath)) {
                                mkdir($uploadPath, 0777, true);
                            }

                            $fileName = time() . '_' . $index . '_' . $line->doc_file->baseName . '.' . $line->doc_file->extension;
                            if ($line->doc_file->saveAs($uploadPath . $fileName)) {
                                $line->doc = $fileName;
                            }
                        } elseif (!empty($lineData['doc'])) {
                            $line->doc = $lineData['doc'];
                        }

                        if (!$line->save(false)) {
                            $transaction->rollBack();
                            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการบันทึกรายการโอนเงิน');
                            return $this->render('update', [
                                'model' => $model,
                                'paymentLines' => $paymentLines,
                            ]);
                        }
                    }

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'paymentLines' => $paymentLines,
        ]);
    }

    /**
     * Deletes an existing PurchPayment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($model->purchPaymentLines as $line) {
                if ($line->doc && file_exists(Yii::getAlias('@backend/web/' . $line->doc))) {
                    unlink(Yii::getAlias('@backend/web/' . $line->doc));
                }
            }

            PurchPaymentLine::deleteAll(['purch_payment_id' => $id]);
            $model->delete();

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการลบข้อมูล');
        }

        return $this->redirect(['index']);
    }

    /**
     * Get Purch Lines by Purch ID via AJAX
     * @return mixed
     */
    public function actionGetPurchLines()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $purchId = Yii::$app->request->post('purch_id');

        if (!$purchId) {
            return ['success' => false, 'message' => 'ไม่พบข้อมูลใบสั่งซื้อ'];
        }

        $purch = Purch::findOne($purchId);
        if (!$purch) {
            return ['success' => false, 'message' => 'ไม่พบข้อมูลใบสั่งซื้อ'];
        }

        $lines = PurchLine::find()
            ->where(['purch_id' => $purchId])
            ->asArray()
            ->all();

        return [
            'success' => true,
            'purch' => [
                'purch_no' => $purch->purch_no,
                'vendor_name' => $purch->vendor_name,
                'purch_date' => $purch->purch_date,
                'total_amount' => $purch->total_amount,
                'net_amount' => $purch->net_amount,
            ],
            'lines' => $lines,
        ];
    }

    /**
     * Finds the PurchPayment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PurchPayment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchPayment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}