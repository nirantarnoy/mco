<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

date_default_timezone_set('Asia/Bangkok');

class Vendor extends \common\models\Vendor
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name'], 'required'],
            [['name'], 'unique', 'targetAttribute' => ['name', 'company_id'], 'message' => 'ชื่อผู้ขายนี้มีอยู่ในระบบแล้ว'],
            [['company_list'], 'safe'],
        ]);
    }

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
        if ($model != null) {
            return \backend\helpers\AddressHelper::formatVendorAddress($model);
        }
        return '';
    }


    public static function getlastno()
    {
        $vendor_code = 'NOT FOUND';

        $prefix = 'VA';

        // Find last number for this type and date
        $lastRecord = Vendor::find()
            ->where(['like', 'code', $prefix . '%', false])
            ->orderBy([new \yii\db\Expression('LENGTH(code) DESC'), 'code' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->code, strlen($prefix)));
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

    public $company_list = [];

    public function getVendorCompanies()
    {
        return $this->hasMany(\common\models\VendorCompany::class, ['vendor_id' => 'id']);
    }

    public function getCompanies()
    {
        return $this->hasMany(\backend\models\Company::class, ['id' => 'company_id'])->via('vendorCompanies');
    }

    public function afterFind()
    {
        parent::afterFind();
        if (empty($this->full_address)) {
            $formatted = \backend\helpers\AddressHelper::formatVendorAddress($this);
            if (!empty($formatted)) {
                $this->full_address = $formatted;
            }
        }
        if ($this->is_common != 1) {
            $this->company_list = \common\models\VendorCompany::find()
                ->select('company_id')
                ->where(['vendor_id' => $this->id])
                ->column();
            if (empty($this->company_list) && !empty($this->company_id)) {
                $this->company_list = [(int)$this->company_id];
            }
        }
    }

    /**
     * ตรวจสอบว่าผู้ขายรายนี้อนุญาตให้ใช้งานในบริษัทที่ระบุหรือไม่
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
        $allowed = \common\models\VendorCompany::find()
            ->where(['vendor_id' => $this->id, 'company_id' => $company_id])
            ->exists();
        if ($allowed) {
            return true;
        }
        return (int)$this->company_id === (int)$company_id;
    }

    public static function getAllowedVendors($company_id = null)
    {
        if ($company_id === null) {
            $sess = (Yii::$app->has('session') && Yii::$app->session) ? Yii::$app->session->get('company_id') : null;
            $company_id = ($sess == 100 ? null : $sess);
        }
        $query = Vendor::find();
        if ($company_id != null) {
            $subQuery = (new \yii\db\Query())
                ->select('vendor_id')
                ->from('vendor_company')
                ->where(['company_id' => $company_id]);

            $query->andWhere([
                'or',
                ['vendor.is_common' => 1],
                ['vendor.company_id' => $company_id],
                ['vendor.id' => $subQuery]
            ]);
        }
        return $query;
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
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

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->full_address = \backend\helpers\AddressHelper::formatVendorAddress($this);
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        \common\models\VendorCompany::deleteAll(['vendor_id' => $this->id]);
        if ($this->is_common != 1 && is_array($this->company_list)) {
            foreach ($this->company_list as $cid) {
                $cid = (int)$cid;
                if ($cid > 0) {
                    $map = new \common\models\VendorCompany();
                    $map->vendor_id = $this->id;
                    $map->company_id = $cid;
                    $map->created_at = time();
                    $map->save(false);
                }
            }
        }
    }



}
