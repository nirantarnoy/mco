<?php
namespace app\controllers;

use Yii;
use backend\models\ActionLog;
use backend\models\ActionLogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ActionLogController implements the CRUD operations for ActionLog model.
 */
class ActionLogController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // เฉพาะผู้ใช้ที่ล็อกอินแล้ว
                        // 'matchCallback' => function ($rule, $action) {
                        //     return Yii::$app->user->identity->role === 'admin'; // เฉพาะ admin
                        // }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'bulk-delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ActionLog models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ActionLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // สถิติ
        $statistics = ActionLogSearch::getStatistics();
        $popularActions = ActionLogSearch::getPopularActions();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'statistics' => $statistics,
            'popularActions' => $popularActions,
        ]);
    }

    /**
     * Displays a single ActionLog model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // บันทึก log การดู
        ActionLog::log('VIEW_LOG', ['viewed_log_id' => $id]);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ActionLog model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        // บันทึก log การลบ
        ActionLog::log('DELETE_LOG', ['deleted_log_id' => $id]);

        Yii::$app->session->setFlash('success', 'Log deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Bulk delete action logs
     */
    public function actionBulkDelete()
    {
        $selection = Yii::$app->request->post('selection');

        if ($selection) {
            $count = ActionLog::deleteAll(['id' => $selection]);

            // บันทึก log การลบหลายรายการ
            ActionLog::log('BULK_DELETE_LOGS', [
                'deleted_count' => $count,
                'deleted_ids' => $selection
            ]);

            Yii::$app->session->setFlash('success', "Deleted {$count} log(s) successfully.");
        } else {
            Yii::$app->session->setFlash('error', 'No logs selected for deletion.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Clean old logs
     */
    public function actionCleanOld($days = 90)
    {
        $count = ActionLog::cleanOldLogs($days);

        // บันทึก log การทำความสะอาด
        ActionLog::log('CLEAN_OLD_LOGS', [
            'days' => $days,
            'deleted_count' => $count
        ]);

        Yii::$app->session->setFlash('success', "Cleaned {$count} old log(s) successfully.");
        return $this->redirect(['index']);
    }

    /**
     * Export logs to CSV
     */
    public function actionExport()
    {
        $searchModel = new ActionLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false; // ดึงข้อมูลทั้งหมด

        $filename = 'action_logs_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, [
            'ID', 'User ID', 'Username', 'Action', 'Controller', 'Action Method',
            'Model Class', 'Model ID', 'IP Address', 'URL', 'Method', 'Status',
            'Message', 'Created At'
        ]);

        // Data
        foreach ($dataProvider->models as $model) {
            fputcsv($output, [
                $model->id,
                $model->user_id,
                $model->username,
                $model->action,
                $model->controller,
                $model->action_method,
                $model->model_class,
                $model->model_id,
                $model->ip_address,
                $model->url,
                $model->method,
                $model->status,
                $model->message,
                $model->created_at,
            ]);
        }

        fclose($output);

        // บันทึก log การ export
        ActionLog::log('EXPORT_LOGS', ['filename' => $filename]);

        exit;
    }

    /**
     * Dashboard view with charts and statistics
     */
    public function actionDashboard()
    {
        // สถิติรายวัน (7 วันล่าสุด)
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dailyStats[] = [
                'date' => $date,
                'total' => ActionLog::find()->where(['like', 'created_at', $date])->count(),
                'success' => ActionLog::find()->where(['like', 'created_at', $date])->andWhere(['status' => ActionLog::STATUS_SUCCESS])->count(),
                'failed' => ActionLog::find()->where(['like', 'created_at', $date])->andWhere(['status' => ActionLog::STATUS_FAILED])->count(),
            ];
        }

        // Top Actions
        $topActions = ActionLog::find()
            ->select(['action', 'COUNT(*) as count'])
            ->where(['>=', 'created_at', date('Y-m-d H:i:s', strtotime('-30 days'))])
            ->groupBy('action')
            ->orderBy('count DESC')
            ->limit(10)
            ->asArray()
            ->all();

        // Top Users
        $topUsers = ActionLog::find()
            ->select(['username', 'COUNT(*) as count'])
            ->where(['>=', 'created_at', date('Y-m-d H:i:s', strtotime('-30 days'))])
            ->andWhere(['is not', 'username', null])
            ->groupBy('username')
            ->orderBy('count DESC')
            ->limit(10)
            ->asArray()
            ->all();

        return $this->render('dashboard', [
            'dailyStats' => $dailyStats,
            'topActions' => $topActions,
            'topUsers' => $topUsers,
            'statistics' => ActionLogSearch::getStatistics(),
        ]);
    }

    /**
     * Finds the ActionLog model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ActionLog the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ActionLog::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}