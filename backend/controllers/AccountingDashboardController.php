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

    public function actionIndex($filter = 'all', $startDate = null, $endDate = null)
    {
        // Helper function for date filtering
        $applyDateFilter = function($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $q->andWhere(['>=', 'purch.purch_date', $startDate])
                  ->andWhere(['<=', 'purch.purch_date', $endDate]);
            }
        };

        // Fix N+1: ใช้ Count SQL ตรงๆ แทนการดึง Model มา loop และเช็ค checkPoremain ทีละใบ
        $qApproved = Purch::find()
            ->where(['purch.approve_status' => Purch::APPROVE_STATUS_APPROVED])
            ->andWhere(['!=', 'purch.status', Purch::STATUS_CANCELLED]);
        $applyDateFilter($qApproved);
        $totalApproved = (int)$qApproved->count();
            
        // ให้ถือว่าสถานะ STATUS_COMPLETED (2) คือรับของครบแล้ว สำหรับการแสดงผลบนกราฟ
        $qReceived = Purch::find()
            ->where(['purch.approve_status' => Purch::APPROVE_STATUS_APPROVED])
            ->andWhere(['purch.status' => Purch::STATUS_COMPLETED]);
        $applyDateFilter($qReceived);
        $totalReceived = (int)$qReceived->count();
            
        $totalPending = $totalApproved - $totalReceived;
        $today = date('Y-m-d');
        
        $qOverdue = Purch::find()
            ->where(['purch.approve_status' => Purch::APPROVE_STATUS_APPROVED])
            ->andWhere(['!=', 'purch.status', Purch::STATUS_CANCELLED])
            ->andWhere(['!=', 'purch.status', Purch::STATUS_COMPLETED])
            ->andWhere(['<', 'purch.target_shipment_date', $today]);
        $applyDateFilter($qOverdue);
        $totalOverdue = (int)$qOverdue->count();
            
        // จำนวน PO ที่ดำเนินการทางบัญชีแล้ว (สร้าง PV แล้ว)
        $qAccounted = Purch::find()
            ->innerJoin('payment_voucher_ref pvr', 'pvr.ref_id = purch.id')
            ->innerJoin('payment_voucher pv', 'pv.id = pvr.payment_voucher_id')
            ->where(['purch.approve_status' => Purch::APPROVE_STATUS_APPROVED])
            ->andWhere(['!=', 'purch.status', Purch::STATUS_CANCELLED])
            ->andWhere(['pvr.ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PO])
            ->andWhere(['!=', 'pv.status', \backend\models\PaymentVoucher::STATUS_CANCELLED]);
        $applyDateFilter($qAccounted);
        $totalAccounted = (int)$qAccounted->count('DISTINCT purch.id');

        // รีเซ็ต Query สำหรับ GridView เพื่อกรองตามปุ่ม
        $query = Purch::find()
            ->where(['purch.approve_status' => Purch::APPROVE_STATUS_APPROVED])
            ->andWhere(['!=', 'purch.status', Purch::STATUS_CANCELLED]);
        $applyDateFilter($query);
            
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
            'totalAccounted' => $totalAccounted,
            'filter' => $filter,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
