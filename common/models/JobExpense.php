<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "job_expense".
 *
 * @property int $id
 * @property int|null $job_id
 * @property string|null $trans_date
 * @property string|null $description
 * @property float|null $line_amount
 * @property string|null $line_doc
 */
class JobExpense extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'job_expense';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['job_id', 'trans_date', 'description', 'line_amount', 'line_doc'], 'default', 'value' => null],
            [['job_id'], 'integer'],
            [['trans_date'], 'safe'],
            [['line_amount'], 'number'],
            [['description', 'line_doc'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'job_id' => 'Job ID',
            'trans_date' => 'Trans Date',
            'description' => 'Description',
            'line_amount' => 'Line Amount',
            'line_doc' => 'Line Doc',
        ];
    }

}
