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

        // Fix N+1: ใช้ Count SQL ตรงๆ แทนการดึง Model มา loop และเช็ค checkPoremain ทีละใบ
        $totalApproved = (int)Purch::find()
            ->where(['approve_status' => Purch::APPROVE_STATUS_APPROVED])
            ->andWhere(['!=', 'status', Purch::STATUS_CANCELLED])
            ->count();
            
        // ให้ถือว่าสถานะ STATUS_COMPLETED (2) คือรับของครบแล้ว สำหรับการแสดงผลบนกราฟ
        $totalReceived = (int)Purch::find()
            ->where(['approve_status' => Purch::APPROVE_STATUS_APPROVED])
            ->andWhere(['status' => Purch::STATUS_COMPLETED])
            ->count();
            
        $totalPending = $totalApproved - $totalReceived;
        $today = date('Y-m-d');
        
        $totalOverdue = (int)Purch::find()
            ->where(['approve_status' => Purch::APPROVE_STATUS_APPROVED])
            ->andWhere(['!=', 'status', Purch::STATUS_CANCELLED])
            ->andWhere(['!=', 'status', Purch::STATUS_COMPLETED])
            ->andWhere(['<', 'target_shipment_date', $today])
            ->count();

        // รีเซ็ต Query สำหรับ GridView เพื่อกรองตามปุ่ม
        $query = Purch::find()
            ->where(['approve_status' => Purch::APPROVE_STATUS_APPROVED])
            ->andWhere(['!=', 'status', Purch::STATUS_CANCELLED]);
            
        // แก้ไข N+1 สำหรับข้อมูลในตาราง
        // (ยังอาจมีส่วนที่เรียก getAttachedDocuments(), getPvStatusText() แต่ลดการ query หนักๆ ไปได้เยอะ)

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
