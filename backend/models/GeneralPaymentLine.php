<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "general_payment_line".
 *
 * @property int $id
 * @property int|null $general_payment_id
 * @property string|null $description
 * @property int|null $bank_id
 * @property string|null $bank_name
 * @property int|null $payment_method_id
 * @property float|null $pay_amount
 * @property string|null $doc
 * @property string|null $note
 */
class GeneralPaymentLine extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'general_payment_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['general_payment_id', 'description', 'bank_id', 'bank_name', 'payment_method_id', 'pay_amount', 'doc', 'note'], 'default', 'value' => null],
            [['general_payment_id', 'bank_id', 'payment_method_id'], 'integer'],
            [['pay_amount'], 'number'],
            [['description', 'bank_name', 'doc', 'note'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'general_payment_id' => 'General Payment ID',
            'description' => 'Description',
            'bank_id' => 'Bank ID',
            'bank_name' => 'Bank Name',
            'payment_method_id' => 'Payment Method ID',
            'pay_amount' => 'Pay Amount',
            'doc' => 'Doc',
            'note' => 'Note',
        ];
    }

}
