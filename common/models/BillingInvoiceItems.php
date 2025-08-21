<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "billing_invoice_items".
 *
 * @property int $id
 * @property int $billing_invoice_id
 * @property int $invoice_id
 * @property int $item_seq
 * @property float $amount
 * @property int|null $sort_order
 *
 * @property BillingInvoices $billingInvoice
 * @property Invoices $invoice
 */
class BillingInvoiceItems extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'billing_invoice_items';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort_order'], 'default', 'value' => 0],
            [['billing_invoice_id', 'invoice_id', 'item_seq', 'amount'], 'required'],
            [['billing_invoice_id', 'invoice_id', 'item_seq', 'sort_order'], 'integer'],
            [['amount'], 'number'],
            [['billing_invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => BillingInvoices::class, 'targetAttribute' => ['billing_invoice_id' => 'id']],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoices::class, 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'billing_invoice_id' => 'Billing Invoice ID',
            'invoice_id' => 'Invoice ID',
            'item_seq' => 'Item Seq',
            'amount' => 'Amount',
            'sort_order' => 'Sort Order',
        ];
    }

    /**
     * Gets query for [[BillingInvoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBillingInvoice()
    {
        return $this->hasOne(BillingInvoices::class, ['id' => 'billing_invoice_id']);
    }

    /**
     * Gets query for [[Invoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoices::class, ['id' => 'invoice_id']);
    }

}
