<?php
namespace backend\controllers;

use Yii;
use backend\models\PurchReq;
use backend\models\PurchReqSearch;
use backend\models\PurchReqLine;
use backend\models\Product;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * PurchReqController implements the CRUD actions for PurchReq model.
 */
class PurchreqController extends Controller
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
     * Lists all PurchReq models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchReqSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PurchReq model.
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
     * Creates a new PurchReq model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PurchReq();
        $model->status = PurchReq::STATUS_DRAFT;
        $model->approve_status = PurchReq::APPROVE_STATUS_PENDING;
        $model->purch_req_date = date('Y-m-d');

        // Initialize with one empty purch req line
        $model->purchReqLines = [new PurchReqLine()];

        if ($model->load(Yii::$app->request->post())) {
            $purchReqLines = [];
            $valid = $model->validate();

            if (isset($_POST['PurchReqLine'])) {
                foreach ($_POST['PurchReqLine'] as $index => $purchReqLineData) {
                    $purchReqLine = new PurchReqLine();
                    $purchReqLine->load($purchReqLineData, '');
                    $purchReqLines[] = $purchReqLine;
                    $valid = $purchReqLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // Save purch req lines
                        foreach ($purchReqLines as $purchReqLine) {
                            $purchReqLine->purch_req_id = $model->id;
                            if (!$purchReqLine->save()) {
                                throw new \Exception('Failed to save purch req line');
                            }
                        }
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'สร้างใบขอซื้อเรียบร้อยแล้ว');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PurchReq model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Load existing purch req lines
        $model->purchReqLines = $model->getPurchReqLines()->all();
        if (empty($model->purchReqLines)) {
            $model->purchReqLines = [new PurchReqLine()];
        }

        if ($model->load(Yii::$app->request->post())) {
            $purchReqLines = [];
            $valid = $model->validate();

            if (isset($_POST['PurchReqLine'])) {
                foreach ($_POST['PurchReqLine'] as $index => $purchReqLineData) {
                    if (isset($purchReqLineData['id']) && !empty($purchReqLineData['id'])) {
                        // Update existing line
                        $purchReqLine = PurchReqLine::findOne($purchReqLineData['id']);
                        if (!$purchReqLine) {
                            $purchReqLine = new PurchReqLine();
                        }
                    } else {
                        // Create new line
                        $purchReqLine = new PurchReqLine();
                    }
                    $purchReqLine->load($purchReqLineData, '');
                    $purchReqLines[] = $purchReqLine;
                    $valid = $purchReqLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // Delete existing lines that are not in the new list
                        $existingLineIds = [];
                        foreach ($purchReqLines as $purchReqLine) {
                            if (!$purchReqLine->isNewRecord) {
                                $existingLineIds[] = $purchReqLine->id;
                            }
                        }

                        PurchReqLine::deleteAll([
                            'and',
                            ['purch_req_id' => $model->id],
                            ['not in', 'id', $existingLineIds]
                        ]);

                        // Save purch req lines
                        foreach ($purchReqLines as $purchReqLine) {
                            $purchReqLine->purch_req_id = $model->id;
                            if (!$purchReqLine->save()) {
                                throw new \Exception('Failed to save purch req line');
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
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PurchReq model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Delete all purch req lines first
            PurchReqLine::deleteAll(['purch_req_id' => $id]);

            // Delete the purch req record
            $model->delete();

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'ลบข้อมูลเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบข้อมูลได้: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Get product info for AJAX
     */
    public function actionGetProductInfo($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return Product::getProductInfo($id);
    }

    /**
     * Approve purchase request
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = PurchReq::APPROVE_STATUS_APPROVED;
        $model->status = PurchReq::STATUS_ACTIVE;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'อนุมัติใบขอซื้อเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถอนุมัติใบขอซื้อได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Reject purchase request
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionReject($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = PurchReq::APPROVE_STATUS_REJECTED;
        $model->status = PurchReq::STATUS_CANCELLED;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'ไม่อนุมัติใบขอซื้อเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถปฏิเสธใบขอซื้อได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the PurchReq model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PurchReq the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchReq::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}