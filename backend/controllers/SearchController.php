<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use backend\models\Job;
use backend\models\JobLine;
use backend\models\Product;

class SearchController extends Controller
{
    public function actionIndex()
    {
        $searchQuery = Yii::$app->request->get('q', '');
        $dataProvider = null;

        if (!empty($searchQuery)) {
            // แยกคำค้นหาด้วยเครื่องหมาย comma
            $keywords = array_map('trim', explode(',', $searchQuery));
            $keywords = array_filter($keywords); // ลบค่าว่าง

            $query = Job::find()
                ->joinWith(['jobLines', 'jobLines.product'])
                ->groupBy('job.id');

            // สร้าง condition สำหรับแต่ละ keyword
            if (!empty($keywords)) {
                $orConditions = ['or'];

                foreach ($keywords as $keyword) {
                    $orConditions[] = ['like', 'job.job_no', $keyword];
                    $orConditions[] = ['like', 'job.quotation_id', $keyword];
                    $orConditions[] = ['like', 'job.status', $keyword];
                    $orConditions[] = ['like', 'job.summary_note', $keyword];
                    $orConditions[] = ['like', 'job_line.note', $keyword];
                    $orConditions[] = ['like', 'product.code', $keyword];
                    $orConditions[] = ['like', 'product.name', $keyword];
                    $orConditions[] = ['like', 'product.description', $keyword];
                }

                $query->where($orConditions);
            }

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 20,
                ],
                'sort' => [
                    'defaultOrder' => [
                        'created_at' => SORT_DESC,
                    ]
                ],
            ]);
        }

        return $this->render('index', [
            'searchQuery' => $searchQuery,
            'dataProvider' => $dataProvider,
        ]);
    }
}