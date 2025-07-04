<?php
namespace backend\controllers;

use Yii;
use backend\models\Purch;
use backend\models\PurchSearch;
use backend\models\PurchLine;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\db\Transaction;

/**
 * PurchController implements the CRUD actions for Purch model.
 */
class PurchController extends Controller
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
     * Lists all Purch models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Purch model.
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
     * Creates a new Purch model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Purch();
        $model->status = Purch::STATUS_DRAFT;
        $model->approve_status = Purch::APPROVE_STATUS_PENDING;
        $model->purch_date = date('Y-m-d');

        // Initialize with one empty purch line
        $model->purchLines = [new PurchLine()];

        if ($model->load(Yii::$app->request->post())) {
            $purchLines = [];
            $valid = $model->validate();

            if (isset($_POST['PurchLine'])) {
                foreach ($_POST['PurchLine'] as $index => $purchLineData) {
                    $purchLine = new PurchLine();
                    $purchLine->load($purchLineData, '');
                    $purchLines[] = $purchLine;
                    $valid = $purchLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // Save purch lines
                        foreach ($purchLines as $purchLine) {
                            $purchLine->purch_id = $model->id;
                            if (!$purchLine->save()) {
                                throw new \Exception('Failed to save purch line');
                            }
                        }
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', 'สร้างใบสั่งซื้อเรียบร้อยแล้ว');
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
     * Updates an existing Purch model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Load existing purch lines
        $model->purchLines = $model->getPurchLines()->all();
        if (empty($model->purchLines)) {
            $model->purchLines = [new PurchLine()];
        }

        if ($model->load(Yii::$app->request->post())) {
            $purchLines = [];
            $valid = $model->validate();

            if (isset($_POST['PurchLine'])) {
                foreach ($_POST['PurchLine'] as $index => $purchLineData) {
                    if (isset($purchLineData['id']) && !empty($purchLineData['id'])) {
                        // Update existing line
                        $purchLine = PurchLine::findOne($purchLineData['id']);
                        if (!$purchLine) {
                            $purchLine = new PurchLine();
                        }
                    } else {
                        // Create new line
                        $purchLine = new PurchLine();
                    }
                    $purchLine->load($purchLineData, '');
                    $purchLines[] = $purchLine;
                    $valid = $purchLine->validate() && $valid;
                }
            }

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($model->save()) {
                        // Delete existing lines that are not in the new list
                        $existingLineIds = [];
                        foreach ($purchLines as $purchLine) {
                            if (!$purchLine->isNewRecord) {
                                $existingLineIds[] = $purchLine->id;
                            }
                        }

                        PurchLine::deleteAll([
                            'and',
                            ['purch_id' => $model->id],
                            ['not in', 'id', $existingLineIds]
                        ]);

                        // Save purch lines
                        foreach ($purchLines as $purchLine) {
                            $purchLine->purch_id = $model->id;
                            if (!$purchLine->save()) {
                                throw new \Exception('Failed to save purch line');
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
     * Deletes an existing Purch model.
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
            // Delete all purch lines first
            PurchLine::deleteAll(['purch_id' => $id]);

            // Delete the purch record
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
     * Approve purchase order
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = Purch::APPROVE_STATUS_APPROVED;
        $model->status = Purch::STATUS_ACTIVE;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'อนุมัติใบสั่งซื้อเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถอนุมัติใบสั่งซื้อได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Reject purchase order
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionReject($id)
    {
        $model = $this->findModel($id);
        $model->approve_status = Purch::APPROVE_STATUS_REJECTED;
        $model->status = Purch::STATUS_CANCELLED;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'ไม่อนุมัติใบสั่งซื้อเรียบร้อยแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถปฏิเสธใบสั่งซื้อได้');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the Purch model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Purch the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Purch::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}