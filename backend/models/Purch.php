<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "purch".
 *
 * @property int $id
 * @property string|null $purch_no
 * @property string|null $purch_date
 * @property int|null $vendor_id
 * @property string|null $vendor_name
 * @property int|null $status
 * @property string|null $note
 * @property int|null $approve_status
 * @property float|null $total_amount
 * @property float|null $discount_amount
 * @property float|null $vat_amount
 * @property float|null $net_amount
 * @property string|null $ref_text
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 *
 * @property PurchLine[] $purchLines
 * @property PurchReq[] $purchReqs
 */
class Purch extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 2;

    const APPROVE_STATUS_PENDING = 0;
    const APPROVE_STATUS_APPROVED = 1;
    const APPROVE_STATUS_REJECTED = 2;

    /**
     * @var PurchLine[] $purchLines for form handling
     */
    public $purchLines = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch';
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
            [['purch_date'], 'safe'],
            [['vendor_id', 'status', 'approve_status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['total_amount', 'discount_amount', 'vat_amount', 'net_amount'], 'number'],
            [['purch_no', 'vendor_name', 'note'], 'string', 'max' => 255],
            [['purch_no'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_no' => 'เลขที่ใบสั่งซื้อ',
            'purch_date' => 'วันที่',
            'vendor_id' => 'รหัสผู้ขาย',
            'vendor_name' => 'ชื่อผู้ขาย',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
            'approve_status' => 'สถานะอนุมัติ',
            'total_amount' => 'ยอดรวม',
            'discount_amount' => 'ส่วนลด',
            'vat_amount' => 'VAT',
            'net_amount' => 'ยอดรวมสุทธิ',
           // 'ref_text' => 'อ้างอิง',
            'created_at' => 'วันที่สร้าง',
            'created_by' => 'สร้างโดย',
            'updated_at' => 'วันที่แก้ไข',
            'updated_by' => 'แก้ไขโดย',
        ];
    }

    /**
     * Gets query for [[PurchLines]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchLines()
    {
        return $this->hasMany(PurchLine::class, ['purch_id' => 'id']);
    }

    /**
     * Gets query for [[PurchReqs]] - Purchase requests that reference this PO
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchReqs()
    {
        return $this->hasMany(PurchReq::class, ['purch_id' => 'id']);
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        $statuses = [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_ACTIVE => 'ใช้งาน',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
        return $statuses[$this->status] ?? 'ไม่ระบุ';
    }

    /**
     * Get approve status label
     */
    public function getApproveStatusLabel()
    {
        $statuses = [
            self::APPROVE_STATUS_PENDING => 'รอพิจารณา',
            self::APPROVE_STATUS_APPROVED => 'อนุมัติ',
            self::APPROVE_STATUS_REJECTED => 'ไม่อนุมัติ',
        ];
        return $statuses[$this->approve_status] ?? 'ไม่ระบุ';
    }

    /**
     * Get approve status badge
     */
    public function getApproveStatusBadge()
    {
        $badges = [
            self::APPROVE_STATUS_PENDING => '<span class="badge badge-warning">รอพิจารณา</span>',
            self::APPROVE_STATUS_APPROVED => '<span class="badge badge-success">อนุมัติ</span>',
            self::APPROVE_STATUS_REJECTED => '<span class="badge badge-danger">ไม่อนุมัติ</span>',
        ];
        return $badges[$this->approve_status] ?? '<span class="badge badge-secondary">ไม่ระบุ</span>';
    }

    /**
     * Calculate total amount from purch lines
     */
    public function calculateTotalAmount()
    {
        $total = 0;
        foreach ($this->purchLines as $line) {
            $total += $line->line_total;
        }
        return $total;
    }

    /**
     * Before save
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->purch_no)) {
                $this->purch_no = $this->generatePurchNo();
            }
            return true;
        }
        return false;
    }

    /**
     * Generate purchase number
     */
    private function generatePurchNo()
    {
        $prefix = 'PO' . date('Ym');
        $lastRecord = self::find()
            ->where(['like', 'purch_no', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->purch_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%04d', $newNumber);
    }

    /**
     * Get related purchase requests
     */
    public function getRelatedPurchReqs()
    {
        return $this->getPurchReqs()->all();
    }
}