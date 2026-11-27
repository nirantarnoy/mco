<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_company".
 *
 * @property int $id
 * @property int $product_id
 * @property int $company_id
 * @property int|null $created_at
 *
 * @property Product $product
 * @property Company $company
 */
class ProductCompany extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'company_id'], 'required'],
            [['product_id', 'company_id', 'created_at'], 'integer'],
            [['product_id', 'company_id'], 'unique', 'targetAttribute' => ['product_id', 'company_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'company_id' => 'Company ID',
            'created_at' => 'Created At',
        ];
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function getCompany()
    {
        return $this->hasOne(\backend\models\Company::class, ['id' => 'company_id']);
    }
}
