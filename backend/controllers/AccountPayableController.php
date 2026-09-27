<?php

namespace backend\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;
use backend\models\Purch;
use backend\models\PurchaseMaster;
use backend\models\SystemSetting;

class AccountPayableController extends BaseController
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionWorklist()
    {
        $slaDays = SystemSetting::findOne(['setting_key' => 'ap_sla_days']);
        if (!$slaDays) {
            $slaDays = new SystemSetting();
            $slaDays->setting_key = 'ap_sla_days';
            $slaDays->setting_value = '3';
            $slaDays->description = 'จำนวนวัน SLA สำหรับการทำจ่าย (บัญชี)';
            $slaDays->save();
        }
        $max_sla_days = (int)$slaDays->setting_value;

        $company_id = Yii::$app->session->get('company_id');
        
        $worklist = [];

        // 1. Get POs that are received but no PV (or PV not completed)
        $poQuery = Purch::find()
            ->where(['status' => Purch::STATUS_COMPLETED]); // รับสินค้าครบแล้ว
            
        if ($company_id && $company_id != 100) {
            $poQuery->andWhere(['company_id' => $company_id]);
        }
        
        $pos = $poQuery->all();
        
        foreach ($pos as $po) {
            $has_pv = false;
            $has_draft = false;
            $has_paid = false;
            
            $refs = \backend\models\PaymentVoucherRef::find()
                ->alias('r')
                ->innerJoin('payment_voucher pv', 'r.payment_voucher_id = pv.id')
                ->where(['r.ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_PO, 'r.ref_id' => $po->id])
                ->andWhere(['!=', 'pv.status', \backend\models\PaymentVoucher::STATUS_CANCELLED])
                ->all();
                
            foreach ($refs as $ref) {
                $has_pv = true;
                if ($ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_COMPLETED || $ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_ACTIVE) { 
                    $has_paid = true;
                }
                if ($ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_DRAFT) {
                    $has_draft = true;
                }
            }
            
            if (!$has_paid) {
                $received_date = $po->updated_at ? date('Y-m-d', $po->updated_at) : date('Y-m-d', $po->created_at);
                // คำนวณวันล่าช้า
                $diff = (strtotime(date('Y-m-d')) - strtotime($received_date)) / (60 * 60 * 24);
                
                $worklist[] = [
                    'type' => 'PO',
                    'id' => $po->id,
                    'doc_no' => $po->purch_no,
                    'date' => $po->purch_date,
                    'vendor_name' => $po->vendor_name,
                    'amount' => $po->net_amount,
                    'status_text' => $has_draft ? 'สร้าง PV แล้ว (ยังไม่จ่าย)' : 'รอตั้งหนี้ (PV)',
                    'status_color' => $has_draft ? 'warning' : 'danger',
                    'received_date' => $received_date,
                    'overdue_days' => $diff,
                    'is_overdue' => $diff > $max_sla_days,
                    'max_sla' => $max_sla_days,
                    'url' => ['/purch/view', 'id' => $po->id]
                ];
            }
        }
        
        // 2. Get None PRs that are received but no PV
        $nprQuery = PurchaseMaster::find()
            ->where(['!=', 'status', PurchaseMaster::STATUS_CANCELLED]);
            
        if ($company_id && $company_id != 100) {
            $nprQuery->andWhere(['company_id' => $company_id]);
        }
        
        $nprs = $nprQuery->all();
        
        foreach ($nprs as $npr) {
            if ($npr->getReceiveStatus() == PurchaseMaster::RECEIVE_STATUS_COMPLETED) {
                $has_pv = false;
                $has_draft = false;
                $has_paid = false;
                
                $refs = \backend\models\PaymentVoucherRef::find()
                    ->alias('r')
                    ->innerJoin('payment_voucher pv', 'r.payment_voucher_id = pv.id')
                    ->where(['r.ref_type' => \backend\models\PaymentVoucherRef::REF_TYPE_NONE_PR, 'r.ref_id' => $npr->id])
                    ->andWhere(['!=', 'pv.status', \backend\models\PaymentVoucher::STATUS_CANCELLED])
                    ->all();
                    
                foreach ($refs as $ref) {
                    $has_pv = true;
                    if ($ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_COMPLETED || $ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_ACTIVE) { 
                        $has_paid = true;
                    }
                    if ($ref->paymentVoucher->status == \backend\models\PaymentVoucher::STATUS_DRAFT) {
                        $has_draft = true;
                    }
                }
                
                if (!$has_paid) {
                    $received_date = $npr->updated_at ? date('Y-m-d', $npr->updated_at) : date('Y-m-d', strtotime($npr->docdat));
                    $diff = (strtotime(date('Y-m-d')) - strtotime($received_date)) / (60 * 60 * 24);

                    $worklist[] = [
                        'type' => 'NPR',
                        'id' => $npr->id,
                        'doc_no' => $npr->docnum,
                        'date' => $npr->docdat,
                        'vendor_name' => $npr->supnam,
                        'amount' => $npr->total_amount,
                        'status_text' => $has_draft ? 'สร้าง PV แล้ว (ยังไม่จ่าย)' : 'รอตั้งหนี้ (PV)',
                        'status_color' => $has_draft ? 'warning' : 'danger',
                        'received_date' => $received_date,
                        'overdue_days' => $diff,
                        'is_overdue' => $diff > $max_sla_days,
                        'max_sla' => $max_sla_days,
                        'url' => ['/purchasemaster/view', 'id' => $npr->id]
                    ];
                }
            }
        }
        
        // Sort by date (oldest first)
        usort($worklist, function($a, $b) {
            return strtotime($a['received_date']) - strtotime($b['received_date']);
        });
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => $worklist,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('worklist', [
            'dataProvider' => $dataProvider,
            'max_sla_days' => $max_sla_days,
        ]);
    }

    public function actionSetting()
    {
        $slaDays = SystemSetting::findOne(['setting_key' => 'ap_sla_days']);
        if (!$slaDays) {
            $slaDays = new SystemSetting();
            $slaDays->setting_key = 'ap_sla_days';
            $slaDays->setting_value = '3';
            $slaDays->description = 'จำนวนวัน SLA สำหรับการทำจ่าย (บัญชี)';
            $slaDays->save();
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if (isset($post['ap_sla_days'])) {
                $slaDays->setting_value = (string) $post['ap_sla_days'];
                
                if ($slaDays->save()) {
                    Yii::$app->session->setFlash('success', 'บันทึกการตั้งค่าเรียบร้อยแล้ว');
                    return $this->redirect(['worklist']);
                } else {
                    Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกการตั้งค่าได้');
                }
            }
        }

        return $this->render('setting', [
            'slaDays' => $slaDays,
        ]);
    }
}
