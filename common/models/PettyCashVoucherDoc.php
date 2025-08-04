<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "petty_cash_voucher_doc".
 *
 * @property int $id
 * @property int|null $petty_cash_voucher_id
 * @property string|null $doc
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $created_by
 */
class PettyCashVoucherDoc extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'petty_cash_voucher_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['petty_cash_voucher_id', 'doc', 'status', 'created_at', 'created_by'], 'default', 'value' => null],
            [['petty_cash_voucher_id', 'status', 'created_at', 'created_by'], 'integer'],
            [['doc'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'petty_cash_voucher_id' => 'Petty Cash Voucher ID',
            'doc' => 'Doc',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

}
