<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "stock_sum".
 *
 * @property int $id
 * @property int|null $product_id
 * @property int|null $warehouse_id
 * @property float|null $qty
 * @property int|null $updated_at
 * @property float|null $reserv_qty
 *
 * @property Product $product
 * @property Warehouse $warehouse
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
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'warehouse_id', 'updated_at'], 'integer'],
            [['qty', 'reserv_qty'], 'number'],
            [['product_id', 'warehouse_id'], 'required'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['warehouse_id' => 'id']],
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
            'product_id' => 'รหัสสินค้า',
            'warehouse_id' => 'รหัสคลังสินค้า',
            'qty' => 'จำนวนคงเหลือ',
            'updated_at' => 'วันที่อัพเดท',
            'reserv_qty' => 'จำนวนจอง',
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Gets query for [[Warehouse]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }

    /**
     * Update stock quantity
     */
    public static function updateStock($productId, $warehouseId, $qty, $stockType)
    {
        $stockSum = self::find()
            ->where(['product_id' => $productId, 'warehouse_id' => $warehouseId])
            ->one();

        if (!$stockSum) {
            // Create new stock record
            $stockSum = new self();
            $stockSum->product_id = $productId;
            $stockSum->warehouse_id = $warehouseId;
            $stockSum->qty = 0;
            $stockSum->reserv_qty = 0;
        }

        // Update quantity based on stock type
        if ($stockType == JournalTrans::STOCK_TYPE_IN) {
            $stockSum->qty += $qty;
        } else {
            $stockSum->qty -= $qty;
        }

        return $stockSum->save();
    }

    /**
     * Get available quantity (qty - reserv_qty)
     */
    public function getAvailableQty()
    {
        return $this->qty - $this->reserv_qty;
    }
}