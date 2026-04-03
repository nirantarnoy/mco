<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Model class for table "temp_invoice_line".
 */
class TempInvoiceLine extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%temp_invoice_line}}';
    }

    public function rules()
    {
        return [
            [['temp_invoice_id'], 'required'],
            [['temp_invoice_id'], 'integer'],
            [['description'], 'string'],
            [['quantity', 'unit_price', 'amount'], 'number'],
            [['unit'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'temp_invoice_id' => 'รหัสใบแจ้งหนี้ชั่วคราว',
            'description' => 'รายการสินค้า/บริการ',
            'quantity' => 'จำนวน',
            'unit' => 'หน่วย',
            'unit_price' => 'ราคาต่อหน่วย',
            'amount' => 'จำนวนเงิน',
        ];
    }

    public function getInvoice()
    {
        return $this->hasOne(TempInvoice::class, ['id' => 'temp_invoice_id']);
    }
}
