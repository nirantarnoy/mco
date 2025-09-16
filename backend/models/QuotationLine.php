<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "quotation_line".
 *
 * @property int $id
 * @property int|null $quotation_id
 * @property int|null $product_id
 * @property string|null $product_name
 * @property float|null $qty
 * @property float|null $line_price
 * @property float|null $line_total
 * @property float|null $discount_amount
 * @property int|null $status
 * @property string|null $note
 *
 * @property Quotation $quotation
 * @property Product $product
 */
class QuotationLine extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'quotation_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quotation_id', 'product_id', 'status'], 'integer'],
            [['qty', 'line_price', 'line_total', 'discount_amount'], 'number'],
            [['product_name', 'note'], 'string', 'max' => 255],
            [['qty', 'line_price'], 'required'],
         //   [['quotation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quotation::class, 'targetAttribute' => ['quotation_id' => 'id']],
          //  [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'quotation_id' => 'รหัสใบเสนอราคา',
            'product_id' => 'รหัสสินค้า',
            'product_name' => 'ชื่อสินค้า/รายละเอียด',
            'qty' => 'จำนวน',
            'line_price' => 'ราคาต่อหน่วย',
            'line_total' => 'ราคารวม',
            'discount_amount' => 'ส่วนลด',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    /**
     * Gets query for [[Quotation]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuotation()
    {
        return $this->hasOne(Quotation::class, ['id' => 'quotation_id']);
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
     * Before save
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Calculate line total (price * qty - discount)
            $subtotal = $this->qty * $this->line_price;
            $this->line_total = $subtotal - ($this->discount_amount ?: 0);
            return true;
        }
        return false;
    }

}