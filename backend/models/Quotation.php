<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "quotation".
 *
 * @property int $id
 * @property string|null $quotation_no
 * @property string|null $quotation_date
 * @property int|null $customer_id
 * @property string|null $customer_name
 * @property int|null $status
 * @property int|null $approve_status
 * @property int|null $approve_by
 * @property float|null $total_amount
 * @property string|null $total_amount_text
 * @property string|null $note
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 *
 * @property QuotationLine[] $quotationLines
 */
class Quotation extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 2;

    const APPROVE_STATUS_PENDING = 0;
    const APPROVE_STATUS_APPROVED = 1;
    const APPROVE_STATUS_REJECTED = 2;

    /**
     * @var QuotationLine[] $quotationLines for form handling
     */
    public $quotationLines = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'quotation';
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
            [['customer_id'], 'required'],
            [['quotation_date', 'currency_id', 'customer_tax_id'], 'safe'],
            [['customer_id', 'status', 'approve_status', 'approve_by', 'created_at', 'created_by', 'updated_at', 'updated_by', 'payment_term_id', 'payment_method_id', 'sale_emp_id'], 'integer'],
            [['total_amount', 'discount_amount', 'discount_percent', 'total_discount_amount', 'vat_percent', 'vat_total_amount'], 'number'],
            [['quotation_no', 'customer_name', 'total_amount_text', 'note', 'delivery_day_text', 'payment_term_text'], 'string', 'max' => 255],
            [['quotation_no'], 'unique', 'filter' => ['status' => self::STATUS_ACTIVE]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'quotation_no' => 'เลขที่ใบเสนอราคา',
            'quotation_date' => 'วันที่',
            'customer_id' => 'รหัสลูกค้า',
            'customer_name' => 'ชื่อลูกค้า',
            'status' => 'สถานะ',
            'approve_status' => 'สถานะอนุมัติ',
            'approve_by' => 'อนุมัติโดย',
            'total_amount' => 'ยอดรวม',
            'total_amount_text' => 'จำนวนเงิน (ตัวอักษร)',
            'note' => 'หมายเหตุ',
            'created_at' => 'วันที่สร้าง',
            'created_by' => 'สร้างโดย',
            'updated_at' => 'วันที่แก้ไข',
            'updated_by' => 'แก้ไขโดย',
            'payment_term_id' => 'เงื่อนไขชำระเงิน',
            'payment_term_text' => 'เงื่อนไขชำระเงิน',
            'payment_method_id' => 'วิธีชำระเงิน',
            'delivery_day_text' => 'กำหนดส่งของ',
            'sale_emp_id' => 'พนักงานขาย',
            'discount_amount' => 'ส่วนลด(จำนวนเงิน)',
            'discount_percent' => 'ส่วนลด %',
            'total_discount_amount' => 'รวมส่วนลด',
            'vat_percent' => 'VAT %',
            'vat_total_amount' => 'รวม VAT',
            'currency_id' => 'สกุลเงิน',
            'customer_tax_id' => 'เลขประจำตัวผู้เสียภาษี'
        ];
    }

    //    public function getQuotation(){
    //        return $this->hasOne(Quotation::className(), ['id' => 'quotation_id']);
    //    }
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[QuotationLines]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuotationLines()
    {
        return $this->hasMany(QuotationLine::class, ['quotation_id' => 'id']);
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
            self::APPROVE_STATUS_PENDING => '<span class="badge bg-warning">รอพิจารณา</span>',
            self::APPROVE_STATUS_APPROVED => '<span class="badge bg-success">อนุมัติ</span>',
            self::APPROVE_STATUS_REJECTED => '<span class="badge bg-danger">ไม่อนุมัติ</span>',
        ];
        return $badges[$this->approve_status] ?? '<span class="badge bg-secondary">ไม่ระบุ</span>';
    }

    /**
     * Calculate total amount from quotation lines
     */
    public function calculateTotalAmount()
    {
        $total = 0;
        foreach ($this->quotationLines as $line) {
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
            if ($insert && empty($this->quotation_no)) {
                $this->quotation_no = $this->generateQuotationNo();
            }

            // Convert amount to Thai text
            if (!empty($this->total_amount)) {
                $this->total_amount_text = $this->convertAmountToThaiText($this->total_amount);
            }
            $this->company_id = \Yii::$app->session->get('company_id');
            return true;
        }

        return false;
    }

    /**
     * Generate quotation number
     */
    private function generateQuotationNo()
    {
        $prefix = 'QT' . date('Ym');
        $lastRecord = self::find()
            ->where(['like', 'quotation_no', $prefix])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($lastRecord) {
            $lastNumber = intval(substr($lastRecord->quotation_no, -4));
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
        // $formatter = new \NumberFormatter('th', \NumberFormatter::SPELLOUT);
        // return $formatter->format($amount) . ' บาทถ้วน';

        return $this->numtothai($amount);
    }

    /**
     * Get approve by name
     */
    public function getApproveByName()
    {
        // You can implement user lookup here
        return $this->approve_by ? 'User ID: ' . $this->approve_by : null;
    }

    public function numtothai($num)
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

        $return .= $this->numtothaistring($number[0]) . "บาท";

        $stang = intval($number[1]);

        if ($stang > 0) {
            $return .= $this->numtothaistring($number[1]) . "สตางค์";
        } else {
            $return .= "ถ้วน";
        }

        return $return;
    }

    public function numtothaistring($num)
    {
        $txtnum1 = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
        $txtnum2 = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');

        $num = str_replace(',', '', $num);
        $num = ltrim($num, '0');
        if ($num == '') return 'ศูนย์';

        $return_str = '';
        $len = strlen($num);

        for ($i = 0; $i < $len; $i++) {
            $digit = intval($num[$i]);
            $position = $len - $i - 1;
            $val = $position % 6;

            if ($digit == 0) {
                if ($position > 0 && $position % 6 == 0) {
                    $return_str .= 'ล้าน';
                }
                continue;
            }

            if ($digit == 1 && $val == 0) {
                if ($i == $len - 1 && $len > 1) {
                    $return_str .= 'เอ็ด';
                } elseif ($i != 0 && $position % 6 == 0) {
                    $return_str .= 'เอ็ด';
                } else {
                    $return_str .= 'หนึ่ง';
                }
            } elseif ($digit == 2 && $val == 1) {
                $return_str .= 'ยี่' . $txtnum2[$val];
            } elseif ($digit == 1 && $val == 1) {
                $return_str .= $txtnum2[$val];
            } else {
                $return_str .= $txtnum1[$digit] . $txtnum2[$val];
            }

            if ($position > 0 && $position % 6 == 0) {
                $return_str .= 'ล้าน';
            }
        }

        return $return_str;
    }

    public function getJob()
    {
        return $this->hasOne(Job::class, ['id' => 'job_id']);
    }

    public static function findCustomerData($quotation_id)
    {
        $data = [];
        $quotation = Quotation::find()->where(['id' => $quotation_id])->one();
        if ($quotation) {
            $customer_data = \backend\models\Customer::find()->where(['id' => $quotation->customer_id])->one();
            if ($customer_data) {
                // ใช้ AddressHelper จัดรูปแบบที่อยู่ (แยกกรุงเทพฯ กับจังหวัดอื่นอัตโนมัติ)
                $formattedAddress = \backend\helpers\AddressHelper::formatCustomerAddress($customer_data);

                array_push($data, [
                    'customer_name' => $customer_data->name,
                    'customer_address' => $formattedAddress,
                    'customer_tax_id' => $customer_data->taxid,
                    'invoice_due_date' => self::calDueDate($quotation->payment_term_id),
                ]);
            }
        }
        return $data;
    }

    public static function calDueDate($payment_term_id)
    {
        $due_date = null;
        if ($payment_term_id) {
            $payment_term = \backend\models\Paymentterm::find()->where(['id' => $payment_term_id])->one();
            if ($payment_term) {
                $due_date = $payment_term->day_count == 0 || $payment_term->day_count == null ? null : date('Y-m-d', strtotime('+' . $payment_term->day_count . ' day'));
            }
        }
        return $due_date;
    }

    public static function findCustomerData2($quotation_id)
    {
        $data = [];
        $quotation = Quotation::find()->where(['id' => $quotation_id])->one();
        if ($quotation) {
            $customer_data = \backend\models\Customer::find()->where(['id' => $quotation->customer_id])->one();
            if ($customer_data) {
                array_push($data, [
                    'customer_name' => $customer_data->name,
                ]);
            }
        }
        return $data;
    }

    public static function findNo($id)
    {
        $quotation = Quotation::find()->where(['id' => $id])->one();
        if ($quotation) {
            return $quotation->quotation_no;
        } else {
            return '';
        }
    }

    public static function findDate($id)
    {
        $quotation = Quotation::find()->where(['id' => $id])->one();
        if ($quotation) {
            return $quotation->quotation_date;
        } else {
            return '';
        }
    }
}
