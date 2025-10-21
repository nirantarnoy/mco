<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

date_default_timezone_set('Asia/Bangkok');

class Vendor extends \common\models\Vendor
{
    public function behaviors()
    {
        return [
            'timestampcdate' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                ],
                'value' => time(),
            ],
            'timestampudate' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'updated_at',
                ],
                'value' => time(),
            ],
            'timestampcby' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_by',
                ],
                'value' => Yii::$app->user->id,
            ],
            'timestamuby' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
                ],
                'value' => Yii::$app->user->id,
            ],
            'timestampupdate' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => time(),
            ],
        ];
    }

    public static function findName($id)
    {
        $model = Vendor::find()->where(['id' => $id])->one();
        return $model != null ? $model->name : '';
    }
    public static function findCode($id)
    {
        $model = Vendor::find()->where(['id' => $id])->one();
        return $model != null ? $model->code : '';
    }

    public static function findFullAddress($id){
        $model = Vendor::find()->where(['id'=>$id])->one();
        return $model!= null?$model->home_number.' '.$model->street.' '.$model->aisle.' '.$model->district_name.' '.$model->city_name.' '.$model->province_name.' '.$model->zipcode:'';
    }


    public static function getlastno()
    {
        $vendor_code = 'NOT FOUND';

        $prefix = 'VA';

        // Find last number for this type and date
        $lastRecord = Vendor::find()
            ->where(['like', 'code', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->code, -3));
            $newNumber = $lastNumber + 1;
            $vendor_code = $lastNumber;
        } else {
            $newNumber = 1;
        }

        $vendor_code = $prefix . sprintf('%03d', $newNumber);


        return $vendor_code;
    }

    public static function findVendorInfo($id) {
        $data = [];
        $model = Vendor::find()->where(['id'=>$id])->one();
        if($model){
            $data['name'] = $model->name;
            $data['home_number'] = $model->home_number;
            $data['street'] = $model->street;
            $data['aisle'] = $model->aisle;
            $data['district_name'] = $model->district_name;
            $data['city_name'] = $model->city_name;
            $data['province_name'] = $model->province_name;
            $data['zipcode'] = $model->zipcode;
            $data['contact_name'] = $model->contact_name;
            $data['phone'] = $model->phone;
            $data['email'] = $model->email;

        }
        return $data;
    }



}
