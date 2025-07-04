<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "job".
 *
 * @property int $id
 * @property string|null $job_no
 * @property string|null $job_date
 * @property int|null $customer_id
 * @property string|null $customer_name
 * @property string|null $quote_no
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class Job extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'job';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['job_no', 'job_date', 'customer_id', 'customer_name', 'quote_no', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['job_date'], 'safe'],
            [['customer_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['job_no', 'customer_name', 'quote_no'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'job_no' => 'Job No',
            'job_date' => 'Job Date',
            'customer_id' => 'Customer ID',
            'customer_name' => 'Customer Name',
            'quote_no' => 'Quote No',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
