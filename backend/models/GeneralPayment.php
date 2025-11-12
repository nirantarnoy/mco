<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "general_payment".
 *
 * @property int $id
 * @property string|null $journal_no
 * @property int|null $company_id
 * @property string|null $trans_date
 * @property string|null $description
 * @property int|null $status
 * @property float|null $total_amount
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class GeneralPayment extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'general_payment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['journal_no', 'company_id', 'trans_date', 'description', 'status', 'total_amount', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['company_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['trans_date'], 'safe'],
            [['total_amount'], 'number'],
            [['journal_no', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'journal_no' => 'Journal No',
            'company_id' => 'Company ID',
            'trans_date' => 'Trans Date',
            'description' => 'Description',
            'status' => 'Status',
            'total_amount' => 'Total Amount',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
