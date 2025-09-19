<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "purch_line".
 *
 * @property int $id
 * @property int|null $purch_id
 * @property int|null $product_id
 * @property string|null $product_name
 * @property string|null $product_description
 * @property int|null $product_type
 * @property float|null $qty
 * @property float|null $line_price
 * @property string|null $unit
 * @property float|null $line_total
 * @property int|null $status
 * @property string|null $note
 *
 * @property Purch $purch
 * @property Product $product
 */
class PurchLine extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_id', 'product_id', 'product_type', 'status'], 'integer'],
            [['qty', 'line_price', 'line_total'], 'number'],
            [['product_name',  'note','doc_ref_no'], 'string', 'max' => 255],
            [['qty', 'line_price'], 'required'],
            [['purch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Purch::class, 'targetAttribute' => ['purch_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['product_description', 'unit',],'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_id' => 'รหัสใบสั่งซื้อ',
            'product_id' => 'รหัสสินค้า',
            'product_name' => 'ชื่อสินค้า',
            'product_description' => 'รายละเอียด',
            'product_type' => 'ประเภทสินค้า',
            'qty' => 'จำนวน',
            'line_price' => 'ราคา/หน่วย',
            'unit' => 'หน่วยนับ',
            'line_total' => 'ราคารวม',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    /**
     * Gets query for [[Purch]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurch()
    {
        return $this->hasOne(Purch::class, ['id' => 'purch_id']);
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
            // Calculate line total
            $this->line_total = $this->qty * $this->line_price;
            return true;
        }
        return false;
    }
}