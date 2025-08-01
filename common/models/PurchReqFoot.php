<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "purch_req_foot".
 *
 * @property int $id
 * @property int|null $purch_req_id
 * @property int|null $footer_id
 * @property int|null $is_enable
 */
class PurchReqFoot extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_req_foot';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_req_id', 'footer_id', 'is_enable'], 'default', 'value' => null],
            [['purch_req_id', 'footer_id', 'is_enable'], 'integer'],
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
            'footer_id' => 'Footer ID',
            'is_enable' => 'Is Enable',
        ];
    }

}
