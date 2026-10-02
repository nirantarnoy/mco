<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use backend\models\Purch;

class AccountingDashboardController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        // ดึง PO ที่อนุมัติแล้ว และยังไม่ยกเลิก
        // เรียงตามวันที่คาดว่าจะได้รับสินค้า เพื่อให้เห็นอันที่ใกล้ถึงกำหนดก่อน
        $query = Purch::find()
            ->where(['approve_status' => Purch::APPROVE_STATUS_APPROVED])
            ->andWhere(['!=', 'status', Purch::STATUS_CANCELLED]);
            
        // กรองเฉพาะ PO ที่อยู่ในปีปัจจุบันหรือย้อนหลังนิดหน่อย เพื่อไม่ให้ข้อมูลเยอะเกินไป
        // $query->andWhere(['>=', 'purch_date', date('Y-01-01')]);

        $allPos = clone $query;
        $allPosList = $allPos->all();
        
        $totalApproved = count($allPosList);
        $totalReceived = 0;
        $totalPending = 0;
        
        foreach ($allPosList as $po) {
            if ($po->status == Purch::STATUS_COMPLETED) {
                $totalReceived++;
            } else {
                $remain = Purch::checkPoremain($po->id);
                if (empty($remain)) {
                    $totalReceived++;
                } else {
                    $totalPending++;
                }
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'target_shipment_date' => SORT_ASC,
                    'purch_date' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'totalApproved' => $totalApproved,
            'totalReceived' => $totalReceived,
            'totalPending' => $totalPending,
        ]);
    }
}
