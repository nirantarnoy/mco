<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "purch_req_line".
 *
 * @property int $id
 * @property int|null $purch_req_id
 * @property int|null $product_id
 * @property string|null $product_name
 * @property string|null $product_description
 * @property int|null $product_type
 * @property float|null $qty
 * @property float|null $line_price
 * @property float|null $line_total
 * @property int|null $status
 * @property string|null $note
 * @property string|null $unit
 *
 * @property PurchReq $purchReq
 * @property Product $product
 */
class PurchReqLine extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_req_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_req_id', 'product_id', 'product_type', 'status'], 'integer'],
            [['qty', 'line_price', 'line_total'], 'number'],
            [['product_name', 'note', 'unit_id','doc_ref_no','product_description'], 'string', 'max' => 255],
            [['qty', 'line_price'], 'required'],
            [['purch_req_id'], 'exist', 'skipOnError' => true, 'targetClass' => PurchReq::class, 'targetAttribute' => ['purch_req_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_req_id' => 'รหัสใบขอซื้อ',
            'product_id' => 'รหัสสินค้า',
            'product_name' => 'ชื่อสินค้า',
            'product_type' => 'ประเภทสินค้า',
            'qty' => 'จำนวน',
            'line_price' => 'ราคา/หน่วย',
            'line_total' => 'ราคารวม',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
            'unit_id' => 'หน่วยนับ',
            'product_description'=>'รายละเอียด',
        ];
    }

    /**
     * Gets query for [[PurchReq]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchReq()
    {
        return $this->hasOne(PurchReq::class, ['id' => 'purch_req_id']);
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