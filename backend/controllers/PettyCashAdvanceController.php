<?php
namespace backend\controllers;

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
                    'delete' => ['POST'],
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
}
