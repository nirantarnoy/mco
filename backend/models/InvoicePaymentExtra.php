<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class InvoicePaymentExtra extends ActiveRecord
{
    public static function tableName()
    {
        return 'invoice_payment_extra';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['payment_receipt_id', 'extra_option_id', 'amount'], 'required'],
            [['payment_receipt_id', 'extra_option_id', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
        ];
    }

    public function getReceipt()
    {
        return $this->hasOne(InvoicePaymentReceipt::class, ['id' => 'payment_receipt_id']);
    }

    public function getExtraOption()
    {
        return $this->hasOne(PaymentExtraOption::class, ['id' => 'extra_option_id']);
    }
}
