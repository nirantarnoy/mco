<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "stock_monthly_snapshot".
 *
 * @property int $id
 * @property int $product_id
 * @property int $warehouse_id
 * @property string|null $lot_no
 * @property float|null $qty
 * @property string $snapshot_period Format YYYY-MM
 * @property string|null $created_at
 *
 * @property Product $product
 * @property Warehouse $warehouse
 */
class StockMonthlySnapshot extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_monthly_snapshot';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'warehouse_id', 'snapshot_period'], 'required'],
            [['product_id', 'warehouse_id'], 'integer'],
            [['qty'], 'number'],
            [['created_at'], 'safe'],
            [['lot_no'], 'string', 'max' => 50],
            [['snapshot_period'], 'string', 'max' => 7],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'warehouse_id' => 'Warehouse ID',
            'lot_no' => 'Lot No',
            'qty' => 'Qty',
            'snapshot_period' => 'Snapshot Period',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
}
