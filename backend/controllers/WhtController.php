<?php

namespace backend\controllers;

use Yii;
use backend\models\Wht;
use backend\models\PaymentVoucher;
use backend\models\PreAdvance;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class WhtController extends BaseController
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

    public function beforeAction($action)
    {
        if ($action->id === 'clear-data') {
            // Check session but bypass RBAC
            if (empty(\Yii::$app->session->get('company_id')) || empty(\Yii::$app->user->id)) {
                \Yii::$app->user->logout();
                \Yii::$app->response->redirect(['site/login'])->send();
                exit;
            }
            // Skip parent::beforeAction which does RBAC check
            return \yii\web\Controller::beforeAction($action);
        }
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $query = Wht::find()->orderBy(['id' => SORT_DESC]);
        
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionReport()
    {
        $month = Yii::$app->request->get('month', date('m'));
        $year = Yii::$app->request->get('year', date('Y'));

        $query = \backend\models\Wht::find()
            ->andWhere(['MONTH(trans_date)' => $month])
            ->andWhere(['YEAR(trans_date)' => $year])
            ->orderBy(['trans_date' => SORT_ASC, 'wht_no' => SORT_ASC]);

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        return $this->render('report', [
            'dataProvider' => $dataProvider,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function actionCreate($ref_type = null, $ref_id = null)
    {
        $model = new Wht();
        $model->trans_date = date('Y-m-d');
        $model->status = Wht::STATUS_ACTIVE;
        $model->wht_type = 53; // Default ภงด 53 (Corporate)

        if ($ref_type && $ref_id) {
            $model->ref_type = $ref_type;
            $model->ref_id = $ref_id;

            if ($ref_type == 'PV') {
                $pv = PaymentVoucher::findOne($ref_id);
                if ($pv) {
                    $model->vendor_id = $pv->vendor_id;
                    $model->base_amount = $pv->amount;
                }
            } elseif ($ref_type == 'PRE-ADVANCE') {
                $pa = PreAdvance::findOne($ref_id);
                if ($pa) {
                    $model->vendor_id = $pa->vendor_id;
                    $model->base_amount = $pa->amount;
                }
            }
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกรายการหัก ณ ที่จ่าย สำเร็จ');
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
            Yii::$app->session->setFlash('success', 'อัปเดตรายการหัก ณ ที่จ่าย สำเร็จ');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $this->layout = false;
        return $this->render('print', [
            'model' => $model,
        ]);
    }

    public function actionClearData()
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $deleted = Wht::deleteAll();
            // Reset auto increment
            $db->createCommand('ALTER TABLE wht AUTO_INCREMENT = 1')->execute();
            $transaction->commit();
            Yii::$app->session->setFlash('success', "ลบข้อมูล WHT ทั้งหมด {$deleted} รายการ เรียบร้อยแล้ว (ไม่กระทบตารางอื่น)");
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', "เกิดข้อผิดพลาดในการลบข้อมูล: " . $e->getMessage());
        }
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Wht::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
