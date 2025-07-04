<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "purch_req".
 *
 * @property int $id
 * @property string|null $purch_req_no
 * @property string|null $purch_req_date
 * @property int|null $vendor_id
 * @property string|null $vendor_name
 * @property int|null $status
 * @property string|null $note
 * @property int|null $approve_status
 * @property float|null $total_amount
 * @property float|null $discount_amount
 * @property float|null $vat_amount
 * @property float|null $net_amount
 * @property string|null $total_text
 * @property int|null $purch_id
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 *
 * @property PurchReqLine[] $purchReqLines
 */
class PurchReq extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 2;

    const APPROVE_STATUS_PENDING = 0;
    const APPROVE_STATUS_APPROVED = 1;
    const APPROVE_STATUS_REJECTED = 2;

    /**
     * @var PurchReqLine[] $purchReqLines for form handling
     */
    public $purchReqLines = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_req';
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
            [['purch_req_date'], 'safe'],
            [['vendor_id', 'status', 'approve_status', 'purch_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['total_amount', 'discount_amount', 'vat_amount', 'net_amount'], 'number'],
            [['purch_req_no', 'vendor_name', 'note', 'total_text'], 'string', 'max' => 255],
            [['purch_req_no'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_req_no' => 'เลขที่ใบขอซื้อ',
            'purch_req_date' => 'วันที่',
            'vendor_id' => 'รหัสผู้ขาย',
            'vendor_name' => 'ชื่อผู้ขาย',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
            'approve_status' => 'สถานะอนุมัติ',
            'total_amount' => 'ยอดรวม',
            'discount_amount' => 'ส่วนลด',
            'vat_amount' => 'VAT',
            'net_amount' => 'ยอดรวมสุทธิ',
            'total_text' => 'ยอดรวม (ตัวอักษร)',
            'purch_id' => 'รหัสใบสั่งซื้อ',
            'created_at' => 'วันที่สร้าง',
            'created_by' => 'สร้างโดย',
            'updated_at' => 'วันที่แก้ไข',
            'updated_by' => 'แก้ไขโดย',
        ];
    }

    /**
     * Gets query for [[PurchReqLines]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurchReqLines()
    {
        return $this->hasMany(PurchReqLine::class, ['purch_req_id' => 'id']);
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
     * Calculate total amount from purch req lines
     */
    public function calculateTotalAmount()
    {
        $total = 0;
        foreach ($this->purchReqLines as $line) {
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
            if ($insert && empty($this->purch_req_no)) {
                $this->purch_req_no = $this->generatePurchReqNo();
            }

            // Convert amount to Thai text
            if (!empty($this->net_amount)) {
                $this->total_text = $this->convertAmountToThaiText($this->net_amount);
            }

            return true;
        }
        return false;
    }

    /**
     * Generate purchase request number
     */
    private function generatePurchReqNo()
    {
        $prefix = 'PR' . date('Ym');
        $lastRecord = self::find()
            ->where(['like', 'purch_req_no', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->purch_req_no, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . sprintf('%04d', $newNumber);
    }

    /**
     * Convert amount to Thai text (simplified version)
     */
    private function convertAmountToThaiText($amount)
    {
        // This is a simplified version - you can implement full Thai number conversion
        $formatter = new \NumberFormatter('th', \NumberFormatter::SPELLOUT);
        return $formatter->format($amount) . ' บาทถ้วน';
    }
}