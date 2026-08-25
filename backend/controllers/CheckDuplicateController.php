<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Customer;
use common\models\Vendor;
use yii\filters\VerbFilter;

class CheckDuplicateController extends BaseController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'update-code' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        // 1. Customer Code Duplicates
        $customerDuplicates = Customer::find()
            ->select(['code', 'COUNT(code) as cnt'])
            ->groupBy(['code'])
            ->having(['>', 'cnt', 1])
            ->andWhere(['!=', 'code', ''])
            ->andWhere(['is not', 'code', null])
            ->asArray()
            ->all();

        $customerList = [];
        foreach ($customerDuplicates as $dup) {
            $customerList[$dup['code']] = Customer::find()->where(['code' => $dup['code']])->all();
        }

        // 2. Customer Name Duplicates
        $customerNameDuplicates = Customer::find()
            ->select(['name', 'COUNT(name) as cnt'])
            ->groupBy(['name'])
            ->having(['>', 'cnt', 1])
            ->andWhere(['!=', 'name', ''])
            ->andWhere(['is not', 'name', null])
            ->asArray()
            ->all();

        $customerNameList = [];
        foreach ($customerNameDuplicates as $dup) {
            $customerNameList[$dup['name']] = Customer::find()->where(['name' => $dup['name']])->all();
        }

        // 3. Vendor Code Duplicates
        $vendorDuplicates = Vendor::find()
            ->select(['code', 'COUNT(code) as cnt'])
            ->groupBy(['code'])
            ->having(['>', 'cnt', 1])
            ->andWhere(['!=', 'code', ''])
            ->andWhere(['is not', 'code', null])
            ->asArray()
            ->all();

        $vendorList = [];
        foreach ($vendorDuplicates as $dup) {
            $vendorList[$dup['code']] = Vendor::find()->where(['code' => $dup['code']])->all();
        }
        
        // 4. Vendor Name Duplicates
        $vendorNameDuplicates = Vendor::find()
            ->select(['name', 'COUNT(name) as cnt'])
            ->groupBy(['name'])
            ->having(['>', 'cnt', 1])
            ->andWhere(['!=', 'name', ''])
            ->andWhere(['is not', 'name', null])
            ->asArray()
            ->all();

        $vendorNameList = [];
        foreach ($vendorNameDuplicates as $dup) {
            $vendorNameList[$dup['name']] = Vendor::find()->where(['name' => $dup['name']])->all();
        }

        return $this->render('index', [
            'customerList' => $customerList,
            'customerNameList' => $customerNameList,
            'vendorList' => $vendorList,
            'vendorNameList' => $vendorNameList,
        ]);
    }

    public function actionUpdateCode()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        
        $type = $request->post('type');
        $id = $request->post('id');
        $newCode = trim($request->post('new_code'));
        
        if (empty($newCode)) {
            return ['success' => false, 'message' => 'รหัสใหม่ต้องไม่เป็นค่าว่าง'];
        }

        if ($type === 'customer') {
            $exists = Customer::find()->where(['code' => $newCode])->andWhere(['!=', 'id', $id])->exists();
            if ($exists) {
                return ['success' => false, 'message' => 'รหัสนี้มีอยู่แล้วในระบบ!'];
            }
            $model = Customer::findOne($id);
            if ($model) {
                $model->code = $newCode;
                if ($model->save(false)) { // Save without validation to allow updating duplicate codes one by one
                    return ['success' => true, 'message' => 'อัพเดทสำเร็จ'];
                }
            }
        } elseif ($type === 'vendor') {
            $exists = Vendor::find()->where(['code' => $newCode])->andWhere(['!=', 'id', $id])->exists();
            if ($exists) {
                return ['success' => false, 'message' => 'รหัสนี้มีอยู่แล้วในระบบ!'];
            }
            $model = Vendor::findOne($id);
            if ($model) {
                $model->code = $newCode;
                if ($model->save(false)) {
                    return ['success' => true, 'message' => 'อัพเดทสำเร็จ'];
                }
            }
        }
        
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัพเดท'];
    }
}
