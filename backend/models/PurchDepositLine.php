<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "purch_deposit_line".
 *
 * @property int $id
 * @property int|null $purch_deposit_id
 * @property float|null $deposit_amount
 * @property string|null $deposit_date
 * @property string|null $deposit_doc
 * @property string|null $receive_date
 * @property string|null $receive_doc
 * @property string|null $note
 */
class PurchDepositLine extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_deposit_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_deposit_id', 'deposit_amount', 'deposit_date', 'deposit_doc', 'receive_date', 'receive_doc', 'note'], 'default', 'value' => null],
            [['purch_deposit_id'], 'integer'],
            [['deposit_amount'], 'number'],
            [['deposit_date', 'receive_date'], 'safe'],
            [['deposit_doc', 'receive_doc', 'note'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_deposit_id' => 'Purch Deposit ID',
            'deposit_amount' => 'Deposit Amount',
            'deposit_date' => 'Deposit Date',
            'deposit_doc' => 'Deposit Doc',
            'receive_date' => 'Receive Date',
            'receive_doc' => 'Receive Doc',
            'note' => 'Note',
        ];
    }

}
