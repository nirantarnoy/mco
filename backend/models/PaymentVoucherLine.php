<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "payment_voucher_line".
 *
 * @property int $id
 * @property int|null $payment_voucher_id
 * @property string|null $account_code
 * @property string|null $bill_code
 * @property string|null $description
 * @property float|null $debit
 * @property float|null $credit
 *
 * @property PaymentVoucher $paymentVoucher
 */
class PaymentVoucherLine extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_voucher_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_voucher_id'], 'integer'],
            [['description'], 'string'],
            [['debit', 'credit'], 'number'],
            [['account_code', 'bill_code'], 'string', 'max' => 50],
            [['payment_voucher_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentVoucher::class, 'targetAttribute' => ['payment_voucher_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_voucher_id' => 'Payment Voucher ID',
            'account_code' => 'Code Acc.',
            'bill_code' => 'Code Bill',
            'description' => 'Description',
            'debit' => 'Debit',
            'credit' => 'Credit',
        ];
    }

    /**
     * Gets query for [[PaymentVoucher]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentVoucher()
    {
        return $this->hasOne(PaymentVoucher::class, ['id' => 'payment_voucher_id']);
    }
}
