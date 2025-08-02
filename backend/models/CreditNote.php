<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "credit_note".
 *
 * @property int $id
 * @property string $document_no
 * @property string $document_date
 * @property int $customer_id
 * @property int|null $invoice_id
 * @property string|null $original_invoice_no
 * @property string|null $original_invoice_date
 * @property float|null $original_amount
 * @property float|null $actual_amount
 * @property float $adjust_amount
 * @property float|null $vat_percent
 * @property float|null $vat_amount
 * @property float $total_amount
 * @property string $reason
 * @property string|null $amount_text
 * @property string|null $status
 * @property int|null $approved_by
 * @property string|null $approved_date
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property Customer $customer
 * @property Invoice $invoice
 * @property CreditNoteItem[] $creditNoteItems
 */
class CreditNote extends \yii\db\ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'credit_note';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['document_no', 'document_date', 'customer_id', 'reason'], 'required'],
            [['document_date', 'original_invoice_date', 'approved_date', 'created_at', 'updated_at'], 'safe'],
            [['customer_id', 'invoice_id', 'approved_by', 'created_by', 'updated_by'], 'integer'],
            [['original_amount', 'actual_amount', 'adjust_amount', 'vat_percent', 'vat_amount', 'total_amount'], 'number'],
            [['reason'], 'string'],
            [['document_no', 'original_invoice_no'], 'string', 'max' => 20],
            [['amount_text'], 'string', 'max' => 255],
            [['status'], 'string'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_APPROVED, self::STATUS_CANCELLED]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['vat_percent'], 'default', 'value' => 7],
            [['document_no'], 'unique'],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'document_no' => 'เลขที่เอกสาร',
            'document_date' => 'วันที่',
            'customer_id' => 'ลูกค้า',
            'invoice_id' => 'ใบแจ้งหนี้',
            'original_invoice_no' => 'เลขที่ใบกำกับภาษีเดิม',
            'original_invoice_date' => 'วันที่ใบกำกับภาษีเดิม',
            'original_amount' => 'มูลค่าสินค้าตามใบกำกับฯเดิม',
            'actual_amount' => 'มูลค่าสินค้าตามจริง',
            'adjust_amount' => 'รวมมูลค่าลดหนี้',
            'vat_percent' => 'ภาษีมูลค่าเพิ่ม %',
            'vat_amount' => 'ภาษีมูลค่าเพิ่ม',
            'total_amount' => 'รวมเป็นเงินทั้งสิ้น',
            'reason' => 'เหตุผลที่ต้องลดหนี้',
            'amount_text' => 'จำนวนเงิน (ตัวอักษร)',
            'status' => 'สถานะ',
            'approved_by' => 'อนุมัติโดย',
            'approved_date' => 'วันที่อนุมัติ',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
            'created_by' => 'สร้างโดย',
            'updated_by' => 'แก้ไขโดย',
        ];
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[Invoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }

    /**
     * Gets query for [[CreditNoteItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreditNoteItems()
    {
        return $this->hasMany(CreditNoteItem::class, ['credit_note_id' => 'id'])->orderBy(['item_no' => SORT_ASC]);
    }

    /**
     * Generate document number
     */
    public function generateDocumentNo()
    {
        $year = date('y');
        $sequence = DocumentSequence::findOne(['document_type' => 'credit_note']);

        if ($sequence->last_year != date('Y')) {
            $sequence->last_number = 0;
            $sequence->last_year = date('Y');
        }

        $sequence->last_number += 1;
        $sequence->save();

        $running = str_pad($sequence->last_number, $sequence->running_length, '0', STR_PAD_LEFT);
        $this->document_no = $sequence->prefix . '-' . $year . '-' . $running;
    }

    /**
     * Calculate totals
     */
    public function calculateTotals()
    {
        $subtotal = 0;
        foreach ($this->creditNoteItems as $item) {
            // Calculate item amount including discount
            $itemTotal = ($item->quantity * $item->unit_price) - $item->discount_amount;
            $item->amount = $itemTotal;
            $item->save(false);

            $subtotal += $itemTotal;
        }

        $this->adjust_amount = $subtotal;
        $this->vat_amount = $this->adjust_amount * ($this->vat_percent / 100);
        $this->total_amount = $this->adjust_amount + $this->vat_amount;

        // Convert amount to text
        $this->amount_text = $this->convertAmountToText($this->total_amount);
    }

    /**
     * Convert amount to Thai text
     */
    protected function convertAmountToText($amount)
    {
        $amount = number_format($amount, 2, '.', '');
        $parts = explode('.', $amount);
        $baht = $parts[0];
        $satang = $parts[1];

        $bahtText = $this->numberToThaiText($baht);
        $satangText = $satang == '00' ? '' : $this->numberToThaiText($satang) . 'สตางค์';

        return $bahtText . 'บาท' . $satangText;
    }

    /**
     * Convert number to Thai text
     */
    protected function numberToThaiText($number)
    {
        $number = ltrim($number, '0');
        if (empty($number)) return 'ศูนย์';

        $digits = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
        $units = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];

        $length = strlen($number);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $digit = (int)$number[$i];
            $position = $length - $i - 1;

            if ($digit == 0) continue;

            if ($position == 0 && $digit == 1 && $length > 1) {
                $result .= 'เอ็ด';
            } elseif ($position == 1 && $digit == 1) {
                $result .= 'สิบ';
            } elseif ($position == 1 && $digit == 2) {
                $result .= 'ยี่สิบ';
            } else {
                $result .= $digits[$digit] . $units[$position % 6];
            }
        }

        return $result;
    }

    /**
     * Status list
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_DRAFT => 'ฉบับร่าง',
            self::STATUS_APPROVED => 'อนุมัติแล้ว',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        $statuses = self::getStatusList();
        return isset($statuses[$this->status]) ? $statuses[$this->status] : '';
    }

    /**
     * Get status color
     */
    public function getStatusColor()
    {
        $colors = [
            self::STATUS_DRAFT => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_CANCELLED => 'danger',
        ];

        return isset($colors[$this->status]) ? $colors[$this->status] : 'default';
    }
}