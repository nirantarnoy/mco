<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "purch_vendor_bill".
 *
 * @property int $id
 * @property int|null $purch_id
 * @property string|null $bill_date
 * @property string|null $appoinment_date
 * @property string|null $bill_doc
 */
class PurchVendorBill extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_vendor_bill';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_id', 'bill_date', 'appoinment_date', 'bill_doc'], 'default', 'value' => null],
            [['purch_id'], 'integer'],
            [['bill_date', 'appoinment_date'], 'safe'],
            [['bill_doc'], 'string', 'max' => 255],
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
            'bill_date' => 'Bill Date',
            'appoinment_date' => 'Appoinment Date',
            'bill_doc' => 'Bill Doc',
        ];
    }

}
