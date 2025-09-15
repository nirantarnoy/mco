<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * PaymentReceiptDetail Model
 *
 * @property int $id
 * @property int $payment_receipt_id
 * @property int $billing_invoice_item_id
 * @property string $description
 * @property float $amount
 * @property string $created_at
 */
class PaymentReceiptDetail extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_receipt_details';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_receipt_id', 'description', 'amount'], 'required'],
            [['payment_receipt_id', 'billing_invoice_item_id'], 'integer'],
            [['amount'], 'number'],
            [['created_at'], 'safe'],
            [['description'], 'string', 'max' => 500],
            [['payment_receipt_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentReceipt::class, 'targetAttribute' => ['payment_receipt_id' => 'id']],
            // [['billing_invoice_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => BillingInvoiceItem::class, 'targetAttribute' => ['billing_invoice_item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_receipt_id' => 'ใบเสร็จรับเงิน',
            'billing_invoice_item_id' => 'รายการใบแจ้งหนี้',
            'description' => 'รายละเอียด',
            'amount' => 'จำนวนเงิน',
            'created_at' => 'วันที่สร้าง',
        ];
    }

    /**
     * Gets query for [[PaymentReceipt]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentReceipt()
    {
        return $this->hasOne(PaymentReceipt::class, ['id' => 'payment_receipt_id']);
    }

    /**
     * Gets query for [[BillingInvoiceItem]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBillingInvoiceItem()
    {
        return $this->hasOne(BillingInvoiceItem::class, ['id' => 'billing_invoice_item_id']);
    }
}
