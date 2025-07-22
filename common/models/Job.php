<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "job".
 *
 * @property int $id
 * @property string|null $job_no
 * @property int|null $quotation_id
 * @property string|null $job_date
 * @property int|null $status
 * @property float|null $job_amount
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
            [['job_no'],'required'],
            [['job_no', 'quotation_id', 'job_date', 'status', 'job_amount', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['quotation_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['job_date','start_date','end_date'], 'safe'],
            [['job_amount'], 'number'],
            [['job_no'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'job_no' => 'เลขที่ใบงาน',
            'quotation_id' => 'ใบเสนอราคา',
            'job_date' => 'วันที่',
            'status' => 'สถานะ',
            'job_amount' => 'มูลค่างาน',
            'start_date' => 'วันที่เริ่มงาน',
            'end_date' => 'วันที่สิ้นสุดงาน',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
