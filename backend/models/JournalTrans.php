<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "journal_trans".
 *
 * @property int $id
 * @property string|null $trans_date
 * @property string|null $journal_no
 * @property int|null $trans_type_id
 * @property int|null $stock_type_id
 * @property int|null $customer_id
 * @property string|null $customer_name
 * @property float|null $qty
 * @property string|null $remark
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 * @property int|null $party_id
 * @property int|null $party_type_id
 * @property int|null $warehouse_id
 * @property int|null $trans_ref_id
 *
 * @property JournalTransLine[] $journalTransLines
 * @property StockTrans[] $stockTrans
 * @property Purch $purchaseOrder
 */
class JournalTrans extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 0;

    const TRANS_TYPE_PO_RECEIVE = 1;
    const TRANS_TYPE_PO_RETURN = 2;
    const TRANS_TYPE_SALE_ISSUE = 3;
    const TRANS_TYPE_SALE_RETURN = 4;
    const TRANS_TYPE_ADJUST_IN = 5;
    const TRANS_TYPE_ADJUST_OUT = 6;

    const STOCK_TYPE_IN = 1;
    const STOCK_TYPE_OUT = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'journal_trans';
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
            [['trans_date'], 'safe'],
            [['trans_type_id', 'stock_type_id', 'customer_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by', 'party_id', 'party_type_id', 'warehouse_id', 'trans_ref_id'], 'integer'],
            [['qty'], 'number'],
            [['journal_no', 'customer_name', 'remark'], 'string', 'max' => 255],
            [['journal_no'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'trans_date' => 'วันที่ทำรายการ',
            'journal_no' => 'เลขที่เอกสาร',
            'trans_type_id' => 'ประเภทการทำรายการ',
            'stock_type_id' => 'ประเภทสต๊อก',
            'customer_id' => 'รหัสลูกค้า',
            'customer_name' => 'ชื่อลูกค้า',
            'qty' => 'จำนวนรวม',
            'remark' => 'หมายเหตุ',
            'status' => 'สถานะ',
            'created_at' => 'วันที่สร้าง',
            'created_by' => 'สร้างโดย',
            'updated_at' => 'วันที่แก้ไข',
            'updated_by' => 'แก้ไขโดย',
            'party_id' => 'รหัสคู่ค้า',
            'party_type_id' => 'ประเภทคู่ค้า',
            'warehouse_id' => 'รหัสคลังสินค้า',
            'trans_ref_id' => 'อ้างอิงเอกสาร',
        ];
    }

    /**
     * Gets query for [[JournalTransLines]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJournalTransLines()
    {
        return $this->hasMany(JournalTransLine::class, ['journal_trans_id' => 'id']);
    }

    /**
     * Gets query for [[StockTrans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockTrans()
    {
        return $this->hasMany(StockTrans::class, ['journal_trans_id' => 'id']);
    }

    /**
     * Gets query for [[PurchaseOrder]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrder()
    {
        return $this->hasOne(Purch::class, ['id' => 'trans_ref_id'])
            ->where(['trans_type_id' => self::TRANS_TYPE_PO_RECEIVE]);
    }

    /**
     * Get transaction type label
     */
    public function getTransTypeLabel()
    {
        $types = [
            self::TRANS_TYPE_PO_RECEIVE => 'รับสินค้าจากใบสั่งซื้อ',
            self::TRANS_TYPE_PO_RETURN => 'คืนสินค้าให้ผู้ขาย',
            self::TRANS_TYPE_SALE_ISSUE => 'จ่ายสินค้าขาย',
            self::TRANS_TYPE_SALE_RETURN => 'รับคืนสินค้าจากลูกค้า',
            self::TRANS_TYPE_ADJUST_IN => 'ปรับปรุงสต๊อกเพิ่ม',
            self::TRANS_TYPE_ADJUST_OUT => 'ปรับปรุงสต๊อกลด',
        ];
        return $types[$this->trans_type_id] ?? 'ไม่ระบุ';
    }

    /**
     * Get stock type label
     */
    public function getStockTypeLabel()
    {
        $types = [
            self::STOCK_TYPE_IN => 'เข้า',
            self::STOCK_TYPE_OUT => 'ออก',
        ];
        return $types[$this->stock_type_id] ?? 'ไม่ระบุ';
    }

    /**
     * Before save
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->journal_no)) {
                $this->journal_no = $this->generateJournalNo();
            }
            return true;
        }
        return false;
    }

    /**
     * Generate journal number
     */
    private function generateJournalNo()
    {
        $prefix = 'JT' . date('Ym');
        $lastRecord = self::find()
            ->where(['like', 'journal_no', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->journal_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%04d', $newNumber);
    }
}