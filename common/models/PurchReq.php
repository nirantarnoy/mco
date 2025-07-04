<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "purch_req".
 *
 * @property int $id
 * @property string|null $purch_req_no
 * @property string|null $purch_req_date
 * @property int|null $vendor_id
 * @property string|null $vendor_name
 * @property int|null $status
 * @property string|null $note
 * @property int|null $approve_status
 * @property float|null $total_amount
 * @property string|null $total_text
 * @property int|null $purch_id
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class PurchReq extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_req';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_req_no', 'purch_req_date', 'vendor_id', 'vendor_name', 'status', 'note', 'approve_status', 'total_amount', 'total_text', 'purch_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['purch_req_date'], 'safe'],
            [['vendor_id', 'status', 'approve_status', 'purch_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['total_amount'], 'number'],
            [['purch_req_no', 'vendor_name', 'note', 'total_text'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_req_no' => 'Purch Req No',
            'purch_req_date' => 'Purch Req Date',
            'vendor_id' => 'Vendor ID',
            'vendor_name' => 'Vendor Name',
            'status' => 'Status',
            'note' => 'Note',
            'approve_status' => 'Approve Status',
            'total_amount' => 'Total Amount',
            'total_text' => 'Total Text',
            'purch_id' => 'Purch ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
