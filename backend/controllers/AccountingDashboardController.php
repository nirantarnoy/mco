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

    public function actionIndex($filter = 'all')
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
        $totalOverdue = 0;
        $today = date('Y-m-d');
        
        foreach ($allPosList as $po) {
            $isReceived = false;
            if ($po->status == Purch::STATUS_COMPLETED) {
                $isReceived = true;
            } else {
                $remain = Purch::checkPoremain($po->id);
                if (empty($remain)) {
                    $isReceived = true;
                }
            }
            
            if ($isReceived) {
                $totalReceived++;
            } else {
                $totalPending++;
                if (!empty($po->target_shipment_date)) {
                    if (strtotime($po->target_shipment_date) < strtotime($today)) {
                        $totalOverdue++;
                    }
                }
            }
        }

        // Apply filters to DataProvider only (Charts use all data)
        if ($filter !== 'all') {
            // สำหรับฟิลเตอร์เรื่องเวลา จะแสดงเฉพาะใบที่ยังรับไม่ครบ
            // เพื่อหลีกเลี่ยงใบที่รับของครบแล้วแต่ยังแสดงอยู่
            $query->andWhere(['!=', 'purch.status', Purch::STATUS_COMPLETED]);
            
            if ($filter === 'overdue') {
                $query->andWhere(['<', 'purch.target_shipment_date', $today]);
            } elseif ($filter === '3days') {
                $query->andWhere(['>=', 'purch.target_shipment_date', $today])
                      ->andWhere(['<=', 'purch.target_shipment_date', date('Y-m-d', strtotime('+3 days'))]);
            } elseif ($filter === '5days') {
                $query->andWhere(['>=', 'purch.target_shipment_date', $today])
                      ->andWhere(['<=', 'purch.target_shipment_date', date('Y-m-d', strtotime('+5 days'))]);
            } elseif ($filter === '7days') {
                $query->andWhere(['>=', 'purch.target_shipment_date', $today])
                      ->andWhere(['<=', 'purch.target_shipment_date', date('Y-m-d', strtotime('+7 days'))]);
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
            'totalOverdue' => $totalOverdue,
            'filter' => $filter,
        ]);
    }
}
