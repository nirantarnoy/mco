<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "purch_deposit".
 *
 * @property int $id
 * @property int|null $purch_id
 * @property string|null $trans_date
 * @property int|null $status
 * @property float|null $amount
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class PurchDeposit extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_deposit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_id', 'trans_date', 'status', 'amount', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['purch_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['trans_date'], 'safe'],
            [['amount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_id' => 'Purch ID',
            'trans_date' => 'Trans Date',
            'status' => 'Status',
            'amount' => 'Amount',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}
