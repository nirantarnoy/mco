<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "stock_trans".
 *
 * @property int $id
 * @property int|null $journal_trans_id
 * @property string|null $trans_date
 * @property int|null $product_id
 * @property int|null $trans_type_id
 * @property float|null $qty
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $status
 * @property string|null $remark
 * @property int|null $stock_type_id
 * @property int|null $warehouse_id
 * @property float|null $line_price
 *
 * @property JournalTrans $journalTrans
 * @property Product $product
 * @property Warehouse $warehouse
 */
class StockTrans extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_trans';
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
            [['journal_trans_id', 'product_id', 'trans_type_id', 'created_at', 'created_by', 'status', 'stock_type_id', 'warehouse_id'], 'integer'],
            [['trans_date'], 'safe'],
            [['qty', 'line_price'], 'number'],
            [['remark'], 'string', 'max' => 255],
            [['journal_trans_id'], 'exist', 'skipOnError' => true, 'targetClass' => JournalTrans::class, 'targetAttribute' => ['journal_trans_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::class, 'targetAttribute' => ['warehouse_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'journal_trans_id' => 'รหัส Journal Transaction',
            'trans_date' => 'วันที่ทำรายการ',
            'product_id' => 'รหัสสินค้า',
            'trans_type_id' => 'ประเภทการทำรายการ',
            'qty' => 'จำนวน',
            'created_at' => 'วันที่สร้าง',
            'created_by' => 'สร้างโดย',
            'status' => 'สถานะ',
            'remark' => 'หมายเหตุ',
            'stock_type_id' => 'ประเภทสต๊อก',
            'warehouse_id' => 'รหัสคลังสินค้า',
            'line_price' => 'ราคาต่อหน่วย',
        ];
    }

    /**
     * Gets query for [[JournalTrans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJournalTrans()
    {
        return $this->hasOne(JournalTrans::class, ['id' => 'journal_trans_id']);
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
}