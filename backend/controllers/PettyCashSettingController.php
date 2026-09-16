<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use backend\models\SystemSetting;

class PettyCashSettingController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Allow logged in users, you might want to restrict this to admins
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $maxAmount = SystemSetting::findOne(['setting_key' => 'petty_cash_max_amount']);
        $minAmount = SystemSetting::findOne(['setting_key' => 'petty_cash_min_amount']);

        // If settings don't exist yet, create them with defaults
        if (!$maxAmount) {
            $maxAmount = new SystemSetting();
            $maxAmount->setting_key = 'petty_cash_max_amount';
            $maxAmount->setting_value = '30000';
            $maxAmount->description = 'วงเงินสดย่อยสูงสุด';
            $maxAmount->save();
        }
        if (!$minAmount) {
            $minAmount = new SystemSetting();
            $minAmount->setting_key = 'petty_cash_min_amount';
            $minAmount->setting_value = '3000';
            $minAmount->description = 'วงเงินสดย่อยขั้นต่ำ';
            $minAmount->save();
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            
            if (isset($post['max_amount']) && isset($post['min_amount'])) {
                $maxAmount->setting_value = (string) $post['max_amount'];
                $minAmount->setting_value = (string) $post['min_amount'];
                
                if ($maxAmount->save() && $minAmount->save()) {
                    Yii::$app->session->setFlash('success', 'บันทึกการตั้งค่าเรียบร้อยแล้ว');
                    return $this->redirect(['/petty-cash-advance/index']);
                } else {
                    Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกการตั้งค่าได้');
                }
            }
        }

        return $this->render('index', [
            'maxAmount' => $maxAmount,
            'minAmount' => $minAmount,
        ]);
    }
}
