<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "purch".
 *
 * @property int $id
 * @property string|null $purch_no
 * @property string|null $purch_date
 * @property int|null $vendor_id
 * @property string|null $vendor_name
 * @property int|null $status
 * @property string|null $note
 * @property int|null $approve_status
 * @property float|null $total_amount
 * @property string|null $total_text
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class Purch extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_no', 'purch_date', 'vendor_id', 'vendor_name', 'status', 'note', 'approve_status', 'total_amount', 'total_text', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['purch_date'], 'safe'],
            [['vendor_id', 'status', 'approve_status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['total_amount'], 'number'],
            [['purch_no', 'vendor_name', 'note', 'total_text'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_no' => 'Purch No',
            'purch_date' => 'Purch Date',
            'vendor_id' => 'Vendor ID',
            'vendor_name' => 'Vendor Name',
            'status' => 'Status',
            'note' => 'Note',
            'approve_status' => 'Approve Status',
            'total_amount' => 'Total Amount',
            'total_text' => 'Total Text',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
