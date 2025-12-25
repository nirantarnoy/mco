<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "invoice_payment_history".
 *
 * @property int $id
 * @property int $invoice_id
 * @property int $receipt_id
 * @property float $amount
 * @property string $payment_date
 * @property string|null $note
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 * @property int|null $company_id
 *
 * @property Invoice $invoice
 * @property Invoice $receipt
 */
class InvoicePaymentHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice_payment_history';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => function() { return date('Y-m-d H:i:s'); },
            ],
            [
                'class' => BlameableBehavior::class,
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_id', 'receipt_id', 'amount', 'payment_date'], 'required'],
            [['invoice_id', 'receipt_id', 'created_by', 'company_id'], 'integer'],
            [['amount'], 'number'],
            [['payment_date', 'created_at', 'updated_at'], 'safe'],
            [['note'], 'string', 'max' => 255],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['invoice_id' => 'id']],
            [['receipt_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['receipt_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'invoice_id' => 'Invoice ID',
            'receipt_id' => 'Receipt ID',
            'amount' => 'Amount',
            'payment_date' => 'Payment Date',
            'note' => 'Note',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'company_id' => 'Company ID',
        ];
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
     * Gets query for [[Receipt]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReceipt()
    {
        return $this->hasOne(Invoice::class, ['id' => 'receipt_id']);
    }
}
