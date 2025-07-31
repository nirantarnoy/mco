<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "quotation".
 *
 * @property int $id
 * @property string|null $quotation_no
 * @property string|null $quotation_date
 * @property int|null $customer_id
 * @property string|null $customer_name
 * @property int|null $status
 * @property int|null $approve_status
 * @property int|null $approve_by
 * @property float|null $total_amount
 * @property string|null $total_amount_text
 * @property string|null $note
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class Quotation extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'quotation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quotation_no', 'quotation_date', 'customer_id', 'customer_name', 'status', 'approve_status', 'approve_by', 'total_amount', 'total_amount_text', 'note', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['quotation_date'], 'safe'],
            [['customer_id', 'status', 'approve_status', 'approve_by', 'created_at', 'created_by', 'updated_at', 'updated_by','payment_term_id','payment_method_id'], 'integer'],
            [['total_amount'], 'number'],
            [['quotation_no', 'customer_name', 'total_amount_text', 'note'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'quotation_no' => 'Quotation No',
            'quotation_date' => 'Quotation Date',
            'customer_id' => 'Customer ID',
            'customer_name' => 'Customer Name',
            'status' => 'Status',
            'approve_status' => 'Approve Status',
            'approve_by' => 'Approve By',
            'total_amount' => 'Total Amount',
            'total_amount_text' => 'Total Amount Text',
            'note' => 'Note',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'payment_term_id' => 'Payment Term',
            'payment_method_id' => 'Payment Method',
        ];
    }

}
