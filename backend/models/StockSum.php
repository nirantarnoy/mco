<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "stock_sum".
 *
 * @property int $id
 * @property int $product_id
 * @property int $warehouse_id
 * @property float $qty
 * @property string $updated_at
 * @property float $reserve_qty
 * @property string $created_at
 *
 * @property Product $product
 */
class StockSum extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_sum';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'warehouse_id'], 'required'],
            [['product_id', 'warehouse_id'], 'integer'],
            [['qty', 'reserv_qty'], 'number'],
            [['updated_at', 'created_at'], 'safe'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['product_id', 'warehouse_id'], 'unique', 'targetAttribute' => ['product_id', 'warehouse_id']],
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
            'qty' => 'Qty',
            'updated_at' => 'Updated At',
            'reserv_qty' => 'Reserve Qty',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Get available quantity (total qty - reserved qty)
     */
    public function getAvailableQty()
    {
        return $this->qty - $this->reserv_qty;
    }

    public static function updateStockIn($productId, $warehouseId, $qty, $stockType)
    {
        $stockSum = self::find()->where(['product_id' => $productId, 'warehouse_id' => $warehouseId])->one();
        if (!$stockSum) {
            $stockSum = new self();
            $stockSum->product_id = $productId;
            $stockSum->warehouse_id = $warehouseId;
            $stockSum->qty = $qty;
            $stockSum->reserv_qty = 0;
            $stockSum->created_at = date('Y-m-d H:i:s');
            if($stockSum->save(false)){
                self::updateProductStock($productId);
            }
        }else{
            $stockSum->qty += $qty;
            $stockSum->updated_at = date('Y-m-d H:i:s');
            if($stockSum->save(false)){
                self::updateProductStock($productId);
            }
        }
        // Calculate quantity change based on stock type
        return $stockSum;
    }

    protected static function updateProductStock($productId): bool
    {
        $totalStock = self::find()
            ->where(['product_id' => $productId])->sum('qty');
        $product = Product::findOne($productId);
        if ($product) {
            $product->stock_qty = $totalStock ?: 0;
            $product->updated_at = date('Y-m-d H:i:s');
            $product->save(false);
        }
        return true;
    }
}