<?php
namespace backend\models;

use NumberFormatter;
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
    const STATUS_CANCELLED = 3;

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
            'timestampcdate'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>'created_at',
                ],
                'value'=> time(),
            ],
            'timestampudate'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>'updated_at',
                ],
                'value'=> time(),
            ],
            'timestampcby'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>'created_by',
                ],
                'value'=> Yii::$app->user->id,
            ],
            'timestamuby'=>[
                'class'=> \yii\behaviors\AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_UPDATE=>'updated_by',
                ],
                'value'=> Yii::$app->user->id,
            ],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vendor_id','job_id'], 'required'],
            [['purch_req_date'], 'safe'],
            [['vendor_id', 'status', 'approve_status', 'purch_id', 'created_at', 'created_by', 'updated_at', 'updated_by','job_id','discount_percent','vat_percent','approve_by','reason_title_id','req_for_dep_id'], 'integer'],
            [['total_amount', 'discount_amount', 'vat_amount', 'net_amount','vat_percent','discount_total_amount','discount_percent'], 'number'],
            [['purch_req_no', 'vendor_name', 'note', 'total_text','reason'], 'string', 'max' => 255],
            [['purch_req_no'], 'unique'],
            [['required_date','approve_date'], 'safe'],
            [['is_vat'],'integer'],
            [['special_note'],'string','max'=>500]
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
            'job_id' => 'ใบงาน',
            'approve_status' => 'สถานะอนุมัติ',
            'total_amount' => 'ยอดรวม',
            'discount_amount' => 'ส่วนลด',
            'vat_amount' => 'VAT',
            'net_amount' => 'ยอดรวมสุทธิ',
            'approve_by' => 'ผู้อนุมัติ',
            'total_text' => 'ยอดรวม (ตัวอักษร)',
            'purch_id' => 'รหัสใบสั่งซื้อ',
            'created_at' => 'วันที่สร้าง',
            'created_by' => 'สร้างโดย',
            'updated_at' => 'วันที่แก้ไข',
            'updated_by' => 'แก้ไขโดย',
            'required_date' => 'วันที่ต้องการ',
            'reason' => 'ระบุเหตุผล',
            'reason_title_id' => 'เหตุผลขอซื้อ',
            'req_for_dep_id' => 'ค่าใช้จ่ายสําหรับแผนก',
            'approve_date'=> 'วันที่อนุมัติใบขอซื้อ',
            'is_vat' => 'มี VAT',
            'vat_percent' => 'VAT %',
            'special_note' => 'บันทึกอื่นๆ',
            'discount_percent'=>'Discount Percent',
            'discount_total_amount'=>'Discount Total Amount',
        ];
    }

    /**
     * Gets query for [[PurchReqLines]].
     *
     * @return \yii\db\ActiveQuery
     */


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
            self::STATUS_CANCELLED => 'ยกเลิก',
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
            self::STATUS_CANCELLED => '<span class="badge badge-danger">ยกเลิก</span>',
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
        $formatter = new NumberFormatter('th', NumberFormatter::SPELLOUT);
        return $formatter->format($amount) . ' บาทถ้วน';
    }

    public function getPurchReqLines()
    {
        return $this->hasMany(PurchReqLine::class, ['purch_req_id' => 'id']);
    }
    public function getJob(){
        return $this->hasOne(Job::class, ['id' => 'job_id']);
    }
    public static function numtothai($num)
    {
        $return = "";
        $num = str_replace(",", "", $num);
        $number = explode(".", $num);

        if (count($number) > 2) {
            return 'รูปแบบข้อมูลไม่ถูกต้อง';
        }

        if (count($number) == 1) {
            $number[1] = "00";
        } else {
            $number[1] = str_pad(substr($number[1], 0, 2), 2, "0", STR_PAD_RIGHT);
        }

        $return .= self::numtothaistring($number[0]) . "บาท";

        $stang = intval($number[1]);

        if ($stang > 0) {
            $return .= self::numtothaistring($number[1]) . "สตางค์";
        } else {
            $return .= "ถ้วน";
        }

        return $return;
    }

    public static function numtothaistring($num)
    {
        $txtnum1 = array('', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
        $txtnum2 = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');

        $num = ltrim($num, '0');
        if ($num == '') return 'ศูนย์';

        $return_str = '';
        $len = strlen($num);
        for ($i = 0; $i < $len; $i++) {
            $digit = intval($num[$i]);
            $position = $len - $i - 1;

            if ($digit == 0) continue;

            if ($position == 0 && $digit == 1 && $len > 1) {
                $return_str .= 'เอ็ด';
            } else if ($position == 1 && $digit == 2) {
                $return_str .= 'ยี่' . $txtnum2[$position];
            } else if ($position == 1 && $digit == 1) {
                $return_str .= $txtnum2[$position];
            } else {
                $return_str .= $txtnum1[$digit] . $txtnum2[$position];
            }
        }

        return $return_str;
    }
}