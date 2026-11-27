<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "customer_company".
 *
 * @property int $id
 * @property int $customer_id
 * @property int $company_id
 * @property int|null $created_at
 *
 * @property Customer $customer
 * @property Company $company
 */
class CustomerCompany extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'company_id'], 'required'],
            [['customer_id', 'company_id', 'created_at'], 'integer'],
            [['customer_id', 'company_id'], 'unique', 'targetAttribute' => ['customer_id', 'company_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'company_id' => 'Company ID',
            'created_at' => 'Created At',
        ];
    }

    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    public function getCompany()
    {
        return $this->hasOne(\backend\models\Company::class, ['id' => 'company_id']);
    }
}
