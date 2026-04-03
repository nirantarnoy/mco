<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * Model class for table "temp_invoice".
 */
class TempInvoice extends ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_CONVERTED = 1;
    const STATUS_CANCELLED = 9;

    public static function tableName()
    {
        return '{{%temp_invoice}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['temp_invoice_id', 'status', 'company_id', 'created_at', 'updated_at', 'created_by'], 'integer'],
            [['invoice_date'], 'safe'],
            [['customer_address', 'raw_text'], 'string'],
            [['subtotal', 'vat_amount', 'total_amount'], 'number'],
            [['invoice_type'], 'string', 'max' => 50],
            [['invoice_number'], 'string', 'max' => 100],
            [['vendor_name', 'customer_name'], 'string', 'max' => 255],
            [['customer_tax_id'], 'string', 'max' => 20],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'invoice_type' => 'ประเภทเอกสาร',
            'invoice_number' => 'เลขที่เอกสาร',
            'invoice_date' => 'วันที่',
            'vendor_name' => 'ผู้ขาย',
            'customer_name' => 'ลูกค้า',
            'customer_tax_id' => 'เลขที่ผู้เสียภาษี',
            'customer_address' => 'ที่อยู่ลูกค้า',
            'subtotal' => 'ยอดรวมก่อนภาษี',
            'vat_amount' => 'ภาษีมูลค่าเพิ่ม',
            'total_amount' => 'ยอดเงินรวมทั้งสิ้น',
            'raw_text' => 'ข้อมูล OCR ดิบ',
            'status' => 'สถานะ',
        ];
    }

    public function getLines()
    {
        return $this->hasMany(TempInvoiceLine::class, ['temp_invoice_id' => 'id']);
    }
}
