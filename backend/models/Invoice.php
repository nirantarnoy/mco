<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "invoices".
 *
 * @property int $id
 * @property string $invoice_type
 * @property string $invoice_number
 * @property string $invoice_date
 * @property string|null $customer_code
 * @property string $customer_name
 * @property string|null $customer_address
 * @property string|null $customer_tax_id
 * @property string|null $po_number
 * @property string|null $po_date
 * @property string|null $credit_terms
 * @property string|null $due_date
 * @property float $subtotal
 * @property float|null $discount_percent
 * @property float|null $discount_amount
 * @property float|null $vat_percent
 * @property float|null $vat_amount
 * @property float $total_amount
 * @property string|null $total_amount_text
 * @property string|null $payment_due_date
 * @property string|null $check_due_date
 * @property string|null $notes
 * @property int $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property InvoiceItem[] $items
 * @property Customer $customer
 */
class Invoice extends ActiveRecord
{
//    const TYPE_QUOTATION = 1;
//    const TYPE_BILL_PLACEMENT = 2;
//    const TYPE_TAX_INVOICE = 3;
//    const TYPE_RECEIPT = 4;
    const TYPE_QUOTATION = 'quotation';
    const TYPE_BILL_PLACEMENT = 'bill_placement';
    const TYPE_TAX_INVOICE = 'tax_invoice';
    const TYPE_RECEIPT = 'receipt';

    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoices';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_type', 'invoice_date', 'customer_name'], 'required'],
            [['status', 'job_id', 'payment_term_id','customer_id','quotation_id','pay_for_emp_id'], 'integer'],
            [['invoice_date', 'po_date', 'due_date', 'payment_due_date', 'check_due_date'], 'safe'],
            [['customer_address', 'notes'], 'string'],
            [['subtotal', 'discount_percent', 'discount_amount', 'vat_percent', 'vat_amount', 'total_amount'], 'number', 'min' => 0],
            [['discount_percent', 'vat_percent'], 'number', 'max' => 100],
            [['invoice_number', 'po_number'], 'string', 'max' => 50],
            [['customer_code', 'customer_tax_id'], 'string', 'max' => 20],
            [['customer_name'], 'string', 'max' => 255],
            [['credit_terms'], 'string', 'max' => 100],
            [['total_amount_text','special_note'], 'string', 'max' => 500],
            [['invoice_type'], 'in', 'range' => [self::TYPE_QUOTATION, self::TYPE_BILL_PLACEMENT, self::TYPE_TAX_INVOICE, self::TYPE_RECEIPT]],
            [['invoice_number'], 'unique', 'targetAttribute' => ['invoice_number', 'invoice_type']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'invoice_type' => 'ประเภทเอกสาร',
            'invoice_number' => 'เลขที่เอกสาร',
            'invoice_date' => 'วันที่',
            'customer_code' => 'รหัสลูกค้า',
            'customer_name' => 'ชื่อลูกค้า',
            'customer_address' => 'ที่อยู่',
            'customer_tax_id' => 'เลขประจำตัวผู้เสียภาษี',
            'po_number' => 'เลขที่ใบสั่งซื้อ',
            'po_date' => 'วันที่ใบสั่งซื้อ',
            'credit_terms' => 'เงื่อนไขการชำระ',
            'due_date' => 'วันครบกำหนด',
            'subtotal' => 'รวมเงิน',
            'discount_percent' => 'ส่วนลด %',
            'discount_amount' => 'จำนวนส่วนลด',
            'vat_percent' => 'ภาษี %',
            'vat_amount' => 'จำนวนภาษี',
            'total_amount' => 'รวมทั้งสิ้น',
            'total_amount_text' => 'ตัวอักษร',
            'payment_due_date' => 'วันนัดชำระเงิน',
            'check_due_date' => 'วันนัดรับเช็ค',
            'notes' => 'หมายเหตุ',
            'status' => 'สถานะ',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขล่าสุด',
            'job_id' => 'เลขงาน',
            'payment_term_id' => 'เงื่อนไขการชำระ',
            'quotation_id' => 'ใบเสนอราคา',
            'pay_for_emp_id' => 'พนักงาน',
            'customer_id' => 'ลูกค้า',
            'special_note' => 'บันทึกอื่นๆ'
        ];
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(InvoiceItem::class, ['invoice_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
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

    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['customer_code' => 'customer_code']);
    }

    public function getJob()
    {
        return $this->hasOne(Job::class, ['id' => 'job_id']);
    }

    public function getPaymentTerm()
    {
        return $this->hasOne(Paymentterm::class, ['id' => 'payment_term_id']);
    }

    /**
     * Get invoice type options
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_QUOTATION => 'ใบแจ้งหนี้',
            self::TYPE_BILL_PLACEMENT => 'ใบวางบิล',
            self::TYPE_TAX_INVOICE => 'ใบกำกับภาษี',
            self::TYPE_RECEIPT => 'ใบเสร็จรับเงิน',
        ];
    }

    /**
     * Get invoice type label
     */
    public function getTypeLabel()
    {
        $options = self::getTypeOptions();
        return isset($options[$this->invoice_type]) ? $options[$this->invoice_type] : $this->invoice_type;
    }

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'ใช้งาน',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        $options = self::getStatusOptions();
        return isset($options[$this->status]) ? $options[$this->status] : $this->status;
    }

    /**
     * Generate invoice number
     */
    public function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('n');

        // Get prefix based on type
        $prefixes = [
            self::TYPE_QUOTATION => 'QT',
            self::TYPE_BILL_PLACEMENT => 'BP',
            self::TYPE_TAX_INVOICE => 'IV',
            self::TYPE_RECEIPT => 'RE',
        ];

        $prefix = $prefixes[$this->invoice_type] ?? 'DOC';

        // Get or create sequence
        $sequence = Yii::$app->db->createCommand("
                SELECT last_number 
                FROM invoice_sequences 
                WHERE invoice_type = :type AND year = :year AND month = 0
            ")->bindValues([
                ':type' => $this->invoice_type,
                ':year' => $year,
            ])
            ->queryOne();

        if (!$sequence) {
            // Insert new sequence
            Yii::$app->db->createCommand()
                ->insert('invoice_sequences', [
                    'invoice_type' => $this->invoice_type,
                    'year' => $year,
                    'month' => 0,
                    'last_number' => 1,
                    'prefix' => $prefix
                ])
                ->execute();
            $nextNumber = 1;
        } else {
            $nextNumber = $sequence['last_number'] + 1;
            Yii::$app->db->createCommand()
                ->update('invoice_sequences',
                    ['last_number' => $nextNumber],
                    [
                        'invoice_type' => $this->invoice_type,
                        'year' => $year,
                        'month' => 0
                    ])
                ->execute();
        }

        if ($this->invoice_type == self::TYPE_BILL_PLACEMENT) {
            return $prefix . '-' . $year . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
        return $prefix . substr($year, -2) . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Before save
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->invoice_number)) {
                $this->invoice_number = $this->generateInvoiceNumber();
            }

            if ($this->quotation_id) {
                if ($this->invoice_type === self::TYPE_RECEIPT) {
                    $sourceInvoice = self::findOne($this->quotation_id);
                    if ($sourceInvoice) {
                        $this->customer_id = $sourceInvoice->customer_id;
                    }
                } else {
                    $quotation = Quotation::findOne($this->quotation_id);
                    if ($quotation) {
                        $this->customer_id = $quotation->customer_id;
                    }
                }
            }

            // Calculate amounts
            $this->calculateAmounts();
            $this->company_id = \Yii::$app->session->get('company_id');
            return true;
        }
        return false;
    }

    /**
     * Calculate amounts from items
     */
    public function calculateAmounts()
    {
        $subtotal = 0;
        $model_line = \backend\models\InvoiceItem::find()->where(['invoice_id' => $this->id])->all();
        foreach ($model_line as $item) {
            $subtotal += $item->amount;
        }

        $this->subtotal = $subtotal;

        // Calculate discount
        if ($this->discount_percent > 0) {
            $this->discount_amount = $subtotal * ($this->discount_percent / 100);
        }

        $afterDiscount = $subtotal - $this->discount_amount;

        // Calculate VAT
        if ($this->vat_percent > 0) {
            $this->vat_amount = $afterDiscount * ($this->vat_percent / 100);
        }

        $this->total_amount = $afterDiscount + $this->vat_amount;

        // Generate amount text
        $this->total_amount_text = \backend\models\PurchReq::numtothai($this->total_amount); // $this->numberToThaiText($this->total_amount);
    }

    /**
     * Update amounts from items
     */
    public function updateAmountsFromItems()
    {
        $this->refresh();
        $this->calculateAmounts();
        return $this->save(false);
    }

    /**
     * Convert number to Thai text
     */
    private function numberToThaiText($number)
    {
        // Simplified Thai number to text conversion
        // You might want to use a more comprehensive library
        $amount = number_format($number, 2, '.', '');
        list($baht, $satang) = explode('.', $amount);

        // Basic conversion - you should implement full Thai number conversion
        if ($satang == '00') {
            return 'จำนวน ' . number_format($baht) . ' บาทถ้วน';
        } else {
            return 'จำนวน ' . number_format($baht) . ' บาท ' . $satang . ' สตางค์';
        }
    }

    /**
     * Copy invoice to create new type
     */
    public function copyToNewType($newType)
    {
        $newInvoice = new self();
        $newInvoice->attributes = $this->attributes;
        $newInvoice->id = null;
        $newInvoice->invoice_type = $newType;
        $newInvoice->invoice_number = null; // Will be generated
        $newInvoice->created_at = null;
        $newInvoice->updated_at = null;

        if ($newInvoice->save()) {
            // Copy items
            foreach ($this->items as $item) {
                $newItem = new InvoiceItem();
                $newItem->attributes = $item->attributes;
                $newItem->id = null;
                $newItem->invoice_id = $newInvoice->id;
                $newItem->save();
            }

            // Create relation
            $relation = new InvoiceRelation();
            $relation->parent_invoice_id = $this->id;
            $relation->child_invoice_id = $newInvoice->id;
            $relation->relation_type = $this->invoice_type . '_to_' . $newType;
            $relation->save();

            return $newInvoice;
        }

        return false;
    }

    public function getBillingInvoiceItems()
    {
        return $this->hasMany(BillingInvoiceItem::class, ['invoice_id' => 'id']);
    }

    // Get unbilled invoices for a customer
    public static function getUnbilledInvoices($customerId)
    {
        return self::find()
            ->where(['customer_id' => $customerId, 'is_billed' => 0,'status' => 1])
            ->orderBy('invoice_date DESC')
            ->all();
    }

    public static function getQuotationNo($inv_id){
        $no = '';
        $model = \backend\models\Invoice::find()->where(['id'=>$inv_id])->one();
        if($model){
            $model_q = \backend\models\Quotation::find()->where(['id'=>$model->quotation_id])->one();
            if($model_q){
                $no = $model_q->quotation_no;
            }
        }
        return $no;
    }
}