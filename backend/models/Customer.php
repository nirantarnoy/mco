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
                'value' => function () {
                    return (Yii::$app->has('user') && Yii::$app->user && !Yii::$app->user->isGuest) ? Yii::$app->user->id : 1;
                },
            ],
            'timestamuby' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
                ],
                'value' => function () {
                    return (Yii::$app->has('user') && Yii::$app->user && !Yii::$app->user->isGuest) ? Yii::$app->user->id : 1;
                },
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

    public static function findCode($id)
    {
        $model = Customer::find()->where(['id' => $id])->one();
        return $model != null ? $model->code : '';
    }
    public static function findName($id)
    {
        $model = Customer::find()->where(['id' => $id])->one();
        return $model != null ? $model->name : '';
    }

    public static function findTaxId($id)
    {
        $model = Customer::find()->where(['id' => $id])->one();
        return $model != null ? $model->taxid : '';
    }

    public static function findFullAddress($id)
    {
        $model = Customer::find()->where(['id' => $id])->one();
        if ($model != null) {
            // ใช้ AddressHelper จัดรูปแบบที่อยู่ (แยกกรุงเทพฯ กับจังหวัดอื่นอัตโนมัติ)
            return \backend\helpers\AddressHelper::formatCustomerAddress($model);
        }
        return '';
    }


    public static function findCustomerInfo($id)
    {
        $data = [];
        $model = Customer::find()->where(['id' => $id])->one();
        if ($model) {
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

    public $company_list = [];

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['company_list'], 'safe'],
        ]);
    }

    public function getCustomerCompanies()
    {
        return $this->hasMany(\common\models\CustomerCompany::class, ['customer_id' => 'id']);
    }

    public function getCompanies()
    {
        return $this->hasMany(\backend\models\Company::class, ['id' => 'company_id'])->via('customerCompanies');
    }

    public function afterFind()
    {
        parent::afterFind();
        if ($this->is_common != 1) {
            $this->company_list = \common\models\CustomerCompany::find()
                ->select('company_id')
                ->where(['customer_id' => $this->id])
                ->column();
            if (empty($this->company_list) && !empty($this->company_id)) {
                $this->company_list = [(int)$this->company_id];
            }
        }
    }

    /**
     * ตรวจสอบว่าลูกค้ารายนี้อนุญาตให้ใช้งานในบริษัทที่ระบุหรือไม่
     * @param int|null $company_id
     * @return bool
     */
    public function isAllowedForCompany($company_id = null)
    {
        if ($this->is_common == 1) {
            return true;
        }
        if ($company_id === null) {
            $company_id = (Yii::$app->has('session') && Yii::$app->session) ? Yii::$app->session->get('company_id') : null;
        }
        if (!$company_id || $company_id == 100) {
            return true;
        }
        $allowed = \common\models\CustomerCompany::find()
            ->where(['customer_id' => $this->id, 'company_id' => $company_id])
            ->exists();
        if ($allowed) {
            return true;
        }
        return (int)$this->company_id === (int)$company_id;
    }

    public static function getAllowedCustomers($company_id = null)
    {
        if ($company_id === null) {
            $sess = (Yii::$app->has('session') && Yii::$app->session) ? Yii::$app->session->get('company_id') : null;
            $company_id = ($sess == 100 ? null : $sess);
        }
        $query = Customer::find();
        if ($company_id != null) {
            $subQuery = (new \yii\db\Query())
                ->select('customer_id')
                ->from('customer_company')
                ->where(['company_id' => $company_id]);

            $query->andWhere([
                'or',
                ['customer.is_common' => 1],
                ['customer.company_id' => $company_id],
                ['customer.id' => $subQuery]
            ]);
        }
        return $query;
    }

    public static function getlastno()
    {
        $customer_code = 'NOT FOUND';

        $prefix = 'CA';

        // Find last number for this type and date
        $lastRecord = Customer::find()
            ->where(['like', 'code', $prefix . '%', false])
            ->orderBy([new \yii\db\Expression('LENGTH(code) DESC'), 'code' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->code, strlen($prefix)));
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
        if (parent::beforeSave($insert)) {
            if ($this->is_common == 1) {
                $this->company_id = null;
            } else {
                if (is_array($this->company_list) && !empty($this->company_list)) {
                    $this->company_id = reset($this->company_list);
                } else {
                    $sessionCompany = (Yii::$app->has('session') && Yii::$app->session) ? Yii::$app->session->get('company_id') : null;
                    $this->company_id = ($sessionCompany == 100 ? null : $sessionCompany);
                }
            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        \common\models\CustomerCompany::deleteAll(['customer_id' => $this->id]);
        if ($this->is_common != 1 && is_array($this->company_list)) {
            foreach ($this->company_list as $cid) {
                $cid = (int)$cid;
                if ($cid > 0) {
                    $map = new \common\models\CustomerCompany();
                    $map->customer_id = $this->id;
                    $map->company_id = $cid;
                    $map->created_at = time();
                    $map->save(false);
                }
            }
        }
    }
}
