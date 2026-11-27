<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "vendor_company".
 *
 * @property int $id
 * @property int $vendor_id
 * @property int $company_id
 * @property int|null $created_at
 *
 * @property Vendor $vendor
 * @property Company $company
 */
class VendorCompany extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vendor_company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id', 'company_id'], 'required'],
            [['vendor_id', 'company_id', 'created_at'], 'integer'],
            [['vendor_id', 'company_id'], 'unique', 'targetAttribute' => ['vendor_id', 'company_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vendor_id' => 'Vendor ID',
            'company_id' => 'Company ID',
            'created_at' => 'Created At',
        ];
    }

    public function getVendor()
    {
        return $this->hasOne(Vendor::class, ['id' => 'vendor_id']);
    }

    public function getCompany()
    {
        return $this->hasOne(\backend\models\Company::class, ['id' => 'company_id']);
    }
}
