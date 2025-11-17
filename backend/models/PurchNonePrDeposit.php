<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "purch_none_pr_deposit".
 *
 * @property int $id
 * @property int|null $purchase_master_id
 * @property string|null $trans_date
 * @property int|null $status
 * @property float|null $amount
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class PurchNonePrDeposit extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_none_pr_deposit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purchase_master_id', 'trans_date', 'status', 'amount', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['purchase_master_id', 'status', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
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
            'purchase_master_id' => 'Purchase Master ID',
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
