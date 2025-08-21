<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "billing_invoices".
 *
 * @property int $id
 * @property string $billing_number
 * @property string $billing_date
 * @property int $customer_id
 * @property float|null $subtotal
 * @property float|null $discount_percent
 * @property float|null $discount_amount
 * @property float|null $vat_percent
 * @property float|null $vat_amount
 * @property float|null $total_amount
 * @property string|null $payment_due_date
 * @property int|null $credit_terms
 * @property string|null $notes
 * @property string|null $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property BillingInvoiceItems[] $billingInvoiceItems
 */
class BillingInvoices extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_ISSUED = 'issued';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'billing_invoices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_due_date', 'notes', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['total_amount'], 'default', 'value' => 0.00],
            [['vat_percent'], 'default', 'value' => 7.00],
            [['credit_terms'], 'default', 'value' => 30],
            [['status'], 'default', 'value' => 'issued'],
            [['billing_number', 'billing_date', 'customer_id'], 'required'],
            [['billing_date', 'payment_due_date', 'created_at', 'updated_at'], 'safe'],
            [['customer_id', 'credit_terms', 'created_by', 'updated_by'], 'integer'],
            [['subtotal', 'discount_percent', 'discount_amount', 'vat_percent', 'vat_amount', 'total_amount'], 'number'],
            [['notes', 'status'], 'string'],
            [['billing_number'], 'string', 'max' => 50],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
            [['billing_number'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'billing_number' => 'Billing Number',
            'billing_date' => 'Billing Date',
            'customer_id' => 'Customer ID',
            'subtotal' => 'Subtotal',
            'discount_percent' => 'Discount Percent',
            'discount_amount' => 'Discount Amount',
            'vat_percent' => 'Vat Percent',
            'vat_amount' => 'Vat Amount',
            'total_amount' => 'Total Amount',
            'payment_due_date' => 'Payment Due Date',
            'credit_terms' => 'Credit Terms',
            'notes' => 'Notes',
            'status' => 'Status',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[BillingInvoiceItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBillingInvoiceItems()
    {
        return $this->hasMany(BillingInvoiceItems::class, ['billing_invoice_id' => 'id']);
    }


    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_DRAFT => 'draft',
            self::STATUS_ISSUED => 'issued',
            self::STATUS_PAID => 'paid',
            self::STATUS_CANCELLED => 'cancelled',
        ];
    }

    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function setStatusToDraft()
    {
        $this->status = self::STATUS_DRAFT;
    }

    /**
     * @return bool
     */
    public function isStatusIssued()
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function setStatusToIssued()
    {
        $this->status = self::STATUS_ISSUED;
    }

    /**
     * @return bool
     */
    public function isStatusPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    public function setStatusToPaid()
    {
        $this->status = self::STATUS_PAID;
    }

    /**
     * @return bool
     */
    public function isStatusCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function setStatusToCancelled()
    {
        $this->status = self::STATUS_CANCELLED;
    }
}
