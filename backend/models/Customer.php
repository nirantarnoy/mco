<?php
namespace backend\models;
use Yii;
use yii\db\ActiveRecord;
date_default_timezone_set('Asia/Bangkok');

class Customer extends \common\models\Customer
{
    public function behaviors()
    {
        return [
            'timestampcdate'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>'created_at',
                ],
                'value'=> time(),
            ],
            'timestampudate'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>'updated_at',
                ],
                'value'=> time(),
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

    public static function findCode($id){
        $model = Customer::find()->where(['id'=>$id])->one();
        return $model!= null?$model->code:'';
    }
    public static function findName($id){
        $model = Customer::find()->where(['id'=>$id])->one();
        return $model!= null?$model->name:'';
    }

    public static function findTaxId($id){
        $model = Customer::find()->where(['id'=>$id])->one();
        return $model!= null?$model->taxid:'';
    }

    public static function findFullAddress($id){
        $model = Customer::find()->where(['id'=>$id])->one();
        return $model!= null?$model->home_number.' '.$model->street.' '.$model->aisle.' '.$model->district_name.' '.$model->city_name.' '.$model->province_name.' '.$model->zipcode:'';
    }


    public static function findCustomerInfo($id){
        $data = [];
        $model = Customer::find()->where(['id'=>$id])->one();
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
            $data['taxid'] = $model->taxid;

        }
        return $data;
    }

    public static function getlastno()
    {
        $customer_code = 'NOT FOUND';

        $prefix = 'CA';

        // Find last number for this type and date
        $lastRecord = Customer::find()
            ->where(['like', 'code', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->code, -3));
            $newNumber = $lastNumber + 1;
            $customer_code = $lastNumber;
        } else {
            $newNumber = 1;
        }

        $customer_code = $prefix . sprintf('%03d', $newNumber);


        return $customer_code;
    }

    public function beforeSave($insert)
    {
        $this->company_id = \Yii::$app->session->get('company_id');
        return true;
    }

}
