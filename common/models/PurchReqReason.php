<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "purch_req_reason".
 *
 * @property int $id
 * @property int|null $purch_req_id
 * @property int|null $reason_id
 * @property int|null $is_enable
 */
class PurchReqReason extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_req_reason';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_req_id', 'reason_id', 'is_enable'], 'default', 'value' => null],
            [['purch_req_id', 'reason_id', 'is_enable'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_req_id' => 'Purch Req ID',
            'reason_id' => 'Reason ID',
            'is_enable' => 'Is Enable',
        ];
    }

}
