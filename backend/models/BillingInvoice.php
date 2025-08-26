<?php
// common/models/BillingInvoice.php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

class BillingInvoice extends ActiveRecord
{
    public $selectedInvoices = []; // For form handling

    public static function tableName()
    {
        return 'billing_invoices';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => date('Y-m-d H:i:s'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    public function rules()
    {
        return [
            [['billing_number', 'billing_date', 'customer_id'], 'required'],
            [['billing_number'], 'string', 'max' => 50],
            [['billing_number'], 'unique'],
            [['billing_date', 'payment_due_date'], 'date', 'format' => 'php:Y-m-d'],
            [['customer_id', 'credit_terms'], 'integer'],
            [['subtotal', 'discount_amount', 'vat_amount', 'total_amount'], 'number'],
            [['discount_percent', 'vat_percent'], 'number', 'max' => 100],
            [['status'], 'in', 'range' => ['draft', 'issued', 'paid', 'cancelled']],
            [['notes'], 'string'],
            [['selectedInvoices'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'billing_number' => 'เลขที่ใบวางบิล',
            'billing_date' => 'วันที่',
            'customer_id' => 'รหัสลูกค้า',
            'subtotal' => 'ยอดรวม',
            'discount_percent' => 'ส่วนลด (%)',
            'discount_amount' => 'ยอดส่วนลด',
            'vat_percent' => 'ภาษี (%)',
            'vat_amount' => 'ยอดภาษี',
            'total_amount' => 'ยอดรวมทั้งสิ้น',
            'credit_terms' => 'เครดิต (วัน)',
            'payment_due_date' => 'กำหนดชำระ',
            'status' => 'สถานะ',
            'selectedInvoices' => 'เลือกใบแจ้งหนี้',
        ];
    }

    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    public function getBillingInvoiceItems()
    {
        return $this->hasMany(BillingInvoiceItem::class, ['billing_invoice_id' => 'id'])->orderBy('sort_order');
    }

    public function getInvoices()
    {
        return $this->hasMany(Invoice::class, ['id' => 'invoice_id'])
            ->viaTable('billing_invoice_items', ['billing_invoice_id' => 'id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->credit_terms && $this->billing_date) {
                $this->payment_due_date = date('Y-m-d', strtotime($this->billing_date . ' +' . $this->credit_terms . ' days'));
            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert && !empty($this->selectedInvoices)) {
            $this->saveSelectedInvoices();
        }
    }

    public function saveSelectedInvoices()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Clear existing items
            BillingInvoiceItem::deleteAll(['billing_invoice_id' => $this->id]);

            $totalAmount = 0;
            $seq = 1;

            foreach ($this->selectedInvoices as $invoiceId) {
                $invoice = Invoice::findOne($invoiceId);
                if ($invoice) {
                    $item = new BillingInvoiceItem();
                    $item->billing_invoice_id = $this->id;
                    $item->invoice_id = $invoiceId;
                    $item->item_seq = $seq++;
                    $item->amount = $invoice->total_amount;
                    $item->save();

                    // Mark invoice as billed
                    $invoice->is_billed = 1;
                    $invoice->status = 1;
                    $invoice->save();

                    $totalAmount += $invoice->total_amount;
                }
            }

            // Update billing totals
            $this->subtotal = $totalAmount;
           // $this->discount_amount = $this->subtotal * ($this->discount_percent / 100);
            $afterDiscount = $this->subtotal - $this->discount_amount;
            $this->vat_amount = $afterDiscount * ($this->vat_percent / 100);
            $this->total_amount = $afterDiscount + $this->vat_amount;
            $this->save(false);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public static function generateBillingNumber()
    {
        $prefix = 'BP-' . date('Y') . '-';
        $lastBilling = self::find()
            ->where(['like', 'billing_number', $prefix])
            ->orderBy('id DESC')
            ->one();

        if ($lastBilling) {
            $lastNumber = (int) str_replace($prefix, '', $lastBilling->billing_number);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}